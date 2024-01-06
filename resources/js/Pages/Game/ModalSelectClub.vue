<script setup>

import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const emit = defineEmits(['close', 'select'])

defineProps({
    clubs: { type: Array, default: () => [] },
    show: { type: Boolean, default: () => false }
});

</script>

<template>
    <Modal :show="show" maxWidth="lg" @close="$emit('close')">

        <div class="p-2 sm:p-4">
            <h2 class="text-sm sm:text-lg font-bold text-center">Selecionar club</h2>

            <table class="w-full text-xs sm:text-sm">
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
        </div>
    </Modal>
</template>