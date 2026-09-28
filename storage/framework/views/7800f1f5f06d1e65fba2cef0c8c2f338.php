<?php
    $sidebarAppearance = app(\App\Services\AppearanceSettingService::class)->forSystem();
    $gradientFrom = $sidebarAppearance['colors'][0] ?? '#F15A22';
    $gradientTo = $sidebarAppearance['colors'][1] ?? '#0A0E27';
    $isSolidColor = (strtolower(trim($gradientFrom)) === strtolower(trim($gradientTo)));
?>

<!-- Sidebar Mobile Overlay -->
<div x-cloak x-show="sidebarOpen"
     x-transition:enter="transition-opacity ease-linear duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="sidebarOpen = false"
     class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40 lg:hidden">
</div>

<div x-ref="sidebarRoot" class="sidebar-panel-preload fixed inset-y-0 left-0 flex-none flex flex-col h-screen text-white z-50 border-r border-white/10 group/sidebar transition-all duration-300 ease-in-out lg:relative lg:translate-x-0"
     style="background: linear-gradient(180deg, <?php echo e($gradientFrom); ?> 0%, <?php echo e($gradientTo); ?> 100%);"
     :class="{
        'translate-x-0': sidebarOpen,
        '-translate-x-full': !sidebarOpen,
        'w-64': !sidebarCollapsed,
        'w-20': sidebarCollapsed
     }"
     x-data="{ 
        openDropdown: localStorage.getItem('sidebar_openDropdown') || null,
        tooltip: null,
        collapsedDropdown: null,
        sidebarScrollTop: 0,
        floatingMenuTop: 0,
        floatingMenuLeft: 88,
        floatingMenuArrow: 20,
        getCollapsedMenuEl() {
            return this.$refs.collapsedMenuPortal || null;
        },
        openCollapsedDropdown(id, btnEl) {
            this.tooltip = null;
            if (this.collapsedDropdown === id) {
                this.collapsedDropdown = null;
                return;
            }
            this.collapsedDropdown = id;
            this.$nextTick(() => {
                this.$nextTick(() => {
                    const menuEl = this.getCollapsedMenuEl();
                    if (btnEl && menuEl) {
                        this.updateFloatingPosition(btnEl, menuEl);
                    }
                });
            });
        },
        repositionCollapsedMenu() {
            if (!this.collapsedDropdown || !this.sidebarCollapsed) {
                return;
            }
            const scrollEl = this.$refs.sidebarScroll;
            const btnEl = scrollEl
                ? scrollEl.querySelector('[data-dropdown-id=\'' + this.collapsedDropdown + '\']')
                : null;
            const menuEl = this.getCollapsedMenuEl();
            if (btnEl && menuEl) {
                this.updateFloatingPosition(btnEl, menuEl);
            } else {
                this.collapsedDropdown = null;
            }
        },
        updateFloatingPosition(btnEl, menuEl) {
            if (!btnEl || !menuEl) return;

            const sidebarEl = this.$refs.sidebarRoot;
            const scrollContainer = btnEl.closest('.custom-scrollbar');
            const btnRect = btnEl.getBoundingClientRect();
            const sidebarRect = sidebarEl
                ? sidebarEl.getBoundingClientRect()
                : { right: 80, top: 0, bottom: window.innerHeight };

            if (scrollContainer) {
                const containerRect = scrollContainer.getBoundingClientRect();
                if (
                    btnRect.bottom < containerRect.top + 8 ||
                    btnRect.top > containerRect.bottom - 8
                ) {
                    this.collapsedDropdown = null;
                    return;
                }
            }

            const menuHeight = menuEl.offsetHeight || 120;
            const viewportHeight = window.innerHeight;
            const btnMidY = btnRect.top + btnRect.height / 2;

            let menuTop = btnMidY - menuHeight / 2;
            menuTop = Math.max(8, Math.min(menuTop, viewportHeight - menuHeight - 8));

            this.floatingMenuLeft = Math.round(sidebarRect.right + 8);
            this.floatingMenuTop = Math.round(menuTop);
            this.floatingMenuArrow = Math.max(
                12,
                Math.min(btnMidY - menuTop, menuHeight - 12)
            );
        },
        handleSidebarScroll(el) {
            this.sidebarScrollTop = el.scrollTop;
            this.tooltip = null;
            this.repositionCollapsedMenu();
        }
     }"
     @click.away="collapsedDropdown = null"
     @resize.window="repositionCollapsedMenu()"
     x-init="
        collapsedDropdown = null;
        tooltip = null;
        $watch('openDropdown', value => localStorage.setItem('sidebar_openDropdown', value)); 
        $watch('sidebarCollapsed', (newVal) => { if(newVal) { collapsedDropdown = null; tooltip = null; } else { collapsedDropdown = null; } });
        $watch('collapsedDropdown', () => { $nextTick(() => repositionCollapsedMenu()); });
        $dispatch('sidebar-toggled');
     "
     @sidebar-toggled.window="collapsedDropdown = null; tooltip = null; openDropdown = null">
    
    <!-- Sidebar Toggle Button (Desktop) -->
    <button @click="sidebarCollapsed = !sidebarCollapsed" 
            class="hidden lg:flex absolute -right-3 top-7 h-6 w-6 border-2 border-white/20 rounded-full items-center justify-center text-white hover:opacity-80 transition-all duration-300 z-[60] shadow-lg group/btn"
            style="background: linear-gradient(135deg, <?php echo e($gradientFrom); ?> 0%, <?php echo e($gradientTo); ?> 100%);"
            :class="sidebarCollapsed ? 'rotate-0' : ''">
        <div class="flex items-center justify-center transition-transform duration-300" :class="sidebarCollapsed ? 'rotate-180' : ''">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7" />
            </svg>
        </div>
    </button>
    <style>
        :root {
            --sidebar-gradient-from: <?php echo e($gradientFrom); ?>;
            --sidebar-gradient-to: <?php echo e($gradientTo); ?>;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: transparent;
            border-radius: 10px;
            transition: background 0.3s ease;
        }
        .group\/sidebar:hover .custom-scrollbar::-webkit-scrollbar-thumb {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: transparent !important;
        }
        /* For Firefox */
        .custom-scrollbar {
            scrollbar-width: none;
        }
        .group\/sidebar:hover .custom-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: transparent transparent;
        }
        /* Sidebar gradient accent styles */
        .sidebar-nav-item.active,
        .sidebar-nav-item:has(a.active),
        a.sidebar-active {
            background: <?php echo e($isSolidColor ? 'rgba(255, 255, 255, 0.15)' : 'linear-gradient(135deg, var(--sidebar-gradient-from) 0%, var(--sidebar-gradient-to) 100%)'); ?>;
        }

        <?php if($isSolidColor): ?>
        /* ── Solid-color-mode: stronger active indicators ── */
        /* Parent nav items that are active (expanded with children) */
        .sidebar-nav-item[style*="rgba(255, 255, 255"] {
            background: rgba(255, 255, 255, 0.20) !important;
            border-left: 3px solid #ffffff;
        }
        /* Sub-page links that are active (current page) */
        .sidebar-submenu a.bg-white\/20,
        .sidebar-submenu a[class*="bg-white/20"],
        /* Also target collapsed flyout menu items */
        .floating-menu a.bg-white\/20,
        .floating-menu a[class*="bg-white/20"] {
            background: rgba(255, 255, 255, 0.35) !important;
            padding-left: 16px;
            font-weight: 700;
        }
        <?php endif; ?>
    </style>

    <!-- Top: Fixed Logo & Close Button -->
    <div class="sidebar-header flex-none flex items-center h-20 px-5 border-b border-white/10" :class="sidebarCollapsed ? 'justify-center' : 'justify-between'">
        <a href="<?php echo e(route('dashboard')); ?>" class="sidebar-logo-link flex items-center" :class="sidebarCollapsed ? 'justify-center w-full' : ''">
            <div class="h-10 w-10 flex-none rounded-full overflow-hidden border-2 bg-white shadow-lg flex items-center justify-center transition-all duration-300" :class="sidebarCollapsed ? 'h-9 w-9' : 'h-10 w-10'" style="border-color: <?php echo e($gradientFrom); ?>">
                <?php if($generalSetting?->logo_dark): ?>
                    <img src="<?php echo e(asset($generalSetting->logo_dark)); ?>" class="h-full w-full object-cover" alt="Logo">
                <?php else: ?>
                    <?php if (isset($component)) { $__componentOriginal8892e718f3d0d7a916180885c6f012e7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8892e718f3d0d7a916180885c6f012e7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.application-logo','data' => ['class' => 'h-6 w-6 text-[#4634ff] fill-current']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('application-logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-6 w-6 text-[#4634ff] fill-current']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $attributes = $__attributesOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $component = $__componentOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__componentOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
                <?php endif; ?>
            </div>
            <span class="sidebar-expand-only ml-3 text-xl font-extrabold tracking-tighter uppercase text-white truncate"
                  x-transition:enter="transition ease-out duration-200"
                  x-transition:enter-start="opacity-0 -translate-x-4"
                  x-transition:enter-end="opacity-100 translate-x-0"><?php echo e($generalSetting->site_title ?? 'MGEDC'); ?></span>
        </a>

        <!-- Mobile Close Button -->
        <button @click="sidebarOpen = false" class="lg:hidden p-1 text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Middle: Scrollable Navigation Links -->
    <div x-ref="sidebarScroll" class="flex-1 overflow-y-auto py-6 custom-scrollbar"
         :class="sidebarCollapsed ? 'px-1.5 space-y-0.5' : 'px-3 space-y-1'" 
         x-init="
            // Save scroll position before page unload
            window.addEventListener('beforeunload', () => {
                localStorage.setItem('sidebar_scrollTop', $el.scrollTop);
            });
            
            // Restore scroll position on page load
            window.addEventListener('load', () => {
                setTimeout(() => {
                    let savedScroll = localStorage.getItem('sidebar_scrollTop');
                    if (savedScroll) {
                        $el.scrollTop = parseInt(savedScroll);
                    }
                }, 100);
            });
            
            // Also restore immediately after initialization
            setTimeout(() => {
                let savedScroll = localStorage.getItem('sidebar_scrollTop');
                if (savedScroll) {
                    $el.scrollTop = parseInt(savedScroll);
                }
            }, 50);
         "
         @scroll="handleSidebarScroll($el)">
        <!-- Tooltip (only visible when collapsed) -->
        <div x-cloak x-show="sidebarCollapsed && tooltip" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-x-2"
             x-transition:enter-end="opacity-100 translate-x-4"
             class="fixed z-[100] px-3 py-1.5 text-white text-[12px] font-bold rounded-md shadow-xl whitespace-nowrap pointer-events-none"
             :style="`background: linear-gradient(135deg, <?php echo e($gradientFrom); ?> 0%, <?php echo e($gradientTo); ?> 100%); top: ${tooltip?.y}px; left: ${($refs.sidebarRoot?.getBoundingClientRect().width || 80) + 8}px;`"
             x-text="tooltip?.name">
        </div>

        <!-- Dashboard -->
        <?php if(auth()->user()->hasPermission('Dashboard')): ?>
            <a href="<?php echo e(route('dashboard')); ?>" 
               @mouseenter="if(sidebarCollapsed && !collapsedDropdown) tooltip = { name: 'Dashboard', y: $el.getBoundingClientRect().top + 10 }"
               @mouseleave="tooltip = null"
               class="sidebar-nav-item flex items-center rounded-lg <?php echo e(request()->routeIs('dashboard') ? 'text-white shadow-md' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?> transition-all duration-200 group"
               :style="{ background: `<?php echo e(request()->routeIs('dashboard') ? ($isSolidColor ? 'rgba(255, 255, 255, 0.15)' : 'linear-gradient(135deg, ' . $gradientFrom . ' 0%, ' . $gradientTo . ' 100%)') : 'transparent'); ?>` }"
               :class="sidebarCollapsed ? '' : 'px-4 py-2.5'">
                <svg class="h-5 w-5 <?php echo e(request()->routeIs('dashboard') ? 'text-white' : 'text-white/60 group-hover:text-white'); ?>" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="sidebar-expand-only text-[13px] font-semibold">Dashboard</span>
            </a>
        <?php endif; ?>

        <!-- Group: MANAGE -->
        <?php
            $showManage = auth()->user()->hasPermission('All Sales') ||
                         auth()->user()->hasPermission('All Purchases') ||
                         auth()->user()->canSeeManageProductsMenu() ||
                         auth()->user()->hasPermission('All Customers') ||
                         auth()->user()->hasPermission('All Suppliers') ||
                         auth()->user()->hasPermission('All Staffs') ||
                         auth()->user()->hasPermission('All Adjustments') ||
                         auth()->user()->hasPermission('All Transfers') ||
                         auth()->user()->hasPermission('All Expenses');
        ?>

        <?php if($showManage): ?>
            <div class="sidebar-expand-only pt-4 pb-2 px-4 text-[11px] font-bold text-white/80 uppercase tracking-[0.2em]">
                MANAGE
            </div>

            <!-- Manage Sales -->
            <?php if(auth()->user()->hasPermission('All Sales')): ?>
                <div class="relative">
                    <button data-dropdown-id="sales" @click.stop="if(sidebarCollapsed) { openCollapsedDropdown('sales', $el) } else { openDropdown = (openDropdown === 'sales' ? null : 'sales') }" 
                            @mouseenter="if(sidebarCollapsed && !collapsedDropdown) tooltip = { name: 'Manage Sales', y: $el.getBoundingClientRect().top + 10 }"
                            @mouseleave="tooltip = null"
                            class="sidebar-nav-item flex items-center w-full text-left rounded-lg transition-all duration-200 <?php echo e(request()->is('sales*') ? 'text-white shadow-md' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>"
                            :style="{ background: `<?php echo e(request()->is('sales*') ? ($isSolidColor ? 'rgba(255, 255, 255, 0.15)' : 'linear-gradient(135deg, ' . $gradientFrom . ' 0%, ' . $gradientTo . ' 100%)') : 'transparent'); ?>` }"
                            :class="sidebarCollapsed ? '' : 'justify-between px-4 py-2.5'">
                        <span class="flex items-center">
                            <svg class="h-5 w-5 <?php echo e(request()->is('sales*') ? 'text-white' : 'text-white/60'); ?>" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span class="sidebar-expand-only text-[13px] font-semibold">Manage Sales</span>
                        </span>
                        <svg class="sidebar-expand-only h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': openDropdown === 'sales' }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <!-- Expanded Dropdown -->
                    <div x-show="!sidebarCollapsed && openDropdown === 'sales'" x-collapse class="sidebar-submenu space-y-1 mt-1" style="display: none;">
                        <a href="<?php echo e(route('sales.all')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e(request()->routeIs('sales.all') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                            <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                <circle cx="3" cy="3" r="3" />
                            </svg>
                            All Sales
                        </a>
                        <?php if(auth()->user()->hasPermission('All Sales Return')): ?>
                            <a href="<?php echo e(route('sales.return')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e(request()->routeIs('sales.return*') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                                <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                    <circle cx="3" cy="3" r="3" />
                                </svg>
                                Sales Return
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Manage Purchases -->
            <?php if(auth()->user()->hasPermission('All Purchases')): ?>
                <div class="relative">
                    <button data-dropdown-id="purchases" @click.stop="if(sidebarCollapsed) { openCollapsedDropdown('purchases', $el) } else { openDropdown = (openDropdown === 'purchases' ? null : 'purchases') }" 
                            @mouseenter="if(sidebarCollapsed && !collapsedDropdown) tooltip = { name: 'Manage Purchases', y: $el.getBoundingClientRect().top + 10 }"
                            @mouseleave="tooltip = null"
                            class="sidebar-nav-item flex items-center w-full text-left rounded-lg transition-all duration-200 <?php echo e(request()->is('purchases*') ? 'text-white shadow-md' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>"
                            :style="{ background: `<?php echo e(request()->is('purchases*') ? ($isSolidColor ? 'rgba(255, 255, 255, 0.15)' : 'linear-gradient(135deg, ' . $gradientFrom . ' 0%, ' . $gradientTo . ' 100%)') : 'transparent'); ?>` }"
                            :class="sidebarCollapsed ? '' : 'justify-between px-4 py-2.5'">
                        <span class="flex items-center">
                            <svg class="h-5 w-5 <?php echo e(request()->is('purchases*') ? 'text-white' : 'text-white/60'); ?>" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            <span class="sidebar-expand-only text-[13px] font-semibold">Manage Purchases</span>
                        </span>
                        <svg class="sidebar-expand-only h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': openDropdown === 'purchases' }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <!-- Expanded Dropdown -->
                    <div x-show="!sidebarCollapsed && openDropdown === 'purchases'" x-collapse class="sidebar-submenu space-y-1 mt-1" style="display: none;">
                        <a href="<?php echo e(route('purchases.all')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e(request()->routeIs('purchases.all') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                            <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                <circle cx="3" cy="3" r="3" />
                            </svg>
                            All Purchases
                        </a>
                        <?php if(auth()->user()->hasPermission('All Purchase Return')): ?>
                            <a href="<?php echo e(route('purchases.return')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e(request()->routeIs('purchases.return*') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                                <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                    <circle cx="3" cy="3" r="3" />
                                </svg>
                                Purchases Return
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Manage Products (menu if user can access Products and/or Categories, Brands, Units) -->
            <?php if(auth()->user()->canSeeManageProductsMenu()): ?>
                <div class="relative">
                    <button data-dropdown-id="products" @click.stop="if(sidebarCollapsed) { openCollapsedDropdown('products', $el) } else { openDropdown = (openDropdown === 'products' ? null : 'products') }" 
                            @mouseenter="if(sidebarCollapsed && !collapsedDropdown) tooltip = { name: 'Manage Products', y: $el.getBoundingClientRect().top + 10 }"
                            @mouseleave="tooltip = null"
                            class="sidebar-nav-item flex items-center w-full text-left rounded-lg transition-all duration-200 <?php echo e(request()->is('products*') ? 'text-white shadow-md' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>"
                            :style="{ background: `<?php echo e(request()->is('products*') ? ($isSolidColor ? 'rgba(255, 255, 255, 0.15)' : 'linear-gradient(135deg, ' . $gradientFrom . ' 0%, ' . $gradientTo . ' 100%)') : 'transparent'); ?>` }"
                            :class="sidebarCollapsed ? '' : 'justify-between px-4 py-2.5'">
                        <span class="flex items-center">
                            <svg class="h-5 w-5 <?php echo e(request()->is('products*') ? 'text-white' : 'text-white/60'); ?>" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <span class="sidebar-expand-only text-[13px] font-semibold">Manage Products</span>
                        </span>
                        <svg class="sidebar-expand-only h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': openDropdown === 'products' }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <!-- Expanded Dropdown -->
                    <div x-show="!sidebarCollapsed && openDropdown === 'products'" x-collapse class="sidebar-submenu space-y-1 mt-1" style="display: none;">
                        <?php if(auth()->user()->hasPermission('All Product')): ?>
                            <a href="<?php echo e(route('products.all')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e(request()->routeIs('products.*') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                                <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                    <circle cx="3" cy="3" r="3" />
                                </svg>
                                Products
                            </a>
                            <a href="<?php echo e(route('batches.index')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e(request()->routeIs('batches.*') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                                <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                    <circle cx="3" cy="3" r="3" />
                                </svg>
                                Batch Management
                            </a>
                        <?php endif; ?>
                        <?php if(auth()->user()->hasPermission('All Categorys')): ?>
                            <a href="<?php echo e(route('categories.index')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e(request()->routeIs('categories.*') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                                <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                    <circle cx="3" cy="3" r="3" />
                                </svg>
                                Categories
                            </a>
                        <?php endif; ?>
                        <?php if(auth()->user()->hasPermission('All Brands')): ?>
                            <a href="<?php echo e(route('brands.index')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e(request()->routeIs('brands.*') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                                <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                    <circle cx="3" cy="3" r="3" />
                                </svg>
                                Brands
                            </a>
                        <?php endif; ?>
                        <?php if(auth()->user()->hasPermission('All Unit')): ?>
                            <a href="<?php echo e(route('units.index')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e(request()->routeIs('units.*') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                                <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                    <circle cx="3" cy="3" r="3" />
                                </svg>
                                Units
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Manage Customers -->
            <?php if(auth()->user()->hasPermission('All Customers') || auth()->user()->hasPermission('All Categorys')): ?>
                <div class="relative">
                    <button data-dropdown-id="customers" @click.stop="if(sidebarCollapsed) { openCollapsedDropdown('customers', $el) } else { openDropdown = (openDropdown === 'customers' ? null : 'customers') }" 
                            @mouseenter="if(sidebarCollapsed && !collapsedDropdown) tooltip = { name: 'Manage Customers', y: $el.getBoundingClientRect().top + 10 }"
                            @mouseleave="tooltip = null"
                            class="sidebar-nav-item flex items-center w-full text-left rounded-lg transition-all duration-200 <?php echo e(request()->is('customers*') || request()->is('store-categories*') || request()->is('customer-routes*') ? 'text-white shadow-md' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>"
                            :style="{ background: `<?php echo e(request()->is('customers*') || request()->is('store-categories*') || request()->is('customer-routes*') ? ($isSolidColor ? 'rgba(255, 255, 255, 0.15)' : 'linear-gradient(135deg, ' . $gradientFrom . ' 0%, ' . $gradientTo . ' 100%)') : 'transparent'); ?>` }"
                            :class="sidebarCollapsed ? '' : 'justify-between px-4 py-2.5'">
                        <span class="flex items-center">
                            <svg class="h-5 w-5 <?php echo e(request()->is('customers*') || request()->is('store-categories*') || request()->is('customer-routes*') ? 'text-white' : 'text-white/60'); ?>" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span class="sidebar-expand-only text-[13px] font-semibold">Manage Customers</span>
                        </span>
                        <svg class="sidebar-expand-only h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': openDropdown === 'customers' }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <!-- Expanded Dropdown -->
                    <div x-show="!sidebarCollapsed && openDropdown === 'customers'" x-collapse class="sidebar-submenu space-y-1 mt-1" style="display: none;">
                        <?php if(auth()->user()->hasPermission('All Customers')): ?>
                            <a href="<?php echo e(route('customers.index')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e(request()->routeIs('customers.index') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                                <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                    <circle cx="3" cy="3" r="3" />
                                </svg>
                                Customers
                            </a>
                        <?php endif; ?>
                        <?php if(auth()->user()->hasPermission('All Categorys')): ?>
                            <a href="<?php echo e(route('store-categories.index')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e(request()->routeIs('store-categories.*') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                                <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                    <circle cx="3" cy="3" r="3" />
                                </svg>
                                Store Categories
                            </a>
                        <?php endif; ?>
                        <?php if(auth()->user()->hasPermission('All Customers')): ?>
                            <a href="<?php echo e(route('customer-routes.index')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e(request()->routeIs('customer-routes.*') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                                <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                    <circle cx="3" cy="3" r="3" />
                                </svg>
                                Delivery Routes
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Manage Suppliers -->
            <?php if(auth()->user()->hasPermission('All Suppliers')): ?>
                <a href="<?php echo e(route('suppliers.index')); ?>" 
                   @mouseenter="if(sidebarCollapsed && !collapsedDropdown) tooltip = { name: 'Manage Suppliers', y: $el.getBoundingClientRect().top + 10 }"
                   @mouseleave="tooltip = null"
                   class="sidebar-nav-item flex items-center rounded-lg <?php echo e(request()->routeIs('suppliers.index') ? 'text-white shadow-md' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?> transition-all duration-200 group"
                   :style="{ background: `<?php echo e(request()->routeIs('suppliers.index') ? ($isSolidColor ? 'rgba(255, 255, 255, 0.15)' : 'linear-gradient(135deg, ' . $gradientFrom . ' 0%, ' . $gradientTo . ' 100%)') : 'transparent'); ?>` }"
                   :class="sidebarCollapsed ? '' : 'px-4 py-2.5'">
                    <svg class="h-5 w-5 <?php echo e(request()->routeIs('suppliers.index') ? 'text-white' : 'text-white/60 group-hover:text-white'); ?>" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span class="sidebar-expand-only text-[13px] font-semibold">Manage Suppliers</span>
                </a>
            <?php endif; ?>

            <!-- Manage Staff -->
            <?php if(auth()->user()->hasPermission('All Staffs')): ?>
                <div class="relative">
                    <button data-dropdown-id="staff" @click.stop="if(sidebarCollapsed) { openCollapsedDropdown('staff', $el) } else { openDropdown = (openDropdown === 'staff' ? null : 'staff') }" 
                            @mouseenter="if(sidebarCollapsed && !collapsedDropdown) tooltip = { name: 'Manage Staff', y: $el.getBoundingClientRect().top + 10 }"
                            @mouseleave="tooltip = null"
                            class="sidebar-nav-item flex items-center w-full text-left rounded-lg transition-all duration-200 <?php echo e(request()->is('staff*') ? 'text-white shadow-md' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>"
                            :style="{ background: `<?php echo e(request()->is('staff*') ? ($isSolidColor ? 'rgba(255, 255, 255, 0.15)' : 'linear-gradient(135deg, ' . $gradientFrom . ' 0%, ' . $gradientTo . ' 100%)') : 'transparent'); ?>` }"
                            :class="sidebarCollapsed ? '' : 'justify-between px-4 py-2.5'">
                        <span class="flex items-center">
                            <svg class="h-5 w-5 <?php echo e(request()->is('staff*') ? 'text-white' : 'text-white/60'); ?>" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span class="sidebar-expand-only text-[13px] font-semibold">Manage Staff</span>
                        </span>
                        <svg class="sidebar-expand-only h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': openDropdown === 'staff' }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <!-- Expanded Dropdown -->
                    <div x-show="!sidebarCollapsed && openDropdown === 'staff'" x-collapse class="sidebar-submenu space-y-1 mt-1" style="display: none;">
                        <a href="<?php echo e(route('staff.index')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e(request()->routeIs('staff.index') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                            <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                <circle cx="3" cy="3" r="3" />
                            </svg>
                            All Staff
                        <a href="<?php echo e(route('staff.performance.dashboard')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e(request()->routeIs('staff.performance.dashboard') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                            <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                <circle cx="3" cy="3" r="3" />
                            </svg>
                            Performance
                        </a>

                        <?php if(auth()->user()->hasPermission('Roles Index')): ?>
                            <a href="<?php echo e(route('staff.roles.index')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e(request()->routeIs('staff.roles.index') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                                <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                    <circle cx="3" cy="3" r="3" />
                                </svg>
                                Roles
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Adjustments -->
            <?php if(auth()->user()->hasPermission('All Adjustments')): ?>
                <a href="<?php echo e(route('adjustments.index')); ?>" 
                   @mouseenter="if(sidebarCollapsed && !collapsedDropdown) tooltip = { name: 'Adjustments', y: $el.getBoundingClientRect().top + 10 }"
                   @mouseleave="tooltip = null"
                   class="sidebar-nav-item flex items-center rounded-lg <?php echo e(request()->routeIs('adjustments.index') ? 'text-white shadow-md' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?> transition-all duration-200 group"
                   :style="{ background: `<?php echo e(request()->routeIs('adjustments.index') ? ($isSolidColor ? 'rgba(255, 255, 255, 0.15)' : 'linear-gradient(135deg, ' . $gradientFrom . ' 0%, ' . $gradientTo . ' 100%)') : 'transparent'); ?>` }"
                   :class="sidebarCollapsed ? '' : 'px-4 py-2.5'">
                    <svg class="h-5 w-5 <?php echo e(request()->routeIs('adjustments.index') ? 'text-white' : 'text-white/60 group-hover:text-white'); ?>" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                    <span class="sidebar-expand-only text-[13px] font-semibold">Adjustments</span>
                </a>
            <?php endif; ?>

            <!-- Manage Expenses -->
            <?php if(auth()->user()->hasPermission('All Expenses')): ?>
                <div class="relative">
                    <button data-dropdown-id="expenses" @click.stop="if(sidebarCollapsed) { openCollapsedDropdown('expenses', $el) } else { openDropdown = (openDropdown === 'expenses' ? null : 'expenses') }" 
                            @mouseenter="if(sidebarCollapsed && !collapsedDropdown) tooltip = { name: 'Manage Expenses', y: $el.getBoundingClientRect().top + 10 }"
                            @mouseleave="tooltip = null"
                            class="sidebar-nav-item flex items-center w-full text-left rounded-lg transition-all duration-200 <?php echo e(request()->is('expenses*') ? 'text-white shadow-md' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>"
                            :style="{ background: `<?php echo e(request()->is('expenses*') ? ($isSolidColor ? 'rgba(255, 255, 255, 0.15)' : 'linear-gradient(135deg, ' . $gradientFrom . ' 0%, ' . $gradientTo . ' 100%)') : 'transparent'); ?>` }"
                            :class="sidebarCollapsed ? '' : 'justify-between px-4 py-2.5'">
                        <span class="flex items-center">
                            <svg class="h-5 w-5 <?php echo e(request()->is('expenses*') ? 'text-white' : 'text-white/60'); ?>" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span class="sidebar-expand-only text-[13px] font-semibold">Manage Expenses</span>
                        </span>
                        <svg class="sidebar-expand-only h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': openDropdown === 'expenses' }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <!-- Expanded Dropdown -->
                    <div x-show="!sidebarCollapsed && openDropdown === 'expenses'" x-collapse class="sidebar-submenu space-y-1 mt-1" style="display: none;">
                        <?php if(auth()->user()->hasPermission('All Expense Types')): ?>
                            <a href="<?php echo e(route('expense_types.index')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e(request()->routeIs('expense_types.index') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                                <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                    <circle cx="3" cy="3" r="3" />
                                </svg>
                                Expense Types
                            </a>
                        <?php endif; ?>
                        <a href="<?php echo e(route('expenses.index')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e(request()->routeIs('expenses.index') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                            <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                <circle cx="3" cy="3" r="3" />
                            </svg>
                            Expenses
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>



            <!-- Group: REPORT -->
             <?php
                 $showReport = auth()->user()->hasPermission('Supplier Payment Report') || 
                              auth()->user()->hasPermission('Customer Payment Report') || 
                              auth()->user()->hasPermission('Stock Report') || 
                              auth()->user()->hasPermission('Purchase Data Entry Report'); // Data entry start
             ?>
 
             <?php if($showReport): ?>
                 <div class="sidebar-expand-only pt-6 pb-2 px-4 text-[11px] font-bold text-white/80 uppercase tracking-[0.2em]">
                     REPORT
                 </div>

             <!-- Generated Reports (Sales, Purchasing, Analytics) -->
             <a href="<?php echo e(route('reports.generated')); ?>" 
                @mouseenter="if(sidebarCollapsed && !collapsedDropdown) tooltip = { name: 'Generated Reports', y: $el.getBoundingClientRect().top + 10 }"
                @mouseleave="tooltip = null"
                class="sidebar-nav-item flex items-center rounded-lg <?php echo e(request()->routeIs('reports.generated*') ? 'text-white shadow-md' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?> transition-all duration-200 group"
                :style="{ background: `<?php echo e(request()->routeIs('reports.generated*') ? ($isSolidColor ? 'rgba(255, 255, 255, 0.15)' : 'linear-gradient(135deg, ' . $gradientFrom . ' 0%, ' . $gradientTo . ' 100%)') : 'transparent'); ?>` }"
                :class="sidebarCollapsed ? '' : 'px-4 py-2.5'">
                 <svg class="h-5 w-5 <?php echo e(request()->routeIs('reports.generated*') ? 'text-white' : 'text-white/60 group-hover:text-white'); ?>" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                 </svg>
                 <span class="sidebar-expand-only text-[13px] font-semibold">Generated Reports</span>
             </a>
  
             <!-- Payment Report -->
            <?php if(auth()->user()->hasPermission('Supplier Payment Report') || auth()->user()->hasPermission('Customer Payment Report')): ?>
                <?php
                    $isPaymentReportActive = request()->is('reports/payments*') || request()->is('supplier-payments*') || request()->is('customer-payments*');
                    $isSupplierPaymentActive = request()->routeIs('reports.payments.supplier') || request()->routeIs('supplier-payments.*');
                    $isCustomerPaymentActive = request()->routeIs('reports.payments.customer') || request()->routeIs('customer-payments.*');
                ?>
                <div class="relative">
                    <button data-dropdown-id="reports_payments" @click.stop="if(sidebarCollapsed) { openCollapsedDropdown('reports_payments', $el) } else { openDropdown = (openDropdown === 'reports_payments' ? null : 'reports_payments') }" 
                            @mouseenter="if(sidebarCollapsed && !collapsedDropdown) tooltip = { name: 'Payment Report', y: $el.getBoundingClientRect().top + 10 }"
                            @mouseleave="tooltip = null"
                            class="sidebar-nav-item flex items-center w-full text-left rounded-lg transition-all duration-200 <?php echo e($isPaymentReportActive ? 'text-white shadow-md' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>"
                            :style="{ background: `<?php echo e($isPaymentReportActive ? ($isSolidColor ? 'rgba(255, 255, 255, 0.15)' : 'linear-gradient(135deg, ' . $gradientFrom . ' 0%, ' . $gradientTo . ' 100%)') : 'transparent'); ?>` }"
                            :class="sidebarCollapsed ? '' : 'justify-between px-4 py-2.5'">
                        <span class="flex items-center">
                            <svg class="h-5 w-5 <?php echo e($isPaymentReportActive ? 'text-white' : 'text-white/60'); ?>" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="sidebar-expand-only text-[13px] font-semibold">Payment Report</span>
                        </span>
                        <svg class="sidebar-expand-only h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': openDropdown === 'reports_payments' }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <!-- Expanded Dropdown -->
                    <div x-show="!sidebarCollapsed && openDropdown === 'reports_payments'" x-collapse class="sidebar-submenu space-y-1 mt-1" style="display: none;">
                        <?php if(auth()->user()->hasPermission('Supplier Payment Report')): ?>
                            <a href="<?php echo e(route('supplier-payments.index')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e($isSupplierPaymentActive ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                                <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                    <circle cx="3" cy="3" r="3" />
                                </svg>
                                Supplier Payments
                            </a>
                        <?php endif; ?>
                        <?php if(auth()->user()->hasPermission('Customer Payment Report')): ?>
                            <a href="<?php echo e(route('customer-payments.index')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e($isCustomerPaymentActive ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                                <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                    <circle cx="3" cy="3" r="3" />
                                </svg>
                                Customer Payments
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Stock Report -->
            <?php if(auth()->user()->hasPermission('Stock Report')): ?>
                <a href="<?php echo e(route('reports.stock')); ?>" 
                   @mouseenter="if(sidebarCollapsed && !collapsedDropdown) tooltip = { name: 'Stock Report', y: $el.getBoundingClientRect().top + 10 }"
                   @mouseleave="tooltip = null"
                   class="sidebar-nav-item flex items-center rounded-lg <?php echo e(request()->routeIs('reports.stock') ? 'text-white shadow-md' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?> transition-all duration-200 group"
                   :style="{ background: `<?php echo e(request()->routeIs('reports.stock') ? ($isSolidColor ? 'rgba(255, 255, 255, 0.15)' : 'linear-gradient(135deg, ' . $gradientFrom . ' 0%, ' . $gradientTo . ' 100%)') : 'transparent'); ?>` }"
                   :class="sidebarCollapsed ? '' : 'px-4 py-2.5'">
                    <svg class="h-5 w-5 <?php echo e(request()->routeIs('reports.stock') ? 'text-white' : 'text-white/60 group-hover:text-white'); ?>" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01" />
                    </svg>
                    <span class="sidebar-expand-only text-[13px] font-semibold">Stock Report</span>
                </a>
            <?php endif; ?>

            <!-- Data Entry Report -->
            <?php
                $showDataEntry = auth()->user()->hasPermission('Purchase Data Entry Report') || 
                                auth()->user()->hasPermission('Sale Data Entry Report') || 
                                auth()->user()->hasPermission('Product Data Entry Report') || 
                                auth()->user()->hasPermission('Customer Data Entry Report') || 
                                auth()->user()->hasPermission('Supplier Data Entry Report') || 
                                auth()->user()->hasPermission('Expense Data Entry Report') || 
                                auth()->user()->hasPermission('Transfer Data Entry Report') || 
                                auth()->user()->hasPermission('Report Data Entry Report Adjustment');
            ?>

            <?php if($showDataEntry): ?>
                <div class="relative">
                    <button data-dropdown-id="reports_data_entry" @click.stop="if(sidebarCollapsed) { openCollapsedDropdown('reports_data_entry', $el) } else { openDropdown = (openDropdown === 'reports_data_entry' ? null : 'reports_data_entry') }" 
                            @mouseenter="if(sidebarCollapsed && !collapsedDropdown) tooltip = { name: 'Data Entry Report', y: $el.getBoundingClientRect().top + 10 }"
                            @mouseleave="tooltip = null"
                            class="sidebar-nav-item flex items-center w-full text-left rounded-lg transition-all duration-200 <?php echo e(request()->is('reports/data-entry*') ? 'text-white shadow-md' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>"
                            :style="{ background: `<?php echo e(request()->is('reports/data-entry*') ? ($isSolidColor ? 'rgba(255, 255, 255, 0.15)' : 'linear-gradient(135deg, ' . $gradientFrom . ' 0%, ' . $gradientTo . ' 100%)') : 'transparent'); ?>` }"
                            :class="sidebarCollapsed ? '' : 'justify-between px-4 py-2.5'">
                        <span class="flex items-center">
                            <svg class="h-5 w-5 <?php echo e(request()->is('reports/data-entry*') ? 'text-white' : 'text-white/60'); ?>" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                            </svg>
                            <span class="sidebar-expand-only text-[13px] font-semibold">Data Entry Report</span>
                        </span>
                        <svg class="sidebar-expand-only h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': openDropdown === 'reports_data_entry' }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <!-- Expanded Dropdown -->
                    <div x-show="!sidebarCollapsed && openDropdown === 'reports_data_entry'" x-collapse class="sidebar-submenu space-y-1 mt-1" style="display: none;">
                        <?php if(auth()->user()->hasPermission('Purchase Data Entry Report')): ?>
                            <a href="<?php echo e(route('reports.data-entry.purchases')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e(request()->routeIs('reports.data-entry.purchases') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                                <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                    <circle cx="3" cy="3" r="3" />
                                </svg>
                                Purchases
                            </a>
                        <?php endif; ?>
                        <?php if(auth()->user()->hasPermission('Purchase Return Data Entry Report')): ?>
                            <a href="<?php echo e(route('reports.data-entry.purchases-return')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e(request()->routeIs('reports.data-entry.purchases-return') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                                <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                    <circle cx="3" cy="3" r="3" />
                                </svg>
                                Purchase Return
                            </a>
                        <?php endif; ?>
                        <?php if(auth()->user()->hasPermission('Sale Data Entry Report')): ?>
                            <a href="<?php echo e(route('reports.data-entry.sales')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e(request()->routeIs('reports.data-entry.sales') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                                <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                    <circle cx="3" cy="3" r="3" />
                                </svg>
                                Sales
                            </a>
                        <?php endif; ?>
                        <?php if(auth()->user()->hasPermission('Sale Return Data Entry Report')): ?>
                            <a href="<?php echo e(route('reports.data-entry.sales-return')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e(request()->routeIs('reports.data-entry.sales-return') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                                <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                    <circle cx="3" cy="3" r="3" />
                                </svg>
                                Sale Return
                            </a>
                        <?php endif; ?>
                        <?php if(auth()->user()->hasPermission('Product Data Entry Report')): ?>
                            <a href="<?php echo e(route('reports.data-entry.products')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e(request()->routeIs('reports.data-entry.products') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                                <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                    <circle cx="3" cy="3" r="3" />
                                </svg>
                                Products
                            </a>
                        <?php endif; ?>
                        <?php if(auth()->user()->hasPermission('Customer Data Entry Report')): ?>
                            <a href="<?php echo e(route('reports.data-entry.customers')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e(request()->routeIs('reports.data-entry.customers') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                                <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                    <circle cx="3" cy="3" r="3" />
                                </svg>
                                Customers
                            </a>
                        <?php endif; ?>
                        <?php if(auth()->user()->hasPermission('Customer Payment Data Entry Report')): ?>
                            <a href="<?php echo e(route('reports.data-entry.customer-payments')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e(request()->routeIs('reports.data-entry.customer-payments') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                                <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                    <circle cx="3" cy="3" r="3" />
                                </svg>
                                Customer Payments
                            </a>
                        <?php endif; ?>
                        <?php if(auth()->user()->hasPermission('Supplier Data Entry Report')): ?>
                            <a href="<?php echo e(route('reports.data-entry.suppliers')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e(request()->routeIs('reports.data-entry.suppliers') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                                <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                    <circle cx="3" cy="3" r="3" />
                                </svg>
                                Suppliers
                            </a>
                        <?php endif; ?>
                        <?php if(auth()->user()->hasPermission('Supplier Payment Data Entry Report')): ?>
                            <a href="<?php echo e(route('reports.data-entry.supplier-payments')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e(request()->routeIs('reports.data-entry.supplier-payments') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                                <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                    <circle cx="3" cy="3" r="3" />
                                </svg>
                                Supplier Payments
                            </a>
                        <?php endif; ?>
                        <?php if(auth()->user()->hasPermission('Report Data Entry Report Adjustment')): ?>
                            <a href="<?php echo e(route('reports.data-entry.adjustments')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e(request()->routeIs('reports.data-entry.adjustments') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                                <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                    <circle cx="3" cy="3" r="3" />
                                </svg>
                                Adjustments
                            </a>
                        <?php endif; ?>
                        <?php if(auth()->user()->hasPermission('Expense Data Entry Report')): ?>
                            <a href="<?php echo e(route('reports.data-entry.expenses')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e(request()->routeIs('reports.data-entry.expenses') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                                <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                    <circle cx="3" cy="3" r="3" />
                                </svg>
                                Expenses
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php
            $showSetting = auth()->user()->hasPermission('Setting Index') || 
                          auth()->user()->hasPermission('System Info');
        ?>

        <?php if($showSetting): ?>
            <div class="sidebar-expand-only pt-6 pb-2 px-4 text-[11px] font-bold text-white/80 uppercase tracking-[0.2em]">
                SETTING
            </div>

            <!-- System Setting -->
            <?php if(auth()->user()->hasPermission('Setting Index')): ?>
                <a href="<?php echo e(route('settings.index')); ?>" 
                   @mouseenter="if(sidebarCollapsed && !collapsedDropdown) tooltip = { name: 'System Setting', y: $el.getBoundingClientRect().top + 10 }"
                   @mouseleave="tooltip = null"
                   class="sidebar-nav-item flex items-center rounded-lg <?php echo e(request()->routeIs('settings.*') ? 'text-white shadow-md' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?> transition-all duration-200 group"
                   :style="{ background: `<?php echo e(request()->routeIs('settings.*') ? ($isSolidColor ? 'rgba(255, 255, 255, 0.15)' : 'linear-gradient(135deg, ' . $gradientFrom . ' 0%, ' . $gradientTo . ' 100%)') : 'transparent'); ?>` }"
                   :class="sidebarCollapsed ? '' : 'px-4 py-2.5'">
                    <svg class="h-5 w-5 <?php echo e(request()->routeIs('settings.*') ? 'text-white' : 'text-white/60 group-hover:text-white'); ?>" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="sidebar-expand-only text-[13px] font-semibold">System Setting</span>
                </a>
            <?php endif; ?>

            <!-- Extra -->
            <?php if(auth()->user()->hasPermission('System Info')): ?>
                <div class="relative">
                    <button data-dropdown-id="extra" @click.stop="if(sidebarCollapsed) { openCollapsedDropdown('extra', $el) } else { openDropdown = (openDropdown === 'extra' ? null : 'extra') }" 
                            @mouseenter="if(sidebarCollapsed && !collapsedDropdown) tooltip = { name: 'Extra', y: $el.getBoundingClientRect().top + 10 }"
                            @mouseleave="tooltip = null"
                            class="sidebar-nav-item flex items-center w-full text-left rounded-lg transition-all duration-200 <?php echo e(request()->is('extra*') ? 'text-white shadow-md' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>"
                            :style="{ background: `<?php echo e(request()->is('extra*') ? ($isSolidColor ? 'rgba(255, 255, 255, 0.15)' : 'linear-gradient(135deg, ' . $gradientFrom . ' 0%, ' . $gradientTo . ' 100%)') : 'transparent'); ?>` }"
                            :class="sidebarCollapsed ? '' : 'justify-between px-4 py-2.5'">
                        <span class="flex items-center">
                            <svg class="h-5 w-5 <?php echo e(request()->is('extra*') ? 'text-white' : 'text-white/60'); ?>" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                            </svg>
                            <span class="sidebar-expand-only text-[13px] font-semibold">Extra</span>
                        </span>
                        <svg class="sidebar-expand-only h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': openDropdown === 'extra' }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <!-- Expanded Dropdown -->
                    <div x-show="!sidebarCollapsed && openDropdown === 'extra'" x-collapse class="sidebar-submenu space-y-1 mt-1" style="display: none;">
                        <a href="<?php echo e(route('extra.application')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e(request()->routeIs('extra.application') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                            <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                <circle cx="3" cy="3" r="3" />
                            </svg>
                            Application
                        </a>
                        <?php if(auth()->user()->hasPermission('System Server Info')): ?>
                            <a href="<?php echo e(route('extra.server')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e(request()->routeIs('extra.server') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                                <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                    <circle cx="3" cy="3" r="3" />
                                </svg>
                                Server
                            </a>
                        <?php endif; ?>
                        <?php if(auth()->user()->hasPermission('System Optimize')): ?>
                            <a href="<?php echo e(route('extra.cache')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e(request()->routeIs('extra.cache') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                                <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                    <circle cx="3" cy="3" r="3" />
                                </svg>
                                Cache
                            </a>
                        <?php endif; ?>
                        <a href="<?php echo e(route('extra.update')); ?>" class="flex items-center py-2 px-4 text-[12px] font-medium rounded-md <?php echo e(request()->routeIs('extra.update') ? 'text-white bg-white/20 font-bold' : 'text-white/70 hover:bg-white/10 hover:text-white'); ?>">
                            <svg class="h-1.5 w-1.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 6 6">
                                <circle cx="3" cy="3" r="3" />
                            </svg>
                            Update
                        </a>
                    </div>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>

    <?php echo $__env->make('layouts.partials.collapsed-sidebar-flyout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php /**PATH C:\laragon\www\GE-System-1.1\resources\views/layouts/navigation.blade.php ENDPATH**/ ?>