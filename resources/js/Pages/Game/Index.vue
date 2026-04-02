<script setup>

// Imports
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Swal from 'sweetalert2';

// Props
const props = defineProps({
    category: { type: Object, default: () => { } },
    games: { type: Array, default: () => [] },
    groups: { type: Object, default: () => [] }
});

// Seccion filtro ..... Inicio
const search = ref('');

const url = route('games.index', props.category.id);

watch(search, (value) => {
    router.get(url, { search: value }, { preserveState: true, preserveScroll: true, only: ['games'] })
}, 300);
// Seccion filtro ..... Fin

const deleteGame = (game) => {

    const alert = Swal.mixin({
        buttonsStyling: true
    });

    alert.fire({
        title: `¿Esta seguro eliminar el partido entre ${game.c1name} vs ${game.c2name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Si, Eliminar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar',
        background: '#1e293b',
        color: '#e2e8f0',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#334155',
    }).then((result) => {
        if (result.isConfirmed) {
            axios
                .delete(route('games.destroy', game.id))
                .then(() => router.reload({ only: ['games'] })
                ).catch(error => console.log(error.response.data.msm))
        }
    });
}

</script>

<template>
    <AdminLayout :title="'Partidos'">

        <!-- Card -->
        <div class="rounded-xl bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] overflow-hidden">

            <!-- Card header -->
            <div class="px-5 py-4 border-b border-gray-200 dark:border-white/[0.06] flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                        <i class="far fa-futbol text-emerald-400 text-[11px]"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">{{ `${category.name} ${category.gender}` }}</h2>
                        <p class="text-[11px] text-gray-400 dark:text-slate-500">Partidos</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <select v-if="groups.length > 0" v-model="search"
                        class="px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] text-[12px] text-gray-600 dark:text-slate-300 focus:outline-none focus:border-emerald-500/40">
                        <option value="">Todos</option>
                        <option v-for="gr in groups" :value="gr.id">{{ gr.name }}</option>
                    </select>
                    <Link v-if="games.length > 0" :href="route('games.create', category.id)"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-emerald-500/10 text-emerald-400 text-xs font-medium border border-emerald-500/20 hover:bg-emerald-500/20 hover:border-emerald-500/30 transition-all duration-200">
                        <i class="fa-solid fa-plus text-[11px]"></i> Crear
                    </Link>
                </div>
            </div>

            <!-- Responsive -->
            <div class="w-full overflow-x-auto">
                <!-- Tabla -->
                <table v-if="games.length > 0" class="w-full text-center">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 w-1">N°</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Equipo 1</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">VS</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Equipo 2</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="game, i in games" :key="game.id"
                            class="border-b border-gray-100 dark:border-white/[0.04] hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150">
                            <td class="px-5 py-3.5 text-[13px] text-gray-500 dark:text-slate-400">{{ i + 1 }}</td>
                            <td class="px-5 py-3.5 text-[13px] text-gray-700 dark:text-slate-200">{{ `${game.c1name} ${game.state === 'finalizado' ? `(${game.gols1 ?? 0})` : ''}` }}</td>
                            <td class="px-5 py-3.5">
                                <span
                                    :class="game.state === 'planificado' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : (game.state === 'finalizado' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-gray-200 dark:bg-slate-700/50 text-gray-500 dark:text-slate-400 border border-gray-200 dark:border-white/[0.06]')"
                                    class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium">VS</span>
                            </td>
                            <td class="px-5 py-3.5 text-[13px] text-gray-700 dark:text-slate-200">{{ `${game.c2name} ${game.state === 'finalizado' ? `(${game.gols2 ?? 0})` : ''}` }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1">
                                    <Link v-if="game.state === 'creado' || game.state === 'planificado'"
                                        :href="route('games.select-players', game.id)"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 dark:text-slate-500 hover:text-emerald-400 hover:bg-emerald-500/10 transition-colors duration-150">
                                        <i class="far fa-futbol text-[11px]"></i>
                                    </Link>
                                    <Link v-else :href="route('playing.index', game.id)"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 dark:text-slate-500 hover:text-emerald-400 hover:bg-emerald-500/10 transition-colors duration-150">
                                        <i class="far fa-futbol text-[11px]"></i>
                                    </Link>
                                    <Link :href="route('games.edit', game.id)"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 dark:text-slate-500 hover:text-gray-700 dark:hover:text-gray-700 dark:text-slate-200 hover:bg-gray-200 dark:bg-slate-700/50 transition-colors duration-150">
                                        <i class="fa fa-edit text-[11px]"></i>
                                    </Link>
                                    <button @click="$event => deleteGame(game)"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 dark:text-slate-500 hover:text-red-400 hover:bg-red-500/10 transition-colors duration-150">
                                        <i class="fa fa-trash text-[11px]"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Mostrar si no hay ningun partido creado -->
                <div v-if="games.length === 0" class="flex flex-col items-center justify-center py-12">
                    <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-slate-800 border border-gray-200 dark:border-white/[0.06] flex items-center justify-center mb-4">
                        <i class="far fa-futbol text-gray-400 dark:text-slate-500 text-lg"></i>
                    </div>
                    <p class="text-[13px] text-gray-500 dark:text-slate-400 text-center">
                        Para crear los partidos de fase de grupos haz clic
                        <Link :href="route('games.vgenerate', category.id)" class="text-emerald-400 hover:text-emerald-300 transition-colors">aqui</Link>
                        , o para crear el primer partido individual haz clic
                        <Link :href="route('games.create', category.id)" class="text-emerald-400 hover:text-emerald-300 transition-colors">aqui</Link>
                    </p>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>
