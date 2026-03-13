<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expert extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nom_complet', 'cabinet',
        'telephone', 'telephone_2',
        'adresse', 'ville', 'code_postal',
        'actif', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
        ];
    }

    // ──────────────────────────────────────
    // Relations
    // ──────────────────────────────────────

    public function emails()
    {
        return $this->hasMany(ExpertEmail::class)->orderByDesc('is_primary');
    }

    public function repairOrders()
    {
        return $this->hasMany(RepairOrder::class);
    }

    // ──────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────

    public function getPrimaryEmailAttribute(): ?string
    {
        $primary = $this->emails->firstWhere('is_primary', true);
        return $primary?->email ?? $this->emails->first()?->email;
    }

    public function getAllEmailsAttribute(): array
    {
        return $this->emails->pluck('email')->toArray();
    }

    public function getDisplayNameAttribute(): string
    {
        $name = $this->nom_complet;
        if ($this->cabinet) {
            $name .= " ({$this->cabinet})";
        }
        return $name;
    }

    public function getInitialsAttribute(): string
    {
        $parts = explode(' ', $this->nom_complet);
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }
        return $initials ?: '?';
    }

    // ──────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────

    public function scopeSearch($query, ?string $search)
    {
        if (!$search) return $query;
        return $query->where(function ($q) use ($search) {
            $q->where('nom_complet', 'like', "%{$search}%")
              ->orWhere('cabinet', 'like', "%{$search}%")
              ->orWhere('telephone', 'like', "%{$search}%")
              ->orWhereHas('emails', fn($e) => $e->where('email', 'like', "%{$search}%"));
        });
    }

    public function scopeActifs($query)
    {
        return $query->where('actif', true);
    }
}
