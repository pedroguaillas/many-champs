<script setup>

// Imports
import DialogModal from '@/Components/DialogModal.vue';

// Props
const props = defineProps({
    players: { type: Array, default: () => [] },
    show: { type: Boolean, default: () => false }
})

// Emits
defineEmits(['close', 'selectPlayer'])

</script>

<template>
    <DialogModal :show="show" maxWidth="lg" @close="$emit('close')">
        <template #title>
            Seleccionar jugador
        </template>
        <template #content>
            <div class="w-full overflow-x-auto mt-4">
                <table class="mt-4 text-sm sm:text-xs table-auto w-full text-center text-gray-700">
                    <thead>
                        <tr class="[&>th]:py-2">
                            <th>N°</th>
                            <th>Equipo</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="player, i in players" :key="player.id" class="border-t [&>td]:py-2">
                            <td>{{ i + 1 }}</td>
                            <td>{{ `${player.first_name} ${player.last_name}` }}</td>
                            <td>
                                <button @click="$event => $emit('selectPlayer', player.id)"
                                    class="rounded px-2 py-1 ml-1 bg-indigo-500 text-xl text-white">
                                    <i class="fa fa-check"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>
    </DialogModal>
</template>