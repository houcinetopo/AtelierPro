<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashMovement extends Model
{
    protected $fillable = [
        'cash_session_id', 'recorded_by',
        'invoice_id', 'invoice_payment_id', 'employee_payment_id',
        'type', 'categorie', 'libelle', 'montant',
        'mode_paiement', 'reference', 'beneficiaire', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
        ];
    }

    public const CATEGORIES_ENTREE = [
        'paiement_client' => 'Paiement client',
        'acompte'         => 'Acompte reçu',
        'autre_entree'    => 'Autre recette',
    ];

    public const CATEGORIES_SORTIE = [
        'achat_pieces'   => 'Achat pièces / fournitures',
        'salaire'        => 'Paiement employé',
        'loyer'          => 'Loyer',
        'charges'        => 'Charges (eau, élec.)',
        'carburant'      => 'Carburant',
        'outillage'      => 'Outillage',
        'frais_divers'   => 'Frais divers',
        'remboursement'  => 'Remboursement client',
    ];

    public const MODES_PAIEMENT = [
        'especes'  => 'Espèces',
        'cheque'   => 'Chèque',
        'virement' => 'Virement',
        'carte'    => 'Carte bancaire',
        'effet'    => 'Effet de commerce',
    ];

    public static function allCategories(): array
    {
        return array_merge(self::CATEGORIES_ENTREE, self::CATEGORIES_SORTIE);
    }

    // ──────────────────────────────────────
    // Relations
    // ──────────────────────────────────────

    public function cashSession()
    {
        return $this->belongsTo(CashSession::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function invoicePayment()
    {
        return $this->belongsTo(InvoicePayment::class);
    }

    // ──────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────

    public function getCategorieLabelAttribute(): string
    {
        return self::allCategories()[$this->categorie] ?? ucfirst($this->categorie);
    }

    public function getModeLabelAttribute(): string
    {
        return self::MODES_PAIEMENT[$this->mode_paiement] ?? ucfirst($this->mode_paiement);
    }

    public function getIsEntreeAttribute(): bool
    {
        return $this->type === 'entree';
    }

    // ──────────────────────────────────────
    // Events
    // ──────────────────────────────────────

    protected static function booted(): void
    {
        static::saved(function (self $mv) {
            $mv->cashSession->recalculate();
        });

        static::deleted(function (self $mv) {
            $mv->cashSession->recalculate();
        });
    }
}
