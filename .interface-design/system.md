# Many Champs — Interface Design System

## Direction
**Stadium control room** — dark chrome shell with illuminated panel cards. The admin manages tournaments from a professional, dense, focused environment. Like a scoreboard operator's booth during a night match.

## Signature
**Pitch stripe** — a 3px emerald bar on the left edge. Appears on: sidebar active items, card headers (create forms), section emphasis. This motif ties the UI to the green pitch/field.

## Palette
- **Shell:** slate-950 (sidebar, header)
- **Canvas:** slate-900 (main content background)
- **Card surface:** slate-800/60 with border-white/[0.06]
- **Elevated surface:** slate-700 (dropdowns, hover states)
- **Input surface:** slate-900/80 with border-white/[0.08] — inset feel
- **Primary action:** emerald-500 (solid buttons), emerald-500/10 (ghost containers)
- **Secondary action:** slate-700/60 with border-white/[0.08]
- **Warning/attention:** amber-500/[0.06] bg, amber-500/20 border
- **Success/found:** emerald-500/[0.06] bg, emerald-500/20 border
- **Destructive:** red-400 text on hover, red-500/10 bg on hover
- **Text primary:** text-white
- **Text secondary:** text-slate-400
- **Text tertiary:** text-slate-500
- **Text muted:** text-slate-600

## Depth Strategy
**Borders-only** — rgba white at low opacity. No drop shadows on cards.
- Standard border: border-white/[0.06]
- Input border: border-white/[0.08]
- Soft divider: border-white/[0.04]
- Focus ring: focus:border-emerald-500/40 focus:ring-1 focus:ring-emerald-500/20

## Spacing
Base unit: 4px (Tailwind default). Key patterns:
- Card padding: p-5 or p-6
- Card header: px-5 py-4 or px-6 py-5
- Input padding: px-3.5 py-2.5
- Button padding: px-5 py-2.5 (primary), px-4 py-2.5 (secondary)
- Section gap: space-y-6
- Field gap: space-y-4 within sections

## Border Radius
- Cards/panels: rounded-xl
- Buttons/inputs: rounded-lg
- Icon containers: rounded-lg (square feel) or rounded-md
- Badges: rounded-md
- Pill badges: rounded-full (only for status indicators)

## Typography
- Font: Roboto (system default)
- Section labels: text-[10px] or text-[11px] font-semibold uppercase tracking-wider text-slate-500
- Card titles: text-sm or text-[15px] font-semibold tracking-tight text-white
- Card subtitles: text-[11px] or text-[12px] text-slate-500
- Table headers: text-[10px] font-semibold uppercase tracking-wider text-slate-500
- Table body: text-[13px] text-slate-200 (primary) or text-slate-400 (secondary)
- Button text: text-xs font-medium or font-semibold
- Input text: text-[13px] text-slate-200, placeholder-slate-600

## Component Patterns

### Card (data panel)
```
rounded-xl bg-slate-800/60 border border-white/[0.06] overflow-hidden
```

### Card header
```
px-5 py-4 border-b border-white/[0.06]
```
With icon container: w-8 h-8 rounded-lg bg-{color}-500/10

### Table rows
```
border-b border-white/[0.04] hover:bg-white/[0.02] transition-colors duration-150
```

### Buttons — component classes (resources/css/app.css)
Use the class, never paste the utility string. All include hover, focus-visible ring, active press (scale 0.97) and disabled.
- `btn-primary` — save/create/start. 12px/600 white on emerald-500, px-5 py-2.5, rounded-lg
- `btn-add` — create action in a card header. emerald-500/10 fill + emerald-500/20 border, px-3.5 py-2
- `btn-icon` — row action. 36px visible, 40px hit area via `::before` (-2px); rows use `gap-1` so hit areas never overlap
  - intent modifiers: `btn-icon-success` (emerald), `btn-icon-info` (blue), `btn-icon-warning` (amber), `btn-icon-danger` (red)

### Ghost button (secondary)
```
px-5 py-2.5 rounded-lg text-xs font-medium text-slate-400 hover:text-slate-200 hover:bg-slate-700/40 transition duration-200
```

### Text input (dark context)
```
w-full px-3.5 py-2.5 rounded-lg bg-slate-900/80 border border-white/[0.08] text-[13px] text-slate-200 placeholder-slate-600 focus:outline-none focus:border-emerald-500/40 focus:ring-1 focus:ring-emerald-500/20 transition-colors duration-200
```

### Status panel (success/found)
```
rounded-lg bg-emerald-500/[0.06] border border-emerald-500/20
```

### Status panel (warning/new)
```
rounded-lg bg-amber-500/[0.06] border border-amber-500/20
```

### Empty state
Centered column with: icon container (w-12 h-12 rounded-xl bg-slate-800 border), text, and action link.

### Back navigation
```
inline-flex items-center gap-2 text-xs text-slate-500 hover:text-slate-300 transition-colors duration-150 mb-4
```

### Pitch stripe (signature)
```
<span class="absolute left-0 top-0 bottom-0 w-[3px] bg-emerald-500 rounded-r-full"></span>
```
Parent needs `relative`.

### Back link
`<BackLink :href label />` (Components/BackLink.vue) as the first child of `AdminLayout` on every sub-page. Label = destination name ("Partidos"), not "Regresar".

### Page action bar
`<template #footer>` in `AdminLayout`: primary action + one-line status ("5 vs 5 · Listo para iniciar" / what is missing). Lives outside the scroll area, handles the safe area. Never `position: fixed`, negative margins or bottom padding hacks for this.

### Sheet (overlay)
`<Sheet :show label @close @opened>` (Components/Sheet.vue): bottom sheet < 640px, centered dialog above. Includes Escape, focus trap and focus return. Use it for pickers and action menus; scroll area inside needs `overscroll-y-contain` + `pb-[env(safe-area-inset-bottom)]`. Don't autofocus inputs on coarse pointers (keyboard covers the list).

### Tappable list row
Whole row is the control (`<button>` full width, py-3, 40px avatar, 14px/500 name, 11px meta). Selected = `bg-emerald-500/[0.07]` + filled check. Blocked rows are a `<div>` with the reason as a badge and the fix as a button ("Cobrar").

### Chips / segmented choices
`inline-flex items-center min-h-[40px] px-3.5 rounded-lg text-xs font-medium border`; selected = emerald-500/10 fill + emerald-500/40 border. Use instead of `<select>` when there are ≤ 6 options.

### Live match (Play/Playing.vue)
- Pitch (Play/Pitch.vue): emerald-700/800 stripes, tokens 44px (36px when a side has > 7). Home = white token, away = blue-500. Orientation follows the pitch's own width via container query (≥ 560px horizontal).
- Team colours: `clubs.color` (#rrggbb, nullable) via `Shared/teamColors.js` → `teamColors(c1, c2)` gives `{bg, fg, ring, alternate}` per side. Fallback home white / away blue-500. Text colour by luminance; green or near-black kits get a white ring on the pitch; if both kits are too similar the away side switches to an alternate (white or slate-900) and the team card says "camiseta alterna". Used by pitch tokens, avatars, team stripes and `ClubBadge` (solid kit colour when set).
- Positions: `game_items.pos_x/pos_y` (0–100, relative to the team: x 0 = own goal, y = touchline to touchline; away side mirrored when drawn). `null` = automatic layout. Mouse drags after 4px; touch needs a 250ms long-press (scroll stays free). Players without a position, or overlapping a teammate (< 7 units), show a dashed amber outline + "Ubicar" badge and an amber status line above the pitch. First move of a team saves the whole team's layout. Lineups inherit each player's last position; a substitute takes the outgoing player's spot.
- No confirmation dialogs for reversible match events: apply optimistically, toast with "Deshacer" (6s). Confirm only what can't be undone (start match, finish match, payments).

## SweetAlert2 Dark Theme
```js
background: '#1e293b',
color: '#e2e8f0',
confirmButtonColor: '#10b981', // or '#ef4444' for destructive
cancelButtonColor: '#334155',
```

## Motion
- Curves are set globally in tailwind.config.js: `ease-out`/`DEFAULT` = cubic-bezier(0.23, 1, 0.32, 1), `ease-in-out` = cubic-bezier(0.77, 0, 0.175, 1). Never `ease-in` (exits included).
- Never `transition-all`: use `transition` (colors, opacity, shadow, transform) or `transition-colors`.
- Animate only transform/opacity. No width/height animations (use scaleX/scaleY).
- Durations: 150ms hover/press, 200ms dropdowns, 300ms page entrance and modals/sidebar.
- Entrance stagger 60ms between items.
- No hover scale on non-interactive cards.
- `prefers-reduced-motion`: transforms snap, only color/opacity transition (global rule in app.css).

## Polish
- Root font smoothing antialiased; Roboto loaded from bunny.net (400/500/600/700).
- Headings `text-wrap: balance`, paragraphs `pretty` (global).
- Numbers: tables get tabular-nums globally; scores, counters and totals outside tables need `tabular-nums`.
- Player photos: `img-outline` (1px inset black/10 · white/10), not a tinted border.
- Every `dark:` utility for an interactive state needs its `dark:hover:` pair — a bare `dark:text-*` next to `hover:` overrides the resting state.

## Mobile (most users are on phones)
Design at 390px first. Rules from the `mobile-native` skill:
- Hover only for real pointers: `future.hoverOnlyWhenSupported` in tailwind.config.js. Never hide content behind hover.
- Press feedback lives on `:active` (all `btn-*` classes include it). Global: no tap highlight, `touch-action: manipulation`, controls not long-press selectable.
- App shell: `h-[100dvh]`, never `h-screen`. Scroll containers `overscroll-y-contain`.
- Safe areas: header `pt-[env(safe-area-inset-top)]`, sidebar top+bottom, main bottom. Requires `viewport-fit=cover` (set in app.blade.php).
- Inputs render at 16px on coarse pointers (global rule) — no iOS zoom. Never `maximum-scale`/`user-scalable=no`.
- Keyboard hints: `inputmode="decimal"` amounts, `numeric` cédula/dorsal/points, `type="tel"` phone.

### Data tables on mobile — priority columns
One table for all sizes, no duplicated markup:
- Add `data-table` to `<table>` (compacts cell padding < 640px).
- Secondary columns get `hidden sm:table-cell` (N°, address, gender, category, GF/GC/PE...).
- Merge what was hidden into the primary cell with `sm:hidden` blocks (e.g. rival + state badge under team 1; group · category under match).
- Row actions must be on screen at 390px without horizontal scroll.

## PWA
- `public/manifest.webmanifest`, icons in `public/icons/` (any + maskable), `public/sw.js`, `public/offline.html`.
- Service worker caches ONLY static assets (`/build/assets`, icons, fonts). HTML and Inertia responses always go to network — live scores must never be stale. Full navigations offline → `offline.html`; in-app (Inertia XHR) offline → `OfflineBanner` (amber status panel).
- Bump `VERSION` in sw.js only when the shell (offline.html, icons, manifest) changes; hashed Vite assets clean themselves up.
- `theme-color` follows the class-based theme (pre-paint script in app.blade.php + `applyTheme` in Header.vue): `#020617` dark, `#ffffff` light.

## Empty states and guidance
- `Components/EmptyState.vue` (`icon`, `title`, `description`, `bordered`; actions in the default slot). Every list uses it: say what is missing, what it is for, and give the next step as a `btn-primary`. Never "haz clic aquí".
- Distinguish "nothing yet" from "filter found nothing" (the latter offers "Ver todos").
- When the previous step is missing, send the user there instead of showing an empty form (e.g. Game/Create and Game/Index with fewer than 2 clubs → "Registrar clubes").
- Two valid ways to do something → two choice cards, the recommended one tinted emerald (Game/Index: generar fase de grupos / crear un partido).
- Dashboard setup guide: ordered steps (categoría → grupos → clubes → jugadores → partidos); done = solid emerald check, current = emerald outline + the only `btn-primary`; the card disappears once all are done. Live matches go above it with a red border.
- Dates relative to "today" (Hoy/Mañana, quick chips) are computed in `onMounted`, never during render.

## Guest pages (welcome, login, password, 2FA)
- Always dark: `<html class="dark">` for guests (app.blade.php) and `AuthenticationCard` carries its own `dark` class, so field components render dark regardless of the saved theme.
- `AuthenticationCard` (`title`, `description`, slots `logo`, default, `footer`): slate-950 page with faint centre-circle pitch lines, slate-900 card `max-w-sm`, pitch stripe beside the title, `min-h-[100svh]` + safe areas.
- Brand: emerald "5IV" square + "Many Champs / SPORT MANAGER" (`AuthenticationCardLogo`, same as Welcome header).
- Copy in Spanish, verbs match the action ("Ingresar", "Enviar enlace", "Guardar contraseña"). Primary button full width on login, 48px+ tall.
- `PrimaryButton` = `btn-primary`; `Checkbox` and `InputLabel` follow the emerald/slate tokens.

## Public pages (shared championship, read-only)
- Routes `/c/{slug}` (championship), `/calendario/{fecha?}`, `/categoria/{id}`, `/partido/{id}`. `{championship}` binds by slug and only resolves `is_public` teams (AppServiceProvider); private = 404.
- Private by default. The organizer's "Compartir" (`Components/ShareChampionship.vue`, owner only) publishes on first use after a confirm, then shows the link sheet; "Dejar de compartir" closes the pages, slug is kept.
- Data is a whitelist built in `app/Queries/*` (GameSummaries, MatchSheet, TopScorers, Standings, PublicChampionships). Never expose cédula, phone, birth date, photos, payments, unpaid sanctions or black cards.
- `Layouts/PublicLayout.vue`: always dark (own `dark` class), `max-w-3xl`, championship name + share in the header, tabs Inicio / Calendario, footer "Hecho con Many Champs".
- `Components/Public/GameRow.vue` is the only match row: when/"En vivo"/"Final" on the left, both clubs with kit dot and score, context line below.
- `Play/Pitch.vue` with `readonly`: tokens are not buttons, no drag, no "Ubicar". Event animations still play.
- Live match page polls with `router.reload({ only })` every 15 s while not finished and the tab is visible.
- `Components/ShareButton.vue`: `navigator.share`, falls back to copying the link.
- Link previews: controllers pass an `og` prop (`title`, `description`); app.blade.php prints the meta tags server-side.
- Date/time text helpers live in `Shared/dates.js` (`dayLabel`, `longDay`, `hour`, `todayParts`).
