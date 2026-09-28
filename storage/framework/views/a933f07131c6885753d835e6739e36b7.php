<?php
    $sidebarAppearance = app(\App\Services\AppearanceSettingService::class)->forSystem();
    $gradientFrom = $sidebarAppearance['colors'][0] ?? '#F15A22';
    $gradientTo = $sidebarAppearance['colors'][1] ?? '#0A0E27';
?>

<template x-teleport="body">
    <div
        x-ref="collapsedMenuPortal"
        data-menu-id="collapsed-flyout"
        x-cloak
        x-show="sidebarCollapsed && collapsedDropdown"
        class="floating-menu fixed z-[200] w-48 border border-white/10 rounded-lg shadow-2xl overflow-hidden py-2 pointer-events-auto max-h-[min(80vh,520px)] overflow-y-auto"
        :style="`background: linear-gradient(180deg, <?php echo e($gradientFrom); ?> 0%, <?php echo e($gradientTo); ?> 100%); top: ${floatingMenuTop}px; left: ${floatingMenuLeft}px;`"
        @click.outside="collapsedDropdown = null"
    >
        <div class="absolute -left-2 w-4 h-4 border-l border-t border-white/10 -rotate-45 z-[-1] pointer-events-none"
             :style="`background: <?php echo e($gradientFrom); ?>; top: ${floatingMenuArrow}px; transform: translateY(-50%) rotate(-45deg);`"></div>

        <div x-show="collapsedDropdown === 'sales'" x-cloak>
            <div class="px-4 py-2 border-b border-white/20 mb-1">
                <span class="text-[11px] font-bold text-white/90 uppercase tracking-wider">Manage Sales</span>
            </div>
            <a href="<?php echo e(route('sales.all')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('sales.all') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">All Sales</a>
            <?php if(auth()->user()->hasPermission('All Sales Return')): ?>
                <a href="<?php echo e(route('sales.return')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('sales.return') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">Sales Return</a>
            <?php endif; ?>
        </div>

        <div x-show="collapsedDropdown === 'purchases'" x-cloak>
            <div class="px-4 py-2 border-b border-white/20 mb-1">
                <span class="text-[11px] font-bold text-white/90 uppercase tracking-wider">Manage Purchases</span>
            </div>
            <a href="<?php echo e(route('purchases.all')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('purchases.all') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">All Purchases</a>
            <?php if(auth()->user()->hasPermission('All Purchase Return')): ?>
                <a href="<?php echo e(route('purchases.return')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('purchases.return*') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">Purchases Return</a>
            <?php endif; ?>
        </div>

        <?php if(auth()->user()->canSeeManageProductsMenu()): ?>
        <div x-show="collapsedDropdown === 'products'" x-cloak>
            <div class="px-4 py-2 border-b border-white/20 mb-1">
                <span class="text-[11px] font-bold text-white/90 uppercase tracking-wider">Manage Products</span>
            </div>
            <?php if(auth()->user()->hasPermission('All Product')): ?>
                <a href="<?php echo e(route('products.all')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('products.*') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">Products</a>
                <a href="<?php echo e(route('batches.index')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('batches.*') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">Batch Management</a>
            <?php endif; ?>
            <?php if(auth()->user()->hasPermission('All Categorys')): ?>
                <a href="<?php echo e(route('categories.index')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('categories.*') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">Categories</a>
            <?php endif; ?>
            <?php if(auth()->user()->hasPermission('All Brands')): ?>
                <a href="<?php echo e(route('brands.index')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('brands.*') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">Brands</a>
            <?php endif; ?>
            <?php if(auth()->user()->hasPermission('All Unit')): ?>
                <a href="<?php echo e(route('units.index')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('units.*') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">Units</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div x-show="collapsedDropdown === 'customers'" x-cloak>
            <div class="px-4 py-2 border-b border-white/20 mb-1">
                <span class="text-[11px] font-bold text-white/90 uppercase tracking-wider">Manage Customers</span>
            </div>
            <?php if(auth()->user()->hasPermission('All Customers')): ?>
                <a href="<?php echo e(route('customers.index')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('customers.index') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">Customers</a>
            <?php endif; ?>
            <?php if(auth()->user()->hasPermission('All Categorys')): ?>
                <a href="<?php echo e(route('store-categories.index')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('store-categories.*') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">Store Categories</a>
            <?php endif; ?>
            <?php if(auth()->user()->hasPermission('All Customers')): ?>
                <a href="<?php echo e(route('customer-routes.index')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('customer-routes.*') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">Delivery Routes</a>
            <?php endif; ?>
        </div>

        <div x-show="collapsedDropdown === 'staff'" x-cloak>
            <div class="px-4 py-2 border-b border-white/20 mb-1">
                <span class="text-[11px] font-bold text-white/90 uppercase tracking-wider">Manage Staff</span>
            </div>
            <a href="<?php echo e(route('staff.index')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('staff.index') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">All Staff</a>
            <a href="<?php echo e(route('staff.performance.dashboard')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('staff.performance.dashboard') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">Performance</a>
            <?php if(auth()->user()->hasPermission('Roles Index')): ?>
                <a href="<?php echo e(route('staff.roles.index')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('staff.roles.index') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">Roles</a>
            <?php endif; ?>
        </div>

        <div x-show="collapsedDropdown === 'expenses'" x-cloak>
            <div class="px-4 py-2 border-b border-white/20 mb-1">
                <span class="text-[11px] font-bold text-white/90 uppercase tracking-wider">Manage Expenses</span>
            </div>
            <?php if(auth()->user()->hasPermission('All Expense Types')): ?>
                <a href="<?php echo e(route('expense_types.index')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('expense_types.index') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">Expense Types</a>
            <?php endif; ?>
            <a href="<?php echo e(route('expenses.index')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('expenses.index') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">Expenses</a>
        </div>

        <div x-show="collapsedDropdown === 'reports_payments'" x-cloak>
            <div class="px-4 py-2 border-b border-white/20 mb-1">
                <span class="text-[11px] font-bold text-white/90 uppercase tracking-wider">Payment Report</span>
            </div>
            <?php if(auth()->user()->hasPermission('Supplier Payment Report')): ?>
                <a href="<?php echo e(route('supplier-payments.index')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('reports.payments.supplier') || request()->routeIs('supplier-payments.*') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">Supplier Payments</a>
            <?php endif; ?>
            <?php if(auth()->user()->hasPermission('Customer Payment Report')): ?>
                <a href="<?php echo e(route('customer-payments.index')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('reports.payments.customer') || request()->routeIs('customer-payments.*') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">Customer Payments</a>
            <?php endif; ?>
        </div>

        <div x-show="collapsedDropdown === 'reports_data_entry'" x-cloak>
            <div class="px-4 py-2 border-b border-white/20 mb-1">
                <span class="text-[11px] font-bold text-white/90 uppercase tracking-wider">Data Entry Report</span>
            </div>
            <?php if(auth()->user()->hasPermission('Purchase Data Entry Report')): ?>
                <a href="<?php echo e(route('reports.data-entry.purchases')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('reports.data-entry.purchases') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">Purchases</a>
            <?php endif; ?>
            <?php if(auth()->user()->hasPermission('Purchase Return Data Entry Report')): ?>
                <a href="<?php echo e(route('reports.data-entry.purchases-return')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('reports.data-entry.purchases-return') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">Purchase Return</a>
            <?php endif; ?>
            <?php if(auth()->user()->hasPermission('Sale Data Entry Report')): ?>
                <a href="<?php echo e(route('reports.data-entry.sales')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('reports.data-entry.sales') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">Sales</a>
            <?php endif; ?>
            <?php if(auth()->user()->hasPermission('Sale Return Data Entry Report')): ?>
                <a href="<?php echo e(route('reports.data-entry.sales-return')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('reports.data-entry.sales-return') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">Sale Return</a>
            <?php endif; ?>
            <?php if(auth()->user()->hasPermission('Product Data Entry Report')): ?>
                <a href="<?php echo e(route('reports.data-entry.products')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('reports.data-entry.products') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">Products</a>
            <?php endif; ?>
            <?php if(auth()->user()->hasPermission('Customer Data Entry Report')): ?>
                <a href="<?php echo e(route('reports.data-entry.customers')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('reports.data-entry.customers') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">Customers</a>
            <?php endif; ?>
            <?php if(auth()->user()->hasPermission('Customer Payment Data Entry Report')): ?>
                <a href="<?php echo e(route('reports.data-entry.customer-payments')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('reports.data-entry.customer-payments') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">Customer Payments</a>
            <?php endif; ?>
            <?php if(auth()->user()->hasPermission('Supplier Data Entry Report')): ?>
                <a href="<?php echo e(route('reports.data-entry.suppliers')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('reports.data-entry.suppliers') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">Suppliers</a>
            <?php endif; ?>
            <?php if(auth()->user()->hasPermission('Supplier Payment Data Entry Report')): ?>
                <a href="<?php echo e(route('reports.data-entry.supplier-payments')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('reports.data-entry.supplier-payments') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">Supplier Payments</a>
            <?php endif; ?>
            <?php if(auth()->user()->hasPermission('Report Data Entry Report Adjustment')): ?>
                <a href="<?php echo e(route('reports.data-entry.adjustments')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('reports.data-entry.adjustments') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">Adjustments</a>
            <?php endif; ?>
            <?php if(auth()->user()->hasPermission('Expense Data Entry Report')): ?>
                <a href="<?php echo e(route('reports.data-entry.expenses')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('reports.data-entry.expenses') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">Expenses</a>
            <?php endif; ?>
        </div>

        <div x-show="collapsedDropdown === 'extra'" x-cloak>
            <div class="px-4 py-2 border-b border-white/20 mb-1">
                <span class="text-[11px] font-bold text-white/90 uppercase tracking-wider">Extra</span>
            </div>
            <a href="<?php echo e(route('extra.application')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('extra.application') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">Application</a>
            <?php if(auth()->user()->hasPermission('System Server Info')): ?>
                <a href="<?php echo e(route('extra.server')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('extra.server') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">Server</a>
            <?php endif; ?>
            <?php if(auth()->user()->hasPermission('System Optimize')): ?>
                <a href="<?php echo e(route('extra.cache')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('extra.cache') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">Cache</a>
            <?php endif; ?>
            <a href="<?php echo e(route('extra.update')); ?>" class="block px-4 py-2 text-[12px] font-medium <?php echo e(request()->routeIs('extra.update') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">Update</a>
        </div>
    </div>
</template>
<?php /**PATH C:\laragon\www\GE-System-1.1\resources\views/layouts/partials/collapsed-sidebar-flyout.blade.php ENDPATH**/ ?>