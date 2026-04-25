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

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::withCount('contacts')
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

        return response()->json([
            'campaign_id' => $campaign->id,
            'stats' => [
                'queued' => $queued,
                'sent' => $sent,
                'opened' => $opened,
                'failed' => $failed,
            ],
            'logs' => $logs,
        ]);
    }

    public function create()
    {
        $contacts = Contact::orderBy('name')->get();
        $groups = Group::orderBy('name')->get();

        return view('campaigns.create', compact('contacts', 'groups'));
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
        ]);

        $campaign = Campaign::create([
            'name' => $data['name'],
            'subject' => $data['subject'],
            'body' => $data['body'],
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'status' => !empty($data['scheduled_at']) ? 'scheduled' : 'draft',
        ]);

        $contactIds = collect($data['contact_ids'] ?? []);
        $groupContactIds = Contact::whereHas('groups', function ($query) use ($data) {
            $query->whereIn('groups.id', $data['group_ids'] ?? []);
        })->pluck('contacts.id');

        $finalIds = $contactIds->merge($groupContactIds)->unique()->values()->all();
        $campaign->contacts()->sync($finalIds);

        return redirect()->route('campaigns.index')->with('success', 'Campaign created successfully.');
    }

    public function edit(Campaign $campaign)
    {
        $contacts = Contact::orderBy('name')->get();
        $groups = Group::orderBy('name')->get();
        $selectedContactIds = $campaign->contacts()->pluck('contacts.id')->toArray();

        return view('campaigns.edit', compact('campaign', 'contacts', 'groups', 'selectedContactIds'));
    }

    public function update(Request $request, Campaign $campaign)
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
        ]);

        $campaign->update([
            'name' => $data['name'],
            'subject' => $data['subject'],
            'body' => $data['body'],
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'status' => !empty($data['scheduled_at']) ? 'scheduled' : 'draft',
        ]);

        $contactIds = collect($data['contact_ids'] ?? []);
        $groupContactIds = Contact::whereHas('groups', function ($query) use ($data) {
            $query->whereIn('groups.id', $data['group_ids'] ?? []);
        })->pluck('contacts.id');

        $finalIds = $contactIds->merge($groupContactIds)->unique()->values()->all();
        $campaign->contacts()->sync($finalIds);

        return redirect()->route('campaigns.index')->with('success', 'Campaign updated successfully.');
    }

    public function destroy(Campaign $campaign)
    {
        $campaign->delete();

        return redirect()->route('campaigns.index')->with('success', 'Campaign deleted successfully.');
    }

    public function sendTestEmail(Request $request, Campaign $campaign)
    {
        $data = $request->validate([
            'test_email' => ['required', 'email'],
        ]);

        $smtp = SmtpServer::where('is_active', 1)->orderByDesc('id')->first();

        if (!$smtp) {
            return back()->withErrors(['campaign_test_email' => 'No active SMTP server found. Please activate an SMTP server first.']);
        }

        try {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $smtp->host,
                'mail.mailers.smtp.port' => $smtp->port,
                'mail.mailers.smtp.username' => $smtp->username,
                'mail.mailers.smtp.password' => $smtp->password,
                'mail.mailers.smtp.encryption' => $smtp->encryption === 'none' ? null : $smtp->encryption,
                'mail.from.address' => $smtp->from_email,
                'mail.from.name' => $smtp->from_name,
            ]);

            Mail::raw($campaign->body, function ($message) use ($data, $campaign, $smtp) {
                $message->to($data['test_email'])
                    ->subject("[TEST] {$campaign->subject}")
                    ->from($smtp->from_email, $smtp->from_name);
            });

            return back()->with('success', 'Test campaign email sent successfully.');
        } catch (\Throwable $e) {
            return back()->withErrors(['campaign_test_email' => "Failed to send test campaign email: {$e->getMessage()}"]);
        }
    }
}
