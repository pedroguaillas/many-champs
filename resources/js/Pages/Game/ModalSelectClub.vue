<script setup>

import Modal from '@/Components/Modal.vue';

const emit = defineEmits(['close', 'select'])

defineProps({
    clubs: { type: Array, default: () => [] },
    show: { type: Boolean, default: () => false }
});

</script>

<template>
    <Modal :show="show" maxWidth="lg" @close="$emit('close')">

        <div class="p-5">
            <h2 class="text-[15px] font-semibold tracking-tight text-white text-center">Selecionar club</h2>

            <table class="w-full mt-4 text-left">
                <thead>
                    <tr>
                        <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-slate-500">N°</th>
                        <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-slate-500">Club</th>
                        <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-slate-500 w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="club in clubs" :key="club.id"
                        class="border-b border-white/[0.04] hover:bg-white/[0.02] transition-colors duration-150">
                        <td class="px-5 py-3.5 text-[13px] text-slate-400">{{ club.id }}</td>
                        <td class="px-5 py-3.5 text-[13px] text-slate-200">{{ club.name }}</td>
                        <td class="px-5 py-3.5">
                            <button @click="$emit('select', club)"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-emerald-500/10 text-emerald-400 text-xs font-medium border border-emerald-500/20 hover:bg-emerald-500/20 hover:border-emerald-500/30 transition-all duration-200">
                                Seleccionar
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </Modal>
</template>
