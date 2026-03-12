<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'stats'        => $this->getStats(),
            'charts'       => $this->getChartsData(),
            'recentOrders' => $this->getRecentOrders(),
            'alerts'       => $this->getAlerts(),
            'recentLogs'   => ActivityLog::with('user')->recent(5)->get(),
        ];

        return view('dashboard', $data);
    }

    /**
     * API : rafraîchir les stats en AJAX (polling)
     */
    public function statsApi()
    {
        return response()->json([
            'stats'  => $this->getStats(),
            'alerts' => $this->getAlerts(),
        ]);
    }

    /**
     * API : données des graphiques
     */
    public function chartsApi()
    {
        return response()->json($this->getChartsData());
    }

    // ══════════════════════════════════════════════
    // STATISTIQUES (Cards)
    // ══════════════════════════════════════════════

    private function getStats(): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth   = $now->copy()->endOfMonth();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd   = $now->copy()->subMonth()->endOfMonth();

        // ── Revenus ──
        $currentRevenue = $this->safeSum('invoices', 'total_ttc', function ($q) use ($startOfMonth, $endOfMonth) {
            return $q->whereBetween('invoice_date', [$startOfMonth, $endOfMonth])
                     ->where('status', '!=', 'annulee');
        });

        $lastRevenue = $this->safeSum('invoices', 'total_ttc', function ($q) use ($lastMonthStart, $lastMonthEnd) {
            return $q->whereBetween('invoice_date', [$lastMonthStart, $lastMonthEnd])
                     ->where('status', '!=', 'annulee');
        });

        // ── Dépenses ──
        $currentExpenses = $this->safeSum('cash_transactions', 'amount', function ($q) use ($startOfMonth, $endOfMonth) {
            return $q->where('type', 'sortie')
                     ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth]);
        });

        $lastExpenses = $this->safeSum('cash_transactions', 'amount', function ($q) use ($lastMonthStart, $lastMonthEnd) {
            return $q->where('type', 'sortie')
                     ->whereBetween('transaction_date', [$lastMonthStart, $lastMonthEnd]);
        });

        // ── Nouveaux clients ──
        $currentClients = $this->safeCount('clients', function ($q) use ($startOfMonth, $endOfMonth) {
            return $q->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
        });

        $lastClients = $this->safeCount('clients', function ($q) use ($lastMonthStart, $lastMonthEnd) {
            return $q->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd]);
        });

        return [
            'vehicles_in_progress'   => $this->safeCount('repair_orders', function ($q) {
                return $q->whereIn('status', ['en_cours', 'brouillon']);
            }),
            'monthly_revenue'        => $currentRevenue,
            'monthly_revenue_change' => $this->pctChange($currentRevenue, $lastRevenue),
            'new_clients'            => $currentClients,
            'new_clients_change'     => $this->pctChange($currentClients, $lastClients),
            'monthly_expenses'       => $currentExpenses,
            'monthly_expenses_change'=> $this->pctChange($currentExpenses, $lastExpenses),
            'cash_balance'           => $this->getCashBalance(),
            'unpaid_invoices_count'  => $this->safeCount('invoices', function ($q) {
                return $q->whereIn('status', ['non_payee', 'partiellement_payee']);
            }),
            'unpaid_invoices_amount' => $this->safeSum('invoices', 'net_a_payer', function ($q) {
                return $q->whereIn('status', ['non_payee', 'partiellement_payee']);
            }),
        ];
    }

    // ══════════════════════════════════════════════
    // GRAPHIQUES
    // ══════════════════════════════════════════════

    private function getChartsData(): array
    {
        return [
            'revenue_expenses' => $this->getRevenueVsExpenses(),
            'repair_types'     => $this->getRepairTypes(),
            'top_clients'      => $this->getTopClients(),
            'monthly_orders'   => $this->getMonthlyOrders(),
        ];
    }

    private function getRevenueVsExpenses(): array
    {
        $labels = [];
        $revenues = [];
        $expenses = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $start = $month->copy()->startOfMonth();
            $end   = $month->copy()->endOfMonth();

            $labels[]   = $month->translatedFormat('M Y');
            $revenues[] = $this->safeSum('invoices', 'total_ttc', function ($q) use ($start, $end) {
                return $q->whereBetween('invoice_date', [$start, $end])->where('status', '!=', 'annulee');
            });
            $expenses[] = $this->safeSum('cash_transactions', 'amount', function ($q) use ($start, $end) {
                return $q->where('type', 'sortie')->whereBetween('transaction_date', [$start, $end]);
            });
        }

        return compact('labels', 'revenues', 'expenses');
    }

    private function getRepairTypes(): array
    {
        if (!Schema::hasTable('repair_order_items')) {
            // Données de démonstration initiales
            return [
                'labels' => ['Carrosserie', 'Mécanique', 'Peinture', 'Électrique', 'Vitrage'],
                'data'   => [0, 0, 0, 0, 0],
                'colors' => ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
            ];
        }

        $types = DB::table('repair_order_items')
            ->select('designation', DB::raw('COUNT(*) as count'))
            ->groupBy('designation')
            ->orderByDesc('count')
            ->limit(6)
            ->get();

        $colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];

        if ($types->isEmpty()) {
            return [
                'labels' => ['Carrosserie', 'Mécanique', 'Peinture', 'Électrique', 'Vitrage'],
                'data'   => [0, 0, 0, 0, 0],
                'colors' => array_slice($colors, 0, 5),
            ];
        }

        return [
            'labels' => $types->pluck('designation')->toArray(),
            'data'   => $types->pluck('count')->toArray(),
            'colors' => array_slice($colors, 0, $types->count()),
        ];
    }

    private function getTopClients(): array
    {
        if (!Schema::hasTable('invoices') || !Schema::hasTable('clients')) {
            return ['labels' => [], 'data' => []];
        }

        $clients = DB::table('invoices')
            ->join('clients', 'invoices.client_id', '=', 'clients.id')
            ->select(
                DB::raw('COALESCE(clients.nom_complet, clients.raison_sociale) as name'),
                DB::raw('SUM(invoices.total_ttc) as total')
            )
            ->where('invoices.status', '!=', 'annulee')
            ->groupBy('clients.id', 'clients.nom_complet', 'clients.raison_sociale')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return [
            'labels' => $clients->pluck('name')->toArray(),
            'data'   => $clients->pluck('total')->toArray(),
        ];
    }

    private function getMonthlyOrders(): array
    {
        $labels = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $start = $month->copy()->startOfMonth();
            $end   = $month->copy()->endOfMonth();

            $labels[] = $month->translatedFormat('M Y');
            $data[]   = $this->safeCount('repair_orders', function ($q) use ($start, $end) {
                return $q->whereBetween('created_at', [$start, $end]);
            });
        }

        return compact('labels', 'data');
    }

    // ══════════════════════════════════════════════
    // DERNIERS ORDRES DE RÉPARATION
    // ══════════════════════════════════════════════

    private function getRecentOrders(): array
    {
        if (!Schema::hasTable('repair_orders')) {
            return [];
        }

        $query = DB::table('repair_orders')
            ->leftJoin('clients', 'repair_orders.client_id', '=', 'clients.id')
            ->leftJoin('vehicles', 'repair_orders.vehicle_id', '=', 'vehicles.id')
            ->select(
                'repair_orders.id',
                'repair_orders.numero',
                'repair_orders.status',
                'repair_orders.date_reception',
                'repair_orders.date_prevue_livraison',
                'repair_orders.total_ttc',
                'repair_orders.created_at',
                DB::raw('COALESCE(clients.nom_complet, clients.raison_sociale) as client_name'),
                'clients.telephone as client_phone',
                DB::raw("CONCAT(vehicles.marque, ' ', vehicles.modele) as vehicle_name"),
                'vehicles.immatriculation'
            )
            ->orderByDesc('repair_orders.created_at')
            ->limit(5);

        if (auth()->user()->isTechnicien()) {
            $query->where('repair_orders.technicien_id', auth()->id());
        }

        return $query->get()->map(function ($order) {
            $order->status_label = $this->getStatusLabel($order->status);
            $order->status_color = $this->getStatusColor($order->status);
            $order->is_late = $order->date_prevue_livraison
                && Carbon::parse($order->date_prevue_livraison)->isPast()
                && !in_array($order->status, ['livre', 'facture']);
            return $order;
        })->toArray();
    }

    // ══════════════════════════════════════════════
    // ALERTES
    // ══════════════════════════════════════════════

    private function getAlerts(): array
    {
        $alerts = [];

        // 1. Stock — Rupture
        if (Schema::hasTable('products')) {
            $outOfStock = DB::table('products')->where('quantite_stock', '<=', 0)->count();
            if ($outOfStock > 0) {
                $alerts[] = [
                    'type'    => 'danger',
                    'icon'    => 'package-x',
                    'title'   => 'Rupture de stock',
                    'message' => "{$outOfStock} produit(s) en rupture totale",
                ];
            }

            // 2. Stock — Faible
            $lowStock = DB::table('products')
                ->whereColumn('quantite_stock', '<=', 'seuil_alerte')
                ->where('quantite_stock', '>', 0)
                ->count();
            if ($lowStock > 0) {
                $alerts[] = [
                    'type'    => 'warning',
                    'icon'    => 'package-minus',
                    'title'   => 'Stock faible',
                    'message' => "{$lowStock} produit(s) sous le seuil d'alerte",
                ];
            }
        }

        // 3. Véhicules en retard
        if (Schema::hasTable('repair_orders')) {
            $lateVehicles = DB::table('repair_orders')
                ->whereNotIn('status', ['livre', 'facture', 'brouillon'])
                ->where('date_prevue_livraison', '<', Carbon::today())
                ->count();
            if ($lateVehicles > 0) {
                $alerts[] = [
                    'type'    => 'warning',
                    'icon'    => 'clock',
                    'title'   => 'Retard de livraison',
                    'message' => "{$lateVehicles} véhicule(s) en retard",
                ];
            }
        }

        // 4. Factures impayées / en retard
        if (Schema::hasTable('invoices')) {
            $overdueInvoices = DB::table('invoices')
                ->whereIn('status', ['non_payee', 'partiellement_payee'])
                ->where('echeance_paiement', '<', Carbon::today())
                ->count();
            if ($overdueInvoices > 0) {
                $alerts[] = [
                    'type'    => 'danger',
                    'icon'    => 'file-warning',
                    'title'   => 'Factures en retard',
                    'message' => "{$overdueInvoices} facture(s) dépassant l'échéance",
                ];
            } else {
                $unpaid = DB::table('invoices')
                    ->whereIn('status', ['non_payee', 'partiellement_payee'])
                    ->count();
                if ($unpaid > 0) {
                    $alerts[] = [
                        'type'    => 'info',
                        'icon'    => 'file-text',
                        'title'   => 'Factures impayées',
                        'message' => "{$unpaid} facture(s) en attente de paiement",
                    ];
                }
            }
        }

        // 5. Assurances véhicules expirées
        if (Schema::hasTable('vehicles')) {
            $expiredInsurance = DB::table('vehicles')
                ->whereNotNull('date_expiration_assurance')
                ->where('date_expiration_assurance', '<', Carbon::today())
                ->count();
            if ($expiredInsurance > 0) {
                $alerts[] = [
                    'type'    => 'info',
                    'icon'    => 'shield-alert',
                    'title'   => 'Assurances expirées',
                    'message' => "{$expiredInsurance} véhicule(s) avec assurance expirée",
                ];
            }
        }

        return $alerts;
    }

    // ══════════════════════════════════════════════
    // UTILITAIRES PRIVÉS
    // ══════════════════════════════════════════════

    private function safeCount(string $table, ?\Closure $cb = null): int
    {
        if (!Schema::hasTable($table)) return 0;
        $q = DB::table($table);
        if ($cb) $q = $cb($q);
        return (int) $q->count();
    }

    private function safeSum(string $table, string $col, ?\Closure $cb = null): float
    {
        if (!Schema::hasTable($table)) return 0;
        $q = DB::table($table);
        if ($cb) $q = $cb($q);
        return (float) ($q->sum($col) ?? 0);
    }

    private function getCashBalance(): float
    {
        if (!Schema::hasTable('cash_transactions')) return 0;
        $in  = (float) DB::table('cash_transactions')->where('type', 'entree')->sum('amount');
        $out = (float) DB::table('cash_transactions')->where('type', 'sortie')->sum('amount');
        return $in - $out;
    }

    private function pctChange(float $current, float $previous): float
    {
        if ($previous == 0) return $current > 0 ? 100 : 0;
        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function getStatusLabel(string $status): string
    {
        return match($status) {
            'brouillon' => 'Brouillon', 'en_cours' => 'En cours',
            'termine'   => 'Terminé',   'livre'    => 'Livré',
            'facture'   => 'Facturé',   default    => ucfirst($status),
        };
    }

    private function getStatusColor(string $status): string
    {
        return match($status) {
            'brouillon' => 'gray',   'en_cours' => 'blue',
            'termine'   => 'green',  'livre'    => 'indigo',
            'facture'   => 'emerald', default   => 'gray',
        };
    }
}
