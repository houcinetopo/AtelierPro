<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id', 'recorded_by',
        'repair_order_id', 'repair_order_item_id',
        'type', 'motif', 'quantite',
        'stock_avant', 'stock_apres',
        'prix_unitaire', 'montant_total',
        'reference_document', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantite'       => 'decimal:2',
            'stock_avant'    => 'decimal:2',
            'stock_apres'    => 'decimal:2',
            'prix_unitaire'  => 'decimal:2',
            'montant_total'  => 'decimal:2',
        ];
    }

    public const TYPES = [
        'entree'      => 'Entrée',
        'sortie'      => 'Sortie',
        'ajustement'  => 'Ajustement',
        'inventaire'  => 'Inventaire',
    ];

    public const MOTIFS_ENTREE = [
        'achat'           => 'Achat fournisseur',
        'retour_client'   => 'Retour client',
        'inventaire_plus' => 'Correction inventaire (+)',
        'transfert_in'    => 'Transfert reçu',
    ];

    public const MOTIFS_SORTIE = [
        'consommation_or'    => 'Utilisé dans un OR',
        'retour_fournisseur' => 'Retour fournisseur',
        'perte'              => 'Perte / Casse',
        'inventaire_moins'   => 'Correction inventaire (-)',
        'transfert_out'      => 'Transfert envoyé',
    ];

    public static function allMotifs(): array
    {
        return array_merge(self::MOTIFS_ENTREE, self::MOTIFS_SORTIE);
    }

    // ──────────────────────────────────────
    // Relations
    // ──────────────────────────────────────

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function repairOrder()
    {
        return $this->belongsTo(RepairOrder::class);
    }

    // ──────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    public function getMotifLabelAttribute(): string
    {
        return self::allMotifs()[$this->motif] ?? ucfirst($this->motif);
    }

    public function getTypeBadgeAttribute(): string
    {
        $c = match($this->type) {
            'entree'     => 'green',
            'sortie'     => 'red',
            'ajustement' => 'amber',
            'inventaire' => 'blue',
            default      => 'gray',
        };
        return "<span class=\"inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-{$c}-100 text-{$c}-700\">{$this->type_label}</span>";
    }

    public function getIsEntreeAttribute(): bool
    {
        return $this->type === 'entree' || ($this->type === 'ajustement' && $this->stock_apres > $this->stock_avant);
    }
}
