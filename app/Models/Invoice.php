<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'numero', 'repair_order_id', 'client_id', 'vehicle_id', 'delivery_note_id', 'created_by',
        'date_facture', 'date_echeance',
        'statut',
        'total_ht', 'taux_tva', 'montant_tva', 'total_ttc',
        'remise_globale', 'net_a_payer', 'total_paye', 'reste_a_payer',
        'objet', 'conditions_paiement', 'notes', 'mentions_legales',
    ];

    protected function casts(): array
    {
        return [
            'date_facture'  => 'date',
            'date_echeance' => 'date',
            'total_ht'      => 'decimal:2',
            'taux_tva'      => 'decimal:2',
            'montant_tva'   => 'decimal:2',
            'total_ttc'     => 'decimal:2',
            'remise_globale'=> 'decimal:2',
            'net_a_payer'   => 'decimal:2',
            'total_paye'    => 'decimal:2',
            'reste_a_payer' => 'decimal:2',
        ];
    }

    // ──────────────────────────────────────
    // Constantes
    // ──────────────────────────────────────

    public const STATUTS = [
        'brouillon'  => 'Brouillon',
        'emise'      => 'Émise',
        'payee'      => 'Payée',
        'partielle'  => 'Partiellement payée',
        'en_retard'  => 'En retard',
        'annulee'    => 'Annulée',
    ];

    public const STATUT_COLORS = [
        'brouillon'  => 'gray',
        'emise'      => 'blue',
        'payee'      => 'green',
        'partielle'  => 'amber',
        'en_retard'  => 'red',
        'annulee'    => 'red',
    ];

    public const MODES_PAIEMENT = [
        'especes'  => 'Espèces',
        'cheque'   => 'Chèque',
        'virement' => 'Virement',
        'carte'    => 'Carte bancaire',
        'effet'    => 'Effet de commerce',
    ];

    public const MENTIONS_LEGALES_DEFAULT = "Conformément à la loi 09-08 relative à la protection des personnes physiques à l'égard du traitement des données à caractère personnel.\nPénalités de retard : 1,5% par mois de retard.\nPas d'escompte pour paiement anticipé.";

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

    public function deliveryNote()
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('ordre');
    }

    public function payments()
    {
        return $this->hasMany(InvoicePayment::class)->orderByDesc('date_paiement');
    }

    // ──────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────

    public function scopeSearch($query, ?string $search)
    {
        if (!$search) return $query;
        return $query->where(function ($q) use ($search) {
            $q->where('numero', 'like', "%{$search}%")
              ->orWhere('objet', 'like', "%{$search}%")
              ->orWhereHas('client', fn($c) => $c->where('nom_complet', 'like', "%{$search}%")->orWhere('raison_sociale', 'like', "%{$search}%"))
              ->orWhereHas('vehicle', fn($v) => $v->where('immatriculation', 'like', "%{$search}%"))
              ->orWhereHas('repairOrder', fn($r) => $r->where('numero', 'like', "%{$search}%"));
        });
    }

    public function scopeByStatut($query, ?string $statut)
    {
        return $statut ? $query->where('statut', $statut) : $query;
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('statut', ['emise', 'partielle', 'en_retard']);
    }

    public function scopeOverdue($query)
    {
        return $query->whereIn('statut', ['emise', 'partielle'])
                     ->whereNotNull('date_echeance')
                     ->where('date_echeance', '<', now());
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

    public function getIsOverdueAttribute(): bool
    {
        return in_array($this->statut, ['emise', 'partielle'])
            && $this->date_echeance
            && $this->date_echeance->isPast();
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->reste_a_payer <= 0 && $this->statut === 'payee';
    }

    public function getProgressPercentAttribute(): float
    {
        if ($this->net_a_payer <= 0) return 100;
        return min(100, round(($this->total_paye / $this->net_a_payer) * 100, 1));
    }

    // ──────────────────────────────────────
    // Génération de numéro
    // ──────────────────────────────────────

    public static function generateNumero(): string
    {
        $year = now()->format('Y');
        $prefix = "FA-{$year}-";
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
        $netAPayer = max(0, $netAPayer);

        $this->update([
            'total_ht'      => $totalHt,
            'montant_tva'   => $montantTva,
            'total_ttc'     => $totalTtc,
            'net_a_payer'   => $netAPayer,
            'reste_a_payer' => max(0, $netAPayer - $this->total_paye),
        ]);
    }

    // ──────────────────────────────────────
    // Gestion des paiements
    // ──────────────────────────────────────

    public function recalculatePayments(): void
    {
        $totalPaye = $this->payments()->sum('montant');
        $resteAPayer = max(0, (float)$this->net_a_payer - $totalPaye);

        $newStatut = $this->statut;
        if ($this->statut !== 'annulee' && $this->statut !== 'brouillon') {
            if ($resteAPayer <= 0) {
                $newStatut = 'payee';
            } elseif ($totalPaye > 0) {
                $newStatut = 'partielle';
            }
        }

        $this->update([
            'total_paye'    => $totalPaye,
            'reste_a_payer' => $resteAPayer,
            'statut'        => $newStatut,
        ]);
    }

    // ──────────────────────────────────────
    // Création depuis un OR
    // ──────────────────────────────────────

    public static function createFromRepairOrder(RepairOrder $order, array $extra = []): self
    {
        $bl = $order->deliveryNote;

        $invoice = self::create(array_merge([
            'numero'               => self::generateNumero(),
            'repair_order_id'      => $order->id,
            'client_id'            => $order->client_id,
            'vehicle_id'           => $order->vehicle_id,
            'delivery_note_id'     => $bl?->id,
            'created_by'           => auth()->id(),
            'date_facture'         => now(),
            'date_echeance'        => now()->addDays(30),
            'statut'               => 'brouillon',
            'taux_tva'             => $order->taux_tva,
            'remise_globale'       => $order->remise_globale,
            'objet'                => "Réparation véhicule — OR {$order->numero}",
            'conditions_paiement'  => 'Paiement à 30 jours.',
            'mentions_legales'     => self::MENTIONS_LEGALES_DEFAULT,
        ], $extra));

        // Copier les lignes de l'OR
        foreach ($order->items as $i => $item) {
            $invoice->items()->create([
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

        return $invoice;
    }
}
