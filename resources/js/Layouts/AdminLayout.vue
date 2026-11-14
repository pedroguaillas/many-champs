<script setup>
import Header from './admin/Header.vue';
import Sidebar from './admin/Sidebar.vue';
import OfflineBanner from '@/Components/OfflineBanner.vue';
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

    <div class="flex h-[100dvh] w-full overflow-hidden bg-gray-50 dark:bg-slate-900">
        <!-- Sidebar -->
        <Sidebar :open="sidebarOpen" @toggle="toggleSidebar" @close="closeSidebar" />

        <!-- Main area -->
        <div class="flex flex-col flex-1 min-w-0">
            <Header :open="sidebarOpen" :title="title" @toggle="toggleSidebar" />
            <OfflineBanner />
            <main class="flex-1 overflow-y-auto overscroll-y-contain pb-[env(safe-area-inset-bottom)] bg-gray-50 dark:bg-slate-900">
                <div class="p-4 sm:p-6">
                    <slot />
                </div>
            </main>
            <!-- Barra de acción fija de la página: fuera del scroll, sobre la zona segura -->
            <div v-if="$slots.footer"
                class="shrink-0 border-t border-gray-200 dark:border-white/[0.06] bg-white dark:bg-slate-950 pb-[env(safe-area-inset-bottom)]">
                <slot name="footer" />
            </div>
        </div>
    </div>
</template>
