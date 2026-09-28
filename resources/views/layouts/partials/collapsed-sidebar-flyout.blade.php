@php
    $sidebarAppearance = app(\App\Services\AppearanceSettingService::class)->forSystem();
    $gradientFrom = $sidebarAppearance['colors'][0] ?? '#F15A22';
    $gradientTo = $sidebarAppearance['colors'][1] ?? '#0A0E27';
@endphp
{{-- Single collapsed-sidebar flyout (one teleport target; avoids stacked menus on body) --}}
<template x-teleport="body">
    <div
        x-ref="collapsedMenuPortal"
        data-menu-id="collapsed-flyout"
        x-cloak
        x-show="sidebarCollapsed && collapsedDropdown"
        class="floating-menu fixed z-[200] w-48 border border-white/10 rounded-lg shadow-2xl overflow-hidden py-2 pointer-events-auto max-h-[min(80vh,520px)] overflow-y-auto"
        :style="`background: linear-gradient(180deg, {{ $gradientFrom }} 0%, {{ $gradientTo }} 100%); top: ${floatingMenuTop}px; left: ${floatingMenuLeft}px;`"
        @click.outside="collapsedDropdown = null"
    >
        <div class="absolute -left-2 w-4 h-4 border-l border-t border-white/10 -rotate-45 z-[-1] pointer-events-none"
             :style="`background: {{ $gradientFrom }}; top: ${floatingMenuArrow}px; transform: translateY(-50%) rotate(-45deg);`"></div>

        <div x-show="collapsedDropdown === 'sales'" x-cloak>
            <div class="px-4 py-2 border-b border-white/20 mb-1">
                <span class="text-[11px] font-bold text-white/90 uppercase tracking-wider">Manage Sales</span>
            </div>
            <a href="{{ route('sales.all') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('sales.all') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">All Sales</a>
            @if(auth()->user()->hasPermission('All Sales Return'))
                <a href="{{ route('sales.return') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('sales.return') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">Sales Return</a>
            @endif
        </div>

        <div x-show="collapsedDropdown === 'purchases'" x-cloak>
            <div class="px-4 py-2 border-b border-white/20 mb-1">
                <span class="text-[11px] font-bold text-white/90 uppercase tracking-wider">Manage Purchases</span>
            </div>
            <a href="{{ route('purchases.all') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('purchases.all') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">All Purchases</a>
            @if(auth()->user()->hasPermission('All Purchase Return'))
                <a href="{{ route('purchases.return') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('purchases.return*') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">Purchases Return</a>
            @endif
        </div>

        @if(auth()->user()->canSeeManageProductsMenu())
        <div x-show="collapsedDropdown === 'products'" x-cloak>
            <div class="px-4 py-2 border-b border-white/20 mb-1">
                <span class="text-[11px] font-bold text-white/90 uppercase tracking-wider">Manage Products</span>
            </div>
            @if(auth()->user()->hasPermission('All Product'))
                <a href="{{ route('products.all') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('products.*') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">Products</a>
                <a href="{{ route('batches.index') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('batches.*') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">Batch Management</a>
            @endif
            @if(auth()->user()->hasPermission('All Categorys'))
                <a href="{{ route('categories.index') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('categories.*') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">Categories</a>
            @endif
            @if(auth()->user()->hasPermission('All Brands'))
                <a href="{{ route('brands.index') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('brands.*') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">Brands</a>
            @endif
            @if(auth()->user()->hasPermission('All Unit'))
                <a href="{{ route('units.index') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('units.*') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">Units</a>
            @endif
        </div>
        @endif

        <div x-show="collapsedDropdown === 'customers'" x-cloak>
            <div class="px-4 py-2 border-b border-white/20 mb-1">
                <span class="text-[11px] font-bold text-white/90 uppercase tracking-wider">Manage Customers</span>
            </div>
            @if(auth()->user()->hasPermission('All Customers'))
                <a href="{{ route('customers.index') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('customers.index') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">Customers</a>
            @endif
            @if(auth()->user()->hasPermission('All Categorys'))
                <a href="{{ route('store-categories.index') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('store-categories.*') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">Store Categories</a>
            @endif
            @if(auth()->user()->hasPermission('All Customers'))
                <a href="{{ route('customer-routes.index') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('customer-routes.*') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">Delivery Routes</a>
            @endif
        </div>

        <div x-show="collapsedDropdown === 'staff'" x-cloak>
            <div class="px-4 py-2 border-b border-white/20 mb-1">
                <span class="text-[11px] font-bold text-white/90 uppercase tracking-wider">Manage Staff</span>
            </div>
            <a href="{{ route('staff.index') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('staff.index') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">All Staff</a>
            <a href="{{ route('staff.performance.dashboard') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('staff.performance.dashboard') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">Performance</a>
            @if(auth()->user()->hasPermission('Roles Index'))
                <a href="{{ route('staff.roles.index') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('staff.roles.index') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">Roles</a>
            @endif
        </div>

        <div x-show="collapsedDropdown === 'expenses'" x-cloak>
            <div class="px-4 py-2 border-b border-white/20 mb-1">
                <span class="text-[11px] font-bold text-white/90 uppercase tracking-wider">Manage Expenses</span>
            </div>
            @if(auth()->user()->hasPermission('All Expense Types'))
                <a href="{{ route('expense_types.index') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('expense_types.index') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">Expense Types</a>
            @endif
            <a href="{{ route('expenses.index') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('expenses.index') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">Expenses</a>
        </div>

        <div x-show="collapsedDropdown === 'reports_payments'" x-cloak>
            <div class="px-4 py-2 border-b border-white/20 mb-1">
                <span class="text-[11px] font-bold text-white/90 uppercase tracking-wider">Payment Report</span>
            </div>
            @if(auth()->user()->hasPermission('Supplier Payment Report'))
                <a href="{{ route('supplier-payments.index') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('reports.payments.supplier') || request()->routeIs('supplier-payments.*') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">Supplier Payments</a>
            @endif
            @if(auth()->user()->hasPermission('Customer Payment Report'))
                <a href="{{ route('customer-payments.index') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('reports.payments.customer') || request()->routeIs('customer-payments.*') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">Customer Payments</a>
            @endif
        </div>

        <div x-show="collapsedDropdown === 'reports_data_entry'" x-cloak>
            <div class="px-4 py-2 border-b border-white/20 mb-1">
                <span class="text-[11px] font-bold text-white/90 uppercase tracking-wider">Data Entry Report</span>
            </div>
            @if(auth()->user()->hasPermission('Purchase Data Entry Report'))
                <a href="{{ route('reports.data-entry.purchases') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.purchases') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">Purchases</a>
            @endif
            @if(auth()->user()->hasPermission('Purchase Return Data Entry Report'))
                <a href="{{ route('reports.data-entry.purchases-return') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.purchases-return') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">Purchase Return</a>
            @endif
            @if(auth()->user()->hasPermission('Sale Data Entry Report'))
                <a href="{{ route('reports.data-entry.sales') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.sales') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">Sales</a>
            @endif
            @if(auth()->user()->hasPermission('Sale Return Data Entry Report'))
                <a href="{{ route('reports.data-entry.sales-return') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.sales-return') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">Sale Return</a>
            @endif
            @if(auth()->user()->hasPermission('Product Data Entry Report'))
                <a href="{{ route('reports.data-entry.products') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.products') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">Products</a>
            @endif
            @if(auth()->user()->hasPermission('Customer Data Entry Report'))
                <a href="{{ route('reports.data-entry.customers') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.customers') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">Customers</a>
            @endif
            @if(auth()->user()->hasPermission('Customer Payment Data Entry Report'))
                <a href="{{ route('reports.data-entry.customer-payments') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.customer-payments') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">Customer Payments</a>
            @endif
            @if(auth()->user()->hasPermission('Supplier Data Entry Report'))
                <a href="{{ route('reports.data-entry.suppliers') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.suppliers') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">Suppliers</a>
            @endif
            @if(auth()->user()->hasPermission('Supplier Payment Data Entry Report'))
                <a href="{{ route('reports.data-entry.supplier-payments') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.supplier-payments') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">Supplier Payments</a>
            @endif
            @if(auth()->user()->hasPermission('Report Data Entry Report Adjustment'))
                <a href="{{ route('reports.data-entry.adjustments') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.adjustments') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">Adjustments</a>
            @endif
            @if(auth()->user()->hasPermission('Expense Data Entry Report'))
                <a href="{{ route('reports.data-entry.expenses') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('reports.data-entry.expenses') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">Expenses</a>
            @endif
        </div>

        <div x-show="collapsedDropdown === 'extra'" x-cloak>
            <div class="px-4 py-2 border-b border-white/20 mb-1">
                <span class="text-[11px] font-bold text-white/90 uppercase tracking-wider">Extra</span>
            </div>
            <a href="{{ route('extra.application') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('extra.application') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">Application</a>
            @if(auth()->user()->hasPermission('System Server Info'))
                <a href="{{ route('extra.server') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('extra.server') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">Server</a>
            @endif
            @if(auth()->user()->hasPermission('System Optimize'))
                <a href="{{ route('extra.cache') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('extra.cache') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">Cache</a>
            @endif
            <a href="{{ route('extra.update') }}" class="block px-4 py-2 text-[12px] font-medium {{ request()->routeIs('extra.update') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">Update</a>
        </div>
    </div>
</template>
