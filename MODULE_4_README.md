# Module 4 : Gestion des Employés

## Fichiers créés

### Migrations
- `create_employees_table.php` — Table principale avec infos personnelles, professionnelles, rémunération, statut
- `create_employee_payments_table.php` — Historique des paiements de salaire avec prime/déduction/net

### Modèles
- `Employee.php` — Avec constantes POSTES (14 postes d'atelier), TYPES_CONTRAT, scopes (active, byPoste, search), accessors (photo_url via ui-avatars, poste_label, salaire_journalier, anciennete, statut_badge)
- `EmployeePayment.php` — Avec relation employee/createdBy, modes de paiement, accessor periode_label (ex: "Mars 2025")

### Controller
- `EmployeeController.php` — CRUD complet + gestion paiements + export CSV avec BOM UTF-8

### Form Requests
- `EmployeeRequest.php` — Validation complète (nom, poste, contrat, salaire, photo max 2Mo)
- `EmployeePaymentRequest.php` — Validation paiement (période format YYYY-MM, montant, mode, primes/déductions)

### Vues (5 fichiers)
- `employees/index.blade.php` — Liste avec 3 stats rapides (total, actifs, masse salariale), filtres multi-critères (recherche, poste, statut, contrat), table avec avatar/CIN/poste/salaire/ancienneté
- `employees/show.blade.php` — Fiche détaillée : infos perso + pro + historique paiements en table + résumé financier (total payé, payé cette année) + formulaire paiement inline avec calcul net en temps réel (Alpine.js)
- `employees/create.blade.php` / `edit.blade.php` — Formulaires via partial
- `employees/_form.blade.php` — Partial réutilisable : 15 champs organisés en 2 colonnes, upload photo avec aperçu circulaire + capture caméra mobile, calcul salaire journalier en temps réel, radio buttons statut avec couleur

### Seeder
- `EmployeeSeeder.php` — 7 employés réalistes (chef atelier, mécanicien, carrossier, peintre, électricien, secrétaire, apprenti) + 3 mois de paiements de démo

### Modifié (sessions précédentes)
- `routes/web.php` — Resource route + export + paiements CRUD
- `sidebar.blade.php` — Lien activé avec icône hard-hat

## Fonctionnalités

### CRUD Employés
- Liste paginée (15/page) avec avatar auto-généré (ui-avatars)
- Filtrage multi-critères : recherche textuelle, poste (14 postes), statut, type contrat
- Calcul salaire journalier en temps réel dans le formulaire
- Upload photo avec aperçu circulaire + capture caméra mobile
- Soft delete

### Paiements de Salaire
- Formulaire inline sur la page show (colonne droite)
- Sélection de période (input month)
- Montant base pré-rempli avec salaire mensuel
- Champs prime + déduction avec calcul net en temps réel
- Protection anti-doublon : un seul paiement par période
- Modes de paiement : Espèces, Chèque, Virement, Autre
- Référence (n° chèque, réf virement)
- Historique en table avec suppression

### Stats rapides (Index)
- Total employés
- Employés actifs
- Masse salariale mensuelle (somme salaire_base des actifs)

### Export CSV
- Export complet avec BOM UTF-8 (compatible Excel français)
- Séparateur point-virgule
- Colonnes : Nom, CIN, Poste, Contrat, Salaire, Jours, Salaire Jour, Tél, CNSS, Statut, Date Embauche

### Postes d'atelier pré-définis
Chef d'atelier, Mécanicien, Carrossier, Peintre, Électricien auto, Préparateur, Magasinier, Secrétaire, Comptable, Réceptionniste, Laveur, Apprenti, Stagiaire, Autre

## Autorisations
- CRUD : admin, gestionnaire
- Paiements : admin, gestionnaire, comptable
