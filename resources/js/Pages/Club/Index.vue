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
        <div class="p-4 bg-white rounded drop-shadow-md">

            <!-- Card Header -->
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold">Clubes</h2>
                <select v-if="groups.length > 0" v-model="search">
                    <option value="">Todos</option>
                    <option v-for="gr in groups" :value="gr.name">{{ gr.name }}</option>
                </select>
                <button @click="newCub" class="px-2 bg-green-500 text-2xl text-white rounded font-bold">
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
                            <th>Nombre</th>
                            <th>Grupo</th>
                            <th>Lugar</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="club, i in clubs" :key="club.id" class="border-t [&>td]:py-2">
                            <td>{{ i + 1 }}</td>
                            <td>{{ club.name }}</td>
                            <td>{{ club.gname }}</td>
                            <td>{{ club.address }}</td>
                            <td>
                                <div class="relative inline-flex [&>a>i]:text-white [&>button>i]:text-white">
                                    <Link :href="route('payments.index', club.id)"
                                        class="mr-1 rounded px-2 py-1 bg-slate-500">
                                    <i class="fas fa-money-bill-wave"></i>
                                    </Link>
                                    <Link :href="route('players.index', club.id)" class="rounded px-2 py-1 bg-green-500">
                                    <i class="fa fa-users"></i>
                                    </Link>
                                    <button @click="edit(club)" class="mx-1 rounded px-2 py-1 bg-blue-500">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button @click="deleteClub(club)" class="rounded px-2 py-1 bg-red-500">
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

    <ModalClub :show="modal" :club="club" :error="errorClub" :groups="groups" @close="toggle" @save="save" />
</template>