<script setup>

// Imports
import DialogModal from '@/Components/DialogModal.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';

// Props
defineProps({
    payment: { type: Object, default: () => ({}) },
    errors: { type: Object, default: () => ({}) },
    show: { type: Boolean, default: false }
});

// Emits
defineEmits(['close', 'save'])

</script>

<template>
    <DialogModal :show="show" maxWidth="lg" @close="$emit('close')">
        <template #title>
            {{ `${payment.id === undefined ? 'Añadir' : 'Editar'} pago` }}
        </template>
        <template #content>
            <div class="mt-4">

                <TextInput v-model="payment.amount" type="number" class="mt-2 block w-full" placeholder="Monto" />
                <InputError :message="errors.amount" class="mt-2" />

                <TextInput v-model="payment.note" type="text" class="mt-2 block w-full" placeholder="Nota" minlength="3"
                    maxlength="50" />
                <InputError :message="errors.note" class="mt-2" />

            </div>
        </template>
        <template #footer>
            <button @click="$emit('save')" class="px-6 py-2 ml-2 bg-blue-600 text-blue-100 rounded">Guardar</button>
        </template>
    </DialogModal>
</template>