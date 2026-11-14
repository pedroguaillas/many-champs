<script setup>

import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BackLink from '@/Components/BackLink.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps({
    category: { type: Object, default: () => { } }
});

const form = useForm({
    date: '',
    time: ''
});

const error = reactive({});

const generate = () => {
    Object.keys(error).forEach(key => delete error[key]);
    form.post(route('games.sgenerate', props.category.id), {
        onError: (errors) => {
            Object.keys(errors).forEach(key => {
                error[key] = errors[key]
            });
        }
    });
}

</script>

<template>
    <AdminLayout :title="'Generar partidos de la fase de grupos'">
        <BackLink :href="route('games.index', category.id)" label="Partidos" />
        <!-- Card -->
        <div class="rounded-xl bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] overflow-hidden">

            <!-- Card header -->
            <div class="px-5 py-4 border-b border-gray-200 dark:border-white/[0.06]">
                <h4 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">
                    Generar partidos de la categoría {{ `${category.name} ${category.gender}` }}
                </h4>
            </div>

            <!-- Card body -->
            <div class="p-5 space-y-4">
                <!-- Qué hace y qué necesita -->
                <div class="space-y-2 text-[13px] text-gray-500 dark:text-slate-400">
                    <p>
                        Se crea un partido entre cada par de clubes del mismo grupo (todos contra todos, solo ida), con la fecha y hora que elijas.
                        Luego puedes cambiar el horario de cada partido.
                    </p>
                    <p>
                        Antes, revisa que la categoría tenga
                        <Link :href="route('groups.index', category.id)" class="font-medium text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300 transition-colors duration-200">grupos</Link>
                        y que cada
                        <Link :href="route('clubs.index', category.id)" class="font-medium text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300 transition-colors duration-200">club</Link>
                        esté asignado a uno.
                    </p>
                    <p class="flex items-start gap-2 rounded-lg px-3 py-2.5 bg-amber-500/10 text-amber-700 dark:text-amber-300">
                        <i class="fa-solid fa-triangle-exclamation mt-0.5 text-[11px]" aria-hidden="true"></i>
                        <span>Si la categoría ya tiene partidos, se eliminan con sus goles y tarjetas, y se crean de nuevo.</span>
                    </p>
                </div>

                <div>
                    <label for="generate-date" class="block text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 mb-2">Día de los partidos</label>
                    <TextInput id="generate-date" v-model="form.date" type="date" class="w-full px-3.5 py-2.5 rounded-lg bg-white dark:bg-slate-900/80 border border-gray-300 dark:border-white/[0.08] text-[13px] text-gray-700 dark:text-slate-200 placeholder-gray-400 dark:placeholder-slate-600 focus:outline-none focus:border-emerald-500/40 focus:ring-1 focus:ring-emerald-500/20 transition-colors duration-200" />
                    <InputError :message="error.date" class="mt-2" />
                </div>

                <div>
                    <label for="generate-time" class="block text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 mb-2">Hora de los partidos</label>
                    <TextInput id="generate-time" v-model="form.time" type="time" class="w-full px-3.5 py-2.5 rounded-lg bg-white dark:bg-slate-900/80 border border-gray-300 dark:border-white/[0.08] text-[13px] text-gray-700 dark:text-slate-200 placeholder-gray-400 dark:placeholder-slate-600 focus:outline-none focus:border-emerald-500/40 focus:ring-1 focus:ring-emerald-500/20 transition-colors duration-200" />
                    <InputError :message="error.time" class="mt-2" />
                </div>

                <div class="w-full flex justify-end pt-2">
                    <button @click="generate" :disabled="form.processing"
                        class="btn-primary">
                        <i class="fa fa-save"></i> Generar partidos
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>