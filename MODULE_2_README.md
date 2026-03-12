# Module 2 : Dashboard (Tableau de bord)

## Fichiers créés / modifiés

### Modifié
- `app/Http/Controllers/DashboardController.php` — Refonte complète
- `routes/web.php` — Ajout de 2 routes API (stats + charts)

### Créé
- `resources/views/dashboard.blade.php` — Vue complète du tableau de bord

## Fonctionnalités

### 5 Cartes Statistiques
1. **Véhicules en cours** — Nombre d'OR en statut brouillon ou en_cours
2. **CA du mois** — Somme TTC des factures du mois + % changement vs mois précédent
3. **Nouveaux clients** — Clients créés ce mois + tendance
4. **Dépenses du mois** — Total des sorties de caisse + tendance (inversée : baisse = vert)
5. **Solde de caisse** — Entrées - Sorties + badge factures impayées

### 4 Graphiques Chart.js
1. **Revenus vs Dépenses** — Line chart, 6 derniers mois, double courbe avec remplissage
2. **Types de réparation** — Doughnut chart, répartition par désignation
3. **Top 5 Clients** — Bar chart horizontal, par chiffre d'affaires
4. **Ordres de Réparation** — Bar chart, nombre d'OR par mois

### Alertes
- Rupture de stock (rouge)
- Stock faible sous seuil d'alerte (orange)
- Véhicules en retard de livraison (orange)
- Factures en retard d'échéance (rouge)
- Factures impayées (bleu)
- Assurances véhicules expirées (bleu)

### Derniers OR
- Tableau des 5 derniers ordres de réparation
- Affiche : N° OR, Client, Véhicule, Statut (badge couleur), Montant TTC
- Badge "En retard" si date prévue dépassée
- Filtrage automatique pour les techniciens (ne voient que leurs OR)

### Activité Récente
- 5 dernières actions du système
- Icônes et couleurs par type d'action
- Lien "Tout voir" vers le journal (admin uniquement)

### Rafraîchissement
- Bouton "Actualiser" manuel
- Auto-refresh toutes les 5 minutes via AJAX
- Routes API : `/dashboard/stats` et `/dashboard/charts`

## Résilience
Le contrôleur utilise `Schema::hasTable()` pour vérifier l'existence des tables.
Le dashboard fonctionne dès le Module 1 (avec des valeurs à 0) et se remplit
progressivement au fur et à mesure que les modules sont ajoutés.

## Notes techniques
- Tooltips en DH marocain (format `1 234,56`)
- Chart.js utilise `Intl.NumberFormat('fr-MA')` pour le formatage
- Les badges de changement (%) sont verts pour les augmentations de revenus,
  mais verts pour les *baisses* de dépenses (logique inversée)
- Le solde de caisse s'affiche en rouge si négatif
