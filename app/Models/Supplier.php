<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'raison_sociale', 'nom_contact',
        'telephone', 'telephone_2', 'email', 'site_web',
        'adresse', 'ville', 'code_postal',
        'ice', 'rc', 'if_fiscal', 'patente', 'rib',
        'mode_paiement_defaut', 'delai_paiement_jours', 'remise_globale', 'delai_livraison_jours',
        'type', 'actif', 'solde_du', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'remise_globale'  => 'decimal:2',
            'solde_du'        => 'decimal:2',
            'actif'           => 'boolean',
        ];
    }

    // ──────────────────────────────────────
    // Constantes
    // ──────────────────────────────────────

    public const TYPES = [
        'pieces'    => 'Pièces de rechange',
        'peinture'  => 'Peinture & consommables',
        'outillage' => 'Outillage',
        'general'   => 'Fournisseur général',
    ];

    public const TYPE_COLORS = [
        'pieces'    => 'orange',
        'peinture'  => 'purple',
        'outillage' => 'blue',
        'general'   => 'gray',
    ];

    public const MODES_PAIEMENT = [
        'especes'  => 'Espèces',
        'cheque'   => 'Chèque',
        'virement' => 'Virement',
        'effet'    => 'Effet de commerce',
        'credit'   => 'Crédit',
    ];

    // ──────────────────────────────────────
    // Relations
    // ──────────────────────────────────────

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    // ──────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────

    public function scopeSearch($query, ?string $search)
    {
        if (!$search) return $query;
        return $query->where(function ($q) use ($search) {
            $q->where('code', 'like', "%{$search}%")
              ->orWhere('raison_sociale', 'like', "%{$search}%")
              ->orWhere('nom_contact', 'like', "%{$search}%")
              ->orWhere('ville', 'like', "%{$search}%")
              ->orWhere('telephone', 'like', "%{$search}%");
        });
    }

    public function scopeByType($query, ?string $type)
    {
        return $type ? $query->where('type', $type) : $query;
    }

    public function scopeActifs($query)
    {
        return $query->where('actif', true);
    }

    // ──────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    public function getTypeBadgeAttribute(): string
    {
        $c = self::TYPE_COLORS[$this->type] ?? 'gray';
        return "<span class=\"inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{$c}-50 text-{$c}-700\">{$this->type_label}</span>";
    }

    public function getModePaiementLabelAttribute(): string
    {
        return self::MODES_PAIEMENT[$this->mode_paiement_defaut] ?? ucfirst($this->mode_paiement_defaut);
    }

    public function getAdresseCompleteAttribute(): string
    {
        return collect([$this->adresse, $this->code_postal, $this->ville])
            ->filter()->implode(', ');
    }

    // ──────────────────────────────────────
    // Génération code
    // ──────────────────────────────────────

    public static function generateCode(): string
    {
        $last = self::withTrashed()
            ->where('code', 'like', 'FRS-%')
            ->orderByDesc('code')
            ->value('code');

        $seq = $last ? (int) substr($last, 4) + 1 : 1;

        return 'FRS-' . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    // ──────────────────────────────────────
    // Stats
    // ──────────────────────────────────────

    public function getTotalAchats30jAttribute(): float
    {
        return $this->purchaseOrders()
            ->where('date_commande', '>=', now()->subDays(30))
            ->whereNotIn('statut', ['annulee'])
            ->sum('net_a_payer');
    }

    public function getNbCommandesAttribute(): int
    {
        return $this->purchaseOrders()->count();
    }

    public function recalculateSolde(): void
    {
        $totalCommandes = $this->purchaseOrders()
            ->whereIn('statut', ['envoyee', 'confirmee', 'livree_partiel', 'livree'])
            ->sum('net_a_payer');

        // Simplification : le solde est le total des commandes non payées
        // Un système de paiement fournisseur complet nécessiterait un module dédié
        $this->update(['solde_du' => $totalCommandes]);
    }
}
