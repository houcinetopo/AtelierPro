<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'numero', 'client_id', 'vehicle_id', 'repair_order_id', 'created_by',
        'date_devis', 'date_validite', 'date_acceptation',
        'statut',
        'total_ht', 'taux_tva', 'montant_tva', 'total_ttc',
        'remise_globale', 'net_a_payer',
        'description_travaux', 'conditions', 'notes', 'motif_refus',
        'duree_estimee_jours',
    ];

    protected function casts(): array
    {
        return [
            'date_devis'       => 'date',
            'date_validite'    => 'date',
            'date_acceptation' => 'date',
            'total_ht'         => 'decimal:2',
            'taux_tva'         => 'decimal:2',
            'montant_tva'      => 'decimal:2',
            'total_ttc'        => 'decimal:2',
            'remise_globale'   => 'decimal:2',
            'net_a_payer'      => 'decimal:2',
        ];
    }

    // ──────────────────────────────────────
    // Constantes
    // ──────────────────────────────────────

    public const STATUTS = [
        'brouillon' => 'Brouillon',
        'envoye'    => 'Envoyé',
        'accepte'   => 'Accepté',
        'refuse'    => 'Refusé',
        'expire'    => 'Expiré',
        'converti'  => 'Converti en OR',
        'annule'    => 'Annulé',
    ];

    public const STATUT_COLORS = [
        'brouillon' => 'gray',
        'envoye'    => 'blue',
        'accepte'   => 'green',
        'refuse'    => 'red',
        'expire'    => 'amber',
        'converti'  => 'indigo',
        'annule'    => 'red',
    ];

    public const STATUT_ICONS = [
        'brouillon' => 'file-edit',
        'envoye'    => 'send',
        'accepte'   => 'check-circle',
        'refuse'    => 'x-circle',
        'expire'    => 'clock',
        'converti'  => 'arrow-right-circle',
        'annule'    => 'ban',
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

    public function repairOrder()
    {
        return $this->belongsTo(RepairOrder::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(QuoteItem::class)->orderBy('ordre');
    }

    // ──────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────

    public function scopeSearch($query, ?string $search)
    {
        if (!$search) return $query;
        return $query->where(function ($q) use ($search) {
            $q->where('numero', 'like', "%{$search}%")
              ->orWhere('description_travaux', 'like', "%{$search}%")
              ->orWhereHas('client', fn($c) => $c->where('nom_complet', 'like', "%{$search}%")->orWhere('raison_sociale', 'like', "%{$search}%"))
              ->orWhereHas('vehicle', fn($v) => $v->where('immatriculation', 'like', "%{$search}%"));
        });
    }

    public function scopeByStatut($query, ?string $statut)
    {
        return $statut ? $query->where('statut', $statut) : $query;
    }

    public function scopeEnAttente($query)
    {
        return $query->whereIn('statut', ['brouillon', 'envoye']);
    }

    public function scopeExpires($query)
    {
        return $query->where('statut', 'envoye')
                     ->where('date_validite', '<', now());
    }

    // ──────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────

    public function getStatutLabelAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? ucfirst($this->statut);
    }

    public function getStatutColorAttribute(): string
    {
        return self::STATUT_COLORS[$this->statut] ?? 'gray';
    }

    public function getStatutBadgeAttribute(): string
    {
        $c = $this->statut_color;
        return "<span class=\"inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-{$c}-100 text-{$c}-700\"><span class=\"w-1.5 h-1.5 rounded-full bg-{$c}-500\"></span>{$this->statut_label}</span>";
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

    public function getIsExpiredAttribute(): bool
    {
        return $this->statut === 'envoye' && $this->date_validite && $this->date_validite->isPast();
    }

    public function getIsConvertibleAttribute(): bool
    {
        return in_array($this->statut, ['accepte']) && !$this->repair_order_id;
    }

    public function getTauxAcceptationAttribute(): ?string
    {
        return null; // Utilisé au niveau stats globales
    }

    // ──────────────────────────────────────
    // Génération de numéro
    // ──────────────────────────────────────

    public static function generateNumero(): string
    {
        $year = now()->format('Y');
        $prefix = "DV-{$year}-";
        $last = self::withTrashed()
            ->where('numero', 'like', "{$prefix}%")
            ->orderByDesc('numero')
            ->value('numero');

        $seq = $last ? (int) substr($last, strlen($prefix)) + 1 : 1;

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

    public function canTransitionTo(string $newStatut): bool
    {
        $allowed = match($this->statut) {
            'brouillon' => ['envoye', 'annule'],
            'envoye'    => ['accepte', 'refuse', 'expire', 'annule'],
            'accepte'   => ['converti', 'annule'],
            'refuse'    => ['brouillon'],
            'expire'    => ['brouillon'],
            'converti'  => [],
            'annule'    => ['brouillon'],
            default     => [],
        };
        return in_array($newStatut, $allowed);
    }

    public function transitionTo(string $newStatut, ?string $motifRefus = null): bool
    {
        if (!$this->canTransitionTo($newStatut)) return false;

        $oldStatut = $this->statut;
        $updates = ['statut' => $newStatut];

        if ($newStatut === 'accepte') {
            $updates['date_acceptation'] = now();
        }
        if ($newStatut === 'refuse' && $motifRefus) {
            $updates['motif_refus'] = $motifRefus;
        }

        $this->update($updates);

        ActivityLog::log('update', "Devis {$this->numero} : {$oldStatut} → {$newStatut}", $this, ['statut' => $oldStatut]);

        return true;
    }

    // ──────────────────────────────────────
    // Conversion en OR
    // ──────────────────────────────────────

    public function convertToRepairOrder(): ?RepairOrder
    {
        if (!$this->is_convertible) return null;

        $order = RepairOrder::create([
            'numero'              => RepairOrder::generateNumero(),
            'client_id'           => $this->client_id,
            'vehicle_id'          => $this->vehicle_id,
            'created_by'          => auth()->id(),
            'date_reception'      => now(),
            'date_prevue_livraison' => $this->duree_estimee_jours ? now()->addDays($this->duree_estimee_jours) : null,
            'description_panne'   => $this->description_travaux,
            'status'              => 'brouillon',
            'taux_tva'            => $this->taux_tva,
            'remise_globale'      => $this->remise_globale,
            'source_ordre'        => 'direct',
        ]);

        // Copier les lignes
        foreach ($this->items as $i => $item) {
            $order->items()->create([
                'type'          => $item->type,
                'designation'   => $item->designation,
                'reference'     => $item->reference,
                'description'   => $item->description,
                'quantite'      => $item->quantite,
                'unite'         => $item->unite,
                'prix_unitaire' => $item->prix_unitaire,
                'remise'        => $item->remise,
                'taux_tva'      => $item->taux_tva,
                'ordre'         => $i,
            ]);
        }

        // Lier et marquer converti
        $this->update([
            'repair_order_id' => $order->id,
            'statut'          => 'converti',
        ]);

        ActivityLog::log('create', "Devis {$this->numero} converti en OR {$order->numero}", $order);

        return $order;
    }
}
