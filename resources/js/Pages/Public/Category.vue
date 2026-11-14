<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import GameRow from '@/Components/Public/GameRow.vue';
import { isHex } from '@/Shared/teamColors';
import { dayLabel, longDay, todayParts } from '@/Shared/dates';
import { Link } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

const props = defineProps({
    championship: { type: Object, required: true },
    category: { type: Object, required: true },
    games: { type: Array, default: () => [] },
    standings: { type: Array, default: () => [] },
    scorers: { type: Array, default: () => [] },
});

const today = ref(null)
onMounted(() => { today.value = todayParts() })

const tabs = [
    { key: 'games', label: 'Partidos' },
    { key: 'standings', label: 'Posiciones' },
    { key: 'scorers', label: 'Goleadores' },
]
const tab = ref('games')

// Flechas entre pestañas, como en cualquier grupo de pestañas
const onTabKey = (e) => {
    const step = { ArrowRight: 1, ArrowLeft: -1 }[e.key]
    if (!step) return
    const i = (tabs.findIndex(t => t.key === tab.value) + step + tabs.length) % tabs.length
    tab.value = tabs[i].key
    e.currentTarget.querySelectorAll('[role="tab"]')[i]?.focus()
}

// Partidos agrupados por día
const days = computed(() => {
    const map = new Map()
    props.games.forEach(game => {
        const key = String(game.date).slice(0, 10)
        if (!map.has(key)) map.set(key, [])
        map.get(key).push(game)
    })
    return [...map].map(([date, games]) => ({ date, games }))
})
const dayHeading = (date) => {
    const relative = dayLabel(date, today.value)
    return ['Hoy', 'Mañana', 'Ayer'].includes(relative) ? `${relative}, ${longDay(date)}` : longDay(date)
}

// Tabla por grupo (ya llega ordenada por grupo y puntos)
const groups = computed(() => {
    const out = []
    props.standings.forEach(club => {
        let group = out.find(g => g.id === club.group_id)
        if (!group) out.push(group = { id: club.group_id, name: club.group_name, clubs: [] })
        group.clubs.push({
            ...club,
            played: Number(club.ganado) + Number(club.empate) + Number(club.perdido),
            diff: Number(club.golf) - Number(club.golc),
            points: Number(club.ganado) * 3 + Number(club.empate) + Number(club.extra_points),
        })
    })
    return out
})
const signed = (n) => n > 0 ? `+${n}` : `${n}`
const dot = (color) => isHex(color) ? { backgroundColor: color } : null
</script>

<template>
    <PublicLayout :title="`${category.name} · ${championship.name}`" :championship="championship"
        :share-text="`${category.name} de ${championship.name}: partidos, posiciones y goleadores`">

        <div>
            <Link :href="route('public.championship', championship.slug)"
                class="inline-flex items-center gap-2 min-h-[40px] text-[13px] font-medium text-slate-400 hover:text-slate-200 rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40">
                <i class="fa-solid fa-arrow-left text-[11px]" aria-hidden="true"></i> Categorías
            </Link>
            <h1 class="mt-1 text-xl font-semibold tracking-tight text-white">{{ category.name }}</h1>
        </div>

        <div role="tablist" aria-label="Información de la categoría" @keydown="onTabKey"
            class="grid grid-cols-3 gap-1 p-1 rounded-lg bg-slate-900 border border-white/[0.06]">
            <button v-for="t in tabs" :key="t.key" type="button" role="tab" :id="`tab-${t.key}`" :aria-selected="tab === t.key" :aria-controls="`panel-${t.key}`"
                :tabindex="tab === t.key ? 0 : -1" @click="tab = t.key"
                class="min-h-[40px] rounded-md text-[13px] font-medium transition-colors duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/50"
                :class="tab === t.key ? 'bg-slate-700/70 text-white' : 'text-slate-400 hover:text-slate-200'">
                {{ t.label }}
            </button>
        </div>

        <!-- Partidos -->
        <div v-show="tab === 'games'" id="panel-games" role="tabpanel" aria-labelledby="tab-games" class="space-y-4">
            <section v-for="day in days" :key="day.date" class="rounded-xl bg-slate-900 border border-white/[0.06] overflow-hidden">
                <h2 class="px-4 sm:px-5 py-2.5 border-b border-white/[0.06] text-[12px] font-semibold text-slate-300 first-letter:uppercase">{{ dayHeading(day.date) }}</h2>
                <ul class="divide-y divide-white/[0.04]">
                    <li v-for="game in day.games" :key="game.id">
                        <GameRow :game="game" :slug="championship.slug" :show-day="false" :show-category="false" />
                    </li>
                </ul>
            </section>
            <EmptyState v-if="!days.length" bordered icon="fa-regular fa-futbol" title="Aún no hay partidos"
                description="Cuando el organizador programe los partidos de esta categoría, aparecerán aquí." />
        </div>

        <!-- Posiciones -->
        <div v-show="tab === 'standings'" id="panel-standings" role="tabpanel" aria-labelledby="tab-standings" class="space-y-4">
            <section v-for="group in groups" :key="group.id ?? 'sin-grupo'" class="rounded-xl bg-slate-900 border border-white/[0.06] overflow-hidden">
                <h2 class="px-4 sm:px-5 py-2.5 border-b border-white/[0.06] text-[12px] font-semibold text-slate-300">{{ group.name ?? 'Sin grupo' }}</h2>
                <table class="w-full text-[13px] tabular-nums">
                    <thead>
                        <tr class="text-[11px] font-medium text-slate-500">
                            <th scope="col" class="pl-4 sm:pl-5 pr-1 py-2 w-8 text-left font-medium"><span class="sr-only">Posición</span>#</th>
                            <th scope="col" class="px-2 py-2 text-left font-medium">Club</th>
                            <th scope="col" class="px-2 py-2 text-right font-medium"><abbr title="Partidos jugados" class="no-underline">PJ</abbr></th>
                            <th scope="col" class="hidden sm:table-cell px-2 py-2 text-right font-medium"><abbr title="Ganados" class="no-underline">G</abbr></th>
                            <th scope="col" class="hidden sm:table-cell px-2 py-2 text-right font-medium"><abbr title="Empatados" class="no-underline">E</abbr></th>
                            <th scope="col" class="hidden sm:table-cell px-2 py-2 text-right font-medium"><abbr title="Perdidos" class="no-underline">P</abbr></th>
                            <th scope="col" class="px-2 py-2 text-right font-medium"><abbr title="Diferencia de goles" class="no-underline">DG</abbr></th>
                            <th scope="col" class="pl-2 pr-4 sm:pr-5 py-2 text-right font-medium"><abbr title="Puntos" class="no-underline">Pts</abbr></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04] border-t border-white/[0.04]">
                        <tr v-for="club in group.clubs" :key="club.club_id">
                            <td class="pl-4 sm:pl-5 pr-1 py-2.5 text-slate-500">{{ club.index }}</td>
                            <th scope="row" class="px-2 py-2.5 text-left font-medium text-slate-100">
                                {{ club.name }}
                                <span class="sm:hidden block text-[11px] font-normal text-slate-500">{{ club.ganado }}G {{ club.empate }}E {{ club.perdido }}P</span>
                            </th>
                            <td class="px-2 py-2.5 text-right text-slate-300">{{ club.played }}</td>
                            <td class="hidden sm:table-cell px-2 py-2.5 text-right text-slate-300">{{ club.ganado }}</td>
                            <td class="hidden sm:table-cell px-2 py-2.5 text-right text-slate-300">{{ club.empate }}</td>
                            <td class="hidden sm:table-cell px-2 py-2.5 text-right text-slate-300">{{ club.perdido }}</td>
                            <td class="px-2 py-2.5 text-right text-slate-300">{{ signed(club.diff) }}</td>
                            <td class="pl-2 pr-4 sm:pr-5 py-2.5 text-right text-[15px] font-semibold text-white">{{ club.points }}</td>
                        </tr>
                    </tbody>
                </table>
            </section>
            <p v-if="groups.length" class="text-[12px] text-slate-500">3 puntos por victoria y 1 por empate. Cuenta solo la fase de grupos.</p>
            <EmptyState v-else bordered icon="fa-solid fa-ranking-star" title="Aún no hay tabla"
                description="La tabla aparece cuando la categoría tiene clubes registrados." />
        </div>

        <!-- Goleadores -->
        <div v-show="tab === 'scorers'" id="panel-scorers" role="tabpanel" aria-labelledby="tab-scorers">
            <ol v-if="scorers.length" class="rounded-xl bg-slate-900 border border-white/[0.06] overflow-hidden divide-y divide-white/[0.04]">
                <li v-for="(player, index) in scorers" :key="player.id" class="flex items-center gap-3 px-4 sm:px-5 py-2.5">
                    <span class="w-6 shrink-0 text-[13px] tabular-nums text-slate-500">{{ index + 1 }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="truncate text-sm font-medium text-slate-100">{{ player.first_name }} {{ player.last_name }}</p>
                        <p class="flex items-center gap-1.5 text-[12px] text-slate-400">
                            <span class="w-2 h-2 shrink-0 rounded-full ring-1 ring-inset ring-white/20 bg-slate-600" :style="dot(player.club_color)" aria-hidden="true"></span>
                            <span class="truncate">{{ player.club }}</span>
                        </p>
                    </div>
                    <span class="shrink-0 text-lg font-semibold tabular-nums text-white">{{ player.goals }}</span>
                    <span class="sr-only">{{ player.goals === 1 ? 'gol' : 'goles' }}</span>
                </li>
            </ol>
            <EmptyState v-else bordered icon="fa-solid fa-crosshairs" title="Aún no hay goles"
                description="Los goleadores aparecen cuando se juegan los primeros partidos." />
        </div>
    </PublicLayout>
</template>
