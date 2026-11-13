# ЖасылҚор (GreenFund Map)

ЖасылҚор is a Laravel application for environmental reporting and green-infrastructure management in Kyzylorda. It uses Blade and Livewire, SQLite, Leaflet/OpenStreetMap, and Chart.js.

## Local setup

Requirements: PHP 8.3+, Composer, Node.js, and npm.

1. Install PHP dependencies and create your local environment file:

    ```powershell
    composer install
    Copy-Item .env.example .env
    php artisan key:generate
    ```

2. Ensure `database\database.sqlite` exists, then keep `DB_CONNECTION=sqlite` in `.env`. For a new checkout:

    ```powershell
    if (-not (Test-Path database\database.sqlite)) {
        New-Item -ItemType File database\database.sqlite | Out-Null
    }
    ```

3. Install frontend dependencies, migrate, and add synthetic local demo data:

    ```powershell
    npm install
    php artisan migrate
    php artisan db:seed
    php artisan storage:link
    npm run build
    ```

    `DatabaseSeeder` only inserts the demo accounts and sample Kyzylorda records when `APP_ENV` is `local` or `testing`. The records are synthetic, not official city data.

4. Start the Laravel server and Vite development server in separate terminals:

    ```powershell
    php artisan serve
    ```

    ```powershell
    npm run dev
    ```

    Open `http://127.0.0.1:8000`.

## Local demo accounts

All demo accounts use `GreenFundLocal2026!`:

| Role                   | Email                         |
| ---------------------- | ----------------------------- |
| Citizen                | `citizen@greenfund.local`     |
| Operator               | `operator@greenfund.local`    |
| Executor               | `executor@greenfund.local`    |
| Landscaping specialist | `landscaping@greenfund.local` |

These credentials are for local development only. Change or remove them before exposing any environment beyond local development.

## Tests

Run the test suite with:

```powershell
php artisan test --compact
```
