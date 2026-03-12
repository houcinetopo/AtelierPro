# Module 3 : Paramètres de la Société

## Fichiers créés

### Migrations
- `create_company_settings_table.php` — Table singleton avec tous les champs (général + juridique)
- `create_company_bank_accounts_table.php` — Table relationnelle pour les comptes bancaires multiples

### Modèles
- `CompanySetting.php` — Avec pattern singleton via `::instance()`, cache, accessors URL images, helpers PDF
- `CompanyBankAccount.php` — Avec soft deletes, méthode `setAsDefault()`

### Controllers
- `CompanySettingController.php` — Gestion des 3 onglets + CRUD comptes bancaires + suppression images AJAX

### Form Requests
- `CompanySettingRequest.php` — Validation conditionnelle par onglet (général ou juridique)
- `BankAccountRequest.php` — Validation des comptes bancaires

### Vues
- `settings/index.blade.php` — Page principale avec 3 onglets
- `settings/_bank_account_form.blade.php` — Partial réutilisable (création + édition inline)

### Seeders
- `CompanySettingsSeeder.php` — Données de démo (atelier + 2 comptes bancaires)

### Modifié
- `routes/web.php` — 7 routes ajoutées sous le préfixe `/settings`
- `components/sidebar.blade.php` — Lien activé
- `DatabaseSeeder.php` — CompanySettingsSeeder ajouté

## Fonctionnalités

### Onglet 1 — Informations Générales
- Raison sociale, adresse, ville, code postal, pays
- Téléphone portable + fixe
- Email principal + secondaire, site web
- Upload images (logo, cachet, signature) :
  - Upload depuis ordinateur (drag & drop)
  - Capture caméra mobile (`capture="environment"`)
  - Aperçu avant enregistrement
  - Suppression via AJAX avec confirmation
  - Stockage dans `storage/app/public/company/`

### Onglet 2 — Identifiants Juridiques
- Forme juridique (dropdown : SARL, SA, Auto-entrepreneur...)
- Capital social, RC, Patente, CNSS, ICE, IF
- Objet de la société
- Nom du gérant + fonction + CIN
- Aperçu live du pied de page des documents

### Onglet 3 — Informations Bancaires
- Liste des comptes avec affichage carte
- Banques marocaines pré-listées (Attijariwafa, BMCE, BP, etc.)
- Numéro de compte, RIB, SWIFT, IBAN
- Agence + ville
- Gestion du compte par défaut (étoile)
- Édition inline (Alpine.js)
- Ajout avec formulaire dépliable
- Suppression avec confirmation

## Helpers pour les autres modules
- `CompanySetting::instance()` — Accès rapide depuis n'importe quel contrôleur
- `->getDocumentHeader()` — En-tête pour les PDFs
- `->getLegalMentions()` — Mentions légales pour les factures
- `->getFooterLine()` — Ligne de pied de page formatée
- `->isConfigured()` — Vérification de configuration minimale
- Cache automatique avec invalidation sur modification
