<script setup>

// Imports
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BackLink from '@/Components/BackLink.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Swal from 'sweetalert2';
import { gameState } from '@/Composables/gameState';

// Props
const props = defineProps({
    category: { type: Object, default: () => { } },
    games: { type: Array, default: () => [] },
    groups: { type: Object, default: () => [] },
    clubs_count: { type: Number, default: 0 }
});

// Seccion filtro ..... Inicio
const search = ref('');

const url = route('games.index', props.category.id);

watch(search, (value) => {
    router.get(url, { search: value }, { preserveState: true, preserveScroll: true, only: ['games'] })
}, 300);
// Seccion filtro ..... Fin

const menuOpen = ref(false);

const regenerateGames = () => {
    menuOpen.value = false;

    const alert = Swal.mixin({
        buttonsStyling: true
    });

    alert.fire({
        title: '¿Volver a crear todos los partidos?',
        text: 'Se eliminarán todos los partidos de esta categoría, con sus goles y tarjetas, y se generarán de nuevo.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Sí, continuar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar',
        background: '#1e293b',
        color: '#e2e8f0',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#334155',
    }).then((result) => {
        if (result.isConfirmed) {
            router.get(route('games.vgenerate', props.category.id));
        }
    });
}

const deleteGame = (game) => {

    const alert = Swal.mixin({
        buttonsStyling: true
    });

    alert.fire({
        title: `¿Eliminar el partido ${game.c1name} vs ${game.c2name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Sí, eliminar',
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
                ).catch(error => Swal.fire({
                    title: 'No se pudo eliminar el partido',
                    text: error.response?.data?.msm ?? 'Inténtalo de nuevo.',
                    icon: 'error',
                    background: '#1e293b',
                    color: '#e2e8f0',
                    confirmButtonColor: '#10b981',
                }))
        }
    });
}

</script>

<template>
    <AdminLayout :title="'Partidos'">
        <BackLink :href="route('select.category', { type: 'partidos', todas: 1 })" label="Cambiar categoría" />

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
                    <select v-if="groups.length > 0" v-model="search" aria-label="Filtrar por grupo"
                        class="px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] text-[12px] text-gray-600 dark:text-slate-300 focus:outline-none focus:border-emerald-500/40">
                        <option value="">Todos los grupos</option>
                        <option v-for="gr in groups" :value="gr.id">{{ gr.name }}</option>
                    </select>
                    <Link v-if="games.length > 0" :href="route('games.create', category.id)" class="btn-add">
                        <i class="fa-solid fa-plus text-[11px]"></i><span class="hidden sm:inline">Crear partido</span>
                    </Link>
                    <div v-if="games.length > 0" class="relative">
                        <button type="button" @click="menuOpen = !menuOpen" aria-label="Más opciones" :aria-expanded="menuOpen" class="btn-icon">
                            <i class="fa-solid fa-ellipsis-vertical text-[13px]"></i>
                        </button>

                        <div v-if="menuOpen" class="fixed inset-0 z-10" @click="menuOpen = false"></div>

                        <div v-if="menuOpen"
                            class="absolute right-0 mt-2 w-56 rounded-lg bg-white dark:bg-slate-800 border border-gray-200 dark:border-white/[0.06] shadow-lg z-20 overflow-hidden">
                            <button type="button" @click="regenerateGames"
                                class="w-full flex items-center gap-2 px-4 py-3 text-left text-[12px] text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/[0.04] transition-colors duration-150">
                                <i class="fa-solid fa-rotate-right text-[11px] w-4 text-red-400"></i> Volver a crear todos los partidos
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Responsive -->
            <div class="w-full overflow-x-auto">
                <!-- Tabla -->
                <table v-if="games.length > 0" class="data-table w-full text-center">
                    <thead>
                        <tr>
                            <th class="hidden sm:table-cell px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 w-1">N°</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500"><span class="sm:hidden">Partido</span><span class="hidden sm:inline">Equipo 1</span></th>
                            <th class="hidden sm:table-cell px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Estado</th>
                            <th class="hidden sm:table-cell px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Equipo 2</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="game, i in games" :key="game.id"
                            class="border-b border-gray-100 dark:border-white/[0.04] hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150">
                            <td class="hidden sm:table-cell px-5 py-3.5 text-[13px] text-gray-500 dark:text-slate-400">{{ i + 1 }}</td>
                            <td class="px-5 py-3.5 text-[13px] text-gray-700 dark:text-slate-200">
                                {{ `${game.c1name} ${game.state === 'finalizado' ? `(${game.gols1 ?? 0})` : ''}` }}
                                <div class="sm:hidden mt-1 flex items-center justify-center gap-2">
                                    <span :class="gameState(game.state).badge"
                                        class="px-1.5 py-px rounded text-[10px] font-medium">{{ gameState(game.state).label }}</span>
                                    <span>{{ `${game.c2name} ${game.state === 'finalizado' ? `(${game.gols2 ?? 0})` : ''}` }}</span>
                                </div>
                            </td>
                            <td class="hidden sm:table-cell px-5 py-3.5">
                                <span :class="gameState(game.state).badge"
                                    class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium">{{ gameState(game.state).label }}</span>
                            </td>
                            <td class="hidden sm:table-cell px-5 py-3.5 text-[13px] text-gray-700 dark:text-slate-200">{{ `${game.c2name} ${game.state === 'finalizado' ? `(${game.gols2 ?? 0})` : ''}` }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1">
                                    <Link v-if="game.state === 'creado' || game.state === 'planificado'"
                                        :href="route('games.select-players', game.id)"
                                        aria-label="Iniciar partido" title="Iniciar partido"
                                        class="btn-icon btn-icon-success">
                                        <i class="fa-solid fa-play text-[11px]"></i>
                                    </Link>
                                    <Link v-else :href="route('playing.index', game.id)"
                                        :aria-label="game.state === 'jugando' ? 'Continuar partido' : 'Ver partido'" :title="game.state === 'jugando' ? 'Continuar partido' : 'Ver partido'"
                                        class="btn-icon btn-icon-success">
                                        <i class="far fa-futbol text-[11px]"></i>
                                    </Link>
                                    <Link :href="route('games.edit', game.id)" aria-label="Editar partido" title="Editar partido"
                                        class="btn-icon">
                                        <i class="fa fa-edit text-[11px]"></i>
                                    </Link>
                                    <button type="button" @click="$event => deleteGame(game)" aria-label="Eliminar partido" title="Eliminar partido"
                                        class="btn-icon btn-icon-danger">
                                        <i class="fa fa-trash text-[11px]"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Sin partidos: guía según lo que falta -->
                <template v-if="games.length === 0">
                    <!-- El filtro de grupo no encontró nada -->
                    <EmptyState v-if="search !== ''" icon="fa-solid fa-filter" title="Este grupo no tiene partidos"
                        description="Prueba con otro grupo o quita el filtro.">
                        <button type="button" @click="search = ''" class="btn-add">Ver todos los grupos</button>
                    </EmptyState>

                    <!-- Faltan clubes -->
                    <EmptyState v-else-if="clubs_count < 2" icon="fa-solid fa-shield-halved" title="Primero registra los clubes"
                        :description="`Un partido necesita dos clubes y esta categoría tiene ${clubs_count}. Regístralos y vuelve para programar los partidos.`">
                        <Link :href="route('clubs.index', category.id)" class="btn-primary">
                            <i class="fa-solid fa-plus text-[11px]"></i> Registrar clubes
                        </Link>
                    </EmptyState>

                    <!-- Listo para crear -->
                    <div v-else class="px-4 sm:px-6 py-8">
                        <div class="text-center mb-6">
                            <h3 class="text-[15px] font-semibold tracking-tight text-gray-900 dark:text-white">Aún no hay partidos</h3>
                            <p class="mt-1 text-[13px] text-gray-500 dark:text-slate-400">Elige cómo quieres crearlos.</p>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-3 max-w-2xl mx-auto text-left">
                            <!-- Generar todos -->
                            <component :is="groups.length ? Link : 'div'" :href="groups.length ? route('games.vgenerate', category.id) : undefined"
                                class="group relative flex flex-col rounded-xl border p-4 sm:p-5 transition duration-150"
                                :class="groups.length
                                    ? 'border-emerald-500/30 bg-emerald-500/[0.04] hover:bg-emerald-500/[0.08] active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40'
                                    : 'border-gray-200 dark:border-white/[0.06]'">
                                <div class="flex items-center gap-3">
                                    <span class="w-10 h-10 shrink-0 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                                        <i class="fa-solid fa-wand-magic-sparkles text-emerald-500 dark:text-emerald-400 text-sm"></i>
                                    </span>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Generar la fase de grupos</p>
                                        <p class="text-[11px] font-medium text-emerald-600 dark:text-emerald-400">Recomendado para empezar</p>
                                    </div>
                                </div>
                                <p class="mt-3 text-[13px] text-gray-500 dark:text-slate-400">
                                    Crea de una vez todos los partidos: cada club juega contra los demás de su grupo.
                                </p>
                                <p v-if="!groups.length" class="mt-3 text-[12px] text-amber-700 dark:text-amber-300">
                                    Necesitas grupos con clubes asignados.
                                    <Link :href="route('groups.index', category.id)" class="font-semibold underline underline-offset-2">Crear grupos</Link>
                                </p>
                            </component>

                            <!-- Uno por uno -->
                            <Link :href="route('games.create', category.id)"
                                class="group flex flex-col rounded-xl border border-gray-200 dark:border-white/[0.06] p-4 sm:p-5 transition duration-150 hover:border-emerald-500/30 dark:hover:border-emerald-500/30 active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40">
                                <div class="flex items-center gap-3">
                                    <span class="w-10 h-10 shrink-0 rounded-lg bg-gray-100 dark:bg-slate-900/80 flex items-center justify-center">
                                        <i class="fa-solid fa-plus text-gray-500 dark:text-slate-400 text-sm"></i>
                                    </span>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Crear un partido</p>
                                </div>
                                <p class="mt-3 text-[13px] text-gray-500 dark:text-slate-400">
                                    Elige los dos clubes, la fecha y la fase. Útil para amistosos, octavos, semifinales o la final.
                                </p>
                            </Link>
                        </div>
                    </div>
                </template>

            </div>
        </div>
    </AdminLayout>
</template>
