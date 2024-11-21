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

## Instalar dependecias de Vite
`./vendor/bin/sail npm install`

