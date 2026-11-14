<script setup>
import { computed, onUnmounted, reactive, ref, watch } from 'vue';
import { teamColors } from '@/Shared/teamColors';

const props = defineProps({
    // Solo los jugadores que están en cancha
    home: { type: Array, default: () => [] },
    away: { type: Array, default: () => [] },
    selectedId: { type: Number, default: 0 },
    // { 1: { bg, fg, ring }, 2: {...} } desde Shared/teamColors.js
    colors: { type: Object, default: () => teamColors() },
    // Página pública: se mira, no se toca (sin arrastre, sin acciones, sin avisos de ubicar)
    readonly: { type: Boolean, default: false },
});

// select: tocar abre acciones · move: el jugador se movió ({ player, side, x, y, team })
const emit = defineEmits(['select', 'move'])

// ───── Posiciones ─────
// Coordenadas relativas al equipo: x 0 = su arco → 100 = arco rival, y 0-100 de banda a banda.
// Para el visitante se invierten ambas al dibujar (ataca hacia el otro lado).

// Filas desde el arco propio hacia el medio. No es táctica: solo reparte las fichas
const formations = {
    1: [1], 2: [1, 1], 3: [1, 2], 4: [1, 2, 1], 5: [1, 2, 2], 6: [1, 2, 2, 1],
    7: [1, 3, 3], 8: [1, 3, 3, 1], 9: [1, 3, 3, 2], 10: [1, 4, 3, 2], 11: [1, 4, 4, 2],
}
const rowsFor = (n) => {
    if (formations[n]) return formations[n]
    const rows = [1]
    for (let left = n - 1; left > 0; left -= 4) rows.push(Math.min(4, left))
    return rows
}
const autoLayout = (players) => {
    const rows = rowsFor(players.length)
    const out = []
    rows.forEach((count, r) => {
        const x = rows.length === 1 ? 25 : 8 + r * (36 / (rows.length - 1))
        for (let i = 0; i < count; i++) out.push({ x, y: (i + 1) / (count + 1) * 100 })
    })
    return out
}

// Dos fichas más cerca que esto se consideran encimadas
const GAP = 7
const dist = (a, b) => Math.hypot(a.x - b.x, a.y - b.y)
// Huecos candidatos en la mitad propia para jugadores sin posición
const grid = [8, 16, 24, 32, 40].flatMap(x => [15, 32.5, 50, 67.5, 85].map(y => ({ x, y })))
const freeSpot = (taken) => grid.reduce((best, c) => {
    const d = taken.length ? Math.min(...taken.map(t => dist(t, c))) : Infinity
    return d > best.d ? { ...c, d } : best
}, { x: 25, y: 50, d: -1 })

const hasPos = (p) => p.pos_x !== null && p.pos_x !== undefined && p.pos_y !== null && p.pos_y !== undefined

const placeTeam = (players, side) => {
    let spots
    if (!players.some(hasPos)) {
        // Sin formación guardada: reparto automático, nada que ubicar
        const auto = autoLayout(players)
        spots = players.map((player, i) => ({ player, ...auto[i], unplaced: false }))
    } else {
        // Con formación: los que tienen posición van a su lugar; los nuevos o encimados, al hueco más libre
        const taken = []
        const byId = new Map()
        const pending = []
        players.forEach(p => {
            const spot = { x: p.pos_x, y: p.pos_y }
            if (hasPos(p) && !taken.some(t => dist(t, spot) < GAP)) {
                taken.push(spot)
                byId.set(p.gi_id, { ...spot, unplaced: false })
            } else {
                pending.push(p)
            }
        })
        pending.forEach(p => {
            const spot = freeSpot(taken)
            taken.push(spot)
            byId.set(p.gi_id, { x: spot.x, y: spot.y, unplaced: true })
        })
        spots = players.map(player => ({ player, ...byId.get(player.gi_id) }))
    }
    return spots.map(s => ({
        ...s, side,
        along: side === 1 ? s.x : 100 - s.x,
        across: side === 1 ? s.y : 100 - s.y,
    }))
}

const tokens = computed(() => [...placeTeam(props.home, 1), ...placeTeam(props.away, 2)])
const unplacedCount = computed(() => tokens.value.filter(t => t.unplaced).length)

const round = (n) => Math.round(n * 10) / 10
const clamp = (n) => Math.min(96, Math.max(4, n))
const teamSnapshot = (side) => tokens.value.filter(t => t.side === side).map(t => ({ gi_id: t.player.gi_id, x: round(t.x), y: round(t.y) }))

// Pantalla (along/across) → coordenadas del equipo
const toTeam = (side, along, across) => side === 1
    ? { x: round(along), y: round(across) }
    : { x: round(100 - along), y: round(100 - across) }

const emitMove = (t, along, across, source) => {
    const { x, y } = toTeam(t.side, clamp(along), clamp(across))
    if (Math.abs(x - t.x) < 0.5 && Math.abs(y - t.y) < 0.5 && !t.unplaced) return
    emit('move', { player: t.player, side: t.side, x, y, team: teamSnapshot(t.side), source })
}

// ───── Arrastre ─────
// Mouse: arrastrar > 4px. Táctil: mantener presionado 250ms y arrastrar (sin bloquear el scroll de la página)
const pitch = ref(null)
const LONG_PRESS = 250
const drag = reactive({ id: null, active: false, dx: 0, dy: 0 })
let session = null
// El clic que llega justo después de soltar un arrastre se ignora
let suppressClickUntil = 0
const suppressClick = () => { suppressClickUntil = performance.now() + 400 }

const isWide = () => pitch.value.getBoundingClientRect().width >= 560

const onPointerDown = (e, t) => {
    if (props.readonly || e.button !== 0 || session) return
    const circle = e.currentTarget.querySelector('[data-circle]').getBoundingClientRect()
    session = {
        t,
        pointerId: e.pointerId,
        touch: e.pointerType !== 'mouse',
        startX: e.clientX,
        startY: e.clientY,
        // Distancia del dedo al centro de la ficha: la ficha no "salta" bajo el dedo
        offsetX: e.clientX - (circle.left + circle.width / 2),
        offsetY: e.clientY - (circle.top + circle.height / 2),
        timer: null,
    }
    drag.id = t.player.gi_id
    drag.active = false
    drag.dx = drag.dy = 0
    if (session.touch) {
        session.timer = setTimeout(activate, LONG_PRESS)
    }
    window.addEventListener('pointermove', onPointerMove)
    window.addEventListener('pointerup', onPointerUp)
    window.addEventListener('pointercancel', cancel)
    window.addEventListener('keydown', onDragKey)
    document.addEventListener('touchmove', blockScroll, { passive: false })
}

const activate = () => {
    if (!session) return
    drag.active = true
    navigator.vibrate?.(10)
}

const onPointerMove = (e) => {
    if (!session || e.pointerId !== session.pointerId) return
    const dx = e.clientX - session.startX
    const dy = e.clientY - session.startY
    if (!drag.active) {
        const moved = Math.hypot(dx, dy)
        // Táctil: si se mueve antes de la presión larga, era scroll
        if (session.touch && moved > 8) return cancel()
        if (!session.touch && moved > 4) activate()
        if (!drag.active) return
    }
    // La ficha no sale de la cancha
    const rect = pitch.value.getBoundingClientRect()
    const cx = Math.min(rect.right - rect.width * 0.04, Math.max(rect.left + rect.width * 0.04, e.clientX - session.offsetX))
    const cy = Math.min(rect.bottom - rect.height * 0.04, Math.max(rect.top + rect.height * 0.04, e.clientY - session.offsetY))
    drag.dx = cx - (session.startX - session.offsetX)
    drag.dy = cy - (session.startY - session.offsetY)
    session.cx = cx
    session.cy = cy
}

const onPointerUp = (e) => {
    if (!session || e.pointerId !== session.pointerId) return
    if (drag.active) {
        suppressClick()
        if (session.cx !== undefined) {
            const rect = pitch.value.getBoundingClientRect()
            const px = (session.cx - rect.left) / rect.width * 100
            const py = (session.cy - rect.top) / rect.height * 100
            const wide = isWide()
            emitMove(session.t, wide ? px : py, wide ? py : px, 'drag')
        }
    }
    end()
}

// Durante el arrastre táctil la página no se desplaza
const blockScroll = (e) => {
    if (drag.active) e.preventDefault()
}

const onDragKey = (e) => {
    if (e.key === 'Escape' && drag.active) {
        suppressClick()
        cancel()
    }
}

const cancel = () => end()

const end = () => {
    clearTimeout(session?.timer)
    session = null
    drag.id = null
    drag.active = false
    drag.dx = drag.dy = 0
    window.removeEventListener('pointermove', onPointerMove)
    window.removeEventListener('pointerup', onPointerUp)
    window.removeEventListener('pointercancel', cancel)
    window.removeEventListener('keydown', onDragKey)
    document.removeEventListener('touchmove', blockScroll)
}

onUnmounted(end)

// ───── Animaciones de lo que pasa en cancha ─────
// Se compara cada ficha con su estado anterior: gol, tarjeta, entra o sale.
// Al cargar la página no se anima nada; solo los cambios posteriores.
const EVENT_MS = 1600
const events = ref([])      // marcas sobre la cancha: { id, kind, along, across }
const flash = reactive({})  // gi_id → 'goal' | 'card': animación de la ficha misma
let snapshot = null
// Fichas que ya estuvieron en cancha: si vuelven (roja retirada) no es un cambio
const seen = new Set()
let eventSeq = 0
const timers = new Set()

const later = (fn, ms) => {
    const id = setTimeout(() => { timers.delete(id); fn() }, ms)
    timers.add(id)
}

const addEvent = (kind, spot) => {
    const event = { id: ++eventSeq, kind, along: spot.along, across: spot.across }
    events.value = [...events.value, event]
    later(() => { events.value = events.value.filter(e => e.id !== event.id) }, EVENT_MS)
}

const flashToken = (giId, kind) => {
    flash[giId] = kind
    later(() => { if (flash[giId] === kind) delete flash[giId] }, 700)
}

const takeSnapshot = (list) => new Map(list.map(t => [t.player.gi_id, {
    goals: Number(t.player.goals ?? 0),
    santion: t.player.santion ?? null,
    black: !!t.player.card_black,
    along: t.along,
    across: t.across,
    player: t.player,
}]))

watch(tokens, (list) => {
    const next = takeSnapshot(list)
    if (snapshot) {
        next.forEach((now, giId) => {
            const before = snapshot.get(giId)
            if (!before) {
                // Entró al partido (cambio o agregado)
                if (!seen.has(giId)) addEvent('in', now)
                return
            }
            if (now.goals > before.goals) {
                addEvent('goal', now)
                flashToken(giId, 'goal')
            }
            if (now.santion === 'amarilla' && before.santion !== 'amarilla') {
                addEvent('yellow', now)
                flashToken(giId, 'card')
            }
            if (now.black && !before.black) {
                addEvent('black', now)
                flashToken(giId, 'card')
            }
        })
        snapshot.forEach((before, giId) => {
            if (next.has(giId)) return
            // Salió de la cancha: roja (incluye segunda amarilla) o cambio
            addEvent(before.player.santion === 'roja' ? 'red' : 'out', before)
        })
    }
    snapshot = next
    next.forEach((_, giId) => seen.add(giId))
}, { immediate: true })

onUnmounted(() => timers.forEach(clearTimeout))

const onClick = (t) => {
    // El clic que cierra un arrastre no abre las acciones
    if (props.readonly || performance.now() < suppressClickUntil) return
    emit('select', t.player, t.side)
}

// Teclado: flechas mueven la ficha enfocada (Shift = paso largo)
const onKeydown = (e, t) => {
    const keys = { ArrowLeft: [-1, 0], ArrowRight: [1, 0], ArrowUp: [0, -1], ArrowDown: [0, 1] }
    if (props.readonly || !keys[e.key]) return
    e.preventDefault()
    const step = e.shiftKey ? 6 : 2
    const [sx, sy] = keys[e.key]
    // Pantalla → along/across según orientación
    const wide = isWide()
    const along = t.along + (wide ? sx : sy) * step
    const across = t.across + (wide ? sy : sx) * step
    emitMove(t, along, across, 'keyboard')
}

const tokenStyle = (t) => {
    const style = { '--along': `${t.along}%`, '--across': `${t.across}%` }
    if (drag.id === t.player.gi_id && drag.active) {
        style.transform = `translate(calc(-50% + ${drag.dx}px), calc(-50% + ${drag.dy}px))`
    }
    return style
}

// Planteles grandes (fútbol 11): fichas más chicas y cancha más larga
const dense = computed(() => Math.max(props.home.length, props.away.length) > 7)

const label = (p) => p.first_name?.split(' ')[0] ?? ''
const initials = (p) => `${p.first_name?.[0] ?? ''}${p.last_name?.[0] ?? ''}`.toUpperCase()
const card = (p) => p.card_black ? 'bg-slate-950 ring-1 ring-white/70' : p.santion === 'roja' ? 'bg-red-500' : p.santion === 'amarilla' ? 'bg-amber-400' : ''
const describe = (t) => {
    const p = t.player
    const parts = [`${p.first_name} ${p.last_name}`]
    if (p.t_shirt) parts.push(`dorsal ${p.t_shirt}`)
    parts.push(`${p.goals} ${p.goals === 1 ? 'gol' : 'goles'}`)
    if (p.santion) parts.push(`tarjeta ${p.santion}`)
    if (p.card_black) parts.push('tarjeta negra')
    if (t.unplaced && !props.readonly) parts.push('sin posición')
    return parts.join(', ')
}
</script>

<template>
    <div class="pitch-box">
        <!-- Jugadores nuevos o encimados: pedir que se ubiquen -->
        <p v-if="unplacedCount && !readonly" role="status"
            class="mb-2 flex items-center gap-2 rounded-lg bg-amber-500/[0.06] border border-amber-500/20 px-3 py-2 text-[12px] text-amber-700 dark:text-amber-300">
            <i class="fa-solid fa-up-down-left-right"></i>
            <span>
                <span class="font-semibold tabular-nums">{{ unplacedCount }}</span>
                {{ unplacedCount === 1 ? 'jugador sin posición' : 'jugadores sin posición' }} ·
                <span class="[@media(pointer:coarse)]:hidden">arrástralo a su lugar</span>
                <span class="hidden [@media(pointer:coarse)]:inline">mantén presionado y arrástralo a su lugar</span>
            </span>
        </p>
        <div ref="pitch" class="pitch relative w-full overflow-hidden rounded-xl border border-emerald-950/40" :class="{ 'pitch-dense': dense }">

            <!-- Líneas del campo: una versión por orientación -->
            <svg viewBox="0 0 160 100" preserveAspectRatio="none" class="pitch-lines-wide absolute inset-0 w-full h-full" aria-hidden="true">
                <g fill="none" stroke="rgba(255,255,255,0.45)" stroke-width="0.6">
                    <rect x="3" y="3" width="154" height="94" rx="1" />
                    <line x1="80" y1="3" x2="80" y2="97" />
                    <circle cx="80" cy="50" r="12" />
                    <path d="M3 25 Q 26 25 26 50 Q 26 75 3 75" />
                    <path d="M157 25 Q 134 25 134 50 Q 134 75 157 75" />
                </g>
                <g fill="rgba(255,255,255,0.5)">
                    <circle cx="80" cy="50" r="0.9" />
                    <circle cx="20" cy="50" r="0.7" />
                    <circle cx="140" cy="50" r="0.7" />
                </g>
            </svg>
            <svg viewBox="0 0 100 130" preserveAspectRatio="none" class="pitch-lines-tall absolute inset-0 w-full h-full" aria-hidden="true">
                <g fill="none" stroke="rgba(255,255,255,0.45)" stroke-width="0.6">
                    <rect x="3" y="3" width="94" height="124" rx="1" />
                    <line x1="3" y1="65" x2="97" y2="65" />
                    <circle cx="50" cy="65" r="12" />
                    <path d="M25 3 Q 25 22 50 22 Q 75 22 75 3" />
                    <path d="M25 127 Q 25 108 50 108 Q 75 108 75 127" />
                </g>
                <g fill="rgba(255,255,255,0.5)">
                    <circle cx="50" cy="65" r="0.9" />
                    <circle cx="50" cy="16" r="0.7" />
                    <circle cx="50" cy="114" r="0.7" />
                </g>
            </svg>

            <!-- Fichas: entran y salen con transición (cambios y expulsiones) -->
            <TransitionGroup enter-active-class="transition duration-500 ease-out" enter-from-class="opacity-0 scale-75"
                leave-active-class="transition duration-500 ease-out delay-300 pointer-events-none" leave-to-class="opacity-0 scale-75">
            <component :is="readonly ? 'div' : 'button'" v-for="t in tokens" :key="t.player.gi_id" :type="readonly ? undefined : 'button'" :style="tokenStyle(t)"
                @pointerdown="onPointerDown($event, t)" @click="onClick(t)" @keydown="onKeydown($event, t)" @contextmenu.prevent
                :aria-label="describe(t)" :role="readonly ? 'img' : undefined" :aria-roledescription="readonly ? undefined : 'ficha movible'"
                class="pitch-token absolute -translate-x-1/2 -translate-y-1/2 min-w-[44px] flex flex-col items-center focus:outline-none group"
                :class="readonly ? 'z-10' : drag.id === t.player.gi_id && drag.active ? 'z-20 cursor-grabbing' : 'z-10 cursor-pointer [@media(pointer:fine)]:cursor-grab'">
                <span data-circle class="relative rounded-full flex items-center justify-center font-bold tabular-nums ring-2 transition duration-150
                             group-focus-visible:ring-4 group-focus-visible:ring-white"
                    :style="{ backgroundColor: colors[t.side].bg, color: colors[t.side].fg, '--tw-ring-color': selectedId === t.player.id ? '#ffffff' : colors[t.side].ring }"
                    :class="[
                        dense ? 'w-9 h-9 text-[12px]' : 'w-11 h-11 sm:w-12 sm:h-12 text-[13px] sm:text-sm',
                        selectedId === t.player.id ? 'ring-4' : '',
                        drag.id === t.player.gi_id && drag.active ? 'scale-110 shadow-xl' : readonly ? 'shadow-md' : 'shadow-md group-active:scale-95',
                        t.unplaced && !readonly ? 'outline outline-2 outline-dashed outline-offset-[3px] outline-amber-300' : '',
                        flash[t.player.gi_id] === 'goal' ? 'fx-pop' : '',
                        flash[t.player.gi_id] === 'card' ? 'fx-shake' : '',
                    ]">
                    {{ t.player.t_shirt ?? initials(t.player) }}

                    <!-- Tarjeta -->
                    <span v-if="card(t.player)" class="absolute -top-1 -left-1 w-2.5 h-3.5 rounded-[2px] shadow" :class="card(t.player)"></span>

                    <!-- Goles -->
                    <span v-if="t.player.goals > 0"
                        class="absolute -top-1.5 -right-2 min-w-[20px] h-5 px-1 rounded-full bg-slate-950 text-white text-[10px] font-bold flex items-center justify-center gap-0.5 ring-2 ring-white/90">
                        <i class="fa-solid fa-futbol text-[8px]"></i>{{ t.player.goals }}
                    </span>
                </span>
                <span class="mt-1 truncate px-1.5 py-px rounded bg-slate-950/60 font-medium text-white leading-tight"
                    :class="dense ? 'max-w-[56px] text-[10px]' : 'max-w-[72px] text-[11px]'">
                    {{ label(t.player) }}
                </span>
                <span v-if="t.unplaced && !readonly" class="mt-0.5 px-1.5 rounded bg-amber-400 text-[9px] font-bold uppercase tracking-wider text-amber-950 leading-tight">Ubicar</span>
            </component>
            </TransitionGroup>

            <!-- Marcas de eventos sobre la ficha (no bloquean los toques) -->
            <div class="absolute inset-0 pointer-events-none z-30" aria-hidden="true">
                <div v-for="e in events" :key="e.id" class="pitch-token absolute"
                    :style="{ '--along': `${e.along}%`, '--across': `${e.across}%` }">
                    <!-- Gol: onda + etiqueta que sube -->
                    <template v-if="e.kind === 'goal'">
                        <span class="fx-burst absolute -left-8 -top-8 w-16 h-16 rounded-full border-[3px] border-white"></span>
                        <span class="fx-burst fx-burst-late absolute -left-8 -top-8 w-16 h-16 rounded-full border-2 border-white/70"></span>
                        <span class="fx-rise absolute left-0 -top-7 whitespace-nowrap px-2 py-0.5 rounded-full bg-white text-slate-900 text-[12px] font-extrabold shadow-lg">
                            <i class="fa-solid fa-futbol mr-1"></i>¡GOL!
                        </span>
                    </template>

                    <!-- Tarjeta: el árbitro la levanta sobre el jugador -->
                    <span v-else-if="['yellow', 'red', 'black'].includes(e.kind)"
                        class="fx-card absolute -left-[9px] -top-10 w-[18px] h-[26px] rounded-[3px] shadow-xl"
                        :class="{ yellow: 'bg-amber-400', red: 'bg-red-500', black: 'bg-slate-950 ring-1 ring-white/70' }[e.kind]"></span>

                    <!-- Cambio: entra / sale -->
                    <span v-else class="fx-rise absolute left-0 -top-7 whitespace-nowrap inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold text-white shadow-lg"
                        :class="e.kind === 'in' ? 'bg-emerald-600' : 'bg-slate-700'">
                        <i class="fa-solid text-[9px]" :class="e.kind === 'in' ? 'fa-arrow-up' : 'fa-arrow-down'"></i>
                        {{ e.kind === 'in' ? 'Entra' : 'Sale' }}
                    </span>
                </div>
            </div>

            <slot v-if="!tokens.length" name="empty">
                <p class="absolute inset-0 flex items-center justify-center text-sm text-white/80">No hay jugadores en cancha</p>
            </slot>
        </div>
    </div>
</template>

<style scoped>
/* ───── Animaciones de eventos (solo transform y opacity) ───── */
.fx-pop {
    animation: fx-pop 600ms cubic-bezier(0.23, 1, 0.32, 1);
}

@keyframes fx-pop {
    0% { transform: scale(1); }
    35% { transform: scale(1.25); }
    100% { transform: scale(1); }
}

.fx-shake {
    animation: fx-shake 450ms cubic-bezier(0.23, 1, 0.32, 1);
}

@keyframes fx-shake {
    0%, 100% { transform: translateX(0); }
    20% { transform: translateX(-4px); }
    40% { transform: translateX(4px); }
    60% { transform: translateX(-3px); }
    80% { transform: translateX(2px); }
}

.fx-burst {
    opacity: 0;
    animation: fx-burst 900ms cubic-bezier(0.23, 1, 0.32, 1);
}

.fx-burst-late {
    animation-delay: 150ms;
}

@keyframes fx-burst {
    0% { transform: scale(0.6); opacity: 0.9; }
    100% { transform: scale(2.2); opacity: 0; }
}

.fx-rise {
    opacity: 0;
    translate: -50% 0;
    animation: fx-rise 1500ms cubic-bezier(0.23, 1, 0.32, 1);
}

@keyframes fx-rise {
    0% { transform: translateY(8px) scale(0.9); opacity: 0; }
    15% { transform: translateY(0) scale(1); opacity: 1; }
    70% { transform: translateY(-6px); opacity: 1; }
    100% { transform: translateY(-14px); opacity: 0; }
}

.fx-card {
    opacity: 0;
    transform-origin: 50% 100%;
    animation: fx-card 1500ms cubic-bezier(0.23, 1, 0.32, 1);
}

@keyframes fx-card {
    0% { transform: translateY(14px) rotate(-14deg) scale(0.8); opacity: 0; }
    18% { transform: translateY(0) rotate(-6deg) scale(1.08); opacity: 1; }
    30% { transform: translateY(0) rotate(-8deg) scale(1); opacity: 1; }
    80% { transform: translateY(-2px) rotate(-8deg) scale(1); opacity: 1; }
    100% { transform: translateY(-8px) rotate(-8deg) scale(0.95); opacity: 0; }
}

/* Movimiento reducido: solo aparece y desaparece, sin desplazamientos */
@media (prefers-reduced-motion: reduce) {
    .fx-burst { display: none; }
    .fx-rise, .fx-card { animation: fx-fade 1500ms linear !important; transform: none; }
    .fx-pop, .fx-shake { animation: none !important; }
}

@keyframes fx-fade {
    0%, 80% { opacity: 1; }
    100% { opacity: 0; }
}

/* La orientación depende del ancho real de la cancha, no del viewport */
.pitch-box {
    container-type: inline-size;
}

/* Vertical (celular): franjas de césped a lo ancho */
.pitch {
    aspect-ratio: 10 / 13;
    background: repeating-linear-gradient(180deg, #047857 0 10%, #065f46 10% 20%);
}

.pitch-dense {
    aspect-ratio: 10 / 15;
}

.pitch-token {
    left: var(--across);
    top: var(--along);
}

.pitch-lines-wide {
    display: none;
}

/* Horizontal: cuando hay ancho suficiente para ver los dos equipos lado a lado */
@container (min-width: 560px) {
    .pitch,
    .pitch-dense {
        aspect-ratio: 16 / 10;
        background: repeating-linear-gradient(90deg, #047857 0 10%, #065f46 10% 20%);
    }

    .pitch-token {
        left: var(--along);
        top: var(--across);
    }

    .pitch-lines-wide {
        display: block;
    }

    .pitch-lines-tall {
        display: none;
    }
}
</style>
