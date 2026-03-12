<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyBankAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_setting_id',
        'nom_banque',
        'numero_compte',
        'rib',
        'code_swift',
        'iban',
        'agence',
        'ville_agence',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    // ──────────────────────────────────────
    // Relations
    // ──────────────────────────────────────

    public function companySetting()
    {
        return $this->belongsTo(CompanySetting::class);
    }

    // ──────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────

    /**
     * Formate les infos bancaires pour les documents
     */
    public function getFormattedInfo(): string
    {
        $parts = array_filter([
            $this->nom_banque,
            $this->agence ? "Agence : {$this->agence}" : null,
            $this->rib ? "RIB : {$this->rib}" : null,
            $this->code_swift ? "SWIFT : {$this->code_swift}" : null,
            $this->iban ? "IBAN : {$this->iban}" : null,
        ]);

        return implode(' — ', $parts);
    }

    /**
     * Définir ce compte comme compte par défaut
     */
    public function setAsDefault(): void
    {
        // Retirer le défaut des autres comptes
        self::where('company_setting_id', $this->company_setting_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        $this->update(['is_default' => true]);
    }
}
