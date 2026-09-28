<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('staff_targets')) {
            Schema::table('staff_targets', function (Blueprint $table) {
                if (!Schema::hasColumn('staff_targets', 'max_commission')) {
                    $table->decimal('max_commission', 15, 2)->default(10000.00)->after('end_date');
                }
                if (!Schema::hasColumn('staff_targets', 'kpi_sales_peso_weight')) {
                    $table->decimal('kpi_sales_peso_weight', 15, 2)->default(5000.00)->after('max_commission');
                }
                if (!Schema::hasColumn('staff_targets', 'kpi_sales_peso_target_pct')) {
                    $table->decimal('kpi_sales_peso_target_pct', 8, 2)->default(100.00)->after('kpi_sales_peso_weight');
                }
                if (!Schema::hasColumn('staff_targets', 'kpi_distribution_weight')) {
                    $table->decimal('kpi_distribution_weight', 15, 2)->default(3000.00)->after('kpi_sales_peso_target_pct');
                }
                if (!Schema::hasColumn('staff_targets', 'kpi_distribution_target_pct')) {
                    $table->decimal('kpi_distribution_target_pct', 8, 2)->default(80.00)->after('kpi_distribution_weight');
                }
                if (!Schema::hasColumn('staff_targets', 'distribution_product_id')) {
                    $table->foreignId('distribution_product_id')->nullable()->after('kpi_distribution_target_pct')->constrained('products')->nullOnDelete();
                }
                if (!Schema::hasColumn('staff_targets', 'kpi_coverage_weight')) {
                    $table->decimal('kpi_coverage_weight', 15, 2)->default(2000.00)->after('distribution_product_id');
                }
                if (!Schema::hasColumn('staff_targets', 'kpi_coverage_target_pct')) {
                    $table->decimal('kpi_coverage_target_pct', 8, 2)->default(90.00)->after('kpi_coverage_weight');
                }
                if (!Schema::hasColumn('staff_targets', 'coverage_days_window')) {
                    $table->unsignedInteger('coverage_days_window')->default(30)->after('kpi_coverage_target_pct');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('staff_targets')) {
            Schema::table('staff_targets', function (Blueprint $table) {
                $droppable = [
                    'coverage_days_window',
                    'kpi_coverage_target_pct',
                    'kpi_coverage_weight',
                ];
                foreach ($droppable as $col) {
                    if (Schema::hasColumn('staff_targets', $col)) {
                        $table->dropColumn($col);
                    }
                }
                if (Schema::hasColumn('staff_targets', 'distribution_product_id')) {
                    $table->dropForeign(['distribution_product_id']);
                    $table->dropColumn('distribution_product_id');
                }
                $moreDroppable = [
                    'kpi_distribution_target_pct',
                    'kpi_distribution_weight',
                    'kpi_sales_peso_target_pct',
                    'kpi_sales_peso_weight',
                    'max_commission',
                ];
                foreach ($moreDroppable as $col) {
                    if (Schema::hasColumn('staff_targets', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
