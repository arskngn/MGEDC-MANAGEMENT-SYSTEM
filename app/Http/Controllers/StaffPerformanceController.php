<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StaffTarget;
use App\Models\OverallQuota;
use App\Models\CustomerRoute;
use App\Models\GeneralSetting;
use App\Services\PdfService;
use App\Services\StaffPerformanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffPerformanceController extends Controller
{
    public function __construct(private StaffPerformanceService $performanceService)
    {
    }

    /**
     * Display the supervisor dashboard with overall quota performance.
     */
    public function dashboard(Request $request)
    {
        $quotaId = $request->filled('quota_id') ? (int) $request->get('quota_id') : null;
        $performance = $this->performanceService->getDashboardPerformance($quotaId);

        $products = \App\Models\Product::orderBy('name')->get(['id', 'name', 'sku']);
        $storeCategories = \App\Models\StoreCategory::orderBy('name')->get();

        return view('staff.performance.dashboard', [
            'staffPerformance' => $performance['staff'],
            'topPerformers' => $performance['topPerformers'],
            'overallQuota' => $performance['overallQuota'],
            'allQuotas' => $performance['allQuotas'],
            'targetAmount' => $performance['targetAmount'],
            'totalSales' => $performance['totalSales'],
            'isAchieved' => $performance['isAchieved'],
            'progress' => $performance['progress'],
            'remainingAmount' => $performance['remainingAmount'],
            'commissionPerStaff' => $performance['commissionPerStaff'],
            'totalCommissionPool' => $performance['totalCommissionPool'],
            'startDate' => $performance['startDate'],
            'endDate' => $performance['endDate'],
            'quotaId' => $performance['quotaId'],
            'averageProgress' => $performance['average'],
            'totalTarget' => $performance['total_target'],
            'routes' => $performance['routes'],
            'products' => $products,
            'storeCategories' => $storeCategories,
        ]);
    }

    /**
     * Display individual staff performance aligned with overall team quota.
     */
    public function show(Request $request, User $user)
    {
        $salesYear = $request->get('sales_year', now()->year);
        $quotaId = $request->filled('quota_id') ? (int) $request->get('quota_id') : null;
        
        $dashboard = $this->performanceService->getDashboardPerformance($quotaId);
        $startDate = $dashboard['startDate'];
        $endDate = $dashboard['endDate'];

        // Find this user's entry in the dashboard
        $userPerf = $dashboard['staff']->firstWhere('user.id', $user->id);
        $teamRank = $dashboard['staff']->search(fn ($p) => $p->user->id === $user->id);
        $teamRank = $teamRank === false ? $dashboard['staff']->count() : $teamRank + 1;

        if (!$userPerf) {
            $actualSales = $this->performanceService->calculateActualSales($user, $startDate, $endDate);
            $totalTeamSales = $dashboard['totalSales'];
            $contributionPct = $totalTeamSales > 0 ? round(($actualSales / $totalTeamSales) * 100, 1) : 0.0;
            $invoicesCount = \App\Models\Sale::notCancelled()
                ->whereBetween('sale_date', [$startDate, $endDate])
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhere('salesperson_id', $user->id);
                })->count();

            $userPerf = (object) [
                'user' => $user,
                'actual_sales' => $actualSales,
                'invoices_count' => $invoicesCount,
                'contribution_pct' => $contributionPct,
                'quota_share_pct' => $dashboard['targetAmount'] > 0 ? round(($actualSales / $dashboard['targetAmount']) * 100, 1) : 0.0,
                'commission_earned' => $dashboard['isAchieved'] ? $dashboard['commissionPerStaff'] : 0.0,
                'commission_status' => $dashboard['isAchieved'] ? 'Awarded' : 'Pending Quota',
                'progress' => $contributionPct,
            ];
        }

        $salesHistory = $this->performanceService->getMonthlySalesHistory($user, $salesYear);

        // Recent sales by this user in the quota period
        $recentSales = \App\Models\Sale::notCancelled()
            ->with(['customer', 'customer.route'])
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('salesperson_id', $user->id);
            })
            ->orderBy('sale_date', 'desc')
            ->limit(15)
            ->get();

        // Available years for history filter
        $createdYear = $user->created_at ? $user->created_at->year : now()->year;
        $currentYear = now()->year;
        $availableYears = range(min($createdYear, $currentYear), $currentYear);

        return view('staff.performance.show', [
            'user' => $user,
            'userPerf' => $userPerf,
            'overallQuota' => $dashboard['overallQuota'],
            'allQuotas' => $dashboard['allQuotas'],
            'targetAmount' => $dashboard['targetAmount'],
            'totalSales' => $dashboard['totalSales'],
            'isAchieved' => $dashboard['isAchieved'],
            'overallProgress' => $dashboard['progress'],
            'remainingAmount' => $dashboard['remainingAmount'],
            'commissionPerStaff' => $dashboard['commissionPerStaff'],
            'startDate' => $startDate,
            'endDate' => $endDate,
            'quotaId' => $dashboard['quotaId'],
            'teamRank' => $teamRank,
            'totalStaff' => $dashboard['staff']->count(),
            'salesHistory' => $salesHistory,
            'recentSales' => $recentSales,
            'availableYears' => $availableYears,
            'salesYear' => $salesYear,
        ]);
    }

    /**
     * Build the shared quota data payload from validated input.
     */
    private function buildQuotaData(array $validated): array
    {
        $routeId   = !empty($validated['route_id']) ? (int) $validated['route_id'] : null;
        $categoryIds = !empty($validated['store_category_ids'])
            ? array_values(array_map('intval', $validated['store_category_ids']))
            : null;

        return [
            'user_id'                     => $validated['user_id'],
            'route_id'                    => $routeId,
            'store_category_ids'          => $categoryIds,
            'target_amount'               => $validated['target_amount'],
            'target_type'                 => $validated['target_type'],
            'start_date'                  => \Carbon\Carbon::parse($validated['start_date'])->toDateString(),
            'end_date'                    => \Carbon\Carbon::parse($validated['end_date'])->toDateString(),
            'max_commission'              => $validated['max_commission'] ?? 10000.00,
            'kpi_sales_peso_weight'       => $validated['kpi_sales_peso_weight'] ?? 5000.00,
            'kpi_sales_peso_target_pct'   => $validated['kpi_sales_peso_target_pct'] ?? 100.00,
            'kpi_distribution_weight'     => $validated['kpi_distribution_weight'] ?? 3000.00,
            'kpi_distribution_target_pct' => $validated['kpi_distribution_target_pct'] ?? 80.00,
            'distribution_product_id'     => !empty($validated['distribution_product_id']) ? (int)$validated['distribution_product_id'] : null,
            'kpi_coverage_weight'         => $validated['kpi_coverage_weight'] ?? 2000.00,
            'kpi_coverage_target_pct'     => $validated['kpi_coverage_target_pct'] ?? 90.00,
            'coverage_days_window'        => $validated['coverage_days_window'] ?? 30,
        ];
    }

    /**
     * Check if a date range overlaps with existing quotas.
     * If $excludeTargetId is supplied, that target is ignored (used during edits).
     */
    private function hasDateOverlap(int $userId, string $targetType, ?int $routeId, string $startDate, string $endDate, ?int $excludeTargetId = null): bool
    {
        $query = StaffTarget::where('user_id', $userId)
            ->where('target_type', $targetType)
            ->where('route_id', $routeId)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($q2) use ($startDate, $endDate) {
                      $q2->where('start_date', '<=', $startDate)
                         ->where('end_date', '>=', $endDate);
                  });
            });

        if ($excludeTargetId) {
            $query->where('id', '!=', $excludeTargetId);
        }

        return $query->exists();
    }

    /**
     * Shared validation rules for quota create / update.
     */
    private function quotaValidationRules(): array
    {
        return [
            'user_id'                      => 'required|exists:users,id',
            'target_id'                    => 'nullable|exists:staff_targets,id',
            'route_id'                     => 'nullable|exists:customer_routes,id',
            'store_category_ids'           => 'nullable|array',
            'store_category_ids.*'         => 'exists:store_categories,id',
            'target_amount'                => 'required|numeric|min:0',
            'target_type'                  => 'required|in:monthly',
            'start_date'                   => 'required|date',
            'end_date'                     => 'required|date|after_or_equal:start_date',
            'max_commission'               => 'nullable|numeric|min:0',
            'kpi_sales_peso_weight'        => 'nullable|numeric|min:0',
            'kpi_sales_peso_target_pct'    => 'nullable|numeric|min:0|max:100',
            'kpi_distribution_weight'      => 'nullable|numeric|min:0',
            'kpi_distribution_target_pct'  => 'nullable|numeric|min:0|max:100',
            'distribution_product_id'      => 'nullable|exists:products,id',
            'kpi_coverage_weight'          => 'nullable|numeric|min:0',
            'kpi_coverage_target_pct'      => 'nullable|numeric|min:0|max:100',
            'coverage_days_window'         => 'nullable|integer|min:1|max:365',
        ];
    }

    /**
     * Assign a route-based quota to a staff member.
     * Multiple quotas per month are allowed as long as date ranges do not overlap.
     */
    public function assignTarget(Request $request)
    {
        $validated = $request->validate($this->quotaValidationRules());

        $data = $this->buildQuotaData($validated);
        $overlap = $this->hasDateOverlap(
            $data['user_id'],
            $data['target_type'],
            $data['route_id'],
            $data['start_date'],
            $data['end_date'],
        );

        if ($overlap) {
            return back()
                ->withInput()
                ->withErrors(['start_date' => 'The selected date range overlaps with an existing quota for this staff member and route. Please choose different dates.']);
        }

        StaffTarget::create($data);

        $user = User::findOrFail($data['user_id']);
        $routeName = $data['route_id'] ? (CustomerRoute::find($data['route_id'])?->display_name ?? 'selected route') : 'All Routes';

        return back()->with('success', "Quota of " . formatCurrency($data['target_amount']) . " ({$data['start_date']} – {$data['end_date']}) on {$routeName} saved for {$user->name}.");
    }

    /**
     * Update an existing staff quota (full edit support).
     */
    public function updateTarget(Request $request)
    {
        $validated = $request->validate($this->quotaValidationRules());

        if (empty($validated['target_id'])) {
            return back()->withInput()->withErrors(['target_id' => 'No quota selected for update.']);
        }

        $target = StaffTarget::findOrFail($validated['target_id']);
        $data = $this->buildQuotaData($validated);

        // Overlap check MUST exclude the target being edited (otherwise changing
        // dates even slightly would trigger a false overlap with itself).
        $overlap = $this->hasDateOverlap(
            $data['user_id'],
            $data['target_type'],
            $data['route_id'],
            $data['start_date'],
            $data['end_date'],
            (int) $target->id,
        );

        if ($overlap) {
            return back()
                ->withInput()
                ->withErrors(['start_date' => 'The updated date range overlaps with another quota for this staff member and route.']);
        }

        $target->update($data);

        $user = User::findOrFail($data['user_id']);
        $routeName = $data['route_id'] ? (CustomerRoute::find($data['route_id'])?->display_name ?? 'selected route') : 'All Routes';

        return back()->with('success', "Quota updated to " . formatCurrency($data['target_amount']) . " ({$data['start_date']} – {$data['end_date']}) on {$routeName} for {$user->name}.");
    }

    /**
     * Delete a specific staff quota.
     */
    public function deleteTarget(StaffTarget $target)
    {
        $target->delete();
        return back()->with('success', 'Quota has been removed.');
    }

    /**
     * Return already-used date ranges for a given user+route combination (for AJAX).
     */
    public function usedDateRanges(Request $request)
    {
        $userId  = $request->query('user_id');
        $routeId = $request->query('route_id') ?: null;

        if (!$userId) {
            return response()->json([]);
        }

        $ranges = StaffTarget::where('user_id', $userId)
            ->where('route_id', $routeId)
            ->where('target_type', 'monthly')
            ->get(['id', 'start_date', 'end_date'])
            ->map(fn ($t) => [
                'id'    => $t->id,
                'start' => $t->start_date->toDateString(),
                'end'   => $t->end_date->toDateString(),
            ]);

        return response()->json($ranges);
    }

    /**
     * Store a new overall team quota.
     */
    public function storeOverallQuota(Request $request)
    {
        OverallQuota::ensureTableExists();

        $validated = $request->validate([
            'target_amount'        => 'required|numeric|min:0',
            'start_date'           => 'required|date',
            'end_date'             => 'required|date|after_or_equal:start_date',
            'commission_per_staff' => 'nullable|numeric|min:0',
            'name'                 => 'nullable|string|max:255',
            'notes'                => 'nullable|string|max:1000',
        ]);

        // Check for date range overlaps with existing team quotas
        $overlap = OverallQuota::where(function ($q) use ($validated) {
            $q->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
              ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
              ->orWhere(function ($q2) use ($validated) {
                  $q2->where('start_date', '<=', $validated['start_date'])
                     ->where('end_date', '>=', $validated['end_date']);
              });
        })->exists();

        if ($overlap) {
            return back()
                ->withInput()
                ->withErrors(['start_date' => 'The selected date range overlaps with an existing team quota. Please choose a non-overlapping date range.']);
        }

        $quota = OverallQuota::create([
            'name'                 => $validated['name'] ?? null,
            'target_amount'        => $validated['target_amount'],
            'start_date'           => $validated['start_date'],
            'end_date'             => $validated['end_date'],
            'commission_per_staff' => $validated['commission_per_staff'] ?? 5000.00,
            'notes'                => $validated['notes'] ?? null,
            'created_by'           => auth()->id(),
        ]);

        return redirect()
            ->route('staff.performance.dashboard', ['quota_id' => $quota->id])
            ->with('success', 'Overall quota of ' . formatCurrency($quota->target_amount) . ' saved successfully.');
    }

    /**
     * Update an existing overall team quota.
     */
    public function updateOverallQuota(Request $request, OverallQuota $overallQuota)
    {
        $validated = $request->validate([
            'target_amount'        => 'required|numeric|min:0',
            'start_date'           => 'required|date',
            'end_date'             => 'required|date|after_or_equal:start_date',
            'commission_per_staff' => 'nullable|numeric|min:0',
            'name'                 => 'nullable|string|max:255',
            'notes'                => 'nullable|string|max:1000',
        ]);

        // Check for date range overlaps excluding current quota
        $overlap = OverallQuota::where('id', '!=', $overallQuota->id)
            ->where(function ($q) use ($validated) {
                $q->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                  ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                  ->orWhere(function ($q2) use ($validated) {
                      $q2->where('start_date', '<=', $validated['start_date'])
                         ->where('end_date', '>=', $validated['end_date']);
                  });
            })->exists();

        if ($overlap) {
            return back()
                ->withInput()
                ->withErrors(['start_date' => 'The selected date range overlaps with another existing team quota. Please choose a non-overlapping date range.']);
        }

        $overallQuota->update([
            'name'                 => $validated['name'] ?? $overallQuota->name,
            'target_amount'        => $validated['target_amount'],
            'start_date'           => $validated['start_date'],
            'end_date'             => $validated['end_date'],
            'commission_per_staff' => $validated['commission_per_staff'] ?? 5000.00,
            'notes'                => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('staff.performance.dashboard', ['quota_id' => $overallQuota->id])
            ->with('success', 'Overall quota updated successfully.');
    }

    /**
     * Export staff team performance and overall quota report to PDF.
     */
    public function exportPdf(Request $request)
    {
        $quotaId = $request->filled('quota_id') ? (int) $request->get('quota_id') : null;
        $performance = $this->performanceService->getDashboardPerformance($quotaId);

        $generalSetting = GeneralSetting::first();
        $pdfService = app(PdfService::class);

        $data = [
            'performance'        => $performance,
            'staff'              => $performance['staff'],
            'overallQuota'       => $performance['overallQuota'],
            'targetAmount'       => $performance['targetAmount'],
            'totalSales'         => $performance['totalSales'],
            'isAchieved'         => $performance['isAchieved'],
            'progress'           => $performance['progress'],
            'remainingAmount'    => $performance['remainingAmount'],
            'commissionPerStaff' => $performance['commissionPerStaff'],
            'startDate'          => $performance['startDate'],
            'endDate'            => $performance['endDate'],
            'generalSetting'     => $generalSetting,
            'logoSrc'            => $pdfService->logoDataUri($generalSetting),
        ];

        $filename = 'team-performance-report-' . date('Y-m-d') . '.pdf';

        if ($request->has('print')) {
            return $pdfService->stream('staff.performance.pdf', $data, $filename, 'landscape');
        }

        return $pdfService->download('staff.performance.pdf', $data, $filename, 'landscape');
    }
}
