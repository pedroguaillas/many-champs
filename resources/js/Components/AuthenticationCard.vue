<script setup>
// Marco de las pantallas de acceso (ingresar, recuperar contraseña, verificar...).
// Siempre oscuro, como el estadio de noche: la clase "dark" aquí activa las variantes
// dark: de los campos sin tocar el tema que eligió el usuario.
defineProps({
    title: { type: String, default: '' },
    description: { type: String, default: '' },
});
</script>

<template>
    <div class="dark relative min-h-[100svh] flex flex-col bg-slate-950 text-slate-300 overflow-hidden
                pt-[env(safe-area-inset-top)] pb-[env(safe-area-inset-bottom)]">

        <!-- Líneas de la cancha: círculo central y media cancha -->
        <svg class="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-[1100px] max-w-none h-[1100px] text-white/[0.035]"
            viewBox="0 0 1100 1100" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <circle cx="550" cy="550" r="330" />
            <circle cx="550" cy="550" r="5" fill="currentColor" />
            <line x1="0" y1="550" x2="1100" y2="550" />
        </svg>

        <main class="relative flex-1 flex flex-col items-center justify-center px-4 py-8">
            <slot name="logo" />

            <div class="w-full max-w-sm mt-7 rounded-xl bg-slate-900 border border-white/[0.08] overflow-hidden">
                <div v-if="title" class="relative px-5 sm:px-6 pt-5">
                    <span class="absolute left-0 top-5 bottom-0 w-[3px] bg-emerald-500 rounded-r-full"></span>
                    <h1 class="text-lg font-semibold tracking-tight text-white">{{ title }}</h1>
                    <p v-if="description" class="mt-1 text-[13px] leading-relaxed text-slate-400">{{ description }}</p>
                </div>
                <div class="px-5 sm:px-6 py-5">
                    <slot />
                </div>
            </div>

            <div v-if="$slots.footer" class="w-full max-w-sm mt-5 text-center text-[13px] text-slate-500">
                <slot name="footer" />
            </div>
        </main>
    </div>
</template>
