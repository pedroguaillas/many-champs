<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import GameRow from '@/Components/Public/GameRow.vue';
import { dayLabel, longDay, todayParts } from '@/Shared/dates';
import { Link, router } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

const props = defineProps({
    championship: { type: Object, required: true },
    date: { type: String, required: true },
    previous: { type: String, required: true },
    next: { type: String, required: true },
    games: { type: Array, default: () => [] },
});

const today = ref(null)
onMounted(() => { today.value = todayParts() })

const heading = computed(() => {
    const relative = dayLabel(props.date, today.value)
    return ['Hoy', 'Mañana', 'Ayer'].includes(relative) ? `${relative}, ${longDay(props.date)}` : longDay(props.date)
})

const go = (date) => { if (date) router.get(route('public.calendar', [props.championship.slug, date]), {}, { preserveScroll: true }) }
const arrow = 'w-11 h-11 shrink-0 inline-flex items-center justify-center rounded-lg border border-white/[0.08] text-slate-400 hover:text-white hover:border-emerald-500/40 active:scale-[0.97] transition duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40'
</script>

<template>
    <PublicLayout :title="`Calendario de ${championship.name}`" :championship="championship"
        :share-text="`Partidos del ${longDay(date)} en ${championship.name}`">

        <!-- Día: anterior, elegir, siguiente -->
        <div class="flex items-center gap-2">
            <Link :href="route('public.calendar', [championship.slug, previous])" preserve-scroll aria-label="Día anterior" :class="arrow">
                <i class="fa-solid fa-chevron-left text-xs" aria-hidden="true"></i>
            </Link>
            <label class="relative flex-1 min-w-0 h-11 flex items-center justify-center gap-2 rounded-lg border border-white/[0.08] px-3 text-sm font-semibold text-white cursor-pointer focus-within:border-emerald-500/50">
                <i class="fa-regular fa-calendar text-[12px] text-slate-500" aria-hidden="true"></i>
                <span class="truncate first-letter:uppercase">{{ heading }}</span>
                <input type="date" :value="date" @change="go($event.target.value)" aria-label="Elegir fecha"
                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
            </label>
            <Link :href="route('public.calendar', [championship.slug, next])" preserve-scroll aria-label="Día siguiente" :class="arrow">
                <i class="fa-solid fa-chevron-right text-xs" aria-hidden="true"></i>
            </Link>
        </div>

        <section class="rounded-xl bg-slate-900 border border-white/[0.06] overflow-hidden" aria-label="Partidos del día">
            <ul v-if="games.length" class="divide-y divide-white/[0.04]">
                <li v-for="game in games" :key="game.id"><GameRow :game="game" :slug="championship.slug" :show-day="false" /></li>
            </ul>
            <EmptyState v-else icon="fa-regular fa-calendar-xmark" title="No hay partidos este día"
                description="Prueba con otro día usando las flechas o tocando la fecha.">
                <Link :href="route('public.championship', championship.slug)" class="btn-add">Ver próximos partidos</Link>
            </EmptyState>
        </section>
    </PublicLayout>
</template>
