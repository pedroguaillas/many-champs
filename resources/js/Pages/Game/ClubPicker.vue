<script setup>
import { computed, ref, watch } from 'vue';
import Sheet from '@/Components/Sheet.vue';
import ClubBadge from './ClubBadge.vue';

const emit = defineEmits(['close', 'select'])

const props = defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: 'Seleccionar club' },
    clubs: { type: Array, default: () => [] },
    groups: { type: Array, default: () => [] },
    selectedId: { type: [Number, String], default: '' },
    // Club elegido en el otro lado: no se puede enfrentar consigo mismo
    blockedId: { type: [Number, String], default: '' },
});

const search = ref('')
const searchInput = ref(null)

const normalize = (text) => (text ?? '').toString().normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase()

const groupName = (id) => props.groups.find(g => g.id === id)?.name

// Clubes filtrados y agrupados por grupo; sin grupo al final
const sections = computed(() => {
    const term = normalize(search.value.trim())
    const filtered = props.clubs.filter(c => !term || normalize(c.name).includes(term))
    const map = new Map()
    filtered.forEach(club => {
        const key = club.group_id ?? 0
        if (!map.has(key)) map.set(key, [])
        map.get(key).push(club)
    })
    return [...map.entries()]
        .sort(([a], [b]) => (a === 0) - (b === 0) || (groupName(a) ?? '').localeCompare(groupName(b) ?? ''))
        .map(([id, clubs]) => ({ id, name: id ? `Grupo ${groupName(id) ?? ''}`.trim() : 'Sin grupo', clubs }))
})

const resultCount = computed(() => sections.value.reduce((n, s) => n + s.clubs.length, 0))

const pick = (club) => {
    if (club.id === props.blockedId) return
    emit('select', club)
}

// Enter en el buscador elige el primer resultado disponible
const pickFirst = () => {
    const first = sections.value.flatMap(s => s.clubs).find(c => c.id !== props.blockedId)
    if (first) pick(first)
}

watch(() => props.show, (show) => {
    if (show) search.value = ''
})

// En móvil no abrimos el teclado de golpe: tapa la lista
const onOpened = () => {
    if (window.matchMedia('(pointer: fine)').matches) searchInput.value?.focus()
}
</script>

<template>
    <Sheet :show="show" :label="title" @close="emit('close')" @opened="onOpened">
        <div class="px-4 sm:px-5 pt-2 sm:pt-4 pb-3 border-b border-gray-200 dark:border-white/[0.06]">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-[15px] font-semibold tracking-tight text-gray-900 dark:text-white">{{ title }}</h2>
                <button type="button" @click="emit('close')" aria-label="Cerrar" class="btn-icon -mr-2">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-gray-400 dark:text-slate-500"></i>
                <input ref="searchInput" v-model="search" type="search" placeholder="Buscar club…" aria-label="Buscar club"
                    autocomplete="off" autocorrect="off" autocapitalize="none" spellcheck="false" enterkeyhint="search"
                    @keydown.enter.prevent="pickFirst"
                    class="w-full pl-9 pr-3.5 py-2.5 rounded-lg bg-white dark:bg-slate-900/80 border border-gray-300 dark:border-white/[0.08] text-[13px] text-gray-700 dark:text-slate-200 placeholder-gray-400 dark:placeholder-slate-600 focus:outline-none focus:border-emerald-500/40 focus:ring-1 focus:ring-emerald-500/20 transition-colors duration-200" />
            </div>
        </div>

        <div class="flex-1 overflow-y-auto overscroll-y-contain pb-[env(safe-area-inset-bottom)]">
            <section v-for="section in sections" :key="section.id">
                <h3 class="sticky top-0 z-10 px-4 sm:px-5 py-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 bg-gray-50 dark:bg-slate-800">
                    {{ section.name }}
                </h3>
                <ul>
                    <li v-for="club in section.clubs" :key="club.id">
                        <button type="button" @click="pick(club)" :disabled="club.id === blockedId"
                            class="w-full flex items-center gap-3 px-4 sm:px-5 py-3 text-left transition-colors duration-150
                                   hover:bg-gray-50 dark:hover:bg-white/[0.03] active:bg-gray-100 dark:active:bg-white/[0.05]
                                   focus:outline-none focus-visible:bg-gray-50 dark:focus-visible:bg-white/[0.03]
                                   disabled:opacity-40 disabled:cursor-not-allowed"
                            :class="club.id === selectedId ? 'bg-emerald-500/[0.06]' : ''">
                            <ClubBadge :club="club" size="sm" />
                            <span class="flex-1 min-w-0">
                                <span class="block truncate text-[14px] font-medium text-gray-800 dark:text-slate-100">{{ club.name }}</span>
                                <span v-if="club.id === blockedId" class="block text-[11px] text-gray-400 dark:text-slate-500">Ya está en el otro lado</span>
                            </span>
                            <i v-if="club.id === selectedId" class="fa-solid fa-check text-emerald-500"></i>
                        </button>
                    </li>
                </ul>
            </section>

            <div v-if="resultCount === 0" class="flex flex-col items-center text-center px-6 py-10">
                <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-slate-900 border border-gray-200 dark:border-white/[0.06] flex items-center justify-center mb-3">
                    <i class="fa-solid fa-shield-halved text-gray-400 dark:text-slate-500"></i>
                </div>
                <p class="text-[13px] text-gray-600 dark:text-slate-300">
                    {{ clubs.length ? `Ningún club coincide con “${search}”` : 'Esta categoría aún no tiene clubes' }}
                </p>
            </div>
        </div>
    </Sheet>
</template>
