<script setup>
import { computed } from 'vue';

const props = defineProps({
    club: { type: Object, default: null },
    size: { type: String, default: 'md' },
});

// Paleta fija: el mismo club siempre tiene el mismo color
const tones = [
    'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400',
    'bg-sky-500/15 text-sky-600 dark:text-sky-400',
    'bg-amber-500/15 text-amber-600 dark:text-amber-400',
    'bg-rose-500/15 text-rose-600 dark:text-rose-400',
    'bg-violet-500/15 text-violet-600 dark:text-violet-400',
    'bg-cyan-500/15 text-cyan-600 dark:text-cyan-400',
]

const initials = computed(() => {
    const words = (props.club?.name ?? '').trim().split(/\s+/).filter(Boolean)
    if (!words.length) return ''
    return (words.length === 1 ? words[0].slice(0, 2) : words[0][0] + words[1][0]).toUpperCase()
})

const tone = computed(() => tones[(props.club?.id ?? 0) % tones.length])

const sizeClass = computed(() => ({
    sm: 'w-9 h-9 text-[12px] rounded-lg',
    md: 'w-12 h-12 text-[15px] rounded-xl',
    lg: 'w-14 h-14 sm:w-16 sm:h-16 text-lg sm:text-xl rounded-xl',
}[props.size]))
</script>

<template>
    <span class="shrink-0 inline-flex items-center justify-center font-bold tracking-tight" :class="[sizeClass, tone]" aria-hidden="true">
        {{ initials }}
    </span>
</template>
