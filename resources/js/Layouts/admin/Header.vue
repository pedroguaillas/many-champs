<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

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
const profilePhotoUrl = computed(() => user.value?.profile_photo_url ?? null);
const hasProfilePhoto = computed(() => {
    return page.props.jetstream?.managesProfilePhotos && profilePhotoUrl.value;
});

const showUserMenu = ref(false);

const logout = () => {
    router.post(route('logout'));
};

const toggleUserMenu = () => {
    showUserMenu.value = !showUserMenu.value;
};

const closeUserMenu = () => {
    showUserMenu.value = false;
};
</script>

<template>
    <header class="sticky top-0 z-30 h-16 w-full bg-slate-950/95 backdrop-blur-sm border-b border-white/[0.08] flex items-center justify-between px-4 sm:px-6">
        <!-- Left: Hamburger + Page title -->
        <div class="flex items-center gap-4">
            <!-- Hamburger (mobile only) -->
            <button
                @click="$emit('toggle')"
                class="sm:hidden relative w-9 h-9 flex items-center justify-center rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors duration-200"
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
                <h1 v-if="title" class="text-white text-sm font-medium tracking-tight">
                    {{ title }}
                </h1>
                <h1 v-else class="text-slate-400 text-sm font-medium">
                    Panel de control
                </h1>
            </div>
        </div>

        <!-- Right: Team + User -->
        <div class="flex items-center gap-3">
            <!-- Team badge -->
            <div v-if="teamName" class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-800/60 border border-white/[0.06]">
                <i class="fa-solid fa-people-group text-[11px] text-emerald-500/70"></i>
                <span class="text-[12px] text-slate-300 font-medium">{{ teamName }}</span>
            </div>

            <!-- User area -->
            <div class="relative">
                <button
                    @click="toggleUserMenu"
                    class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-slate-800/60 transition-colors duration-200 group"
                >
                    <!-- Avatar -->
                    <div v-if="hasProfilePhoto" class="w-8 h-8 rounded-full overflow-hidden ring-2 ring-white/[0.08] group-hover:ring-emerald-500/30 transition-all duration-200">
                        <img :src="profilePhotoUrl" :alt="userName" class="w-full h-full object-cover" />
                    </div>
                    <div v-else class="w-8 h-8 rounded-full bg-slate-800 border border-white/[0.1] flex items-center justify-center group-hover:border-emerald-500/30 transition-all duration-200">
                        <span class="text-[11px] font-semibold text-slate-300">{{ userInitials }}</span>
                    </div>

                    <!-- Name (hidden on very small screens) -->
                    <span class="hidden sm:block text-[13px] text-slate-300 font-medium group-hover:text-white transition-colors duration-200">
                        {{ userName }}
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] text-slate-500 transition-transform duration-200 hidden sm:block" :class="showUserMenu ? 'rotate-180' : ''" />
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
                        class="absolute right-0 top-full mt-2 w-56 rounded-xl bg-slate-900 border border-white/[0.08] shadow-xl shadow-black/40 overflow-hidden"
                    >
                        <!-- User info -->
                        <div class="px-4 py-3 border-b border-white/[0.06]">
                            <p class="text-sm text-white font-medium">{{ userName }}</p>
                            <p v-if="teamName" class="text-xs text-slate-500 mt-0.5">{{ teamName }}</p>
                        </div>

                        <!-- Menu items -->
                        <div class="py-1.5">
                            <a
                                v-if="$page.props.jetstream?.canUpdateProfileInformation"
                                :href="route('profile.show')"
                                class="flex items-center gap-3 px-4 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-slate-800/60 transition-colors duration-150"
                                @click="closeUserMenu"
                            >
                                <i class="fa-solid fa-user-pen w-4 text-center text-xs"></i>
                                Editar perfil
                            </a>
                            <button
                                @click="logout"
                                class="w-full flex items-center gap-3 px-4 py-2 text-[13px] text-slate-400 hover:text-red-400 hover:bg-slate-800/60 transition-colors duration-150"
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

    <!-- Click-away for user menu -->
    <div v-if="showUserMenu" class="fixed inset-0 z-20" @click="closeUserMenu" />
</template>
