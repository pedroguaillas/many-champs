<script setup>

// Imports
import ModalCategory from './ModalCategory.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router, useForm, Link } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

// Props
defineProps({
    categories: { type: Array, default: () => [] }
});

// Refs
const modal = ref(false);

const initialCategory = { name: '', inscription: '', gender: '' }

// Reactives
const category = reactive({ ...initialCategory });
const errorCategory = reactive({ ...initialCategory });

const newCategory = () => {
    if (category.id !== undefined) {
        delete category.id
    }
    Object.assign(category, initialCategory);
    toggle();
}

const resetErrorForm = () => {
    Object.assign(errorCategory, initialCategory);
}

const toggle = () => {
    modal.value = !modal.value
}

const save = () => {
    if (category.id === undefined) {
        axios
            .post(route('categories.store'), category)
            .then(() => {
                toggle();
                resetErrorForm();

                router.reload({ only: ['categories'] });
            }).catch(error => {
                resetErrorForm();

                Object.keys(error.response.data.errors).forEach(key => {
                    errorCategory[key] = error.response.data.errors[key][0]
                });
            })
    } else {
        axios
            .put(route('categories.update', category.id), category)
            .then(() => {
                toggle();
                resetErrorForm();

                router.reload({ only: ['categories'] });
            }).catch(error => {
                resetErrorForm();

                Object.keys(error.response.data.errors).forEach(key => {
                    errorCategory[key] = error.response.data.errors[key][0]
                });
            })
    }
}

const edit = (cat) => {
    Object.keys(cat).forEach(key => {
        category[key] = cat[key]
    });
    category.inscription = '' + parseFloat(category.inscription)
    toggle();
}

// utilizado para eliminar una categoría
const form = useForm({ id: '' });

const deleteCategory = (id, name) => {
    const alert = Swal.mixin({
        buttonsStyling: true
    });
    alert.fire({
        title: `¿Esta seguro eliminar la categoría ${name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Si, Eliminar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            form.delete(route('categories.destroy', id), {
                onFinish: () => router.reload({ only: ['categories'] })
            })
        }
    });
}

</script>

<template>
    <AdminLayout :title="'Categorías'">
        <!-- Card -->
        <div class="p-4 bg-white rounded drop-shadow-md">

            <!-- Card header -->
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold">Categorías</h2>
                <button @click="newCategory" class="px-2 bg-green-500 text-2xl text-white rounded font-bold">
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
                            <th>Categoría</th>
                            <th>Inscripción</th>
                            <th>Género</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item, i in categories" :key="item.id" class="border-t [&>td]:py-2">
                            <td>{{ i + 1 }}</td>
                            <td>{{ item.name }}</td>
                            <td>{{ `$${item.inscription}` }}</td>
                            <td>{{ item.gender }}</td>
                            <td>
                                <div class="relative inline-flex [&>a>i]:text-white [&>button>i]:text-white">
                                    <Link :href="route('groups.index', item.id)" class="rounded p-2 mr-1 bg-green-500">
                                    <i class="fa fa-users"></i>
                                    </Link>
                                    <button @click="edit(item)" class="rounded px-2 py-1 bg-blue-500">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button @click="$event => deleteCategory(item.id, item.name)"
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

    <ModalCategory :show="modal" :category="category" :error="errorCategory" @close="toggle" @save="save" />
</template>
