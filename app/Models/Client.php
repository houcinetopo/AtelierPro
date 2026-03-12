<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type_client', 'nom_complet', 'cin',
        'raison_sociale', 'ice', 'registre_commerce', 'contact_societe',
        'telephone', 'telephone_2', 'email', 'adresse', 'ville', 'code_postal',
        'solde_credit', 'plafond_credit',
        'source', 'notes', 'is_blacklisted',
    ];

    protected function casts(): array
    {
        return [
            'solde_credit'   => 'decimal:2',
            'plafond_credit' => 'decimal:2',
            'is_blacklisted' => 'boolean',
        ];
    }

    public const SOURCES = [
        'direct'          => 'Direct (sans intermédiaire)',
        'recommandation'  => 'Recommandation client',
        'publicite'       => 'Publicité',
        'internet'        => 'Internet / Réseaux sociaux',
        'assurance'       => 'Compagnie d\'assurance',
        'autre'           => 'Autre',
    ];

    // ──────────────────────────────────────
    // Relations
    // ──────────────────────────────────────

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function repairOrders()
    {
        return $this->hasMany(\App\Models\RepairOrder::class ?? 'App\Models\RepairOrder');
    }

    public function deliveryNotes()
    {
        return $this->hasMany(\App\Models\DeliveryNote::class);
    }

    public function quotes()
    {
        return $this->hasMany(\App\Models\Quote::class);
    }

    public function invoices()
    {
        return $this->hasMany(\App\Models\Invoice::class ?? 'App\Models\Invoice');
    }

    // ──────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────

    public function scopeSearch($query, ?string $search)
    {
        if (!$search) return $query;
        return $query->where(function ($q) use ($search) {
            $q->where('nom_complet', 'like', "%{$search}%")
              ->orWhere('raison_sociale', 'like', "%{$search}%")
              ->orWhere('telephone', 'like', "%{$search}%")
              ->orWhere('telephone_2', 'like', "%{$search}%")
              ->orWhere('cin', 'like', "%{$search}%")
              ->orWhere('ice', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    public function scopeParticuliers($query)
    {
        return $query->where('type_client', 'particulier');
    }

    public function scopeSocietes($query)
    {
        return $query->where('type_client', 'societe');
    }

    public function scopeWithDebt($query)
    {
        return $query->where('solde_credit', '>', 0);
    }

    // ──────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────

    /**
     * Retourne le nom d'affichage (nom complet ou raison sociale)
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->type_client === 'societe'
            ? ($this->raison_sociale ?? $this->contact_societe ?? '—')
            : ($this->nom_complet ?? '—');
    }

    /**
     * Identifiant légal (CIN ou ICE)
     */
    public function getLegalIdAttribute(): ?string
    {
        return $this->type_client === 'societe' ? $this->ice : $this->cin;
    }

    public function getLegalIdLabelAttribute(): string
    {
        return $this->type_client === 'societe' ? 'ICE' : 'CIN';
    }

    public function getTypeBadgeAttribute(): string
    {
        return match($this->type_client) {
            'particulier' => '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Particulier</span>',
            'societe'     => '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700">Société</span>',
            default       => '',
        };
    }

    public function getInitialsAttribute(): string
    {
        $name = $this->display_name;
        $parts = explode(' ', $name);
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }
        return $initials ?: '?';
    }

    public function getAvatarUrlAttribute(): string
    {
        $bg = $this->type_client === 'societe' ? '8b5cf6' : '3b82f6';
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->display_name) . "&background={$bg}&color=fff&size=128";
    }

    public function getSourceLabelAttribute(): string
    {
        return self::SOURCES[$this->source] ?? ucfirst($this->source);
    }

    /**
     * Vérifie si le client a dépassé son plafond de crédit
     */
    public function isOverCreditLimit(): bool
    {
        if (!$this->plafond_credit) return false;
        return $this->solde_credit > $this->plafond_credit;
    }
}
