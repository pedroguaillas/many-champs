<script setup>

// Imports
import AdminLayout from '@/Layouts/AdminLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import ShareChampionship from '@/Components/ShareChampionship.vue';
import { isHex } from '@/Shared/teamColors';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

// Props
const props = defineProps({
    players: { type: Number, default: () => 0 },
    clubs: { type: Number, default: () => 0 },
    games: { type: Number, default: () => 0 },
    sanctions: { type: Number, default: () => 0 },
    categories: { type: Number, default: () => 0 },
    groups: { type: Number, default: () => 0 },
    live: { type: Array, default: () => [] },
    upcoming: { type: Array, default: () => [] },
})

// Page data
const page = usePage();
const userName = computed(() => (page.props.auth?.user?.name ?? '').split(' ')[0]);
const teamName = computed(() => page.props.auth?.user?.current_team?.name ?? null);
const isPublic = computed(() => !!page.props.auth?.user?.current_team?.is_public);

// La fecha depende del reloj del dispositivo: se calcula al montar (el servidor está en UTC)
const today = ref(null);
onMounted(() => {
    const now = new Date();
    today.value = [now.getFullYear(), now.getMonth() + 1, now.getDate()];
});

// Puesta en marcha: el orden real en que se arma un campeonato
const steps = computed(() => [
    {
        title: 'Crea una categoría',
        help: 'Es el torneo: por ejemplo "Sénior masculino" o "Sub 12".',
        done: props.categories > 0, action: 'Crear categoría', href: route('categories.index'),
    },
    {
        title: 'Arma los grupos',
        help: 'Divide la categoría en grupos: "Grupo A", "Grupo B"...',
        done: props.groups > 0, action: 'Crear grupos', href: route('select.category', 'grupos'),
    },
    {
        title: 'Registra los clubes',
        help: 'Los equipos que compiten, cada uno en su grupo y con el color de su camiseta.',
        done: props.clubs > 1, action: 'Registrar clubes', href: route('select.category', 'clubs'),
    },
    {
        title: 'Inscribe a los jugadores',
        help: 'Entra a un club y agrega a sus jugadores con su número.',
        done: props.players > 0, action: 'Ir a los clubes', href: route('select.category', 'clubs'),
    },
    {
        title: 'Programa los partidos',
        help: 'Genera toda la fase de grupos de una vez o crea los partidos uno por uno.',
        done: props.games > 0, action: 'Crear partidos', href: route('select.category', 'partidos'),
    },
]);
const doneCount = computed(() => steps.value.filter(s => s.done).length);
const currentStep = computed(() => steps.value.findIndex(s => !s.done));
const settingUp = computed(() => currentStep.value !== -1);

const stats = computed(() => [
    { label: 'Clubes', value: props.clubs, href: route('select.category', 'clubs') },
    { label: 'Jugadores', value: props.players, href: route('select.category', 'clubs') },
    { label: 'Partidos', value: props.games, href: route('select.category', 'partidos') },
    { label: 'Sanciones', value: props.sanctions, href: route('santions.index') },
]);

const shortcuts = [
    { label: 'Partidos', icon: 'fa-futbol', href: route('select.category', 'partidos') },
    { label: 'Posiciones', icon: 'fa-ranking-star', href: route('select.category', 'tabla-de-posiciones') },
    { label: 'Goleadores', icon: 'fa-crosshairs', href: route('select.category', 'tabla-de-goleadores') },
    { label: 'Calendario', icon: 'fa-calendar-days', href: route('calendar') },
];

// "sáb 15 nov" a partir del texto de la fecha, sin pasar por zona horaria
const parts = (date) => String(date ?? '').slice(0, 10).split('-').map(Number);
const dayLabel = (date) => {
    const [y, m, d] = parts(date);
    if (!y) return '';
    if (today.value) {
        const diff = Math.round((Date.UTC(y, m - 1, d) - Date.UTC(today.value[0], today.value[1] - 1, today.value[2])) / 86400000);
        if (diff === 0) return 'Hoy';
        if (diff === 1) return 'Mañana';
    }
    return new Intl.DateTimeFormat('es', { weekday: 'short', day: 'numeric', month: 'short' }).format(new Date(y, m - 1, d)).replace(/\./g, '');
};
const hour = (time) => String(time ?? '').match(/(\d{2}:\d{2})(:\d{2})?$/)?.[1] ?? '';
const dot = (color) => isHex(color) ? { backgroundColor: color } : null;

</script>

<template>
    <AdminLayout :title="'Panel de inicio'">

        <div class="max-w-5xl mx-auto space-y-5">

            <!-- Saludo -->
            <header class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h1 class="text-xl sm:text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                        Hola<template v-if="userName">, {{ userName }}</template>
                    </h1>
                    <p v-if="teamName" class="mt-0.5 truncate text-[13px] text-gray-500 dark:text-slate-400">
                        {{ teamName }}<span v-if="isPublic" class="text-emerald-600 dark:text-emerald-400">, compartido</span>
                    </p>
                </div>
                <!-- Enlace público para jugadores e hinchas -->
                <ShareChampionship v-if="games > 0" :text="`Sigue los partidos de ${teamName}`" class="btn-add shrink-0 min-h-[40px]" />
            </header>

            <!-- En juego ahora: lo más urgente va primero -->
            <section v-if="live.length" aria-labelledby="live-heading"
                class="rounded-xl bg-white dark:bg-slate-800/60 border border-red-500/30 overflow-hidden">
                <h2 id="live-heading" class="flex items-center gap-2 px-4 sm:px-5 pt-4 text-[13px] font-semibold text-red-600 dark:text-red-400">
                    <span class="relative flex w-2 h-2">
                        <span class="absolute inset-0 rounded-full bg-red-500 animate-ping opacity-75 motion-reduce:hidden"></span>
                        <span class="relative w-2 h-2 rounded-full bg-red-500"></span>
                    </span>
                    En juego ahora
                </h2>
                <ul class="mt-2 divide-y divide-gray-100 dark:divide-white/[0.04]">
                    <li v-for="game in live" :key="game.id">
                        <Link :href="route('playing.index', game.id)"
                            class="flex items-center gap-3 px-4 sm:px-5 py-3 min-h-[56px] hover:bg-gray-50 dark:hover:bg-white/[0.03] active:bg-gray-100 dark:active:bg-white/[0.05] transition-colors duration-150 focus:outline-none focus-visible:bg-gray-50 dark:focus-visible:bg-white/[0.05]">
                            <div class="flex-1 min-w-0">
                                <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ game.c1name }} vs {{ game.c2name }}</p>
                                <p class="truncate text-[12px] text-gray-500 dark:text-slate-400">{{ game.category }}</p>
                            </div>
                            <span class="shrink-0 inline-flex items-center gap-1.5 text-[12px] font-semibold text-emerald-600 dark:text-emerald-400">
                                Continuar <i class="fa-solid fa-chevron-right text-[10px]" aria-hidden="true"></i>
                            </span>
                        </Link>
                    </li>
                </ul>
            </section>

            <!-- Puesta en marcha: desaparece cuando el campeonato ya tiene partidos -->
            <section v-if="settingUp" aria-labelledby="setup-heading"
                class="rounded-xl bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] overflow-hidden">
                <div class="relative px-4 sm:px-6 py-4 sm:py-5 border-b border-gray-200 dark:border-white/[0.06]">
                    <span class="absolute left-0 top-0 bottom-0 w-[3px] bg-emerald-500 rounded-r-full"></span>
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <h2 id="setup-heading" class="text-[15px] font-semibold tracking-tight text-gray-900 dark:text-white">
                                {{ doneCount === 0 ? 'Arma tu campeonato' : 'Sigue armando tu campeonato' }}
                            </h2>
                            <p class="mt-0.5 text-[13px] text-gray-500 dark:text-slate-400">Cinco pasos y empiezas a jugar.</p>
                        </div>
                        <p class="shrink-0 text-[12px] tabular-nums text-gray-500 dark:text-slate-400">{{ doneCount }} de {{ steps.length }}</p>
                    </div>
                    <div class="mt-3 h-1 rounded-full bg-gray-100 dark:bg-slate-900/80 overflow-hidden" role="progressbar"
                        :aria-valuenow="doneCount" aria-valuemin="0" :aria-valuemax="steps.length" aria-label="Pasos completados">
                        <div class="h-full w-full origin-left rounded-full bg-emerald-500 transition-transform duration-300 ease-out"
                            :style="{ transform: `scaleX(${doneCount / steps.length})` }"></div>
                    </div>
                </div>

                <ol>
                    <li v-for="(step, index) in steps" :key="step.title"
                        class="flex gap-3 sm:gap-4 px-4 sm:px-6 py-3.5 border-b last:border-b-0 border-gray-100 dark:border-white/[0.04]"
                        :class="index === currentStep ? 'bg-emerald-500/[0.04]' : ''"
                        :aria-current="index === currentStep ? 'step' : undefined">
                        <!-- Número del paso, o visto si ya está -->
                        <span class="mt-0.5 w-7 h-7 shrink-0 rounded-full flex items-center justify-center text-[12px] font-semibold tabular-nums"
                            :class="step.done
                                ? 'bg-emerald-500 text-white'
                                : index === currentStep
                                    ? 'border-2 border-emerald-500 text-emerald-600 dark:text-emerald-400'
                                    : 'border border-gray-300 dark:border-white/[0.12] text-gray-400 dark:text-slate-500'">
                            <i v-if="step.done" class="fa-solid fa-check text-[11px]" aria-hidden="true"></i>
                            <template v-else>{{ index + 1 }}</template>
                        </span>
                        <div class="flex-1 min-w-0 sm:flex sm:items-center sm:gap-4">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold"
                                    :class="step.done ? 'text-gray-500 dark:text-slate-400' : index === currentStep ? 'text-gray-900 dark:text-white' : 'text-gray-700 dark:text-slate-300'">
                                    {{ step.title }}
                                    <span v-if="step.done" class="sr-only">(listo)</span>
                                </p>
                                <p v-if="!step.done" class="mt-0.5 text-[13px] text-gray-500 dark:text-slate-400">{{ step.help }}</p>
                            </div>
                            <Link v-if="index === currentStep" :href="step.href" class="btn-primary mt-3 sm:mt-0 shrink-0">
                                {{ step.action }}
                            </Link>
                        </div>
                    </li>
                </ol>
            </section>

            <div class="grid lg:grid-cols-3 gap-5">

                <!-- Próximos partidos -->
                <section aria-labelledby="upcoming-heading"
                    class="lg:col-span-2 rounded-xl bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] overflow-hidden">
                    <div class="flex items-center justify-between gap-3 px-4 sm:px-5 py-3.5 border-b border-gray-200 dark:border-white/[0.06]">
                        <h2 id="upcoming-heading" class="text-[13px] font-semibold text-gray-900 dark:text-white">Próximos partidos</h2>
                        <Link :href="route('calendar')" class="-my-2 py-2 text-[12px] font-medium text-emerald-600 dark:text-emerald-400 hover:underline rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40">
                            Ver calendario
                        </Link>
                    </div>

                    <ul v-if="upcoming.length" class="divide-y divide-gray-100 dark:divide-white/[0.04]">
                        <li v-for="game in upcoming" :key="game.id">
                            <Link :href="route('games.select-players', game.id)"
                                class="flex items-center gap-3 sm:gap-4 px-4 sm:px-5 py-3 min-h-[60px] hover:bg-gray-50 dark:hover:bg-white/[0.03] active:bg-gray-100 dark:active:bg-white/[0.05] transition-colors duration-150 focus:outline-none focus-visible:bg-gray-50 dark:focus-visible:bg-white/[0.05]">
                                <div class="w-16 shrink-0">
                                    <p class="text-[12px] font-semibold capitalize text-gray-900 dark:text-white">{{ dayLabel(game.date) }}</p>
                                    <p class="text-[12px] tabular-nums text-gray-500 dark:text-slate-400">{{ hour(game.time) }}</p>
                                </div>
                                <div class="flex-1 min-w-0 space-y-0.5">
                                    <p v-for="side in [{ name: game.c1name, color: game.c1color }, { name: game.c2name, color: game.c2color }]" :key="side.name"
                                        class="flex items-center gap-2 text-sm font-medium text-gray-800 dark:text-slate-200">
                                        <span class="w-2.5 h-2.5 shrink-0 rounded-full ring-1 ring-inset ring-black/10 dark:ring-white/20 bg-gray-300 dark:bg-slate-600" :style="dot(side.color)"></span>
                                        <span class="truncate">{{ side.name }}</span>
                                    </p>
                                    <p class="truncate text-[11px] text-gray-400 dark:text-slate-500">{{ game.category }}</p>
                                </div>
                                <span class="shrink-0 inline-flex items-center gap-1.5 text-[12px] font-medium text-gray-400 dark:text-slate-500">
                                    <span class="hidden sm:inline">Iniciar</span>
                                    <i class="fa-solid fa-chevron-right text-[10px]" aria-hidden="true"></i>
                                </span>
                            </Link>
                        </li>
                    </ul>

                    <EmptyState v-else-if="games > 0" icon="fa-regular fa-calendar" title="No hay partidos por jugar"
                        description="Todos los partidos programados ya se jugaron o tienen fecha pasada. Programa los siguientes.">
                        <Link :href="route('select.category', 'partidos')" class="btn-add">Ir a partidos</Link>
                    </EmptyState>
                    <EmptyState v-else icon="fa-regular fa-calendar" title="Aún no hay partidos"
                        :description="settingUp ? 'Aparecerán aquí cuando termines los pasos de arriba.' : 'Programa el primero para verlo aquí.'" />
                </section>

                <div class="space-y-5">
                    <!-- Resumen: una sola tarjeta, cada cifra lleva a su sección -->
                    <section aria-label="Resumen del campeonato"
                        class="rounded-xl bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] overflow-hidden">
                        <div class="grid grid-cols-2">
                            <Link v-for="(stat, index) in stats" :key="stat.label" :href="stat.href"
                                class="px-4 py-3.5 border-gray-100 dark:border-white/[0.04] hover:bg-gray-50 dark:hover:bg-white/[0.03] active:bg-gray-100 dark:active:bg-white/[0.05] transition-colors duration-150 focus:outline-none focus-visible:bg-gray-50 dark:focus-visible:bg-white/[0.05]"
                                :class="[index % 2 === 0 ? 'border-r' : '', index < 2 ? 'border-b' : '']">
                                <p class="text-2xl font-semibold tabular-nums leading-none text-gray-900 dark:text-white">{{ stat.value }}</p>
                                <p class="mt-1.5 text-[12px] text-gray-500 dark:text-slate-400">{{ stat.label }}</p>
                            </Link>
                        </div>
                    </section>

                    <!-- Accesos -->
                    <nav aria-label="Accesos rápidos"
                        class="rounded-xl bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] overflow-hidden divide-y divide-gray-100 dark:divide-white/[0.04]">
                        <Link v-for="item in shortcuts" :key="item.label" :href="item.href"
                            class="flex items-center gap-3 px-4 min-h-[48px] text-[13px] font-medium text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/[0.03] active:bg-gray-100 dark:active:bg-white/[0.05] transition-colors duration-150 focus:outline-none focus-visible:bg-gray-50 dark:focus-visible:bg-white/[0.05]">
                            <i class="fa-solid w-4 text-center text-[13px] text-emerald-500 dark:text-emerald-400" :class="item.icon" aria-hidden="true"></i>
                            <span class="flex-1">{{ item.label }}</span>
                            <i class="fa-solid fa-chevron-right text-[10px] text-gray-300 dark:text-slate-600" aria-hidden="true"></i>
                        </Link>
                    </nav>
                </div>
            </div>

        </div>

    </AdminLayout>
</template>
