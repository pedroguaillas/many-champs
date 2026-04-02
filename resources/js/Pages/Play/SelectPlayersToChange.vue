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

        <div class="p-5">
            <h1 class="text-[15px] font-semibold tracking-tight text-white text-center">Seleccionar jugador</h1>

            <div class="w-full overflow-x-auto mt-4">
                <table class="w-full text-left">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 w-1">N°</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Jugador</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="player, i in players" :key="player.id"
                            class="border-b border-gray-100 dark:border-white/[0.04] hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150">
                            <td class="px-5 py-3.5 text-[13px] text-gray-500 dark:text-slate-400">{{ i + 1 }}</td>
                            <td class="px-5 py-3.5 text-[13px] text-gray-700 dark:text-slate-200">{{ `${player.first_name} ${player.last_name}` }}</td>
                            <td class="px-5 py-3.5">
                                <button v-if="player.santion" @click="$event => $emit('paySantion', player)"
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[11px] font-medium transition-colors duration-150"
                                    :class="player.santion === 'roja' ? 'bg-red-500/10 text-red-400 border border-red-500/20 hover:bg-red-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20 hover:bg-amber-500/20'">
                                    <i class="fa fa-dollar"></i>
                                </button>
                                <button v-else @click="$event => $emit('selectPlayer', player.id)"
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-emerald-500/10 text-emerald-400 text-[11px] font-medium border border-emerald-500/20 hover:bg-emerald-500/20 transition-colors duration-150">
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
