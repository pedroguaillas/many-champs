<script setup>

// Imports
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

// Props
defineProps({
    sanctions: { type: Array, default: () => [] }
});

const pay = (gi) => {

    const alert = Swal.mixin({
        buttonsStyling: true
    });

    alert.fire({
        title: `Cobro de sanción`,
        text: `¿Esta seguro cobrar la targete ${gi.santion} de ${gi.first_name} ${gi.last_name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Si, Cobrar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            router.patch(route('gameitems.patch', gi.id),
                { patch: 'santion' },
                {
                    onError: () => {
                        console.log('Error al cobrar la sanción')
                    }
                })
        }
    });
}

const payBlack = (gi) => {

    const alert = Swal.mixin({
        buttonsStyling: true
    });

    alert.fire({
        title: `Cobro de sanción`,
        text: `¿Esta seguro cobrar la targete negra de ${gi.first_name} ${gi.last_name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Si, Cobrar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            router.patch(route('gameitems.patch', gi.id),
                { patch: 'black' },
                {
                    onError: () => {
                        console.log('Error al cobrar la sanción')
                    }
                })
        }
    });
}

</script>

<template>
    <AdminLayout title="Sanciones">

        <!-- Card -->
        <div class="p-4 bg-white rounded drop-shadow-md">

            <!-- Card header -->
            <div class="flex justify-between items-center">
                <h2 class="text-sm sm:text-lg font-bold uppercase">Sanciones</h2>
            </div>

            <!-- Resposive -->
            <div class="w-full overflow-x-auto">
                <!-- Tabla -->
                <table v-if="sanctions.length > 0"
                    class="mt-4 text-xs sm:text-sm table-auto w-full text-center text-gray-700">
                    <thead>
                        <tr>
                            <th>Jugador</th>
                            <th>Club</th>
                            <th>Tarjeta</th>
                            <th>Categoría</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="sanction in sanctions">
                            <td class="text-left">{{ `${sanction.first_name} ${sanction.last_name}` }}</td>
                            <td class="text-left">{{ sanction.player_team_id === sanction.c1id ? sanction.c1name :
                                sanction.c2name }}
                            </td>
                            <td>
                                <span v-if="sanction.card_black" class="bg-black p-1 rounded text-white">
                                    negra
                                </span>
                                <span v-if="sanction.santion !== null" class="p-1 rounded"
                                    :class="sanction.santion === 'roja' ? 'bg-red-500' : 'bg-yellow-500'">
                                    {{ sanction.santion }}
                                </span>
                            </td>
                            <td>{{ sanction.category_name }}</td>
                            <td>
                                <div class="flex">
                                    <button v-if="sanction.card_black === 1 && sanction.paid_black === null"
                                        @click="$event => payBlack(sanction)"
                                        class="rounded px-2 py-1 ml-1 text-sm text-white bg-slate-500">
                                        <i class="fa fa-check"></i>
                                    </button>
                                    <button v-if="sanction.santion !== null && sanction.paid_santion === null"
                                        @click="$event => pay(sanction)" class="rounded px-2 py-1 ml-1 text-sm text-white"
                                        :class="sanction.santion === 'roja' ? 'bg-red-500' : 'bg-yellow-500'">
                                        <i class="fa fa-check"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>