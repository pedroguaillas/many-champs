<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BackLink from '@/Components/BackLink.vue';
import InputError from '@/Components/InputError.vue';
import ClubPicker from './ClubPicker.vue';
import ClubBadge from './ClubBadge.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

const props = defineProps({
    category: { type: Object, default: () => { } },
    clubs: { type: Array, default: () => [] },
    groups: { type: Array, default: () => [] },
    progress: { type: Array, default: () => [] }
});

// Fechas en hora local del dispositivo (YYYY-MM-DD)
const pad = (n) => String(n).padStart(2, '0')
const toDateValue = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`
const parseDate = (value) => {
    const [y, m, d] = value.split('-').map(Number)
    return new Date(y, m - 1, d)
}
const addDays = (d, days) => new Date(d.getFullYear(), d.getMonth(), d.getDate() + days)

const form = useForm({
    club1_id: '',
    club2_id: '',
    progress_id: props.progress[0]?.id ?? '',
    date: '',
    time: ''
});

// La fecha depende del reloj del dispositivo: se fija en el cliente para que
// el HTML del servidor (SSR, en UTC) y el del navegador coincidan
const today = ref(null)
onMounted(() => {
    const now = new Date()
    // Hora por defecto: siguiente cuarto de hora
    const rounded = new Date(now.getTime() + (15 - now.getMinutes() % 15) * 60000)
    today.value = now
    form.defaults({ date: toDateValue(rounded), time: `${pad(rounded.getHours())}:${pad(rounded.getMinutes())}` })
    form.reset('date', 'time')
})

const club1 = computed(() => props.clubs.find(c => c.id === form.club1_id) ?? null)
const club2 = computed(() => props.clubs.find(c => c.id === form.club2_id) ?? null)
const groupName = (club) => {
    const name = props.groups.find(g => g.id === club?.group_id)?.name
    return name ? `Grupo ${name}` : ''
}
// En fase de grupos lo normal es que ambos clubes sean del mismo grupo
const differentGroups = computed(() => club1.value && club2.value && club1.value.group_id !== club2.value.group_id
    && /grupo/i.test(props.progress.find(p => p.id === form.progress_id)?.description ?? ''))

// Picker
const picking = ref(null) // 'club1_id' | 'club2_id' | null
const openPicker = (side) => { picking.value = side }
const onPick = (club) => {
    if (!picking.value) return
    form[picking.value] = club.id
    form.clearErrors(picking.value)
    // Tras elegir local, pasamos directo a visita si falta
    const next = picking.value === 'club1_id' && !form.club2_id ? 'club2_id' : null
    picking.value = null
    if (next) setTimeout(() => { picking.value = next }, 250)
}
const swap = () => {
    [form.club1_id, form.club2_id] = [form.club2_id, form.club1_id]
}

// Atajos de fecha: hoy, mañana y el próximo fin de semana
const quickDates = computed(() => {
    if (!today.value) return []
    const list = [
        { label: 'Hoy', date: today.value },
        { label: 'Mañana', date: addDays(today.value, 1) },
    ]
    for (const weekday of [6, 0]) {
        const d = addDays(today.value, ((weekday - today.value.getDay() + 7) % 7) || 7)
        if (!list.some(q => toDateValue(q.date) === toDateValue(d))) {
            const name = new Intl.DateTimeFormat('es', { weekday: 'short' }).format(d).replace('.', '')
            list.push({ label: `${name[0].toUpperCase()}${name.slice(1)} ${d.getDate()}`, date: d })
        }
    }
    return list.map(q => ({ ...q, value: toDateValue(q.date) }))
})

const longDate = computed(() => form.date
    ? new Intl.DateTimeFormat('es', { weekday: 'long', day: 'numeric', month: 'long' }).format(parseDate(form.date))
    : '')
const isPast = computed(() => today.value && form.date && form.time && new Date(`${form.date}T${form.time}`) < today.value)

const phaseName = computed(() => props.progress.find(p => p.id === form.progress_id)?.description ?? '')

const canSave = computed(() => form.club1_id && form.club2_id && form.club1_id !== form.club2_id && form.date && form.time && form.progress_id)
const missing = computed(() => {
    if (!form.club1_id) return 'Elige el equipo local'
    if (!form.club2_id) return 'Elige el equipo visitante'
    if (!form.date || !form.time) return 'Completa fecha y hora'
    if (!form.progress_id) return 'Elige la fase'
    return ''
})

const save = () => {
    if (!canSave.value) return
    form.post(route('games.store', props.category.id), { preserveScroll: true })
}

const sides = [
    { key: 'club1_id', label: 'Local', club: club1 },
    { key: 'club2_id', label: 'Visita', club: club2 },
]
</script>

<template>
    <AdminLayout :title="'Registrar juego'">
        <form @submit.prevent="save" class="max-w-3xl mx-auto">

            <BackLink :href="route('games.index', category.id)" label="Partidos" />

            <div class="rounded-xl bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] overflow-hidden">

                <!-- Cabecera con pitch stripe -->
                <div class="relative px-5 sm:px-6 py-4 sm:py-5 border-b border-gray-200 dark:border-white/[0.06]">
                    <span class="absolute left-0 top-0 bottom-0 w-[3px] bg-emerald-500 rounded-r-full"></span>
                    <h2 class="text-[15px] font-semibold tracking-tight text-gray-900 dark:text-white">Nuevo partido</h2>
                    <p class="text-[12px] text-gray-500 dark:text-slate-500 mt-0.5">{{ category.name }}</p>
                </div>

                <!-- Marcador: Local vs Visita -->
                <div class="p-4 sm:p-6 border-b border-gray-200 dark:border-white/[0.06]">
                    <div class="grid grid-cols-[1fr_auto_1fr] items-stretch gap-2 sm:gap-4">
                        <template v-for="(side, index) in sides" :key="side.key">
                            <!-- VS + intercambiar -->
                            <div v-if="index === 1" class="flex flex-col items-center justify-center gap-2">
                                <span class="text-[11px] font-bold tracking-widest text-gray-300 dark:text-slate-600">VS</span>
                                <button type="button" @click="swap" :disabled="!form.club1_id && !form.club2_id"
                                    aria-label="Intercambiar local y visita" title="Intercambiar local y visita"
                                    class="w-10 h-10 inline-flex items-center justify-center rounded-lg border border-gray-200 dark:border-white/[0.08] text-gray-400 dark:text-slate-500 hover:text-emerald-600 hover:border-emerald-500/40 dark:hover:text-emerald-400 dark:hover:border-emerald-500/40 active:scale-[0.97] transition duration-150 disabled:opacity-30 disabled:pointer-events-none">
                                    <i class="fa-solid fa-right-left text-xs"></i>
                                </button>
                            </div>

                            <div class="min-w-0">
                                <button type="button" @click="openPicker(side.key)"
                                    class="group w-full h-full min-h-[148px] sm:min-h-[168px] flex flex-col items-center justify-center gap-2.5 px-2 py-4 rounded-xl border-2 border-dashed transition duration-150 active:scale-[0.97]
                                           focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40"
                                    :class="side.club.value
                                        ? 'border-solid border-gray-200 dark:border-white/[0.08] bg-gray-50 dark:bg-slate-900/60 hover:border-emerald-500/40 dark:hover:border-emerald-500/40'
                                        : form.errors[side.key]
                                            ? 'border-red-400/60 bg-red-500/[0.04]'
                                            : 'border-gray-300 dark:border-white/[0.1] hover:border-emerald-500/50 hover:bg-emerald-500/[0.04] dark:hover:border-emerald-500/50 dark:hover:bg-emerald-500/[0.04]'">
                                    <span class="text-[10px] font-semibold uppercase tracking-wider"
                                        :class="side.club.value ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400 dark:text-slate-500'">
                                        {{ side.label }}
                                    </span>

                                    <template v-if="side.club.value">
                                        <ClubBadge :club="side.club.value" size="lg" />
                                        <span class="w-full text-center text-[13px] sm:text-[15px] font-semibold leading-tight text-gray-900 dark:text-white line-clamp-2 break-words">
                                            {{ side.club.value.name }}
                                        </span>
                                        <span class="text-[11px] text-gray-400 dark:text-slate-500">
                                            {{ groupName(side.club.value) || 'Cambiar' }}
                                        </span>
                                    </template>
                                    <template v-else>
                                        <span class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl flex items-center justify-center bg-gray-100 dark:bg-slate-900/80 text-gray-400 dark:text-slate-500 group-hover:text-emerald-500 transition-colors duration-150">
                                            <i class="fa-solid fa-plus text-lg"></i>
                                        </span>
                                        <span class="text-[13px] font-medium text-gray-500 dark:text-slate-400">Elegir club</span>
                                    </template>
                                </button>
                                <InputError :message="form.errors[side.key]" class="mt-2 text-center" />
                            </div>
                        </template>
                    </div>

                    <p v-if="differentGroups" class="mt-4 flex items-start gap-2 rounded-lg bg-amber-500/[0.06] border border-amber-500/20 px-3 py-2 text-[12px] text-amber-700 dark:text-amber-300">
                        <i class="fa-solid fa-circle-info mt-0.5"></i>
                        Los clubes son de grupos distintos y la fase es de grupos. Verifica antes de guardar.
                    </p>
                </div>

                <!-- Cuándo -->
                <div class="p-4 sm:p-6 space-y-4 border-b border-gray-200 dark:border-white/[0.06]">
                    <div class="flex items-baseline justify-between gap-3">
                        <h3 class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Cuándo</h3>
                        <span class="text-[12px] text-gray-500 dark:text-slate-400 first-letter:uppercase truncate">{{ longDate }}</span>
                    </div>

                    <div class="flex flex-wrap gap-2 min-h-[34px]">
                        <button v-for="q in quickDates" :key="q.value" type="button" @click="form.date = q.value"
                            class="inline-flex items-center min-h-[40px] px-3.5 rounded-lg text-xs font-medium border transition duration-150 active:scale-[0.97]"
                            :class="form.date === q.value
                                ? 'bg-emerald-500 border-emerald-500 text-white'
                                : 'border-gray-200 dark:border-white/[0.08] text-gray-600 dark:text-slate-300 hover:border-emerald-500/40 dark:hover:border-emerald-500/40'">
                            {{ q.label }}
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="game-date" class="block text-[11px] font-medium text-gray-500 dark:text-slate-400 mb-1.5">Fecha</label>
                            <input id="game-date" v-model="form.date" type="date" required
                                class="w-full px-3.5 py-2.5 rounded-lg bg-white dark:bg-slate-900/80 border border-gray-300 dark:border-white/[0.08] text-[13px] text-gray-700 dark:text-slate-200 tabular-nums focus:outline-none focus:border-emerald-500/40 focus:ring-1 focus:ring-emerald-500/20 transition-colors duration-200 dark:[color-scheme:dark]" />
                            <InputError :message="form.errors.date" class="mt-2" />
                        </div>
                        <div>
                            <label for="game-time" class="block text-[11px] font-medium text-gray-500 dark:text-slate-400 mb-1.5">Hora</label>
                            <input id="game-time" v-model="form.time" type="time" required
                                class="w-full px-3.5 py-2.5 rounded-lg bg-white dark:bg-slate-900/80 border border-gray-300 dark:border-white/[0.08] text-[13px] text-gray-700 dark:text-slate-200 tabular-nums focus:outline-none focus:border-emerald-500/40 focus:ring-1 focus:ring-emerald-500/20 transition-colors duration-200 dark:[color-scheme:dark]" />
                            <InputError :message="form.errors.time" class="mt-2" />
                        </div>
                    </div>

                    <p v-if="isPast" class="flex items-center gap-2 text-[12px] text-amber-600 dark:text-amber-400">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        Esta fecha ya pasó. ¿Estás registrando un partido jugado?
                    </p>
                </div>

                <!-- Fase -->
                <div class="p-4 sm:p-6">
                    <h3 id="phase-label" class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 mb-3">Fase</h3>
                    <div role="radiogroup" aria-labelledby="phase-label" class="flex flex-wrap gap-2">
                        <button v-for="p in progress" :key="p.id" type="button" role="radio" :aria-checked="form.progress_id === p.id"
                            @click="form.progress_id = p.id"
                            class="inline-flex items-center min-h-[40px] px-3.5 rounded-lg text-xs font-medium border transition duration-150 active:scale-[0.97]"
                            :class="form.progress_id === p.id
                                ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-700 dark:text-emerald-300'
                                : 'border-gray-200 dark:border-white/[0.08] text-gray-600 dark:text-slate-300 hover:border-emerald-500/40 dark:hover:border-emerald-500/40'">
                            <i v-if="form.progress_id === p.id" class="fa-solid fa-check mr-1.5 text-[10px]"></i>{{ p.description }}
                        </button>
                    </div>
                    <InputError :message="form.errors.progress_id" class="mt-2" />
                </div>

            </div>
        </form>

        <ClubPicker :show="picking !== null"
            :title="picking === 'club2_id' ? 'Equipo visitante' : 'Equipo local'"
            :clubs="clubs" :groups="groups"
            :selected-id="picking ? form[picking] : ''"
            :blocked-id="picking === 'club1_id' ? form.club2_id : form.club1_id"
            @close="picking = null" @select="onPick" />

        <!-- Barra de acción: siempre visible, fuera del scroll -->
        <template #footer>
            <div class="max-w-3xl mx-auto flex items-center gap-3 px-4 sm:px-6 py-3">
                <p class="flex-1 min-w-0 truncate text-[12px] text-gray-500 dark:text-slate-400">
                    <template v-if="canSave">
                        <span class="font-medium text-gray-800 dark:text-slate-200">{{ club1.name }}</span> vs
                        <span class="font-medium text-gray-800 dark:text-slate-200">{{ club2.name }}</span>
                        <span class="hidden sm:inline"> · <span class="tabular-nums">{{ form.time }}</span> · {{ phaseName }}</span>
                    </template>
                    <template v-else>{{ missing }}</template>
                </p>
                <Link :href="route('games.index', category.id)"
                    class="hidden sm:inline-flex px-5 py-2.5 rounded-lg text-xs font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-700/40 transition duration-200">
                    Cancelar
                </Link>
                <button type="button" @click="save" :disabled="!canSave || form.processing" class="btn-primary shrink-0 py-3 sm:py-2.5 text-sm sm:text-xs">
                    <i class="fa-solid" :class="form.processing ? 'fa-spinner fa-spin' : 'fa-floppy-disk'"></i>
                    Guardar partido
                </button>
            </div>
        </template>
    </AdminLayout>
</template>
