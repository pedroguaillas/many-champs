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
        <div class="m-4 p-4 bg-white rounded drop-shadow-md">

            <!-- Card header -->
            <h4 class="mb-4 text-center font-bold">
                Generar partidos de la categoría {{ `${category.name} ${category.gender}` }}
            </h4>

            <label :for="form.date">Dia a crear los partidos</label>
            <TextInput v-model="form.date" type="date" class="block w-full rounded mb-2" />
            <InputError :message="error.date" class="mt-2" />

            <label :for="form.time">Hora a crear los partidos</label>
            <TextInput v-model="form.time" type="time" class="block w-full rounded" />
            <InputError :message="error.time" class="mt-2" />

            <div class="w-full flex justify-end">
                <button @click="generate" :disabled="form.processing"
                    class="py-2 px-4 mt-2 bg-indigo-500 rounded text-white">
                    <i class="fa fa-save"></i> Generar
                </button>
            </div>
        </div>
    </AdminLayout>
</template>