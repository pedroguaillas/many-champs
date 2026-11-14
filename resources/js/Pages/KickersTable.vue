<script setup>

// Imports
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BackLink from '@/Components/BackLink.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { Link } from '@inertiajs/vue3';
import Pagination from '@/Shared/Pagination.vue'

// Props
defineProps({
    category: { type: Object, default: () => { } },
    players: { type: Object, default: () => { } }
})

</script>

<template>
    <AdminLayout title="Tabla de goleadores">
        <BackLink :href="route('select.category', { type: 'tabla-de-goleadores', todas: 1 })" label="Cambiar categoría" />

        <!-- Card -->
        <div class="rounded-xl bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] overflow-hidden">

            <!-- Card header -->
            <div class="px-5 py-4 border-b border-gray-200 dark:border-white/[0.06]">
                <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white uppercase">Tabla de goleadores {{ category.name }}</h2>
            </div>

            <!-- Responsive -->
            <div class="w-full overflow-x-auto">
                <!-- Tabla -->
                <table v-if="players.data.length > 0"
                    class="data-table table-auto w-full text-center">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-white/[0.06]">
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">#</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Jugador</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Club</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Gol</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="player, i in players.data" :key="i" class="border-b border-gray-100 dark:border-white/[0.04] hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150">
                            <td class="px-5 py-3 text-[13px] text-gray-400 dark:text-slate-500 tabular-nums">{{ (players.from ?? 1) + i }}</td>
                            <td class="px-5 py-3 text-[13px] text-gray-700 dark:text-slate-200">{{ `${player.first_name} ${player.last_name}` }}</td>
                            <td class="px-5 py-3 text-[13px] text-gray-500 dark:text-slate-400">{{ player.club }}</td>
                            <td class="px-5 py-3 text-[13px] text-emerald-400 font-semibold tabular-nums">{{ player.goals ?? 0 }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Sin goles -->
                <EmptyState v-else icon="fa-solid fa-crosshairs" title="Aún no hay goles registrados"
                    description="Los goleadores aparecen cuando se anotan goles durante un partido en vivo. Abre un partido de esta categoría y registra cada gol al jugador que lo marcó.">
                    <Link :href="route('games.index', category.id)" class="btn-primary">
                        <i class="fa-solid fa-futbol text-[11px]"></i> Ir a partidos
                    </Link>
                </EmptyState>
            </div>

            <div v-if="players.data.length > 0" class="px-5 py-4 border-t border-gray-200 dark:border-white/[0.06]">
                <Pagination :links="players.links" />
            </div>

        </div>
    </AdminLayout>
</template>