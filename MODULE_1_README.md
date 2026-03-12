# Module 1 : Setup du Projet + Authentification + Rôles

## Installation du projet Laravel

```bash
# 1. Créer le projet Laravel 11
composer create-project laravel/laravel atelier-app "11.*"
cd atelier-app

# 2. Configurer .env
# Copier les valeurs de .env.example et ajuster :
# DB_DATABASE=atelier_db
# DB_USERNAME=root
# DB_PASSWORD=votre_mot_de_passe
# APP_NAME="Atelier Pro"
# APP_URL=http://localhost:8000
# APP_LOCALE=fr

# 3. Installer les dépendances
composer require barryvdh/laravel-dompdf
composer require maatwebsite/excel
composer require intervention/image

# 4. Installer Tailwind CSS
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p

# 5. Créer la base de données MySQL
mysql -u root -p -e "CREATE DATABASE atelier_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 6. Copier les fichiers de ce module dans le projet

# 7. Lancer les migrations et seeders
php artisan migrate
php artisan db:seed

# 8. Lancer le serveur
php artisan serve
```

## Comptes de démonstration

| Rôle          | Email                    | Mot de passe |
|---------------|--------------------------|-------------|
| Administrateur| admin@atelier.ma         | password    |
| Gestionnaire  | gestionnaire@atelier.ma  | password    |
| Comptable     | comptable@atelier.ma     | password    |
| Technicien    | technicien@atelier.ma    | password    |

## Fichiers inclus dans ce module

### Migrations
- `create_users_table.php` (modifiée avec rôle)
- `create_activity_logs_table.php`

### Modèles
- `User.php`
- `ActivityLog.php`

### Middleware
- `RoleMiddleware.php`
- `LogActivity.php`

### Controllers
- `Auth/LoginController.php`
- `Auth/ForgotPasswordController.php`
- `Admin/UserController.php`
- `DashboardController.php`

### Form Requests
- `UserRequest.php`

### Views
- `layouts/app.blade.php` (layout principal avec sidebar)
- `auth/login.blade.php`
- `auth/forgot-password.blade.php`
- `dashboard.blade.php`
- `admin/users/index.blade.php`
- `admin/users/create.blade.php`
- `admin/users/edit.blade.php`
- `components/sidebar.blade.php`
- `components/toast.blade.php`

### Routes
- `web.php`

### Seeders
- `UserSeeder.php`
- `DatabaseSeeder.php`

### Config
- `roles.php`
