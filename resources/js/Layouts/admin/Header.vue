<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';

const emit = defineEmits(['toggle']);

const props = defineProps({
    open: Boolean,
    title: String,
});

const page = usePage();

const user = computed(() => page.props.auth?.user);
const userName = computed(() => user.value?.name ?? 'Usuario');
const userInitials = computed(() => {
    const name = userName.value;
    const parts = name.split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
});
const teamName = computed(() => user.value?.current_team?.name ?? null);
const allTeams = computed(() => user.value?.all_teams ?? []);
const currentTeamId = computed(() => user.value?.current_team_id);
const profilePhotoUrl = computed(() => user.value?.profile_photo_url ?? null);
const hasProfilePhoto = computed(() => {
    return page.props.jetstream?.managesProfilePhotos && profilePhotoUrl.value;
});

const showUserMenu = ref(false);
const showTeamMenu = ref(false);

// Theme management
const theme = ref('dark');

const applyTheme = (mode) => {
    theme.value = mode;
    localStorage.setItem('theme', mode);

    if (mode === 'dark') {
        document.documentElement.classList.add('dark');
    } else if (mode === 'light') {
        document.documentElement.classList.remove('dark');
    } else {
        // system
        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }
};

onMounted(() => {
    const saved = localStorage.getItem('theme') || 'dark';
    applyTheme(saved);

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        if (theme.value === 'system') {
            applyTheme('system');
        }
    });
});

const logout = () => {
    router.post(route('logout'));
};

const toggleUserMenu = () => {
    showUserMenu.value = !showUserMenu.value;
    showTeamMenu.value = false;
};

const closeUserMenu = () => {
    showUserMenu.value = false;
};

const toggleTeamMenu = () => {
    showTeamMenu.value = !showTeamMenu.value;
    showUserMenu.value = false;
};

const closeTeamMenu = () => {
    showTeamMenu.value = false;
};

const switchToTeam = (team) => {
    router.put(route('current-team.update'), {
        team_id: team.id,
    }, {
        preserveState: false,
    });
};
</script>

<template>
    <header class="sticky top-0 z-30 h-16 w-full bg-white/95 dark:bg-slate-950/95 backdrop-blur-sm border-b border-gray-200 dark:border-white/[0.08] flex items-center justify-between px-4 sm:px-6">
        <!-- Left: Hamburger + Page title -->
        <div class="flex items-center gap-4">
            <!-- Hamburger (mobile only) -->
            <button
                @click="$emit('toggle')"
                class="sm:hidden relative w-9 h-9 flex items-center justify-center rounded-lg text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors duration-200"
                aria-label="Toggle menu"
            >
                <div class="w-5 h-4 flex flex-col justify-between relative">
                    <span
                        class="block h-[2px] w-5 bg-current rounded-full transition-all duration-300 origin-center"
                        :class="open ? 'rotate-45 translate-y-[7px]' : ''"
                    />
                    <span
                        class="block h-[2px] w-5 bg-current rounded-full transition-all duration-300"
                        :class="open ? 'opacity-0 scale-x-0' : ''"
                    />
                    <span
                        class="block h-[2px] w-5 bg-current rounded-full transition-all duration-300 origin-center"
                        :class="open ? '-rotate-45 -translate-y-[7px]' : ''"
                    />
                </div>
            </button>

            <!-- Page title / breadcrumb -->
            <div class="flex items-center gap-2">
                <div class="hidden sm:block w-1.5 h-1.5 rounded-full bg-emerald-500" />
                <h1 v-if="title" class="text-gray-900 dark:text-white text-sm font-medium tracking-tight">
                    {{ title }}
                </h1>
                <h1 v-else class="text-gray-500 dark:text-slate-400 text-sm font-medium">
                    Panel de control
                </h1>
            </div>
        </div>

        <!-- Right: Team + User -->
        <div class="flex items-center gap-3">
            <!-- Team switcher -->
            <div v-if="teamName" class="relative">
                <button
                    @click="toggleTeamMenu"
                    class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] hover:bg-gray-200 dark:hover:bg-slate-700/60 transition-colors duration-200 cursor-pointer"
                >
                    <i class="fa-solid fa-trophy text-[11px] text-amber-500/70"></i>
                    <span class="text-[12px] text-gray-600 dark:text-slate-300 font-medium hidden sm:block">{{ teamName }}</span>
                    <i v-if="allTeams.length > 1" class="fa-solid fa-chevron-down text-[8px] text-gray-400 dark:text-slate-500 hidden sm:block" :class="showTeamMenu ? 'rotate-180' : ''" />
                </button>

                <!-- Team dropdown -->
                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="opacity-0 scale-95 -translate-y-1"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 -translate-y-1"
                >
                    <div
                        v-if="showTeamMenu && allTeams.length > 1"
                        class="absolute right-0 top-full mt-2 w-56 rounded-xl bg-white dark:bg-slate-900 border border-gray-200 dark:border-white/[0.08] shadow-xl shadow-gray-200/50 dark:shadow-black/40 overflow-hidden z-50"
                    >
                        <div class="px-4 py-2 border-b border-gray-100 dark:border-white/[0.06]">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Cambiar campeonato</p>
                        </div>
                        <div class="py-1.5">
                            <button
                                v-for="team in allTeams"
                                :key="team.id"
                                @click="switchToTeam(team); closeTeamMenu()"
                                class="w-full flex items-center gap-3 px-4 py-2 text-[13px] text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-slate-800/60 transition-colors duration-150"
                            >
                                <i v-if="team.id === currentTeamId" class="fa-solid fa-circle-check text-emerald-500 dark:text-emerald-400 text-xs w-4 text-center"></i>
                                <i v-else class="fa-regular fa-circle text-gray-300 dark:text-slate-600 text-xs w-4 text-center"></i>
                                {{ team.name }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>

            <!-- User area -->
            <div class="relative">
                <button
                    @click="toggleUserMenu"
                    class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800/60 transition-colors duration-200 group"
                >
                    <!-- Avatar -->
                    <div v-if="hasProfilePhoto" class="w-8 h-8 rounded-full overflow-hidden ring-2 ring-gray-200 dark:ring-white/[0.08] group-hover:ring-emerald-500/30 transition-all duration-200">
                        <img :src="profilePhotoUrl" :alt="userName" class="w-full h-full object-cover" />
                    </div>
                    <div v-else class="w-8 h-8 rounded-full bg-gray-100 dark:bg-slate-800 border border-gray-300 dark:border-white/[0.1] flex items-center justify-center group-hover:border-emerald-500/30 transition-all duration-200">
                        <span class="text-[11px] font-semibold text-gray-600 dark:text-slate-300">{{ userInitials }}</span>
                    </div>

                    <!-- Name (hidden on very small screens) -->
                    <span class="hidden sm:block text-[13px] text-gray-600 dark:text-slate-300 font-medium group-hover:text-gray-900 dark:group-hover:text-white transition-colors duration-200">
                        {{ userName }}
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] text-gray-400 dark:text-slate-500 transition-transform duration-200 hidden sm:block" :class="showUserMenu ? 'rotate-180' : ''" />
                </button>

                <!-- Dropdown -->
                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="opacity-0 scale-95 -translate-y-1"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 -translate-y-1"
                >
                    <div
                        v-if="showUserMenu"
                        class="absolute right-0 top-full mt-2 w-56 rounded-xl bg-white dark:bg-slate-900 border border-gray-200 dark:border-white/[0.08] shadow-xl shadow-gray-200/50 dark:shadow-black/40 overflow-hidden"
                    >
                        <!-- User info -->
                        <div class="px-4 py-3 border-b border-gray-100 dark:border-white/[0.06]">
                            <p class="text-sm text-gray-900 dark:text-white font-medium">{{ userName }}</p>
                            <p v-if="teamName" class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">{{ teamName }}</p>
                        </div>

                        <!-- Menu items -->
                        <div class="py-1.5">
                            <a
                                v-if="$page.props.jetstream?.canUpdateProfileInformation"
                                :href="route('profile.show')"
                                class="flex items-center gap-3 px-4 py-2 text-[13px] text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-slate-800/60 transition-colors duration-150"
                                @click="closeUserMenu"
                            >
                                <i class="fa-solid fa-user-pen w-4 text-center text-xs"></i>
                                Editar perfil
                            </a>
                        </div>

                        <!-- Theme switcher -->
                        <div class="border-t border-gray-100 dark:border-white/[0.06] px-4 py-3">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 mb-2">Tema</p>
                            <div class="flex items-center gap-1 p-1 rounded-lg bg-gray-100 dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06]">
                                <button
                                    @click="applyTheme('light')"
                                    :class="theme === 'light' ? 'bg-white dark:bg-slate-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-400 dark:text-slate-500 hover:text-gray-600 dark:hover:text-slate-300'"
                                    class="flex-1 flex items-center justify-center gap-1.5 py-1.5 rounded-md text-[11px] font-medium transition-all duration-200"
                                >
                                    <i class="fa-solid fa-sun text-[10px]"></i>
                                    Light
                                </button>
                                <button
                                    @click="applyTheme('system')"
                                    :class="theme === 'system' ? 'bg-white dark:bg-slate-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-400 dark:text-slate-500 hover:text-gray-600 dark:hover:text-slate-300'"
                                    class="flex-1 flex items-center justify-center gap-1.5 py-1.5 rounded-md text-[11px] font-medium transition-all duration-200"
                                >
                                    <i class="fa-solid fa-desktop text-[10px]"></i>
                                    System
                                </button>
                                <button
                                    @click="applyTheme('dark')"
                                    :class="theme === 'dark' ? 'bg-white dark:bg-slate-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-400 dark:text-slate-500 hover:text-gray-600 dark:hover:text-slate-300'"
                                    class="flex-1 flex items-center justify-center gap-1.5 py-1.5 rounded-md text-[11px] font-medium transition-all duration-200"
                                >
                                    <i class="fa-solid fa-moon text-[10px]"></i>
                                    Dark
                                </button>
                            </div>
                        </div>

                        <!-- Logout -->
                        <div class="border-t border-gray-100 dark:border-white/[0.06] py-1.5">
                            <button
                                @click="logout"
                                class="w-full flex items-center gap-3 px-4 py-2 text-[13px] text-gray-500 dark:text-slate-400 hover:text-red-500 dark:hover:text-red-400 hover:bg-gray-50 dark:hover:bg-slate-800/60 transition-colors duration-150"
                            >
                                <i class="fa-solid fa-right-from-bracket w-4 text-center text-xs"></i>
                                Cerrar sesión
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </div>
    </header>

    <!-- Click-away for menus -->
    <div v-if="showUserMenu" class="fixed inset-0 z-20" @click="closeUserMenu" />
    <div v-if="showTeamMenu" class="fixed inset-0 z-20" @click="closeTeamMenu" />
</template>
