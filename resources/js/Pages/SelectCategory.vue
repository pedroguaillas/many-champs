<script setup>

// Imports
import AdminLayout from '@/Layouts/AdminLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { Link } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

// Props
const props = defineProps({
    categories: { type: Array, default: () => [] },
    type: { type: String, default: () => '' },
});

// A qué sección se entra: el título dice para qué se elige la categoría
const sections = {
    clubs: { label: 'Clubes', icon: 'fa-shield-halved' },
    games: { label: 'Partidos', icon: 'fa-futbol' },
    groups: { label: 'Grupos', icon: 'fa-layer-group' },
    'tabla-de-posiciones': { label: 'Posiciones', icon: 'fa-ranking-star' },
    'tabla-de-goleadores': { label: 'Goleadores', icon: 'fa-crosshairs' },
}
const section = computed(() => sections[props.type] ?? { label: 'Categorías', icon: 'fa-layer-group' })

// Última categoría usada (solo en este dispositivo): va primero y marcada
const STORAGE_KEY = 'many-champs:last-category'
const lastId = ref(null)
onMounted(() => {
    try { lastId.value = Number(localStorage.getItem(STORAGE_KEY)) || null } catch { }
})
const remember = (id) => {
    try { localStorage.setItem(STORAGE_KEY, String(id)) } catch { }
}

const list = computed(() => [...props.categories].sort((a, b) =>
    (b.id === lastId.value) - (a.id === lastId.value)
    || Number(b.live) - Number(a.live)
))

const plural = (n, one, many) => `${n} ${Number(n) === 1 ? one : many}`
const progress = (c) => Number(c.games) ? Math.round(Number(c.finished) / Number(c.games) * 100) : 0

// "sáb 15 nov · 19:00" (la fecha llega como texto, sin pasar por zona horaria)
const nextGame = (value) => {
    if (!value) return ''
    const [date, time] = value.split(' ')
    const [y, m, d] = date.split('-').map(Number)
    const day = new Intl.DateTimeFormat('es', { weekday: 'short', day: 'numeric', month: 'short' }).format(new Date(y, m - 1, d)).replace(/\./g, '')
    return `${day} · ${time?.slice(0, 5) ?? ''}`
}

</script>

<template>
    <AdminLayout :title="section.label">
        <div class="max-w-4xl">

            <!-- Para qué se elige -->
            <header class="mb-5 flex items-center gap-3">
                <div class="w-10 h-10 shrink-0 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                    <i class="fa-solid text-emerald-500 dark:text-emerald-400 text-sm" :class="section.icon"></i>
                </div>
                <div class="min-w-0">
                    <h1 class="text-[15px] font-semibold tracking-tight text-gray-900 dark:text-white">{{ section.label }}</h1>
                    <p class="text-[12px] text-gray-500 dark:text-slate-500">Elige la categoría</p>
                </div>
            </header>

            <!-- Categorías: toda la tarjeta es el enlace -->
            <ul v-if="categories.length" class="grid sm:grid-cols-2 gap-3">
                <li v-for="category in list" :key="category.id">
                    <Link :href="route(`${type}.index`, category.id)" @click="remember(category.id)"
                        class="group relative flex flex-col h-full rounded-xl bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] overflow-hidden
                               transition duration-150 hover:border-emerald-500/30 dark:hover:border-emerald-500/30 active:scale-[0.99]
                               focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40">
                        <span v-if="category.id === lastId" class="absolute left-0 top-0 bottom-0 w-[3px] bg-emerald-500 rounded-r-full"></span>

                        <div class="flex items-start gap-3 px-4 sm:px-5 pt-4 pb-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <h2 class="truncate text-base font-semibold tracking-tight text-gray-900 dark:text-white">{{ category.name }}</h2>
                                    <span v-if="category.id === lastId"
                                        class="px-1.5 py-px rounded-md text-[10px] font-semibold uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                        Última
                                    </span>
                                </div>
                                <p class="mt-0.5 text-[12px] text-gray-500 dark:text-slate-400">
                                    <span v-if="category.gender" class="capitalize">{{ category.gender }} · </span>
                                    <span class="tabular-nums">{{ plural(category.count, 'club', 'clubes') }}</span> ·
                                    <span class="tabular-nums">{{ plural(category.players, 'jugador', 'jugadores') }}</span>
                                </p>
                            </div>
                            <i class="fa-solid fa-chevron-right mt-1.5 text-[11px] text-gray-300 dark:text-slate-600 group-hover:text-emerald-500 group-hover:translate-x-0.5 transition duration-150"></i>
                        </div>

                        <!-- Estado de la competencia -->
                        <div class="mt-auto px-4 sm:px-5 pb-4 space-y-2.5">
                            <div class="flex flex-wrap items-center gap-2 min-h-[22px]">
                                <span v-if="Number(category.live)"
                                    class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-red-500/10 text-red-600 dark:text-red-400">
                                    <span class="relative flex w-1.5 h-1.5">
                                        <span class="absolute inset-0 rounded-full bg-red-500 animate-ping opacity-75 motion-reduce:hidden"></span>
                                        <span class="relative w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                    </span>
                                    {{ plural(category.live, 'partido en vivo', 'partidos en vivo') }}
                                </span>
                                <span v-if="category.next_game" class="inline-flex items-center gap-1.5 text-[11px] text-gray-500 dark:text-slate-400">
                                    <i class="fa-regular fa-calendar text-[10px]"></i>
                                    Próximo: <span class="tabular-nums font-medium text-gray-700 dark:text-slate-300">{{ nextGame(category.next_game) }}</span>
                                </span>
                                <span v-else-if="!Number(category.live) && !Number(category.games)" class="text-[11px] text-gray-400 dark:text-slate-500">
                                    Sin partidos programados
                                </span>
                            </div>

                            <!-- Avance del torneo: partidos jugados del total -->
                            <div v-if="Number(category.games)" class="flex items-center gap-3">
                                <div class="flex-1 h-1 rounded-full bg-gray-100 dark:bg-slate-900/80 overflow-hidden"
                                    role="progressbar" :aria-valuenow="progress(category)" aria-valuemin="0" aria-valuemax="100"
                                    :aria-label="`Partidos jugados de ${category.name}`">
                                    <div class="h-full w-full origin-left rounded-full bg-emerald-500" :style="{ transform: `scaleX(${progress(category) / 100})` }"></div>
                                </div>
                                <span class="shrink-0 text-[11px] tabular-nums text-gray-500 dark:text-slate-400">
                                    {{ category.finished }}/{{ category.games }} jugados
                                </span>
                            </div>
                        </div>
                    </Link>
                </li>
            </ul>

            <!-- Sin categorías -->
            <EmptyState v-else bordered icon="fa-solid fa-layer-group" title="Aún no hay categorías"
                :description="`Una categoría es un torneo, por ejemplo &quot;Sénior masculino&quot;. Crea la primera para gestionar ${section.label.toLowerCase()}.`">
                <Link :href="route('categories.index')" class="btn-primary">
                    <i class="fa-solid fa-plus text-[11px]"></i> Crear categoría
                </Link>
            </EmptyState>
        </div>
    </AdminLayout>
</template>
