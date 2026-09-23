<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\PageView;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AnalyticsController extends Controller
{
    private const PERIODS = [
        'today' => 1,
        '7d' => 7,
        '30d' => 30,
        '90d' => 90,
        '365d' => 365,
    ];

    public function index(Request $request)
    {
        $period = $request->input('period', '30d');
        $days = self::PERIODS[$period] ?? self::PERIODS['30d'];

        $to = Carbon::now()->endOfDay();
        $from = Carbon::now()->subDays($days - 1)->startOfDay();

        $prevTo = $from->copy()->subSecond();
        $prevFrom = $prevTo->copy()->subDays($days - 1)->startOfDay();

        $series = $this->buildSeries($from, $to);

        return response()->json([
            'data' => [
                'period' => $period,
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'summary' => [
                    'visits' => $this->summaryMetric(
                        PageView::whereBetween('viewed_at', [$from, $to])->count(),
                        PageView::whereBetween('viewed_at', [$prevFrom, $prevTo])->count(),
                    ),
                    'unique_visitors' => $this->summaryMetric(
                        PageView::whereBetween('viewed_at', [$from, $to])->distinct('visitor_hash')->count('visitor_hash'),
                        PageView::whereBetween('viewed_at', [$prevFrom, $prevTo])->distinct('visitor_hash')->count('visitor_hash'),
                    ),
                    'new_accounts' => $this->summaryMetric(
                        User::whereBetween('created_at', [$from, $to])->count(),
                        User::whereBetween('created_at', [$prevFrom, $prevTo])->count(),
                    ),
                    'new_auctions' => $this->summaryMetric(
                        Auction::whereBetween('created_at', [$from, $to])->count(),
                        Auction::whereBetween('created_at', [$prevFrom, $prevTo])->count(),
                    ),
                    'bids' => $this->summaryMetric(
                        Bid::whereBetween('created_at', [$from, $to])->count(),
                        Bid::whereBetween('created_at', [$prevFrom, $prevTo])->count(),
                    ),
                ],
                'series' => $series,
                'top_pages' => PageView::whereBetween('viewed_at', [$from, $to])
                    ->selectRaw('path, COUNT(*) as views')
                    ->groupBy('path')
                    ->orderByDesc('views')
                    ->limit(10)
                    ->get(),
            ],
        ]);
    }

    private function summaryMetric(int $current, int $previous): array
    {
        $changePct = $previous > 0
            ? round((($current - $previous) / $previous) * 100, 1)
            : ($current > 0 ? 100.0 : 0.0);

        return [
            'value' => $current,
            'change_pct' => $changePct,
        ];
    }

    private function buildSeries(Carbon $from, Carbon $to): array
    {
        $visitsByDate = PageView::whereBetween('viewed_at', [$from, $to])
            ->selectRaw('DATE(viewed_at) as date, COUNT(*) as visits, COUNT(DISTINCT visitor_hash) as unique_visitors')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $accountsByDate = User::whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $series = [];
        $cursor = $from->copy();
        while ($cursor->lte($to)) {
            $date = $cursor->toDateString();
            $series[] = [
                'date' => $date,
                'visits' => (int) ($visitsByDate[$date]->visits ?? 0),
                'unique_visitors' => (int) ($visitsByDate[$date]->unique_visitors ?? 0),
                'new_accounts' => (int) ($accountsByDate[$date]->count ?? 0),
            ];
            $cursor->addDay();
        }

        return $series;
    }

    /**
     * Export analytics
     */
    public function export()
    {
        $data = [
            'users' => [
                'total' => User::count(),
                'today' => User::whereDate('created_at', now()->today())->count(),
                'banned' => User::where('is_banned', true)->count(),
                'premium' => User::where('premium_plan', '!=', 'free')->count(),
            ],
            'auctions' => [
                'total' => Auction::count(),
                'active' => Auction::where('status', 'active')->count(),
                'ended' => Auction::where('status', 'ended')->count(),
                'total_bids' => Bid::count(),
            ],
            'exported_at' => now(),
        ];

        return response()->json($data);
    }
}
