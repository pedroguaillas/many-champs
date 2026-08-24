# Levantar entorno

## Instalar desde docker dependencias de Laravel
`docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php81-composer:latest \
    composer install --ignore-platform-reqs`

## Levantar el ambiente
`./vendor/bin/sail up -d`

## Bajar el ambiente
`./vendor/bin/sail down`

## Instalar dependecias de Vite
`./vendor/bin/sail pnpm install`

## Frontend dev server (hot reload puerto 5173)
`./vendor/bin/sail pnpm run dev`

## Build frontend para producción (incluye build SSR)
`./vendor/bin/sail pnpm run build`

## Correr migraciones
`./vendor/bin/sail artisan migrate`

## Correr todos los tests
`./vendor/bin/sail artisan test`

## Correr un archivo de test específico
`./vendor/bin/sail artisan test tests/Feature/SomeTest.php`

## Correr un método de test específico
`./vendor/bin/sail artisan test --filter=test_method_name`

# Producción (VPS / Docker)

## Deploy / rebuild imagen
`docker compose -f docker-compose.prod.yml up -d --build`

## Ver logs en vivo (stdout del container)
`docker logs otra_app --tail 200 -f`

## Ver log de Laravel dentro del container
`docker exec -it otra_app tail -n 200 -f storage/logs/laravel.log`

## Vaciar el log de Laravel sin borrar el archivo
`docker exec -it otra_app truncate -s 0 storage/logs/laravel.log`

## Descarga del log de laravel
`docker cp otra_app:/var/www/html/storage/logs/laravel.log ./laravel.log`
`scp user@IP-Ser:/opt/many-champs/laravel.log /path-local/laravel.log`
