# Deploying SONIT Laravel Portal to cPanel

This Laravel application uses zero Node/Vite runtime dependencies (pure CSS/JS in `public/css/sonit.css` and `public/js/sonit.js`), making it ready for standard shared cPanel hosting.

## 1. Requirements on cPanel
- **PHP 8.2 or 8.3** (select in cPanel -> *MultiPHP Manager*)
- PHP extensions enabled: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`
- **MySQL / MariaDB** database created in cPanel -> *MySQL Databases*

## 2. Upload Files
1. Upload the project files to your cPanel directory (recommended: place Laravel outside `public_html` and point your domain's Document Root to `.../public`, **or** upload into `public_html` — the included root `.htaccess` routes safely into `public/` and blocks access to `.env`, `storage/`, `app/`, and `vendor/`).
2. Ensure `storage/` and `bootstrap/cache/` are writable (`chmod -R 775 storage bootstrap/cache`).
3. Ensure `public/uploads/` is writable (`chmod -R 775 public/uploads`).

## 3. Configure `.env` for MySQL
Copy `.env.example` to `.env` and set:
```env
APP_NAME="SONIT NIT Portal"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cpaneluser_studentsportal
DB_USERNAME=cpaneluser_dbuser
DB_PASSWORD=your_strong_db_password

SESSION_DRIVER=database
CACHE_STORE=database
```

## 4. Run Migrations & Seed NIT Data (via cPanel Terminal or SSH)
```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 5. Seeded Evaluation Accounts (Change Passwords Immediately in Production!)
| Role | Login Identifier | Password |
| :--- | :--- | :--- |
| **Super Admin** | `superadmin@sonit.nit.ac.tz` | `SuperAdmin@2026` |
| **SONIT Admin** | `admin@sonit.nit.ac.tz` | `Admin@2026` |
| **BLTM Class Rep (CR)** | `NIT/BLTM/2022/4805` | `ClassRep@2026` |
| **BIT Class Rep (CR)** | `NIT/BIT/2022/4567` | `ClassRep@2026` |
| **BLTM Student** | `NIT/BLTM/2023/7658` | `Student@2026` |
