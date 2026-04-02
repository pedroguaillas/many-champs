<script setup>

// Imports
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Shared/Pagination.vue'

// Props
defineProps({
    category: { type: Object, default: () => { } },
    players: { type: Object, default: () => { } }
})

</script>

<template>
    <AdminLayout title="Tabla de goleadores">

        <!-- Card -->
        <div class="rounded-xl bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] overflow-hidden">

            <!-- Card header -->
            <div class="px-5 py-4 border-b border-gray-200 dark:border-white/[0.06]">
                <h2 class="text-sm font-semibold tracking-tight text-white uppercase">Tabla de goleadores {{ category.name }}</h2>
            </div>

            <!-- Responsive -->
            <div class="w-full overflow-x-auto">
                <!-- Tabla -->
                <table v-if="players.data.length > 0"
                    class="table-auto w-full text-center">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-white/[0.06]">
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">#</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Jugador</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Club</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Gol</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="player, i in players.data" class="border-b border-gray-100 dark:border-white/[0.04] hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150">
                            <td class="px-5 py-3 text-[13px] text-gray-400 dark:text-slate-500 tabular-nums">{{ i + 1 }}</td>
                            <td class="px-5 py-3 text-[13px] text-gray-700 dark:text-slate-200">{{ `${player.first_name} ${player.last_name}` }}</td>
                            <td class="px-5 py-3 text-[13px] text-gray-500 dark:text-slate-400">{{ player.club }}</td>
                            <td class="px-5 py-3 text-[13px] text-emerald-400 font-semibold tabular-nums">{{ player.goals ?? 0 }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-4 border-t border-gray-200 dark:border-white/[0.06]">
                <Pagination :links="players.links" />
            </div>

        </div>
    </AdminLayout>
</template>