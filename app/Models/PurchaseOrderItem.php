<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id', 'product_id',
        'designation', 'reference', 'quantite', 'quantite_recue',
        'unite', 'prix_unitaire', 'remise',
        'montant_ht', 'taux_tva', 'montant_ttc', 'ordre',
    ];

    protected function casts(): array
    {
        return [
            'quantite'       => 'decimal:2',
            'quantite_recue' => 'decimal:2',
            'prix_unitaire'  => 'decimal:2',
            'remise'         => 'decimal:2',
            'montant_ht'     => 'decimal:2',
            'taux_tva'       => 'decimal:2',
            'montant_ttc'    => 'decimal:2',
        ];
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getResteARecevoirAttribute(): float
    {
        return max(0, (float)$this->quantite - (float)$this->quantite_recue);
    }

    public function getIsFullyReceivedAttribute(): bool
    {
        return $this->quantite_recue >= $this->quantite;
    }

    public function calculateTotals(): void
    {
        $ht = round($this->quantite * $this->prix_unitaire * (1 - $this->remise / 100), 2);
        $ttc = round($ht * (1 + $this->taux_tva / 100), 2);
        $this->montant_ht = $ht;
        $this->montant_ttc = $ttc;
    }

    protected static function booted(): void
    {
        static::saving(function (self $item) {
            $item->calculateTotals();
        });

        static::saved(function (self $item) {
            $item->purchaseOrder->recalculateTotals();
        });

        static::deleted(function (self $item) {
            $item->purchaseOrder->recalculateTotals();
        });
    }
}
