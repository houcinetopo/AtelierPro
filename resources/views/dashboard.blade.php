@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
<div class="space-y-6" x-data="dashboardApp()" x-init="init()">

    {{-- ═══════════ HEADER ═══════════ --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Tableau de bord</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                Bienvenue, {{ auth()->user()->name }} — {{ now()->translatedFormat('l d F Y') }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            {{-- Refresh --}}
            <button @click="refreshStats()"
                    class="inline-flex items-center gap-2 px-3 py-2 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                    :class="{ 'opacity-50 cursor-wait': loading }">
                <i data-lucide="refresh-cw" class="w-4 h-4" :class="{ 'animate-spin': loading }"></i>
                <span class="hidden sm:inline">Actualiser</span>
            </button>
        </div>
    </div>

    {{-- ═══════════ ALERTES ═══════════ --}}
    @if(count($alerts) > 0)
    <div class="space-y-2" id="alerts-section">
        @foreach($alerts as $alert)
        <div class="flex items-center gap-3 px-4 py-3 rounded-lg border
            {{ match($alert['type']) {
                'danger'  => 'bg-red-50 border-red-200 text-red-800',
                'warning' => 'bg-amber-50 border-amber-200 text-amber-800',
                'info'    => 'bg-blue-50 border-blue-200 text-blue-800',
                default   => 'bg-gray-50 border-gray-200 text-gray-800',
            } }}">
            <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center
                {{ match($alert['type']) {
                    'danger'  => 'bg-red-100',
                    'warning' => 'bg-amber-100',
                    'info'    => 'bg-blue-100',
                    default   => 'bg-gray-100',
                } }}">
                <i data-lucide="{{ $alert['icon'] }}" class="w-4 h-4"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold">{{ $alert['title'] }}</p>
                <p class="text-xs opacity-80">{{ $alert['message'] }}</p>
            </div>
            <i data-lucide="chevron-right" class="w-4 h-4 opacity-40"></i>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ═══════════ STATS CARDS ═══════════ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">

        {{-- Card 1 : Véhicules en cours --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                    <i data-lucide="car" class="w-5 h-5 text-blue-500"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['vehicles_in_progress']) }}</p>
            <p class="text-xs text-gray-500 mt-1">Véhicules en cours</p>
        </div>

        {{-- Card 2 : CA du mois --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-5 h-5 text-green-500"></i>
                </div>
                @if($stats['monthly_revenue_change'] != 0)
                <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-xs font-medium
                    {{ $stats['monthly_revenue_change'] > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    <i data-lucide="{{ $stats['monthly_revenue_change'] > 0 ? 'trending-up' : 'trending-down' }}" class="w-3 h-3"></i>
                    {{ abs($stats['monthly_revenue_change']) }}%
                </span>
                @endif
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['monthly_revenue'], 2, ',', ' ') }}</p>
            <p class="text-xs text-gray-500 mt-1">CA du mois (DH)</p>
        </div>

        {{-- Card 3 : Nouveaux clients --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center">
                    <i data-lucide="user-plus" class="w-5 h-5 text-purple-500"></i>
                </div>
                @if($stats['new_clients_change'] != 0)
                <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-xs font-medium
                    {{ $stats['new_clients_change'] > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    <i data-lucide="{{ $stats['new_clients_change'] > 0 ? 'trending-up' : 'trending-down' }}" class="w-3 h-3"></i>
                    {{ abs($stats['new_clients_change']) }}%
                </span>
                @endif
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['new_clients']) }}</p>
            <p class="text-xs text-gray-500 mt-1">Nouveaux clients</p>
        </div>

        {{-- Card 4 : Dépenses du mois --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center">
                    <i data-lucide="receipt" class="w-5 h-5 text-red-500"></i>
                </div>
                @if($stats['monthly_expenses_change'] != 0)
                <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-xs font-medium
                    {{ $stats['monthly_expenses_change'] < 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    <i data-lucide="{{ $stats['monthly_expenses_change'] < 0 ? 'trending-down' : 'trending-up' }}" class="w-3 h-3"></i>
                    {{ abs($stats['monthly_expenses_change']) }}%
                </span>
                @endif
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['monthly_expenses'], 2, ',', ' ') }}</p>
            <p class="text-xs text-gray-500 mt-1">Dépenses du mois (DH)</p>
        </div>

        {{-- Card 5 : Solde de caisse --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center">
                    <i data-lucide="wallet" class="w-5 h-5 text-amber-500"></i>
                </div>
                @if($stats['unpaid_invoices_count'] > 0)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-700">
                    {{ $stats['unpaid_invoices_count'] }} impayée(s)
                </span>
                @endif
            </div>
            <p class="text-2xl font-bold {{ $stats['cash_balance'] >= 0 ? 'text-gray-800' : 'text-red-600' }}">
                {{ number_format($stats['cash_balance'], 2, ',', ' ') }}
            </p>
            <p class="text-xs text-gray-500 mt-1">Solde de caisse (DH)</p>
        </div>

    </div>

    {{-- ═══════════ GRAPHIQUES ═══════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Graphique 1 : Revenus vs Dépenses (occupe 2 colonnes) --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-semibold text-gray-800">Revenus vs Dépenses</h2>
                    <p class="text-xs text-gray-400">6 derniers mois</p>
                </div>
                <div class="flex items-center gap-4 text-xs">
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-blue-500"></span> Revenus
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-red-400"></span> Dépenses
                    </span>
                </div>
            </div>
            <div class="relative" style="height: 280px;">
                <canvas id="revenueExpensesChart"></canvas>
            </div>
        </div>

        {{-- Graphique 2 : Types de réparation (camembert) --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="mb-4">
                <h2 class="text-base font-semibold text-gray-800">Types de réparation</h2>
                <p class="text-xs text-gray-400">Répartition par désignation</p>
            </div>
            <div class="relative flex items-center justify-center" style="height: 280px;">
                <canvas id="repairTypesChart"></canvas>
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Graphique 3 : Top 5 clients --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="mb-4">
                <h2 class="text-base font-semibold text-gray-800">Top 5 Clients</h2>
                <p class="text-xs text-gray-400">Par chiffre d'affaires total (DH)</p>
            </div>
            <div class="relative" style="height: 250px;">
                <canvas id="topClientsChart"></canvas>
            </div>
        </div>

        {{-- Graphique 4 : Ordres de réparation mensuels --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="mb-4">
                <h2 class="text-base font-semibold text-gray-800">Ordres de Réparation</h2>
                <p class="text-xs text-gray-400">Nombre d'OR par mois</p>
            </div>
            <div class="relative" style="height: 250px;">
                <canvas id="monthlyOrdersChart"></canvas>
            </div>
        </div>

    </div>

    {{-- ═══════════ SECTION BASSE : OR RÉCENTS + ACTIVITÉ ═══════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Derniers OR (2 colonnes) --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-800">Derniers Ordres de Réparation</h2>
                    <p class="text-xs text-gray-400">5 derniers OR enregistrés</p>
                </div>
                {{-- Le lien sera actif quand le module OR sera construit --}}
                <span class="text-sm text-gray-300">Voir tout →</span>
            </div>

            @if(count($recentOrders) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">N° OR</th>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Client</th>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Véhicule</th>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                            <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($recentOrders as $order)
                        @php $order = (object) $order; @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">
                                <span class="font-mono text-xs font-medium text-primary-600">{{ $order->numero }}</span>
                                @if($order->is_late)
                                    <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-600">
                                        En retard
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-700 text-xs">{{ $order->client_name ?? '—' }}</p>
                                <p class="text-xs text-gray-400">{{ $order->client_phone ?? '' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-xs text-gray-700">{{ $order->vehicle_name ?? '—' }}</p>
                                <p class="text-xs text-gray-400 font-mono">{{ $order->immatriculation ?? '' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $colorMap = [
                                        'gray'    => 'bg-gray-100 text-gray-600',
                                        'blue'    => 'bg-blue-100 text-blue-600',
                                        'green'   => 'bg-green-100 text-green-600',
                                        'indigo'  => 'bg-indigo-100 text-indigo-600',
                                        'emerald' => 'bg-emerald-100 text-emerald-600',
                                    ];
                                    $cls = $colorMap[$order->status_color] ?? 'bg-gray-100 text-gray-600';
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $cls }}">
                                    {{ $order->status_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="text-sm font-semibold text-gray-800">
                                    {{ $order->total_ttc ? number_format($order->total_ttc, 2, ',', ' ') : '—' }}
                                </span>
                                <span class="text-xs text-gray-400">DH</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="px-5 py-12 text-center">
                <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
                    <i data-lucide="clipboard-list" class="w-8 h-8 text-gray-300"></i>
                </div>
                <p class="text-sm text-gray-400">Aucun ordre de réparation pour le moment</p>
                <p class="text-xs text-gray-300 mt-1">Les OR apparaîtront ici dès qu'ils seront créés</p>
            </div>
            @endif
        </div>

        {{-- Activité récente (1 colonne) --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-800">Activité récente</h2>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.activity-logs') }}" class="text-xs text-primary-600 hover:text-primary-700 font-medium">
                        Tout voir →
                    </a>
                @endif
            </div>
            <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                @forelse ($recentLogs as $log)
                <div class="px-4 py-3 flex items-start gap-3">
                    <div class="mt-0.5 w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0
                        {{ match($log->action) {
                            'create' => 'bg-green-100',
                            'update' => 'bg-blue-100',
                            'delete' => 'bg-red-100',
                            'login'  => 'bg-indigo-100',
                            'logout' => 'bg-gray-100',
                            default  => 'bg-gray-100',
                        } }}">
                        <i data-lucide="{{ match($log->action) {
                            'create' => 'plus',
                            'update' => 'edit',
                            'delete' => 'trash-2',
                            'login'  => 'log-in',
                            'logout' => 'log-out',
                            default  => 'activity',
                        } }}"
                        class="w-3.5 h-3.5 {{ match($log->action) {
                            'create' => 'text-green-600',
                            'update' => 'text-blue-600',
                            'delete' => 'text-red-600',
                            'login'  => 'text-indigo-600',
                            'logout' => 'text-gray-500',
                            default  => 'text-gray-500',
                        } }}"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-gray-700 leading-relaxed">
                            <span class="font-medium">{{ $log->user?->name ?? 'Système' }}</span>
                            — {{ $log->description }}
                        </p>
                        <p class="text-xs text-gray-300 mt-0.5">{{ $log->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <div class="px-5 py-10 text-center">
                    <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-gray-200"></i>
                    <p class="text-xs text-gray-400">Aucune activité récente</p>
                </div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
// ══════════════════════════════════════════════
// Chart.js — Configuration globale
// ══════════════════════════════════════════════

Chart.defaults.font.family = 'system-ui, -apple-system, sans-serif';
Chart.defaults.font.size = 12;
Chart.defaults.color = '#6b7280';
Chart.defaults.plugins.legend.display = false;

const chartData = @json($charts);

// ──────── 1. Revenus vs Dépenses (Line Chart) ────────
const reCtx = document.getElementById('revenueExpensesChart');
if (reCtx) {
    new Chart(reCtx, {
        type: 'line',
        data: {
            labels: chartData.revenue_expenses.labels,
            datasets: [
                {
                    label: 'Revenus',
                    data: chartData.revenue_expenses.revenues,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.08)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6,
                },
                {
                    label: 'Dépenses',
                    data: chartData.revenue_expenses.expenses,
                    borderColor: '#f87171',
                    backgroundColor: 'rgba(248, 113, 113, 0.08)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#f87171',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1f2937',
                    titleColor: '#f9fafb',
                    bodyColor: '#d1d5db',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: ctx => `${ctx.dataset.label}: ${new Intl.NumberFormat('fr-MA', { style: 'decimal', minimumFractionDigits: 2 }).format(ctx.raw)} DH`,
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 } },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: {
                        font: { size: 11 },
                        callback: v => new Intl.NumberFormat('fr-MA', { notation: 'compact' }).format(v),
                    },
                },
            },
        },
    });
}

// ──────── 2. Types de réparation (Doughnut) ────────
const rtCtx = document.getElementById('repairTypesChart');
if (rtCtx) {
    new Chart(rtCtx, {
        type: 'doughnut',
        data: {
            labels: chartData.repair_types.labels,
            datasets: [{
                data: chartData.repair_types.data,
                backgroundColor: chartData.repair_types.colors,
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        padding: 12,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        font: { size: 11 },
                    },
                },
                tooltip: {
                    backgroundColor: '#1f2937',
                    padding: 10,
                    cornerRadius: 8,
                },
            },
        },
    });
}

// ──────── 3. Top 5 Clients (Horizontal Bar) ────────
const tcCtx = document.getElementById('topClientsChart');
if (tcCtx) {
    const hasClientData = chartData.top_clients.labels.length > 0;

    if (hasClientData) {
        new Chart(tcCtx, {
            type: 'bar',
            data: {
                labels: chartData.top_clients.labels,
                datasets: [{
                    data: chartData.top_clients.data,
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899'],
                    borderRadius: 6,
                    barThickness: 24,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1f2937',
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: ctx => `${new Intl.NumberFormat('fr-MA', { minimumFractionDigits: 2 }).format(ctx.raw)} DH`,
                        },
                    },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        ticks: {
                            font: { size: 11 },
                            callback: v => new Intl.NumberFormat('fr-MA', { notation: 'compact' }).format(v),
                        },
                    },
                    y: {
                        grid: { display: false },
                        ticks: { font: { size: 11 } },
                    },
                },
            },
        });
    } else {
        tcCtx.parentElement.innerHTML = `
            <div class="flex flex-col items-center justify-center h-full text-gray-300">
                <svg class="w-10 h-10 mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <p class="text-xs">Données disponibles après la première facturation</p>
            </div>`;
    }
}

// ──────── 4. Ordres mensuels (Bar Chart) ────────
const moCtx = document.getElementById('monthlyOrdersChart');
if (moCtx) {
    new Chart(moCtx, {
        type: 'bar',
        data: {
            labels: chartData.monthly_orders.labels,
            datasets: [{
                data: chartData.monthly_orders.data,
                backgroundColor: 'rgba(59, 130, 246, 0.7)',
                borderColor: '#3b82f6',
                borderWidth: 1,
                borderRadius: 6,
                barThickness: 32,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1f2937',
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: ctx => `${ctx.raw} ordre(s)`,
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 } },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: {
                        stepSize: 1,
                        font: { size: 11 },
                    },
                },
            },
        },
    });
}

// ══════════════════════════════════════════════
// Alpine.js — Refresh AJAX
// ══════════════════════════════════════════════

function dashboardApp() {
    return {
        loading: false,

        init() {
            // Auto-refresh toutes les 5 minutes
            setInterval(() => this.refreshStats(), 5 * 60 * 1000);
        },

        async refreshStats() {
            this.loading = true;
            try {
                const response = await fetch('{{ route("dashboard.stats-api") }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });
                if (response.ok) {
                    // Recharger la page pour mettre à jour les stats et graphiques
                    window.location.reload();
                }
            } catch (e) {
                console.error('Erreur lors du rafraîchissement:', e);
            } finally {
                this.loading = false;
            }
        },
    };
}
</script>
@endpush
