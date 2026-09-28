<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('purchases')) {
            return;
        }

        Schema::table('purchases', function (Blueprint $table) {
            if (! Schema::hasColumn('purchases', 'delivery_date')) {
                $table->date('delivery_date')->nullable()->after('purchase_date');
            }
        });

        if (Schema::hasColumn('purchases', 'actual_delivery_date') && Schema::hasColumn('purchases', 'expected_delivery_date')) {
            DB::statement('UPDATE purchases SET delivery_date = COALESCE(actual_delivery_date, expected_delivery_date) WHERE delivery_date IS NULL');
        } elseif (Schema::hasColumn('purchases', 'actual_delivery_date')) {
            DB::statement('UPDATE purchases SET delivery_date = actual_delivery_date WHERE delivery_date IS NULL');
        } elseif (Schema::hasColumn('purchases', 'expected_delivery_date')) {
            DB::statement('UPDATE purchases SET delivery_date = expected_delivery_date WHERE delivery_date IS NULL');
        }

        Schema::table('purchases', function (Blueprint $table) {
            $dropColumns = [];

            if (Schema::hasColumn('purchases', 'expected_delivery_date')) {
                $dropColumns[] = 'expected_delivery_date';
            }

            if (Schema::hasColumn('purchases', 'actual_delivery_date')) {
                $dropColumns[] = 'actual_delivery_date';
            }

            if ($dropColumns !== []) {
                $table->dropColumn($dropColumns);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('purchases')) {
            return;
        }

        Schema::table('purchases', function (Blueprint $table) {
            if (! Schema::hasColumn('purchases', 'expected_delivery_date')) {
                $table->date('expected_delivery_date')->nullable()->after('purchase_date');
            }

            if (! Schema::hasColumn('purchases', 'actual_delivery_date')) {
                $table->date('actual_delivery_date')->nullable()->after('expected_delivery_date');
            }
        });

        if (Schema::hasColumn('purchases', 'delivery_date')) {
            DB::statement('UPDATE purchases SET expected_delivery_date = delivery_date WHERE expected_delivery_date IS NULL');
            DB::statement('UPDATE purchases SET actual_delivery_date = delivery_date WHERE actual_delivery_date IS NULL');
        }

        Schema::table('purchases', function (Blueprint $table) {
            if (Schema::hasColumn('purchases', 'delivery_date')) {
                $table->dropColumn('delivery_date');
            }
        });
    }
};
