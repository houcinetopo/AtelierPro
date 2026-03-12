<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'numero', 'supplier_id', 'created_by',
        'date_commande', 'date_livraison_prevue', 'date_reception',
        'statut',
        'total_ht', 'taux_tva', 'montant_tva', 'total_ttc',
        'remise_globale', 'net_a_payer',
        'reference_fournisseur', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_commande'          => 'date',
            'date_livraison_prevue'  => 'date',
            'date_reception'         => 'date',
            'total_ht'       => 'decimal:2',
            'taux_tva'       => 'decimal:2',
            'montant_tva'    => 'decimal:2',
            'total_ttc'      => 'decimal:2',
            'remise_globale' => 'decimal:2',
            'net_a_payer'    => 'decimal:2',
        ];
    }

    // ──────────────────────────────────────
    // Constantes
    // ──────────────────────────────────────

    public const STATUTS = [
        'brouillon'      => 'Brouillon',
        'envoyee'        => 'Envoyée',
        'confirmee'      => 'Confirmée',
        'livree_partiel' => 'Livraison partielle',
        'livree'         => 'Livrée',
        'annulee'        => 'Annulée',
    ];

    public const STATUT_COLORS = [
        'brouillon'      => 'gray',
        'envoyee'        => 'blue',
        'confirmee'      => 'indigo',
        'livree_partiel' => 'amber',
        'livree'         => 'green',
        'annulee'        => 'red',
    ];

    public const TRANSITIONS = [
        'brouillon'      => ['envoyee', 'annulee'],
        'envoyee'        => ['confirmee', 'annulee'],
        'confirmee'      => ['livree_partiel', 'livree', 'annulee'],
        'livree_partiel' => ['livree'],
        'livree'         => [],
        'annulee'        => ['brouillon'],
    ];

    // ──────────────────────────────────────
    // Relations
    // ──────────────────────────────────────

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class)->orderBy('ordre');
    }

    // ──────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────

    public function scopeSearch($query, ?string $search)
    {
        if (!$search) return $query;
        return $query->where(function ($q) use ($search) {
            $q->where('numero', 'like', "%{$search}%")
              ->orWhere('reference_fournisseur', 'like', "%{$search}%")
              ->orWhereHas('supplier', fn($sq) => $sq->where('raison_sociale', 'like', "%{$search}%"));
        });
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

    public function getIsEditableAttribute(): bool
    {
        return in_array($this->statut, ['brouillon']);
    }

    // ──────────────────────────────────────
    // State machine
    // ──────────────────────────────────────

    public function canTransitionTo(string $newStatut): bool
    {
        return in_array($newStatut, self::TRANSITIONS[$this->statut] ?? []);
    }

    public function transitionTo(string $newStatut): bool
    {
        if (!$this->canTransitionTo($newStatut)) return false;
        $this->update(['statut' => $newStatut]);
        return true;
    }

    public function getAvailableTransitions(): array
    {
        return collect(self::TRANSITIONS[$this->statut] ?? [])
            ->mapWithKeys(fn($s) => [$s => self::STATUTS[$s]])
            ->toArray();
    }

    // ──────────────────────────────────────
    // Numérotation
    // ──────────────────────────────────────

    public static function generateNumero(): string
    {
        $year = now()->year;
        $last = self::withTrashed()
            ->where('numero', 'like', "BC-{$year}-%")
            ->orderByDesc('numero')
            ->value('numero');

        $seq = $last ? (int) substr($last, -5) + 1 : 1;

        return "BC-{$year}-" . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    // ──────────────────────────────────────
    // Recalcul
    // ──────────────────────────────────────

    public function recalculateTotals(): void
    {
        $totalHt = $this->items()->sum('montant_ht');
        $tva = round($totalHt * $this->taux_tva / 100, 2);
        $ttc = round($totalHt + $tva, 2);
        $net = round($ttc - (float)$this->remise_globale, 2);

        $this->update([
            'total_ht'    => $totalHt,
            'montant_tva' => $tva,
            'total_ttc'   => $ttc,
            'net_a_payer' => max(0, $net),
        ]);
    }

    // ──────────────────────────────────────
    // Réception stock
    // ──────────────────────────────────────

    public function receiveItems(array $quantities): void
    {
        foreach ($this->items as $item) {
            $qteRecue = $quantities[$item->id] ?? 0;
            if ($qteRecue <= 0) continue;

            // Update received quantity
            $item->increment('quantite_recue', $qteRecue);

            // Add stock if product linked
            if ($item->product_id) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->addStock($qteRecue, 'achat', [
                        'prix_unitaire'      => $item->prix_unitaire,
                        'montant_total'      => round($qteRecue * $item->prix_unitaire, 2),
                        'reference_document' => $this->numero,
                        'notes'              => "Réception BC {$this->numero}",
                    ]);
                }
            }
        }

        // Check if fully received
        $allReceived = $this->items()->get()->every(fn($i) => $i->quantite_recue >= $i->quantite);
        $partiallyReceived = $this->items()->where('quantite_recue', '>', 0)->exists();

        if ($allReceived) {
            $this->update(['statut' => 'livree', 'date_reception' => now()]);
        } elseif ($partiallyReceived && $this->statut !== 'livree_partiel') {
            $this->update(['statut' => 'livree_partiel']);
        }
    }
}
