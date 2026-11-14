<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import GameRow from '@/Components/Public/GameRow.vue';
import { todayParts } from '@/Shared/dates';
import { Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

defineProps({
    championship: { type: Object, required: true },
    categories: { type: Array, default: () => [] },
    live: { type: Array, default: () => [] },
    upcoming: { type: Array, default: () => [] },
    results: { type: Array, default: () => [] },
});

const today = ref(null)
onMounted(() => { today.value = todayParts() })

const plural = (n, one, many) => `${n} ${Number(n) === 1 ? one : many}`
const progress = (c) => c.games ? Math.round(c.finished / c.games * 100) : 0
</script>

<template>
    <PublicLayout :title="championship.name" :championship="championship"
        :share-text="`Sigue los partidos de ${championship.name}`">

        <!-- En juego ahora -->
        <section v-if="live.length" aria-labelledby="live-heading" class="rounded-xl bg-slate-900 border border-red-500/30 overflow-hidden">
            <h2 id="live-heading" class="px-4 sm:px-5 pt-4 pb-1 text-[13px] font-semibold text-red-400">En juego ahora</h2>
            <ul class="divide-y divide-white/[0.04]">
                <li v-for="game in live" :key="game.id"><GameRow :game="game" :slug="championship.slug" :today="today" /></li>
            </ul>
        </section>

        <!-- Categorías -->
        <section aria-labelledby="categories-heading">
            <h2 id="categories-heading" class="mb-2.5 text-[13px] font-semibold text-white">Categorías</h2>
            <ul v-if="categories.length" class="grid sm:grid-cols-2 gap-3">
                <li v-for="category in categories" :key="category.id">
                    <Link :href="route('public.category', [championship.slug, category.id])"
                        class="group flex flex-col h-full rounded-xl bg-slate-900 border border-white/[0.06] px-4 py-3.5 transition duration-150 hover:border-emerald-500/30 active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40">
                        <div class="flex items-start gap-3">
                            <div class="flex-1 min-w-0">
                                <h3 class="truncate text-base font-semibold tracking-tight text-white">{{ category.name }}</h3>
                                <p class="mt-0.5 text-[12px] text-slate-400">
                                    <span v-if="category.gender" class="capitalize">{{ category.gender }}, </span>
                                    <span class="tabular-nums">{{ plural(category.clubs, 'club', 'clubes') }}</span>
                                </p>
                            </div>
                            <i class="fa-solid fa-chevron-right mt-1.5 text-[11px] text-slate-600 group-hover:text-emerald-400 transition-colors duration-150" aria-hidden="true"></i>
                        </div>
                        <div v-if="category.games" class="mt-3 flex items-center gap-3">
                            <div class="flex-1 h-1 rounded-full bg-slate-950 overflow-hidden" role="progressbar" :aria-valuenow="progress(category)" aria-valuemin="0" aria-valuemax="100"
                                :aria-label="`Partidos jugados de ${category.name}`">
                                <div class="h-full w-full origin-left rounded-full bg-emerald-500" :style="{ transform: `scaleX(${progress(category) / 100})` }"></div>
                            </div>
                            <span class="shrink-0 text-[11px] tabular-nums text-slate-400">{{ category.finished }}/{{ category.games }} jugados</span>
                        </div>
                        <p v-else class="mt-3 text-[11px] text-slate-500">Sin partidos programados</p>
                    </Link>
                </li>
            </ul>
            <EmptyState v-else bordered icon="fa-solid fa-layer-group" title="Aún no hay categorías"
                description="El organizador todavía no ha creado las categorías de este campeonato." />
        </section>

        <!-- Próximos -->
        <section aria-labelledby="upcoming-heading" class="rounded-xl bg-slate-900 border border-white/[0.06] overflow-hidden">
            <div class="flex items-center justify-between gap-3 px-4 sm:px-5 py-3 border-b border-white/[0.06]">
                <h2 id="upcoming-heading" class="text-[13px] font-semibold text-white">Próximos partidos</h2>
                <Link :href="route('public.calendar', championship.slug)" class="-my-2 py-2 text-[12px] font-medium text-emerald-400 hover:underline rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40">
                    Ver calendario
                </Link>
            </div>
            <ul v-if="upcoming.length" class="divide-y divide-white/[0.04]">
                <li v-for="game in upcoming" :key="game.id"><GameRow :game="game" :slug="championship.slug" :today="today" /></li>
            </ul>
            <EmptyState v-else icon="fa-regular fa-calendar" title="No hay partidos programados"
                description="Cuando el organizador programe los siguientes, aparecerán aquí." />
        </section>

        <!-- Resultados -->
        <section v-if="results.length" aria-labelledby="results-heading" class="rounded-xl bg-slate-900 border border-white/[0.06] overflow-hidden">
            <h2 id="results-heading" class="px-4 sm:px-5 py-3 border-b border-white/[0.06] text-[13px] font-semibold text-white">Últimos resultados</h2>
            <ul class="divide-y divide-white/[0.04]">
                <li v-for="game in results" :key="game.id"><GameRow :game="game" :slug="championship.slug" :today="today" /></li>
            </ul>
        </section>
    </PublicLayout>
</template>
