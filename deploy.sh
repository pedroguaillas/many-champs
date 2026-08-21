#!/bin/bash
set -e

# ===========================================
# many-champs - Deploy Script
# ===========================================
# Postgres/Redis son compartidos con otra app (docker-compose.shared.yml)
# y Caddy (docker-compose.proxy.yml) corren aparte, una sola vez en el droplet.
#
# Usage:
#   Deploy/update:  ./deploy.sh update
#   View logs:      ./deploy.sh logs [service]
#   DB backup:      ./deploy.sh backup
#   DB restore:     ./deploy.sh restore <file>
#   Status:         ./deploy.sh status
#   Stop:           ./deploy.sh stop

COMPOSE="docker compose -f docker-compose.prod.yml"
DB_NAME="${DB_DATABASE:-many_champs}"
PGSQL_CONTAINER="shared_postgres"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

log() { echo -e "${GREEN}[DEPLOY]${NC} $1"; }
warn() { echo -e "${YELLOW}[WARN]${NC} $1"; }
error() { echo -e "${RED}[ERROR]${NC} $1"; exit 1; }

cmd_update() {
    [ -f .env ] || error ".env no encontrado. Configura .env de produccion primero."
    docker network inspect shared_net >/dev/null 2>&1 || error "Red shared_net no existe. Levanta docker-compose.shared.yml y docker-compose.proxy.yml primero."

    if [ -d .git ]; then
        log "Actualizando codigo..."
        git pull origin main
    fi

    log "Construyendo imagen..."
    $COMPOSE build

    log "Levantando servicios..."
    $COMPOSE up -d --remove-orphans

    log "Deploy completo. Migraciones y cache se corren solas via entrypoint."
}

cmd_logs() {
    SERVICE=${1:-""}
    if [ -n "$SERVICE" ]; then
        $COMPOSE logs -f "$SERVICE"
    else
        $COMPOSE logs -f
    fi
}

cmd_backup() {
    BACKUP_DIR="./backups"
    mkdir -p "$BACKUP_DIR"
    TIMESTAMP=$(date +%Y%m%d_%H%M%S)

    log "Respaldando base de datos '$DB_NAME'..."
    docker exec "$PGSQL_CONTAINER" pg_dump -U postgres -d "$DB_NAME" | gzip > "$BACKUP_DIR/${DB_NAME}_${TIMESTAMP}.sql.gz"
    log "Backup guardado en $BACKUP_DIR/${DB_NAME}_${TIMESTAMP}.sql.gz"

    ls -t "$BACKUP_DIR"/"${DB_NAME}"_*.sql.gz 2>/dev/null | tail -n +8 | xargs -r rm
}

cmd_restore() {
    BACKUP_FILE=$1
    [ -z "$BACKUP_FILE" ] && error "Uso: ./deploy.sh restore <archivo.sql.gz>"
    [ -f "$BACKUP_FILE" ] || error "Archivo no encontrado: $BACKUP_FILE"

    warn "Esto SOBRESCRIBE la base '$DB_NAME'. Continuar? (y/N)"
    read -r confirm
    [ "$confirm" = "y" ] || exit 0

    log "Restaurando '$DB_NAME'..."
    gunzip -c "$BACKUP_FILE" | docker exec -i "$PGSQL_CONTAINER" psql -U postgres -d "$DB_NAME"
    log "Restaurado."
}

cmd_status() { $COMPOSE ps; }
cmd_stop() { $COMPOSE down; log "Servicios detenidos."; }

case "${1:-help}" in
    update)  cmd_update ;;
    logs)    cmd_logs "$2" ;;
    backup)  cmd_backup ;;
    restore) cmd_restore "$2" ;;
    status)  cmd_status ;;
    stop)    cmd_stop ;;
    *)
        echo "Uso: $0 <update|logs|backup|restore|status|stop>"
        ;;
esac
