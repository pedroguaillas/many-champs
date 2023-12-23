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
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar'
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
        <div class="p-4 bg-white rounded drop-shadow-md">

            <!-- Card header -->
            <div class="flex justify-between items-center">
                <h2 class="text-sm sm:text-lg font-bold">{{ `${category.name} ${category.gender}` }}</h2>
                <select v-if="groups.length > 0" v-model="search">
                    <option value="">Todos</option>
                    <option v-for="gr in groups" :value="gr.id">{{ gr.name }}</option>
                </select>
                <Link v-if="games.length > 0" :href="route('games.create', category.id)"
                    class="px-2 bg-green-500 text-2xl text-white rounded font-bold">
                +
                </Link>
            </div>

            <!-- Resposive -->
            <div class="w-full overflow-x-auto">
                <!-- Tabla -->
                <table v-if="games.length > 0" class="mt-4 text-xs sm:text-sm table-auto w-full text-center text-gray-700">
                    <thead>
                        <tr class="[&>th]:py-2">
                            <th class="w-1">N°</th>
                            <th>Equipo 1</th>
                            <th>VS</th>
                            <th>Equipo 2</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="game, i in games" :key="game.id" class="border-t [&>td]:py-2">
                            <td>{{ i + 1 }}</td>
                            <td>{{ `${game.c1name} ${game.state === 'finalizado' ? `(${game.gols1 ?? 0})` : ''}` }}</td>
                            <td>
                                <span
                                    :class="game.state === 'planificado' ? 'bg-yellow-500' : (game.state === 'finalizado' ? 'bg-green-500' : '')"
                                    class="rounded px-2 py-1">VS</span>
                            </td>
                            <td>{{ `${game.c2name} ${game.state === 'finalizado' ? `(${game.gols2 ?? 0})` : ''}` }}</td>
                            <td>
                                <div class="relative inline-flex [&>a>i]:text-white [&>button>i]:text-white">
                                    <Link v-if="game.state === 'creado' || game.state === 'planificado'"
                                        :href="route('games.select-players', game.id)"
                                        class="rounded px-2 py-2 bg-green-500">
                                    <i class="far fa-futbol"></i>
                                    </Link>
                                    <Link v-else :href="route('playing.index', game.id)"
                                        class="rounded px-2 py-2 bg-green-500">
                                    <i class="far fa-futbol"></i>
                                    </Link>
                                    <Link :href="route('games.edit', game.id)" class="rounded p-2 mx-1 bg-blue-500">
                                    <i class="fa fa-edit"></i>
                                    </Link>
                                    <button @click="$event => deleteGame(game)" class="rounded px-2 py-1 bg-red-500">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Mstrar si no hay ningun partido creado -->
                <p v-if="games.length === 0" class="mt-4">
                    Para crear los partidos de fase de grupos haz clic
                    <Link :href="route('games.vgenerate', category.id)" class="text-indigo-500">aqui</Link>
                    , o para crear el primer partido individual haz clic
                    <Link :href="route('games.create', category.id)" class="text-indigo-500">aqui</Link>
                </p>

            </div>
        </div>
    </AdminLayout>
</template>