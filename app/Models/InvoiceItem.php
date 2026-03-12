<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id', 'type', 'designation', 'reference', 'description',
        'quantite', 'unite', 'prix_unitaire', 'remise',
        'montant_ht', 'taux_tva', 'montant_ttc', 'ordre',
    ];

    protected function casts(): array
    {
        return [
            'quantite'      => 'decimal:2',
            'prix_unitaire' => 'decimal:2',
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
        'u' => 'Unité', 'h' => 'Heure', 'forfait' => 'Forfait',
        'm' => 'Mètre', 'kg' => 'Kg', 'l' => 'Litre',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

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
            $item->invoice->recalculateTotals();
        });

        static::deleted(function (self $item) {
            $item->invoice->recalculateTotals();
        });
    }
}
