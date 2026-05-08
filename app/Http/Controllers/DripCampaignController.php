<?php

namespace App\Http\Controllers;

use App\Models\DripCampaign;
use App\Models\DripEnrollment;
use App\Models\DripStep;
use App\Models\Group;
use App\Models\Contact;
use App\Models\Unsubscribe;
use Illuminate\Http\Request;

class DripCampaignController extends Controller
{
    public function index()
    {
        $accountId = $this->currentAccountId();

        $drips = DripCampaign::where('account_id', $accountId)
            ->withCount(['steps', 'enrollments', 'enrollments as active_count' => fn($q) => $q->where('status', 'active')])
            ->latest()
            ->paginate(15);

        return view('drip.index', compact('drips'));
    }

    public function create()
    {
        $accountId = $this->currentAccountId();
        $groups = Group::where('account_id', $accountId)->orderBy('name')->get();

        return view('drip.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $accountId = $this->getAccountId($request);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'group_id'    => ['nullable', 'integer', 'exists:groups,id'],
        ]);

        $drip = DripCampaign::create([
            'account_id'  => $accountId,
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'group_id'    => $data['group_id'] ?? null,
            'status'      => 'draft',
        ]);

        return redirect()->route('drip.show', $drip)->with('success', 'Drip campaign created. Now add your email steps.');
    }

    public function show(DripCampaign $drip)
    {
        $accountId = $this->currentAccountId();
        abort_if((int) $drip->account_id !== $accountId, 403);

        $drip->load('steps', 'group');
        $enrollmentCount = $drip->enrollments()->count();
        $activeCount     = $drip->enrollments()->where('status', 'active')->count();
        $completedCount  = $drip->enrollments()->where('status', 'completed')->count();

        return view('drip.show', compact('drip', 'enrollmentCount', 'activeCount', 'completedCount'));
    }

    public function edit(DripCampaign $drip)
    {
        $accountId = $this->currentAccountId();
        abort_if((int) $drip->account_id !== $accountId, 403);

        $groups = Group::where('account_id', $accountId)->orderBy('name')->get();

        return view('drip.edit', compact('drip', 'groups'));
    }

    public function update(Request $request, DripCampaign $drip)
    {
        $accountId = $this->getAccountId($request);
        abort_if((int) $drip->account_id !== $accountId, 403);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'group_id'    => ['nullable', 'integer', 'exists:groups,id'],
        ]);

        $drip->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'group_id'    => $data['group_id'] ?? null,
        ]);

        return redirect()->route('drip.show', $drip)->with('success', 'Drip campaign updated.');
    }

    public function destroy(DripCampaign $drip)
    {
        $accountId = $this->currentAccountId();
        abort_if((int) $drip->account_id !== $accountId, 403);

        $drip->delete();

        return redirect()->route('drip.index')->with('success', 'Drip campaign deleted.');
    }

    // ── Steps ─────────────────────────────────────────────────────────────────

    public function storeStep(Request $request, DripCampaign $drip)
    {
        $accountId = $this->getAccountId($request);
        abort_if((int) $drip->account_id !== $accountId, 403);

        $data = $request->validate([
            'subject'    => ['required', 'string', 'max:255'],
            'body'       => ['required', 'string'],
            'delay_days' => ['required', 'integer', 'min:0', 'max:365'],
        ]);

        $nextPosition = $drip->steps()->max('position') + 1;

        DripStep::create([
            'drip_campaign_id' => $drip->id,
            'position'         => $nextPosition,
            'subject'          => $data['subject'],
            'body'             => html_entity_decode($data['body'], ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'delay_days'       => $data['delay_days'],
        ]);

        return redirect()->route('drip.show', $drip)->with('success', "Step {$nextPosition} added.");
    }

    public function updateStep(Request $request, DripCampaign $drip, DripStep $step)
    {
        $accountId = $this->getAccountId($request);
        abort_if((int) $drip->account_id !== $accountId, 403);
        abort_if($step->drip_campaign_id !== $drip->id, 404);

        $data = $request->validate([
            'subject'    => ['required', 'string', 'max:255'],
            'body'       => ['required', 'string'],
            'delay_days' => ['required', 'integer', 'min:0', 'max:365'],
        ]);

        $step->update([
            'subject'    => $data['subject'],
            'body'       => html_entity_decode($data['body'], ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'delay_days' => $data['delay_days'],
        ]);

        return redirect()->route('drip.show', $drip)->with('success', 'Step updated.');
    }

    public function destroyStep(DripCampaign $drip, DripStep $step)
    {
        $accountId = $this->currentAccountId();
        abort_if((int) $drip->account_id !== $accountId, 403);
        abort_if($step->drip_campaign_id !== $drip->id, 404);

        $step->delete();

        // Re-sequence positions
        $drip->steps()->orderBy('position')->each(function ($s, $i) {
            $s->update(['position' => $i + 1]);
        });

        return redirect()->route('drip.show', $drip)->with('success', 'Step removed.');
    }

    // ── Activate / Pause ─────────────────────────────────────────────────────

    public function activate(DripCampaign $drip)
    {
        $accountId = $this->currentAccountId();
        abort_if((int) $drip->account_id !== $accountId, 403);

        if ($drip->steps()->count() === 0) {
            return back()->withErrors(['drip' => 'Add at least one step before activating.']);
        }

        $drip->update(['status' => 'active']);

        // Auto-enroll all contacts from the linked group (if set)
        $enrolled = 0;
        if ($drip->group_id) {
            $unsubEmails = Unsubscribe::pluck('email')->map(fn($e) => strtolower($e))->flip();

            $contacts = Contact::whereHas('groups', fn($q) => $q->where('groups.id', $drip->group_id))
                ->where('account_id', $accountId)
                ->where('is_bounced', false)
                ->get(['id', 'email', 'account_id']);

            $firstStep = $drip->steps()->orderBy('position')->first();

            foreach ($contacts as $contact) {
                if ($unsubEmails->has(strtolower($contact->email))) continue;

                $created = DripEnrollment::firstOrCreate(
                    ['drip_campaign_id' => $drip->id, 'contact_id' => $contact->id],
                    [
                        'account_id'   => $accountId,
                        'current_step' => 1,
                        'next_send_at' => now()->addDays($firstStep?->delay_days ?? 0),
                        'status'       => 'active',
                    ]
                );

                if ($created->wasRecentlyCreated) $enrolled++;
            }
        }

        $msg = 'Drip campaign activated.';
        if ($enrolled > 0) $msg .= " {$enrolled} contacts enrolled from group.";

        return back()->with('success', $msg);
    }

    public function pause(DripCampaign $drip)
    {
        $accountId = $this->currentAccountId();
        abort_if((int) $drip->account_id !== $accountId, 403);

        $drip->update(['status' => 'paused']);

        return back()->with('success', 'Drip campaign paused.');
    }

    // ── Manual enroll a single contact ───────────────────────────────────────

    public function enroll(Request $request, DripCampaign $drip)
    {
        $accountId = $this->getAccountId($request);
        abort_if((int) $drip->account_id !== $accountId, 403);

        $data = $request->validate([
            'contact_id' => ['required', 'integer', 'exists:contacts,id'],
        ]);

        $contact = Contact::where('id', $data['contact_id'])
            ->where('account_id', $accountId)
            ->firstOrFail();

        $firstStep = $drip->steps()->orderBy('position')->first();

        DripEnrollment::firstOrCreate(
            ['drip_campaign_id' => $drip->id, 'contact_id' => $contact->id],
            [
                'account_id'   => $accountId,
                'current_step' => 1,
                'next_send_at' => now()->addDays($firstStep?->delay_days ?? 0),
                'status'       => 'active',
            ]
        );

        return back()->with('success', "{$contact->email} enrolled in drip.");
    }

    // ── Unenroll a contact ───────────────────────────────────────────────────

    public function unenroll(DripCampaign $drip, DripEnrollment $enrollment)
    {
        $accountId = $this->currentAccountId();
        abort_if((int) $drip->account_id !== $accountId, 403);
        abort_if($enrollment->drip_campaign_id !== $drip->id, 404);

        $enrollment->delete();

        return back()->with('success', 'Contact unenrolled from drip.');
    }
}
