<script setup>

// Imports
import DialogModal from '@/Components/DialogModal.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';

// Props
defineProps({
    category: { type: Object, default: () => ({}) },
    error: { type: Object, default: () => ({}) },
    show: { type: Boolean, default: false }
});

// Emits
defineEmits(['close', 'save'])

</script>

<template>
    <DialogModal :show="show" maxWidth="lg" @close="$emit('close')">
        <template #title>
            {{ `${category.id === undefined ? 'Añadir' : 'Editar'} categoría` }}
        </template>
        <template #content>
            <div class="mt-4">
                <form @submit.prevent="category.id === undefined ? save() : update()" class="w-2xl">

                    <TextInput v-model="category.name" type="text" class="mt-2 block w-full" placeholder="Categoría"
                        minlength="3" maxlength="50" />
                    <InputError :message="error.name" class="mt-2" />

                    <TextInput v-model="category.inscription" type="text" class="mt-2 block w-full"
                        placeholder="Costo de inscripción" minlength="1" />
                    <InputError :message="error.inscription" class="mt-2" />

                    <select v-model="category.gender" class="mt-2 block w-full rounded border-gray-300">
                        <option value="femenino">Femenino</option>
                        <option value="masculino">Masculino</option>
                    </select>
                    <InputError :message="error.gender" class="mt-2" />
                </form>
            </div>
        </template>
        <template #footer>
            <button @click="$emit('save')" class="px-6 py-2 ml-2 bg-blue-600 text-blue-100 rounded">Guardar</button>
        </template>
    </DialogModal>
</template>