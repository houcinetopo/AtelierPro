<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class TvaDeclaration extends Model
{
    protected $fillable = [
        'created_by', 'validated_by',
        'regime', 'annee', 'mois', 'trimestre', 'date_debut', 'date_fin',
        'ca_ht_20', 'ca_ht_14', 'ca_ht_10', 'ca_ht_7', 'ca_ht_exonere',
        'tva_collectee_20', 'tva_collectee_14', 'tva_collectee_10', 'tva_collectee_7', 'total_tva_collectee',
        'achats_ht_20', 'achats_ht_14', 'achats_ht_10', 'achats_ht_7',
        'tva_deductible_20', 'tva_deductible_14', 'tva_deductible_10', 'tva_deductible_7', 'total_tva_deductible',
        'credit_tva_anterieur', 'tva_due', 'credit_tva',
        'statut', 'date_declaration', 'date_paiement', 'reference_paiement',
        'montant_paye', 'penalites', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_debut'       => 'date',
            'date_fin'         => 'date',
            'date_declaration' => 'date',
            'date_paiement'    => 'date',
            'ca_ht_20'         => 'decimal:2', 'ca_ht_14'         => 'decimal:2',
            'ca_ht_10'         => 'decimal:2', 'ca_ht_7'          => 'decimal:2',
            'ca_ht_exonere'    => 'decimal:2',
            'tva_collectee_20' => 'decimal:2', 'tva_collectee_14' => 'decimal:2',
            'tva_collectee_10' => 'decimal:2', 'tva_collectee_7'  => 'decimal:2',
            'total_tva_collectee'  => 'decimal:2',
            'achats_ht_20'     => 'decimal:2', 'achats_ht_14'     => 'decimal:2',
            'achats_ht_10'     => 'decimal:2', 'achats_ht_7'      => 'decimal:2',
            'tva_deductible_20'=> 'decimal:2', 'tva_deductible_14'=> 'decimal:2',
            'tva_deductible_10'=> 'decimal:2', 'tva_deductible_7' => 'decimal:2',
            'total_tva_deductible' => 'decimal:2',
            'credit_tva_anterieur' => 'decimal:2',
            'tva_due'          => 'decimal:2',
            'credit_tva'       => 'decimal:2',
            'montant_paye'     => 'decimal:2',
            'penalites'        => 'decimal:2',
        ];
    }

    // ──────────────────────────────────────
    // Constantes
    // ──────────────────────────────────────

    public const STATUTS = [
        'brouillon' => 'Brouillon',
        'calculee'  => 'Calculée',
        'validee'   => 'Validée',
        'declaree'  => 'Déclarée',
        'payee'     => 'Payée',
    ];

    public const STATUT_COLORS = [
        'brouillon' => 'gray',
        'calculee'  => 'blue',
        'validee'   => 'indigo',
        'declaree'  => 'amber',
        'payee'     => 'green',
    ];

    public const TAUX_TVA = [
        20 => '20%',
        14 => '14%',
        10 => '10%',
        7  => '7%',
        0  => 'Exonéré',
    ];

    public const REGIMES = [
        'mensuel'      => 'Mensuel',
        'trimestriel'  => 'Trimestriel',
    ];

    public const MOIS_LABELS = [
        1 => 'Janvier', 2 => 'Février', 3 => 'Mars',
        4 => 'Avril', 5 => 'Mai', 6 => 'Juin',
        7 => 'Juillet', 8 => 'Août', 9 => 'Septembre',
        10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
    ];

    public const TRIMESTRE_LABELS = [
        1 => '1er trimestre (Jan-Mar)',
        2 => '2ème trimestre (Avr-Jun)',
        3 => '3ème trimestre (Jul-Sep)',
        4 => '4ème trimestre (Oct-Déc)',
    ];

    // ──────────────────────────────────────
    // Relations
    // ──────────────────────────────────────

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validatedBy()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // ──────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────

    public function scopeByAnnee($query, ?int $annee)
    {
        return $annee ? $query->where('annee', $annee) : $query;
    }

    public function scopeByStatut($query, ?string $statut)
    {
        return $statut ? $query->where('statut', $statut) : $query;
    }

    // ──────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────

    public function getStatutLabelAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? ucfirst($this->statut);
    }

    public function getStatutBadgeAttribute(): string
    {
        $c = self::STATUT_COLORS[$this->statut] ?? 'gray';
        return "<span class=\"inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-{$c}-100 text-{$c}-700\"><span class=\"w-1.5 h-1.5 rounded-full bg-{$c}-500\"></span>{$this->statut_label}</span>";
    }

    public function getPeriodeLabelAttribute(): string
    {
        if ($this->regime === 'mensuel' && $this->mois) {
            return (self::MOIS_LABELS[$this->mois] ?? $this->mois) . ' ' . $this->annee;
        }
        if ($this->regime === 'trimestriel' && $this->trimestre) {
            return (self::TRIMESTRE_LABELS[$this->trimestre] ?? "T{$this->trimestre}") . ' ' . $this->annee;
        }
        return "{$this->annee}";
    }

    public function getPeriodeCourtAttribute(): string
    {
        if ($this->regime === 'mensuel' && $this->mois) {
            return str_pad($this->mois, 2, '0', STR_PAD_LEFT) . '/' . $this->annee;
        }
        return "T{$this->trimestre}/{$this->annee}";
    }

    public function getTotalCaHtAttribute(): float
    {
        return (float)$this->ca_ht_20 + (float)$this->ca_ht_14
             + (float)$this->ca_ht_10 + (float)$this->ca_ht_7
             + (float)$this->ca_ht_exonere;
    }

    public function getTotalAchatsHtAttribute(): float
    {
        return (float)$this->achats_ht_20 + (float)$this->achats_ht_14
             + (float)$this->achats_ht_10 + (float)$this->achats_ht_7;
    }

    public function getIsEditableAttribute(): bool
    {
        return in_array($this->statut, ['brouillon', 'calculee']);
    }

    public function getIsOverdueAttribute(): bool
    {
        if (in_array($this->statut, ['declaree', 'payee'])) return false;

        // Déclaration due avant la fin du mois suivant
        $dateLimite = $this->date_fin->copy()->addMonth()->endOfMonth();
        return now()->gt($dateLimite);
    }

    public function getDateLimiteAttribute(): Carbon
    {
        return $this->date_fin->copy()->addMonth()->endOfMonth();
    }

    // ──────────────────────────────────────
    // Calcul automatique depuis les données
    // ──────────────────────────────────────

    public function calculateFromData(): void
    {
        // ── TVA Collectée : factures émises/payées dans la période ──
        $invoices = Invoice::whereBetween('date_facture', [$this->date_debut, $this->date_fin])
            ->whereIn('statut', ['emise', 'payee', 'partielle', 'en_retard'])
            ->get();

        // Group by TVA rate
        $caByRate = [20 => 0, 14 => 0, 10 => 0, 7 => 0, 0 => 0];
        foreach ($invoices as $inv) {
            $rate = (int)$inv->taux_tva;
            if (isset($caByRate[$rate])) {
                $caByRate[$rate] += (float)$inv->total_ht;
            } else {
                $caByRate[20] += (float)$inv->total_ht; // Default to 20%
            }
        }

        $this->ca_ht_20 = $caByRate[20];
        $this->ca_ht_14 = $caByRate[14];
        $this->ca_ht_10 = $caByRate[10];
        $this->ca_ht_7  = $caByRate[7];
        $this->ca_ht_exonere = $caByRate[0];

        $this->tva_collectee_20 = round($caByRate[20] * 0.20, 2);
        $this->tva_collectee_14 = round($caByRate[14] * 0.14, 2);
        $this->tva_collectee_10 = round($caByRate[10] * 0.10, 2);
        $this->tva_collectee_7  = round($caByRate[7] * 0.07, 2);

        $this->total_tva_collectee = round(
            $this->tva_collectee_20 + $this->tva_collectee_14
            + $this->tva_collectee_10 + $this->tva_collectee_7, 2
        );

        // ── TVA Déductible : bons de commande livrés dans la période ──
        $purchases = PurchaseOrder::whereBetween('date_commande', [$this->date_debut, $this->date_fin])
            ->whereIn('statut', ['livree', 'livree_partiel', 'confirmee', 'envoyee'])
            ->get();

        $achatsByRate = [20 => 0, 14 => 0, 10 => 0, 7 => 0];
        foreach ($purchases as $po) {
            $rate = (int)$po->taux_tva;
            if (isset($achatsByRate[$rate])) {
                $achatsByRate[$rate] += (float)$po->total_ht;
            } else {
                $achatsByRate[20] += (float)$po->total_ht;
            }
        }

        // Also add cash movements for purchases (achat_pieces category)
        $cashAchats = CashMovement::whereHas('cashSession', function ($q) {
                $q->whereBetween('date_session', [$this->date_debut, $this->date_fin]);
            })
            ->where('type', 'sortie')
            ->whereIn('categorie', ['achat_pieces', 'outillage', 'charges'])
            ->sum('montant');

        // Cash purchases default to 20% rate
        $achatsByRate[20] += (float)$cashAchats;

        $this->achats_ht_20 = $achatsByRate[20];
        $this->achats_ht_14 = $achatsByRate[14];
        $this->achats_ht_10 = $achatsByRate[10];
        $this->achats_ht_7  = $achatsByRate[7];

        $this->tva_deductible_20 = round($achatsByRate[20] * 0.20, 2);
        $this->tva_deductible_14 = round($achatsByRate[14] * 0.14, 2);
        $this->tva_deductible_10 = round($achatsByRate[10] * 0.10, 2);
        $this->tva_deductible_7  = round($achatsByRate[7] * 0.07, 2);

        $this->total_tva_deductible = round(
            $this->tva_deductible_20 + $this->tva_deductible_14
            + $this->tva_deductible_10 + $this->tva_deductible_7, 2
        );

        // ── Crédit antérieur ──
        $previousDeclaration = self::where('annee', '<=', $this->annee)
            ->where('id', '!=', $this->id ?? 0)
            ->where(function ($q) {
                $q->where('annee', '<', $this->annee)
                  ->orWhere(function ($q2) {
                      if ($this->regime === 'mensuel') {
                          $q2->where('annee', $this->annee)->where('mois', '<', $this->mois);
                      } else {
                          $q2->where('annee', $this->annee)->where('trimestre', '<', $this->trimestre);
                      }
                  });
            })
            ->orderByDesc('date_fin')
            ->first();

        $this->credit_tva_anterieur = $previousDeclaration ? (float)$previousDeclaration->credit_tva : 0;

        // ── Solde final ──
        $solde = round(
            (float)$this->total_tva_collectee
            - (float)$this->total_tva_deductible
            - (float)$this->credit_tva_anterieur, 2
        );

        if ($solde >= 0) {
            $this->tva_due = $solde;
            $this->credit_tva = 0;
        } else {
            $this->tva_due = 0;
            $this->credit_tva = abs($solde);
        }

        $this->statut = 'calculee';
        $this->save();
    }

    // ──────────────────────────────────────
    // State transitions
    // ──────────────────────────────────────

    public const TRANSITIONS = [
        'brouillon' => ['calculee'],
        'calculee'  => ['validee', 'brouillon'],
        'validee'   => ['declaree', 'calculee'],
        'declaree'  => ['payee'],
        'payee'     => [],
    ];

    public function canTransitionTo(string $statut): bool
    {
        return in_array($statut, self::TRANSITIONS[$this->statut] ?? []);
    }

    public function transitionTo(string $statut): bool
    {
        if (!$this->canTransitionTo($statut)) return false;
        $this->update(['statut' => $statut]);
        return true;
    }

    public function getAvailableTransitions(): array
    {
        return collect(self::TRANSITIONS[$this->statut] ?? [])
            ->mapWithKeys(fn($s) => [$s => self::STATUTS[$s]])
            ->toArray();
    }

    // ──────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────

    public static function getDatesForPeriod(string $regime, int $annee, int $periodeNum): array
    {
        if ($regime === 'mensuel') {
            $debut = Carbon::create($annee, $periodeNum, 1)->startOfMonth();
            $fin = $debut->copy()->endOfMonth();
        } else {
            $moisDebut = ($periodeNum - 1) * 3 + 1;
            $debut = Carbon::create($annee, $moisDebut, 1)->startOfMonth();
            $fin = $debut->copy()->addMonths(2)->endOfMonth();
        }
        return [$debut, $fin];
    }

    public function getInvoicesForPeriod()
    {
        return Invoice::whereBetween('date_facture', [$this->date_debut, $this->date_fin])
            ->whereIn('statut', ['emise', 'payee', 'partielle', 'en_retard'])
            ->with('client')
            ->orderByDesc('date_facture')
            ->get();
    }

    public function getPurchasesForPeriod()
    {
        return PurchaseOrder::whereBetween('date_commande', [$this->date_debut, $this->date_fin])
            ->whereIn('statut', ['livree', 'livree_partiel', 'confirmee', 'envoyee'])
            ->with('supplier')
            ->orderByDesc('date_commande')
            ->get();
    }

    // ──────────────────────────────────────
    // Stats globales
    // ──────────────────────────────────────

    public static function getAnnualSummary(int $annee): array
    {
        $declarations = self::where('annee', $annee)->get();

        return [
            'total_ca_ht'          => $declarations->sum('total_ca_ht'),
            'total_tva_collectee'  => $declarations->sum('total_tva_collectee'),
            'total_achats_ht'      => $declarations->sum('total_achats_ht'),
            'total_tva_deductible' => $declarations->sum('total_tva_deductible'),
            'total_tva_due'        => $declarations->sum('tva_due'),
            'total_paye'           => $declarations->whereNotNull('montant_paye')->sum('montant_paye'),
            'credit_restant'       => $declarations->last()?->credit_tva ?? 0,
            'nb_declarations'      => $declarations->count(),
            'nb_payees'            => $declarations->where('statut', 'payee')->count(),
        ];
    }
}
