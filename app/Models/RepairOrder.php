<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RepairOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'numero', 'client_id', 'vehicle_id', 'technicien_id', 'created_by',
        'date_reception', 'date_prevue_livraison', 'date_livraison_effective',
        'status', 'kilometrage_entree', 'kilometrage_sortie',
        'total_ht', 'taux_tva', 'montant_tva', 'total_ttc',
        'remise_globale', 'net_a_payer',
        'description_panne', 'diagnostic', 'observations', 'notes_internes',
        'niveau_carburant', 'etat_vehicule', 'source_ordre',
    ];

    protected function casts(): array
    {
        return [
            'date_reception'           => 'date',
            'date_prevue_livraison'    => 'date',
            'date_livraison_effective' => 'date',
            'total_ht'                 => 'decimal:2',
            'taux_tva'                 => 'decimal:2',
            'montant_tva'              => 'decimal:2',
            'total_ttc'                => 'decimal:2',
            'remise_globale'           => 'decimal:2',
            'net_a_payer'              => 'decimal:2',
            'etat_vehicule'            => 'array',
        ];
    }

    // ──────────────────────────────────────
    // Constantes
    // ──────────────────────────────────────

    public const STATUSES = [
        'brouillon'  => 'Brouillon',
        'en_cours'   => 'En cours',
        'en_attente' => 'En attente',
        'termine'    => 'Terminé',
        'livre'      => 'Livré',
        'facture'    => 'Facturé',
        'annule'     => 'Annulé',
    ];

    public const STATUS_COLORS = [
        'brouillon'  => 'gray',
        'en_cours'   => 'blue',
        'en_attente' => 'amber',
        'termine'    => 'green',
        'livre'      => 'indigo',
        'facture'    => 'emerald',
        'annule'     => 'red',
    ];

    public const NIVEAUX_CARBURANT = ['vide', '1/4', '1/2', '3/4', 'plein'];

    public const SOURCES = [
        'direct'    => 'Direct',
        'telephone' => 'Téléphone',
        'assurance' => 'Assurance',
        'expertise' => 'Expertise',
        'autre'     => 'Autre',
    ];

    // ──────────────────────────────────────
    // Relations
    // ──────────────────────────────────────

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function technicien()
    {
        return $this->belongsTo(User::class, 'technicien_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(RepairOrderItem::class)->orderBy('ordre');
    }

    public function photos()
    {
        return $this->hasMany(RepairOrderPhoto::class)->orderBy('moment');
    }

    public function deliveryNote()
    {
        return $this->hasOne(DeliveryNote::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    // ──────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────

    public function scopeSearch($query, ?string $search)
    {
        if (!$search) return $query;
        return $query->where(function ($q) use ($search) {
            $q->where('numero', 'like', "%{$search}%")
              ->orWhere('description_panne', 'like', "%{$search}%")
              ->orWhereHas('client', fn($c) => $c->where('nom_complet', 'like', "%{$search}%")->orWhere('raison_sociale', 'like', "%{$search}%"))
              ->orWhereHas('vehicle', fn($v) => $v->where('immatriculation', 'like', "%{$search}%")->orWhere('marque', 'like', "%{$search}%"));
        });
    }

    public function scopeByStatus($query, ?string $status)
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeByTechnicien($query, ?int $technicienId)
    {
        return $technicienId ? $query->where('technicien_id', $technicienId) : $query;
    }

    public function scopeForTechnicien($query, int $userId)
    {
        return $query->where('technicien_id', $userId);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['en_cours', 'en_attente']);
    }

    public function scopeLate($query)
    {
        return $query->where('date_prevue_livraison', '<', now())
                     ->whereNotIn('status', ['livre', 'facture', 'annule', 'brouillon']);
    }

    // ──────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }

    public function getStatusBadgeAttribute(): string
    {
        $color = $this->status_color;
        return "<span class=\"inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-{$color}-100 text-{$color}-700\"><span class=\"w-1.5 h-1.5 rounded-full bg-{$color}-500\"></span>{$this->status_label}</span>";
    }

    public function getIsLateAttribute(): bool
    {
        return $this->date_prevue_livraison
            && $this->date_prevue_livraison->isPast()
            && !in_array($this->status, ['livre', 'facture', 'annule', 'brouillon']);
    }

    public function getClientNameAttribute(): string
    {
        if (!$this->client) return '—';
        return $this->client->nom_complet ?? $this->client->raison_sociale ?? '—';
    }

    public function getVehicleLabelAttribute(): string
    {
        if (!$this->vehicle) return '—';
        return trim("{$this->vehicle->marque} {$this->vehicle->modele}") . " ({$this->vehicle->immatriculation})";
    }

    public function getDureeReelleAttribute(): ?string
    {
        if (!$this->date_livraison_effective) return null;
        $diff = $this->date_reception->diff($this->date_livraison_effective);
        return $diff->days . ' jour' . ($diff->days > 1 ? 's' : '');
    }

    // ──────────────────────────────────────
    // Génération de numéro
    // ──────────────────────────────────────

    public static function generateNumero(): string
    {
        $year = now()->format('Y');
        $prefix = "OR-{$year}-";
        $last = self::withTrashed()
            ->where('numero', 'like', "{$prefix}%")
            ->orderByDesc('numero')
            ->value('numero');

        if ($last) {
            $seq = (int) substr($last, strlen($prefix)) + 1;
        } else {
            $seq = 1;
        }

        return $prefix . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    // ──────────────────────────────────────
    // Recalcul des totaux
    // ──────────────────────────────────────

    public function recalculateTotals(): void
    {
        $totalHt = $this->items()->sum('montant_ht');
        $montantTva = round($totalHt * $this->taux_tva / 100, 2);
        $totalTtc = round($totalHt + $montantTva, 2);
        $netAPayer = round($totalTtc - $this->remise_globale, 2);

        $this->update([
            'total_ht'    => $totalHt,
            'montant_tva' => $montantTva,
            'total_ttc'   => $totalTtc,
            'net_a_payer' => max(0, $netAPayer),
        ]);
    }

    // ──────────────────────────────────────
    // Transitions de statut
    // ──────────────────────────────────────

    public function canTransitionTo(string $newStatus): bool
    {
        $allowed = match($this->status) {
            'brouillon'  => ['en_cours', 'annule'],
            'en_cours'   => ['en_attente', 'termine', 'annule'],
            'en_attente' => ['en_cours', 'annule'],
            'termine'    => ['livre', 'en_cours'],
            'livre'      => ['facture'],
            'facture'    => [],
            'annule'     => ['brouillon'],
            default      => [],
        };
        return in_array($newStatus, $allowed);
    }

    public function transitionTo(string $newStatus): bool
    {
        if (!$this->canTransitionTo($newStatus)) return false;

        $oldStatus = $this->status;
        $this->update(['status' => $newStatus]);

        if ($newStatus === 'livre' && !$this->date_livraison_effective) {
            $this->update(['date_livraison_effective' => now()]);
        }

        ActivityLog::log('update', "Ordre {$this->numero} : {$oldStatus} → {$newStatus}", $this, ['status' => $oldStatus]);

        return true;
    }
}
