# Module 9 : Factures

## Fichiers créés

### Migrations (3)
- `create_invoices_table.php` — Numéro auto (FA-YYYY-XXXXX), lié à OR + client + véhicule + BL, dates (facture + échéance), 6 statuts (brouillon/émise/payée/partielle/en_retard/annulée), montants complets (HT/TVA/TTC/remise/net/payé/reste), objet, conditions, mentions légales
- `create_invoice_items_table.php` — Même structure que repair_order_items/quote_items : type (4), désignation, référence, quantité, unité, prix unitaire, remise, TVA
- `create_invoice_payments_table.php` — Paiements multiples par facture : date, montant, mode (5 : espèces/chèque/virement/carte/effet), référence, banque, notes, recorded_by

### Modèles (3)
- `Invoice.php` — Numérotation auto, 6 statuts avec couleurs, scopes (search, byStatut, unpaid, overdue), accessors (statut_badge, is_overdue, is_paid, progress_percent), recalculateTotals(), recalculatePayments() (auto-transition payée/partielle), createFromRepairOrder() (copie lignes OR), mentions légales par défaut conformes loi 09-08
- `InvoiceItem.php` — Calcul automatique montant_ht/montant_ttc via événements Eloquent, recalcul parent
- `InvoicePayment.php` — Événements saved/deleted déclenchent recalculatePayments() sur la facture (auto-transition statut)

### Controller (1)
- `InvoiceController.php` — 11 méthodes :
  - `index` — Stats financières (total, CA mois, encaissé mois, impayés DH, en retard count), auto-marquage en retard, filtres (recherche, statut, dates, checkbox impayées)
  - `create` — Depuis un OR (pré-rempli) ou facture libre, vérification doublons
  - `store` — Création, copie lignes, transition OR → "facturé"
  - `show` — Vue détaillée avec lignes, historique paiements, formulaire paiement intégré
  - `edit` / `update` — Bloqué si payée/annulée
  - `emit` — Brouillon → émise (vérifie au moins 1 ligne)
  - `cancel` — Annulation (bloqué si paiements existants)
  - `addPayment` — Enregistrement paiement inline (date, montant, mode, référence, banque)
  - `deletePayment` — Suppression paiement avec recalcul auto
  - `destroy` — Suppression (bloqué si payée)

### Vues (5)
- `index.blade.php` — 5 stats financières (total factures, CA mois, encaissé mois, impayés DH, en retard count), filtres complets, table avec N° facture + badge retard, client, OR, statut, date + échéance, net TTC, payé, reste (rouge/vert)
- `show.blade.php` — 3 cartes header (client avec ICE, véhicule, références OR/BL). Lignes avec totaux. **Historique paiements** avec formulaire inline Alpine.js (toggle, pré-rempli reste à payer, modes marocains). Barre de progression paiement (%). Actions : émettre, modifier, annuler (bloqué si paiements). Suppression paiements individuels
- `create.blade.php` / `edit.blade.php` — Via partial
- `_form.blade.php` — Formulaire Alpine.js : sélection OR source ou facture libre, dates (facture + échéance 30j), objet, conditions, mentions légales pré-remplies. Lignes dynamiques identiques aux modules 6/8. Lignes auto-copiées depuis OR si création depuis OR

### Seeder (1)
- `InvoiceSeeder.php` — Crée des factures depuis les OR livrés/facturés avec 4 scénarios : entièrement payé (1 paiement), partiellement payé (acompte), en retard (pas de paiement), émise récemment. Banques marocaines réalistes (Attijariwafa, BMCE, BP, CIH, CDM)

## Fonctionnalités clés

### Système de paiements multiples
- Chaque facture peut recevoir N paiements
- Formulaire inline dans la vue show (pas de page séparée)
- 5 modes de paiement : Espèces, Chèque, Virement, Carte, Effet de commerce
- Chaque paiement stocke : date, montant, mode, référence (N° chèque), banque, notes
- Recalcul automatique via événements Eloquent (InvoicePayment::saved/deleted → recalculatePayments())

### Auto-transition de statut
```
brouillon → émise (action manuelle)
émise → payée (automatique quand total_paye >= net_a_payer)
émise → partielle (automatique quand 0 < total_paye < net_a_payer)
émise/partielle → en_retard (automatique quand date_echeance < today)
* → annulée (action manuelle, bloqué si paiements existants)
```

### Barre de progression
- Visuelle dans la vue show (pourcentage payé)
- Couleurs : vert si 100%, ambre si partiel, gris si 0%

### Création depuis un OR
- Lignes de l'OR copiées automatiquement dans la facture
- OR passe en statut "facturé"
- BL lié automatiquement si existant
- Un OR ne peut avoir qu'une seule facture active

### Auto-marquage en retard
- À chaque visite de l'index, les factures émises/partielles dont l'échéance est passée passent en "en_retard"

### Mentions légales marocaines
- Pré-remplies avec référence loi 09-08 (protection données personnelles)
- Pénalités de retard : 1,5% par mois
- Modifiables par facture

## Routes ajoutées (11)
```
GET    /invoices                                    → index
GET    /invoices/create                             → create
POST   /invoices                                    → store
GET    /invoices/{id}                               → show
GET    /invoices/{id}/edit                          → edit
PUT    /invoices/{id}                               → update
DELETE /invoices/{id}                               → destroy
PATCH  /invoices/{id}/emit                          → emit
PATCH  /invoices/{id}/cancel                        → cancel
POST   /invoices/{id}/payments                      → addPayment
DELETE /invoices/{id}/payments/{payment}             → deletePayment
```

## Fichiers modifiés
- `routes/web.php` — Import InvoiceController + 6 routes
- `sidebar.blade.php` — Lien activé (icône receipt)
- `RepairOrder.php` — Ajout relation `invoice()` (hasOne)
- `repair-orders/show.blade.php` — Bouton "Facturer" pour OR livrés/terminés sans facture
- `DatabaseSeeder.php` — Ajout InvoiceSeeder
