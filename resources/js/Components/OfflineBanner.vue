<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

// Las visitas de Inertia son XHR: sin red fallan en silencio. Este aviso explica por que.
const offline = ref(false);

const update = () => {
    offline.value = !navigator.onLine;
};

onMounted(() => {
    update();
    window.addEventListener('online', update);
    window.addEventListener('offline', update);
});

onUnmounted(() => {
    window.removeEventListener('online', update);
    window.removeEventListener('offline', update);
});
</script>

<template>
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 -translate-y-1"
        leave-active-class="transition duration-150 ease-out"
        leave-to-class="opacity-0"
    >
        <div v-if="offline" role="status"
            class="relative flex items-center gap-3 px-4 sm:px-6 py-2.5 bg-amber-500/[0.06] border-b border-amber-500/20 text-[12px] text-amber-700 dark:text-amber-300">
            <span class="absolute left-0 top-0 bottom-0 w-[3px] bg-amber-500 rounded-r-full"></span>
            <i class="fa-solid fa-plug-circle-xmark text-[11px]" aria-hidden="true"></i>
            <span><span class="font-semibold">Sin conexión.</span> Los cambios no se guardarán hasta que vuelva la señal.</span>
        </div>
    </Transition>
</template>
