# Module 8 : Devis

## Fichiers créés

### Migrations (2)
- `create_quotes_table.php` — Numéro auto (DV-YYYY-XXXXX), client + véhicule (optionnel), 7 statuts (brouillon/envoyé/accepté/refusé/expiré/converti/annulé), montants (HT/TVA/TTC/remise/net), description travaux, conditions de paiement, motif de refus, durée estimée, lien vers OR généré
- `create_quote_items_table.php` — Même structure que repair_order_items : type (4 types), désignation, référence, quantité, unité (6 unités), prix unitaire, remise %, TVA, ordre

### Modèles (2)
- `Quote.php` — Numérotation auto, 7 statuts avec couleurs et icônes, machine à états (canTransitionTo/transitionTo), scopes (search, byStatut, enAttente, expires), accessors (statut_badge, is_expired, is_convertible), recalculateTotals(), convertToRepairOrder() (crée un OR avec toutes les lignes copiées)
- `QuoteItem.php` — Calcul automatique montant_ht/montant_ttc via événements Eloquent, déclenche recalculateTotals() sur le devis parent

### Controller (1)
- `QuoteController.php` — 10 méthodes :
  - `index` — Liste paginée avec stats, auto-expiration des devis envoyés périmés
  - `create` — Sélection client/véhicule avec AJAX, pré-sélection si ?client_id=X
  - `store` / `update` — Validation complète, sync des lignes avec recalcul auto
  - `show` — Vue détaillée avec transitions disponibles et modal refus
  - `edit` — Bloqué si converti/annulé
  - `destroy` — Bloqué si converti
  - `updateStatut` — Machine à états avec motif de refus optionnel
  - `convertToRepairOrder` — Conversion en OR (copie toutes les lignes)
  - `duplicate` — Clone le devis en nouveau brouillon (nouveau numéro, nouvelles dates)
  - `vehiclesByClient` — API AJAX pour dropdown véhicules

### Vues (5)
- `index.blade.php` — 4 stats (total, en attente, acceptés, montant DH ce mois) + filtres (recherche, statut, dates) + table avec badge expiré, bouton conversion en OR
- `show.blade.php` — 2 colonnes : détail lignes avec totaux, client/véhicule, description, motif de refus. Actions : convertir en OR, changer statut (dropdown avec transitions valides), modifier, dupliquer, supprimer. Modal de refus avec champ motif
- `create.blade.php` / `edit.blade.php` — Via partial
- `_form.blade.php` — Formulaire 3 étapes Alpine.js :
  - Étape 1 : Client (AJAX véhicules) + Véhicule optionnel
  - Étape 2 : Dates (devis + validité 30j par défaut), durée estimée, description, conditions, notes
  - Étape 3 : Lignes dynamiques (même interface que OR : type, désignation, référence, qté, unité, P.U., remise), totaux en temps réel (HT + TVA 20% - remise globale = net à payer)

### Seeder (1)
- `QuoteSeeder.php` — 6 devis réalistes couvrant tous les statuts :
  1. Réparation carrosserie accident (accepté)
  2. Remplacement pare-brise + calibrage ADAS (envoyé)
  3. Réfection freinage (refusé — "trouvé moins cher")
  4. Traitement anti-corrosion (brouillon)
  5. Révision 100 000 km distribution (converti)
  6. Remise en état optiques (expiré)

## Fonctionnalités clés

### Machine à états (7 statuts)
```
brouillon → envoyé, annulé
envoyé → accepté, refusé, expiré, annulé
accepté → converti, annulé
refusé → brouillon (révision possible)
expiré → brouillon (relance possible)
converti → (final, immutable)
annulé → brouillon (réactivation)
```

### Conversion Devis → Ordre de Réparation
- Disponible uniquement si statut = "accepté" et pas encore converti
- Crée un OR en brouillon avec toutes les lignes copiées (type, désignation, référence, qté, unité, prix, remise, TVA)
- Reporte la description des travaux comme description_panne
- Reporte la durée estimée comme date_prevue_livraison
- Lie le devis à l'OR (repair_order_id) et passe en statut "converti"

### Duplication
- Clone un devis existant en nouveau brouillon
- Nouveau numéro, date du jour, validité 30 jours
- Toutes les lignes copiées
- Ouvre directement en mode édition

### Auto-expiration
- À chaque visite de l'index, les devis envoyés dont la date_validite est passée passent automatiquement en statut "expiré"

### Modal de refus
- Lors du passage en statut "refusé", un modal demande le motif du refus
- Le motif est affiché en encart rouge dans la vue show

### Calcul automatique
- Même logique que les OR : événements Eloquent sur QuoteItem → calculateTotals() → recalculateTotals() sur Quote
- Alpine.js en temps réel dans le formulaire

## Routes ajoutées (12)
```
GET    /quotes/vehicles-by-client          → vehiclesByClient (AJAX, AVANT resource)
GET    /quotes                             → index
GET    /quotes/create                      → create
POST   /quotes                             → store
GET    /quotes/{id}                        → show
GET    /quotes/{id}/edit                   → edit
PUT    /quotes/{id}                        → update
DELETE /quotes/{id}                        → destroy
PATCH  /quotes/{id}/statut                 → updateStatut
POST   /quotes/{id}/convert               → convertToRepairOrder
POST   /quotes/{id}/duplicate             → duplicate
```

## Fichiers modifiés
- `routes/web.php` — Import QuoteController + 6 routes
- `sidebar.blade.php` — Lien activé (icône file-text)
- `Client.php` — Ajout relation `quotes()` (hasMany)
- `DatabaseSeeder.php` — Ajout QuoteSeeder
