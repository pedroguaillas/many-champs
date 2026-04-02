<script setup>

// Imports
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';

// Props
const props = defineProps({
    games: { type: Array, default: () => { } },
    date: { type: String, default: () => null }
})

// Refs
let date = ref(props.date)

// Reactives
let games = reactive(props.games)

const changeDate = (e) => {
    router.get(route('calendar', e.target.value))
}

</script>

<template>
    <AdminLayout title="Calendario">

        <!-- Card -->
        <div class="rounded-xl bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] overflow-hidden">

            <!-- Card header -->
            <div class="px-5 py-4 border-b border-gray-200 dark:border-white/[0.06] flex justify-between items-center">
                <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Calendario de juegos</h2>
                <input v-model="date" type="date" @change="changeDate"
                    class="px-3.5 py-2.5 rounded-lg bg-white dark:bg-slate-900/80 border border-gray-300 dark:border-white/[0.08] text-[13px] text-gray-700 dark:text-slate-200 focus:outline-none focus:border-emerald-500/40 focus:ring-1 focus:ring-emerald-500/20 transition-colors duration-200" />
            </div>

            <!-- Responsive -->
            <div class="w-full overflow-x-auto">
                <!-- Tabla -->
                <table v-if="games.length > 0" class="table-auto w-full text-center">
                    <tbody>
                        <tr class="bg-gray-100 dark:bg-slate-700/30">
                            <th colspan="8" class="px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-emerald-400">{{ games[0].date }}</th>
                        </tr>
                        <template v-for="game, i in games" :key="i">

                            <tr v-if="i > 0 && games[i - 1].date !== game.date" class="bg-gray-100 dark:bg-slate-700/30">
                                <th colspan="8" class="px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-emerald-400">{{ game.date }}</th>
                            </tr>

                            <tr class="border-b border-gray-100 dark:border-white/[0.04] hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150">
                                <td class="px-3 py-3 text-[13px] text-gray-400 dark:text-slate-500">{{ i + 1 }}</td>
                                <td class="px-3 py-3 text-[13px] text-gray-700 dark:text-slate-200 text-center">{{ game.c1name }}</td>
                                <td class="px-3 py-3 text-center">
                                    <span class="rounded-md text-[10px] px-2 py-0.5 font-medium"
                                        :class="game.state === 'finalizado' ? 'bg-emerald-500/20 text-emerald-400' : (game.state === 'planificado' ? 'bg-amber-500/20 text-amber-400' : 'text-gray-400 dark:text-slate-500')">VS</span>
                                </td>
                                <td class="px-3 py-3 text-[13px] text-gray-700 dark:text-slate-200">{{ game.c2name }}</td>
                                <td class="px-3 py-3 text-[13px] text-gray-400 dark:text-slate-500">{{ game.time.substring(0, 2) }}</td>
                                <td class="px-3 py-3 text-[13px] text-gray-500 dark:text-slate-400">{{ `${game.g_name} | ${game.name}` }}</td>
                                <td class="px-3 py-3 w-1">
                                    <Link v-if="game.state === 'creado' || game.state === 'planificado'"
                                        :href="route('games.select-players', game.id)"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30 transition-colors duration-200">
                                    <i class="far fa-futbol text-xs"></i>
                                    </Link>
                                    <Link v-else :href="route('playing.index', game.id)"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30 transition-colors duration-200">
                                    <i class="far fa-futbol text-xs"></i>
                                    </Link>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

    </AdminLayout>
</template>
