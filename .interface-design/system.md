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

### Primary button
```
inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-emerald-500 text-xs font-semibold text-white hover:bg-emerald-400 disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-200
```

### Ghost button (secondary)
```
px-5 py-2.5 rounded-lg text-xs font-medium text-slate-400 hover:text-slate-200 hover:bg-slate-700/40 transition-all duration-200
```

### Add/create button (card header)
```
inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-emerald-500/10 text-emerald-400 text-xs font-medium border border-emerald-500/20 hover:bg-emerald-500/20 hover:border-emerald-500/30 transition-all duration-200
```

### Icon action button
```
w-8 h-8 rounded-lg flex items-center justify-center text-slate-500 hover:text-slate-200 hover:bg-slate-700/50 transition-colors duration-150
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

## SweetAlert2 Dark Theme
```js
background: '#1e293b',
color: '#e2e8f0',
confirmButtonColor: '#10b981', // or '#ef4444' for destructive
cancelButtonColor: '#334155',
```

## Transitions
- Duration: 150ms (hover states), 200ms (general transitions), 300ms (sidebar/overlay)
- Easing: ease-in-out (sidebar), ease-out (dropdown enter), ease-in (dropdown leave)
