# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Environment

This project uses Laravel Sail (Docker). All commands should be run via `./vendor/bin/sail`:

```bash
# Start the environment
./vendor/bin/sail up -d

# Stop the environment
./vendor/bin/sail down
```

If starting fresh (no vendor/ directory yet):
```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php81-composer:latest \
    composer install --ignore-platform-reqs
```

## Common Commands

```bash
# Frontend dev server (hot reload on port 5173)
./vendor/bin/sail npm run dev

# Build frontend for production (includes SSR build)
./vendor/bin/sail npm run build

# Install/update PHP dependencies
./vendor/bin/sail composer install

# Install/update Node dependencies
./vendor/bin/sail npm install

# Run database migrations
./vendor/bin/sail artisan migrate

# Run all PHP tests
./vendor/bin/sail artisan test

# Run a single test file
./vendor/bin/sail artisan test tests/Feature/SomeTest.php

# Run a single test method
./vendor/bin/sail artisan test --filter=test_method_name
```

## Architecture

**Stack**: Laravel 10 + Inertia.js + Vue 3 + Tailwind CSS, with Server-Side Rendering (SSR).

**Request flow**:
```
HTTP Request → Laravel Routes (routes/web.php) → Controller → Inertia::render($component, $data) → Vue Page (SSR hydrated on client)
```

Inertia.js is the bridge between backend and frontend — controllers return `Inertia::render()` calls instead of traditional JSON/blade responses, passing data as props to Vue page components.

**Backend** (`app/`):
- `Http/Controllers/` — 14 domain controllers. Each maps to a feature area (games, clubs, players, categories, payments, etc.)
- `Models/` — Eloquent models. Key relationships: `Category → Groups → Clubs → Players`, `Game → GameItems`
- `Actions/` — Single-purpose action classes (Jetstream pattern)
- `Policies/` — Authorization policies

**Frontend** (`resources/js/`):
- `Pages/` — Full-page Vue components, organized by domain (one per route)
- `Components/` — Reusable UI components (buttons, modals, forms, dropdowns)
- `Layouts/` — Page layout wrappers
- `Shared/` — Shared utilities/components
- `app.js` — Client-side entry point (Inertia + Vue setup)
- `ssr.js` — Server-side rendering entry point

## Domain Model

This is a **sports tournament management system** (futsal/soccer) in Spanish:

- **Category** — A tournament/league instance
- **Group** — Division within a category
- **Club** — A team in a group
- **Player** — Individual player belonging to a club
- **Game** — A match between two clubs; has states: `creado → planificado → jugando → finalizado`
- **GameItem** — Play-by-play event within a game (goals, assists, yellow/red cards)
- **Payment** — Payment records per club
- **Progress** — Tournament progress tracking

## Key Routes

All main routes are authenticated (middleware: `auth`, `verified`). See `routes/web.php` for full list. Key flows:
1. `/dashboard` → select category → manage clubs/players
2. `/categoria/{category}/juegos` → create game → select players → live tracking (`/partido/{game_id}`) → finalize
3. Statistics: `/tabla-de-posiciones`, `/tabla-de-goleadores`, `/sanciones`

## UI Libraries

- **Tailwind CSS** with `@tailwindcss/forms` and `@tailwindcss/typography` plugins; Roboto font as default sans-serif
- **Font Awesome 6** for icons
- **SweetAlert2** for confirmation dialogs/alerts
- **Ziggy** for using named Laravel routes in JavaScript (`route('name', params)`)
