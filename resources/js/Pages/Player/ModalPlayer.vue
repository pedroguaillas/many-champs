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

                <label for="photo" class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Foto tamaño carnet</label>
                <div class="w-full mt-1">
                    <input type="file" @change="previewImage" accept="image/*"
                        class="text-[13px] text-slate-400 file:mr-3 file:py-2 file:px-3.5 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-slate-700/50 file:text-slate-300 hover:file:bg-slate-700 file:cursor-pointer file:transition-colors file:duration-150">
                    <img :src="imagePreviewURL" class="mt-2 rounded-lg max-w-[120px] border border-white/[0.06] mx-auto" v-if="player.photo" alt="Image Preview">
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
            <button @click="$emit('save')" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-emerald-500 text-xs font-semibold text-white hover:bg-emerald-400 transition-all duration-200">Guardar</button>
        </template>
    </DialogModal>
</template>