<script setup>
import { teamColors } from '@/Shared/teamColors';
import { dayLabel, hour } from '@/Shared/dates';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

// Un partido en una lista pública: lleva a su página. Con marcador si ya empezó.
const props = defineProps({
    game: { type: Object, required: true },
    slug: { type: String, required: true },
    // todayParts() del dispositivo, para decir "Hoy" / "Mañana"
    today: { type: Array, default: null },
    showDay: { type: Boolean, default: true },
    showCategory: { type: Boolean, default: true },
});

const started = computed(() => ['jugando', 'finalizado'].includes(props.game.state))
const live = computed(() => props.game.state === 'jugando')
// Mismos colores que en la cancha: sin color, local blanco y visita azul
const colors = computed(() => teamColors(props.game.c1color, props.game.c2color))
const sides = computed(() => [
    { name: props.game.c1name, color: colors.value[1].bg, score: Number(props.game.score1), won: Number(props.game.score1) > Number(props.game.score2) },
    { name: props.game.c2name, color: colors.value[2].bg, score: Number(props.game.score2), won: Number(props.game.score2) > Number(props.game.score1) },
])
// "A" se lee mejor como "Grupo A"
const group = computed(() => {
    const name = props.game.group_name
    return name && !/grupo/i.test(name) ? `Grupo ${name}` : name
})
const context = computed(() => [props.showCategory ? props.game.category : null, group.value].filter(Boolean).join(', '))
</script>

<template>
    <Link :href="route('public.game', [slug, game.id])"
        class="flex items-center gap-3 sm:gap-4 px-4 sm:px-5 py-3 min-h-[64px] hover:bg-white/[0.03] active:bg-white/[0.05] transition-colors duration-150 focus:outline-none focus-visible:bg-white/[0.05]">
        <!-- Cuándo, o cómo va -->
        <div class="w-[68px] shrink-0">
            <p v-if="live" class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-red-400">
                <span class="relative flex w-1.5 h-1.5">
                    <span class="absolute inset-0 rounded-full bg-red-500 animate-ping opacity-75 motion-reduce:hidden"></span>
                    <span class="relative w-1.5 h-1.5 rounded-full bg-red-500"></span>
                </span>
                En vivo
            </p>
            <template v-else>
                <p v-if="showDay" class="text-[12px] font-semibold first-letter:uppercase text-white">{{ dayLabel(game.date, today) }}</p>
                <p class="text-[12px] tabular-nums text-slate-400">{{ game.state === 'finalizado' ? 'Final' : hour(game.time) }}</p>
            </template>
        </div>

        <div class="flex-1 min-w-0">
            <p v-for="side in sides" :key="side.name" class="flex items-center gap-2 py-px text-sm"
                :class="game.state === 'finalizado' && !side.won && sides.some(s => s.won) ? 'text-slate-400' : 'font-medium text-slate-100'">
                <span class="w-2.5 h-2.5 shrink-0 rounded-full ring-1 ring-inset ring-white/20" :style="{ backgroundColor: side.color }" aria-hidden="true"></span>
                <span class="flex-1 truncate">{{ side.name }}</span>
                <span v-if="started" class="shrink-0 w-6 text-right text-[15px] font-semibold tabular-nums" :class="live ? 'text-white' : ''">{{ side.score }}</span>
            </p>
            <p v-if="context" class="mt-0.5 truncate text-[11px] text-slate-500">{{ context }}</p>
        </div>

        <i class="fa-solid fa-chevron-right shrink-0 text-[10px] text-slate-600" aria-hidden="true"></i>
    </Link>
</template>
