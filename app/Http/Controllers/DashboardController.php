<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\EmailClick;
use App\Models\EmailOpen;
use App\Models\EmailQueue;
use App\Models\Group;
use App\Models\SmtpServer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $accountId = (int) ($request->user()?->account_id ?? 0);
        abort_if($accountId <= 0, 403, 'Account context is missing.');

        $now = now();
        $monthStart = $now->copy()->startOfMonth();
        $weekAgo    = $now->copy()->subDays(6)->startOfDay();

        // ── Core counts ───────────────────────────────────────────────────────
        $totalContacts   = Contact::where('account_id', $accountId)->count();
        $totalGroups     = Group::where('account_id', $accountId)->count();
        $totalSmtp       = SmtpServer::where('account_id', $accountId)->active()->count();

        $emailsSentTotal = EmailQueue::where('account_id', $accountId)->where('status', 'sent')->count();
        $emailsSentMonth = EmailQueue::where('account_id', $accountId)
            ->where('status', 'sent')
            ->where('sent_at', '>=', $monthStart)
            ->count();
        $emailsFailed    = EmailQueue::where('account_id', $accountId)->where('status', 'failed')->count();
        $emailsPending   = EmailQueue::where('account_id', $accountId)->whereIn('status', ['pending', 'queued'])->count();

        $campaignsSent  = Campaign::where('account_id', $accountId)
            ->whereIn('status', ['sent', 'completed', 'sending'])
            ->count();
        $campaignsDraft = Campaign::where('account_id', $accountId)
            ->whereIn('status', ['draft', 'scheduled'])
            ->count();

        // ── Open & click rates ────────────────────────────────────────────────
        $totalOpens  = EmailOpen::whereHas('emailQueue', fn ($q) => $q->where('account_id', $accountId))->count();
        $totalClicks = EmailClick::whereHas('emailQueue', fn ($q) => $q->where('account_id', $accountId))->count();

        $openRate  = $emailsSentTotal > 0 ? round(($totalOpens  / $emailsSentTotal) * 100, 1) : 0;
        $clickRate = $emailsSentTotal > 0 ? round(($totalClicks / $emailsSentTotal) * 100, 1) : 0;

        // ── 7-day sent chart ──────────────────────────────────────────────────
        $sentByDateRaw = EmailQueue::where('account_id', $accountId)
            ->where('status', 'sent')
            ->whereNotNull('sent_at')
            ->where('sent_at', '>=', $weekAgo)
            ->selectRaw('DATE(sent_at) as sent_date, COUNT(*) as total')
            ->groupBy('sent_date')
            ->pluck('total', 'sent_date');

        $sentChartLabels = [];
        $sentChartValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i);
            $sentChartLabels[] = $day->format('D d');
            $sentChartValues[] = (int) ($sentByDateRaw[$day->toDateString()] ?? 0);
        }

        // ── Opens vs Clicks chart (top 5 campaigns) ───────────────────────────
        $topCampaigns     = Campaign::where('account_id', $accountId)->latest()->take(5)->get(['id', 'name']);
        $openClickLabels  = [];
        $openClickOpens   = [];
        $openClickClicks  = [];

        foreach ($topCampaigns as $c) {
            $queueIds = EmailQueue::where('campaign_id', $c->id)->pluck('id');
            $openClickLabels[]  = \Illuminate\Support\Str::limit($c->name, 20);
            $openClickOpens[]   = EmailOpen::whereIn('email_queue_id', $queueIds)->count();
            $openClickClicks[]  = EmailClick::whereIn('email_queue_id', $queueIds)->count();
        }

        // ── Recent campaigns ──────────────────────────────────────────────────
        $recentCampaigns = Campaign::where('account_id', $accountId)
            ->latest()
            ->take(6)
            ->get(['id', 'name', 'subject', 'status', 'created_at']);

        // ── Recent failed emails ──────────────────────────────────────────────
        $recentFailed = EmailQueue::where('account_id', $accountId)
            ->where('status', 'failed')
            ->latest()
            ->take(5)
            ->get(['id', 'email', 'subject', 'last_error', 'updated_at']);

        // ── Campaign status donut ─────────────────────────────────────────────
        $campaignStatusCounts = Campaign::where('account_id', $accountId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $donutLabels = [];
        $donutValues = [];
        $donutColors = [];
        $statusColorMap = [
            'draft'     => '#94a3b8',
            'scheduled' => '#f59e0b',
            'sending'   => '#3b82f6',
            'completed' => '#10b981',
            'sent'      => '#10b981',
            'paused'    => '#f97316',
            'failed'    => '#ef4444',
        ];
        foreach ($campaignStatusCounts as $status => $count) {
            $donutLabels[] = ucfirst($status);
            $donutValues[] = $count;
            $donutColors[] = $statusColorMap[$status] ?? '#6b7280';
        }

        // ── 30-day emails sent trend ──────────────────────────────────────────
        $thirtyDaysAgo = $now->copy()->subDays(29)->startOfDay();
        $sentBy30DayRaw = EmailQueue::where('account_id', $accountId)
            ->where('status', 'sent')
            ->whereNotNull('sent_at')
            ->where('sent_at', '>=', $thirtyDaysAgo)
            ->selectRaw('DATE(sent_at) as sent_date, COUNT(*) as total')
            ->groupBy('sent_date')
            ->pluck('total', 'sent_date');

        $trend30Labels = [];
        $trend30Values = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i);
            $trend30Labels[] = $day->format('d M');
            $trend30Values[] = (int) ($sentBy30DayRaw[$day->toDateString()] ?? 0);
        }

        // ── Top 5 contacts by opens ───────────────────────────────────────────
        $topContactOpens = DB::table('email_opens')
            ->join('email_queue', 'email_queue.id', '=', 'email_opens.email_queue_id')
            ->join('contacts', 'contacts.id', '=', 'email_queue.contact_id')
            ->where('email_queue.account_id', $accountId)
            ->whereNotNull('email_queue.contact_id')
            ->selectRaw('contacts.name, contacts.email as contact_email, COUNT(email_opens.id) as opens')
            ->groupBy('contacts.id', 'contacts.name', 'contacts.email')
            ->orderByDesc('opens')
            ->limit(5)
            ->get();

        $topContactLabels = $topContactOpens->map(fn($c) => \Illuminate\Support\Str::limit($c->name ?: $c->contact_email, 18))->values()->toArray();
        $topContactValues = $topContactOpens->pluck('opens')->values()->toArray();

        return view('dashboard', compact(
            'totalContacts', 'totalGroups', 'totalSmtp',
            'emailsSentTotal', 'emailsSentMonth', 'emailsFailed', 'emailsPending',
            'campaignsSent', 'campaignsDraft',
            'openRate', 'clickRate',
            'sentChartLabels', 'sentChartValues',
            'openClickLabels', 'openClickOpens', 'openClickClicks',
            'donutLabels', 'donutValues', 'donutColors',
            'trend30Labels', 'trend30Values',
            'topContactLabels', 'topContactValues',
            'recentCampaigns', 'recentFailed'
        ));
    }
}
