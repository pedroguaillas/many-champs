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
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar',
        background: '#1e293b',
        color: '#e2e8f0',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#334155',
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
        <div class="rounded-xl bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] overflow-hidden">

            <!-- Card header -->
            <div class="px-5 py-4 border-b border-gray-200 dark:border-white/[0.06] flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-violet-500/10 flex items-center justify-center">
                        <i class="fa-solid fa-layer-group text-violet-400 text-[11px]"></i>
                    </div>
                    <h2 class="text-sm font-semibold tracking-tight text-white">{{ `Grupos ${props.category.name} ${props.category.gender}` }}</h2>
                </div>
                <button @click="newGroup"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-emerald-500/10 text-emerald-400 text-xs font-medium border border-emerald-500/20 hover:bg-emerald-500/20 hover:border-emerald-500/30 transition-all duration-200">
                    <i class="fa-solid fa-plus text-[11px]"></i> Agregar
                </button>
            </div>

            <!-- Responsive -->
            <div class="w-full overflow-x-auto">
                <!-- Tabla -->
                <table class="w-full text-center">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">N°</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Grupo</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item, i in groups" :key="item.id"
                            class="border-b border-gray-100 dark:border-white/[0.04] hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150">
                            <td class="px-5 py-3.5 text-[13px] text-gray-500 dark:text-slate-400">{{ i + 1 }}</td>
                            <td class="px-5 py-3.5 text-[13px] text-gray-700 dark:text-slate-200">{{ item.name }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-center gap-1">
                                    <button @click="edit(item)"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 dark:text-slate-500 hover:text-gray-700 dark:hover:text-gray-700 dark:text-slate-200 hover:bg-gray-200 dark:hover:bg-gray-200 dark:bg-slate-700/50 transition-colors duration-150">
                                        <i class="fa fa-edit text-[11px]"></i>
                                    </button>
                                    <button @click="$event => deleteGroup(item)"
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

    <ModalGroup :show="modal" :group="group" :error="errors" @close="toggle" @save="save" />
</template>
