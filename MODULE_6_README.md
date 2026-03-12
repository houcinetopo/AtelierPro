# Module 6 : Ordres de Réparation

## Fichiers créés

### Migrations (3)
- `create_repair_orders_table.php` — Table principale : numéro auto, statut, client, véhicule, technicien, dates, montants, description, diagnostic, kilométrage, carburant, état véhicule (JSON)
- `create_repair_order_items_table.php` — Lignes de travaux : type (main d'œuvre/pièce/fourniture/sous-traitance), désignation, quantité, prix unitaire, remise, TVA, calcul automatique montants
- `create_repair_order_photos_table.php` — Photos avant/pendant/après avec caption

### Modèles (3)
- `RepairOrder.php` — Statuts avec transitions validées, numérotation auto (OR-YYYY-XXXXX), recalcul totaux, scopes (search, byStatus, late, forTechnicien), accessors (status_badge, is_late, vehicle_label, duree_reelle)
- `RepairOrderItem.php` — Calcul automatique HT/TTC via events (saving/saved), types et unités avec labels, liaison au recalcul de l'ordre parent
- `RepairOrderPhoto.php` — 3 moments (avant/pendant/après), suppression physique auto via event deleting

### Controller
- `RepairOrderController.php` — CRUD complet avec DB::beginTransaction, gestion items dynamiques, upload photos, changement de statut, API véhicules par client, contrôle d'accès technicien

### Vues (5)
- `index.blade.php` — 4 stats rapides (total, en cours, en retard, terminés) + filtres 5 critères (recherche, statut, technicien, date début/fin) + table paginée avec badges statut/retard
- `show.blade.php` — Vue détaillée 2 colonnes : client/véhicule, détails intervention, table travaux avec totaux HT/TVA/TTC, galerie photos par moment, infos latérales (dates, technicien, km, carburant, financier), notes internes (admin only), transitions statut dropdown, zone de danger
- `create.blade.php` / `edit.blade.php` — Formulaires via partial
- `_form.blade.php` — Formulaire 4 étapes avec Alpine.js :
  - Étape 1 : Client + Véhicule (chargement AJAX des véhicules par client)
  - Étape 2 : Détails (technicien, dates, description, diagnostic, km, carburant, source, observations, notes internes)
  - Étape 3 : Lignes dynamiques (ajout/suppression, type/désignation/référence/qté/unité/PU/remise, calcul temps réel HT, total HT/TVA/remise globale/net à payer)
  - Étape 4 : Photos avec upload par moment (avant/pendant/après)

### Seeder
- `RepairOrderSeeder.php` — 5 ordres réalistes (choc carrosserie, peinture complète, pare-brise, joint culasse, révision) avec items détaillés, statuts variés

## Fonctionnalités

### Numérotation automatique
Format `OR-YYYY-XXXXX` (ex: OR-2025-00001), séquence par année, inclut les enregistrements soft-deleted pour éviter les doublons.

### Machine à états (statuts)
```
brouillon → en_cours → en_attente → en_cours (boucle)
                     → termine → livre → facture
                     → annule
annule → brouillon (réactivation)
```
Chaque transition est validée côté modèle. La livraison met à jour automatiquement `date_livraison_effective`.

### Lignes de travaux dynamiques (Alpine.js)
- 4 types : Main d'œuvre, Pièce, Fourniture, Sous-traitance
- 6 unités : Unité, Heure, Forfait, Mètre, Kg, Litre
- Remise par ligne (%)
- Calcul en temps réel : montant HT par ligne, total HT, TVA 20%, remise globale, net à payer
- Recalcul automatique côté serveur via Eloquent events (RepairOrderItem::saving → calcul, ::saved → recalcul parent)

### Photos avant/pendant/après
- Upload multiple avec sélection du moment
- Stockage `storage/app/public/repair-orders/{id}/`
- Galerie groupée par moment dans la vue show
- Suppression avec nettoyage fichier physique
- Max 5 Mo par photo

### Contrôle d'accès
- Admin/Gestionnaire : CRUD complet + changement de statut
- Comptable : lecture seule
- Technicien : voit uniquement ses ordres assignés (scope forTechnicien)
- Ordres facturés : non modifiables, non supprimables

### Intégration Dashboard
Le dashboard (Module 2) utilise déjà les repair_orders via Schema::hasTable() :
- Véhicules en cours (count en_cours + brouillon)
- Types de réparation (groupement repair_order_items.designation)
- Ordres mensuels (graphique barres)
- Ordres récents (table)
- Alertes livraisons en retard

## Routes ajoutées (6)
```
GET    /repair-orders                          → index
GET    /repair-orders/vehicles-by-client       → vehiclesByClient (AJAX)
GET    /repair-orders/create                   → create
POST   /repair-orders                          → store
GET    /repair-orders/{id}                     → show
GET    /repair-orders/{id}/edit                → edit
PUT    /repair-orders/{id}                     → update
DELETE /repair-orders/{id}                     → destroy
PATCH  /repair-orders/{id}/status              → updateStatus
DELETE /repair-orders/{id}/photos/{photo}      → deletePhoto
```
