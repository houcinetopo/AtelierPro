# Module 5 : Clients & Véhicules

## Fichiers créés

### Migrations (3)
- `create_clients_table.php` — 2 types (particulier/société), coordonnées, financier (solde/plafond crédit), source, blacklist
- `create_vehicles_table.php` — Lié au client, immatriculation unique, marque/modèle/couleur/année, carburant, VIN, assurance, contrôle technique, kilométrage
- `create_vehicle_photos_table.php` — Photos du véhicule avec légende

### Modèles (3)
- `Client.php` — Scopes (search, particuliers, societes, withDebt), accessors (display_name, legal_id, type_badge, initials, avatar_url, source_label), isOverCreditLimit(), relations (vehicles, repairOrders, invoices)
- `Vehicle.php` — 28 marques marocaines, 5 types carburant, scopes (search, byMarque), accessors (full_name, display_label, carburant_label, assurance_status/badge, controle_status), relations (client, photos, repairOrders)
- `VehiclePhoto.php` — Upload avec suppression physique auto

### Controllers (2)
- `ClientController.php` — CRUD + export CSV + API search (AJAX autocomplete)
- `VehicleController.php` — CRUD + upload photos multiples + suppression photo + API véhicules par client

### Form Requests (2)
- `ClientRequest.php` — Validation conditionnelle par type (particulier: nom_complet requis, société: raison_sociale+ICE requis)
- `VehicleRequest.php` — Immatriculation unique (sauf edit), marque requise, dates assurance/contrôle

### Vues (10)
- `clients/index.blade.php` — Stats (total, particuliers, sociétés, avec dette) + filtres (recherche, type, source) + table avec avatar, type badge, téléphone, solde crédit
- `clients/show.blade.php` — Fiche complète avec liste véhicules du client, solde crédit, bouton "Nouvel OR" direct
- `clients/create.blade.php` / `edit.blade.php` — Via partial
- `clients/_form.blade.php` — Formulaire dynamique Alpine.js : affiche champs particulier OU société selon type sélectionné
- `vehicles/index.blade.php` — Liste avec client, marque, immatriculation, badges assurance/contrôle
- `vehicles/show.blade.php` — Fiche véhicule avec galerie photos, infos assurance/contrôle, historique ordres
- `vehicles/create.blade.php` / `edit.blade.php` — Via partial
- `vehicles/_form.blade.php` — Dropdown 28 marques, 5 carburants, upload photo avec aperçu

### Seeder
- `ClientVehicleSeeder.php` — 8 clients réalistes (6 particuliers + 2 sociétés marocaines) avec 12 véhicules associés

## Fonctionnalités

### Clients
- 2 types : Particulier (nom+CIN) et Société (raison sociale+ICE+RC+contact)
- Formulaire dynamique : affiche les champs appropriés selon le type sélectionné
- Gestion crédit : solde impayé + plafond, détection dépassement
- Source d'acquisition : Direct, Recommandation, Publicité, Internet, Assurance, Autre
- Blacklist : marquage client problématique
- Export CSV avec BOM UTF-8
- API recherche AJAX pour autocomplete dans les ordres de réparation

### Véhicules
- 28 marques populaires au Maroc (Dacia, Renault, Peugeot, Toyota, Hyundai, Kia, BYD...)
- 5 types carburant (Essence, Diesel, GPL, Électrique, Hybride)
- Suivi assurance avec alertes : badges Valide/Expire bientôt/Expirée
- Suivi contrôle technique avec alertes similaires
- Kilométrage mis à jour automatiquement via les ordres de réparation
- Galerie photos avec upload multiple et suppression
- Numéro de chassis (VIN) optionnel
- Puissance fiscale marocaine

### API Endpoints
- `GET /api/clients/search?q=xxx` — Recherche clients pour autocomplete
- `GET /api/vehicles/by-client?client_id=x` — Véhicules d'un client (utilisé dans Module 6)

## Routes ajoutées
```
Resource clients  (index, create, store, show, edit, update, destroy)
GET  /clients-export                    → export CSV
GET  /api/clients/search               → apiSearch (AJAX)

Resource vehicles (index, create, store, show, edit, update, destroy)
POST /vehicles/{id}/photos             → uploadPhotos
DELETE /vehicles/{id}/photos/{photo}   → deletePhoto
GET  /api/vehicles/by-client           → apiByClient (AJAX)
```
