<script setup>

// Imports
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BackLink from '@/Components/BackLink.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

// Props
const props = defineProps({
    category: { type: Object, default: () => { } },
    clubs: { type: Array, default: () => [] }
});

// Hay clubes pero ningún partido finalizado: la tabla sale en cero
const played = computed(() => props.clubs.some(c => Number(c.ganado) + Number(c.empate) + Number(c.perdido) > 0))

</script>

<template>
    <AdminLayout title="Tabla de posiciones">
        <BackLink :href="route('select.category', { type: 'tabla-de-posiciones', todas: 1 })" label="Cambiar categoría" />

        <!-- Card -->
        <div class="rounded-xl bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] overflow-hidden">

            <!-- Card header -->
            <div class="px-5 py-4 border-b border-gray-200 dark:border-white/[0.06]">
                <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white uppercase">Tabla de posiciones {{ category.name }}</h2>
            </div>

            <!-- Responsive -->
            <div class="w-full overflow-x-auto">
                <!-- Tabla -->
                <table v-if="clubs.length > 0" class="data-table table-auto w-full text-center">
                    <tbody>

                        <template v-for="club, i in clubs" :key="club.id">

                            <tr v-if="i === 0 || club.group_id !== clubs[i - 1].group_id" class="bg-gray-100 dark:bg-slate-700/30">
                                <th colspan="2" class="px-5 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">{{ `GRUPO ${club.group_name}` }}</th>
                                <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">PJ</th>
                                <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">G</th>
                                <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">E</th>
                                <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">P</th>
                                <th class="hidden sm:table-cell px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">GF</th>
                                <th class="hidden sm:table-cell px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">GC</th>
                                <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">DG</th>
                                <th class="hidden sm:table-cell px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">PE</th>
                                <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Pts</th>
                            </tr>

                            <tr class="border-b border-gray-100 dark:border-white/[0.04] hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150">
                                <td class="px-5 py-3 text-left text-[13px] text-gray-400 dark:text-slate-500 tabular-nums">{{ club.index }}</td>
                                <td class="px-3 py-3 text-left text-[13px] text-gray-700 dark:text-slate-200 uppercase tabular-nums">{{ club.name }}</td>
                                <td class="px-3 py-3 text-[13px] text-gray-700 dark:text-slate-200 tabular-nums">{{ Number(club.ganado) + Number(club.empate) + Number(club.perdido) }}</td>
                                <td class="px-3 py-3 text-[13px] text-gray-700 dark:text-slate-200 tabular-nums">{{ club.ganado }}</td>
                                <td class="px-3 py-3 text-[13px] text-gray-700 dark:text-slate-200 tabular-nums">{{ club.empate }}</td>
                                <td class="px-3 py-3 text-[13px] text-gray-700 dark:text-slate-200 tabular-nums">{{ club.perdido }}</td>
                                <td class="hidden sm:table-cell px-3 py-3 text-[13px] text-gray-700 dark:text-slate-200 tabular-nums">{{ club.golf ? club.golf : 0 }}</td>
                                <td class="hidden sm:table-cell px-3 py-3 text-[13px] text-gray-700 dark:text-slate-200 tabular-nums">{{ club.golc ? club.golc : 0 }}</td>
                                <td class="px-3 py-3 text-[13px] text-gray-700 dark:text-slate-200 tabular-nums">{{ club.golf - club.golc }}</td>
                                <td class="hidden sm:table-cell px-3 py-3 text-[13px] text-gray-700 dark:text-slate-200 tabular-nums">{{ parseFloat(club.extra_points) }}</td>
                                <td class="px-3 py-3 text-[13px] text-gray-900 dark:text-white font-semibold tabular-nums">{{ Number(club.extra_points) + (club.ganado * 3) + Number(club.empate) }}
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <!-- Sin clubes -->
                <EmptyState v-else icon="fa-solid fa-ranking-star" title="Esta categoría aún no tiene clubes"
                    description="La tabla ordena a los clubes de cada grupo por puntos: 3 por victoria y 1 por empate. Registra los clubes y juega partidos para verla.">
                    <Link :href="route('clubs.index', category.id)" class="btn-primary">
                        <i class="fa-solid fa-shield-halved text-[11px]"></i> Registrar clubes
                    </Link>
                </EmptyState>
            </div>

            <!-- Con clubes pero sin partidos finalizados -->
            <div v-if="clubs.length > 0 && !played"
                class="px-5 py-4 border-t border-gray-200 dark:border-white/[0.06] flex flex-wrap items-center justify-between gap-x-4 gap-y-2">
                <p class="text-[13px] text-gray-500 dark:text-slate-400">
                    Todavía no hay partidos finalizados. Los puntos se suman al finalizar cada partido.
                </p>
                <Link :href="route('games.index', category.id)"
                    class="px-5 py-2.5 rounded-lg text-xs font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-700/40 transition duration-200">
                    Ir a partidos
                </Link>
            </div>
        </div>
    </AdminLayout>
</template>