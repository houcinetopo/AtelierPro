# Module 7 : Bons de Livraison

## Fichiers créés

### Migration (1)
- `create_delivery_notes_table.php` — Numéro auto (BL-YYYY-XXXXX), lié à repair_order + client + vehicle, dates/heures, km sortie, carburant, travaux effectués, observations, réserves client, recommandations, réceptionnaire (nom + CIN), signatures (atelier + client), montants (total_ttc, montant_paye, reste_a_payer), mode paiement (6 modes dont crédit), statut (brouillon/validé/annulé)

### Modèle (1)
- `DeliveryNote.php` — Numérotation auto, scopes (search, byStatut, valides, withUnpaid), accessors (statut_badge, mode_paiement_label, is_paid), helper `createFromRepairOrder()` pour générer un BL depuis un OR

### Controller (1)
- `DeliveryNoteController.php` — 8 méthodes :
  - `index` — Liste paginée avec stats, filtres (recherche, statut, dates, impayés)
  - `create` — Sélection OR éligible ou pré-remplissage si `?repair_order_id=X`, vérification doublons
  - `store` — Création BL, calcul reste à payer, transition OR vers "livré", MAJ km véhicule, MAJ solde crédit client si paiement à crédit
  - `show` — Vue détaillée avec lignes de l'OR
  - `edit` / `update` — Modification (sauf annulés)
  - `validate_note` — Passage brouillon → validé
  - `cancel` — Annulation
  - `destroy` — Suppression (sauf validés, il faut annuler d'abord)

### Vues (5)
- `index.blade.php` — 4 stats (total, validés, ce mois, impayés) + filtres (recherche, statut, dates, checkbox impayés) + table avec N° BL, N° OR, client, véhicule, date, total TTC, reste à payer (rouge si impayé / vert si soldé), statut badge
- `show.blade.php` — 3 colonnes header (OR, client, véhicule) + travaux effectués + détail lignes OR + observations/réserves/recommandations. Colonne droite : état véhicule, réceptionnaire avec signatures, paiement avec reste à payer. Actions : valider (brouillon→validé), modifier, annuler, supprimer
- `create.blade.php` / `edit.blade.php` — Via partial
- `_form.blade.php` — Formulaire 5 étapes avec Alpine.js :
  - Étape 1 : Sélection OR source (dropdown ordres terminés sans BL, ou pré-sélectionné)
  - Étape 2 : Date/heure livraison, km sortie, carburant
  - Étape 3 : Travaux (pré-rempli depuis OR), observations, réserves client, recommandations
  - Étape 4 : Réceptionnaire (nom + CIN), checkboxes signatures atelier/client
  - Étape 5 : Paiement (total TTC affiché, montant payé, mode, reste à payer dynamique)

### Seeder (1)
- `DeliveryNoteSeeder.php` — Crée des BL pour les ordres terminés/livrés, 75% payés, recommandations variées, réceptionnaires réalistes

## Fonctionnalités clés

### Lien OR → BL
- Un bon de livraison est toujours lié à un ordre de réparation
- Un OR ne peut avoir qu'un seul BL actif (brouillon ou validé)
- Bouton "Créer BL" ajouté dans la vue show de l'OR (visible quand OR terminé/livré et sans BL)
- Les travaux sont pré-remplis depuis les lignes de l'OR

### Workflow de statut
```
brouillon → validé (irréversible)
brouillon / validé → annulé
```
- Un BL validé ne peut pas être supprimé, il faut l'annuler d'abord
- Un BL annulé ne peut plus être modifié

### Gestion financière
- Total TTC repris de l'OR (net_a_payer)
- Montant payé saisi à la livraison
- Reste à payer calculé automatiquement (Alpine.js temps réel + serveur)
- 6 modes de paiement : Espèces, Chèque, Virement, Carte, À crédit, Mixte
- Si paiement à crédit : incrémentation du solde_credit du client
- Badge "Soldé" (vert) ou montant restant (rouge) dans la liste

### Réceptionnaire
- Nom et CIN de la personne qui récupère le véhicule
- Checkboxes de signature (atelier + client)
- Préparé pour future signature numérique (champ signature_client_path)

### Intégrations
- OR → transition automatique vers "livré" à la création du BL
- Véhicule → MAJ kilométrage sortie
- Client → MAJ solde crédit si paiement à crédit

## Routes ajoutées (9)
```
GET    /delivery-notes                          → index
GET    /delivery-notes/create                   → create
POST   /delivery-notes                          → store
GET    /delivery-notes/{id}                     → show
GET    /delivery-notes/{id}/edit                → edit
PUT    /delivery-notes/{id}                     → update
DELETE /delivery-notes/{id}                     → destroy
PATCH  /delivery-notes/{id}/validate            → validate_note
PATCH  /delivery-notes/{id}/cancel              → cancel
```

## Fichiers modifiés
- `routes/web.php` — Import DeliveryNoteController + routes
- `sidebar.blade.php` — Lien activé (icône truck)
- `RepairOrder.php` — Ajout relation `deliveryNote()` (hasOne)
- `Client.php` — Ajout relation `deliveryNotes()` (hasMany)
- `repair-orders/show.blade.php` — Bouton "Créer BL" pour OR terminés
- `DatabaseSeeder.php` — Ajout DeliveryNoteSeeder
