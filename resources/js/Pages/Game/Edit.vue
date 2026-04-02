<script setup>

// Imports
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectClub from './SelectClub.vue';
import { useForm } from '@inertiajs/vue3';
import { reactive } from 'vue';
import InputError from '@/Components/InputError.vue';

// Props
const props = defineProps({
    category: { type: Object, default: () => { } },
    clubs: { type: Array, default: () => [] },
    progress: { type: Array, default: () => [] },
    game: { type: Object, default: () => { } }
});

const form = useForm(props.game);

const error = reactive({})

const selectClub1Id = (id) => {
    form.club1_id = id
}

const selectClub2Id = (id) => {
    form.club2_id = id
}

const save = () => {
    form.put(route('games.update', form.id), {
        onError: (errors) => {
            Object.keys(errors).forEach(key => {
                error[key] = errors[key]
            });
        }
    })
}

</script>

<template>
    <AdminLayout :title="'Registrar juego'">
        <!-- Card -->
        <div class="rounded-xl bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] overflow-hidden">

            <!-- Card header -->
            <div class="px-5 py-4 border-b border-gray-200 dark:border-white/[0.06]">
                <h4 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Editar partido</h4>
            </div>

            <!-- Card body -->
            <div class="p-5 space-y-4">

                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 mb-2">Club 1</label>
                    <SelectClub :clubs="clubs" @selectId="selectClub1Id" :club_id="form.club1_id" :placeholder="'Club 1'" />
                    <InputError :message="error.club1_id" class="mt-2" />
                </div>

                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 mb-2">Club 2</label>
                    <SelectClub :clubs="clubs" @selectId="selectClub2Id" :club_id="form.club2_id" :placeholder="'Club 2'" />
                    <InputError :message="error.club2_id" class="mt-2" />
                </div>

                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 mb-2">Fecha</label>
                    <TextInput v-model="form.date" type="date" class="w-full px-3.5 py-2.5 rounded-lg bg-white dark:bg-slate-900/80 border border-gray-300 dark:border-white/[0.08] text-[13px] text-gray-700 dark:text-slate-200 placeholder-gray-400 dark:placeholder-slate-600 focus:outline-none focus:border-emerald-500/40 focus:ring-1 focus:ring-emerald-500/20 transition-colors duration-200" />
                    <InputError :message="error.date" class="mt-2" />
                </div>

                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 mb-2">Hora</label>
                    <TextInput v-model="form.time" type="time" class="w-full px-3.5 py-2.5 rounded-lg bg-white dark:bg-slate-900/80 border border-gray-300 dark:border-white/[0.08] text-[13px] text-gray-700 dark:text-slate-200 placeholder-gray-400 dark:placeholder-slate-600 focus:outline-none focus:border-emerald-500/40 focus:ring-1 focus:ring-emerald-500/20 transition-colors duration-200" />
                    <InputError :message="error.time" class="mt-2" />
                </div>

                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 mb-2">Fase</label>
                    <select v-model="form.progress_id" class="w-full px-3.5 py-2.5 rounded-lg bg-white dark:bg-slate-900/80 border border-gray-300 dark:border-white/[0.08] text-[13px] text-gray-700 dark:text-slate-200 focus:outline-none focus:border-emerald-500/40 focus:ring-1 focus:ring-emerald-500/20 transition-colors duration-200">
                        <option v-for="p in progress" :key="p.id" :value="p.id" :selected="form.progress_id === p.id">{{
                            p.description
                        }}</option>
                    </select>
                    <InputError :message="error.progress_id" class="mt-2" />
                </div>

                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 mb-2">Estado</label>
                    <select v-model="form.state" class="w-full px-3.5 py-2.5 rounded-lg bg-white dark:bg-slate-900/80 border border-gray-300 dark:border-white/[0.08] text-[13px] text-gray-700 dark:text-slate-200 focus:outline-none focus:border-emerald-500/40 focus:ring-1 focus:ring-emerald-500/20 transition-colors duration-200">
                        <option value="creado" :selected="form.state === 'creado'">Creado</option>
                        <option value="planificado" :selected="form.state === 'planificado'">Planificado</option>
                        <option value="jugando" :selected="form.state === 'jugando'">Jugando</option>
                        <option value="suspendido" :selected="form.state === 'suspendido'">Suspendido</option>
                        <option value="finalizado" :selected="form.state === 'finalizado'">Finalizado</option>
                    </select>
                    <InputError :message="error.state" class="mt-2" />
                </div>

                <div class="w-full flex justify-end pt-2">
                    <button @click="save" :disabled="form.processing" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-emerald-500 text-xs font-semibold text-white hover:bg-emerald-400 disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-200">
                        <i class="fa fa-save"></i> Guardar
                    </button>
                </div>
            </div>
        </div>

    </AdminLayout>
</template>