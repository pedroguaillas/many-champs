<script setup>

// Imports
import Header from './admin/Header.vue';
import Sidebar from './admin/Sidebar.vue';
import { Head } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

// Props
defineProps({
    title: String
});

// Refs
const divRef = ref(null);
const menu = ref(false);

const toggle = () => {
    menu.value = !menu.value;
};

watch(divRef, () => {
    if (divRef.value.getBoundingClientRect().width > 640) {
        toggle();
    }
})

</script>

<template>
    <Head :title="title" />

    <div ref="divRef" class="bg-slate-200 h-screen">

        <Sidebar :menu="menu" />
        <Header :menu="menu" @toggle="toggle" />

        <main :class="{ 'sm:ml-[16em] sm:w-[100%-16em]': menu, 'ml-0 w-full': !menu }" class="ease-out duration-200 z-50">
            <slot />
        </main>
    </div>
</template>