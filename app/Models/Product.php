<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference', 'code_barre', 'category_id',
        'designation', 'description', 'marque', 'modele_compatible',
        'type',
        'prix_achat', 'prix_vente', 'taux_tva', 'marge_percent',
        'quantite_stock', 'seuil_alerte', 'seuil_commande', 'quantite_max',
        'unite', 'emplacement',
        'fournisseur_nom', 'fournisseur_ref', 'delai_livraison_jours',
        'actif', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'prix_achat'      => 'decimal:2',
            'prix_vente'      => 'decimal:2',
            'taux_tva'        => 'decimal:2',
            'marge_percent'   => 'decimal:2',
            'quantite_stock'  => 'decimal:2',
            'seuil_alerte'    => 'decimal:2',
            'seuil_commande'  => 'decimal:2',
            'quantite_max'    => 'decimal:2',
            'actif'           => 'boolean',
        ];
    }

    // ──────────────────────────────────────
    // Constantes
    // ──────────────────────────────────────

    public const TYPES = [
        'piece'       => 'Pièce de rechange',
        'fourniture'  => 'Fourniture',
        'outillage'   => 'Outillage',
        'accessoire'  => 'Accessoire',
    ];

    public const TYPE_COLORS = [
        'piece'       => 'orange',
        'fourniture'  => 'green',
        'outillage'   => 'blue',
        'accessoire'  => 'purple',
    ];

    public const UNITES = [
        'u'     => 'Unité',
        'kg'    => 'Kilogramme',
        'l'     => 'Litre',
        'm'     => 'Mètre',
        'boite' => 'Boîte',
        'jeu'   => 'Jeu',
        'kit'   => 'Kit',
    ];

    // ──────────────────────────────────────
    // Relations
    // ──────────────────────────────────────

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class)->orderByDesc('created_at');
    }

    // ──────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────

    public function scopeSearch($query, ?string $search)
    {
        if (!$search) return $query;
        return $query->where(function ($q) use ($search) {
            $q->where('reference', 'like', "%{$search}%")
              ->orWhere('designation', 'like', "%{$search}%")
              ->orWhere('marque', 'like', "%{$search}%")
              ->orWhere('code_barre', 'like', "%{$search}%")
              ->orWhere('fournisseur_nom', 'like', "%{$search}%");
        });
    }

    public function scopeByType($query, ?string $type)
    {
        return $type ? $query->where('type', $type) : $query;
    }

    public function scopeByCategory($query, ?int $categoryId)
    {
        return $categoryId ? $query->where('category_id', $categoryId) : $query;
    }

    public function scopeActifs($query)
    {
        return $query->where('actif', true);
    }

    public function scopeEnAlerte($query)
    {
        return $query->where('actif', true)
                     ->whereColumn('quantite_stock', '<=', 'seuil_alerte');
    }

    public function scopeEnRupture($query)
    {
        return $query->where('actif', true)->where('quantite_stock', '<=', 0);
    }

    public function scopeACommander($query)
    {
        return $query->where('actif', true)
                     ->whereColumn('quantite_stock', '<=', 'seuil_commande');
    }

    // ──────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    public function getTypeColorAttribute(): string
    {
        return self::TYPE_COLORS[$this->type] ?? 'gray';
    }

    public function getTypeBadgeAttribute(): string
    {
        $c = $this->type_color;
        return "<span class=\"inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{$c}-50 text-{$c}-700\">{$this->type_label}</span>";
    }

    public function getUniteLabelAttribute(): string
    {
        return self::UNITES[$this->unite] ?? $this->unite;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->quantite_stock <= 0) return 'rupture';
        if ($this->quantite_stock <= $this->seuil_alerte) return 'alerte';
        if ($this->quantite_stock <= $this->seuil_commande) return 'bas';
        return 'ok';
    }

    public function getStockBadgeAttribute(): string
    {
        return match($this->stock_status) {
            'rupture' => '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Rupture</span>',
            'alerte'  => '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Alerte</span>',
            'bas'     => '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700"><span class="w-1.5 h-1.5 rounded-full bg-yellow-500"></span>Bas</span>',
            default   => '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>OK</span>',
        };
    }

    public function getMargeCalculeeAttribute(): ?float
    {
        if ($this->prix_achat <= 0) return null;
        return round(($this->prix_vente - $this->prix_achat) / $this->prix_achat * 100, 2);
    }

    public function getValeurStockAttribute(): float
    {
        return round($this->quantite_stock * $this->prix_achat, 2);
    }

    public function getPrixVenteTtcAttribute(): float
    {
        return round($this->prix_vente * (1 + $this->taux_tva / 100), 2);
    }

    // ──────────────────────────────────────
    // Génération de référence
    // ──────────────────────────────────────

    public static function generateReference(string $type = 'piece'): string
    {
        $prefixes = ['piece' => 'PR', 'fourniture' => 'FN', 'outillage' => 'OT', 'accessoire' => 'AC'];
        $prefix = $prefixes[$type] ?? 'PR';

        $last = self::withTrashed()
            ->where('reference', 'like', "{$prefix}-%")
            ->orderByDesc('reference')
            ->value('reference');

        $seq = $last ? (int) substr($last, strlen($prefix) + 1) + 1 : 1;

        return $prefix . '-' . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    // ──────────────────────────────────────
    // Mouvements de stock
    // ──────────────────────────────────────

    public function addStock(float $quantite, string $motif, array $extra = []): StockMovement
    {
        $stockAvant = (float) $this->quantite_stock;
        $stockApres = $stockAvant + $quantite;

        $this->update(['quantite_stock' => $stockApres]);

        return $this->stockMovements()->create(array_merge([
            'type'          => 'entree',
            'motif'         => $motif,
            'quantite'      => $quantite,
            'stock_avant'   => $stockAvant,
            'stock_apres'   => $stockApres,
            'recorded_by'   => auth()->id(),
        ], $extra));
    }

    public function removeStock(float $quantite, string $motif, array $extra = []): StockMovement
    {
        $stockAvant = (float) $this->quantite_stock;
        $stockApres = $stockAvant - $quantite;

        $this->update(['quantite_stock' => max(0, $stockApres)]);

        return $this->stockMovements()->create(array_merge([
            'type'          => 'sortie',
            'motif'         => $motif,
            'quantite'      => $quantite,
            'stock_avant'   => $stockAvant,
            'stock_apres'   => max(0, $stockApres),
            'recorded_by'   => auth()->id(),
        ], $extra));
    }

    public function adjustStock(float $newQuantite, string $notes = ''): StockMovement
    {
        $stockAvant = (float) $this->quantite_stock;
        $diff = $newQuantite - $stockAvant;
        $motif = $diff >= 0 ? 'inventaire_plus' : 'inventaire_moins';

        $this->update(['quantite_stock' => $newQuantite]);

        return $this->stockMovements()->create([
            'type'        => 'ajustement',
            'motif'       => $motif,
            'quantite'    => abs($diff),
            'stock_avant' => $stockAvant,
            'stock_apres' => $newQuantite,
            'recorded_by' => auth()->id(),
            'notes'       => $notes,
        ]);
    }
}
