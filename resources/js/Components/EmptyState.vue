<script setup>
// Pantalla o sección sin datos: dice qué falta, para qué sirve y cuál es el siguiente paso.
// Las acciones van en el slot (btn-primary para la principal, enlace secundario al lado).
defineProps({
    icon: { type: String, default: 'fa-solid fa-folder-open' },
    title: { type: String, required: true },
    description: { type: String, default: '' },
    // Dentro de una tarjeta que ya tiene borde no se dibuja otro
    bordered: { type: Boolean, default: false },
});
</script>

<template>
    <div class="flex flex-col items-center text-center px-6 py-12"
        :class="bordered ? 'rounded-xl border border-dashed border-gray-300 dark:border-white/[0.1]' : ''">
        <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-slate-800 border border-gray-200 dark:border-white/[0.06] flex items-center justify-center mb-4">
            <i class="text-lg text-gray-400 dark:text-slate-500" :class="icon" aria-hidden="true"></i>
        </div>
        <h3 class="text-[14px] font-semibold text-gray-800 dark:text-slate-200">{{ title }}</h3>
        <p v-if="description || $slots.description" class="mt-1 max-w-sm text-[13px] text-gray-500 dark:text-slate-400">
            <slot name="description">{{ description }}</slot>
        </p>
        <div v-if="$slots.default" class="mt-5 flex flex-wrap items-center justify-center gap-x-4 gap-y-2">
            <slot />
        </div>
    </div>
</template>
