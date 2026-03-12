# Module 11 : Stock & Produits

## Fichiers créés

### Migrations (3)
- `create_product_categories_table.php` — Catégories avec nom, slug, couleur, ordre, actif
- `create_products_table.php` — Référence auto (PR/FN/OT/AC-XXXXX), code barre, 4 types (pièce/fourniture/outillage/accessoire), prix achat/vente/TVA/marge, stock (quantité/seuil alerte/seuil commande/max), unité (7 options), emplacement, fournisseur (nom/ref/délai), soft deletes
- `create_stock_movements_table.php` — Entrées/Sorties/Ajustements avec 9 motifs, stock avant/après, prix unitaire, montant total, lien OR/item optionnel, référence document

### Modèles (3)
- `ProductCategory.php` — Slug auto, scope actives, relation products
- `Product.php` — Référence auto par type (PR/FN/OT/AC), 4 types avec couleurs, 7 unités, scopes (search, byType, byCategory, actifs, enAlerte, enRupture, aCommander), accessors (stock_status, stock_badge, type_badge, marge_calculee, valeur_stock, prix_vente_ttc), méthodes stock: `addStock()`, `removeStock()`, `adjustStock()` avec traçabilité complète
- `StockMovement.php` — 4 types (entrée/sortie/ajustement/inventaire), 9 motifs (achat, retour, perte, consommation OR, etc.), relations product/recordedBy/repairOrder, badges couleur

### Controller (1)
- `ProductController.php` — 12 méthodes :
  - `index` — Liste paginée, filtres (recherche, type, catégorie, alertes seules), 4 stats (total actifs, valeur stock, en alerte, en rupture)
  - `create/store` — Référence auto par type, stock initial = mouvement d'entrée, marge auto-calculée
  - `show` — Fiche produit avec historique mouvements paginé, formulaire nouveau mouvement (type toggle entrée/sortie/ajustement, motif dynamique, quantité, prix, référence doc), stats 30 jours
  - `edit/update` — Stock non modifiable via update (uniquement via mouvements)
  - `destroy` — Bloqué si stock > 0
  - `addMovement` — Entrée/Sortie/Ajustement avec validation stock suffisant pour sorties
  - `alerts` — Vue dédiée : rupture (rouge), alerte (amber), à commander (bleu), séparées
  - `storeCategory/destroyCategory` — Mini CRUD catégories inline
  - `searchApi` — API JSON pour autocomplete dans formulaires OR/Devis

### Vues (7)
- `index.blade.php` — 4 stats + filtres (recherche, type, catégorie, checkbox alertes) + table avec coloration lignes (rouge rupture, amber alerte) + gestion catégories inline (admin, collapsible)
- `show.blade.php` — 2 colonnes : formulaire mouvement (type toggle vert/rouge/amber, motifs dynamiques) + historique mouvements paginé. Sidebar : stock actuel (grand chiffre coloré), prix & marge, seuils, fournisseur, stats 30j, danger zone
- `create.blade.php` / `edit.blade.php` — Via partial
- `_form.blade.php` — 4 sections numérotées : Identification (ref/code barre/type/désignation/catégorie/marque/modèles compatibles), Prix (achat/vente/TVA/marge), Stock (initial/unité/seuils/emplacement), Fournisseur (nom/ref/délai)
- `alerts.blade.php` — 3 sections : En rupture (table rouge), Sous seuil alerte (table amber), Sous seuil commande (table bleu). Message vert si tout va bien.

### Seeder (1)
- `ProductSeeder.php` — 7 catégories (Carrosserie, Peinture, Freinage, Moteur, Éclairage, Consommables, Outillage) + 26 produits réalistes d'atelier carrosserie marocain avec fournisseurs locaux, dont 2 en état d'alerte pour tester les alertes

## Fonctionnalités clés

### Gestion de stock traçable
- Chaque modification de stock crée un mouvement avec stock avant/après
- Stock initial à la création = mouvement d'entrée "achat"
- Pas de modification directe du stock — uniquement via mouvements
- Historique complet consultable et paginé

### Alertes à 3 niveaux
- **Rupture** (rouge) : stock = 0
- **Alerte** (amber) : stock ≤ seuil_alerte
- **À commander** (bleu) : stock ≤ seuil_commande
- Vue dédiée `/products/alerts` avec lien "Approvisionner" vers la fiche produit

### Référencement automatique
- Préfixe par type : PR (pièce), FN (fourniture), OT (outillage), AC (accessoire)
- Séquence à 5 chiffres avec continuité (withTrashed)
- Modifiable manuellement à la création

### Catégories avec couleurs
- Mini CRUD inline dans l'index (admin uniquement)
- Protection : impossible de supprimer une catégorie avec des produits
- Couleur personnalisable (color picker)

### API recherche
- `/products/search-api?q=...` retourne JSON pour autocomplete
- Prêt à être intégré dans les formulaires OR/Devis/Factures

## Routes (13)
```
GET    /products/search-api               → searchApi (JSON)
GET    /products/alerts                    → alerts
GET    /products                           → index
GET    /products/create                    → create
POST   /products                           → store
GET    /products/{id}                      → show
GET    /products/{id}/edit                 → edit
PUT    /products/{id}                      → update
DELETE /products/{id}                      → destroy
POST   /products/{id}/movements            → addMovement
POST   /products/categories               → storeCategory
DELETE /products/categories/{id}           → destroyCategory
```

## Fichiers modifiés
- `routes/web.php` — Import ProductController + 7 routes (resource + 5 custom)
- `sidebar.blade.php` — Lien activé (icône package)
- `DatabaseSeeder.php` — Ajout ProductSeeder
