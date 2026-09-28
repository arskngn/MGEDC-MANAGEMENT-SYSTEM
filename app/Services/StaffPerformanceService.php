<?php

namespace App\Services;

use App\Models\User;
use App\Models\StaffTarget;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\CustomerRoute;
use App\Models\Customer;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StaffPerformanceService
{
    /**
     * Get date range based on type.
     */
    public function getDateRange(string $type): array
    {
        $now = Carbon::now();
        
        return match ($type) {
            'daily' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'weekly' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'monthly' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };
    }

    /**
     * Calculate actual sales for a user in a date range, optionally filtered by delivery route and store categories.
     *
     * @param array<int>|null $categoryIds
     */
    public function calculateActualSales(User $user, Carbon $startDate, Carbon $endDate, ?int $routeId = null, ?array $categoryIds = null): float
    {
        $grossSalesQuery = Sale::notCancelled()
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('salesperson_id', $user->id);
            });

        if ($routeId || !empty($categoryIds)) {
            $grossSalesQuery->whereHas('customer', function ($q) use ($routeId, $categoryIds) {
                if ($routeId) {
                    $q->where('route_id', $routeId);
                }
                if (!empty($categoryIds)) {
                    $q->whereIn('store_category_id', $categoryIds);
                }
            });
        }

        $grossSales = $grossSalesQuery->sum('receivable_amount');

        $returnsQuery = SaleReturn::where('status', '!=', SaleReturn::STATUS_REJECTED)
            ->whereBetween('return_date', [$startDate, $endDate])
            ->whereHas('sale', function ($q) use ($user, $routeId, $categoryIds) {
                $q->notCancelled()
                  ->where(function ($q2) use ($user) {
                      $q2->where('user_id', $user->id)
                         ->orWhere('salesperson_id', $user->id);
                  });
                if ($routeId || !empty($categoryIds)) {
                    $q->whereHas('customer', function ($q3) use ($routeId, $categoryIds) {
                        if ($routeId) {
                            $q3->where('route_id', $routeId);
                        }
                        if (!empty($categoryIds)) {
                            $q3->whereIn('store_category_id', $categoryIds);
                        }
                    });
                }
            });

        $returns = $returnsQuery->sum('payable_amount');

        return max(0, (float)$grossSales - (float)$returns);
    }

    /**
     * Get current target for user in date range, optionally by route.
     */
    public function getCurrentTarget(User $user, string $targetType, Carbon $startDate, Carbon $endDate, ?int $routeId = null): ?StaffTarget
    {
        $startStr = $startDate->toDateString();
        $endStr   = $endDate->toDateString();

        $query = StaffTarget::where('user_id', $user->id)
            ->where('target_type', $targetType)
            ->when($routeId, fn ($q) => $q->where('route_id', $routeId));

        // First try to find a target overlapping the date range
        $target = (clone $query)->where(function ($q) use ($startStr, $endStr) {
                $q->whereBetween('start_date', [$startStr, $endStr])
                  ->orWhereBetween('end_date', [$startStr, $endStr])
                  ->orWhere(function ($q2) use ($startStr, $endStr) {
                      $q2->where('start_date', '<=', $startStr)
                         ->where('end_date', '>=', $endStr);
                  });
            })
            ->orderBy('updated_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        // Fallback: get latest target for this user & target_type (and route)
        if (!$target) {
            $target = (clone $query)
                ->orderBy('updated_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();
        }

        return $target;
    }

    /**
     * Calculate route-by-route performance breakdown for a staff member.
     */
    public function calculateRoutePerformance(User $user, string $targetType, Carbon $startDate, Carbon $endDate): Collection
    {
        $routes = CustomerRoute::active()->orderBy('from_location')->get();

        return $routes->map(function ($route) use ($user, $targetType, $startDate, $endDate) {
            $target = $this->getCurrentTarget($user, $targetType, $startDate, $endDate, $route->id);
            $targetAmount = $target ? (float)$target->target_amount : 0;
            $categoryIds = $target?->store_category_ids;
            $actualSales = $this->calculateActualSales($user, $startDate, $endDate, $route->id, $categoryIds);
            $progress = $targetAmount > 0 ? ($actualSales / $targetAmount) * 100 : 0;
            $progress = min(100, round($progress, 2));

            return (object)[
                'route' => $route,
                'target' => $target,
                'target_amount' => $targetAmount,
                'actual_sales' => $actualSales,
                'progress' => $progress,
                'status' => $target ? $target->getStatus($progress) : ($actualSales > 0 ? 'Active (No Quota)' : 'No Target'),
                'color' => $target ? $target->getStatusColor($progress) : ($actualSales > 0 ? 'blue' : 'gray'),
                'categories_label' => $target ? $target->getScopeCategoriesLabel() : 'All Store Categories',
            ];
        });
    }

    /**
     * Calculate route-scoped performance metrics for a user.
     */
    public function calculatePerformance(User $user, string $targetType, Carbon $startDate, Carbon $endDate, ?int $routeId = null): object
    {
        // If a specific route is requested:
        if ($routeId) {
            $target = $this->getCurrentTarget($user, $targetType, $startDate, $endDate, $routeId);
            $targetAmount = $target ? (float)$target->target_amount : 0;
            $categoryIds = $target?->store_category_ids;
            $actualSales = $this->calculateActualSales($user, $startDate, $endDate, $routeId, $categoryIds);
            $progress = $targetAmount > 0 ? ($actualSales / $targetAmount) * 100 : 0;
            $progress = min(100, round($progress, 2));
            $route = CustomerRoute::find($routeId);
            $commission = $this->calculateCommissionSummary($user, $target, $actualSales, $route, $startDate, $endDate);

            return (object)[
                'user' => $user,
                'route' => $route,
                'route_id' => $routeId,
                'target_amount' => $targetAmount,
                'actual_sales' => $actualSales,
                'progress' => $progress,
                'status' => $target ? $target->getStatus($progress) : ($actualSales > 0 ? 'Active (No Quota)' : 'No Target'),
                'color' => $target ? $target->getStatusColor($progress) : ($actualSales > 0 ? 'blue' : 'gray'),
                'target' => $target,
                'categories_label' => $target ? $target->getScopeCategoriesLabel() : 'All Store Categories',
                'assigned_categories' => $target?->assignedStoreCategories() ?? collect(),
                'commission' => $commission,
                'route_breakdown' => collect(),
            ];
        }

        // Each staff member has one route assigned at a time.
        // Check if user has an assigned target with a route for this period:
        $activeTarget = $this->getCurrentTarget($user, $targetType, $startDate, $endDate);
        $assignedRoute = $activeTarget?->route_id ? CustomerRoute::find($activeTarget->route_id) : null;

        if ($assignedRoute) {
            $targetAmount = (float)$activeTarget->target_amount;
            $categoryIds = $activeTarget->store_category_ids;
            $actualSales = $this->calculateActualSales($user, $startDate, $endDate, $assignedRoute->id, $categoryIds);
            $progress = $targetAmount > 0 ? ($actualSales / $targetAmount) * 100 : 0;
            $progress = min(100, round($progress, 2));
            $commission = $this->calculateCommissionSummary($user, $activeTarget, $actualSales, $assignedRoute, $startDate, $endDate);

            return (object)[
                'user' => $user,
                'route' => $assignedRoute,
                'route_id' => $assignedRoute->id,
                'target_amount' => $targetAmount,
                'actual_sales' => $actualSales,
                'progress' => $progress,
                'status' => $activeTarget->getStatus($progress),
                'color' => $activeTarget->getStatusColor($progress),
                'target' => $activeTarget,
                'categories_label' => $activeTarget->getScopeCategoriesLabel(),
                'assigned_categories' => $activeTarget->assignedStoreCategories(),
                'commission' => $commission,
                'route_breakdown' => collect(),
            ];
        }

        // Fallback for staff without a route assigned or with global target:
        $totalTarget = $activeTarget ? (float)$activeTarget->target_amount : 0;
        $categoryIds = $activeTarget?->store_category_ids;
        $totalActual = $this->calculateActualSales($user, $startDate, $endDate, null, $categoryIds);
        $progress = $totalTarget > 0 ? ($totalActual / $totalTarget) * 100 : 0;
        $progress = min(100, round($progress, 2));
        $dummyTarget = new StaffTarget();
        $commission = $this->calculateCommissionSummary($user, $activeTarget, $totalActual, null, $startDate, $endDate);

        return (object)[
            'user' => $user,
            'route' => null,
            'route_id' => null,
            'target_amount' => $totalTarget,
            'actual_sales' => $totalActual,
            'progress' => $progress,
            'status' => $totalTarget > 0 ? $dummyTarget->getStatus($progress) : ($totalActual > 0 ? 'Active (No Quota)' : 'No Target'),
            'color' => $totalTarget > 0 ? $dummyTarget->getStatusColor($progress) : ($totalActual > 0 ? 'blue' : 'gray'),
            'target' => $activeTarget,
            'categories_label' => $activeTarget ? $activeTarget->getScopeCategoriesLabel() : 'All Store Categories',
            'assigned_categories' => $activeTarget?->assignedStoreCategories() ?? collect(),
            'commission' => $commission,
            'route_breakdown' => collect(),
        ];
    }

    /**
     * Get all staff performance for dashboard based on overall quota.
     */
    public function getDashboardPerformance($quotaId = null, ?int $routeId = null): array
    {
        \App\Models\OverallQuota::ensureTableExists();

        // 1. Resolve overall quota
        $quota = null;
        if (is_numeric($quotaId)) {
            $quota = \App\Models\OverallQuota::find((int) $quotaId);
        }

        if (!$quota) {
            // Find an overall quota that includes today
            $today = Carbon::today();
            $quota = \App\Models\OverallQuota::where('start_date', '<=', $today)
                ->where('end_date', '>=', $today)
                ->orderBy('created_at', 'desc')
                ->first();
        }

        if (!$quota) {
            // Fallback: latest overall quota created
            $quota = \App\Models\OverallQuota::orderBy('start_date', 'desc')->first();
        }

        $allQuotas = \App\Models\OverallQuota::orderBy('start_date', 'desc')->get();

        if ($quota) {
            $startDate = $quota->start_date->copy()->startOfDay();
            $endDate = $quota->end_date->copy()->endOfDay();
            $targetAmount = (float) $quota->target_amount;
            $commissionPerStaff = (float) ($quota->commission_per_staff ?? 5000.00);
        } else {
            [$startDate, $endDate] = $this->getDateRange('monthly');
            $targetAmount = 0.0;
            $commissionPerStaff = 5000.00;
        }

        // 2. Fetch sales staff members
        $staffMembers = User::whereHas('role', function ($q) {
            $q->whereIn('name', ['staff', 'salesperson']);
        })->get();

        if ($staffMembers->isEmpty()) {
            return [
                'staff' => collect(),
                'topPerformers' => collect(),
                'overallQuota' => $quota,
                'allQuotas' => $allQuotas,
                'targetAmount' => $targetAmount,
                'totalSales' => 0.0,
                'isAchieved' => false,
                'progress' => 0.0,
                'remainingAmount' => $targetAmount,
                'commissionPerStaff' => $commissionPerStaff,
                'totalCommissionPool' => 0.0,
                'average' => 0.0,
                'total_sales' => 0.0,
                'total_target' => $targetAmount,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'quotaId' => $quota?->id,
                'routes' => CustomerRoute::active()->orderBy('from_location')->get(),
            ];
        }

        // 3. Compute contributions
        $rawPerformances = $staffMembers->map(function ($user) use ($startDate, $endDate) {
            $actualSales = $this->calculateActualSales($user, $startDate, $endDate);

            $invoicesCount = Sale::notCancelled()
                ->whereBetween('sale_date', [$startDate, $endDate])
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhere('salesperson_id', $user->id);
                })
                ->count();

            return (object) [
                'user' => $user,
                'actual_sales' => $actualSales,
                'invoices_count' => $invoicesCount,
            ];
        });

        $totalSales = (float) $rawPerformances->sum('actual_sales');
        $isAchieved = $targetAmount > 0 && $totalSales >= $targetAmount;
        $progressPct = $targetAmount > 0 ? min(100.0, round(($totalSales / $targetAmount) * 100, 2)) : 0.0;
        $remainingAmount = max(0.0, $targetAmount - $totalSales);

        // 4. Enrich each staff member with contribution share and commission
        $performances = $rawPerformances->map(function ($item) use ($totalSales, $targetAmount, $isAchieved, $commissionPerStaff) {
            $contributionPct = $totalSales > 0 ? round(($item->actual_sales / $totalSales) * 100, 1) : 0.0;
            $quotaSharePct = $targetAmount > 0 ? round(($item->actual_sales / $targetAmount) * 100, 1) : 0.0;

            // ₱5000 commission distributed to all sales staff when overall quota is reached
            $commissionEarned = $isAchieved ? $commissionPerStaff : 0.0;

            return (object) [
                'user' => $item->user,
                'actual_sales' => $item->actual_sales,
                'invoices_count' => $item->invoices_count,
                'contribution_pct' => $contributionPct,
                'quota_share_pct' => $quotaSharePct,
                'commission_earned' => $commissionEarned,
                'commission_status' => $isAchieved ? 'Awarded' : 'Pending Quota',
                'progress' => $contributionPct,
            ];
        })->sortByDesc('actual_sales')->values();

        return [
            'staff' => $performances,
            'topPerformers' => $performances->take(5),
            'overallQuota' => $quota,
            'allQuotas' => $allQuotas,
            'targetAmount' => $targetAmount,
            'totalSales' => $totalSales,
            'isAchieved' => $isAchieved,
            'progress' => $progressPct,
            'remainingAmount' => $remainingAmount,
            'commissionPerStaff' => $commissionPerStaff,
            'totalCommissionPool' => $isAchieved ? ($staffMembers->count() * $commissionPerStaff) : 0.0,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'quotaId' => $quota?->id,
            'total_sales' => $totalSales,
            'total_target' => $targetAmount,
            'average' => $progressPct,
            'routes' => CustomerRoute::active()->orderBy('from_location')->get(),
        ];
    }

    /**
     * Get monthly sales history for a user, optionally by route.
     */
    public function getMonthlySalesHistory(User $user, int $year = null, ?int $routeId = null): Collection
    {
        $year = $year ?? now()->year;

        $grossSalesByMonth = Sale::notCancelled()
            ->whereYear('sale_date', $year)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('salesperson_id', $user->id);
            })
            ->when($routeId, function ($q) use ($routeId) {
                $q->whereHas('customer', fn ($cq) => $cq->where('route_id', $routeId));
            })
            ->selectRaw('MONTH(sale_date) as month, SUM(receivable_amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month');

        $returnsByMonth = SaleReturn::where('status', '!=', SaleReturn::STATUS_REJECTED)
            ->whereYear('return_date', $year)
            ->whereHas('sale', function ($q) use ($user, $routeId) {
                $q->notCancelled()
                  ->where(function ($q2) use ($user) {
                      $q2->where('user_id', $user->id)
                         ->orWhere('salesperson_id', $user->id);
                  });
                if ($routeId) {
                    $q->whereHas('customer', fn ($cq) => $cq->where('route_id', $routeId));
                }
            })
            ->selectRaw('MONTH(return_date) as month, SUM(payable_amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month');

        $result = collect();
        for ($m = 1; $m <= 12; $m++) {
            $gross = (float) ($grossSalesByMonth->get($m) ?? 0);
            $ret   = (float) ($returnsByMonth->get($m) ?? 0);
            $net   = max(0, $gross - $ret);
            if ($gross > 0 || $ret > 0) {
                $result->push((object) [
                    'month' => $m,
                    'total' => $net,
                ]);
            }
        }

        return $result;
    }

    /**
     * Get performance trend for a user over months, optionally by route.
     */
    public function getPerformanceTrend(User $user, int $months = 12, int $year = null, ?int $routeId = null): Collection
    {
        $year = $year ?? now()->year;
        $trends = collect();

        for ($month = 1; $month <= 12; $month++) {
            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end = Carbon::create($year, $month, 1)->endOfMonth();

            // Check if user was employed during this month
            $employedDuring = $user->created_at->lessThanOrEqualTo($end);
            
            if (!$employedDuring) {
                $trends->push((object)[
                    'month' => $start->format('M Y'),
                    'month_num' => $month,
                    'sales' => 0,
                    'target' => 0,
                    'progress' => 0,
                    'status' => 'Not yet employed',
                ]);
                continue;
            }

            $actualSales = $this->calculateActualSales($user, $start, $end, $routeId);

            $targetQuery = StaffTarget::where('user_id', $user->id)
                ->where('target_type', 'monthly')
                ->where('start_date', '<=', $end->toDateString())
                ->where('end_date', '>=', $start->toDateString())
                ->when($routeId, fn ($q) => $q->where('route_id', $routeId));

            if ($routeId) {
                $target = (clone $targetQuery)->orderBy('updated_at', 'desc')->orderBy('id', 'desc')->first();
                $targetAmount = $target ? (float)$target->target_amount : 0;
            } else {
                // Sum all route targets in this month for user
                $targetAmount = (float)(clone $targetQuery)->sum('target_amount');
            }

            $progress = $targetAmount > 0 ? ($actualSales / $targetAmount) * 100 : 0;
            $progress = min(100, round($progress, 2));

            $trends->push((object)[
                'month' => $start->format('M Y'),
                'month_num' => $month,
                'sales' => $actualSales,
                'target' => $targetAmount,
                'progress' => $progress,
                'status' => 'Active',
            ]);
        }

        return $trends;
    }

    /**
     * Calculate Sales Peso KPI (Category 1).
     * Max: ₱5,000 (configurable via $target->kpi_sales_peso_weight)
     * Requirement: 100% of the assigned sales target in terms of peso value.
     */
    public function calculateSalesPesoKPI(User $user, ?StaffTarget $target, float $actualSales): object
    {
        $targetAmount = $target ? (float)$target->target_amount : 0.0;
        $maxCommission = $target ? (float)($target->kpi_sales_peso_weight ?? 5000.00) : 5000.00;
        $requiredPct = $target ? (float)($target->kpi_sales_peso_target_pct ?? 100.00) : 100.00;

        $achievementRate = $targetAmount > 0 ? ($actualSales / $targetAmount) * 100 : 0.0;
        $isAchieved = $achievementRate >= $requiredPct && $targetAmount > 0;

        // When 100% achieved, earns full weight (₱5,000)
        $earnedCommission = $isAchieved ? $maxCommission : 0.0;

        return (object)[
            'name' => 'Sales Peso Target',
            'description' => '100% Sales Peso Target: You must achieve 100% of the assigned sales target in terms of peso value.',
            'target_amount' => $targetAmount,
            'actual_amount' => $actualSales,
            'required_percentage' => $requiredPct,
            'current_percentage' => min(100, round($achievementRate, 2)),
            'max_commission' => $maxCommission,
            'earned_commission' => $earnedCommission,
            'is_achieved' => $isAchieved,
            'status' => $isAchieved ? 'Achieved' : ($achievementRate >= 50 ? 'On Track' : 'In Progress'),
            'color' => $isAchieved ? 'green' : ($achievementRate >= 50 ? 'blue' : 'amber'),
        ];
    }

    /**
     * Calculate Product Distribution KPI (Category 2).
     * Max: ₱3,000 (configurable via $target->kpi_distribution_weight)
     *
     * CONFIRMATION RULE (2026-09-18):
     *   1. The salesperson's penetration % must first MEET the required threshold
     *      (e.g. 80% of stores are carrying the focus product).
     *   2. The commission is ONLY CONFIRMED / counted as earned ONCE the
     *      assigned quota period has fully ENDED (today >= end_date of the
     *      StaffTarget).  Hitting the bar mid-period is "Threshold Met – Pending",
     *      not "Achieved".
     */
    public function calculateProductDistributionKPI(User $user, ?CustomerRoute $route, Carbon $startDate, Carbon $endDate, ?StaffTarget $target = null): object
    {
        $maxCommission = $target ? (float)($target->kpi_distribution_weight ?? 3000.00) : 3000.00;
        $requiredPct = $target ? (float)($target->kpi_distribution_target_pct ?? 80.00) : 80.00;
        $focusProductId = $target?->distribution_product_id;
        $categoryIds = $target?->store_category_ids;

        // Quota period end date used for confirmation gating
        $quotaEndDate = $target?->end_date ? $target->end_date->copy()->endOfDay() : $endDate->copy()->endOfDay();
        $periodEnded = now()->greaterThanOrEqualTo($quotaEndDate);

        // Total stores on this route (or all active customers if no route specified)
        $storesQuery = Customer::where('status', Customer::STATUS_ACTIVE);
        if ($route) {
            $storesQuery->where('route_id', $route->id);
        }
        if (!empty($categoryIds)) {
            $storesQuery->whereIn('store_category_id', $categoryIds);
        }
        $totalStores = $storesQuery->count();

        // Stores that ordered the focus product (or any product if none designated) from this salesperson in the period
        $salesQuery = Sale::notCancelled()
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('salesperson_id', $user->id);
            });

        if ($route || !empty($categoryIds)) {
            $salesQuery->whereHas('customer', function ($q) use ($route, $categoryIds) {
                if ($route) {
                    $q->where('route_id', $route->id);
                }
                if (!empty($categoryIds)) {
                    $q->whereIn('store_category_id', $categoryIds);
                }
            });
        }

        if ($focusProductId) {
            $salesQuery->whereHas('items', function ($q) use ($focusProductId) {
                $q->where('product_id', $focusProductId);
            });
            $focusProduct = Product::find($focusProductId);
            $productLabel = $focusProduct ? $focusProduct->name : 'Designated Product';
        } else {
            // If no specific product is locked in, check stores carrying products sold by this salesperson
            $salesQuery->whereHas('items');
            $productLabel = 'Focus Products';
        }

        $carryingStoreIds = $salesQuery->distinct()->pluck('customer_id');
        $carryingStoresCount = $carryingStoreIds->count();

        $distributionPct = $totalStores > 0 ? ($carryingStoresCount / $totalStores) * 100 : 0.0;
        $thresholdMet    = $distributionPct >= $requiredPct && $totalStores > 0;

        // Achieved = threshold reached AND the quota period has ended.
        $isAchieved      = $thresholdMet && $periodEnded;
        $earnedCommission = $isAchieved ? $maxCommission : 0.0;

        // Status: expose a 3-state UI so staff knows the difference between
        // "still needs sales" and "waiting for period-end confirmation".
        if ($isAchieved) {
            $status = 'Achieved';
            $color  = 'green';
        } elseif ($thresholdMet) {
            $status = 'Pending – Period End';
            $color  = 'amber';
        } elseif ($distributionPct >= 50) {
            $status = 'On Track';
            $color  = 'blue';
        } else {
            $status = 'In Progress';
            $color  = 'amber';
        }

        return (object)[
            'name' => 'Product Distribution',
            'product_name' => $productLabel,
            'description' => "Product Distribution: {$requiredPct}% of stores must carry {$productLabel}. Commission is confirmed only at the END of the assigned quota period ({$quotaEndDate->format('M d, Y')}).",
            'total_stores' => $totalStores,
            'carrying_stores' => $carryingStoresCount,
            'required_percentage' => $requiredPct,
            'current_percentage' => round($distributionPct, 2),
            'threshold_met' => $thresholdMet,
            'period_ended' => $periodEnded,
            'period_end_date' => $quotaEndDate,
            'max_commission' => $maxCommission,
            'earned_commission' => $earnedCommission,
            'is_achieved' => $isAchieved,
            'status' => $status,
            'color' => $color,
        ];
    }

    /**
     * Calculate Account Coverage KPI (Category 3).
     * Max: ₱2,000 (configurable via $target->kpi_coverage_weight)
     *
     * CONFIRMATION RULE (2026-09-18):
     *   The 90% ordering threshold must be met in TWO SUCCESSIVE coverage
     *   windows (e.g. two back-to-back 30-day windows) before any commission
     *   is counted.  In other words:
     *      1. After the 1st window ends ("the set day"),
     *      2. The SAME 90% (or more) of stores must ORDER AGAIN in the next
     *         coverage window — only then is the commission confirmed.
     *
     *   - "Current window":  [end_date - coverage_days_window, end_date]
     *   - "Previous window": [end_date - 2*coverage_days_window, end_date - coverage_days_window - 1]
     *   - Both windows must independently have their ordering coverage %
     *     >= requiredPct.
     */
    public function calculateAccountCoverageKPI(User $user, ?CustomerRoute $route, Carbon $startDate, Carbon $endDate, ?StaffTarget $target = null): object
    {
        $maxCommission = $target ? (float)($target->kpi_coverage_weight ?? 2000.00) : 2000.00;
        $requiredPct = $target ? (float)($target->kpi_coverage_target_pct ?? 90.00) : 90.00;
        $daysWindow = $target ? (int)($target->coverage_days_window ?? 30) : 30;
        $categoryIds = $target?->store_category_ids;

        // Build the total-active-stores base query once, reuse across both windows
        $storesBaseQuery = Customer::where('status', Customer::STATUS_ACTIVE);
        if ($route) {
            $storesBaseQuery->where('route_id', $route->id);
        }
        if (!empty($categoryIds)) {
            $storesBaseQuery->whereIn('store_category_id', $categoryIds);
        }
        $totalStores = (clone $storesBaseQuery)->count();

        // --------  WINDOW 1 (Previous window)  --------
        // Previous window ends the DAY BEFORE the current window starts, so
        // windows are strictly non-overlapping.  Any sale counts in exactly one
        // window, which is required by the "2 successive windows" retention rule.
        $prevWinEnd   = $endDate->copy()->subDays($daysWindow + 1)->endOfDay();
        $prevWinStart = $endDate->copy()->subDays($daysWindow * 2 + 1)->startOfDay();
        // If the target period started inside the "previous" window, clamp the
        // previous window start so we don't require ordering BEFORE the target
        // was even assigned to the staffer.
        if ($startDate->greaterThan($prevWinStart)) {
            $prevWinStart = $startDate->copy()->startOfDay();
        }

        // Enough time has actually passed for a previous window to exist?
        $retentionEligible = $prevWinEnd->greaterThanOrEqualTo($prevWinStart)
            && $totalStores > 0
            && $prevWinEnd->diffInDays($prevWinStart) + 1 >= (int)ceil($daysWindow / 2);

        $prevOrdered = $this->coverageOrderedStores($user, $route, $categoryIds, $prevWinStart, $prevWinEnd);
        $prevPct = $retentionEligible && $totalStores > 0
            ? ($prevOrdered->count() / $totalStores) * 100
            : 0.0;
        $prevThresholdMet = $retentionEligible && $prevPct >= $requiredPct && $totalStores > 0;

        // --------  WINDOW 2 (Current / Most recent window)  --------
        $currWinStart = $endDate->copy()->subDays($daysWindow)->startOfDay();
        if ($currWinStart->lessThan($startDate)) {
            $currWinStart = $startDate->copy()->startOfDay();
        }
        $currWinEnd = $endDate->copy()->endOfDay();

        $currOrdered = $this->coverageOrderedStores($user, $route, $categoryIds, $currWinStart, $currWinEnd);
        $currPct = $totalStores > 0 ? ($currOrdered->count() / $totalStores) * 100 : 0.0;
        $currThresholdMet = $currPct >= $requiredPct && $totalStores > 0;

        // --------  Final confirmation rule  --------
        $isAchieved = $currThresholdMet && $prevThresholdMet && $retentionEligible;
        $earnedCommission = $isAchieved ? $maxCommission : 0.0;

        // --------  UI status messaging  --------
        if ($isAchieved) {
            $status = 'Achieved';
            $color  = 'green';
        } elseif ($currThresholdMet && !$retentionEligible) {
            // Current window passes, but we don't yet have a full previous
            // window to compare against — system is still "warming up".
            $status = 'Warming Up — 1st Window';
            $color  = 'amber';
        } elseif ($currThresholdMet && !$prevThresholdMet) {
            // Threshold hit this window; need it AGAIN in the next rolling
            // window for the 2-cycle rule ("order again").
            $status = 'Pending Retention — 2nd Window';
            $color  = 'amber';
        } elseif ($currPct >= 50) {
            $status = 'On Track';
            $color  = 'blue';
        } else {
            $status = 'In Progress';
            $color  = 'amber';
        }

        return (object)[
            'name' => 'Account Coverage',
            'description' => "Account Coverage: {$requiredPct}% of stores must order in TWO successive {$daysWindow}-day windows (\"order again\" rule). Current window: {$currWinStart->format('M d')} – {$currWinEnd->format('M d, Y')}. Previous window: {$prevWinStart->format('M d')} – {$prevWinEnd->format('M d, Y')}.",
            'days_window' => $daysWindow,
            'total_stores' => $totalStores,
            'current_window_start' => $currWinStart,
            'current_window_end'   => $currWinEnd,
            'previous_window_start' => $prevWinStart,
            'previous_window_end'   => $prevWinEnd,
            'ordered_stores' => $currOrdered->count(),
            'current_percentage' => round($currPct, 2),
            'previous_ordered_stores' => $prevOrdered->count(),
            'previous_percentage' => round($prevPct, 2),
            'required_percentage' => $requiredPct,
            'threshold_met'          => $currThresholdMet,
            'previous_threshold_met' => $prevThresholdMet,
            'retention_eligible'     => $retentionEligible,
            'max_commission' => $maxCommission,
            'earned_commission' => $earnedCommission,
            'is_achieved' => $isAchieved,
            'status' => $status,
            'color' => $color,
        ];
    }

    /**
     * Helper: return distinct customer IDs of stores that placed at least one
     * sale from $user inside [$winStart, $winEnd], scoped to $route + $categoryIds.
     *
     * @return \Illuminate\Support\Collection<int, int>
     */
    private function coverageOrderedStores(User $user, ?CustomerRoute $route, ?array $categoryIds, Carbon $winStart, Carbon $winEnd): Collection
    {
        if ($winStart->greaterThan($winEnd)) {
            return collect();
        }

        $coveredQuery = Sale::notCancelled()
            ->whereBetween('sale_date', [$winStart, $winEnd])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('salesperson_id', $user->id);
            });

        if ($route || !empty($categoryIds)) {
            $coveredQuery->whereHas('customer', function ($q) use ($route, $categoryIds) {
                if ($route) {
                    $q->where('route_id', $route->id);
                }
                if (!empty($categoryIds)) {
                    $q->whereIn('store_category_id', $categoryIds);
                }
            });
        }

        return $coveredQuery->distinct()->pluck('customer_id');
    }

    /**
     * Calculate comprehensive 3-KPI Commission Summary for a user and period.
     */
    public function calculateCommissionSummary(User $user, ?StaffTarget $target, float $actualSales, ?CustomerRoute $route, Carbon $startDate, Carbon $endDate): object
    {
        $maxTotalCommission = $target ? (float)($target->max_commission ?? 10000.00) : 10000.00;

        $kpiSalesPeso = $this->calculateSalesPesoKPI($user, $target, $actualSales);
        $kpiDistribution = $this->calculateProductDistributionKPI($user, $route, $startDate, $endDate, $target);
        $kpiCoverage = $this->calculateAccountCoverageKPI($user, $route, $startDate, $endDate, $target);

        $earnedTotal = $kpiSalesPeso->earned_commission + $kpiDistribution->earned_commission + $kpiCoverage->earned_commission;
        $achievedCount = ($kpiSalesPeso->is_achieved ? 1 : 0) + ($kpiDistribution->is_achieved ? 1 : 0) + ($kpiCoverage->is_achieved ? 1 : 0);
        $commissionProgress = $maxTotalCommission > 0 ? round(($earnedTotal / $maxTotalCommission) * 100, 2) : 0;

        return (object)[
            'max_commission' => $maxTotalCommission,
            'earned_commission' => $earnedTotal,
            'progress' => $commissionProgress,
            'achieved_count' => $achievedCount,
            'total_kpis' => 3,
            'is_fully_achieved' => $achievedCount === 3,
            'kpis' => [
                'sales_peso' => $kpiSalesPeso,
                'distribution' => $kpiDistribution,
                'coverage' => $kpiCoverage,
            ],
        ];
    }

    /**
     * Compare user performance with team average.
     */
    public function compareWithTeamAverage(User $user, string $targetType = 'monthly', ?int $routeId = null): array
    {
        $dashboard = $this->getDashboardPerformance();
        $userPerf = $this->calculatePerformance($user, $targetType, $dashboard['startDate'], $dashboard['endDate'], $routeId);

        $index = $dashboard['staff']->search(function ($perf) use ($user) {
            return $perf->user->id === $user->id;
        });

        return [
            'user' => $userPerf,
            'team_average' => $dashboard['average'],
            'rank' => $index === false ? $dashboard['staff']->count() + 1 : $index + 1,
            'total_staff' => $dashboard['staff']->count(),
        ];
    }
}
