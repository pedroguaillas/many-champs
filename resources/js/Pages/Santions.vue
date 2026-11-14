<script setup>

// Imports
import AdminLayout from '@/Layouts/AdminLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { Link, router } from '@inertiajs/vue3';
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
        text: `¿Está seguro de cobrar la tarjeta ${gi.santion} de ${gi.first_name} ${gi.last_name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Sí, cobrar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar',
        background: '#1e293b',
        color: '#e2e8f0',
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#334155',
    }).then((result) => {
        if (result.isConfirmed) {
            router.patch(route('gameitems.patch', gi.id),
                { patch: 'santion' },
                {
                    onError: () => {
                        Swal.fire({
                            title: 'No se pudo cobrar la sanción',
                            text: 'Vuelve a intentarlo.',
                            icon: 'error',
                            background: '#1e293b',
                            color: '#e2e8f0',
                            confirmButtonColor: '#10b981',
                        })
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
        text: `¿Está seguro de cobrar la tarjeta negra de ${gi.first_name} ${gi.last_name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Sí, cobrar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar',
        background: '#1e293b',
        color: '#e2e8f0',
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#334155',
    }).then((result) => {
        if (result.isConfirmed) {
            router.patch(route('gameitems.patch', gi.id),
                { patch: 'black' },
                {
                    onError: () => {
                        Swal.fire({
                            title: 'No se pudo cobrar la sanción',
                            text: 'Vuelve a intentarlo.',
                            icon: 'error',
                            background: '#1e293b',
                            color: '#e2e8f0',
                            confirmButtonColor: '#10b981',
                        })
                    }
                })
        }
    });
}

</script>

<template>
    <AdminLayout title="Sanciones">

        <!-- Card -->
        <div class="rounded-xl bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] overflow-hidden">

            <!-- Card header -->
            <div class="px-5 py-4 border-b border-gray-200 dark:border-white/[0.06]">
                <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white uppercase">Sanciones</h2>
            </div>

            <!-- Responsive -->
            <div class="w-full overflow-x-auto">
                <!-- Tabla -->
                <table v-if="sanctions.length > 0"
                    class="data-table table-auto w-full text-center">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-white/[0.06]">
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Jugador</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Club</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Tarjeta</th>
                            <th class="hidden sm:table-cell px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Categoría</th>
                            <th class="w-1 px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="sanction, i in sanctions" :key="i" class="border-b border-gray-100 dark:border-white/[0.04] hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150">
                            <td class="px-5 py-3 text-left text-[13px] text-gray-700 dark:text-slate-200">{{ `${sanction.first_name} ${sanction.last_name}` }}</td>
                            <td class="px-5 py-3 text-left text-[13px] text-gray-500 dark:text-slate-400">{{ sanction.player_club_id === sanction.c1id ? sanction.c1name :
                                sanction.c2name }}
                            </td>
                            <td class="px-5 py-3">
                                <span v-if="sanction.card_black" class="bg-slate-200 text-slate-900 text-[10px] px-2 py-0.5 rounded-md font-medium">
                                    negra
                                </span>
                                <span v-if="sanction.santion !== null" class="text-[10px] px-2 py-0.5 rounded-md font-medium"
                                    :class="sanction.santion === 'roja' ? 'bg-red-500/20 text-red-400' : 'bg-amber-500/20 text-amber-400'">
                                    {{ sanction.santion }}
                                </span>
                            </td>
                            <td class="hidden sm:table-cell px-5 py-3 text-[13px] text-gray-500 dark:text-slate-400">{{ sanction.category_name }}</td>
                            <td class="px-5 py-3">
                                <div class="flex gap-1">
                                    <button v-if="sanction.card_black && sanction.paid_black === null"
                                        @click="$event => payBlack(sanction)"
                                        title="Cobrar tarjeta negra" :aria-label="`Cobrar la tarjeta negra de ${sanction.first_name} ${sanction.last_name}`"
                                        class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-slate-200/20 text-gray-600 dark:text-slate-300 hover:bg-slate-200/30 transition-colors duration-200">
                                        <i class="fa fa-check text-xs"></i>
                                    </button>
                                    <button v-if="sanction.santion !== null && sanction.paid_santion === null"
                                        @click="$event => pay(sanction)"
                                        :title="`Cobrar tarjeta ${sanction.santion}`" :aria-label="`Cobrar la tarjeta ${sanction.santion} de ${sanction.first_name} ${sanction.last_name}`"
                                        class="inline-flex items-center justify-center w-10 h-10 rounded-lg transition-colors duration-200"
                                        :class="sanction.santion === 'roja' ? 'bg-red-500/20 text-red-400 hover:bg-red-500/30' : 'bg-amber-500/20 text-amber-400 hover:bg-amber-500/30'">
                                        <i class="fa fa-check text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Sin sanciones por cobrar -->
                <EmptyState v-else icon="fa-solid fa-circle-check" title="No hay sanciones por cobrar"
                    description="Aquí aparecen las tarjetas amarillas, rojas y negras que se registran durante los partidos, hasta que se cobran. Por ahora todos los clubes están al día.">
                    <Link :href="route('calendar')"
                        class="px-5 py-2.5 rounded-lg text-xs font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-700/40 transition duration-200">
                        Ver el calendario
                    </Link>
                </EmptyState>
            </div>
        </div>
    </AdminLayout>
</template>