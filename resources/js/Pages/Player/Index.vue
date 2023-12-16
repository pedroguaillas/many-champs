<script setup>

// Imports
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ModalPlayer from './ModalPlayer.vue';
import { router, useForm } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

// Props
const props = defineProps({
    club: { type: Object, default: () => { } },
    players: { type: Array, default: () => [] }
})

// Refs
const modal = ref(false);

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
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            form.delete(route('players.destroy', player.id), {
                onFinish: () => router.reload({ only: ['players'] })
            })
        }
    });

}

</script>

<template>
    <AdminLayout :title="`Jugadores de ${club.name}`">

        <!-- Card -->
        <div class="p-4 bg-white rounded drop-shadow-md">

            <!-- Card Header -->
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold">{{ `Jugadores de ${club.name}` }}</h2>
                <button @click="newPlayer" class="px-2 bg-green-500 text-2xl text-white rounded font-bold">
                    +
                </button>
            </div>

            <!-- Resposive -->
            <div class="w-full overflow-x-auto">
                <!-- Tabla -->
                <table class="mt-4 text-sm sm:text-xs table-auto w-full text-center text-gray-700">
                    <thead>
                        <tr class="[&>th]:py-2">
                            <th class="w-1">N°</th>
                            <th></th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Camiseta</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="player, i in players" :key="player.id" class="border-t [&>td]:py-2">
                            <td>{{ i + 1 }}</td>
                            <td class="w-10">
                                <img v-if="player.photo" :src="`/storage/avatars/${player.photo}`" class="rounded"
                                    alt="Avatar" />
                            </td>
                            <td>{{ player.first_name }}</td>
                            <td>{{ player.last_name }}</td>
                            <td>{{ player.t_shirt }}</td>
                            <td>
                                <div class="relative inline-flex [&>a>i]:text-white [&>button>i]:text-white">
                                    <button @click="edit(player)" class="mx-1 rounded px-2 py-1 bg-blue-500">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button @click="deletePlayer(player)" class="rounded px-2 py-1 bg-red-500">
                                        <i class="fa fa-trash"></i>
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
</template>
