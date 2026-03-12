# Module 10 : Caisse

## Fichiers créés

### Migrations (2)
- `create_cash_sessions_table.php` — Session journalière unique (date_session unique), solde ouverture/entrées/sorties/théorique/réel/écart, statut ouverte/clôturée, heures ouverture/clôture, notes, liens opened_by/closed_by (users)
- `create_cash_movements_table.php` — Mouvements liés à une session, type entree/sortie, 11 catégories (paiement_client, acompte, autre_entree, achat_pieces, salaire, loyer, charges, carburant, outillage, frais_divers, remboursement), liens optionnels invoice/invoice_payment/employee_payment, mode_paiement, référence, bénéficiaire

### Modèles (2)
- `CashSession.php` (168 lignes) — Relations openedBy/closedBy/movements/entrees/sorties, `recalculate()` (sum entrées/sorties, solde théorique, écart si solde réel saisi), `openToday()` (reprend solde théorique de la veille), `close()` (calcule écart réel vs théorique), accessors statut_badge/ecart_badge/is_open
- `CashMovement.php` (110 lignes) — Constantes CATEGORIES_ENTREE (3) et CATEGORIES_SORTIE (8), MODES_PAIEMENT (5), relations cashSession/recordedBy/invoice/invoicePayment, événements Eloquent saved/deleted → recalculate() auto sur session parent

### Controller (1)
- `CashController.php` — 6 méthodes :
  - `index` — Session du jour + historique paginé avec filtre par mois + stats (solde actuel, entrées/sorties mois, nb sessions, écart total mois)
  - `open` — Ouvre la caisse du jour (solde d'ouverture = solde théorique de la dernière session si non spécifié)
  - `session` — Journal de caisse : liste mouvements entrées/sorties, résumé par catégorie triée par montant
  - `close` — Clôture avec saisie du solde réel, calcul d'écart
  - `addMovement` — Ajoute une entrée ou sortie (validation type/catégorie/libellé/montant/mode)
  - `deleteMovement` — Supprime un mouvement (bloqué si session clôturée ou lié à paiement facture)

### Vues (2)
- `index.blade.php` (197 lignes) — 5 stats (solde actuel, entrées mois, sorties mois, sessions, écart total). Bouton "Ouvrir la caisse" si pas de session aujourd'hui (formulaire solde d'ouverture + notes). Bouton "Voir la caisse du jour" si session ouverte. Table historique : date, solde ouverture, entrées, sorties, solde théorique, solde réel, écart (badge couleur), statut, nb mouvements. Filtre par mois.
- `session.blade.php` (319 lignes) — Header avec date + statut badge + solde théorique. 4 cartes stats (solde ouverture, total entrées vert, total sorties rouge, solde théorique). Résumé par catégorie (barres avec label/count/total). Formulaire ajout mouvement (type toggle entree/sortie, catégorie dynamique Alpine.js, libellé, montant, mode paiement, référence, bénéficiaire, notes). Table mouvements : heure, type badge (vert/rouge), catégorie, libellé, mode, bénéficiaire, montant (signé), bouton supprimer. Section clôture (saisie solde réel, notes, affichage écart en temps réel Alpine.js).

### Seeder (1)
- `CashSeeder.php` (102 lignes) — Crée des sessions sur les 7 derniers jours avec mouvements réalistes (paiements clients, achats pièces, salaires, charges, carburant). 6 sessions clôturées avec écarts variés + 1 session ouverte aujourd'hui.

## Fonctionnalités clés

### Session journalière unique
- Une seule session par date (contrainte unique sur date_session)
- À l'ouverture, le solde d'ouverture reprend automatiquement le solde théorique de la session précédente
- Le solde théorique = ouverture + entrées - sorties (calculé automatiquement)

### Clôture avec contrôle d'écart
- Saisie du solde réel compté physiquement
- Écart = solde réel - solde théorique
- Badge couleur : vert si 0, bleu si excédent, rouge si déficit

### Catégories de mouvements
- **Entrées** : Paiement client, Acompte, Autre recette
- **Sorties** : Achat pièces, Salaire, Loyer, Charges, Carburant, Outillage, Frais divers, Remboursement

### Liens avec autres modules
- Mouvement ↔ Facture (invoice_id)
- Mouvement ↔ Paiement facture (invoice_payment_id)
- Mouvement ↔ Paiement employé (employee_payment_id)
- Protection : mouvement lié à un paiement facture ne peut être supprimé manuellement

### Recalcul automatique
- Événements Eloquent sur CashMovement (saved/deleted) déclenchent recalculate() sur la session
- Totaux entrées/sorties/théorique/écart toujours à jour

## Routes (6)
```
GET    /cash                               → index
POST   /cash/open                          → open
GET    /cash/session/{id}                  → session
PATCH  /cash/session/{id}/close            → close
POST   /cash/session/{id}/movements        → addMovement
DELETE /cash/session/{id}/movements/{id}   → deleteMovement
```

## Fichiers modifiés
- `routes/web.php` — Routes cash group avec prefix/name
- `sidebar.blade.php` — Lien activé (icône banknote)
- `DatabaseSeeder.php` — Ajout CashSeeder
