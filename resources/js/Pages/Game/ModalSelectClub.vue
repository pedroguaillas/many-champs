<script setup>

import DialogModal from '@/Components/DialogModal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const emit = defineEmits(['close', 'select'])

defineProps({
    clubs: { type: Array, default: () => [] },
    show: { type: Boolean, default: () => false }
});

</script>

<template>
    <DialogModal :show="show" maxWidth="lg" @close="$emit('close')">
        <template #title>
            <h2>Selecionar club</h2>
        </template>
        <template #content>
            <table class="w-full">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th class="text-left">Club</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="club in clubs" :key="club.id">
                        <td class="text-center">{{ club.id }}</td>
                        <td>{{ club.name }}</td>
                        <td>
                            <PrimaryButton @click="$emit('select', club)">
                                Seleccionar
                            </PrimaryButton>
                        </td>
                    </tr>
                </tbody>
            </table>
        </template>
    </DialogModal>
</template>