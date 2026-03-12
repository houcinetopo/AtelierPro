<?php

namespace App\Http\Controllers;

use App\Http\Requests\BankAccountRequest;
use App\Http\Requests\CompanySettingRequest;
use App\Models\ActivityLog;
use App\Models\CompanyBankAccount;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanySettingController extends Controller
{
    /**
     * Affiche la page des paramètres avec onglets
     */
    public function index(Request $request)
    {
        $settings = CompanySetting::instance();
        $bankAccounts = $settings->bankAccounts()->orderByDesc('is_default')->get();
        $tab = $request->get('tab', 'general');

        return view('settings.index', compact('settings', 'bankAccounts', 'tab'));
    }

    /**
     * Enregistre les modifications (Onglet 1 ou 2)
     */
    public function update(CompanySettingRequest $request)
    {
        $settings = CompanySetting::instance();
        $tab = $request->input('tab');
        $data = $request->validated();
        unset($data['tab']);

        // ── Upload des images (onglet général) ──
        if ($tab === 'general') {
            foreach (['logo', 'cachet', 'signature'] as $field) {
                if ($request->hasFile($field)) {
                    // Supprimer l'ancien fichier
                    if ($settings->$field) {
                        Storage::disk('public')->delete($settings->$field);
                    }
                    $data[$field] = $request->file($field)->store("company/{$field}", 'public');
                }

                // Gestion de la suppression d'image
                if ($request->boolean("remove_{$field}")) {
                    if ($settings->$field) {
                        Storage::disk('public')->delete($settings->$field);
                    }
                    $data[$field] = null;
                }
            }
        }

        $settings->update($data);
        CompanySetting::clearCache();

        ActivityLog::log('update', "Modification des paramètres société (onglet {$tab})", $settings);

        $tabLabels = ['general' => 'générales', 'juridique' => 'juridiques'];

        return redirect()->route('settings.index', ['tab' => $tab])
            ->with('success', "Les informations {$tabLabels[$tab]} ont été mises à jour.");
    }

    /**
     * Supprime une image (logo, cachet, signature) via AJAX
     */
    public function removeImage(Request $request)
    {
        $request->validate([
            'field' => ['required', 'in:logo,cachet,signature'],
        ]);

        $settings = CompanySetting::instance();
        $field = $request->input('field');

        if ($settings->$field) {
            Storage::disk('public')->delete($settings->$field);
            $settings->update([$field => null]);
            CompanySetting::clearCache();

            ActivityLog::log('update', "Suppression de l'image '{$field}' des paramètres société", $settings);
        }

        return response()->json(['success' => true, 'message' => 'Image supprimée.']);
    }

    // ══════════════════════════════════════════════
    // COMPTES BANCAIRES (Onglet 3)
    // ══════════════════════════════════════════════

    /**
     * Ajoute un nouveau compte bancaire
     */
    public function storeBankAccount(BankAccountRequest $request)
    {
        $settings = CompanySetting::instance();
        $data = $request->validated();
        $data['company_setting_id'] = $settings->id;

        // Si c'est le premier compte ou marqué comme défaut
        if ($settings->bankAccounts()->count() === 0) {
            $data['is_default'] = true;
        }

        $account = CompanyBankAccount::create($data);

        // Si marqué comme défaut, retirer le défaut des autres
        if ($account->is_default) {
            $account->setAsDefault();
        }

        CompanySetting::clearCache();
        ActivityLog::log('create', "Ajout du compte bancaire {$account->nom_banque}", $account);

        return redirect()->route('settings.index', ['tab' => 'bancaire'])
            ->with('success', "Le compte bancaire {$account->nom_banque} a été ajouté.");
    }

    /**
     * Met à jour un compte bancaire
     */
    public function updateBankAccount(BankAccountRequest $request, CompanyBankAccount $bankAccount)
    {
        $bankAccount->update($request->validated());

        if ($bankAccount->is_default) {
            $bankAccount->setAsDefault();
        }

        CompanySetting::clearCache();
        ActivityLog::log('update', "Modification du compte bancaire {$bankAccount->nom_banque}", $bankAccount);

        return redirect()->route('settings.index', ['tab' => 'bancaire'])
            ->with('success', "Le compte bancaire a été mis à jour.");
    }

    /**
     * Supprime un compte bancaire
     */
    public function destroyBankAccount(CompanyBankAccount $bankAccount)
    {
        $name = $bankAccount->nom_banque;
        $wasDefault = $bankAccount->is_default;

        ActivityLog::log('delete', "Suppression du compte bancaire {$name}", $bankAccount);
        $bankAccount->delete();

        // Si c'était le défaut, mettre le premier restant comme défaut
        if ($wasDefault) {
            $settings = CompanySetting::instance();
            $first = $settings->bankAccounts()->first();
            if ($first) {
                $first->setAsDefault();
            }
        }

        CompanySetting::clearCache();

        return redirect()->route('settings.index', ['tab' => 'bancaire'])
            ->with('success', "Le compte bancaire {$name} a été supprimé.");
    }

    /**
     * Définit un compte comme compte par défaut
     */
    public function setDefaultBankAccount(CompanyBankAccount $bankAccount)
    {
        $bankAccount->setAsDefault();
        CompanySetting::clearCache();

        return redirect()->route('settings.index', ['tab' => 'bancaire'])
            ->with('success', "{$bankAccount->nom_banque} est maintenant le compte par défaut.");
    }
}
