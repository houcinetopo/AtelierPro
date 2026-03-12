# Guide de Déploiement — Hostinger Shared Hosting

## Prérequis

- Plan Hostinger Business Shared (~4€/mois)
- PHP 8.2+ activé dans hPanel
- Base de données MySQL créée
- Accès SSH activé

---

## Étapes de déploiement

### 1. Préparer l'application en local

```bash
# Optimiser pour la production
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link

# Modifier .env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.ma
```

### 2. Créer la base de données sur Hostinger

1. Aller dans **hPanel → Bases de données → MySQL**
2. Créer une nouvelle base de données
3. Noter : nom_db, utilisateur_db, mot_de_passe_db
4. Mettre à jour `.env` avec ces informations

### 3. Uploader les fichiers

**Option A — Via SSH (recommandé) :**
```bash
# Se connecter en SSH
ssh u123456789@votre-serveur.hostinger.com -p 65002

# Cloner le repo dans le dossier home
cd ~
git clone https://github.com/votre-repo/atelier-app.git

# Copier les fichiers vers public_html
cp -r atelier-app/* ~/domains/votre-domaine.ma/public_html/
```

**Option B — Via File Manager :**
1. Compresser le projet en .zip
2. Uploader dans `public_html/`
3. Extraire

### 4. Configurer le .htaccess

Le fichier `.htaccess` à la racine redirige vers `/public`.
S'assurer qu'il est bien à la racine de `public_html/`.

### 5. Configurer les permissions

```bash
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

### 6. Lancer les migrations

```bash
cd ~/domains/votre-domaine.ma/public_html/
php artisan migrate --force
php artisan db:seed --force
```

### 7. Créer le lien symbolique storage

```bash
php artisan storage:link
```

### 8. Vérifier

- Accéder à `https://votre-domaine.ma`
- Se connecter avec `admin@atelier.ma` / `password`
- **CHANGER LE MOT DE PASSE ADMIN IMMÉDIATEMENT**

---

## Dépannage

| Problème | Solution |
|----------|----------|
| Page blanche | Vérifier `APP_DEBUG=true` temporairement, consulter `storage/logs/laravel.log` |
| Erreur 500 | Vérifier permissions de `storage/` et `bootstrap/cache/` |
| CSS/JS ne charge pas | Vérifier `APP_URL` dans `.env`, exécuter `php artisan storage:link` |
| Erreur de connexion DB | Vérifier les identifiants dans `.env`, le host est souvent `localhost` sur Hostinger |

---

## Maintenance

```bash
# Vider les caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Recacher pour la production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
