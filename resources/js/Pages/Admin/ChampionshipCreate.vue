<script setup>

import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import { reactive, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';

const form = reactive({
    name: '',
    owner_mode: '',
    owner_id: null,
    owner_name: '',
    owner_email: '',
    owner_password: '',
});

const errors = reactive({});
const searching = ref(false);
const searchDone = ref(false);
const foundUser = ref(null);
const emailSearch = ref('');
const submitting = ref(false);

const resetOwner = () => {
    form.owner_mode = '';
    form.owner_id = null;
    form.owner_name = '';
    form.owner_email = '';
    form.owner_password = '';
    foundUser.value = null;
    searchDone.value = false;
    emailSearch.value = '';
    Object.keys(errors).forEach(key => delete errors[key]);
};

const searchUser = async () => {
    if (!emailSearch.value) return;

    searching.value = true;
    searchDone.value = false;
    foundUser.value = null;
    form.owner_mode = '';

    try {
        const { data } = await axios.post(route('admin.championships.search-user'), {
            email: emailSearch.value,
        });

        searchDone.value = true;

        if (data.user) {
            foundUser.value = data.user;
            form.owner_mode = 'existing';
            form.owner_id = data.user.id;
        } else {
            form.owner_mode = 'new';
            form.owner_email = emailSearch.value;
        }
    } catch (e) {
        if (e.response?.data?.errors?.email) {
            errors.email_search = e.response.data.errors.email[0];
        }
    } finally {
        searching.value = false;
    }
};

const submit = () => {
    submitting.value = true;
    Object.keys(errors).forEach(key => delete errors[key]);

    axios.post(route('admin.championships.store'), form)
        .then(() => {
            router.visit(route('admin.championships.index'));
        })
        .catch(error => {
            if (error.response?.data?.errors) {
                Object.entries(error.response.data.errors).forEach(([key, val]) => {
                    errors[key] = val[0];
                });
            }
        })
        .finally(() => {
            submitting.value = false;
        });
};

</script>

<template>
    <AdminLayout :title="'Crear Campeonato'">
        <div class="max-w-xl mx-auto">

            <!-- Back link -->
            <Link :href="route('admin.championships.index')"
                class="inline-flex items-center gap-2 text-xs text-slate-500 hover:text-slate-300 transition-colors duration-150 mb-4">
                <i class="fa-solid fa-arrow-left text-[10px]"></i>
                Campeonatos
            </Link>

            <!-- Main card -->
            <div class="rounded-xl bg-slate-800/60 border border-white/[0.06] overflow-hidden">

                <!-- Card header with pitch stripe -->
                <div class="px-6 py-5 border-b border-white/[0.06] relative">
                    <span class="absolute left-0 top-0 bottom-0 w-[3px] bg-emerald-500 rounded-r-full"></span>
                    <h2 class="text-white text-[15px] font-semibold tracking-tight">Nuevo Campeonato</h2>
                    <p class="text-[12px] text-slate-500 mt-0.5">Configura el torneo y asigna un propietario</p>
                </div>

                <form @submit.prevent="submit" class="p-6 space-y-6">

                    <!-- Nombre del campeonato -->
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-2">
                            Nombre del campeonato
                        </label>
                        <input v-model="form.name" type="text"
                            class="w-full px-3.5 py-2.5 rounded-lg bg-slate-900/80 border border-white/[0.08] text-[13px] text-slate-200 placeholder-slate-600 focus:outline-none focus:border-emerald-500/40 focus:ring-1 focus:ring-emerald-500/20 transition-colors duration-200"
                            placeholder="Ej: Copa Verano 2026" maxlength="255" />
                        <InputError :message="errors.name" class="mt-1.5" />
                    </div>

                    <!-- Divider -->
                    <div class="border-t border-white/[0.04]"></div>

                    <!-- Buscar propietario -->
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-2">
                            Propietario
                        </label>
                        <p class="text-[12px] text-slate-500 mb-3">Busca por correo electrónico para asignar o crear un propietario</p>

                        <!-- Search input -->
                        <div v-if="!searchDone" class="flex gap-2">
                            <input v-model="emailSearch" type="email"
                                class="flex-1 px-3.5 py-2.5 rounded-lg bg-slate-900/80 border border-white/[0.08] text-[13px] text-slate-200 placeholder-slate-600 focus:outline-none focus:border-emerald-500/40 focus:ring-1 focus:ring-emerald-500/20 transition-colors duration-200"
                                placeholder="correo@ejemplo.com"
                                @keydown.enter.prevent="searchUser" />
                            <button type="button" @click="searchUser" :disabled="searching || !emailSearch"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-slate-700/60 border border-white/[0.08] text-xs font-medium text-slate-300 hover:text-white hover:bg-slate-700 disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-200">
                                <i v-if="searching" class="fa-solid fa-spinner fa-spin text-[10px]"></i>
                                <i v-else class="fa-solid fa-magnifying-glass text-[10px]"></i>
                                Buscar
                            </button>
                        </div>
                        <InputError :message="errors.email_search" class="mt-1.5" />
                    </div>

                    <!-- Usuario encontrado -->
                    <div v-if="searchDone && foundUser"
                        class="rounded-lg bg-emerald-500/[0.06] border border-emerald-500/20 overflow-hidden">
                        <div class="px-4 py-3.5 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-emerald-500/15 flex items-center justify-center">
                                    <i class="fa-solid fa-user-check text-emerald-400 text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-[13px] font-medium text-emerald-300">{{ foundUser.name }}</p>
                                    <p class="text-[11px] text-emerald-500/70">{{ foundUser.email }}</p>
                                </div>
                            </div>
                            <button type="button" @click="resetOwner"
                                class="w-7 h-7 rounded-md flex items-center justify-center text-slate-500 hover:text-slate-300 hover:bg-slate-700/50 transition-colors duration-150">
                                <i class="fa-solid fa-xmark text-xs"></i>
                            </button>
                        </div>
                        <div class="px-4 py-2 bg-emerald-500/[0.04] border-t border-emerald-500/10">
                            <p class="text-[11px] text-emerald-500/80">
                                <i class="fa-solid fa-circle-check text-[9px] mr-1"></i>
                                Será asignado como propietario del campeonato
                            </p>
                        </div>
                    </div>

                    <!-- Usuario no encontrado: crear nuevo -->
                    <div v-if="searchDone && !foundUser"
                        class="rounded-lg bg-amber-500/[0.06] border border-amber-500/20 overflow-hidden">

                        <!-- Header -->
                        <div class="px-4 py-3 flex items-center justify-between border-b border-amber-500/10">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-user-plus text-amber-400/70 text-xs"></i>
                                <p class="text-[12px] font-medium text-amber-300/90">
                                    Usuario no encontrado — crear nuevo
                                </p>
                            </div>
                            <button type="button" @click="resetOwner"
                                class="w-7 h-7 rounded-md flex items-center justify-center text-slate-500 hover:text-slate-300 hover:bg-slate-700/50 transition-colors duration-150">
                                <i class="fa-solid fa-xmark text-xs"></i>
                            </button>
                        </div>

                        <!-- Fields -->
                        <div class="p-4 space-y-4">
                            <div>
                                <label class="block text-[11px] font-medium text-slate-400 mb-1.5">Nombre</label>
                                <input v-model="form.owner_name" type="text"
                                    class="w-full px-3.5 py-2.5 rounded-lg bg-slate-900/60 border border-white/[0.08] text-[13px] text-slate-200 placeholder-slate-600 focus:outline-none focus:border-amber-500/40 focus:ring-1 focus:ring-amber-500/20 transition-colors duration-200"
                                    placeholder="Nombre completo" maxlength="255" />
                                <InputError :message="errors.owner_name" class="mt-1.5" />
                            </div>

                            <div>
                                <label class="block text-[11px] font-medium text-slate-400 mb-1.5">Correo</label>
                                <input v-model="form.owner_email" type="email" disabled
                                    class="w-full px-3.5 py-2.5 rounded-lg bg-slate-900/40 border border-white/[0.06] text-[13px] text-slate-500 cursor-not-allowed" />
                                <InputError :message="errors.owner_email" class="mt-1.5" />
                            </div>

                            <div>
                                <label class="block text-[11px] font-medium text-slate-400 mb-1.5">Contraseña</label>
                                <input v-model="form.owner_password" type="password"
                                    class="w-full px-3.5 py-2.5 rounded-lg bg-slate-900/60 border border-white/[0.08] text-[13px] text-slate-200 placeholder-slate-600 focus:outline-none focus:border-amber-500/40 focus:ring-1 focus:ring-amber-500/20 transition-colors duration-200"
                                    placeholder="Mínimo 8 caracteres" minlength="8" />
                                <InputError :message="errors.owner_password" class="mt-1.5" />
                            </div>
                        </div>
                    </div>

                    <!-- Footer actions -->
                    <div class="flex items-center justify-end gap-3 pt-5 border-t border-white/[0.06]">
                        <Link :href="route('admin.championships.index')"
                            class="px-5 py-2.5 rounded-lg text-xs font-medium text-slate-400 hover:text-slate-200 hover:bg-slate-700/40 transition-all duration-200">
                            Cancelar
                        </Link>
                        <button type="submit"
                            :disabled="submitting || !form.owner_mode || !form.name"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-emerald-500 text-xs font-semibold text-white hover:bg-emerald-400 disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-200">
                            <i v-if="submitting" class="fa-solid fa-spinner fa-spin text-[10px]"></i>
                            <i v-else class="fa-solid fa-plus text-[10px]"></i>
                            Crear Campeonato
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </AdminLayout>
</template>
