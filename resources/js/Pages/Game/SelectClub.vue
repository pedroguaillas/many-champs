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
            class="border-gray-300 w-full dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-l-md shadow-sm"
            :value="modelText" />
        <button @click="toggle" class="py-2 px-3 bg-indigo-500 rounded-r text-white">
            <i class="fa fa-search"></i>
        </button>
    </div>

    <ModalSelectClub :show="modal" :clubs="clubs" maxWidth="lg" @close="toggle" @select="select" />
</template>