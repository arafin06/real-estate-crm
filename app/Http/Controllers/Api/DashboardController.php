<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\DealActivity;
use App\Models\Property;
use App\Models\Task;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $isAdmin = auth()->user()->isSuperAdmin();

        $scope = fn ($class) => $class::query()
            ->when(! $isAdmin, fn ($q) => $q->where('user_id', $userId));

        // ── Summary ───────────────────────────────────────
        $activeListings = $scope(Property::class)
            ->where('status', 'active')->count();

        $openDeals = $scope(Deal::class)
            ->whereNotIn('stage', ['closed', 'lost'])->count();

        $pipelineValue = (float) $scope(Deal::class)
            ->whereNotIn('stage', ['closed', 'lost'])->sum('deal_value');

        $commissionYtd = (float) $scope(Deal::class)
            ->where('stage', 'closed')
            ->whereYear('closed_at', now()->year)
            ->sum('commission_amount');

        $totalDeals = $scope(Deal::class)->count();
        $closedDeals = $scope(Deal::class)->where('stage', 'closed')->count();

        $conversionRate = $totalDeals > 0
            ? round(($closedDeals / $totalDeals) * 100, 1) : 0;

        $overdueTasksCount = $scope(Task::class)
            ->whereNull('parent_id')
            ->whereDate('due_date', '<', now()->toDateString())
            ->whereIn('status', ['incomplete', 'in_progress'])
            ->count();

        // ── Pipeline by stage ─────────────────────────────
        $pipelineRaw = $scope(Deal::class)
            ->select(
                'stage',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(deal_value) as total_value'),
                DB::raw('SUM(commission_amount) as total_commission')
            )
            ->groupBy('stage')
            ->get()
            ->keyBy('stage');

        $stages = ['lead', 'prospect', 'showing', 'offer_made', 'under_contract', 'closed', 'lost'];
        $pipeline = collect($stages)->map(fn ($s) => [
            'stage' => $s,
            'count' => (int) ($pipelineRaw[$s]->count ?? 0),
            'total_value' => (float) ($pipelineRaw[$s]->total_value ?? 0),
            'total_commission' => (float) ($pipelineRaw[$s]->total_commission ?? 0),
        ]);

        // ── Monthly closed — last 6 months ────────────────
        $monthlyRaw = $scope(Deal::class)
            ->where('stage', 'closed')
            ->where('closed_at', '>=', now()->subMonths(5)->startOfMonth())
            ->select(
                DB::raw('YEAR(closed_at) as year'),
                DB::raw('MONTH(closed_at) as month'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(deal_value) as total_value'),
                DB::raw('SUM(commission_amount) as total_commission')
            )
            ->groupBy('year', 'month')
            ->get();

        $monthly = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $found = $monthlyRaw->first(
                fn ($m) => $m->year == $date->year && $m->month == $date->month
            );
            $monthly->push([
                'label' => $date->format('M Y'),
                'count' => (int) ($found?->count ?? 0),
                'total_value' => (float) ($found?->total_value ?? 0),
                'total_commission' => (float) ($found?->total_commission ?? 0),
            ]);
        }

        // ── Tasks due today ───────────────────────────────
        $tasksDueToday = $scope(Task::class)
            ->with(['client:id,name', 'deal:id,title'])
            ->whereNull('parent_id')
            ->whereDate('due_date', now()->toDateString())
            ->whereIn('status', ['incomplete', 'in_progress'])
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
            ->limit(5)
            ->get();

        // ── Recent activity ───────────────────────────────
        $recentActivity = DealActivity::with(['deal:id,title', 'user:id,name'])
            ->when(! $isAdmin, fn ($q) => $q->whereHas('deal', fn ($q) => $q->where('user_id', $userId))
            )
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // ── Top clients ───────────────────────────────────
        $topClients = $scope(Deal::class)
            ->select(
                'client_id',
                DB::raw('COUNT(*) as deal_count'),
                DB::raw('SUM(deal_value) as total_value'),
                DB::raw('SUM(commission_amount) as total_commission')
            )
            ->with('client:id,name')
            ->whereNotNull('client_id')
            ->groupBy('client_id')
            ->orderByDesc('total_value')
            ->limit(5)
            ->get();

        return response()->json([
            'summary' => [
                'active_listings' => $activeListings,
                'open_deals' => $openDeals,
                'pipeline_value' => $pipelineValue,
                'commission_ytd' => $commissionYtd,
                'conversion_rate' => $conversionRate,
                'overdue_tasks' => $overdueTasksCount,
                'closed_deals' => $closedDeals,
                'total_deals' => $totalDeals,
            ],
            'pipeline' => $pipeline,
            'monthly' => $monthly,
            'tasks_due_today' => $tasksDueToday,
            'recent_activity' => $recentActivity,
            'top_clients' => $topClients,
        ]);
    }
}
