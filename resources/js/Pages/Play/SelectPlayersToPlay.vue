<script setup>

// Imports
import { computed, reactive, ref } from 'vue';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import BackLink from '@/Components/BackLink.vue';
import ClubBadge from '../Game/ClubBadge.vue';
import { Link, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

// Props
const props = defineProps({
    game: { type: Object, default: () => { } },
    club1_players: { type: Array, default: () => [] },
    club2_players: { type: Array, default: () => [] }
})

const swalConfig = {
    background: '#1e293b',
    color: '#e2e8f0',
    confirmButtonColor: '#10b981',
    cancelButtonColor: '#334155',
};

// Sanción pendiente: el jugador no puede jugar hasta pagarla
// (card_black llega como true en pgsql y 1 en mysql)
const pendings = (p) => {
    const list = []
    if (p.black) list.push({ type: 'black', label: 'Tarjeta negra', tone: 'bg-slate-900 text-white dark:bg-black dark:text-slate-200 border-slate-900 dark:border-white/20' })
    if (p.santion === 'roja') list.push({ type: 'santion', label: 'Tarjeta roja', tone: 'bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/20' })
    if (p.santion === 'amarilla') list.push({ type: 'santion', label: 'Tarjeta amarilla', tone: 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20' })
    return list
}
const isBlocked = (p) => pendings(p).length > 0

const teams = computed(() => [
    { key: 'clubs1', club: { id: props.game.club1_id, name: props.game.c1_name }, paid: Number(props.game.paid1 ?? 0), players: props.club1_players, accent: 'bg-emerald-500' },
    { key: 'clubs2', club: { id: props.game.club2_id, name: props.game.c2_name }, paid: Number(props.game.paid2 ?? 0), players: props.club2_players, accent: 'bg-blue-500' },
])

// Selección por equipo
const selected = reactive({ clubs1: [], clubs2: [] })
const isSelected = (key, id) => selected[key].includes(id)
const toggle = (key, player) => {
    if (isBlocked(player)) return
    selected[key] = isSelected(key, player.id)
        ? selected[key].filter(id => id !== player.id)
        : [...selected[key], player.id]
}
const available = (team) => team.players.filter(p => !isBlocked(p))
const allSelected = (team) => available(team).length > 0 && available(team).every(p => isSelected(team.key, p.id))
const toggleAll = (team) => {
    selected[team.key] = allSelected(team) ? [] : available(team).map(p => p.id)
}

// Búsqueda por equipo (útil con planteles grandes)
const search = reactive({ clubs1: '', clubs2: '' })
const normalize = (text) => (text ?? '').toString().normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase()
const visible = (team) => {
    const term = normalize(search[team.key].trim())
    const list = term
        ? team.players.filter(p => normalize(`${p.first_name} ${p.last_name} ${p.t_shirt ?? ''}`).includes(term))
        : team.players
    // Disponibles primero, sancionados al final
    return [...list].sort((a, b) => isBlocked(a) - isBlocked(b))
}

// Móvil: un equipo a la vez con pestañas
const activeTab = ref('clubs1')

const playerName = (p) => `${p.first_name} ${p.last_name}`
const initials = (p) => `${p.first_name?.[0] ?? ''}${p.last_name?.[0] ?? ''}`.toUpperCase()

const ready = computed(() => selected.clubs1.length > 0 && selected.clubs2.length > 0)
const missing = computed(() => {
    if (!selected.clubs1.length) return `Elige titulares de ${props.game.c1_name}`
    if (!selected.clubs2.length) return `Elige titulares de ${props.game.c2_name}`
    return ''
})

const longDate = computed(() => {
    if (!props.game.date) return ''
    const [y, m, d] = props.game.date.split('-').map(Number)
    return new Intl.DateTimeFormat('es', { weekday: 'short', day: 'numeric', month: 'short' }).format(new Date(y, m - 1, d))
})

const processing = ref(false)

const save = () => {
    if (!ready.value) {
        // Lleva al usuario al equipo que le falta
        activeTab.value = selected.clubs1.length ? 'clubs2' : 'clubs1'
        return
    }
    Swal.fire({
        title: '¿Iniciar partido?',
        html: `<b>${props.game.c1_name}</b>: ${selected.clubs1.length} titulares<br><b>${props.game.c2_name}</b>: ${selected.clubs2.length} titulares<br><br><small>La alineación inicial no se puede editar después. Los cambios se registran durante el partido.</small>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="far fa-futbol"></i> Iniciar',
        cancelButtonText: 'Revisar',
        ...swalConfig,
    }).then((result) => {
        if (!result.isConfirmed) return
        router.post(route('play.store', props.game.id),
            { clubs1: selected.clubs1, clubs2: selected.clubs2 },
            {
                onStart: () => processing.value = true,
                onFinish: () => processing.value = false,
                onError: () => {
                    Swal.fire({ title: 'No se pudo iniciar', text: 'Revisa que ambos equipos tengan titulares e inténtalo de nuevo.', icon: 'error', ...swalConfig })
                }
            })
    });
}

const pay = (player, pending) => {
    Swal.fire({
        title: 'Cobrar sanción',
        text: `¿Registrar el pago de la ${pending.label.toLowerCase()} de ${playerName(player)}? Podrá jugar este partido.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Sí, cobrar',
        cancelButtonText: 'Cancelar',
        ...swalConfig,
    }).then((result) => {
        if (!result.isConfirmed) return
        const id = pending.type === 'black' ? player.gi_back_id : player.gi_saction_id
        router.patch(route('gameitems.patch', id),
            { patch: pending.type },
            {
                preserveScroll: true,
                preserveState: true,
                onError: () => {
                    Swal.fire({ title: 'No se pudo registrar el pago', icon: 'error', ...swalConfig })
                }
            })
    });
}

</script>

<template>
    <AdminLayout :title="'Seleccionar jugadores'">
        <div>
            <BackLink :href="route('games.index', game.category_id)" label="Partidos" />

            <!-- Contexto del partido -->
            <div class="relative rounded-xl bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] px-4 sm:px-6 py-4 sm:py-5 overflow-hidden">
                <span class="absolute left-0 top-0 bottom-0 w-[3px] bg-emerald-500 rounded-r-full"></span>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Alineación inicial</p>
                <div class="mt-2 grid grid-cols-[1fr_auto_1fr] items-center gap-3">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <ClubBadge :club="teams[0].club" size="sm" />
                        <span class="truncate text-[14px] sm:text-[15px] font-semibold text-gray-900 dark:text-white">{{ game.c1_name }}</span>
                    </div>
                    <span class="text-[11px] font-bold tracking-widest text-gray-300 dark:text-slate-600">VS</span>
                    <div class="flex items-center justify-end gap-2.5 min-w-0">
                        <span class="truncate text-right text-[14px] sm:text-[15px] font-semibold text-gray-900 dark:text-white">{{ game.c2_name }}</span>
                        <ClubBadge :club="teams[1].club" size="sm" />
                    </div>
                </div>
                <p class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-[12px] text-gray-500 dark:text-slate-400">
                    <span v-if="longDate"><i class="fa-regular fa-calendar mr-1.5"></i>{{ longDate }}</span>
                    <span v-if="game.time" class="tabular-nums"><i class="fa-regular fa-clock mr-1.5"></i>{{ game.time.slice(0, 5) }}</span>
                    <span v-if="game.phase"><i class="fa-solid fa-trophy mr-1.5"></i>{{ game.phase }}</span>
                </p>
                <p class="mt-3 text-[12px] text-gray-500 dark:text-slate-400">
                    Toca a los jugadores que <span class="font-medium text-gray-700 dark:text-slate-200">empiezan en cancha</span>. Los suplentes entran luego con un cambio.
                </p>
            </div>

            <!-- Pestañas (móvil) -->
            <div role="tablist" class="lg:hidden sticky top-0 z-20 py-3 bg-gray-50 dark:bg-slate-900">
                <div class="grid grid-cols-2 gap-1 p-1 rounded-xl bg-gray-200/70 dark:bg-slate-800">
                    <button v-for="team in teams" :key="team.key" type="button" role="tab" :aria-selected="activeTab === team.key"
                        @click="activeTab = team.key"
                        class="flex items-center justify-center gap-2 min-w-0 px-3 py-2.5 rounded-lg text-[13px] font-medium transition duration-150"
                        :class="activeTab === team.key
                            ? 'bg-white dark:bg-slate-700 text-gray-900 dark:text-white shadow-sm'
                            : 'text-gray-500 dark:text-slate-400'">
                        <span class="truncate">{{ team.club.name }}</span>
                        <span class="shrink-0 min-w-[22px] px-1.5 py-0.5 rounded-full text-[11px] font-semibold tabular-nums"
                            :class="selected[team.key].length ? 'bg-emerald-500 text-white' : 'bg-gray-300/70 dark:bg-slate-600 text-gray-600 dark:text-slate-300'">
                            {{ selected[team.key].length }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Equipos: lado a lado en desktop -->
            <div class="mt-0 lg:mt-4 grid lg:grid-cols-2 gap-4 items-start">
                <section v-for="team in teams" :key="team.key" :class="activeTab === team.key ? 'block' : 'hidden lg:block'"
                    class="rounded-xl bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] overflow-hidden">

                    <!-- Cabecera del equipo -->
                    <header class="relative px-4 sm:px-5 py-4 border-b border-gray-200 dark:border-white/[0.06]">
                        <span class="absolute left-0 top-0 bottom-0 w-[3px] rounded-r-full" :class="team.accent"></span>
                        <div class="flex items-center gap-3">
                            <ClubBadge :club="team.club" size="sm" />
                            <div class="flex-1 min-w-0">
                                <h2 class="truncate text-sm font-semibold tracking-tight text-gray-900 dark:text-white">{{ team.club.name }}</h2>
                                <p class="text-[12px] text-gray-500 dark:text-slate-400">
                                    <span class="tabular-nums font-medium" :class="selected[team.key].length ? 'text-emerald-600 dark:text-emerald-400' : ''">{{ selected[team.key].length }}</span>
                                    titulares · <span class="tabular-nums">{{ team.players.length }}</span> en plantel
                                </p>
                            </div>
                            <span class="shrink-0 inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-[11px] font-medium tabular-nums border"
                                :title="'Total pagado por el club'"
                                :class="team.paid > 0
                                    ? 'bg-emerald-500/[0.06] border-emerald-500/20 text-emerald-700 dark:text-emerald-300'
                                    : 'bg-amber-500/[0.06] border-amber-500/20 text-amber-700 dark:text-amber-300'">
                                <i class="fa-solid fa-coins text-[10px]"></i>${{ team.paid }}
                            </span>
                        </div>

                        <div v-if="team.players.length" class="mt-3 flex items-center gap-2">
                            <div v-if="team.players.length > 10" class="relative flex-1">
                                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-[11px] text-gray-400 dark:text-slate-500"></i>
                                <input v-model="search[team.key]" type="search" placeholder="Buscar nombre o dorsal…" aria-label="Buscar jugador"
                                    autocomplete="off" autocorrect="off" autocapitalize="none" spellcheck="false" enterkeyhint="search"
                                    class="w-full pl-8 pr-3 py-2 rounded-lg bg-white dark:bg-slate-900/80 border border-gray-300 dark:border-white/[0.08] text-[13px] text-gray-700 dark:text-slate-200 placeholder-gray-400 dark:placeholder-slate-600 focus:outline-none focus:border-emerald-500/40 focus:ring-1 focus:ring-emerald-500/20 transition-colors duration-200" />
                            </div>
                            <button type="button" @click="toggleAll(team)" :disabled="!available(team).length"
                                class="ml-auto shrink-0 inline-flex items-center min-h-[40px] px-3 rounded-lg text-xs font-medium border border-gray-200 dark:border-white/[0.08] text-gray-600 dark:text-slate-300 hover:border-emerald-500/40 dark:hover:border-emerald-500/40 active:scale-[0.97] transition duration-150 disabled:opacity-40">
                                <i class="fa-solid mr-1.5 text-[10px]" :class="allSelected(team) ? 'fa-xmark' : 'fa-check-double'"></i>
                                {{ allSelected(team) ? 'Quitar todos' : 'Marcar todos' }}
                            </button>
                        </div>
                    </header>

                    <!-- Plantel -->
                    <ul class="divide-y divide-gray-100 dark:divide-white/[0.04]">
                        <li v-for="player in visible(team)" :key="player.id">
                            <!-- Jugador habilitado: toda la fila es el control -->
                            <button v-if="!isBlocked(player)" type="button" role="checkbox" :aria-checked="isSelected(team.key, player.id)"
                                @click="toggle(team.key, player)"
                                class="w-full flex items-center gap-3 px-4 sm:px-5 py-3 text-left transition-colors duration-150 active:bg-gray-100 dark:active:bg-white/[0.05]"
                                :class="isSelected(team.key, player.id)
                                    ? 'bg-emerald-500/[0.07] hover:bg-emerald-500/[0.1]'
                                    : 'hover:bg-gray-50 dark:hover:bg-white/[0.02]'">
                                <span class="relative shrink-0">
                                    <img v-if="player.photo" :src="`/storage/avatars/${player.photo}`" alt=""
                                        class="w-10 h-10 rounded-lg object-cover img-outline" />
                                    <span v-else class="w-10 h-10 rounded-lg flex items-center justify-center bg-gray-100 dark:bg-slate-900/80 text-[12px] font-semibold text-gray-500 dark:text-slate-400">
                                        {{ initials(player) }}
                                    </span>
                                </span>
                                <span class="flex-1 min-w-0">
                                    <span class="block truncate text-[14px] font-medium"
                                        :class="isSelected(team.key, player.id) ? 'text-gray-900 dark:text-white' : 'text-gray-700 dark:text-slate-200'">
                                        {{ playerName(player) }}
                                    </span>
                                    <span v-if="player.t_shirt" class="block text-[11px] text-gray-400 dark:text-slate-500 tabular-nums">Dorsal {{ player.t_shirt }}</span>
                                </span>
                                <span class="shrink-0 w-6 h-6 rounded-full flex items-center justify-center border-2 transition duration-150"
                                    :class="isSelected(team.key, player.id)
                                        ? 'bg-emerald-500 border-emerald-500 text-white scale-100'
                                        : 'border-gray-300 dark:border-slate-600 text-transparent'">
                                    <i class="fa-solid fa-check text-[10px]"></i>
                                </span>
                            </button>

                            <!-- Jugador con sanción pendiente -->
                            <div v-else class="flex items-center gap-3 px-4 sm:px-5 py-3 bg-gray-50/60 dark:bg-slate-900/30">
                                <span class="shrink-0 w-10 h-10 rounded-lg flex items-center justify-center bg-gray-100 dark:bg-slate-900/80 text-gray-400 dark:text-slate-600">
                                    <i class="fa-solid fa-ban text-sm"></i>
                                </span>
                                <span class="flex-1 min-w-0">
                                    <span class="block truncate text-[14px] font-medium text-gray-500 dark:text-slate-400">{{ playerName(player) }}</span>
                                    <span class="mt-1 flex flex-wrap gap-1">
                                        <span v-for="pending in pendings(player)" :key="pending.type"
                                            class="inline-flex items-center gap-1 px-1.5 py-px rounded-md border text-[10px] font-medium" :class="pending.tone">
                                            <i class="fa-solid fa-square text-[8px]"></i>{{ pending.label }} sin pagar
                                        </span>
                                    </span>
                                </span>
                                <div class="shrink-0 flex flex-col gap-1">
                                    <button v-for="pending in pendings(player)" :key="pending.type" type="button" @click="pay(player, pending)"
                                        class="inline-flex items-center justify-center min-h-[40px] px-3 rounded-lg text-xs font-medium bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20 active:scale-[0.97] transition duration-150">
                                        Cobrar<span v-if="pendings(player).length > 1"> {{ pending.type === 'black' ? 'negra' : pending.label.split(' ')[1] }}</span>
                                    </button>
                                </div>
                            </div>
                        </li>
                    </ul>

                    <!-- Vacíos -->
                    <div v-if="!team.players.length" class="flex flex-col items-center text-center px-6 py-10">
                        <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-slate-900 border border-gray-200 dark:border-white/[0.06] flex items-center justify-center mb-3">
                            <i class="fa-solid fa-user-plus text-gray-400 dark:text-slate-500"></i>
                        </div>
                        <p class="text-[13px] text-gray-600 dark:text-slate-300">Este club no tiene jugadores registrados</p>
                        <Link :href="route('players.index', team.club.id)" class="mt-2 inline-flex items-center min-h-[40px] text-xs font-medium text-emerald-600 dark:text-emerald-400 hover:underline">Agregar jugadores</Link>
                    </div>
                    <p v-else-if="!visible(team).length" class="px-5 py-8 text-center text-[13px] text-gray-500 dark:text-slate-400">
                        Ningún jugador coincide con “{{ search[team.key] }}”
                    </p>
                </section>
            </div>

        </div>

        <!-- Barra de acción: siempre visible, fuera del scroll -->
        <template #footer>
            <div class="flex items-center gap-3 px-4 sm:px-6 py-3">
                <div class="flex-1 min-w-0">
                    <p class="flex items-center gap-2 text-[13px] font-medium text-gray-800 dark:text-slate-100 tabular-nums">
                        <span class="truncate">{{ selected.clubs1.length }} <span class="hidden sm:inline">· {{ game.c1_name }}</span></span>
                        <span class="text-gray-300 dark:text-slate-600">vs</span>
                        <span class="truncate">{{ selected.clubs2.length }} <span class="hidden sm:inline">· {{ game.c2_name }}</span></span>
                    </p>
                    <p class="truncate text-[11px]" :class="ready ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400 dark:text-slate-500'">
                        {{ ready ? 'Listo para iniciar' : missing }}
                    </p>
                </div>
                <button type="button" @click="save" :disabled="processing" :aria-disabled="!ready"
                    class="btn-primary shrink-0 py-3 sm:py-2.5 text-sm sm:text-xs" :class="!ready ? 'opacity-40' : ''">
                    <i :class="processing ? 'fa-solid fa-spinner fa-spin' : 'far fa-futbol'"></i>
                    Iniciar partido
                </button>
            </div>
        </template>
    </AdminLayout>
</template>
