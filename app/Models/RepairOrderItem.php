<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepairOrderItem extends Model
{
    protected $fillable = [
        'repair_order_id', 'product_id', 'fournisseur_id',
        'type', 'designation', 'reference', 'description',
        'quantite', 'unite', 'prix_unitaire', 'prix_achat', 'remise',
        'montant_ht', 'taux_tva', 'montant_ttc', 'ordre', 'source',
    ];

    protected function casts(): array
    {
        return [
            'quantite'      => 'decimal:2',
            'prix_unitaire' => 'decimal:2',
            'prix_achat'    => 'decimal:2',
            'remise'        => 'decimal:2',
            'montant_ht'    => 'decimal:2',
            'taux_tva'      => 'decimal:2',
            'montant_ttc'   => 'decimal:2',
        ];
    }

    public const TYPES = [
        'main_oeuvre'    => 'Main d\'œuvre',
        'piece'          => 'Pièce de rechange',
        'fourniture'     => 'Fourniture',
        'sous_traitance' => 'Sous-traitance',
    ];

    public const UNITES = [
        'u'       => 'Unité',
        'h'       => 'Heure',
        'forfait' => 'Forfait',
        'm'       => 'Mètre',
        'kg'      => 'Kg',
        'l'       => 'Litre',
    ];

    // ── Relations ──

    public function repairOrder()
    {
        return $this->belongsTo(RepairOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function fournisseur()
    {
        return $this->belongsTo(Supplier::class, 'fournisseur_id');
    }

    // ── Accessors ──

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    public function getUniteLabelAttribute(): string
    {
        return self::UNITES[$this->unite] ?? $this->unite;
    }

    // ── Calcul automatique ──

    public function calculateTotals(): void
    {
        $ht = $this->quantite * $this->prix_unitaire;
        if ($this->remise > 0) {
            $ht -= $ht * $this->remise / 100;
        }
        $this->montant_ht = round($ht, 2);
        $this->montant_ttc = round($ht + ($ht * $this->taux_tva / 100), 2);
    }

    protected static function booted(): void
    {
        static::saving(function (self $item) {
            $item->calculateTotals();
        });

        static::saved(function (self $item) {
            $item->repairOrder->recalculateTotals();
        });

        static::deleted(function (self $item) {
            $item->repairOrder->recalculateTotals();
        });
    }
}
