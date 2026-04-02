<script setup>

// Imports
import DialogModal from '@/Components/DialogModal.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';

// Props
defineProps({
    group: { type: Object, default: () => ({}) },
    error: { type: Object, default: () => ({}) },
    show: { type: Boolean, default: false }
});

// Emits
defineEmits(['close', 'save'])

</script>

<template>
    <DialogModal :show="show" maxWidth="lg" @close="$emit('close')">
        <template #title>
            {{ `${group.id === undefined ? 'Añadir' : 'Editar'} Grupo` }}
        </template>
        <template #content>
            <div class="mt-4">

                <TextInput v-model="group.name" type="text" class="mt-2 block w-full" placeholder="Grupo" minlength="1"
                    maxlength="1" />
                <InputError :message="error.name" class="mt-2" />
            </div>
        </template>
        <template #footer>
            <button @click="$emit('save')" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-emerald-500 text-xs font-semibold text-white hover:bg-emerald-400 transition-all duration-200">Guardar</button>
        </template>
    </DialogModal>
</template>