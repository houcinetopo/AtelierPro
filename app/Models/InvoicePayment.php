<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoicePayment extends Model
{
    protected $fillable = [
        'invoice_id', 'recorded_by',
        'date_paiement', 'montant', 'mode',
        'reference', 'banque', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_paiement' => 'date',
            'montant'       => 'decimal:2',
        ];
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getModeLabelAttribute(): string
    {
        return Invoice::MODES_PAIEMENT[$this->mode] ?? ucfirst($this->mode);
    }

    protected static function booted(): void
    {
        static::saved(function (self $payment) {
            $payment->invoice->recalculatePayments();
        });

        static::deleted(function (self $payment) {
            $payment->invoice->recalculatePayments();
        });
    }
}
