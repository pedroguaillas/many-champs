<script setup>

import { ref } from 'vue';
import Sidebar from '@/Components/admin/Sidebar.vue';
import { Head } from '@inertiajs/vue3';
import Header from '@/Components/admin/Header.vue';

defineProps({
    title: String
});

const menu = ref(false);

const toggle = () => {
    menu.value = !menu.value;
};

</script>

<template>
    <Head :title="title" />

    <div class="bg-slate-200 h-screen">

        <Sidebar :menu="menu" />
        <Header :menu="menu" @toggle="toggle" />

        <main :class="{ 'sm:ml-[16em] sm:w-[100%-16em]': menu, 'ml-0 w-full': !menu }" class="ease-out duration-200 z-50">
            <slot />
        </main>
    </div>
</template>