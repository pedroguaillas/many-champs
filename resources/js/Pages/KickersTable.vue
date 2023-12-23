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
        <div class="p-4 bg-white rounded drop-shadow-md">

            <!-- Card header -->
            <div class="flex justify-between items-center">
                <h2 class="text-sm sm:text-lg font-bold uppercase">TABLA DE GOLEADORES {{ category.name }}</h2>
            </div>

            <!-- Resposive -->
            <div class="w-full overflow-x-auto">
                <!-- Tabla -->
                <table v-if="players.data.length > 0"
                    class="mt-4 text-xs sm:text-sm table-auto w-full text-center text-gray-700">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Jugador</th>
                            <th>Club</th>
                            <th>Gol</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="player, i in players.data">
                            <td class="w-4 sm:w-6 lg:w-10 py-2">{{ i + 1 }}</td>
                            <td>{{ `${player.first_name} ${player.last_name}` }}</td>
                            <td>{{ player.club }}</td>
                            <td>{{ player.goals ?? 0 }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="players.links" />

        </div>
    </AdminLayout>
</template>