# Module 12 : Fournisseurs & Bons de Commande

## Fichiers créés

### Migrations (4)
- `create_suppliers_table.php` — Fournisseur avec code unique (FRS-XXXXX), coordonnées (raison_sociale, contact, tel, email, site_web), adresse marocaine, infos fiscales (ICE, RC, IF, Patente, RIB), conditions commerciales (mode paiement défaut, délai paiement/livraison, remise globale %), type (pieces/peinture/outillage/general), solde_du, actif, soft deletes
- `add_supplier_id_to_products_table.php` — FK supplier_id sur la table products (Module 11)
- `create_purchase_orders_table.php` — Bon de commande (BC-YYYY-XXXXX), FK supplier_id/created_by, dates (commande/livraison prévue/réception), 6 statuts (brouillon/envoyee/confirmee/livree_partiel/livree/annulee), montants HT/TVA/TTC/remise/net, référence fournisseur, soft deletes
- `create_purchase_order_items_table.php` — Lignes BC : FK product_id (optionnel), désignation, référence, quantité commandée/reçue, unité, prix unitaire, remise %, montants HT/TTC, ordre

### Modèles (3)
- `Supplier.php` (165 lignes) — 4 types avec couleurs, 5 modes paiement, relations products/purchaseOrders, scopes search/byType/actifs, accessors type_badge/mode_paiement_label/adresse_complete, `generateCode()` FRS-XXXXX, `recalculateSolde()`, stats total_achats_30j/nb_commandes
- `PurchaseOrder.php` (221 lignes) — Machine à états 6 statuts avec transitions, relations supplier/createdBy/items, `generateNumero()` BC-YYYY-XXXXX, `recalculateTotals()`, `receiveItems()` (réception partielle/totale avec mise à jour automatique du stock produit et transition statut)
- `PurchaseOrderItem.php` (71 lignes) — Relations purchaseOrder/product, accessors reste_a_recevoir/is_fully_received, `calculateTotals()` auto en saving, événements Eloquent pour recalcul BC parent

### Controller (1)
- `SupplierController.php` — 13 méthodes :
  - CRUD fournisseurs (index/create/store/show/edit/update/destroy) avec stats, filtres, protection suppression si commandes en cours
  - `createOrder` — Formulaire nouvelle BC avec produits pré-liés au fournisseur
  - `storeOrder` — Création BC avec lignes (transaction DB)
  - `showOrder` — Détail BC avec lignes, progression réception, transitions disponibles
  - `updateOrderStatut` — Machine à états avec validation
  - `receiveOrder` — Réception articles avec quantités, mise à jour stock automatique
  - `searchApi` — Endpoint JSON pour autocomplétion

### Vues (7)
- `index.blade.php` (118 lignes) — Stats (total actifs, avec solde, solde total DH, commandes mois), filtres search/type, table avec code/raison sociale/contact/ville/type badge/produits count/solde
- `_form.blade.php` (145 lignes) — Formulaire 3 sections : informations générales (code auto, raison sociale, contact, tels, email, site web), adresse + infos fiscales (ICE, RC, IF, patente, RIB), conditions commerciales (type, mode paiement, délais, remise)
- `create.blade.php` / `edit.blade.php` — Wrappers formulaire
- `show.blade.php` (177 lignes) — Header avec code + type badge + actif/inactif. Stats commandes (total/en cours/total achats). Onglets : dernières commandes (table avec numéro/date/statut/montant) + produits liés + infos complètes
- `create-order.blade.php` (137 lignes) — Formulaire BC Alpine.js avec lignes dynamiques, produits du fournisseur pré-chargés, calcul totaux temps réel
- `show-order.blade.php` — Détail BC : lignes avec progression réception (vert/amber/gris), section réception interactive (saisie quantités reçues par article), sidebar infos/financier/fournisseur, boutons changement statut

### Seeder (1)
- `SupplierSeeder.php` — 5 fournisseurs réalistes marocains (Casablanca, Agadir, Fès), 3 bons de commande (1 livré, 1 confirmé en attente, 1 brouillon), liaison automatique produits-fournisseurs

## Machine à états — Bons de Commande

```
brouillon → envoyee, annulee
envoyee → confirmee, annulee
confirmee → livree_partiel, livree, annulee
livree_partiel → livree
livree → (final)
annulee → brouillon
```

## Fonctionnalités clés

### Gestion fournisseurs
- Code auto-généré FRS-XXXXX
- Infos fiscales marocaines complètes (ICE, RC, IF, Patente)
- Conditions commerciales par défaut (mode paiement, délais, remise)
- 4 types : Pièces, Peinture & consommables, Outillage, Général
- Suivi solde dû

### Bons de commande
- Numérotation BC-YYYY-XXXXX
- Lignes liées aux produits du catalogue (optionnel)
- Remise par ligne + remise globale
- Référence fournisseur (BL du fournisseur)

### Réception intelligente
- Réception partielle : saisie quantité reçue par article
- Mise à jour automatique du stock (appel Product::addStock avec motif 'achat')
- Transition automatique : confirmée → livraison partielle → livrée
- Traçabilité : mouvement de stock créé avec référence BC

### Intégration Module 11 (Stock)
- FK supplier_id sur products
- Produits du fournisseur pré-chargés dans formulaire BC
- Réception BC → ajout stock automatique avec StockMovement

## Routes (12)

```
GET    /suppliers/search-api                        → searchApi (AJAX)
GET    /suppliers                                    → index
GET    /suppliers/create                             → create
POST   /suppliers                                    → store
GET    /suppliers/{id}                               → show
GET    /suppliers/{id}/edit                           → edit
PUT    /suppliers/{id}                               → update
DELETE /suppliers/{id}                               → destroy
GET    /suppliers/{id}/orders/create                  → createOrder
POST   /suppliers/{id}/orders                        → storeOrder
GET    /suppliers/{id}/orders/{order}                 → showOrder
PATCH  /suppliers/{id}/orders/{order}/statut          → updateOrderStatut
POST   /suppliers/{id}/orders/{order}/receive         → receiveOrder
```

## Fichiers modifiés
- `routes/web.php` — Import SupplierController, 12 routes activées
- `sidebar.blade.php` — Lien activé (icône factory)
- `DatabaseSeeder.php` — Ajout SupplierSeeder
