<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nom_complet', 'cin', 'photo', 'poste', 'date_embauche',
        'type_contrat', 'salaire_base', 'jours_travail_mois',
        'telephone', 'adresse', 'ville', 'cnss', 'email',
        'date_naissance', 'contact_urgence', 'telephone_urgence',
        'notes', 'statut',
    ];

    protected function casts(): array
    {
        return [
            'date_embauche'  => 'date',
            'date_naissance' => 'date',
            'salaire_base'   => 'decimal:2',
        ];
    }

    // ──────────────────────────────────────
    // Constantes
    // ──────────────────────────────────────

    public const POSTES = [
        'chef_atelier'    => 'Chef d\'atelier',
        'mecanicien'      => 'Mécanicien',
        'carrossier'      => 'Carrossier',
        'peintre'         => 'Peintre',
        'electricien'     => 'Électricien auto',
        'preparateur'     => 'Préparateur',
        'magasinier'      => 'Magasinier',
        'secretaire'      => 'Secrétaire',
        'comptable'       => 'Comptable',
        'receptionniste'  => 'Réceptionniste',
        'laveur'          => 'Laveur',
        'apprenti'        => 'Apprenti',
        'stagiaire'       => 'Stagiaire',
        'autre'           => 'Autre',
    ];

    public const TYPES_CONTRAT = ['CDI', 'CDD', 'Stage', 'Freelance'];

    // ──────────────────────────────────────
    // Relations
    // ──────────────────────────────────────

    public function payments()
    {
        return $this->hasMany(EmployeePayment::class)->orderByDesc('date_paiement');
    }

    // ──────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeByPoste($query, string $poste)
    {
        return $query->where('poste', $poste);
    }

    public function scopeSearch($query, ?string $search)
    {
        if (!$search) return $query;
        return $query->where(function ($q) use ($search) {
            $q->where('nom_complet', 'like', "%{$search}%")
              ->orWhere('cin', 'like', "%{$search}%")
              ->orWhere('telephone', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    // ──────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────

    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo) {
            return Storage::disk('public')->url($this->photo);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->nom_complet) . '&background=6366f1&color=fff&size=128';
    }

    public function getPosteLabelAttribute(): string
    {
        return self::POSTES[$this->poste] ?? ucfirst($this->poste);
    }

    public function getSalaireJournalierAttribute(): float
    {
        if ($this->jours_travail_mois <= 0) return 0;
        return round($this->salaire_base / $this->jours_travail_mois, 2);
    }

    public function getAncienneteAttribute(): string
    {
        if (!$this->date_embauche) return '—';
        $diff = $this->date_embauche->diff(now());
        $parts = [];
        if ($diff->y > 0) $parts[] = $diff->y . ' an' . ($diff->y > 1 ? 's' : '');
        if ($diff->m > 0) $parts[] = $diff->m . ' mois';
        return implode(' et ', $parts) ?: 'Moins d\'un mois';
    }

    public function getStatutBadgeAttribute(): string
    {
        return match($this->statut) {
            'actif'  => '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Actif</span>',
            'inactif'=> '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500"><span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>Inactif</span>',
            default  => '',
        };
    }

    // ──────────────────────────────────────
    // Helpers paiements
    // ──────────────────────────────────────

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()->sum('net_paye');
    }

    public function getLastPaymentAttribute(): ?EmployeePayment
    {
        return $this->payments()->first();
    }

    /**
     * Vérifie si le salaire a été payé pour une période donnée
     */
    public function isPaidForPeriod(string $periode): bool
    {
        return $this->payments()->where('periode', $periode)->exists();
    }
}
