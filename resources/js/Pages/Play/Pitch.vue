<script setup>
import { computed } from 'vue';

const props = defineProps({
    // Solo los jugadores que están en cancha
    home: { type: Array, default: () => [] },
    away: { type: Array, default: () => [] },
    selectedId: { type: Number, default: 0 },
});

const emit = defineEmits(['select'])

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

// Posición en % del campo: --along = eje arco a arco, --across = eje de banda a banda.
// El CSS decide qué eje es horizontal según el ancho disponible (container query)
const layout = (players, side) => {
    const rows = rowsFor(players.length)
    const out = []
    let index = 0
    rows.forEach((count, r) => {
        const depth = rows.length === 1 ? 25 : 8 + r * (36 / (rows.length - 1))
        for (let i = 0; i < count; i++) {
            out.push({
                player: players[index++], side,
                style: { '--along': `${side === 1 ? depth : 100 - depth}%`, '--across': `${(i + 1) / (count + 1) * 100}%` },
            })
        }
    })
    return out
}

const tokens = computed(() => [...layout(props.home, 1), ...layout(props.away, 2)])

// Planteles grandes (fútbol 11): fichas más chicas y cancha más larga
const dense = computed(() => Math.max(props.home.length, props.away.length) > 7)

const label = (p) => p.first_name?.split(' ')[0] ?? ''
const initials = (p) => `${p.first_name?.[0] ?? ''}${p.last_name?.[0] ?? ''}`.toUpperCase()
const card = (p) => p.card_black ? 'bg-slate-950 ring-1 ring-white/70' : p.santion === 'roja' ? 'bg-red-500' : p.santion === 'amarilla' ? 'bg-amber-400' : ''
const describe = (p) => {
    const parts = [`${p.first_name} ${p.last_name}`]
    if (p.t_shirt) parts.push(`dorsal ${p.t_shirt}`)
    parts.push(`${p.goals} ${p.goals === 1 ? 'gol' : 'goles'}`)
    if (p.santion) parts.push(`tarjeta ${p.santion}`)
    if (p.card_black) parts.push('tarjeta negra')
    return parts.join(', ')
}
</script>

<template>
    <div class="pitch-box">
        <div class="pitch relative w-full overflow-hidden rounded-xl border border-emerald-950/40" :class="{ 'pitch-dense': dense }">

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

            <!-- Fichas -->
            <button v-for="t in tokens" :key="t.player.gi_id" type="button" :style="t.style"
                @click="emit('select', t.player, t.side)" :aria-label="describe(t.player)"
                class="pitch-token absolute -translate-x-1/2 -translate-y-1/2 min-w-[44px] flex flex-col items-center focus:outline-none group">
                <span class="relative rounded-full flex items-center justify-center font-bold tabular-nums shadow-md ring-2 transition duration-150
                             group-active:scale-95 group-focus-visible:ring-4 group-focus-visible:ring-white"
                    :class="[
                        dense ? 'w-9 h-9 text-[12px]' : 'w-11 h-11 sm:w-12 sm:h-12 text-[13px] sm:text-sm',
                        t.side === 1 ? 'bg-white text-emerald-800 ring-emerald-950/30' : 'bg-blue-500 text-white ring-blue-950/30',
                        selectedId === t.player.id ? 'ring-4 !ring-white' : '',
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
            </button>

            <slot v-if="!tokens.length" name="empty">
                <p class="absolute inset-0 flex items-center justify-center text-sm text-white/80">No hay jugadores en cancha</p>
            </slot>
        </div>
    </div>
</template>

<style scoped>
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
