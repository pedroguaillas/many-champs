<script setup>

// Imports
import AdminLayout from '@/Layouts/AdminLayout.vue';

// Props
defineProps({
    category: { type: Object, default: () => { } },
    clubs: { type: Array, default: () => [] }
});

</script>

<template>
    <AdminLayout title="Tabla de posciones">

        <!-- Card -->
        <div class="rounded-xl bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] overflow-hidden">

            <!-- Card header -->
            <div class="px-5 py-4 border-b border-gray-200 dark:border-white/[0.06]">
                <h2 class="text-sm font-semibold tracking-tight text-white uppercase">Tabla de posiciones {{ category.name }}</h2>
            </div>

            <!-- Responsive -->
            <div class="w-full overflow-x-auto">
                <!-- Tabla -->
                <table v-if="clubs.length > 0" class="table-auto w-full text-center">
                    <tbody>

                        <template v-for="club, i in clubs" :key="club.id">

                            <tr v-if="i === 0 || club.group_id !== clubs[i - 1].group_id" class="bg-gray-100 dark:bg-slate-700/30">
                                <th colspan="2" class="px-5 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">{{ `GRUPO ${club.group_name}` }}</th>
                                <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">PJ</th>
                                <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">G</th>
                                <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">E</th>
                                <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">P</th>
                                <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">GF</th>
                                <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">GC</th>
                                <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">DG</th>
                                <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">PE</th>
                                <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Pts</th>
                            </tr>

                            <tr class="border-b border-gray-100 dark:border-white/[0.04] hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150">
                                <td class="px-5 py-3 text-left text-[13px] text-gray-400 dark:text-slate-500 tabular-nums">{{ club.index }}</td>
                                <td class="px-3 py-3 text-left text-[13px] text-gray-700 dark:text-slate-200 uppercase tabular-nums">{{ club.name }}</td>
                                <td class="px-3 py-3 text-[13px] text-gray-700 dark:text-slate-200 tabular-nums">{{ club.ganado + club.empate + club.perdido }}</td>
                                <td class="px-3 py-3 text-[13px] text-gray-700 dark:text-slate-200 tabular-nums">{{ club.ganado }}</td>
                                <td class="px-3 py-3 text-[13px] text-gray-700 dark:text-slate-200 tabular-nums">{{ club.empate }}</td>
                                <td class="px-3 py-3 text-[13px] text-gray-700 dark:text-slate-200 tabular-nums">{{ club.perdido }}</td>
                                <td class="px-3 py-3 text-[13px] text-gray-700 dark:text-slate-200 tabular-nums">{{ club.golf ? club.golf : 0 }}</td>
                                <td class="px-3 py-3 text-[13px] text-gray-700 dark:text-slate-200 tabular-nums">{{ club.golc ? club.golc : 0 }}</td>
                                <td class="px-3 py-3 text-[13px] text-gray-700 dark:text-slate-200 tabular-nums">{{ club.golf - club.golc }}</td>
                                <td class="px-3 py-3 text-[13px] text-gray-700 dark:text-slate-200 tabular-nums">{{ parseFloat(club.extra_points) }}</td>
                                <td class="px-3 py-3 text-[13px] text-white font-semibold tabular-nums">{{ club.extra_points + (club.ganado * 3) + club.empate }}
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>