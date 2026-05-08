<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\EmailOpen;
use App\Models\EmailQueue;
use App\Models\Group;
use App\Models\SmtpServer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class CampaignController extends Controller
{
    public function index()
    {
        $accountId = $this->currentAccountId();

        $campaigns = Campaign::query()
            ->where('account_id', $accountId)
            ->withCount('contacts')
            ->withCount([
                'emailQueue as queued_count' => fn($q) => $q->whereIn('status', ['queued', 'pending']),
                'emailQueue as sent_count' => fn($q) => $q->where('status', 'sent'),
                'emailQueue as failed_count' => fn($q) => $q->where('status', 'failed'),
            ])
            ->latest()
            ->paginate(10);

        $campaignIds = $campaigns->pluck('id');

        $openedCounts = EmailOpen::query()
            ->selectRaw('email_queue.campaign_id, COUNT(email_opens.id) as open_count')
            ->join('email_queue', 'email_queue.id', '=', 'email_opens.email_queue_id')
            ->whereIn('email_queue.campaign_id', $campaignIds)
            ->groupBy('email_queue.campaign_id')
            ->pluck('open_count', 'campaign_id');

        foreach ($campaigns as $campaign) {
            $campaign->opened_count = (int) ($openedCounts[$campaign->id] ?? 0);
        }

        return view('campaigns.index', compact('campaigns'));
    }

    public function liveStats(Campaign $campaign)
    {
        $accountId = $this->currentAccountId();
        abort_if((int) $campaign->account_id !== $accountId, 403);

        $queued = $campaign->emailQueue()->whereIn('status', ['queued', 'pending'])->count();
        $sent = $campaign->emailQueue()->where('status', 'sent')->count();
        $failed = $campaign->emailQueue()->where('status', 'failed')->count();

        $opened = EmailOpen::query()
            ->join('email_queue', 'email_queue.id', '=', 'email_opens.email_queue_id')
            ->where('email_queue.campaign_id', $campaign->id)
            ->count('email_opens.id');

        $logs = EmailQueue::query()
            ->where('campaign_id', $campaign->id)
            ->latest('id')
            ->take(20)
            ->get(['id', 'email', 'status', 'attempts', 'last_error', 'updated_at'])
            ->map(function ($row) {
                return [
                    'id' => $row->id,
                    'email' => $row->email,
                    'status' => $row->status,
                    'attempts' => $row->attempts,
                    'last_error' => $row->last_error,
                    'updated_at' => optional($row->updated_at)->format('Y-m-d H:i:s'),
                ];
            });

        // A/B variant open counts
        $abStats = null;
        if ($campaign->ab_enabled) {
            $abStats = EmailOpen::query()
                ->join('email_queue', 'email_queue.id', '=', 'email_opens.email_queue_id')
                ->where('email_queue.campaign_id', $campaign->id)
                ->whereIn('email_queue.ab_variant', ['a', 'b'])
                ->selectRaw('email_queue.ab_variant, COUNT(email_opens.id) as opens')
                ->groupBy('email_queue.ab_variant')
                ->pluck('opens', 'ab_variant');
        }

        return response()->json([
            'campaign_id' => $campaign->id,
            'stats' => [
                'queued' => $queued,
                'sent' => $sent,
                'opened' => $opened,
                'failed' => $failed,
                'ab_a_opens' => $abStats ? (int) ($abStats['a'] ?? 0) : null,
                'ab_b_opens' => $abStats ? (int) ($abStats['b'] ?? 0) : null,
            ],
            'logs' => $logs,
        ]);
    }

    public function create()
    {
        $accountId = $this->currentAccountId();

        $contacts = Contact::query()->where('account_id', $accountId)->with('groups')->orderBy('name')->get();
        $groupContacts = Contact::query()
            ->whereHas('groups', function ($query) use ($accountId) {
                $query->where('groups.account_id', $accountId);
            })
            ->with('groups')
            ->orderBy('name')
            ->get();
        $groups = Group::query()->where('account_id', $accountId)->orderBy('name')->get();

        $warmupSchedule = Campaign::WARMUP_SCHEDULE;

        return view('campaigns.create', compact('contacts', 'groupContacts', 'groups', 'warmupSchedule'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'contact_ids' => ['nullable', 'array'],
            'contact_ids.*' => ['integer', 'exists:contacts,id'],
            'group_ids' => ['nullable', 'array'],
            'group_ids.*' => ['integer', 'exists:groups,id'],
            'attachment' => ['nullable', 'file', 'max:10240'],
            'warmup_enabled' => ['nullable', 'boolean'],
            'emails_per_minute' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'ab_enabled' => ['nullable', 'boolean'],
            'ab_subject_b' => ['nullable', 'string', 'max:255'],
            'ab_body_b' => ['nullable', 'string'],
        ]);

        $attachmentPath = null;
        $attachmentName = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('campaign_attachments', 'public');
            $attachmentName = $file->getClientOriginalName();
        }

        $accountId = $this->getAccountId($request);
        $abEnabled = $request->boolean('ab_enabled');

        $campaign = new Campaign();
        $campaign->account_id = $accountId;
        $campaign->name = $data['name'];
        $campaign->subject = $data['subject'];
        $campaign->body = html_entity_decode($data['body'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $campaign->attachment_path = $attachmentPath;
        $campaign->attachment_name = $attachmentName;
        $campaign->scheduled_at = $data['scheduled_at'] ?? null;
        $campaign->status = !empty($data['scheduled_at']) ? 'scheduled' : 'draft';
        $campaign->warmup_enabled = $request->boolean('warmup_enabled');
        $campaign->emails_per_minute = $data['emails_per_minute'] ?? null;
        $campaign->warmup_day = $campaign->warmup_day ?: 1;
        $campaign->ab_enabled = $abEnabled;
        $campaign->ab_subject_b = $abEnabled ? ($data['ab_subject_b'] ?? null) : null;
        $campaign->ab_body_b = $abEnabled && !empty($data['ab_body_b'])
            ? html_entity_decode($data['ab_body_b'], ENT_QUOTES | ENT_HTML5, 'UTF-8')
            : null;

        if ($campaign->warmup_enabled && empty($campaign->warmup_started_at)) {
            $campaign->warmup_started_at = now();
        }

        $campaign->save();

        $contactIds = Contact::query()
            ->where('account_id', $accountId)
            ->whereIn('id', collect($data['contact_ids'] ?? [])->map(fn ($id) => (int) $id)->all())
            ->pluck('id');

        $groupContactIds = Contact::query()
            ->whereHas('groups', function ($query) use ($data, $accountId) {
                $query->where('groups.account_id', $accountId)
                    ->whereIn('groups.id', $data['group_ids'] ?? []);
            })
            ->pluck('contacts.id');

        $finalIds = $contactIds->merge($groupContactIds)->unique()->values()->all();
        $campaign->contacts()->sync($finalIds);

        return redirect()->route('campaigns.index')->with('success', 'Campaign created successfully.');
    }

    public function edit(Campaign $campaign)
    {
        $accountId = $this->currentAccountId();
        abort_if((int) $campaign->account_id !== $accountId, 403);

        $contacts = Contact::query()->where('account_id', $accountId)->with('groups')->orderBy('name')->get();        $groupContacts = Contact::query()
            ->whereHas('groups', function ($query) use ($accountId) {
                $query->where('groups.account_id', $accountId);
            })
            ->with('groups')
            ->orderBy('name')
            ->get();        $groups = Group::query()->where('account_id', $accountId)->orderBy('name')->get();
        $selectedContactIds = $campaign->contacts()->where('contacts.account_id', $accountId)->pluck('contacts.id')->toArray();
        $selectedGroupIds   = [];

        $warmupSchedule = Campaign::WARMUP_SCHEDULE;

        return view('campaigns.edit', compact('campaign', 'contacts', 'groupContacts', 'groups', 'selectedContactIds', 'selectedGroupIds', 'warmupSchedule'));
    }

    public function update(Request $request, Campaign $campaign)
    {
        $accountId = $this->getAccountId($request);
        abort_if((int) $campaign->account_id !== $accountId, 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'contact_ids' => ['nullable', 'array'],
            'contact_ids.*' => ['integer', 'exists:contacts,id'],
            'group_ids' => ['nullable', 'array'],
            'group_ids.*' => ['integer', 'exists:groups,id'],
            'attachment' => ['nullable', 'file', 'max:10240'],
            'remove_attachment' => ['nullable', 'boolean'],
            'warmup_enabled' => ['nullable', 'boolean'],
            'emails_per_minute' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'ab_enabled' => ['nullable', 'boolean'],
            'ab_subject_b' => ['nullable', 'string', 'max:255'],
            'ab_body_b' => ['nullable', 'string'],
        ]);

        $wasWarmupEnabled = (bool) $campaign->warmup_enabled;
        $isWarmupEnabled = $request->boolean('warmup_enabled');
        $abEnabled = $request->boolean('ab_enabled');

        $updateData = [
            'name' => $data['name'],
            'subject' => $data['subject'],
            'body' => html_entity_decode($data['body'], ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'status' => !empty($data['scheduled_at']) ? 'scheduled' : 'draft',
            'warmup_enabled' => $isWarmupEnabled,
            'emails_per_minute' => $data['emails_per_minute'] ?? null,
            'ab_enabled' => $abEnabled,
            'ab_subject_b' => $abEnabled ? ($data['ab_subject_b'] ?? null) : null,
            'ab_body_b' => $abEnabled && !empty($data['ab_body_b'])
                ? html_entity_decode($data['ab_body_b'], ENT_QUOTES | ENT_HTML5, 'UTF-8')
                : null,
        ];

        if (!$wasWarmupEnabled && $isWarmupEnabled && empty($campaign->warmup_started_at)) {
            $updateData['warmup_started_at'] = now();
            $updateData['warmup_day'] = max(1, (int) ($campaign->warmup_day ?: 1));
        }

        if ($request->boolean('remove_attachment')) {
            if (!empty($campaign->attachment_path) && Storage::disk('public')->exists($campaign->attachment_path)) {
                Storage::disk('public')->delete($campaign->attachment_path);
            }
            $updateData['attachment_path'] = null;
            $updateData['attachment_name'] = null;
        }

        if ($request->hasFile('attachment')) {
            if (!empty($campaign->attachment_path) && Storage::disk('public')->exists($campaign->attachment_path)) {
                Storage::disk('public')->delete($campaign->attachment_path);
            }
            $file = $request->file('attachment');
            $updateData['attachment_path'] = $file->store('campaign_attachments', 'public');
            $updateData['attachment_name'] = $file->getClientOriginalName();
        }

        $campaign->update($updateData);

        $contactIds = Contact::query()
            ->where('account_id', $accountId)
            ->whereIn('id', collect($data['contact_ids'] ?? [])->map(fn ($id) => (int) $id)->all())
            ->pluck('id');

        $groupContactIds = Contact::query()
            ->whereHas('groups', function ($query) use ($data, $accountId) {
                $query->where('groups.account_id', $accountId)
                    ->whereIn('groups.id', $data['group_ids'] ?? []);
            })
            ->pluck('contacts.id');

        $finalIds = $contactIds->merge($groupContactIds)->unique()->values()->all();
        $campaign->contacts()->sync($finalIds);

        return redirect()->route('campaigns.index')->with('success', 'Campaign updated successfully.');
    }

    public function destroy(Campaign $campaign)
    {
        $accountId = $this->currentAccountId();
        abort_if((int) $campaign->account_id !== $accountId, 403);

        $campaign->delete();

        return redirect()->route('campaigns.index')->with('success', 'Campaign deleted successfully.');
    }

    public function duplicate(Campaign $campaign)
    {
        $accountId = $this->currentAccountId();
        abort_if((int) $campaign->account_id !== $accountId, 403);

        $clone = $campaign->replicate(['warmup_started_at', 'warmup_day']);
        $clone->name        = 'Copy of ' . $campaign->name;
        $clone->status      = 'draft';
        $clone->scheduled_at = null;
        $clone->warmup_day  = 1;
        $clone->warmup_started_at = null;
        // Don't copy attachment — user should re-attach if needed
        $clone->attachment_path = null;
        $clone->attachment_name = null;
        $clone->save();

        // Copy contacts
        $contactIds = $campaign->contacts()->pluck('contacts.id');
        $clone->contacts()->sync($contactIds);

        return redirect()->route('campaigns.edit', $clone)
            ->with('success', 'Campaign duplicated. Review and update before sending.');
    }

    public function sendTestEmail(Request $request, Campaign $campaign)
    {
        $accountId = $this->getAccountId($request);
        abort_if((int) $campaign->account_id !== $accountId, 403);

        $data = $request->validate([
            'test_email' => ['required', 'email'],
        ]);

        $smtp = SmtpServer::query()
            ->where('account_id', $accountId)
            ->where('is_active', 1)
            ->orderByDesc('id')
            ->first();

        if (!$smtp) {
            return back()->withErrors(['campaign_test_email' => 'No active SMTP server found. Please activate an SMTP server first.']);
        }

        try {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.host' => $smtp->host,
                'mail.mailers.smtp.port' => $smtp->port,
                'mail.mailers.smtp.username' => $smtp->username,
                'mail.mailers.smtp.password' => $smtp->password,
                'mail.mailers.smtp.encryption' => $smtp->encryption === 'none' ? null : $smtp->encryption,
                'mail.mailers.smtp.timeout' => 8,
                'mail.from.address' => $smtp->from_email,
                'mail.from.name' => $smtp->from_name,
            ]);

            $mergePlaceholders = [
                '{{First Name}}', '{{Name}}', '{{Email}}', '{{Business Name}}', '{{Website}}'
            ];
            $mergeValues = [
                '[First Name]', '[Name]', $data['test_email'], '[Business Name]', '[Website]'
            ];

            $testBody = html_entity_decode($campaign->body, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $testBody = str_replace($mergePlaceholders, $mergeValues, $testBody);

            $testSubject = str_replace($mergePlaceholders, $mergeValues, (string) $campaign->subject);

            Mail::html($testBody, function ($message) use ($data, $testSubject, $campaign, $smtp) {
                $message->to($data['test_email'])
                    ->subject("[TEST] {$testSubject}")
                    ->from($smtp->from_email, $smtp->from_name);

                if (!empty($campaign->attachment_path) && Storage::disk('public')->exists($campaign->attachment_path)) {
                    $message->attach(Storage::disk('public')->path($campaign->attachment_path), [
                        'as' => $campaign->attachment_name ?: basename($campaign->attachment_path),
                    ]);
                }
            });

            return back()->with('success', 'Test campaign email sent successfully.');
        } catch (\Throwable $e) {
            return back()->withErrors(['campaign_test_email' => "Failed to send test campaign email: {$e->getMessage()}"]);
        }
    }
}
