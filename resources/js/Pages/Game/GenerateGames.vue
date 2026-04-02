<script setup>

import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';
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
                <div>
                    <label :for="form.date" class="block text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 mb-2">Dia a crear los partidos</label>
                    <TextInput v-model="form.date" type="date" class="w-full px-3.5 py-2.5 rounded-lg bg-white dark:bg-slate-900/80 border border-gray-300 dark:border-white/[0.08] text-[13px] text-gray-700 dark:text-slate-200 placeholder-gray-400 dark:placeholder-slate-600 focus:outline-none focus:border-emerald-500/40 focus:ring-1 focus:ring-emerald-500/20 transition-colors duration-200" />
                    <InputError :message="error.date" class="mt-2" />
                </div>

                <div>
                    <label :for="form.time" class="block text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 mb-2">Hora a crear los partidos</label>
                    <TextInput v-model="form.time" type="time" class="w-full px-3.5 py-2.5 rounded-lg bg-white dark:bg-slate-900/80 border border-gray-300 dark:border-white/[0.08] text-[13px] text-gray-700 dark:text-slate-200 placeholder-gray-400 dark:placeholder-slate-600 focus:outline-none focus:border-emerald-500/40 focus:ring-1 focus:ring-emerald-500/20 transition-colors duration-200" />
                    <InputError :message="error.time" class="mt-2" />
                </div>

                <div class="w-full flex justify-end pt-2">
                    <button @click="generate" :disabled="form.processing"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-emerald-500 text-xs font-semibold text-white hover:bg-emerald-400 disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-200">
                        <i class="fa fa-save"></i> Generar
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>