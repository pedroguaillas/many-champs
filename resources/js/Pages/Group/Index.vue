<script setup>

// Imports
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ModalGroup from './ModalGroup.vue';
import { router, useForm } from '@inertiajs/vue3'
import { reactive, ref } from 'vue';
import Swal from 'sweetalert2';

// Props
const props = defineProps({
    category: { type: Object, default: () => { } },
    groups: { type: Array, default: () => [] },
});

// Refs
const modal = ref(false);

const initialGroup = { name: '' }

// Forms

// Reactives
const group = reactive({ ...initialGroup });
const errors = reactive({ ...initialGroup });

const newGroup = () => {
    if (group.id !== undefined) {
        delete group.id
    }
    Object.assign(group, initialGroup);
    toggle();
}

const resetError = () => {
    Object.assign(errors, initialGroup);
}

const toggle = () => {
    modal.value = !modal.value
}

const save = () => {

    if (group.id === undefined) {
        axios
            .post(route('groups.store'), { category_id: props.category.id, ...group })
            .then(() => {
                toggle();
                resetError();

                router.reload({ only: ['groups'] });
            }).catch(error => {
                resetError();

                Object.keys(error.response.data.errors).forEach(key => {
                    errors[key] = error.response.data.errors[key][0]
                });
            })
    } else {
        axios
            .put(route('groups.update', group.id), group)
            .then(() => {
                toggle();
                resetError();

                router.reload({ only: ['groups'] });
            }).catch(error => {
                resetError();

                Object.keys(error.response.data.errors).forEach(key => {
                    errors[key] = error.response.data.errors[key][0]
                });
            })
    }
}

const edit = (item) => {
    Object.keys(item).forEach(key => {
        group[key] = item[key]
    });
    toggle();
}

// utilizado para eliminar una categoría
const form = useForm({ id: '' });

const deleteGroup = (item) => {
    const alert = Swal.mixin({
        buttonsStyling: true
    });
    alert.fire({
        title: `¿Esta seguro eliminar el grupo ${item.name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Si, Eliminar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            form.delete(route('groups.destroy', item.id))
                .then(() => {
                    router.reload({ only: ['groups'] });
                });
        }
    });
}

</script>

<template>
    <AdminLayout :title="`Grupos ${props.category.name} ${props.category.gender}`">

        <!-- Card -->
        <div class="p-4 bg-white rounded drop-shadow-md">

            <!-- Card header -->
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold">{{ `Grupos ${props.category.name} ${props.category.gender}` }}</h2>
                <button @click="newGroup" class="px-2 bg-green-500 text-2xl text-white rounded font-bold">
                    +
                </button>
            </div>

            <!-- Resposive -->
            <div class="w-full overflow-x-auto">
                <!-- Tabla -->
                <table class="mt-4 text-sm sm:text-xs table-auto w-full text-center text-gray-700">
                    <thead>
                        <tr class="[&>th]:py-2">
                            <th>N°</th>
                            <th>Grupo</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item, i in groups" :key="item.id" class="border-t [&>td]:py-2">
                            <td>{{ i + 1 }}</td>
                            <td>{{ item.name }}</td>
                            <td>
                                <div class="relative inline-flex [&>a>i]:text-white [&>button>i]:text-white">
                                    <button @click="edit(item)" class="rounded px-2 py-1 bg-blue-500">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button @click="$event => deleteGroup(item)" class="rounded px-2 py-1 ml-1 bg-red-500">
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

    <ModalGroup :show="modal" :group="group" :error="errors" @close="toggle" @save="save" />
</template>
