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
    public function index(Request $request): View
    {
        $user = $request->user();
        $accountId = $user->account_id;

        $dateRange = $request->string('date_range', '7d')->toString();
        $campaignId = $request->integer('campaign_id') ?: null;

        $from = null;
        $to = null;

        if ($dateRange === '30d') {
            $from = now()->startOfDay()->subDays(29);
            $to = now()->endOfDay();
        } elseif ($dateRange === 'custom') {
            $fromInput = $request->input('from');
            $toInput = $request->input('to');

            $from = $fromInput ? Carbon::parse($fromInput)->startOfDay() : now()->startOfDay()->subDays(6);
            $to = $toInput ? Carbon::parse($toInput)->endOfDay() : now()->endOfDay();

            if ($from->gt($to)) {
                [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
            }
        } else {
            $dateRange = '7d';
            $from = now()->startOfDay()->subDays(6);
            $to = now()->endOfDay();
        }

        $sentBase = EmailQueue::query()
            ->where('account_id', $accountId)
            ->whereBetween('created_at', [$from, $to]);

        if ($campaignId) {
            $sentBase->where('campaign_id', $campaignId);
        }

        $sentCount = (clone $sentBase)->count();

        $openBase = EmailOpen::query()
            ->join('email_queue', 'email_queue.id', '=', 'email_opens.email_queue_id')
            ->where('email_queue.account_id', $accountId)
            ->whereBetween('email_opens.created_at', [$from, $to]);

        if ($campaignId) {
            $openBase->where('email_queue.campaign_id', $campaignId);
        }

        $opensCount = (clone $openBase)->distinct('email_opens.email_queue_id')->count('email_opens.email_queue_id');

        $clickBase = EmailClick::query()
            ->join('email_queue', 'email_queue.id', '=', 'email_clicks.email_queue_id')
            ->where('email_queue.account_id', $accountId)
            ->whereBetween('email_clicks.created_at', [$from, $to]);

        if ($campaignId) {
            $clickBase->where('email_queue.campaign_id', $campaignId);
        }

        $clicksCount = (clone $clickBase)->distinct('email_clicks.email_queue_id')->count('email_clicks.email_queue_id');

        $unsubscribeBase = Unsubscribe::query()
            ->where('account_id', $accountId)
            ->whereBetween('created_at', [$from, $to]);

        if ($campaignId) {
            $unsubscribeBase->where('campaign_id', $campaignId);
        }

        $unsubscribesCount = (clone $unsubscribeBase)->count();

        $openRate = $sentCount > 0 ? round(($opensCount / $sentCount) * 100, 2) : 0;
        $clickRate = $sentCount > 0 ? round(($clicksCount / $sentCount) * 100, 2) : 0;

        $days = collect();
        $cursor = $from->copy()->startOfDay();
        while ($cursor->lte($to)) {
            $days->push($cursor->toDateString());
            $cursor->addDay();
        }

        $sentSeriesRows = (clone $sentBase)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $opensSeriesRows = (clone $openBase)
            ->selectRaw('DATE(email_opens.created_at) as day, COUNT(DISTINCT email_opens.email_queue_id) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $clicksSeriesRows = (clone $clickBase)
            ->selectRaw('DATE(email_clicks.created_at) as day, COUNT(DISTINCT email_clicks.email_queue_id) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $chartLabels = $days->all();
        $sentSeries = $days->map(fn ($day) => (int) ($sentSeriesRows[$day] ?? 0))->all();
        $opensSeries = $days->map(fn ($day) => (int) ($opensSeriesRows[$day] ?? 0))->all();
        $clicksSeries = $days->map(fn ($day) => (int) ($clicksSeriesRows[$day] ?? 0))->all();

        $campaignRows = EmailQueue::query()
            ->leftJoin('campaigns', 'campaigns.id', '=', 'email_queue.campaign_id')
            ->leftJoin('email_opens', 'email_opens.email_queue_id', '=', 'email_queue.id')
            ->leftJoin('email_clicks', 'email_clicks.email_queue_id', '=', 'email_queue.id')
            ->where('email_queue.account_id', $accountId)
            ->whereBetween('email_queue.created_at', [$from, $to])
            ->when($campaignId, fn ($q) => $q->where('email_queue.campaign_id', $campaignId))
            ->selectRaw('
                email_queue.campaign_id as campaign_id,
                COALESCE(campaigns.name, "Single / Unnamed") as campaign_name,
                COUNT(DISTINCT email_queue.id) as sent_count,
                COUNT(DISTINCT email_opens.email_queue_id) as open_count,
                COUNT(DISTINCT email_clicks.email_queue_id) as click_count
            ')
            ->groupBy('email_queue.campaign_id', 'campaigns.name')
            ->orderByDesc('sent_count')
            ->limit(50)
            ->get()
            ->map(function ($row) {
                $sent = (int) $row->sent_count;
                $open = (int) $row->open_count;
                $click = (int) $row->click_count;

                $row->open_rate = $sent > 0 ? round(($open / $sent) * 100, 2) : 0;
                $row->click_rate = $sent > 0 ? round(($click / $sent) * 100, 2) : 0;

                return $row;
            });

        $utmSourceRows = EmailClick::query()
            ->join('email_queue', 'email_queue.id', '=', 'email_clicks.email_queue_id')
            ->where('email_queue.account_id', $accountId)
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
            ->where('email_queue.account_id', $accountId)
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
                'date_range' => $dateRange,
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'campaign_id' => $campaignId,
            ],
            'metrics' => [
                'sent' => $sentCount,
                'opens' => $opensCount,
                'clicks' => $clicksCount,
                'unsubscribes' => $unsubscribesCount,
                'open_rate' => $openRate,
                'click_rate' => $clickRate,
            ],
            'chart' => [
                'labels' => $chartLabels,
                'sent' => $sentSeries,
                'opens' => $opensSeries,
                'clicks' => $clicksSeries,
            ],
            'campaignRows' => $campaignRows,
            'utmSourceRows' => $utmSourceRows,
            'utmCampaignRows' => $utmCampaignRows,
            'campaignOptions' => $campaignOptions,
        ]);
    }
}
