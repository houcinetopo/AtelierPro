<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeePayment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id', 'periode', 'montant', 'date_paiement',
        'mode_paiement', 'reference', 'notes',
        'prime', 'deduction', 'net_paye', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_paiement' => 'date',
            'montant'       => 'decimal:2',
            'prime'         => 'decimal:2',
            'deduction'     => 'decimal:2',
            'net_paye'      => 'decimal:2',
        ];
    }

    public const MODES_PAIEMENT = [
        'especes'  => 'Espèces',
        'cheque'   => 'Chèque',
        'virement' => 'Virement bancaire',
        'autre'    => 'Autre',
    ];

    // ──────────────────────────────────────
    // Relations
    // ──────────────────────────────────────

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ──────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────

    public function getModePaiementLabelAttribute(): string
    {
        return self::MODES_PAIEMENT[$this->mode_paiement] ?? ucfirst($this->mode_paiement);
    }

    public function getPeriodeLabelAttribute(): string
    {
        if (!$this->periode) return '—';
        $parts = explode('-', $this->periode);
        if (count($parts) !== 2) return $this->periode;
        $months = ['01'=>'Janvier','02'=>'Février','03'=>'Mars','04'=>'Avril','05'=>'Mai','06'=>'Juin',
                    '07'=>'Juillet','08'=>'Août','09'=>'Septembre','10'=>'Octobre','11'=>'Novembre','12'=>'Décembre'];
        return ($months[$parts[1]] ?? $parts[1]) . ' ' . $parts[0];
    }
}
