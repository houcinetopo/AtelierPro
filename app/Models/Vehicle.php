<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id', 'immatriculation', 'marque', 'modele', 'couleur',
        'annee', 'type_carburant', 'numero_chassis', 'puissance_fiscale',
        'compagnie_assurance', 'numero_police_assurance', 'date_expiration_assurance',
        'date_controle_technique', 'date_prochain_controle',
        'kilometrage', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'annee'                     => 'integer',
            'kilometrage'               => 'integer',
            'date_expiration_assurance' => 'date',
            'date_controle_technique'   => 'date',
            'date_prochain_controle'    => 'date',
        ];
    }

    public const MARQUES = [
        'Dacia', 'Renault', 'Peugeot', 'Citroën', 'Volkswagen', 'Toyota',
        'Hyundai', 'Kia', 'Fiat', 'Ford', 'Mercedes-Benz', 'BMW', 'Audi',
        'Nissan', 'Opel', 'Seat', 'Škoda', 'Suzuki', 'Mitsubishi', 'Honda',
        'Chevrolet', 'Jeep', 'Land Rover', 'Volvo', 'MG', 'Chery', 'BYD', 'Autre',
    ];

    public const CARBURANTS = [
        'essence'    => 'Essence',
        'diesel'     => 'Diesel',
        'gpl'        => 'GPL',
        'electrique' => 'Électrique',
        'hybride'    => 'Hybride',
    ];

    // ──────────────────────────────────────
    // Relations
    // ──────────────────────────────────────

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function photos()
    {
        return $this->hasMany(VehiclePhoto::class)->orderByDesc('created_at');
    }

    public function repairOrders()
    {
        return $this->hasMany(\App\Models\RepairOrder::class ?? 'App\Models\RepairOrder');
    }

    // ──────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────

    public function scopeSearch($query, ?string $search)
    {
        if (!$search) return $query;
        return $query->where(function ($q) use ($search) {
            $q->where('immatriculation', 'like', "%{$search}%")
              ->orWhere('marque', 'like', "%{$search}%")
              ->orWhere('modele', 'like', "%{$search}%")
              ->orWhere('numero_chassis', 'like', "%{$search}%");
        });
    }

    public function scopeByMarque($query, string $marque)
    {
        return $query->where('marque', $marque);
    }

    // ──────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return trim("{$this->marque} {$this->modele}");
    }

    public function getDisplayLabelAttribute(): string
    {
        return "{$this->full_name} — {$this->immatriculation}";
    }

    public function getCarburantLabelAttribute(): string
    {
        return self::CARBURANTS[$this->type_carburant] ?? ucfirst($this->type_carburant ?? '—');
    }

    public function getAssuranceStatusAttribute(): string
    {
        if (!$this->date_expiration_assurance) return 'unknown';
        if ($this->date_expiration_assurance->isPast()) return 'expired';
        if ($this->date_expiration_assurance->diffInDays(now()) <= 30) return 'expiring';
        return 'valid';
    }

    public function getAssuranceBadgeAttribute(): string
    {
        return match($this->assurance_status) {
            'expired'  => '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Expirée</span>',
            'expiring' => '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Expire bientôt</span>',
            'valid'    => '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Valide</span>',
            default    => '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Non renseignée</span>',
        };
    }

    public function getControleStatusAttribute(): string
    {
        if (!$this->date_prochain_controle) return 'unknown';
        if ($this->date_prochain_controle->isPast()) return 'expired';
        if ($this->date_prochain_controle->diffInDays(now()) <= 30) return 'expiring';
        return 'valid';
    }
}
