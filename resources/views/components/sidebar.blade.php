{{-- ═══════════ SIDEBAR DESKTOP ═══════════ --}}
<aside class="fixed top-0 left-0 h-screen bg-white border-r border-gray-200 z-40 transition-all duration-300 hidden lg:block"
       :class="sidebarOpen ? 'w-64' : 'w-20'">

    {{-- Logo --}}
    <div class="flex items-center h-16 px-4 border-b border-gray-200">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-primary-600 flex items-center justify-center flex-shrink-0">
                <i data-lucide="wrench" class="w-6 h-6 text-white"></i>
            </div>
            <span x-show="sidebarOpen" x-cloak class="font-bold text-lg text-gray-800">Atelier Pro</span>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="mt-4 px-3 space-y-1 overflow-y-auto" style="max-height: calc(100vh - 5rem);">
        @php
            $menu = [
                ['route' => 'dashboard', 'icon' => 'layout-dashboard', 'label' => 'Tableau de bord', 'module' => 'dashboard'],
                ['route' => 'admin.users.index', 'icon' => 'users', 'label' => 'Utilisateurs', 'module' => 'users', 'role' => 'admin'],
                ['route' => 'admin.activity-logs', 'icon' => 'scroll-text', 'label' => 'Journal d\'activité', 'module' => 'activity_logs', 'role' => 'admin'],
                // Module 3
                ['route' => 'settings.index', 'icon' => 'building-2', 'label' => 'Paramètres Société', 'module' => 'settings', 'role' => 'admin'],
                // Module 4
                ['route' => 'employees.index', 'icon' => 'hard-hat', 'label' => 'Employés', 'module' => 'employees'],
                // Module 5
                ['route' => 'clients.index', 'icon' => 'users', 'label' => 'Clients', 'module' => 'clients'],
                ['route' => 'vehicles.index', 'icon' => 'car', 'label' => 'Véhicules', 'module' => 'vehicles'],
                // Module 6
                ['route' => 'repair-orders.index', 'icon' => 'clipboard-list', 'label' => 'Ordres de Réparation', 'module' => 'repair_orders'],
                // Module 7
                ['route' => 'delivery-notes.index', 'icon' => 'truck', 'label' => 'Bons de Livraison', 'module' => 'delivery_notes'],
                // Module 8
                ['route' => 'quotes.index', 'icon' => 'file-text', 'label' => 'Devis', 'module' => 'quotes'],
                // Module 9
                ['route' => 'invoices.index', 'icon' => 'receipt', 'label' => 'Factures', 'module' => 'invoices'],
                // Module 10
                ['route' => 'cash.index', 'icon' => 'banknote', 'label' => 'Caisse', 'module' => 'cash'],
                // Module 11
                ['route' => 'products.index', 'icon' => 'package', 'label' => 'Stock', 'module' => 'stock'],
                // Module 12
                ['route' => 'suppliers.index', 'icon' => 'factory', 'label' => 'Fournisseurs', 'module' => 'suppliers'],
                // Module 13
                ['route' => 'tva.index', 'icon' => 'calculator', 'label' => 'TVA', 'module' => 'tva'],
                // Module 14 - Experts
                ['route' => 'experts.index', 'icon' => 'user-check', 'label' => 'Experts', 'module' => 'experts'],
            ];
        @endphp

        @foreach ($menu as $item)
            @if (!isset($item['role']) || auth()->user()->hasRole($item['role']))
                @if (auth()->user()->canAccess($item['module']) || auth()->user()->isAdmin())
                    <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                       class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 transition-colors
                              {{ request()->routeIs($item['route'] . '*') ? 'active' : '' }}"
                       @if(!$sidebarOpen ?? false) title="{{ $item['label'] }}" @endif>
                        <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5 flex-shrink-0"></i>
                        <span x-show="sidebarOpen" x-cloak>{{ $item['label'] }}</span>
                    </a>
                @endif
            @endif
        @endforeach
    </nav>
</aside>

{{-- ═══════════ SIDEBAR MOBILE ═══════════ --}}
<aside x-show="mobileSidebar" x-cloak
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="-translate-x-full"
       x-transition:enter-end="translate-x-0"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="translate-x-0"
       x-transition:leave-end="-translate-x-full"
       class="fixed top-0 left-0 h-screen w-64 bg-white border-r border-gray-200 z-50 lg:hidden">

    {{-- Logo + Close --}}
    <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-primary-600 flex items-center justify-center">
                <i data-lucide="wrench" class="w-6 h-6 text-white"></i>
            </div>
            <span class="font-bold text-lg text-gray-800">Atelier Pro</span>
        </div>
        <button @click="mobileSidebar = false" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100">
            <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
        </button>
    </div>

    {{-- Navigation (même contenu) --}}
    <nav class="mt-4 px-3 space-y-1 overflow-y-auto" style="max-height: calc(100vh - 5rem);">
        @foreach ($menu as $item)
            @if (!isset($item['role']) || auth()->user()->hasRole($item['role']))
                @if (auth()->user()->canAccess($item['module']) || auth()->user()->isAdmin())
                    <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                       @click="mobileSidebar = false"
                       class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 transition-colors
                              {{ request()->routeIs($item['route'] . '*') ? 'active' : '' }}">
                        <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5 flex-shrink-0"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endif
            @endif
        @endforeach
    </nav>
</aside>
