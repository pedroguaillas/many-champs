<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectClub from './SelectClub.vue';
import { useForm } from '@inertiajs/vue3';
import { reactive } from 'vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    category: { type: Object, default: () => { } },
    clubs: { type: Array, default: () => [] },
    progress: { type: Array, default: () => [] }
});

let date = new Date()
date.setHours(date.getHours() - 5)

const form = useForm({
    club1_id: '',
    club2_id: '',
    progress_id: '',
    date: date.toISOString().substring(0, 10),
    time: date.toISOString().substring(11, 16)
});

const error = reactive({})

const selectClub1Id = (id) => {
    form.club1_id = id
}

const selectClub2Id = (id) => {
    form.club2_id = id
}

const save = () => {
    form.post(route('games.store', props.category.id), {
        onError: (errors) => {
            Object.keys(errors).forEach(key => {
                error[key] = errors[key]
            });
        }
    });
}

</script>

<template>
    <AdminLayout :title="'Registrar juego'">
        <!-- Card -->
        <div class="p-4 bg-white rounded drop-shadow-md">

            <h4 class="mb-4 text-center font-bold">Registrar partido</h4>

            <!-- Centraliza el contenido a visualizar -->

            <SelectClub :clubs="clubs" @selectId="selectClub1Id" :placeholder="'Club 1'" />
            <InputError :message="error.club1_id" class="mt-2" />

            <SelectClub :clubs="clubs" @selectId="selectClub2Id" :placeholder="'Club 2'" :class="'pt-2'" />
            <InputError :message="error.club2_id" class="mt-2" />

            <TextInput v-model="form.date" type="date" class="mt-2 block w-full" />
            <InputError :message="error.date" class="mt-2" />

            <TextInput v-model="form.time" type="time" class="mt-2 block w-full" />
            <InputError :message="error.time" class="mt-2" />

            <label class="mt-3 mb-0 block">Fase</label>
            <select v-model="form.progress_id" class="mt-2 block w-full rounded border-gray-300">
                <option v-for="p in progress" :key="p.id" :value="p.id">{{ p.description }}</option>
            </select>
            <InputError :message="error.progress_id" class="mt-2" />

            <div class="w-full flex justify-end">
                <button @click="save" :disabled="form.processing" class="py-2 px-4 mt-2 bg-indigo-500 rounded text-white">
                    <i class="fa fa-save"></i> Guardar
                </button>
            </div>
        </div>

    </AdminLayout>
</template>