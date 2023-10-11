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
        <div class="p-4 bg-white rounded drop-shadow-md">

            <!-- Card header -->
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold uppercase">TABLA DE POSICIONES {{ category.name }}</h2>
            </div>

            <!-- Resposive -->
            <div class="w-full overflow-x-auto">
                <!-- Tabla -->
                <table v-if="clubs.length > 0" class="mt-4 text-sm sm:text-xs table-auto w-full text-center text-gray-700">
                    <!-- <table class="table table-sm table-striped table-borderless"> -->
                    <tbody>

                        <template v-for="club, i in clubs" :key="club.id">

                            <tr v-if="i === 0 || club.group_id !== clubs[i - 1].group_id">
                                <th colspan="2" class="text-left">{{ `GRUPO ${club.group_name}` }}</th>
                                <th>PJ</th>
                                <th>G</th>
                                <th>E</th>
                                <th>P</th>
                                <th>GF</th>
                                <th>GC</th>
                                <th>DG</th>
                                <th>PE</th>
                                <th>Pts</th>
                            </tr>

                            <tr>
                                <td class="w-4 sm:w-6 lg:w-10 text-left py-2">{{ i + 1 }}</td>
                                <td class="text-left uppercase">{{ club.name }}</td>
                                <td>{{ club.ganado + club.empate + club.perdido }}</td>
                                <td>{{ club.ganado }}</td>
                                <td>{{ club.empate }}</td>
                                <td>{{ club.perdido }}</td>
                                <td>{{ club.golf ? club.golf : 0 }}</td>
                                <td>{{ club.golc ? club.golc : 0 }}</td>
                                <td>{{ club.golf - club.golc }}</td>
                                <td>{{ parseFloat(club.extra_points) }}</td>
                                <td>{{ club.extra_points + (club.ganado * 3) + club.empate }}
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>