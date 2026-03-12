# Module 13 : TVA — Déclarations & Suivi Fiscal

## Fichiers créés

### Migration (1)
- `create_tva_declarations_table.php` — Déclaration TVA : régime (mensuel/trimestriel), année/mois/trimestre, dates début/fin. Ventilation par taux (20%, 14%, 10%, 7%, exonéré) pour CA ventes et achats. TVA collectée/déductible par taux. Crédit antérieur, TVA due/crédit. 5 statuts (brouillon/calculée/validée/déclarée/payée). Date/référence paiement DGI, pénalités. Contraintes d'unicité par période.

### Modèle (1)
- `TvaDeclaration.php` (~280 lignes) :
  - Constantes : 5 STATUTS avec couleurs, 5 TAUX_TVA marocains, REGIMES, MOIS/TRIMESTRE labels
  - Relations : createdBy, validatedBy (users)
  - Scopes : byAnnee, byStatut
  - Accessors : statut_badge, periode_label, periode_court, total_ca_ht, total_achats_ht, is_editable, is_overdue, date_limite
  - `calculateFromData()` : calcul automatique depuis factures émises + bons de commande + mouvements caisse de la période. Ventile par taux TVA. Récupère crédit antérieur de la déclaration précédente.
  - Machine à états : brouillon → calculée → validée → déclarée → payée (retour possible calculée↔brouillon, validée→calculée)
  - `getDatesForPeriod()` : calcule dates début/fin selon régime et numéro de période
  - `getInvoicesForPeriod()` / `getPurchasesForPeriod()` : requêtes pré-filtrées
  - `getAnnualSummary()` : stats récapitulatives annuelles

### Controller (1)
- `TvaController.php` — 8 méthodes :
  - `index` : Liste déclarations par année avec stats annuelles (TVA collectée/déductible/due/payée), filtre par année/statut, alerte déclarations en retard
  - `create` : Formulaire avec sélection régime (mensuel/trimestriel), année, période
  - `store` : Création avec vérification unicité période, calcul dates automatique
  - `show` : Détail complet avec tableau synthèse multi-taux, listes factures et BC de la période, sidebar résultat TVA
  - `calculate` : Calcul automatique depuis les données de la période (factures, BC, mouvements caisse)
  - `update` : Modification manuelle des bases HT avec recalcul TVA
  - `updateStatut` : Transitions avec modal paiement (date, montant, référence quittance DGI)
  - `destroy` : Suppression (bloquée si déclarée/payée)

### Vues (3)
- `index.blade.php` (~170 lignes) — 4 stats annuelles (TVA collectée bleu, déductible amber, due rouge, payée vert). Badge alerte "en retard". Filtres année/statut. Table : période (lien), régime, statut badge, TVA collectée/déductible/due, date limite (rouge si en retard).
- `create.blade.php` (~110 lignes) — Sélection régime avec cartes radio Alpine.js (mensuel CA>1M DH, trimestriel CA≤1M DH). Dropdowns année/mois/trimestre dynamiques. Info-box explicative du workflow.
- `show.blade.php` (~300 lignes) — Header avec période + statut + badge retard. Boutons : calculer auto (bleu), changer statut (dropdown avec transitions), modal paiement TVA (date/montant/réf quittance). Tableau synthèse TVA multi-taux : 4 taux + exonéré × (base HT ventes, TVA collectée, base HT achats, TVA déductible) avec totaux. Section édition manuelle (toggle hidden). Tables factures et BC de la période avec liens. Sidebar : résultat TVA (collectée − déductible − crédit = due/crédit), pénalités, infos période/dates/validation, danger zone suppression.

### Seeder (1)
- `TvaSeeder.php` — 4 déclarations mensuelles : 1 payée (3 mois ago), 1 déclarée (2 mois), 1 validée (mois dernier), 1 brouillon (mois en cours). Montants réalistes pour atelier carrosserie marocain.

## Taux TVA marocains supportés
- **20%** — Taux standard (appliqué par défaut dans tout le système)
- **14%** — Transport, énergie
- **10%** — Huiles alimentaires, certains services
- **7%** — Eau, produits pharmaceutiques
- **0%** — Exonéré (exportations, etc.)

## Machine à états — Déclarations

```
brouillon → calculée (via bouton "Calculer automatiquement")
calculée → validée (validation gestionnaire) | brouillon (retour pour correction)
validée → déclarée (envoi DGI) | calculée (retour pour recalcul)
déclarée → payée (avec date/montant/quittance DGI)
payée → (final)
```

## Fonctionnalités clés

### Calcul automatique
- Agrège les factures émises/payées/partielles de la période → TVA collectée
- Agrège les bons de commande confirmés/livrés de la période → TVA déductible
- Inclut les mouvements de caisse (achats pièces, charges) → TVA déductible complémentaire
- Récupère le crédit TVA de la période précédente automatiquement
- Ventilation par taux : chaque source est classée selon son taux_tva

### Suivi des échéances
- Date limite calculée automatiquement (fin du mois suivant la période)
- Badge "EN RETARD" si date limite dépassée et non déclaré/payé
- Compteur global des déclarations en retard sur l'index

### Workflow fiscal complet
- Brouillon → Calcul auto → Vérification/ajustement → Validation → Déclaration DGI → Paiement
- Possibilité d'édition manuelle des bases HT à tout moment (tant que editable)
- Enregistrement du paiement avec référence quittance DGI et montant exact

### Régimes
- **Mensuel** : pour CA > 1 000 000 DH (obligation légale)
- **Trimestriel** : pour CA ≤ 1 000 000 DH
- Contrainte d'unicité : une seule déclaration par mois ou trimestre

## Routes (8)

```
GET    /tva                    → index
GET    /tva/create             → create
POST   /tva                    → store
GET    /tva/{id}               → show
PUT    /tva/{id}               → update (modification manuelle)
DELETE /tva/{id}               → destroy
POST   /tva/{id}/calculate     → calculate (calcul automatique)
PATCH  /tva/{id}/statut        → updateStatut
```

## Fichiers modifiés
- `routes/web.php` — Import TvaController, 8 routes dans groupe prefix tva
- `sidebar.blade.php` — Lien activé (icône calculator)
- `DatabaseSeeder.php` — Ajout TvaSeeder
