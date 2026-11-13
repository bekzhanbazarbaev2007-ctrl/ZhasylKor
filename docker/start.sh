
#!/bin/sh
set -e

echo "Preparing Laravel..."

php artisan optimize:clear
php artisan migrate --force

echo "Starting ZhasylKor..."

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
