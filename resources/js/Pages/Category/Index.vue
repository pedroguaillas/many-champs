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
        <div class="rounded-xl bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] overflow-hidden">

            <!-- Card header -->
            <div class="px-5 py-4 border-b border-gray-200 dark:border-white/[0.06] flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                        <i class="fa-solid fa-layer-group text-emerald-400 text-[11px]"></i>
                    </div>
                    <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Categorías</h2>
                </div>
                <button @click="newCategory"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-emerald-500/10 text-emerald-400 text-xs font-medium border border-emerald-500/20 hover:bg-emerald-500/20 hover:border-emerald-500/30 transition-all duration-200">
                    <i class="fa-solid fa-plus text-[11px]"></i> Agregar
                </button>
            </div>

            <!-- Responsive -->
            <div class="w-full overflow-x-auto">
                <!-- Tabla -->
                <table class="w-full text-left">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 w-1">N°</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Categoría</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Inscripción</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Género</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item, i in categories" :key="item.id"
                            class="border-b border-gray-100 dark:border-white/[0.04] hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150">
                            <td class="px-5 py-3.5 text-[13px] text-gray-500 dark:text-slate-400">{{ i + 1 }}</td>
                            <td class="px-5 py-3.5 text-[13px] text-gray-700 dark:text-slate-200">{{ item.name }}</td>
                            <td class="px-5 py-3.5 text-[13px] text-gray-700 dark:text-slate-200">{{ `$${item.inscription}` }}</td>
                            <td class="px-5 py-3.5 text-[13px] text-gray-500 dark:text-slate-400">{{ item.gender }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1">
                                    <Link :href="route('groups.index', item.id)"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 dark:text-slate-500 hover:text-emerald-400 hover:bg-emerald-500/10 transition-colors duration-150">
                                        <i class="fa fa-users text-[11px]"></i>
                                    </Link>
                                    <button @click="edit(item)"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 dark:text-slate-500 hover:text-gray-700 dark:hover:text-gray-700 dark:text-slate-200 hover:bg-gray-200 dark:bg-slate-700/50 transition-colors duration-150">
                                        <i class="fa fa-edit text-[11px]"></i>
                                    </button>
                                    <button @click="$event => deleteCategory(item.id, item.name)"
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

    <ModalCategory :show="modal" :category="category" :error="errorCategory" @close="toggle" @save="save" />
</template>
