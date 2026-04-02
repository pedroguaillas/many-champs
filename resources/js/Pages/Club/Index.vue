<script setup>

// Imports
import ModalClub from './ModalClub.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router, Link } from '@inertiajs/vue3';
import { reactive, ref, watch } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

// Props
const props = defineProps({
    category: { type: Object, default: () => { } },
    clubs: { type: Array, default: () => [] },
    groups: { type: Array, default: () => [] }
});

// Refs
const modal = ref(false);

const initialClub = { name: '', address: '', group_id: 0, extra_points: '' }

// Reactives
const club = reactive({ ...initialClub });
const errorClub = reactive({ ...initialClub });

// Seccion filtro ..... Inicio
const search = ref('');

const url = route('clubs.index', props.category.id);

watch(search, (value) => {
    router.get(url, { search: value }, { preserveState: true, preserveScroll: true, only: ['clubs'] })
}, 300);
// Seccion filtro ..... Fin

const newCub = () => {
    // Reinicio el formularios con valores vacios
    if (club.id !== undefined) {
        delete club.id
    }
    Object.assign(club, initialClub);
    // Muestro el modal
    toggle();
}

const resetErrorForm = () => {
    Object.assign(errorClub, initialClub);
}

const toggle = () => {
    modal.value = !modal.value;
}

const save = () => {
    if (club.id === undefined) {
        const data = { ...club, category_id: props.category.id, group_id: club.group_id > 0 ? club.group_id : null, extra_points: club.extra_points === '' ? 0 : club.extra_points }
        axios
            .post(route('clubs.store'), data)
            .then(() => {
                toggle();
                resetErrorForm();

                router.reload({ only: ['clubs'] });
            }).catch(error => {
                resetErrorForm();

                Object.keys(error.response.data.errors).forEach(key => {
                    errorClub[key] = error.response.data.errors[key][0]
                });
            })
    } else {
        axios
            .put(route('clubs.update', club.id), { ...club, extra_points: club.extra_points === '' ? 0 : club.extra_points })
            .then(() => {
                toggle();
                resetErrorForm();

                router.reload({ only: ['clubs'] });
            }).catch(error => {
                resetErrorForm();

                Object.keys(error.response.data.errors).forEach(key => {
                    errorClub[key] = error.response.data.errors[key][0]
                });
            })
    }
}

const edit = (clubEdit) => {
    Object.keys(clubEdit).forEach(key => {
        if (key === 'extra_points') {
            // Para convertir a string extra_points
            club.extra_points = '' + (clubEdit[key] === 0 ? '' : clubEdit[key])
        } else {
            club[key] = clubEdit[key]
        }
    });
    toggle();
}

const deleteClub = (clubDelete) => {

    const alert = Swal.mixin({
        buttonsStyling: true
    })

    if (clubDelete.game_id !== null || clubDelete.payment_id !== null || clubDelete.player_id !== null) {
        alert.fire({
            title: 'No se permite anular el club, ya que puede contener juegos, jugadores o pagos',
            icon: 'error'
        })
        return
    }

    alert.fire({
        title: `¿Esta seguro eliminar el club ${clubDelete.name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Si, Eliminar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            axios
                .delete(route('clubs.destroy', clubDelete.id))
                .then(() => {
                    router.reload({ only: ['clubs'] })
                }).catch(error => {
                    console.log(error.response.data.msm)
                })
        }
    });
}

</script>

<template>
    <AdminLayout title="Clubes">

        <!-- Card -->
        <div class="rounded-xl bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] overflow-hidden">

            <!-- Card Header -->
            <div class="px-5 py-4 border-b border-gray-200 dark:border-white/[0.06] flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center">
                        <i class="fa-solid fa-shield-halved text-blue-400 text-[11px]"></i>
                    </div>
                    <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Clubes</h2>
                </div>
                <div class="flex items-center gap-3">
                    <select v-if="groups.length > 0" v-model="search"
                        class="px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] text-[12px] text-gray-600 dark:text-slate-300 focus:outline-none focus:border-emerald-500/40">
                        <option value="">Todos</option>
                        <option v-for="gr in groups" :value="gr.name">{{ gr.name }}</option>
                    </select>
                    <button @click="newCub"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-emerald-500/10 text-emerald-400 text-xs font-medium border border-emerald-500/20 hover:bg-emerald-500/20 hover:border-emerald-500/30 transition-all duration-200">
                        <i class="fa-solid fa-plus text-[11px]"></i> Agregar
                    </button>
                </div>
            </div>

            <!-- Responsive -->
            <div class="w-full overflow-x-auto">
                <!-- Tabla -->
                <table class="w-full text-left">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 w-1">N°</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Nombre</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Grupo</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Lugar</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="club, i in clubs" :key="club.id"
                            class="border-b border-gray-100 dark:border-white/[0.04] hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150">
                            <td class="px-5 py-3.5 text-[13px] text-gray-500 dark:text-slate-400">{{ i + 1 }}</td>
                            <td class="px-5 py-3.5 text-[13px] text-gray-700 dark:text-slate-200">{{ club.name }}</td>
                            <td class="px-5 py-3.5 text-[13px] text-gray-500 dark:text-slate-400">{{ club.gname }}</td>
                            <td class="px-5 py-3.5 text-[13px] text-gray-500 dark:text-slate-400">{{ club.address }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1">
                                    <Link :href="route('payments.index', club.id)"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 dark:text-slate-500 hover:text-emerald-400 hover:bg-emerald-500/10 transition-colors duration-150">
                                        <i class="fas fa-money-bill-wave text-[11px]"></i>
                                    </Link>
                                    <Link :href="route('players.index', club.id)"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 dark:text-slate-500 hover:text-emerald-400 hover:bg-emerald-500/10 transition-colors duration-150">
                                        <i class="fa fa-users text-[11px]"></i>
                                    </Link>
                                    <button @click="edit(club)"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 dark:text-slate-500 hover:text-gray-700 dark:hover:text-gray-700 dark:text-slate-200 hover:bg-gray-200 dark:bg-slate-700/50 transition-colors duration-150">
                                        <i class="fa fa-edit text-[11px]"></i>
                                    </button>
                                    <button @click="deleteClub(club)"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 dark:text-slate-500 hover:text-red-400 hover:bg-red-500/10 transition-colors duration-150">
                                        <i class="fa fa-trash text-[11px]"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>

    <ModalClub :show="modal" :club="club" :error="errorClub" :groups="groups" @close="toggle" @save="save" />
</template>