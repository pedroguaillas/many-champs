<script setup>

import AdminLayout from '@/Layouts/AdminLayout.vue';
import ModalChampionship from './ModalChampionship.vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

defineProps({
    championships: { type: Array, default: () => [] }
});

const modal = ref(false);

const initialForm = { name: '', owner_id: '' };

const form = reactive({ ...initialForm });
const errors = reactive({ ...initialForm });

const resetErrors = () => {
    Object.assign(errors, initialForm);
};

const toggle = () => {
    modal.value = !modal.value;
};

const save = () => {
    axios
        .put(route('admin.championships.update', form.id), form)
        .then(() => {
            toggle();
            resetErrors();
            router.reload({ only: ['championships'] });
        }).catch(error => {
            resetErrors();
            Object.keys(error.response.data.errors).forEach(key => {
                errors[key] = error.response.data.errors[key][0];
            });
        });
};

const edit = (championship) => {
    Object.assign(form, {
        id: championship.id,
        name: championship.name,
        owner_id: championship.user_id,
    });
    toggle();
};

const deleteForm = useForm({ id: '' });

const deleteChampionship = (id, name) => {
    Swal.fire({
        title: `¿Eliminar "${name}"?`,
        text: 'Se eliminarán todas las categorías, clubes, jugadores y partidos asociados.',
        icon: 'warning',
        showCancelButton: true,
        background: '#1e293b',
        color: '#e2e8f0',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#334155',
        confirmButtonText: '<i class="fa-solid fa-check"></i> Sí, Eliminar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            deleteForm.delete(route('admin.championships.destroy', id), {
                onFinish: () => router.reload({ only: ['championships'] })
            });
        }
    });
};

</script>

<template>
    <AdminLayout :title="'Campeonatos'">
        <div class="rounded-xl bg-slate-800/60 border border-white/[0.06] overflow-hidden">

            <!-- Card header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-white/[0.06]">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                        <i class="fa-solid fa-trophy text-emerald-400 text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-white text-sm font-semibold tracking-tight">Campeonatos</h2>
                        <p class="text-[11px] text-slate-500">Gestión de torneos registrados</p>
                    </div>
                </div>
                <Link :href="route('admin.championships.create')"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-emerald-500/10 text-emerald-400 text-xs font-medium border border-emerald-500/20 hover:bg-emerald-500/20 hover:border-emerald-500/30 transition-all duration-200">
                    <i class="fa-solid fa-plus text-[10px]"></i>
                    Nuevo campeonato
                </Link>
            </div>

            <!-- Table -->
            <div class="w-full overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-white/[0.06]">
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-slate-500 w-12">N°</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-slate-500">Campeonato</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-slate-500">Propietario</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-slate-500 text-center">Miembros</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-slate-500 w-24"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, i) in championships" :key="item.id"
                            class="border-b border-white/[0.04] hover:bg-white/[0.02] transition-colors duration-150">
                            <td class="px-5 py-3.5 text-xs text-slate-500 tabular-nums">{{ i + 1 }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-7 h-7 rounded-md bg-amber-500/10 flex items-center justify-center flex-shrink-0">
                                        <i class="fa-solid fa-trophy text-amber-400/70 text-[10px]"></i>
                                    </div>
                                    <span class="text-[13px] text-slate-200 font-medium">{{ item.name }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-[13px] text-slate-400">{{ item.owner?.name ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-700/50 text-slate-300 text-xs font-medium tabular-nums">
                                    <i class="fa-solid fa-users text-[9px] text-slate-500"></i>
                                    {{ item.users_count }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="edit(item)"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-500 hover:text-slate-200 hover:bg-slate-700/50 transition-colors duration-150">
                                        <i class="fa-solid fa-pen text-[11px]"></i>
                                    </button>
                                    <button @click="deleteChampionship(item.id, item.name)"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-500 hover:text-red-400 hover:bg-red-500/10 transition-colors duration-150">
                                        <i class="fa-solid fa-trash text-[11px]"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Empty state -->
                <div v-if="championships.length === 0" class="flex flex-col items-center justify-center py-16">
                    <div class="w-12 h-12 rounded-xl bg-slate-800 border border-white/[0.06] flex items-center justify-center mb-3">
                        <i class="fa-solid fa-trophy text-slate-600 text-lg"></i>
                    </div>
                    <p class="text-sm text-slate-500">No hay campeonatos registrados</p>
                    <Link :href="route('admin.championships.create')"
                        class="mt-3 text-xs text-emerald-400 hover:text-emerald-300 transition-colors duration-150">
                        Crear el primero
                    </Link>
                </div>
            </div>
        </div>

        <ModalChampionship :show="modal" :form="form" :errors="errors" @close="toggle" @save="save" />
    </AdminLayout>
</template>
