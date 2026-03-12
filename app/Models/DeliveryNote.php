<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryNote extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'numero', 'repair_order_id', 'client_id', 'vehicle_id', 'created_by',
        'date_livraison', 'heure_livraison',
        'kilometrage_sortie', 'niveau_carburant',
        'travaux_effectues', 'observations_sortie', 'reserves_client', 'recommandations',
        'signe_atelier', 'signe_client', 'nom_receptionnaire', 'cin_receptionnaire', 'signature_client_path',
        'total_ttc', 'montant_paye', 'reste_a_payer', 'mode_paiement',
        'statut', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_livraison'  => 'date',
            'total_ttc'       => 'decimal:2',
            'montant_paye'    => 'decimal:2',
            'reste_a_payer'   => 'decimal:2',
            'signe_atelier'   => 'boolean',
            'signe_client'    => 'boolean',
        ];
    }

    // ──────────────────────────────────────
    // Constantes
    // ──────────────────────────────────────

    public const STATUTS = [
        'brouillon' => 'Brouillon',
        'valide'    => 'Validé',
        'annule'    => 'Annulé',
    ];

    public const STATUT_COLORS = [
        'brouillon' => 'gray',
        'valide'    => 'green',
        'annule'    => 'red',
    ];

    public const MODES_PAIEMENT = [
        'especes'  => 'Espèces',
        'cheque'   => 'Chèque',
        'virement' => 'Virement',
        'carte'    => 'Carte bancaire',
        'credit'   => 'À crédit',
        'mixte'    => 'Mixte',
    ];

    public const NIVEAUX_CARBURANT = ['vide', '1/4', '1/2', '3/4', 'plein'];

    // ──────────────────────────────────────
    // Relations
    // ──────────────────────────────────────

    public function repairOrder()
    {
        return $this->belongsTo(RepairOrder::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ──────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────

    public function scopeSearch($query, ?string $search)
    {
        if (!$search) return $query;
        return $query->where(function ($q) use ($search) {
            $q->where('numero', 'like', "%{$search}%")
              ->orWhere('nom_receptionnaire', 'like', "%{$search}%")
              ->orWhereHas('client', fn($c) => $c->where('nom_complet', 'like', "%{$search}%")->orWhere('raison_sociale', 'like', "%{$search}%"))
              ->orWhereHas('vehicle', fn($v) => $v->where('immatriculation', 'like', "%{$search}%"))
              ->orWhereHas('repairOrder', fn($r) => $r->where('numero', 'like', "%{$search}%"));
        });
    }

    public function scopeByStatut($query, ?string $statut)
    {
        return $statut ? $query->where('statut', $statut) : $query;
    }

    public function scopeValides($query)
    {
        return $query->where('statut', 'valide');
    }

    public function scopeWithUnpaid($query)
    {
        return $query->where('reste_a_payer', '>', 0);
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

    public function getModePaiementLabelAttribute(): string
    {
        return self::MODES_PAIEMENT[$this->mode_paiement] ?? ucfirst($this->mode_paiement ?? '—');
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

    public function getIsPaidAttribute(): bool
    {
        return $this->reste_a_payer <= 0;
    }

    // ──────────────────────────────────────
    // Génération de numéro
    // ──────────────────────────────────────

    public static function generateNumero(): string
    {
        $year = now()->format('Y');
        $prefix = "BL-{$year}-";
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
    // Helpers
    // ──────────────────────────────────────

    /**
     * Crée un BL à partir d'un OR terminé/livré
     */
    public static function createFromRepairOrder(RepairOrder $order, array $extra = []): self
    {
        return self::create(array_merge([
            'numero'            => self::generateNumero(),
            'repair_order_id'   => $order->id,
            'client_id'         => $order->client_id,
            'vehicle_id'        => $order->vehicle_id,
            'created_by'        => auth()->id(),
            'date_livraison'    => now()->toDateString(),
            'heure_livraison'   => now()->format('H:i'),
            'kilometrage_sortie'=> $order->kilometrage_sortie ?? $order->kilometrage_entree,
            'niveau_carburant'  => $order->niveau_carburant,
            'total_ttc'         => $order->net_a_payer,
            'montant_paye'      => 0,
            'reste_a_payer'     => $order->net_a_payer,
            'statut'            => 'brouillon',
            'travaux_effectues' => $order->items->map(fn($i) => "- {$i->designation}")->implode("\n"),
        ], $extra));
    }
}
