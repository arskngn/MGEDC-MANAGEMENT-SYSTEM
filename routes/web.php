<?php

use App\Http\Controllers\ProductBatchController;
use App\Http\Controllers\AdjustmentController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseTypeController;

use App\Http\Controllers\GeneralSettingController;
use App\Http\Controllers\PageAppearanceController;
use App\Services\AppearanceSettingService;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationSettingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\SystemConfigurationController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierPaymentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerPaymentController;

use App\Http\Controllers\CustomerRouteController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StaffPerformanceController;
use App\Http\Controllers\StoreCategoryController;
use App\Http\Controllers\ReportController;

use Illuminate\Support\Facades\Route;

Route::get('/', function (AppearanceSettingService $appearance) {
    return view('welcome', [
        'pageAppearance' => $appearance->forWelcome(),
    ]);
});

Route::get('/temp-migrate', function () {
    $log = [];
    try {
        // 1. Create customer_routes table if missing
        if (!\Illuminate\Support\Facades\Schema::hasTable('customer_routes')) {
            \Illuminate\Support\Facades\Schema::create('customer_routes', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('from_location');
                $table->string('to_location');
                $table->text('description')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
            });
            $log[] = 'Created customer_routes table.';
        } else {
            $log[] = 'customer_routes table already exists.';
        }

        // 2. Add route_id to customers if missing
        if (!\Illuminate\Support\Facades\Schema::hasColumn('customers', 'route_id')) {
            \Illuminate\Support\Facades\Schema::table('customers', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->foreignId('route_id')->nullable()->after('store_category_id')->constrained('customer_routes')->nullOnDelete();
            });
            $log[] = 'Added route_id to customers.';
        } else {
            $log[] = 'route_id already exists in customers.';
        }

        // 3. Seed default route if none exists
        $defaultRouteId = \Illuminate\Support\Facades\DB::table('customer_routes')->value('id');
        if (!$defaultRouteId) {
            $defaultRouteId = \Illuminate\Support\Facades\DB::table('customer_routes')->insertGetId([
                'name'          => 'Main Route',
                'from_location' => 'Warehouse',
                'to_location'   => 'City Center',
                'status'        => 'active',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
            $log[] = "Seeded default route (ID: {$defaultRouteId}).";
        } else {
            $log[] = "Default route already exists (ID: {$defaultRouteId}).";
        }

        // 4. Assign default route to customers with no route
        if ($defaultRouteId) {
            $updated = \Illuminate\Support\Facades\DB::table('customers')
                ->whereNull('route_id')
                ->update(['route_id' => $defaultRouteId]);
            $log[] = "Assigned default route to {$updated} customers.";
        }

        // 5. Add route_id and KPI commission columns to staff_targets if missing
        if (\Illuminate\Support\Facades\Schema::hasTable('staff_targets')) {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('staff_targets', 'route_id')) {
                \Illuminate\Support\Facades\Schema::table('staff_targets', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->foreignId('route_id')->nullable()->after('user_id')->constrained('customer_routes')->nullOnDelete();
                });
                $log[] = 'Added route_id column to staff_targets table.';
            } else {
                $log[] = 'route_id column already exists in staff_targets.';
            }

            // Commission KPI Columns
            $commissionColumns = [
                'max_commission'             => fn(\Illuminate\Database\Schema\Blueprint $table) => $table->decimal('max_commission', 15, 2)->default(10000.00),
                'kpi_sales_peso_weight'      => fn(\Illuminate\Database\Schema\Blueprint $table) => $table->decimal('kpi_sales_peso_weight', 15, 2)->default(5000.00),
                'kpi_sales_peso_target_pct'  => fn(\Illuminate\Database\Schema\Blueprint $table) => $table->decimal('kpi_sales_peso_target_pct', 8, 2)->default(100.00),
                'kpi_distribution_weight'    => fn(\Illuminate\Database\Schema\Blueprint $table) => $table->decimal('kpi_distribution_weight', 15, 2)->default(3000.00),
                'kpi_distribution_target_pct'=> fn(\Illuminate\Database\Schema\Blueprint $table) => $table->decimal('kpi_distribution_target_pct', 8, 2)->default(80.00),
                'distribution_product_id'    => fn(\Illuminate\Database\Schema\Blueprint $table) => $table->foreignId('distribution_product_id')->nullable()->constrained('products')->nullOnDelete(),
                'kpi_coverage_weight'        => fn(\Illuminate\Database\Schema\Blueprint $table) => $table->decimal('kpi_coverage_weight', 15, 2)->default(2000.00),
                'kpi_coverage_target_pct'    => fn(\Illuminate\Database\Schema\Blueprint $table) => $table->decimal('kpi_coverage_target_pct', 8, 2)->default(90.00),
                'coverage_days_window'       => fn(\Illuminate\Database\Schema\Blueprint $table) => $table->unsignedInteger('coverage_days_window')->default(30),
            ];

            foreach ($commissionColumns as $col => $definition) {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('staff_targets', $col)) {
                    \Illuminate\Support\Facades\Schema::table('staff_targets', function (\Illuminate\Database\Schema\Blueprint $table) use ($definition) {
                        $definition($table);
                    });
                    $log[] = "Added {$col} column to staff_targets.";
                }
            }

            if ($defaultRouteId) {
                $targetsUpdated = \Illuminate\Support\Facades\DB::table('staff_targets')
                    ->whereNull('route_id')
                    ->update(['route_id' => $defaultRouteId]);
                $log[] = "Assigned default route to {$targetsUpdated} existing staff targets.";
            }
        }

        // 5. Run permission seeder (non-fatal)
        try {
            $seeder = new \Database\Seeders\PermissionSeeder();
            $seeder->run();
            \App\Models\Permission::whereIn('module', ['PaymentReport', 'Warehouse', 'Transfer'])->delete();
            \App\Models\Permission::whereIn('name', [
                'Download Customer Payment Report CSV',
                'Download Customer Payment Report PDF',
                'Download Supplier Payment Report CSV',
                'Download Supplier Payment Report PDF',
                'Banned',
                'Staff Login',
            ])->delete();
            \App\Services\PermissionCacheService::invalidateAll();
            $log[] = 'Permissions seeded and cleaned.';
        } catch (\Exception $pe) {
            $log[] = 'Permission seeder warning (non-fatal): ' . $pe->getMessage();
        }

        // 6. Flush cache
        \Illuminate\Support\Facades\Cache::flush();
        $log[] = 'Cache flushed.';

        return 'SUCCESS | ' . implode(' | ', $log);
    } catch (\Exception $e) {
        return 'ERROR: ' . $e->getMessage() . ' | Done: ' . implode(' | ', $log);
    }
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('permission:Dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats')->middleware('permission:Dashboard');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData'])->name('dashboard.chart-data')->middleware('permission:Dashboard');
    Route::get('/dashboard/export/pdf', [DashboardController::class, 'exportPdf'])->name('dashboard.export.pdf')->middleware('permission:Dashboard');
    Route::get('/dashboard/top-selling-data', [DashboardController::class, 'topSellingData'])->name('dashboard.top-selling-data')->middleware('permission:Dashboard');
    Route::get('/dashboard/top-selling-breakdown', [DashboardController::class, 'topSellingBreakdown'])->name('dashboard.top-selling-breakdown')->middleware('permission:Dashboard');
    Route::get('/dashboard/top-selling/export/pdf', [DashboardController::class, 'exportTopSellingPdf'])->name('dashboard.top-selling.export.pdf')->middleware('permission:Dashboard');
    Route::get('/dashboard/product-sales-performance/export/pdf', [DashboardController::class, 'exportProductSalesPerformancePdf'])->name('dashboard.product-sales-performance.export.pdf')->middleware('permission:Dashboard');
    Route::get('/dashboard/payment-chart-data', [DashboardController::class, 'paymentChartData'])->name('dashboard.payment-chart-data')->middleware('permission:Dashboard');
    Route::get('/dashboard/category-performance-data', [DashboardController::class, 'categoryPerformanceData'])->name('dashboard.category-performance-data')->middleware('permission:Dashboard');
    Route::get('/dashboard/category-breakdown-data', [DashboardController::class, 'categoryBreakdownData'])->name('dashboard.category-breakdown-data')->middleware('permission:Dashboard');
    Route::get('/dashboard/category-performance/export/pdf', [DashboardController::class, 'exportCategoryPerformancePdf'])->name('dashboard.category-performance.export.pdf')->middleware('permission:Dashboard');

    Route::get('/system-settings', function () {
        return view('settings.index');
    })->name('settings.index')->middleware('permission:Setting Index');

    Route::get('/system-settings/general', [GeneralSettingController::class, 'index'])->name('settings.general')->middleware('permission:Setting Index');
    Route::post('/system-settings/general', [GeneralSettingController::class, 'update'])->name('settings.general.update')->middleware('permission:Update Setting');

    Route::get('/system-settings/logo-favicon', [GeneralSettingController::class, 'logoFavicon'])->name('settings.logo-favicon')->middleware('permission:Setting Logo Icon');
    Route::post('/system-settings/logo-favicon', [GeneralSettingController::class, 'updateLogoFavicon'])->name('settings.logo-favicon.update')->middleware('permission:Store Setting Logo Icon');

    Route::get('/system-settings/page-appearance', [PageAppearanceController::class, 'index'])->name('settings.page-appearance')->middleware('permission:Setting Index');
    Route::post('/system-settings/page-appearance', [PageAppearanceController::class, 'updatePersonal'])->name('settings.page-appearance.update')->middleware('permission:Update Setting');
    Route::post('/system-settings/page-appearance/defaults', [PageAppearanceController::class, 'updateSiteDefaults'])->name('settings.page-appearance.defaults')->middleware('permission:Update Setting');

    Route::get('/system-settings/configuration', [SystemConfigurationController::class, 'index'])->name('settings.configuration')->middleware('permission:Setting System Configuration');
    Route::post('/system-settings/configuration', [SystemConfigurationController::class, 'update'])->name('settings.configuration.update')->middleware('permission:Store Setting System Configuration');

    Route::get('/system-settings/notification', [NotificationSettingController::class, 'index'])->name('settings.notification')->middleware('permission:Email Notification Setting');
    Route::post('/system-settings/notification', [NotificationSettingController::class, 'update'])->name('settings.notification.update')->middleware('permission:Notification Setting Email Update');
    Route::post('/system-settings/notification/test-mail', [NotificationSettingController::class, 'sendTestMail'])->name('settings.notification.test-mail')->middleware('permission:Notification Setting Email Test');
    Route::post('/system-settings/notification/test-sms', [NotificationSettingController::class, 'sendTestSms'])->name('settings.notification.test-sms')->middleware('permission:Notification Setting SMS Test');
    Route::post('/system-settings/notification/template/{id}', [NotificationSettingController::class, 'updateTemplate'])->name('settings.notification.template.update')->middleware('permission:Setting Notification Template Update');

    // Extensions routes removed — table and controller dropped

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-welcome-as-read', [NotificationController::class, 'markWelcomeAsRead'])->name('notifications.mark-welcome-as-read');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
    Route::post('/notifications/delete-all', [NotificationController::class, 'deleteAll'])->name('notifications.delete-all');
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');

    // Staff Management
    Route::controller(StaffController::class)->group(function () {
        Route::get('/staff/all', 'index')->name('staff.index')->middleware('permission:All Staffs');
        Route::post('/staff/store', 'store')->name('staff.store')->middleware('permission:Save Staff');
        Route::post('/staff/update/{staff}', 'update')->name('staff.update')->middleware('permission:Save Staff');
        Route::post('/staff/toggle-status/{staff}', 'toggleStatus')->name('staff.toggle-status')->middleware('permission:Staff Status');
        Route::get('/staff/statuses', 'getStatuses')->name('staff.statuses');
    });

    // Staff Performance
    Route::controller(StaffPerformanceController::class)->group(function () {
        Route::get('/staff/performance', 'dashboard')->name('staff.performance.dashboard')->middleware('permission:All Staffs');
        Route::get('/staff/performance/export/pdf', 'exportPdf')->name('staff.performance.export.pdf')->middleware('permission:All Staffs');
        Route::post('/staff/performance/overall-quota', 'storeOverallQuota')->name('staff.performance.overall-quota.store')->middleware('permission:Save Staff');
        Route::post('/staff/performance/overall-quota/{overallQuota}', 'updateOverallQuota')->name('staff.performance.overall-quota.update')->middleware('permission:Save Staff');
        Route::get('/staff/{user}/performance', 'show')->name('staff.performance.show')->middleware('permission:All Staffs');
        Route::post('/staff/performance/assign', 'assignTarget')->name('staff.performance.assign')->middleware('permission:Save Staff');
        Route::post('/staff/performance/update', 'updateTarget')->name('staff.performance.update')->middleware('permission:Save Staff');
        Route::delete('/staff/performance/target/{target}', 'deleteTarget')->name('staff.performance.delete')->middleware('permission:Save Staff');
        Route::get('/staff/performance/used-dates', 'usedDateRanges')->name('staff.performance.used-dates')->middleware('permission:All Staffs');
    });

    // Role Management
    Route::controller(RoleController::class)->group(function () {
        Route::get('/staff/roles', 'index')->name('staff.roles.index')->middleware('permission:Roles Index');
        Route::get('/staff/roles/create', 'create')->name('staff.roles.create')->middleware('permission:Roles Add');
        Route::post('/staff/roles/store', 'store')->name('staff.roles.store')->middleware('permission:Roles Save');
        Route::get('/staff/roles/edit/{role}', 'edit')->name('staff.roles.edit')->middleware('permission:Roles Edit');
        Route::post('/staff/roles/update/{role}', 'update')->name('staff.roles.update')->middleware('permission:Roles Save');
    });

    // Page Routes
    // Purchases (static routes must be registered before /purchases/{purchase})
    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.all')->middleware('permission:All Purchases');
    Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create')->middleware('permission:New Purchase');
    Route::get('/purchases/products/search', [PurchaseController::class, 'searchProducts'])->name('purchases.products.search')->middleware('permission:Product Searching');
    Route::get('/purchases/export/pdf', [PurchaseController::class, 'exportPdfAll'])->name('purchases.export.pdf')->middleware('permission:Download Purchase PDF');
    Route::get('/purchases/export/csv', [PurchaseController::class, 'exportCsvAll'])->name('purchases.export.csv')->middleware('permission:Download Purchase CSV');
    Route::get('/purchases/return', [PurchaseReturnController::class, 'index'])->name('purchases.return')->middleware('permission:All Purchase Return');
    Route::get('/purchases/return/{purchaseReturn}', [PurchaseReturnController::class, 'show'])->name('purchases.return.show')->middleware('permission:All Purchase Return');
    Route::get('/purchases/return/export/pdf', [PurchaseReturnController::class, 'exportPdfAll'])->name('purchases.return.export.pdf')->middleware('permission:Download Purchase Return PDF');
    Route::get('/purchases/return/export/csv', [PurchaseReturnController::class, 'exportCsvAll'])->name('purchases.return.export.csv')->middleware('permission:Download Purchase Return CSV');
    Route::get('/purchases/return/{purchaseReturn}/edit', [PurchaseReturnController::class, 'edit'])->name('purchases.return.edit')->middleware('permission:Edit Purchase Return');
    Route::get('/purchases/return/{purchaseReturn}/pdf', [PurchaseReturnController::class, 'exportPdfInvoice'])->name('purchases.return.invoice.pdf')->middleware('permission:Download Purchase Return Invoice PDF');

    Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show')->middleware('permission:All Purchases');
    Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store')->middleware('permission:New Purchase');
    Route::get('/purchases/{purchase}/returns/create', [PurchaseReturnController::class, 'create'])->name('purchases.return.create')->middleware('permission:Store Purchase Return');
    Route::post('/purchases/{purchase}/returns', [PurchaseReturnController::class, 'store'])->name('purchases.return.store')->middleware('permission:Store Purchase Return');
    Route::put('/purchases/return/{purchaseReturn}', [PurchaseReturnController::class, 'update'])->name('purchases.return.update')->middleware('permission:Update Purchase Return');
    Route::post('/purchases/return/{purchaseReturn}/payment', [PurchaseReturnController::class, 'storePayment'])->name('purchases.return.payment')->middleware('permission:Store Supplier Payment Receive');
    Route::get('/purchases/{purchase}/edit', [PurchaseController::class, 'edit'])->name('purchases.edit')->middleware('permission:Edit Purchase');
    Route::get('/purchases/{purchase}/receipt', [PurchaseController::class, 'receipt'])->name('purchases.receipt')->middleware('permission:All Purchases');
    Route::post('/purchases/{purchase}/receive', [PurchaseController::class, 'receiveItems'])->name('purchases.receive')->middleware('permission:Edit Purchase');
    Route::put('/purchases/{purchase}', [PurchaseController::class, 'update'])->name('purchases.update')->middleware('permission:Purchase Update');
    Route::post('/purchases/{purchase}/clone', [PurchaseController::class, 'clone'])->name('purchases.clone')->middleware('permission:New Purchase');
    Route::post('/purchases/{purchase}/payment', [PurchaseController::class, 'storePayment'])->name('purchases.payment')->middleware('permission:Store Supplier Payment');
    Route::get('/purchases/{purchase}/pdf', [PurchaseController::class, 'exportPdfInvoice'])->name('purchases.invoice.pdf')->middleware('permission:Download Purchase Invoice PDF');

    Route::get('/products/all', [ProductController::class, 'index'])->name('products.all')->middleware('permission:All Product');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create')->middleware('permission:Add Product');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store')->middleware('permission:Product Store');
    Route::get('/products/export/pdf', [ProductController::class, 'exportPdfAll'])->name('products.export.pdf')->middleware('permission:Download Product PDF');
    Route::get('/products/export/csv', [ProductController::class, 'exportCsvAll'])->name('products.export.csv')->middleware('permission:Download Product CSV');
    Route::get('/products/import/sample', [ProductController::class, 'importSample'])->name('products.import.sample')->middleware('permission:Product Import');
    Route::post('/products/import', [ProductController::class, 'importStore'])->name('products.import')->middleware('permission:Product Import');

    // Categories (register before /products/{product} so paths stay unambiguous)
    Route::get('/products/categories/import/sample', [CategoryController::class, 'importSample'])->name('categories.import.sample')->middleware('permission:Import Category');
    Route::post('/products/categories/import', [CategoryController::class, 'importStore'])->name('categories.import')->middleware('permission:Import Category');
    Route::get('/products/categories', [CategoryController::class, 'index'])->name('categories.index')->middleware('permission:All Categorys');
    Route::post('/products/categories', [CategoryController::class, 'store'])->name('categories.store')->middleware('permission:Store Category');
    Route::put('/products/categories/{category}', [CategoryController::class, 'update'])->name('categories.update')->middleware('permission:Store Category');
    Route::delete('/products/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy')->middleware('permission:Delete Category');

    // Brands (same pattern as categories; register before /products/{product})
    Route::get('/products/brands/import/sample', [BrandController::class, 'importSample'])->name('brands.import.sample')->middleware('permission:Import Brand');
    Route::post('/products/brands/import', [BrandController::class, 'importStore'])->name('brands.import')->middleware('permission:Import Brand');
    Route::get('/products/brands', [BrandController::class, 'index'])->name('brands.index')->middleware('permission:All Brands');
    Route::post('/products/brands', [BrandController::class, 'store'])->name('brands.store')->middleware('permission:Store Brand');
    Route::put('/products/brands/{brand}', [BrandController::class, 'update'])->name('brands.update')->middleware('permission:Store Brand');
    Route::delete('/products/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy')->middleware('permission:Delete Brand');

    // Units (same pattern; register before /products/{product})
    Route::get('/products/units/import/sample', [UnitController::class, 'importSample'])->name('units.import.sample')->middleware('permission:Import Unit');
    Route::post('/products/units/import', [UnitController::class, 'importStore'])->name('units.import')->middleware('permission:Import Unit');
    Route::get('/products/units', [UnitController::class, 'index'])->name('units.index')->middleware('permission:All Unit');
    Route::post('/products/units', [UnitController::class, 'store'])->name('units.store')->middleware('permission:Store Unit');
    Route::put('/products/units/{unit}', [UnitController::class, 'update'])->name('units.update')->middleware('permission:Store Unit');
    Route::delete('/products/units/{unit}', [UnitController::class, 'destroy'])->name('units.destroy')->middleware('permission:Delete Unit');

    // Expense Types
    Route::get('/expense_types/import/sample', [ExpenseTypeController::class, 'importSample'])->name('expense_types.import.sample')->middleware('permission:Import Expense Types');
    Route::post('/expense_types/import', [ExpenseTypeController::class, 'importStore'])->name('expense_types.import')->middleware('permission:Import Expense Types');
    Route::get('/expense_types', [ExpenseTypeController::class, 'index'])->name('expense_types.index')->middleware('permission:All Expense Types');
    Route::post('/expense_types', [ExpenseTypeController::class, 'store'])->name('expense_types.store')->middleware('permission:Store Expense Type');
    Route::put('/expense_types/{expense_type}', [ExpenseTypeController::class, 'update'])->name('expense_types.update')->middleware('permission:Store Expense Type');
    Route::delete('/expense_types/{expense_type}', [ExpenseTypeController::class, 'destroy'])->name('expense_types.destroy')->middleware('permission:Delete Expense Type');

    // Expenses
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index')->middleware('permission:All Expenses');
    Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create')->middleware('permission:Store Expense');
    Route::get('/expenses/export/pdf', [ExpenseController::class, 'exportPdfAll'])->name('expenses.export.pdf')->middleware('permission:Download Expense PDF');
    Route::get('/expenses/export/csv', [ExpenseController::class, 'exportCsvAll'])->name('expenses.export.csv')->middleware('permission:Download Expense CSV');
    Route::get('/expenses/import/sample', [ExpenseController::class, 'importSample'])->name('expenses.import.sample')->middleware('permission:Import Expenses');
    Route::post('/expenses/import', [ExpenseController::class, 'importStore'])->name('expenses.import')->middleware('permission:Import Expenses');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store')->middleware('permission:Store Expense');
    Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'show'])->name('expenses.show')->middleware('permission:Store Expense');
    Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update')->middleware('permission:Store Expense');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy')->middleware('permission:Delete Expense');

    // Suppliers
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index')->middleware('permission:All Suppliers');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store')->middleware('permission:Store Supplier');
    Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update')->middleware('permission:Store Supplier');

    Route::get('/suppliers/export/pdf', [SupplierController::class, 'exportPdfAll'])->name('suppliers.export.pdf')->middleware('permission:Download Supplier PDF');
    Route::get('/suppliers/export/csv', [SupplierController::class, 'exportCsvAll'])->name('suppliers.export.csv')->middleware('permission:Download Supplier CSV');
    Route::get('/suppliers/import/sample', [SupplierController::class, 'importSample'])->name('suppliers.import.sample')->middleware('permission:Import Suppliers');
    Route::post('/suppliers/import', [SupplierController::class, 'importStore'])->name('suppliers.import')->middleware('permission:Import Suppliers');

    Route::get('/suppliers/{supplier}/payments', [SupplierPaymentController::class, 'index'])->name('suppliers.payments.index')->middleware('permission:Supplier Payment Index');
    Route::post('/suppliers/{supplier}/payments/clear', [SupplierPaymentController::class, 'clear'])->name('suppliers.payments.clear')->middleware('permission:Supplier Payment Clear');

    // Supplier Payments Report
    Route::get('/supplier-payments', [SupplierPaymentController::class, 'allPayments'])->name('supplier-payments.index')->middleware('permission:Supplier Payment Report');
    Route::get('/supplier-payments/export/pdf', [SupplierPaymentController::class, 'exportPdf'])->name('supplier-payments.export.pdf')->middleware('permission:Download Supplier Payment PDF');
    Route::get('/supplier-payments/export/csv', [SupplierPaymentController::class, 'exportCsv'])->name('supplier-payments.export.csv')->middleware('permission:Download Supplier Payment CSV');

    // Customer Payments Report
    Route::get('/customer-payments', [CustomerPaymentController::class, 'allPayments'])->name('customer-payments.index')->middleware('permission:Customer Payment Report');
    Route::get('/customer-payments/export/pdf', [CustomerPaymentController::class, 'exportPdf'])->name('customer-payments.export.pdf')->middleware('permission:Download Customer Payment PDF');
    Route::get('/customer-payments/export/csv', [CustomerPaymentController::class, 'exportCsv'])->name('customer-payments.export.csv')->middleware('permission:Download Customer Payment CSV');

    // Customers
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index')->middleware('permission:All Customers');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store')->middleware('permission:Store Customer');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update')->middleware('permission:Store Customer');

    Route::get('/customers/export/pdf', [CustomerController::class, 'exportPdfAll'])->name('customers.export.pdf')->middleware('permission:Download Customer PDF');
    Route::get('/customers/export/csv', [CustomerController::class, 'exportCsvAll'])->name('customers.export.csv')->middleware('permission:Download Customer CSV');
    Route::get('/customers/import/sample', [CustomerController::class, 'importSample'])->name('customers.import.sample')->middleware('permission:Import Customers');
    Route::post('/customers/import', [CustomerController::class, 'importStore'])->name('customers.import')->middleware('permission:Import Customers');

    Route::get('/customers/{customer}/payments', [CustomerPaymentController::class, 'index'])->name('customers.payments.index')->middleware('permission:All Customer Payments');
    Route::post('/customers/{customer}/payments/clear', [CustomerPaymentController::class, 'clear'])->name('customers.payments.clear')->middleware('permission:Clear Payment Of Customer');

    // Store Categories
    Route::get('/store-categories/import/sample', [StoreCategoryController::class, 'importSample'])->name('store-categories.import.sample')->middleware('permission:Import Category');
    Route::post('/store-categories/import', [StoreCategoryController::class, 'importStore'])->name('store-categories.import')->middleware('permission:Import Category');
    Route::get('/store-categories', [StoreCategoryController::class, 'index'])->name('store-categories.index')->middleware('permission:All Categorys');
    Route::post('/store-categories', [StoreCategoryController::class, 'store'])->name('store-categories.store')->middleware('permission:Store Category');
    Route::put('/store-categories/{storeCategory}', [StoreCategoryController::class, 'update'])->name('store-categories.update')->middleware('permission:Store Category');
    Route::delete('/store-categories/{storeCategory}', [StoreCategoryController::class, 'destroy'])->name('store-categories.destroy')->middleware('permission:Delete Category');

    // Customer Routes
    Route::get('/customer-routes', [CustomerRouteController::class, 'index'])->name('customer-routes.index')->middleware('permission:All Customers');
    Route::post('/customer-routes', [CustomerRouteController::class, 'store'])->name('customer-routes.store')->middleware('permission:Store Customer');
    Route::put('/customer-routes/{customerRoute}', [CustomerRouteController::class, 'update'])->name('customer-routes.update')->middleware('permission:Store Customer');
    Route::delete('/customer-routes/{customerRoute}', [CustomerRouteController::class, 'destroy'])->name('customer-routes.destroy')->middleware('permission:Store Customer');



    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit')->middleware('permission:Product Edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update')->middleware('permission:Product Store');
    
    // Batch Management
    Route::controller(ProductBatchController::class)->group(function () {
        Route::get('/batches', 'index')->name('batches.index')->middleware('permission:All Product');
        Route::get('/batches/{batch}', 'show')->name('batches.show')->middleware('permission:All Product');
        Route::post('/batches', 'store')->name('batches.store')->middleware('permission:Product Store');
        Route::put('/batches/{batch}', 'update')->name('batches.update')->middleware('permission:Product Store');
        Route::post('/batches/{batch}/status', 'updateStatus')->name('batches.update-status')->middleware('permission:Product Store');
        Route::delete('/batches/{batch}', 'destroy')->name('batches.destroy')->middleware('permission:Product Store');
        Route::post('/batches/{batch}/dispose', 'dispose')->name('batches.dispose')->middleware('permission:Product Store');
    });

    // Product Batches (Legacy - keep for compatibility if needed, but point to new controller)
    Route::post('/products/{product}/batches', [ProductBatchController::class, 'store'])->name('products.batches.store')->middleware('permission:Product Store');
    Route::put('/products/{product}/batches/{batch}', [ProductBatchController::class, 'update'])->name('products.batches.update')->middleware('permission:Product Store');
    Route::delete('/products/{product}/batches/{batch}', [ProductBatchController::class, 'destroy'])->name('products.batches.delete')->middleware('permission:Product Store');

    // Sales (static routes first, then parameterised)
    Route::get('/sales/all', [SaleController::class, 'index'])->name('sales.all')->middleware('permission:All Sales');
    Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create')->middleware('permission:Create Sale');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store')->middleware('permission:Store Sale');
    Route::get('/sales/products/search', [SaleController::class, 'searchProducts'])->name('sales.products.search')->middleware('permission:Product Searching');
    Route::get('sales/export/pdf', [SaleController::class, 'exportPdfAll'])->name('sales.export.pdf')->middleware('permission:Download Sales PDF');
    Route::get('sales/export/csv', [SaleController::class, 'exportCsvAll'])->name('sales.export.csv')->middleware('permission:Download Sales CSV');
    Route::get('/sales/returns/export/pdf', [SaleController::class, 'exportPdfAllReturns'])->name('sales.returns.export.pdf')->middleware('permission:Download Sale Return PDF');
    Route::get('/sales/returns/export/csv', [SaleController::class, 'exportCsvAllReturns'])->name('sales.returns.export.csv')->middleware('permission:Download Sale Return CSV');
    Route::get('/sales/return', [PageController::class, 'salesReturn'])->name('sales.return')->middleware('permission:All Sales Return');
    Route::post('sales/{sale}/confirm', [SaleController::class, 'confirm'])->name('sales.confirm')->middleware('permission:Confirm Sale');
    Route::post('sales/{sale}/reject', [SaleController::class, 'reject'])->name('sales.reject')->middleware('permission:Reject Sale');
    Route::post('sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel')->middleware('permission:Cancel Sale');
    Route::post('sales/{sale}/delivery-status', [SaleController::class, 'updateDeliveryStatus'])->name('sales.delivery-status')->middleware('permission:Update Sale Delivery Status');
    Route::get('sales/{sale}/delivery-status', fn (\App\Models\Sale $sale) => redirect()->route('sales.show', $sale));
    Route::post('sales/bulk-delivery-status', [SaleController::class, 'bulkUpdateDeliveryStatus'])->name('sales.bulk-delivery-status')->middleware('permission:Update Sale Delivery Status');

    Route::get('/sales/{sale}/returns/create', [SaleController::class, 'returnCreate'])->name('sales.return.create')->middleware('permission:Store Sale Return');
    Route::post('/sales/{sale}/returns', [SaleController::class, 'returnStore'])->name('sales.return.store')->middleware('permission:Store Sale Return');
    Route::get('/sales/{sale}/edit', [SaleController::class, 'edit'])->name('sales.edit');
    Route::put('/sales/{sale}', [SaleController::class, 'update'])->name('sales.update');
    Route::post('/sales/{sale}/payment', [SaleController::class, 'storePayment'])->name('sales.payment')->middleware('permission:Store Customer Payment');
    Route::get('/sales/{sale}/pdf', [SaleController::class, 'exportPdfInvoice'])->name('sales.invoice.pdf')->middleware('permission:Download Sale Invoice PDF');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show')->middleware('permission:All Sales');
    Route::get('/sales-returns/{saleReturn}/edit', [SaleController::class, 'returnEdit'])->name('sales.return.edit')->middleware('permission:Edit Sale Return');
    Route::get('/sales-returns/{saleReturn}', [SaleController::class, 'returnShow'])->name('sales.return.show')->middleware('permission:All Sales Return');
    Route::post('/sales-returns/{saleReturn}/confirm', [SaleController::class, 'confirmReturn'])->name('sales.return.confirm')->middleware('permission:Confirm Sale Return');
    Route::post('/sales-returns/{saleReturn}/reject', [SaleController::class, 'rejectReturn'])->name('sales.return.reject')->middleware('permission:Reject Sale Return');
    Route::put('/sales-returns/{saleReturn}', [SaleController::class, 'returnUpdate'])->name('sales.return.update')->middleware('permission:Edit Sale Return');
    Route::post('/sales-returns/{saleReturn}/payment', [SaleController::class, 'storeReturnPayment'])->name('sales.return.payment');
    Route::get('/sales-returns/{saleReturn}/pdf', [SaleController::class, 'exportPdfReturn'])->name('sales.return.pdf')->middleware('permission:Download Sale Return Invoice PDF');
    Route::resource('sales', SaleController::class);


    // Adjustments (static routes must be registered before {adjustment} parameterized routes)
    Route::get('/adjustments', [AdjustmentController::class, 'index'])->name('adjustments.index')->middleware('permission:All Adjustments');
    Route::get('/adjustments/create', [AdjustmentController::class, 'create'])->name('adjustments.create')->middleware('permission:Create Adjustment');
    Route::get('/adjustments/products/search', [AdjustmentController::class, 'searchProducts'])->name('adjustments.products.search')->middleware('permission:Adjustment Product Searching');
    Route::get('/adjustments/export/pdf', [AdjustmentController::class, 'exportPdfAll'])->name('adjustments.export.pdf')->middleware('permission:Download Adjustment PDF');
    Route::get('/adjustments/export/csv', [AdjustmentController::class, 'exportCsvAll'])->name('adjustments.export.csv')->middleware('permission:Download Adjustment CSV');
    Route::post('/adjustments', [AdjustmentController::class, 'store'])->name('adjustments.store')->middleware('permission:Store Adjustment');
    Route::get('/adjustments/{adjustment}/edit', [AdjustmentController::class, 'edit'])->name('adjustments.edit')->middleware('permission:Edit Adjustment');
    Route::get('/adjustments/{adjustment}/pdf', [AdjustmentController::class, 'exportPdfInvoice'])->name('adjustments.pdf')->middleware('permission:Download Adjustment PDF');
    Route::put('/adjustments/{adjustment}', [AdjustmentController::class, 'update'])->name('adjustments.update')->middleware('permission:Update Adjustment');
    Route::delete('/adjustments/{adjustment}', [AdjustmentController::class, 'destroy'])->name('adjustments.destroy')->middleware('permission:Delete Adjustment');

    // Dedicated Generated Reports
    Route::controller(ReportController::class)->group(function () {
        Route::get('/reports/generated', 'index')->name('reports.generated');
        Route::get('/reports/generated/sales/export/csv', 'exportSalesCsv')->name('reports.generated.sales.csv');
        Route::get('/reports/generated/sales/export/pdf', 'exportSalesPdf')->name('reports.generated.sales.pdf');
        Route::get('/reports/generated/purchases/export/csv', 'exportPurchasesCsv')->name('reports.generated.purchases.csv');
        Route::get('/reports/generated/purchases/export/pdf', 'exportPurchasesPdf')->name('reports.generated.purchases.pdf');
        Route::get('/reports/generated/dashboard/export/pdf', 'exportDashboardPdf')->name('reports.generated.dashboard.pdf');
    });

    Route::controller(PageController::class)->group(function () {
        Route::get('/reports/payments/supplier', 'reportSupplierPayments')->name('reports.payments.supplier')->middleware('permission:Supplier Payment Report');
        Route::get('/reports/payments/customer', 'reportCustomerPayments')->name('reports.payments.customer')->middleware('permission:Customer Payment Report');
        Route::get('/reports/stock', 'reportStock')->name('reports.stock')->middleware('permission:Stock Report');
        Route::get('/reports/stock/export', 'exportStock')->name('reports.stock.export');

        Route::get('/reports/data-entry/purchases', 'reportDataEntryPurchases')->name('reports.data-entry.purchases')->middleware('permission:Purchase Data Entry Report');
        Route::get('/reports/data-entry/purchase-return', 'reportDataEntryPurchaseReturn')->name('reports.data-entry.purchases-return')->middleware('permission:Purchase Return Data Entry Report');
        Route::get('/reports/data-entry/sales', 'reportDataEntrySales')->name('reports.data-entry.sales')->middleware('permission:Sale Data Entry Report');
        Route::get('/reports/data-entry/sale-return', 'reportDataEntrySaleReturn')->name('reports.data-entry.sales-return')->middleware('permission:Sale Return Data Entry Report');
        Route::get('/reports/data-entry/products', 'reportDataEntryProducts')->name('reports.data-entry.products')->middleware('permission:Product Data Entry Report');
        Route::get('/reports/data-entry/customers', 'reportDataEntryCustomers')->name('reports.data-entry.customers')->middleware('permission:Customer Data Entry Report');
        Route::get('/reports/data-entry/customer-payments', 'reportDataEntryCustomerPayments')->name('reports.data-entry.customer-payments')->middleware('permission:Customer Payment Data Entry Report');
        Route::get('/reports/data-entry/suppliers', 'reportDataEntrySuppliers')->name('reports.data-entry.suppliers')->middleware('permission:Supplier Data Entry Report');
        Route::get('/reports/data-entry/supplier-payments', 'reportDataEntrySupplierPayments')->name('reports.data-entry.supplier-payments')->middleware('permission:Supplier Payment Data Entry Report');
        Route::get('/reports/data-entry/adjustments', 'reportDataEntryAdjustments')->name('reports.data-entry.adjustments')->middleware('permission:Report Data Entry Report Adjustment');
        Route::get('/reports/data-entry/expenses', 'reportDataEntryExpenses')->name('reports.data-entry.expenses')->middleware('permission:Expense Data Entry Report');

        // Extra Routes
        Route::get('/extra/application', 'extraApplication')->name('extra.application')->middleware('permission:System Info');
        Route::get('/extra/server', 'extraServer')->name('extra.server')->middleware('permission:System Server Info');
        Route::get('/extra/cache', 'extraCache')->name('extra.cache')->middleware('permission:System Optimize');
        Route::post('/extra/cache/clear', 'clearCache')->name('extra.cache.clear')->middleware('permission:System Optimize');
        Route::get('/extra/update', 'extraUpdate')->name('extra.update');
    });

    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
