<script setup>

// Imports
import Modal from '@/Components/Modal.vue';

// Props
const props = defineProps({
    players: { type: Array, default: () => [] },
    show: { type: Boolean, default: () => false }
})

// Emits
defineEmits(['close', 'selectPlayer', 'paySantion'])

</script>

<template>
    <Modal :show="show" maxWidth="lg" @close="$emit('close')">

        <div class="p-2 sm:p-4">
            <h1 class="text-sm sm:text-lg font-bold text-center">Seleccionar jugador</h1>

            <div class="w-full overflow-x-auto mt-2 sm:mt-4">
                <table class="mt-4 text-xs sm:text-sm table-auto w-full text-center text-gray-700">
                    <thead>
                        <tr class="[&>th]:py-2">
                            <th class="w-1 sm:w-2">N°</th>
                            <th class="text-left">Jugador</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="player, i in players" :key="player.id" class="border-t [&>td]:py-2">
                            <td class="text-center">{{ i + 1 }}</td>
                            <td class="text-left">{{ `${player.first_name} ${player.last_name}` }}</td>
                            <td>
                                <button v-if="player.santion" @click="$event => $emit('paySantion', player)"
                                    class="rounded px-2 py-1 ml-1 text-sm sm:text-lg text-white"
                                    :class="player.santion === 'roja' ? 'bg-red-500' : 'bg-yellow-500'">
                                    <i class="fa fa-dollar"></i>
                                </button>
                                <button v-else @click="$event => $emit('selectPlayer', player.id)"
                                    class="rounded px-2 py-1 ml-1 bg-indigo-500 text-sm sm:text-lg text-white">
                                    <i class="fa fa-check"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </Modal>
</template>