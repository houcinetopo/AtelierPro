# Atelier Pro — Phase 2 : Améliorations

## Résumé des 8 Modifications

### Modification 1 : Liaison Paiement → Caisse
**Fichiers modifiés/créés :**
- `app/Observers/InvoicePaymentObserver.php` (NOUVEAU)
- `app/Providers/AppServiceProvider.php` (MODIFIÉ — observer enregistré)

**Fonctionnement :** Lorsqu'un paiement est enregistré sur une facture via `InvoicePayment::create()`, l'observer crée automatiquement un `CashMovement` d'entrée dans la session de caisse du jour. Si aucune session n'est ouverte, elle est créée automatiquement. La suppression d'un paiement supprime le mouvement de caisse lié.

---

### Modification 2 : Boutons Documents (PDF, Impression, Email)
**Fichiers créés :**
- `app/Http/Controllers/DocumentController.php`
- `app/Services/DocumentService.php`
- `resources/views/components/document-actions.blade.php`

**Nouvelles routes :**
```
GET  /documents/{type}/{id}/download  → Téléchargement PDF
GET  /documents/{type}/{id}/print     → Affichage PDF (impression)
POST /documents/{type}/{id}/email     → Envoi par email
```

**Usage dans les vues :**
```blade
@include('components.document-actions', [
    'type' => 'facture',
    'document' => $invoice,
    'showExpert' => true,
    'showFournisseur' => false,
])
```

**Important :** Nécessite l'installation de DomPDF :
```bash
composer require barryvdh/laravel-dompdf
```
Et la création des vues PDF dans `resources/views/pdf/`.

---

### Modification 3 : Notifications SMS et Email
**Fichiers créés :**
- `app/Observers/RepairOrderObserver.php`
- `app/Models/NotificationLog.php`
- Migration : table `notification_logs`

**Fonctionnement :** Quand un OR passe au statut `termine`, l'observer envoie automatiquement un email au client (via Laravel Mail) et log un SMS en attente. Le SMS nécessite la configuration d'une API (Twilio/Infobip).

**Variables .env à configurer :**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...

# Pour SMS (quand prêt)
TWILIO_SID=...
TWILIO_TOKEN=...
TWILIO_FROM=...
```

---

### Modification 4 : Améliorations Fiche Expert
**Fichiers créés :**
- `app/Models/Expert.php`
- `app/Models/ExpertEmail.php`
- `app/Http/Controllers/ExpertController.php`
- `resources/views/experts/` (index, create, edit, show, _form)
- Migration : tables `experts` et `expert_emails`

**Champs supprimés :** `specialite`, `n_agrement` (n'existaient pas encore en DB)  
**Ajouté :** Support de plusieurs emails par expert avec un email marqué comme principal.

---

### Modification 5 : Devis obligatoire avant OR
**Fichiers modifiés :**
- `app/Http/Controllers/RepairOrderController.php` — méthode `create()` redirige vers les devis
- `app/Models/RepairOrder.php` — ajout relation `quote()`
- Migration : ajout colonne `quote_id` sur `repair_orders`

**Flux :** Devis (créer) → Accepter → Convertir en OR → L'OR hérite des données du devis. La conversion est gérée par `Quote::convertToRepairOrder()` qui existait déjà.

---

### Modification 6 : Actions depuis l'Ordre de Réparation
**Fichiers modifiés/créés :**
- `app/Http/Controllers/RepairOrderController.php` — méthodes `generateInvoice()`, `generateDeliveryNote()`, `generatePurchaseOrder()`
- `resources/views/components/or-document-generator.blade.php`

**Nouvelles routes :**
```
POST /repair-orders/{id}/generate-invoice
POST /repair-orders/{id}/generate-delivery-note
POST /repair-orders/{id}/generate-purchase-order
```

---

### Modification 7 : Liaison OR – Stock – Fournisseurs
**Fichiers créés :**
- `app/Services/StockService.php`
- `resources/views/components/stock-picker.blade.php`
- Migration : ajout `product_id`, `fournisseur_id`, `prix_achat`, `source` sur `repair_order_items`

**Nouvelles routes :**
```
POST   /repair-orders/{id}/add-product
DELETE /repair-orders/{id}/remove-product/{item}
```

**Fonctionnement :** L'ajout d'une pièce décrémente le stock atomiquement. Si stock insuffisant, la pièce est marquée `source=commande` et un bon de commande est recommandé. Le retrait d'une pièce remet la quantité en stock.

---

### Modification 8 : Calcul des Coûts et Rentabilité
**Fichiers modifiés/créés :**
- `app/Models/RepairOrder.php` — accesseurs `cout_pieces`, `cout_main_oeuvre`, `cout_total`, `benefice`, `marge`, `resume_financier`
- `resources/views/components/financial-summary.blade.php`

**Usage :** Le résumé financier est automatiquement calculé à partir des `prix_achat` des items de l'OR vs les `prix_unitaire` facturés.

---

## Migrations à exécuter

```bash
php artisan migrate
```

Migrations créées :
1. `2026_03_13_000001_create_experts_table.php` — Tables experts + expert_emails
2. `2026_03_13_000002_add_phase2_columns.php` — Colonnes quote_id, expert_id sur repair_orders + product_id, fournisseur_id, prix_achat, source sur repair_order_items + table notification_logs
3. `2026_03_13_000003_add_repair_order_id_to_purchase_orders.php` — Colonne repair_order_id sur purchase_orders

## Dépendances à installer

```bash
composer require barryvdh/laravel-dompdf
```

## Intégration dans les vues existantes

Pour intégrer les nouveaux composants dans la page `repair-orders/show.blade.php`, ajoutez :

```blade
{{-- Boutons Document (Modification 2) --}}
@include('components.document-actions', ['type' => 'ordre_reparation', 'document' => $repairOrder, 'showExpert' => true])

{{-- Générer des documents (Modification 6) --}}
@include('components.or-document-generator', ['repairOrder' => $repairOrder])

{{-- Ajouter pièce du stock (Modification 7) --}}
@include('components.stock-picker', ['repairOrder' => $repairOrder, 'products' => $products])

{{-- Résumé financier (Modification 8) --}}
@include('components.financial-summary', ['resumeFinancier' => $resumeFinancier])
```
