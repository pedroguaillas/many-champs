<script setup>

// Imports
import { ref } from 'vue';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

// Props
const props = defineProps({
    game: { type: Object, default: () => { } },
    club1_players: { type: Array, default: () => [] },
    club2_players: { type: Array, default: () => [] }
})

// Reactives
const clubs1 = ref([]);
const clubs2 = ref([]);

const swalConfig = {
    background: '#1e293b',
    color: '#e2e8f0',
    confirmButtonColor: '#10b981',
    cancelButtonColor: '#334155',
};

// Seleccionar y deseccionar los jugadores
const active1 = (id) => {
    if (!clubs1.value.some(el => el === id)) {
        clubs1.value = [...clubs1.value, id];
    } else {
        clubs1.value = clubs1.value.filter(el => el !== id);
    }
}

const active2 = (id) => {
    if (!clubs2.value.some(el => el === id)) {
        clubs2.value = [...clubs2.value, id];
    } else {
        clubs2.value = clubs2.value.filter(el => el !== id);
    }
}

// Verificar si esta seleccionado
const contiene1 = (id) => {
    return clubs1 && clubs1.value.some(el => el === id)
}

const contiene2 = (id) => {
    return clubs2 && clubs2.value.some(el => el === id)
}

const save = () => {
    router.post(route('play.store', props.game.id),
        { clubs1: clubs1.value, clubs2: clubs2.value },
        {
            onError: () => {
                console.log('Error al seleccionar los jugadores')
            }
        })
}

const payBlack = (gi) => {
    Swal.fire({
        title: `Cobro de sanción`,
        text: `¿Esta seguro cobrar la targeta negra a ${gi.first_name} ${gi.last_name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Si, Cobrar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar',
        ...swalConfig,
    }).then((result) => {
        if (result.isConfirmed) {
            router.patch(route('gameitems.patch', gi.gi_back_id),
                { patch: 'black' },
                {
                    onError: () => {
                        console.log('Error al cobrar la sanción')
                    }
                })
        }
    });
}

const paySantion = (gi) => {
    Swal.fire({
        title: `Cobro de sanción`,
        text: `¿Esta seguro cobrar la targete ${gi.santion} de ${gi.first_name} ${gi.last_name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Si, Cobrar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar',
        ...swalConfig,
    }).then((result) => {
        if (result.isConfirmed) {
            router.patch(route('gameitems.patch', gi.gi_saction_id),
                { patch: 'santion' },
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
    <AdminLayout :title="'Seleccionar jugadores'">

        <!-- Club 1 -->
        <div class="rounded-xl bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] overflow-hidden">

            <!-- Card header -->
            <div class="px-5 py-4 border-b border-gray-200 dark:border-white/[0.06] flex justify-between items-center relative">
                <span class="absolute left-0 top-0 bottom-0 w-[3px] bg-emerald-500 rounded-r-full"></span>
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                        <i class="fa-solid fa-shield-halved text-emerald-400 text-[11px]"></i>
                    </div>
                    <h2 class="text-sm font-semibold tracking-tight text-white">{{ `${game.c1_name} ($${game.paid1 === null ? 0 : game.paid1})` }}</h2>
                </div>
            </div>

            <!-- Responsive -->
            <div class="w-full overflow-x-auto">
                <!-- Tabla -->
                <table v-if="club1_players.length > 0" class="w-full text-left">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 w-1">N°</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Jugador@s</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="c1, i in club1_players" :key="c1.id"
                            class="border-b border-gray-100 dark:border-white/[0.04] hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150">
                            <td class="px-5 py-3.5 text-[13px] text-gray-500 dark:text-slate-400">{{ i + 1 }}</td>
                            <td class="px-5 py-3.5 text-[13px] text-gray-700 dark:text-slate-200">{{ `${c1.first_name} ${c1.last_name}` }}</td>
                            <td class="px-5 py-3.5">
                                <template v-if="c1.black === 1 || c1.santion !== null">
                                    <div class="flex items-center gap-1">
                                        <button v-if="c1.black === 1" @click="$event => payBlack(c1)"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-gray-200 dark:bg-slate-700/50 text-gray-600 dark:text-slate-300 text-[11px] font-medium hover:bg-slate-700 transition-colors duration-150">
                                            <i class="fa fa-check"></i>
                                        </button>
                                        <button v-if="c1.santion !== null" @click="$event => paySantion(c1)"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[11px] font-medium transition-colors duration-150"
                                            :class="c1.santion === 'roja' ? 'bg-red-500/10 text-red-400 border border-red-500/20 hover:bg-red-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20 hover:bg-amber-500/20'">
                                            <i class="fa fa-check"></i>
                                        </button>
                                    </div>
                                </template>
                                <!-- Switch Container -->
                                <div v-else @click="$event => active1(c1.id)"
                                    :class="contiene1(c1.id) ? 'bg-emerald-500' : 'bg-slate-700'"
                                    class="w-10 h-6 flex items-center rounded-full p-1 cursor-pointer transition-colors duration-200">
                                    <div :class="{ 'translate-x-3': contiene1(c1.id) }"
                                        class="bg-white w-5 h-5 rounded-full shadow-md ease-out duration-200"></div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Club 2 -->
        <div class="mt-4 rounded-xl bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] overflow-hidden">

            <!-- Card header -->
            <div class="px-5 py-4 border-b border-gray-200 dark:border-white/[0.06] flex justify-between items-center relative">
                <span class="absolute left-0 top-0 bottom-0 w-[3px] bg-blue-500 rounded-r-full"></span>
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center">
                        <i class="fa-solid fa-shield-halved text-blue-400 text-[11px]"></i>
                    </div>
                    <h2 class="text-sm font-semibold tracking-tight text-white">{{ `${game.c2_name} ($${game.paid2 === null ? 0 : game.paid2})` }}</h2>
                </div>
            </div>

            <!-- Responsive -->
            <div class="w-full overflow-x-auto">
                <!-- Tabla -->
                <table v-if="club2_players.length > 0" class="w-full text-left">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 w-1">N°</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Jugador@s</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="c2, i in club2_players" :key="c2.id"
                            class="border-b border-gray-100 dark:border-white/[0.04] hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150">
                            <td class="px-5 py-3.5 text-[13px] text-gray-500 dark:text-slate-400">{{ i + 1 }}</td>
                            <td class="px-5 py-3.5 text-[13px] text-gray-700 dark:text-slate-200">{{ `${c2.first_name} ${c2.last_name}` }}</td>
                            <td class="px-5 py-3.5">
                                <template v-if="c2.black === 1 || c2.santion !== null">
                                    <div class="flex items-center gap-1">
                                        <button v-if="c2.black === 1" @click="$event => payBlack(c2)"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-gray-200 dark:bg-slate-700/50 text-gray-600 dark:text-slate-300 text-[11px] font-medium hover:bg-slate-700 transition-colors duration-150">
                                            <i class="fa fa-check"></i>
                                        </button>
                                        <button v-if="c2.santion !== null" @click="$event => paySantion(c2)"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[11px] font-medium transition-colors duration-150"
                                            :class="c2.santion === 'roja' ? 'bg-red-500/10 text-red-400 border border-red-500/20 hover:bg-red-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20 hover:bg-amber-500/20'">
                                            <i class="fa fa-check"></i>
                                        </button>
                                    </div>
                                </template>
                                <!-- Switch Container -->
                                <div v-else @click="$event => active2(c2.id)"
                                    :class="contiene2(c2.id) ? 'bg-emerald-500' : 'bg-slate-700'"
                                    class="w-10 h-6 flex items-center rounded-full p-1 cursor-pointer transition-colors duration-200">
                                    <div :class="{ 'translate-x-3': contiene2(c2.id) }"
                                        class="bg-white w-5 h-5 rounded-full shadow-md ease-out duration-200"></div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            <button @click="save"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-emerald-500 text-xs font-semibold text-white hover:bg-emerald-400 transition-all duration-200">
                <i class="far fa-futbol text-[11px]"></i> Jugar
            </button>
        </div>
    </AdminLayout>
</template>
