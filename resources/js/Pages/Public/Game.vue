<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import Pitch from '@/Pages/Play/Pitch.vue';
import { teamColors } from '@/Shared/teamColors';
import { dayLabel, hour, longDay, todayParts } from '@/Shared/dates';
import { Link, router } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    championship: { type: Object, required: true },
    game: { type: Object, required: true },
    home: { type: Array, default: () => [] },
    away: { type: Array, default: () => [] },
});

const today = ref(null)

const live = computed(() => props.game.state === 'jugando')
const finished = computed(() => props.game.state === 'finalizado')
const started = computed(() => live.value || finished.value)
const colors = computed(() => teamColors(props.game.c1color, props.game.c2color))

// Mismo criterio que la pantalla del organizador
const isSubbed = (p) => p.change_player_id !== null
const isExpelled = (p) => p.santion === 'roja'
const onField = (list) => list.filter(p => !isSubbed(p) && !isExpelled(p))

const teams = computed(() => [
    { side: 1, name: props.game.c1name, score: Number(props.game.score1), players: props.home },
    { side: 2, name: props.game.c2name, score: Number(props.game.score2), players: props.away },
])
const hasLineup = computed(() => props.home.length + props.away.length > 0)
const group = computed(() => {
    const name = props.game.group_name
    return name && !/grupo/i.test(name) ? `Grupo ${name}` : name
})
const context = computed(() => [props.game.category, props.game.phase, group.value].filter(Boolean).join(', '))
const when = computed(() => {
    const relative = dayLabel(props.game.date, today.value)
    const day = ['Hoy', 'Mañana', 'Ayer'].includes(relative) ? relative : longDay(props.game.date)
    return `${day}, ${hour(props.game.time)}`
})
const shareText = computed(() => started.value
    ? `${props.game.c1name} ${props.game.score1} - ${props.game.score2} ${props.game.c2name}${live.value ? ' (en vivo)' : ''}`
    : `${props.game.c1name} vs ${props.game.c2name}`)

const fullName = (p) => `${p.first_name} ${p.last_name}`
const status = (p) => isExpelled(p) ? 'Expulsado' : isSubbed(p) ? 'Salió' : ''

// Mientras no termine, se vuelve a pedir el partido cada 15 s (solo con la pestaña a la vista)
const REFRESH_MS = 15000
let timer = null
const refresh = () => {
    if (finished.value || document.visibilityState !== 'visible') return
    router.reload({ only: ['game', 'home', 'away'] })
}
onMounted(() => {
    today.value = todayParts()
    timer = setInterval(refresh, REFRESH_MS)
    document.addEventListener('visibilitychange', refresh)
})
onUnmounted(() => {
    clearInterval(timer)
    document.removeEventListener('visibilitychange', refresh)
})
</script>

<template>
    <PublicLayout :title="`${game.c1name} vs ${game.c2name}`" :championship="championship" :share-text="shareText">

        <Link :href="route('public.category', [championship.slug, game.category_id])"
            class="inline-flex items-center gap-2 min-h-[40px] text-[13px] font-medium text-slate-400 hover:text-slate-200 rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40">
            <i class="fa-solid fa-arrow-left text-[11px]" aria-hidden="true"></i> {{ game.category }}
        </Link>

        <!-- Marcador -->
        <section class="rounded-xl bg-slate-900 border overflow-hidden px-4 sm:px-6 py-5" :class="live ? 'border-red-500/30' : 'border-white/[0.06]'" aria-label="Marcador">
            <p class="flex justify-center">
                <span v-if="live" class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-red-500/10 text-[11px] font-semibold text-red-400">
                    <span class="relative flex w-1.5 h-1.5">
                        <span class="absolute inset-0 rounded-full bg-red-500 animate-ping opacity-75 motion-reduce:hidden"></span>
                        <span class="relative w-1.5 h-1.5 rounded-full bg-red-500"></span>
                    </span>
                    En vivo
                </span>
                <span v-else-if="finished" class="px-2 py-0.5 rounded-md bg-emerald-500/10 text-[11px] font-semibold text-emerald-400">Finalizado</span>
                <span v-else class="text-[12px] font-medium text-slate-400 first-letter:uppercase">{{ when }}</span>
            </p>

            <div class="mt-3 grid grid-cols-[1fr_auto_1fr] items-center gap-3">
                <p class="flex flex-col items-center gap-2 min-w-0 text-center">
                    <span class="w-4 h-4 rounded-full ring-1 ring-inset ring-white/25" :style="{ backgroundColor: colors[1].bg }" aria-hidden="true"></span>
                    <span class="max-w-full text-sm sm:text-base font-semibold text-white break-words">{{ game.c1name }}</span>
                </p>
                <p v-if="started" class="flex items-center gap-2.5 text-4xl sm:text-5xl font-bold tabular-nums text-white" aria-live="polite" aria-atomic="true">
                    <span class="sr-only">{{ game.c1name }}</span>{{ game.score1 }}
                    <span class="text-2xl text-slate-600" aria-hidden="true">–</span>
                    {{ game.score2 }}<span class="sr-only">{{ game.c2name }}</span>
                </p>
                <p v-else class="text-sm font-bold tracking-widest text-slate-600">VS</p>
                <p class="flex flex-col items-center gap-2 min-w-0 text-center">
                    <span class="w-4 h-4 rounded-full ring-1 ring-inset ring-white/25" :style="{ backgroundColor: colors[2].bg }" aria-hidden="true"></span>
                    <span class="max-w-full text-sm sm:text-base font-semibold text-white break-words">{{ game.c2name }}</span>
                </p>
            </div>

            <p class="mt-3 text-center text-[12px] text-slate-500">{{ started ? `${context}, ${when}` : context }}</p>
        </section>

        <!-- Cancha: solo para mirar -->
        <template v-if="hasLineup">
            <Pitch readonly :home="onField(home)" :away="onField(away)" :colors="colors" />
            <p v-if="live" class="-mt-2 text-center text-[12px] text-slate-500">Se actualiza solo cada pocos segundos.</p>

            <!-- Planteles -->
            <div class="grid sm:grid-cols-2 gap-4">
                <section v-for="team in teams" :key="team.side" class="rounded-xl bg-slate-900 border border-white/[0.06] overflow-hidden" :aria-label="`Jugadores de ${team.name}`">
                    <h2 class="flex items-center gap-2 px-4 py-3 border-b border-white/[0.06] text-[13px] font-semibold text-white">
                        <span class="w-2.5 h-2.5 shrink-0 rounded-full ring-1 ring-inset ring-white/25" :style="{ backgroundColor: colors[team.side].bg }" aria-hidden="true"></span>
                        <span class="truncate">{{ team.name }}</span>
                    </h2>
                    <ul class="divide-y divide-white/[0.04]">
                        <li v-for="p in team.players" :key="p.gi_id" class="flex items-center gap-3 px-4 py-2.5" :class="status(p) ? 'opacity-60' : ''">
                            <span class="w-7 shrink-0 text-center text-[13px] font-semibold tabular-nums text-slate-400">{{ p.t_shirt ?? '–' }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="truncate text-sm text-slate-100">{{ fullName(p) }}</p>
                                <p v-if="status(p)" class="text-[11px] text-slate-400">{{ status(p) }}</p>
                            </div>
                            <span v-if="p.santion" class="shrink-0 w-3 h-4 rounded-[2px]" :class="p.santion === 'roja' ? 'bg-red-500' : 'bg-amber-400'"
                                role="img" :aria-label="`Tarjeta ${p.santion}`"></span>
                            <span v-if="p.goals > 0" class="shrink-0 inline-flex items-center gap-1 text-[13px] font-semibold tabular-nums text-white">
                                <i class="fa-solid fa-futbol text-[10px] text-slate-400" aria-hidden="true"></i>{{ p.goals }}
                                <span class="sr-only">{{ p.goals === 1 ? 'gol' : 'goles' }}</span>
                            </span>
                        </li>
                        <li v-if="!team.players.length" class="px-4 py-4 text-[13px] text-slate-500">Sin alineación todavía.</li>
                    </ul>
                </section>
            </div>
        </template>

        <!-- Todavía no empieza -->
        <section v-else class="rounded-xl border border-dashed border-white/[0.1] px-6 py-10 text-center">
            <p class="text-sm font-semibold text-slate-200">{{ finished ? 'Este partido no tiene alineación registrada' : 'El partido aún no empieza' }}</p>
            <p v-if="!finished" class="mt-1 text-[13px] text-slate-400">Cuando arranque verás aquí la cancha, los goles y las tarjetas. Esta página se actualiza sola.</p>
        </section>
    </PublicLayout>
</template>
