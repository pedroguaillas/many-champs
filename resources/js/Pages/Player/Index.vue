<script setup>

// Imports
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ModalPlayer from './ModalPlayer.vue';
import ModalViewPlayer from './ModalViewPlayer.vue';
import { router, useForm } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

// Props
const props = defineProps({
    auth: { type: Object, default: () => ({}) },
    club: { type: Object, default: () => { } },
    players: { type: Array, default: () => [] }
})

// Refs
const modal = ref(false);
const modalView = ref(false);

const initialPlayer = {
    cedula: '',
    first_name: '',
    last_name: '',
    date_of_birth: '',
    t_shirt: '',
    phone: '',
    photo: null
}

// Reactive
const player = reactive({ ...initialPlayer });
const errors = reactive({ ...initialPlayer });

const newPlayer = () => {
    if (player.id !== undefined) {
        delete player.id
    }
    Object.assign(player, initialPlayer);
    toggle();
}

const resetError = () => {
    Object.assign(errors, initialPlayer);
}

const toggle = () => {
    modal.value = !modal.value
}

const loadImage = (file) => {
    player.photo = file
}

const save = () => {

    if (player.id === undefined) {
        const data = { ...player, club_id: props.club.id }
        axios
            .post(route('players.store'), data, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
            .then(() => {
                toggle();
                resetError();

                router.reload({ only: ['players'] });
            }).catch(error => {
                resetError();

                Object.keys(error.response.data.errors).forEach(key => {
                    errors[key] = error.response.data.errors[key][0]
                });
            })
    } else {
        axios
            .put(route('players.update', player.id), player)
            .then(() => {
                toggle();
                resetError();

                router.reload({ only: ['players'] });
            }).catch(error => {
                resetError();

                Object.keys(error.response.data.errors).forEach(key => {
                    errors[key] = error.response.data.errors[key][0]
                });
            })
    }
}

const edit = (playerEdit) => {
    resetError();
    Object.keys(playerEdit).forEach(key => {
        player[key] = playerEdit[key]
    });
    toggle();
}

// Utilizado para eliminar un club
const form = useForm({ id: '' });

const deletePlayer = (player) => {

    const alert = Swal.mixin({
        buttonsStyling: true
    });

    alert.fire({
        title: `¿Esta seguro eliminar el jugador ${player.first_name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Si, Eliminar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar',
        background: '#1e293b',
        color: '#e2e8f0',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#334155',
    }).then((result) => {
        if (result.isConfirmed) {
            form.delete(route('players.destroy', player.id), {
                onFinish: () => router.reload({ only: ['players'] })
            })
        }
    });

}

const toggleView = () => {
    modalView.value = !modalView.value
}

const viewPlayer = (playerView) => {
    Object.keys(playerView).forEach(key => {
        player[key] = playerView[key]
    });
    toggleView();
}

</script>

<template>
    <AdminLayout :title="`Jugadores de ${club.name}`">

        <!-- Card -->
        <div class="rounded-xl bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] overflow-hidden">

            <!-- Card Header -->
            <div class="px-5 py-4 border-b border-gray-200 dark:border-white/[0.06] flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center">
                        <i class="fa fa-users text-blue-400 text-[11px]"></i>
                    </div>
                    <h2 class="text-sm font-semibold tracking-tight text-white">{{ `Jugadores de ${club.name}` }}</h2>
                </div>
                <button @click="newPlayer"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-emerald-500/10 text-emerald-400 text-xs font-medium border border-emerald-500/20 hover:bg-emerald-500/20 hover:border-emerald-500/30 transition-all duration-200">
                    <i class="fa fa-user-plus text-[11px]"></i> Agregar
                </button>
            </div>

            <!-- Responsive -->
            <div class="w-full overflow-x-auto">
                <!-- Tabla -->
                <table class="w-full text-center">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 w-1">N°</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500"></th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Nombre</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Apellido</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Dorsal</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="player, i in players" :key="player.id"
                            class="border-b border-gray-100 dark:border-white/[0.04] hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150">
                            <td class="px-5 py-3.5 text-[13px] text-gray-500 dark:text-slate-400">{{ i + 1 }}</td>
                            <td class="px-5 py-3.5 w-10">
                                <img v-if="player.photo" :src="`/storage/avatars/${player.photo}`"
                                    class="w-8 h-8 rounded-lg object-cover border border-gray-200 dark:border-white/[0.06]" alt="Avatar" />
                            </td>
                            <td class="px-5 py-3.5 text-[13px] text-gray-700 dark:text-slate-200">{{ player.first_name }}</td>
                            <td class="px-5 py-3.5 text-[13px] text-gray-700 dark:text-slate-200">{{ player.last_name }}</td>
                            <td class="px-5 py-3.5 text-[13px] text-gray-500 dark:text-slate-400">{{ player.t_shirt }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1">
                                    <button @click="viewPlayer(player)"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 dark:text-slate-500 hover:text-amber-400 hover:bg-amber-500/10 transition-colors duration-150">
                                        <i class="fa fa-eye text-[11px]"></i>
                                    </button>
                                    <button @click="edit(player)"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 dark:text-slate-500 hover:text-gray-700 dark:hover:text-gray-700 dark:text-slate-200 hover:bg-gray-200 dark:hover:bg-gray-200 dark:bg-slate-700/50 transition-colors duration-150">
                                        <i class="fa fa-user-edit text-[11px]"></i>
                                    </button>
                                    <button @click="deletePlayer(player)"
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

    <ModalPlayer :player="player" :error="errors" :show="modal" @close="toggle" @save="save" @loadImage="loadImage" />
    <ModalViewPlayer :show="modalView" :player="player" :auth="auth" :club="club" @close="toggleView" />
</template>
