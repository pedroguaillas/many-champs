<script setup>

import ModalSelectClub from './ModalSelectClub.vue';
import { ref } from 'vue';

// Emits & Props

const emit = defineEmits(['selectId'])

const props = defineProps({
    clubs: { type: Array, default: () => [] },
    club_id: { type: Number, default: () => 0 },
    placeholder: { type: String, default: () => '' },
    class: { type: String, default: () => '' }
});

// Refs

const input = ref(null);
const modelText = ref(props.club_id !== 0 ? props.clubs.filter(c => c.id === props.club_id)[0].name : '');
const modal = ref(false);

// Functions

const toggle = () => {
    modal.value = !modal.value
}

const select = (club) => {
    modelText.value = club.name
    emit('selectId', club.id)
    toggle()
}

// Hooks


</script>

<template>
    <div class="flex" :class="class">
        <input ref="input" :placeholder="placeholder"
            class="w-full px-3.5 py-2.5 rounded-l-lg bg-white dark:bg-slate-900/80 border border-gray-300 dark:border-white/[0.08] text-[13px] text-gray-700 dark:text-slate-200 placeholder-gray-400 dark:placeholder-slate-600 focus:outline-none focus:border-emerald-500/40 focus:ring-1 focus:ring-emerald-500/20 transition-colors duration-200"
            :value="modelText" />
        <button @click="toggle" class="px-3 py-2.5 bg-gray-300 dark:bg-slate-700/60 border border-gray-300 dark:border-white/[0.08] rounded-r-lg text-gray-600 dark:text-slate-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-300 dark:bg-slate-700 transition-colors duration-200">
            <i class="fa fa-search"></i>
        </button>
    </div>

    <ModalSelectClub :show="modal" :clubs="clubs" maxWidth="lg" @close="toggle" @select="select" />
</template>