<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffTarget extends Model
{
    protected $fillable = [
        'user_id',
        'route_id',
        'store_category_ids',
        'target_amount',
        'target_type',
        'start_date',
        'end_date',
        'max_commission',
        'kpi_sales_peso_weight',
        'kpi_sales_peso_target_pct',
        'kpi_distribution_weight',
        'kpi_distribution_target_pct',
        'distribution_product_id',
        'kpi_coverage_weight',
        'kpi_coverage_target_pct',
        'coverage_days_window',
    ];

    protected $casts = [
        'route_id' => 'integer',
        'store_category_ids' => 'array',
        'target_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'max_commission' => 'decimal:2',
        'kpi_sales_peso_weight' => 'decimal:2',
        'kpi_sales_peso_target_pct' => 'decimal:2',
        'kpi_distribution_weight' => 'decimal:2',
        'kpi_distribution_target_pct' => 'decimal:2',
        'distribution_product_id' => 'integer',
        'kpi_coverage_weight' => 'decimal:2',
        'kpi_coverage_target_pct' => 'decimal:2',
        'coverage_days_window' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(CustomerRoute::class, 'route_id');
    }

    public function distributionProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'distribution_product_id');
    }

    /**
     * Get the assigned store categories collection.
     */
    public function assignedStoreCategories(): \Illuminate\Database\Eloquent\Collection
    {
        if (empty($this->store_category_ids) || !is_array($this->store_category_ids)) {
            return new \Illuminate\Database\Eloquent\Collection();
        }

        return StoreCategory::whereIn('id', $this->store_category_ids)->orderBy('name')->get();
    }

    /**
     * Get a human readable label of assigned categories.
     */
    public function getScopeCategoriesLabel(): string
    {
        if (empty($this->store_category_ids) || !is_array($this->store_category_ids)) {
            return 'All Store Categories';
        }

        $names = StoreCategory::whereIn('id', $this->store_category_ids)->pluck('name')->toArray();

        return !empty($names) ? implode(', ', $names) : 'All Store Categories';
    }

    /**
     * Get the status based on percentage.
     */
    public function getStatus(float $percentage): string
    {
        if ($percentage >= 100) {
            return 'Achieved';
        } elseif ($percentage >= 50) {
            return 'On Track';
        }
        return 'Below Target';
    }

    /**
     * Get color class for UI.
     */
    public function getStatusColor(float $percentage): string
    {
        if ($percentage >= 100) {
            return 'green';
        } elseif ($percentage >= 50) {
            return 'blue';
        }
        return 'red';
    }
}
