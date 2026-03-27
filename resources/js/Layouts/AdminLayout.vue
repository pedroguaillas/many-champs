<script setup>
import Header from './admin/Header.vue';
import Sidebar from './admin/Sidebar.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    title: String
});

const sidebarOpen = ref(false);

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const closeSidebar = () => {
    sidebarOpen.value = false;
};

const handleKeydown = (e) => {
    if (e.key === 'Escape' && sidebarOpen.value) {
        closeSidebar();
    }
};

onMounted(() => {
    document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <Head :title="title" />

    <div class="flex h-screen w-screen overflow-hidden bg-slate-900">
        <!-- Sidebar -->
        <Sidebar :open="sidebarOpen" @toggle="toggleSidebar" @close="closeSidebar" />

        <!-- Main area -->
        <div class="flex flex-col flex-1 min-w-0">
            <Header :open="sidebarOpen" :title="title" @toggle="toggleSidebar" />
            <main class="flex-1 overflow-y-auto bg-slate-200">
                <div class="p-4 sm:p-6">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
