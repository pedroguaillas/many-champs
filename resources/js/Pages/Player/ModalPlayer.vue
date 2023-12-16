<script setup>

// Imports
import DialogModal from '@/Components/DialogModal.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref } from 'vue';

// Props
defineProps({
    player: { type: Object, default: () => ({}) },
    error: { type: Object, default: () => ({}) },
    show: { type: Boolean, default: false }
});

// Emits
const emit = defineEmits(['close', 'save', 'loadImage'])

const imagePreviewURL = ref(null);

const previewImage = (event) => {
    const file = event.target.files[0];
    imagePreviewURL.value = URL.createObjectURL(file);
    emit('loadImage', file)
}

</script>

<template>
    <DialogModal :show="show" maxWidth="lg" @close="$emit('close')">
        <template #title>
            {{ `${player.id === undefined ? 'Añadir' : 'Editar'} Jugador` }}
        </template>
        <template #content>
            <div class="mt-4">

                <label :for="'photo'">Foto tamaño carnet</label>
                <div class="w-full">
                    <input type="file" @change="previewImage" accept="image/*">
                    <img :src="imagePreviewURL" class="mt-2 rounded mx-auto" v-if="player.photo" alt="Image Preview">
                </div>

                <TextInput v-model="player.cedula" type="text" class="mt-2 block w-full" placeholder="Cédula" minlength="10"
                    maxlength="10" />
                <InputError :message="error.cedula" class="mt-2" />

                <TextInput v-model="player.first_name" type="text" class="mt-2 block w-full" placeholder="Nombre"
                    minlength="3" maxlength="50" />
                <InputError :message="error.first_name" class="mt-2" />

                <TextInput v-model="player.last_name" type="text" class="mt-2 block w-full" placeholder="Apellido"
                    minlength="3" maxlength="50" />
                <InputError :message="error.last_name" class="mt-2" />

                <TextInput v-model="player.t_shirt" type="number" class="mt-2 block w-full" placeholder="# camiseta" />
                <InputError :message="error.t_shirt" class="mt-2" />

                <TextInput v-model="player.date_of_birth" type="date" class="mt-2 block w-full" />
                <InputError :message="error.date_of_birth" class="mt-2" />

                <TextInput v-model="player.phone" type="text" class="mt-2 block w-full" placeholder="Celular" minlength="10"
                    maxlength="10" />
                <InputError :message="error.phone" class="mt-2" />

            </div>
        </template>
        <template #footer>
            <button @click="$emit('save')" class="px-6 py-2 ml-2 bg-blue-600 text-blue-100 rounded">Guardar</button>
        </template>
    </DialogModal>
</template>