<script setup>
import ShareButton from '@/Components/ShareButton.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

// Marco de las páginas públicas de un campeonato: solo lectura, siempre oscuro.
const props = defineProps({
    title: { type: String, required: true },
    championship: { type: Object, required: true },
    // Texto que acompaña al enlace al compartir esta página
    shareText: { type: String, default: '' },
});

// El dominio solo se conoce en el navegador; la ruta sigue a la página actual
// (al cambiar de día en el calendario el layout no se vuelve a montar)
const page = usePage()
const origin = ref('')
onMounted(() => { origin.value = window.location.origin })
const url = computed(() => origin.value ? origin.value + page.url.split('#')[0] : '')

const tabs = computed(() => [
    { label: 'Inicio', href: route('public.championship', props.championship.slug), active: route().current('public.championship') || route().current('public.category') },
    { label: 'Calendario', href: route('public.calendar', props.championship.slug), active: route().current('public.calendar') },
])
</script>

<template>
    <Head :title="title" />

    <div class="dark min-h-[100svh] flex flex-col bg-slate-950 text-slate-300">
        <header class="border-b border-white/[0.06] pt-[env(safe-area-inset-top)]">
            <div class="max-w-3xl mx-auto px-4 sm:px-6">
                <div class="flex items-center gap-3 h-14">
                    <Link :href="route('public.championship', championship.slug)" class="flex-1 min-w-0 flex items-center gap-2.5 rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/50">
                        <span class="w-8 h-8 shrink-0 rounded-lg bg-emerald-500/10 flex items-center justify-center" aria-hidden="true">
                            <i class="fa-solid fa-trophy text-[13px] text-emerald-400"></i>
                        </span>
                        <span class="truncate text-[15px] font-semibold tracking-tight text-white">{{ championship.name }}</span>
                    </Link>
                    <ShareButton v-if="url" :url="url" :title="title" :text="shareText || title"
                        class="btn-add shrink-0 min-h-[40px]" />
                </div>
                <nav class="flex gap-1 -mb-px" aria-label="Secciones del campeonato">
                    <Link v-for="tab in tabs" :key="tab.label" :href="tab.href" :aria-current="tab.active ? 'page' : undefined"
                        class="px-3 min-h-[44px] inline-flex items-center border-b-2 text-[13px] font-medium transition-colors duration-150 focus:outline-none focus-visible:bg-white/[0.05]"
                        :class="tab.active ? 'border-emerald-500 text-white' : 'border-transparent text-slate-400 hover:text-slate-200'">
                        {{ tab.label }}
                    </Link>
                </nav>
            </div>
        </header>

        <main class="flex-1 w-full max-w-3xl mx-auto px-4 sm:px-6 py-5 space-y-5">
            <slot />
        </main>

        <footer class="border-t border-white/[0.06] pb-[env(safe-area-inset-bottom)]">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 py-4 flex flex-wrap items-center justify-between gap-x-4 gap-y-1 text-[12px] text-slate-500">
                <Link :href="route('home')" class="py-2 hover:text-slate-300 rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40">
                    Hecho con <span class="font-semibold text-slate-400">Many Champs</span>
                </Link>
                <Link :href="route('login')" class="py-2 font-medium text-emerald-400 hover:text-emerald-300 rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40">
                    Soy organizador
                </Link>
            </div>
        </footer>
    </div>
</template>
