<script setup>

// Imports
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';

// Props
defineProps({
    players: { type: Number, default: () => 0 },
    clubs: { type: Number, default: () => 0 },
    games: { type: Number, default: () => 0 },
    sanctions: { type: Number, default: () => 0 }
})

// Page data
const page = usePage();
const userName = computed(() => page.props.auth?.user?.name ?? 'Organizador');
const teamName = computed(() => page.props.auth?.user?.current_team?.name ?? null);

// Current date formatted in Spanish
const currentDate = computed(() => {
    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const formatted = now.toLocaleDateString('es-ES', options);
    return formatted.charAt(0).toUpperCase() + formatted.slice(1);
});

// Current hour for greeting
const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Buenos días';
    if (hour < 18) return 'Buenas tardes';
    return 'Buenas noches';
});

// Mount animation
const mounted = ref(false);
onMounted(() => {
    setTimeout(() => { mounted.value = true; }, 50);
});

</script>

<template>
    <AdminLayout :title="'Panel de inicio'">

        <div class="max-w-7xl mx-auto space-y-8">

            <!-- Header / Welcome -->
            <div
                class="transition-all duration-700 ease-out"
                :class="mounted ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
            >
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">
                            {{ greeting }}, {{ userName }}
                        </h1>
                        <p class="text-slate-500 mt-1 text-sm sm:text-base">
                            <template v-if="teamName">
                                <i class="fas fa-trophy text-amber-500 mr-1.5"></i>
                                {{ teamName }}
                                <span class="mx-2 text-slate-300">|</span>
                            </template>
                            Centro de mando del torneo
                        </p>
                    </div>
                    <div class="text-sm text-slate-400 font-medium tabular-nums">
                        <i class="far fa-calendar mr-1.5"></i>
                        {{ currentDate }}
                    </div>
                </div>
            </div>

            <!-- Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

                <!-- Jugadores -->
                <div
                    class="group bg-white border border-slate-200 rounded-xl shadow-sm p-5
                           hover:shadow-md hover:scale-[1.02] transition-all duration-300 ease-out cursor-default"
                    :class="mounted ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
                    :style="{ transitionDelay: '100ms' }"
                >
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Jugadores</p>
                            <p class="text-4xl font-bold text-slate-900 tabular-nums leading-none">{{ players }}</p>
                            <p class="text-xs text-slate-400 mt-2">
                                <i class="fas fa-shirt mr-1 text-emerald-400"></i>
                                Registrados en el torneo
                            </p>
                        </div>
                        <div class="w-11 h-11 rounded-lg bg-emerald-50 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-users text-emerald-500 text-lg"></i>
                        </div>
                    </div>
                    <div class="mt-4 h-1 rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full rounded-full bg-emerald-400 transition-all duration-1000 ease-out"
                             :style="{ width: mounted ? '100%' : '0%' }"></div>
                    </div>
                </div>

                <!-- Clubes -->
                <div
                    class="group bg-white border border-slate-200 rounded-xl shadow-sm p-5
                           hover:shadow-md hover:scale-[1.02] transition-all duration-300 ease-out cursor-default"
                    :class="mounted ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
                    :style="{ transitionDelay: '200ms' }"
                >
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Clubes</p>
                            <p class="text-4xl font-bold text-slate-900 tabular-nums leading-none">{{ clubs }}</p>
                            <p class="text-xs text-slate-400 mt-2">
                                <i class="fas fa-shield-halved mr-1 text-blue-400"></i>
                                Equipos compitiendo
                            </p>
                        </div>
                        <div class="w-11 h-11 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-medal text-blue-500 text-lg"></i>
                        </div>
                    </div>
                    <div class="mt-4 h-1 rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full rounded-full bg-blue-400 transition-all duration-1000 ease-out"
                             :style="{ width: mounted ? '100%' : '0%' }"></div>
                    </div>
                </div>

                <!-- Partidos -->
                <div
                    class="group bg-white border border-slate-200 rounded-xl shadow-sm p-5
                           hover:shadow-md hover:scale-[1.02] transition-all duration-300 ease-out cursor-default"
                    :class="mounted ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
                    :style="{ transitionDelay: '300ms' }"
                >
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Partidos</p>
                            <p class="text-4xl font-bold text-slate-900 tabular-nums leading-none">{{ games }}</p>
                            <p class="text-xs text-slate-400 mt-2">
                                <i class="fas fa-calendar-check mr-1 text-amber-400"></i>
                                Encuentros programados
                            </p>
                        </div>
                        <div class="w-11 h-11 rounded-lg bg-amber-50 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-futbol text-amber-500 text-lg"></i>
                        </div>
                    </div>
                    <div class="mt-4 h-1 rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full rounded-full bg-amber-400 transition-all duration-1000 ease-out"
                             :style="{ width: mounted ? '100%' : '0%' }"></div>
                    </div>
                </div>

                <!-- Sanciones -->
                <div
                    class="group bg-white border border-slate-200 rounded-xl shadow-sm p-5
                           hover:shadow-md hover:scale-[1.02] transition-all duration-300 ease-out cursor-default"
                    :class="mounted ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
                    :style="{ transitionDelay: '400ms' }"
                >
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Sanciones</p>
                            <p class="text-4xl font-bold text-slate-900 tabular-nums leading-none">{{ sanctions }}</p>
                            <p class="text-xs text-slate-400 mt-2">
                                <i class="fas fa-square mr-1 text-rose-400"></i>
                                Tarjetas acumuladas
                            </p>
                        </div>
                        <div class="w-11 h-11 rounded-lg bg-rose-50 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-triangle-exclamation text-rose-500 text-lg"></i>
                        </div>
                    </div>
                    <div class="mt-4 h-1 rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full rounded-full bg-rose-400 transition-all duration-1000 ease-out"
                             :style="{ width: mounted ? '100%' : '0%' }"></div>
                    </div>
                </div>

            </div>

            <!-- Bottom section: Quick Actions + Empty state -->
            <div
                class="grid grid-cols-1 lg:grid-cols-3 gap-5 transition-all duration-700 ease-out"
                :class="mounted ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
                :style="{ transitionDelay: '500ms' }"
            >

                <!-- Quick Actions -->
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-400 mb-4">
                        Acciones rápidas
                    </h3>
                    <div class="space-y-3">
                        <a :href="route('categories.index')"
                           class="flex items-center gap-3 px-4 py-3 rounded-lg border border-slate-200
                                  text-slate-700 text-sm font-medium
                                  hover:bg-emerald-50 hover:border-emerald-200 hover:text-emerald-700
                                  transition-colors duration-200">
                            <i class="fas fa-trophy text-emerald-500"></i>
                            Ver categorías
                        </a>
                        <a :href="route('dashboard')"
                           class="flex items-center gap-3 px-4 py-3 rounded-lg border border-slate-200
                                  text-slate-700 text-sm font-medium
                                  hover:bg-blue-50 hover:border-blue-200 hover:text-blue-700
                                  transition-colors duration-200">
                            <i class="fas fa-chart-line text-blue-500"></i>
                            Ver estadísticas
                        </a>
                        <a :href="route('categories.index')"
                           class="flex items-center gap-3 px-4 py-3 rounded-lg border border-slate-200
                                  text-slate-700 text-sm font-medium
                                  hover:bg-amber-50 hover:border-amber-200 hover:text-amber-700
                                  transition-colors duration-200">
                            <i class="fas fa-futbol text-amber-500"></i>
                            Ir a partidos
                        </a>
                    </div>
                </div>

                <!-- Activity / Upcoming matches empty state -->
                <div class="lg:col-span-2 bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-400 mb-4">
                        Próximos partidos
                    </h3>
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                            <i class="fas fa-futbol text-2xl text-slate-300"></i>
                        </div>
                        <p class="text-slate-500 font-medium text-sm">No hay partidos programados</p>
                        <p class="text-slate-400 text-xs mt-1">
                            Los próximos encuentros aparecerán aquí
                        </p>
                    </div>
                </div>

            </div>

        </div>

    </AdminLayout>
</template>
