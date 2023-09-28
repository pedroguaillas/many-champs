<script setup>

// Imports
import AdminLayout from '../../Layouts/AdminLayout.vue';

// Props
const props = defineProps({
    game: { type: Object, default: () => { } },
    c1_players: { type: Array, default: () => [] },
    c2_players: { type: Array, default: () => [] }
})

</script>

<template>
    <AdminLayout :title="'Seleccionar jugadores'">
        <div class="p-4 bg-white rounded drop-shadow-md">

            <!-- Card header -->
            <div class="flex justify-between items-center bg-blue-400">
                <h2 class="text-xl font-bold">{{ `${game.c1_name} ()` }}</h2>
            </div>

            <!-- Resposive -->
            <div class="w-full overflow-x-auto">
                <!-- Tabla -->
                <table v-if="c1_players.length > 0"
                    class="mt-4 text-sm sm:text-xs table-auto w-full text-gray-700 [&>thead>tr>th]:text-left">
                    <tbody>
                        <tr v-for="c1, i in c1_players" :key="c1.id" class="border-t [&>td]:py-2">
                            <td>{{ `${c1.first_name} ${c1.last_name}` }}</td>
                        </tr>
                    </tbody>
                </table>

            </div>
        </div>

        <div class="mt-4 p-4 bg-white rounded drop-shadow-md">

            <!-- Card header -->
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold">{{ `${game.c2_name} ()` }}</h2>
            </div>

            <!-- Resposive -->
            <div class="w-full overflow-x-auto">
                <!-- Tabla -->
                <table v-if="c2_players.length > 0"
                    class="mt-4 text-sm sm:text-xs table-auto w-full text-gray-700 [&>thead>tr>th]:text-left">
                    <tbody>
                        <tr v-for="c2, i in c2_players" :key="c2.id" class="border-t [&>td]:py-2">
                            <td>{{ `${c2.first_name} ${c2.last_name}` }}</td>
                        </tr>
                    </tbody>
                </table>

            </div>
        </div>
    </AdminLayout>
</template>