<script setup>

// Imports
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link } from '@inertiajs/vue3';

// Props
defineProps({
    games: { type: Array, default: () => { } }
})

</script>

<template>
    <AdminLayout title="Calendario">

        <!-- Card -->
        <div class="p-4 bg-white rounded drop-shadow-md">

            <!-- Card header -->
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold">Calendario de juegos</h2>
                <!-- <button @click="newGroup" class="px-2 bg-green-500 text-2xl text-white rounded font-bold">
                    +
                </button> -->
            </div>

            <!-- Resposive -->
            <div class="w-full overflow-x-auto">
                <!-- Tabla -->
                <table v-if="games.length > 0" class="mt-4 text-sm sm:text-xs table-auto w-full text-center text-gray-700">
                    <tbody>
                        <tr class="[&>th]:py-2">
                            <th colspan="8" class="text-center">{{ games[0].date }}</th>
                        </tr>
                        <template v-for="game, i in games" :key="i">

                            <tr v-if="i > 0 && games[i - 1].date !== game.date" class="[&>th]:py-2">
                                <th colspan="8" class="text-center">{{ game.date }}</th>
                            </tr>

                            <tr class="[&>td]:py-2">
                                <td>{{ i + 1 }}</td>
                                <td class="text-center">{{ game.c1name }}</td>
                                <td class="text-center">
                                    <span class="p-1"
                                        :class="game.state === 'finalizado' ? 'bg-green-500' : (game.state === 'planificado' ? 'bg-yellow-500' : '')">VS</span>
                                </td>
                                <td>{{ game.c2name }}</td>
                                <td>{{ game.time.substring(0, 2) }}</td>
                                <td>{{ `${game.gname} | ${game.name}` }}</td>
                                <td title="Jugar" class="w-1 text-white">
                                    <Link v-if="game.state === 'creado' || game.state === 'planificado'"
                                        :href="route('games.select-players', game.id)"
                                        class="rounded px-2 py-2 bg-green-500">
                                    <i class="far fa-futbol"></i>
                                    </Link>
                                    <Link v-else :href="route('playing.index', game.id)"
                                        class="rounded px-2 py-2 bg-green-500">
                                    <i class="far fa-futbol"></i>
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
