<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\EmailClick;
use App\Models\EmailOpen;
use App\Models\EmailQueue;
use App\Models\Unsubscribe;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportsController extends Controller
{
    // ── Shared date range resolver ─────────────────────────────────────────────
    private function resolveDateRange(Request $request, string $defaultRange = '30d'): array
    {
        $dateRange = $request->string('date_range', $defaultRange)->toString();

        switch ($dateRange) {
            case 'today':
                $from = now()->startOfDay();
                $to   = now()->endOfDay();
                break;
            case '7d':
                $from = now()->startOfDay()->subDays(6);
                $to   = now()->endOfDay();
                break;
            case '30d':
                $from = now()->startOfDay()->subDays(29);
                $to   = now()->endOfDay();
                break;
            case 'custom':
                $fromInput = $request->input('from');
                $toInput   = $request->input('to');
                $from = $fromInput ? Carbon::parse($fromInput)->startOfDay() : now()->startOfDay()->subDays(29);
                $to   = $toInput   ? Carbon::parse($toInput)->endOfDay()     : now()->endOfDay();
                if ($from->gt($to)) {
                    [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
                }
                break;
            default:
                $dateRange = '30d';
                $from = now()->startOfDay()->subDays(29);
                $to   = now()->endOfDay();
                break;
        }

        return [$dateRange, $from, $to];
    }

    // ── Campaign Report ────────────────────────────────────────────────────────
    public function index(Request $request): View
    {
        $accountId  = (int) ($request->user()->account_id ?? 0);
        $campaignId = $request->integer('campaign_id') ?: null;

        [$dateRange, $from, $to] = $this->resolveDateRange($request, '30d');

        // Campaign IDs belonging to this account — used to scope email_queue rows
        // even when account_id on email_queue was not populated correctly (legacy rows).
        $accountCampaignIds = Campaign::where('account_id', $accountId)->pluck('id');

        // Sent — scope by campaign ownership (covers rows where account_id is 0/null)
        $sentBase = EmailQueue::query()
            ->whereIn('campaign_id', $accountCampaignIds)
            ->where('status', 'sent')
            ->whereBetween('sent_at', [$from, $to]);

        if ($campaignId) {
            $sentBase->where('campaign_id', $campaignId);
        }

        $sentCount = (clone $sentBase)->count();

        // Opens
        $openBase = EmailOpen::query()
            ->join('email_queue', 'email_queue.id', '=', 'email_opens.email_queue_id')
            ->whereIn('email_queue.campaign_id', $accountCampaignIds)
            ->whereBetween('email_opens.created_at', [$from, $to]);

        if ($campaignId) {
            $openBase->where('email_queue.campaign_id', $campaignId);
        }

        $opensCount = (clone $openBase)->distinct('email_opens.email_queue_id')->count('email_opens.email_queue_id');

        // Clicks
        $clickBase = EmailClick::query()
            ->join('email_queue', 'email_queue.id', '=', 'email_clicks.email_queue_id')
            ->whereIn('email_queue.campaign_id', $accountCampaignIds)
            ->whereBetween('email_clicks.created_at', [$from, $to]);

        if ($campaignId) {
            $clickBase->where('email_queue.campaign_id', $campaignId);
        }

        $clicksCount = (clone $clickBase)->distinct('email_clicks.email_queue_id')->count('email_clicks.email_queue_id');

        // Unsubscribes — scope by emails sent in account campaigns (no campaign_id column on unsubscribes)
        $sentEmailsSubquery = EmailQueue::whereIn('campaign_id', $accountCampaignIds)
            ->when($campaignId, fn ($q) => $q->where('campaign_id', $campaignId))
            ->select('email');

        $unsubscribesCount = Unsubscribe::whereIn('email', $sentEmailsSubquery)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $openRate  = $sentCount > 0 ? round(($opensCount  / $sentCount) * 100, 2) : 0;
        $clickRate = $sentCount > 0 ? round(($clicksCount / $sentCount) * 100, 2) : 0;

        // Chart series (day-by-day)
        $days   = collect();
        $cursor = $from->copy()->startOfDay();
        while ($cursor->lte($to)) {
            $days->push($cursor->toDateString());
            $cursor->addDay();
        }

        $sentSeriesRows = (clone $sentBase)
            ->selectRaw('DATE(sent_at) as day, COUNT(*) as total')
            ->groupBy('day')->orderBy('day')
            ->pluck('total', 'day');

        $opensSeriesRows = (clone $openBase)
            ->selectRaw('DATE(email_opens.created_at) as day, COUNT(DISTINCT email_opens.email_queue_id) as total')
            ->groupBy('day')->orderBy('day')
            ->pluck('total', 'day');

        $clicksSeriesRows = (clone $clickBase)
            ->selectRaw('DATE(email_clicks.created_at) as day, COUNT(DISTINCT email_clicks.email_queue_id) as total')
            ->groupBy('day')->orderBy('day')
            ->pluck('total', 'day');

        $chartLabels  = $days->all();
        $sentSeries   = $days->map(fn ($d) => (int) ($sentSeriesRows[$d]   ?? 0))->all();
        $opensSeries  = $days->map(fn ($d) => (int) ($opensSeriesRows[$d]  ?? 0))->all();
        $clicksSeries = $days->map(fn ($d) => (int) ($clicksSeriesRows[$d] ?? 0))->all();

        // Per-campaign breakdown table
        $campaignRows = EmailQueue::query()
            ->leftJoin('campaigns', 'campaigns.id', '=', 'email_queue.campaign_id')
            ->leftJoin('email_opens', 'email_opens.email_queue_id', '=', 'email_queue.id')
            ->leftJoin('email_clicks', 'email_clicks.email_queue_id', '=', 'email_queue.id')
            ->whereIn('email_queue.campaign_id', $accountCampaignIds)
            ->where('email_queue.status', 'sent')
            ->whereBetween('email_queue.sent_at', [$from, $to])
            ->when($campaignId, fn ($q) => $q->where('email_queue.campaign_id', $campaignId))
            ->selectRaw('
                email_queue.campaign_id,
                COALESCE(campaigns.name, "Single / Unnamed") as campaign_name,
                campaigns.status as campaign_status,
                COUNT(DISTINCT email_queue.id) as sent_count,
                COUNT(DISTINCT email_opens.email_queue_id) as open_count,
                COUNT(DISTINCT email_clicks.email_queue_id) as click_count
            ')
            ->groupBy('email_queue.campaign_id', 'campaigns.name', 'campaigns.status')
            ->orderByDesc('sent_count')
            ->limit(50)
            ->get()
            ->map(function ($row) {
                $sent  = (int) $row->sent_count;
                $open  = (int) $row->open_count;
                $click = (int) $row->click_count;

                $row->open_rate  = $sent > 0 ? round(($open  / $sent) * 100, 2) : 0;
                $row->click_rate = $sent > 0 ? round(($click / $sent) * 100, 2) : 0;

                return $row;
            });

        // UTM tables
        $utmSourceRows = EmailClick::query()
            ->join('email_queue', 'email_queue.id', '=', 'email_clicks.email_queue_id')
            ->whereIn('email_queue.campaign_id', $accountCampaignIds)
            ->whereBetween('email_clicks.created_at', [$from, $to])
            ->when($campaignId, fn ($q) => $q->where('email_queue.campaign_id', $campaignId))
            ->selectRaw('
                COALESCE(NULLIF(email_queue.utm_source, ""), "(none)") as utm_source,
                COALESCE(NULLIF(email_queue.utm_medium, ""), "(none)") as utm_medium,
                COUNT(*) as total_clicks
            ')
            ->groupBy('utm_source', 'utm_medium')
            ->orderByDesc('total_clicks')
            ->limit(10)
            ->get();

        $utmCampaignRows = EmailClick::query()
            ->join('email_queue', 'email_queue.id', '=', 'email_clicks.email_queue_id')
            ->whereIn('email_queue.campaign_id', $accountCampaignIds)
            ->whereBetween('email_clicks.created_at', [$from, $to])
            ->when($campaignId, fn ($q) => $q->where('email_queue.campaign_id', $campaignId))
            ->selectRaw('
                COALESCE(NULLIF(email_queue.utm_campaign, ""), "(none)") as utm_campaign,
                COUNT(*) as total_clicks
            ')
            ->groupBy('utm_campaign')
            ->orderByDesc('total_clicks')
            ->limit(10)
            ->get();

        $campaignOptions = Campaign::query()
            ->where('account_id', $accountId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('reports.index', [
            'filters' => [
                'date_range'  => $dateRange,
                'from'        => $from->toDateString(),
                'to'          => $to->toDateString(),
                'campaign_id' => $campaignId,
            ],
            'metrics' => [
                'sent'         => $sentCount,
                'opens'        => $opensCount,
                'clicks'       => $clicksCount,
                'unsubscribes' => $unsubscribesCount,
                'open_rate'    => $openRate,
                'click_rate'   => $clickRate,
            ],
            'chart' => [
                'labels' => $chartLabels,
                'sent'   => $sentSeries,
                'opens'  => $opensSeries,
                'clicks' => $clicksSeries,
            ],
            'campaignRows'    => $campaignRows,
            'utmSourceRows'   => $utmSourceRows,
            'utmCampaignRows' => $utmCampaignRows,
            'campaignOptions' => $campaignOptions,
        ]);
    }

    // ── Campaign Detail ────────────────────────────────────────────────────────
    public function campaignDetail(Request $request, int $campaignId): View
    {
        $accountId = (int) ($request->user()->account_id ?? 0);

        $campaign = Campaign::where('account_id', $accountId)->findOrFail($campaignId);

        $totalSent   = EmailQueue::where('campaign_id', $campaignId)->where('status', 'sent')->count();
        $totalFailed = EmailQueue::where('campaign_id', $campaignId)->where('status', 'failed')->count();
        $totalQueued = EmailQueue::where('campaign_id', $campaignId)->whereIn('status', ['queued', 'pending'])->count();

        $totalOpens = EmailOpen::query()
            ->join('email_queue', 'email_queue.id', '=', 'email_opens.email_queue_id')
            ->where('email_queue.campaign_id', $campaignId)
            ->distinct('email_opens.email_queue_id')
            ->count('email_opens.email_queue_id');

        $totalClicks = EmailClick::query()
            ->join('email_queue', 'email_queue.id', '=', 'email_clicks.email_queue_id')
            ->where('email_queue.campaign_id', $campaignId)
            ->distinct('email_clicks.email_queue_id')
            ->count('email_clicks.email_queue_id');

        // Count unsubscribes by matching emails that were sent in this campaign
        $campaignEmails = EmailQueue::where('campaign_id', $campaignId)->pluck('email');
        $totalUnsubs = Unsubscribe::whereIn('email', $campaignEmails)->count();

        $openRate  = $totalSent > 0 ? round(($totalOpens  / $totalSent) * 100, 2) : 0;
        $clickRate = $totalSent > 0 ? round(($totalClicks / $totalSent) * 100, 2) : 0;
        $unsubRate = $totalSent > 0 ? round(($totalUnsubs / $totalSent) * 100, 2) : 0;

        // Per-recipient delivery + engagement status
        $recipients = EmailQueue::query()
            ->where('campaign_id', $campaignId)
            ->where('status', 'sent')
            ->leftJoin('email_opens', 'email_opens.email_queue_id', '=', 'email_queue.id')
            ->leftJoin('email_clicks', 'email_clicks.email_queue_id', '=', 'email_queue.id')
            ->leftJoin('unsubscribes', 'unsubscribes.email', '=', 'email_queue.email')
            ->selectRaw('
                email_queue.id,
                email_queue.email,
                email_queue.sent_at,
                MAX(email_opens.id)    as opened_id,
                MAX(email_clicks.id)   as clicked_id,
                MAX(unsubscribes.id)   as unsub_id
            ')
            ->groupBy('email_queue.id', 'email_queue.email', 'email_queue.sent_at')
            ->orderByDesc('email_queue.sent_at')
            ->paginate(25)
            ->withQueryString();

        // Failed deliveries
        $failedEmails = EmailQueue::query()
            ->where('campaign_id', $campaignId)
            ->where('status', 'failed')
            ->select('id', 'email', 'last_error', 'updated_at')
            ->orderByDesc('updated_at')
            ->limit(100)
            ->get();

        return view('reports.campaign-detail', compact(
            'campaign',
            'totalSent', 'totalFailed', 'totalQueued',
            'totalOpens', 'totalClicks', 'totalUnsubs',
            'openRate', 'clickRate', 'unsubRate',
            'recipients', 'failedEmails'
        ));
    }

    // ── Single Email Report ────────────────────────────────────────────────────
    public function singleEmailReport(Request $request): View
    {
        $accountId = (int) ($request->user()->account_id ?? 0);

        [$dateRange, $from, $to] = $this->resolveDateRange($request, '30d');

        $emailLogs = EmailQueue::query()
            ->where('email_queue.account_id', $accountId)
            ->where('email_queue.status', 'sent')
            ->where('email_queue.type', 'single')
            ->whereBetween(\Illuminate\Support\Facades\DB::raw('COALESCE(email_queue.sent_at, email_queue.created_at)'), [$from, $to])
            ->select([
                'email_queue.id',
                'email_queue.created_at',
                'email_queue.sent_at',
                'email_queue.type',
                'email_queue.email',
                'email_queue.subject',
                'email_queue.from_name',
                'email_queue.from_email',
                'email_queue.status',
                'email_queue.body_snapshot',
            ])
            ->orderByDesc(\Illuminate\Support\Facades\DB::raw('COALESCE(email_queue.sent_at, email_queue.created_at)'))
            ->orderByDesc('email_queue.id')
            ->paginate(20)
            ->withQueryString();

        return view('reports.single-email', [
            'filters' => [
                'date_range' => $dateRange,
                'from'       => $from->toDateString(),
                'to'         => $to->toDateString(),
            ],
            'emailLogs' => $emailLogs,
        ]);
    }

    // ── Email Preview (JSON) ───────────────────────────────────────────────────
    public function showEmail(Request $request, int $id)
    {
        $accountId = (int) ($request->user()?->account_id ?? 0);

        $emailLog = EmailQueue::query()
            ->where('account_id', $accountId)
            ->findOrFail($id);

        return response()->json([
            'id'            => $emailLog->id,
            'subject'       => $emailLog->subject,
            'to'            => $emailLog->email,
            'from_name'     => $emailLog->from_name,
            'from_email'    => $emailLog->from_email,
            'type'          => $emailLog->type,
            'status'        => $emailLog->status,
            'sent_at'       => optional($emailLog->sent_at)->toDateTimeString(),
            'created_at'    => optional($emailLog->created_at)->toDateTimeString(),
            'body_snapshot' => $emailLog->body_snapshot ?: $emailLog->body ?: '',
        ]);
    }
}
