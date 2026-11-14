<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: { type: String, required: true },
    label: { type: String, default: 'Regresar' },
});
</script>

<template>
    <!-- Área táctil de 40px -->
    <Link :href="href"
        class="group inline-flex items-center gap-2 min-h-[40px] mb-2 pr-3 text-xs font-medium text-gray-500 dark:text-slate-500 hover:text-gray-800 dark:hover:text-slate-300 transition-colors duration-150">
        <span class="w-7 h-7 inline-flex items-center justify-center rounded-lg border border-gray-200 dark:border-white/[0.08] group-hover:border-gray-300 dark:group-hover:border-white/[0.16] transition-colors duration-150">
            <i class="fa-solid fa-arrow-left text-[10px]"></i>
        </span>
        {{ label }}
    </Link>
</template>
