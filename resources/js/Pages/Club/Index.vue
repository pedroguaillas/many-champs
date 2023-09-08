<script setup>
import ModalClub from '@/Components/admin/ModalClub.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
    category: { type: Object, default: () => { } },
    clubs: { type: Array, default: () => [] },
    groups: { type: Array, default: () => [] }
});

const processing = ref(false);
const modal = ref(false);

const initialStateClub = {
    name: '',
    address: ''
}

const club = reactive({ ...initialStateClub });
const errorClub = reactive({ ...initialStateClub });

const resetForm = () => {
    Object.assign(club, initialStateClub);
}

const resetErrorForm = () => {
    Object.assign(errorClub, initialStateClub);
}

const toggle = () => {
    modal.value = !modal.value;
}

const save = () => {
    processing.value = true;

    if (club.id === undefined) {
        axios
            .post(route('clubs.store'), { category_id: props.category.id, ...club })
            .then(() => {
                processing.value = false;

                toggle();
                resetForm();
                resetErrorForm();

                router.reload({ only: ['clubs'] });
            }).catch(error => {
                processing.value = false;

                resetErrorForm();

                Object.keys(error.response.data.errors).forEach(key => {
                    errorClub[key] = error.response.data.errors[key][0]
                });
            })
    } else {
        axios
            .put(route('clubs.update', club.id), club)
            .then(() => {
                processing.value = false;

                toggle();
                resetForm();
                resetErrorForm();

                router.reload({ only: ['clubs'] });
            }).catch(error => {
                processing.value = false;

                resetErrorForm();

                Object.keys(error.response.data.errors).forEach(key => {
                    errorClub[key] = error.response.data.errors[key][0]
                });
            })
    }
}

const edit = (clubEdit) => {
    Object.keys(clubEdit).forEach(key => {
        club[key] = clubEdit[key]
    });
    toggle();
}

// Utilizado para eliminar un club
const form = useForm({ id: '' });

const deleteClub = (id, name) => {

    const alert = Swal.mixin({
        buttonsStyling: true
    });

    alert.fire({
        title: `¿Esta seguro eliminar el club ${name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Si, Eliminar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            form.delete(route('clubs.destroy', id))
                .then(() => {
                    router.reload({ only: ['clubs'] });
                });
        }
    });
}

</script>

<template>
    <AdminLayout :title="'Clubes'">

        <!-- Card -->
        <div class="m-4 p-4 bg-white rounded drop-shadow-md">

            <!-- Card Header -->
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold">Clubes</h2>
                <button @click="toggle"
                    class="px-2 bg-green-500 text-2xl text-white rounded font-bold">
                    +
                </button>
            </div>

            <!-- Resposive -->
            <div class="w-full overflow-x-auto">
                <!-- Tabla -->
                <table class="mt-4 text-sm sm:text-xs table-auto w-full text-center text-gray-700">
                    <thead>
                        <tr class="[&>th]:py-2">
                            <th>Nombre</th>
                            <th>Grupo</th>
                            <th>Lugar</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="club in clubs" :key="club.id" class="border-t [&>td]:py-2">
                            <td>{{ club.name }}</td>
                            <td>{{ club.group_name }}</td>
                            <td>{{ club.address }}</td>
                            <td>
                                <div class="relative inline-flex [&>button>i]:text-white">
                                    <button @click="edit(club)" class="rounded px-2 py-1 bg-blue-500">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button @click="deleteClub(club.id, club.name)"
                                        class="rounded px-2 py-1 ml-1 bg-red-500">
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

    <ModalClub :show="modal" :club="club" :error="errorClub" @close="toggle" @save="save" /></template>