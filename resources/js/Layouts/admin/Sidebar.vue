<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const emit = defineEmits(['toggle', 'close']);

const props = defineProps({
    open: Boolean
});

const page = usePage();

const isActive = (routeName, param) => {
    if (param) {
        return route().current(routeName, param);
    }
    return route().current(routeName);
};

const isLinkActive = (checks) => {
    return checks.some(([routeName, param]) => isActive(routeName, param));
};

const closeMobile = () => {
    emit('close');
};

const isSuperAdmin = computed(() => page.props.is_superadmin ?? false);

const navGroups = computed(() => {
    const groups = [
    {
        label: 'Principal',
        items: [
            {
                label: 'Dashboard',
                icon: 'fa-solid fa-gauge-high',
                href: () => route('dashboard'),
                active: () => isLinkActive([['dashboard']]),
            },
        ],
    },
    {
        label: 'Gestión',
        items: [
            {
                label: 'Clubes',
                icon: 'fa-solid fa-shield-halved',
                href: () => route('select.category', 'clubs'),
                active: () => isLinkActive([['select.category', 'clubs'], ['clubs.index']]),
            },
            {
                label: 'Partidos',
                icon: 'fa-solid fa-futbol',
                href: () => route('select.category', 'partidos'),
                active: () => isLinkActive([['select.category', 'partidos'], ['games.index']]),
            },
            {
                label: 'Calendario',
                icon: 'fa-solid fa-calendar-days',
                href: () => route('calendar'),
                active: () => isLinkActive([['calendar']]),
            },
        ],
    },
    {
        label: 'Estadísticas',
        items: [
            {
                label: 'Posiciones',
                icon: 'fa-solid fa-ranking-star',
                href: () => route('select.category', 'tabla-de-posiciones'),
                active: () => isLinkActive([['select.category', 'tabla-de-posiciones'], ['tabla-de-posiciones.index']]),
            },
            {
                label: 'Goleadores',
                icon: 'fa-solid fa-crosshairs',
                href: () => route('select.category', 'tabla-de-goleadores'),
                active: () => isLinkActive([['select.category', 'tabla-de-goleadores'], ['tabla-de-goleadores.index']]),
            },
            {
                label: 'Sanciones',
                icon: 'fa-solid fa-square',
                href: () => route('santions.index'),
                active: () => isLinkActive([['santions.index']]),
            },
        ],
    },
    {
        label: 'Administración',
        items: [
            {
                label: 'Económico',
                icon: 'fa-solid fa-coins',
                href: () => route('diary'),
                active: () => isLinkActive([['diary']]),
            },
            {
                label: 'Categorías',
                icon: 'fa-solid fa-layer-group',
                href: () => route('categories.index'),
                active: () => isLinkActive([['categories.index'], ['groups.index']]),
            },
        ],
    },
];

    if (isSuperAdmin.value) {
        groups.push({
            label: 'Super Admin',
            items: [
                {
                    label: 'Campeonatos',
                    icon: 'fa-solid fa-trophy',
                    href: () => route('admin.championships.index'),
                    active: () => isLinkActive([['admin.championships.index'], ['admin.championships.create']]),
                },
            ],
        });
    }

    return groups;
});
</script>

<template>
    <!-- Overlay (mobile only) -->
    <Transition
        enter-active-class="transition-opacity duration-300 ease-in-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-300 ease-in-out"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="open"
            class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 sm:hidden"
            @click="closeMobile"
        />
    </Transition>

    <!-- Sidebar -->
    <aside
        class="fixed sm:sticky top-0 left-0 z-50 h-screen w-[280px] sm:w-[260px] flex flex-col bg-white dark:bg-slate-950 border-r border-gray-200 dark:border-white/[0.08] transform transition-transform duration-300 ease-in-out sm:translate-x-0 sm:flex-shrink-0"
        :class="open ? 'translate-x-0' : '-translate-x-full'"
    >
        <!-- Brand -->
        <div class="flex items-center justify-between px-5 py-5 border-b border-gray-200 dark:border-white/[0.08]">
            <Link :href="route('dashboard')" class="flex items-center gap-3 group" @click="closeMobile">
                <div class="w-9 h-9 rounded-lg bg-emerald-500 flex items-center justify-center shadow-lg shadow-emerald-500/20 group-hover:shadow-emerald-500/40 transition-shadow duration-300">
                    <span class="text-white font-bold text-sm tracking-tighter">5IV</span>
                </div>
                <div>
                    <span class="text-gray-900 dark:text-white font-semibold tracking-tight text-[15px]">Many Champs</span>
                    <span class="block text-[10px] text-gray-400 dark:text-slate-500 font-medium uppercase tracking-widest">Sport Manager</span>
                </div>
            </Link>
            <button
                @click="closeMobile"
                class="sm:hidden w-8 h-8 flex items-center justify-center rounded-lg text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors duration-200"
            >
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-6 scrollbar-thin">
            <div v-for="group in navGroups" :key="group.label">
                <p class="px-3 mb-2 text-[10px] font-semibold uppercase tracking-widest text-gray-400 dark:text-slate-500">
                    {{ group.label }}
                </p>
                <ul class="space-y-0.5">
                    <li v-for="item in group.items" :key="item.label">
                        <Link
                            :href="item.href()"
                            @click="closeMobile"
                            class="group relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13px] font-medium transition-all duration-200"
                            :class="item.active()
                                ? 'text-gray-900 dark:text-white bg-gray-100 dark:bg-slate-800/80'
                                : 'text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-slate-800/50'"
                        >
                            <!-- Active indicator bar -->
                            <span
                                v-if="item.active()"
                                class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 bg-emerald-500 rounded-r-full"
                            />
                            <i
                                :class="[
                                    item.icon,
                                    'w-5 text-center text-sm transition-colors duration-200',
                                    item.active()
                                        ? 'text-emerald-500 dark:text-emerald-400'
                                        : 'text-gray-400 dark:text-slate-500 group-hover:text-gray-600 dark:group-hover:text-slate-300'
                                ]"
                            ></i>
                            <span>{{ item.label }}</span>
                            <!-- Sanciones: tarjeta icon trick -->
                            <span
                                v-if="item.label === 'Sanciones'"
                                class="absolute right-0 top-1/2 -translate-y-1/2 w-2.5 h-3.5 rounded-[2px] mr-3"
                                :class="item.active() ? 'bg-yellow-400' : 'bg-yellow-500/30'"
                            />
                        </Link>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Footer -->
        <div class="px-5 py-4 border-t border-gray-200 dark:border-white/[0.08]">
            <div class="flex items-center gap-2 text-[11px] text-gray-400 dark:text-slate-600">
                <i class="fa-solid fa-futbol text-emerald-600/50"></i>
                <span>5IV Sport &copy; {{ new Date().getFullYear() }}</span>
            </div>
        </div>
    </aside>
</template>
