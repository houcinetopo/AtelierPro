<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CompanySetting extends Model
{
    protected $fillable = [
        // Informations Générales
        'raison_sociale', 'adresse', 'ville', 'code_postal', 'pays',
        'telephone_portable', 'telephone_fixe',
        'email_principal', 'email_secondaire', 'site_web',
        'logo', 'cachet', 'signature',
        // Identifiants Juridiques
        'forme_juridique', 'capital_social',
        'registre_commerce', 'patente', 'cnss', 'ice', 'identifiant_fiscal',
        'objet_societe', 'nom_responsable', 'fonction_responsable', 'cin_responsable',
    ];

    // ──────────────────────────────────────
    // Relations
    // ──────────────────────────────────────

    public function bankAccounts()
    {
        return $this->hasMany(CompanyBankAccount::class);
    }

    public function defaultBankAccount()
    {
        return $this->hasOne(CompanyBankAccount::class)->where('is_default', true);
    }

    // ──────────────────────────────────────
    // Singleton : récupérer les paramètres
    // ──────────────────────────────────────

    /**
     * Récupère l'instance unique des paramètres (avec cache)
     */
    public static function instance(): self
    {
        return Cache::remember('company_settings', 3600, function () {
            return self::with('bankAccounts')->firstOrCreate([]);
        });
    }

    /**
     * Vide le cache après modification
     */
    public static function clearCache(): void
    {
        Cache::forget('company_settings');
    }

    // ──────────────────────────────────────
    // Accessors pour les URLs d'images
    // ──────────────────────────────────────

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? Storage::disk('public')->url($this->logo) : null;
    }

    public function getCachetUrlAttribute(): ?string
    {
        return $this->cachet ? Storage::disk('public')->url($this->cachet) : null;
    }

    public function getSignatureUrlAttribute(): ?string
    {
        return $this->signature ? Storage::disk('public')->url($this->signature) : null;
    }

    // ──────────────────────────────────────
    // Helpers pour les PDFs / documents
    // ──────────────────────────────────────

    /**
     * Retourne un tableau avec toutes les infos pour l'en-tête des documents
     */
    public function getDocumentHeader(): array
    {
        return [
            'raison_sociale'  => $this->raison_sociale,
            'adresse'         => $this->adresse,
            'ville'           => $this->ville,
            'telephone'       => $this->telephone_portable ?? $this->telephone_fixe,
            'email'           => $this->email_principal,
            'site_web'        => $this->site_web,
            'logo_path'       => $this->logo ? Storage::disk('public')->path($this->logo) : null,
            'cachet_path'     => $this->cachet ? Storage::disk('public')->path($this->cachet) : null,
            'signature_path'  => $this->signature ? Storage::disk('public')->path($this->signature) : null,
        ];
    }

    /**
     * Retourne les mentions légales pour les documents
     */
    public function getLegalMentions(): array
    {
        return [
            'forme_juridique'    => $this->forme_juridique,
            'capital_social'     => $this->capital_social,
            'registre_commerce'  => $this->registre_commerce,
            'patente'            => $this->patente,
            'cnss'               => $this->cnss,
            'ice'                => $this->ice,
            'identifiant_fiscal' => $this->identifiant_fiscal,
        ];
    }

    /**
     * Retourne la ligne de pied de page pour les documents
     */
    public function getFooterLine(): string
    {
        $parts = array_filter([
            $this->forme_juridique ? "{$this->forme_juridique}" : null,
            $this->capital_social ? "Capital : {$this->capital_social} DH" : null,
            $this->registre_commerce ? "RC : {$this->registre_commerce}" : null,
            $this->patente ? "Patente : {$this->patente}" : null,
            $this->ice ? "ICE : {$this->ice}" : null,
            $this->identifiant_fiscal ? "IF : {$this->identifiant_fiscal}" : null,
            $this->cnss ? "CNSS : {$this->cnss}" : null,
        ]);

        return implode(' | ', $parts);
    }

    /**
     * Vérifie si les paramètres minimaux sont configurés
     */
    public function isConfigured(): bool
    {
        return !empty($this->raison_sociale) && !empty($this->adresse);
    }
}
