<script setup>

// Imports
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BackLink from '@/Components/BackLink.vue';
import EmptyState from '@/Components/EmptyState.vue';
import ModalGroup from './ModalGroup.vue';
import { Link, router, useForm } from '@inertiajs/vue3'
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
        title: `¿Está seguro de eliminar el grupo ${item.name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Sí, eliminar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar',
        background: '#1e293b',
        color: '#e2e8f0',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#334155',
    }).then((result) => {
        if (result.isConfirmed) {
            // form.delete no devuelve una promesa: se recarga al terminar
            form.delete(route('groups.destroy', item.id), {
                preserveScroll: true,
                onFinish: () => router.reload({ only: ['groups'] }),
            });
        }
    });
}

</script>

<template>
    <AdminLayout :title="`Grupos ${props.category.name} ${props.category.gender}`">
        <BackLink :href="route('categories.index')" label="Categorías" />

        <!-- Card -->
        <div class="rounded-xl bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] overflow-hidden">

            <!-- Card header -->
            <div class="px-5 py-4 border-b border-gray-200 dark:border-white/[0.06] flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-violet-500/10 flex items-center justify-center">
                        <i class="fa-solid fa-layer-group text-violet-400 text-[11px]"></i>
                    </div>
                    <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">{{ `Grupos ${props.category.name} ${props.category.gender}` }}</h2>
                </div>
                <button @click="newGroup"
                    class="btn-add">
                    <i class="fa-solid fa-plus text-[11px]"></i> Agregar
                </button>
            </div>

            <!-- Responsive -->
            <div class="w-full overflow-x-auto">
                <!-- Tabla -->
                <table v-if="groups.length > 0" class="data-table w-full text-center">
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
                                    <button @click="edit(item)" title="Editar" aria-label="Editar grupo"
                                        class="btn-icon">
                                        <i class="fa fa-edit text-[11px]"></i>
                                    </button>
                                    <button @click="$event => deleteGroup(item)" title="Eliminar" aria-label="Eliminar grupo"
                                        class="btn-icon btn-icon-danger">
                                        <i class="fa fa-trash text-[11px]"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Sin grupos -->
                <EmptyState v-else icon="fa-solid fa-layer-group" title="Esta categoría aún no tiene grupos"
                    description="Los grupos dividen a los clubes en la primera fase (Grupo A, Grupo B…). Los partidos de fase de grupos se generan entre los clubes de un mismo grupo.">
                    <button type="button" @click="newGroup" class="btn-primary">
                        <i class="fa-solid fa-plus text-[11px]"></i> Crear el primer grupo
                    </button>
                    <Link :href="route('clubs.index', category.id)"
                        class="px-5 py-2.5 rounded-lg text-xs font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-700/40 transition duration-200">
                        Ir a clubes
                    </Link>
                </EmptyState>
            </div>
        </div>
    </AdminLayout>

    <ModalGroup :show="modal" :group="group" :error="errors" @close="toggle" @save="save" />
</template>
