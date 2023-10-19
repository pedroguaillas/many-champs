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
    // divRef si altura del main mas altura del Header > h-screen ? h-full : h-screen
})

</script>

<template>
    <Head :title="title" />

    <div ref="divRef" class="w-screen h-screen flex">

        <!-- Sidebar -->
        <Sidebar :menu="menu" @toggle="toggle" />
        <!-- <div class="w-[400px] h-full bg-gray-200 text-white">
            <div class="h-[50px] bg-gray-900">
                Header
            </div>
            <div class="h-[calc(100vh-50px)] bg-gray-800">
                menu
            </div>
        </div> -->

        <!-- Main -->
        <div class="w-full h-full">
            <Header :menu="menu" @toggle="toggle" />
            <!-- <div class="h-[50px] bg-gray-900">
                Header sidebar
            </div> -->
            <div class="h-[calc(100vh-50px)] bg-slate-200 p-4 overflow-y-auto">
                <slot />

            </div>
        </div>
    </div>

    <!-- <div ref="divRef" class="bg-slate-200 h-screen">

        <Sidebar :menu="menu" />
        <Header :menu="menu" @toggle="toggle" />

        <main :class="{ 'sm:ml-[16em] sm:w-[100%-16em]': menu, 'ml-0 w-full': !menu }" class="ease-out duration-200 z-50 h-[calc(100vh - 4em)] px-4 pt-[5em] pb-4 overflow-y-auto">
            <slot />
        </main>
    </div> -->
</template>