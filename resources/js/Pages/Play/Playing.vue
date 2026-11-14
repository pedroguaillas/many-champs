<script setup>

// Imports
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import BackLink from '@/Components/BackLink.vue';
import Sheet from '@/Components/Sheet.vue';
import ClubBadge from '../Game/ClubBadge.vue';
import Pitch from './Pitch.vue';
import Swal from 'sweetalert2';
import { computed, onUnmounted, ref, watch } from 'vue';
import axios from 'axios';

// Props
const props = defineProps({
    game: { type: Object, default: () => { } },
    c1_players: { type: Array, default: () => [] },
    c2_players: { type: Array, default: () => [] }
})

const swalConfig = {
    background: '#1e293b',
    color: '#e2e8f0',
    confirmButtonColor: '#10b981',
    cancelButtonColor: '#334155',
};

// Copia local para actualizar al instante (optimista) y sincronizar luego con el servidor
const clone = (list) => list.map(p => ({ ...p, card_black: !!p.card_black }))
const team1 = ref(clone(props.c1_players))
const team2 = ref(clone(props.c2_players))

// Mientras haya escrituras en curso no pisamos el estado local con datos viejos del servidor
let pending = 0
watch(() => [props.c1_players, props.c2_players], ([c1, c2]) => {
    if (pending > 0) return
    team1.value = clone(c1)
    team2.value = clone(c2)
})

const teams = computed(() => [
    { side: 1, club: { id: props.game.club1_id, name: props.game.c1_name }, players: team1.value },
    { side: 2, club: { id: props.game.club2_id, name: props.game.c2_name }, players: team2.value },
])

// Estado del jugador en el partido
const isSubbed = (p) => p.change_player_id !== null
const isExpelled = (p) => p.santion === 'roja'
const onField = (list) => list.filter(p => !isSubbed(p) && !isExpelled(p))
const offField = (list) => list.filter(p => isSubbed(p) || isExpelled(p))
const allPlayers = computed(() => [...team1.value, ...team2.value])
const nameOf = (id) => {
    const p = allPlayers.value.find(x => x.id === id)
    return p ? `${p.first_name} ${p.last_name}` : ''
}

const score = (list) => list.reduce((sum, p) => sum + (p.goals ?? 0), 0)
const scorers = (list) => list.filter(p => p.goals > 0)
const shortName = (p) => `${p.first_name.split(' ')[0]} ${p.last_name?.[0] ?? ''}.`
const fullName = (p) => `${p.first_name} ${p.last_name}`
const initials = (p) => `${p.first_name?.[0] ?? ''}${p.last_name?.[0] ?? ''}`.toUpperCase()

const finished = computed(() => props.game.state === 'finalizado')

const started = computed(() => allPlayers.value.length > 0)

// ───── Aviso con deshacer ─────
const toast = ref(null)
let toastTimer = null
const notify = (message, { undo = null, tone = 'ok' } = {}) => {
    clearTimeout(toastTimer)
    toast.value = { message, undo, tone, key: Date.now() }
    toastTimer = setTimeout(() => { toast.value = null }, undo ? 6000 : 3500)
}
const runUndo = () => {
    const fn = toast.value?.undo
    toast.value = null
    fn?.()
}
onUnmounted(() => clearTimeout(toastTimer))

const sync = () => router.reload({ only: ['c1_players', 'c2_players'], preserveScroll: true, preserveState: true })

// Actualiza un game_item: aplica local, guarda y ofrece deshacer
const update = (giId, data, message, undoable = true) => {
    // Siempre sobre el objeto vigente: la lista se reemplaza en cada sincronización
    const current = () => allPlayers.value.find(p => p.gi_id === giId)
    const player = current()
    if (!player) return
    const before = Object.fromEntries(Object.keys(data).map(k => [k, player[k]]))
    Object.assign(player, data)
    pending++
    axios.put(route('gameitems.update', giId), data)
        .then(() => {
            notify(message, {
                undo: undoable ? () => update(giId, before, `Deshecho: ${message.charAt(0).toLowerCase()}${message.slice(1)}`, false) : null
            })
        })
        .catch(() => {
            const p = current()
            if (p) Object.assign(p, before)
            notify('No se pudo guardar. Revisa tu conexión.', { tone: 'error' })
        })
        .finally(() => {
            if (--pending === 0) sync()
        })
}

// ───── Hoja de acciones ─────
const selected = ref(null) // { player, side }
const mode = ref('actions') // 'actions' | 'bench'
const selectedTeam = computed(() => teams.value.find(t => t.side === selected.value?.side))

const openPlayer = (player, side) => {
    selected.value = { player, side }
    mode.value = 'actions'
}
const closeSheet = () => {
    selected.value = null
    bench.value = []
}

const addGoal = () => {
    const p = selected.value.player
    update(p.gi_id, { goals: (p.goals ?? 0) + 1 }, `Gol de ${shortName(p)}`)
    closeSheet()
}
const removeGoal = () => {
    const p = selected.value.player
    if (!p.goals) return
    update(p.gi_id, { goals: p.goals - 1 }, `Gol anulado a ${shortName(p)}`)
    closeSheet()
}
const yellow = () => {
    const p = selected.value.player
    // Segunda amarilla = roja
    const santion = p.santion === null ? 'amarilla' : 'roja'
    update(p.gi_id, { santion }, santion === 'roja' ? `Segunda amarilla: roja a ${shortName(p)}` : `Amarilla a ${shortName(p)}`)
    closeSheet()
}
const red = () => {
    const p = selected.value.player
    const santion = p.santion === 'roja' ? null : 'roja'
    update(p.gi_id, { santion }, santion ? `Roja a ${shortName(p)}` : `Roja retirada a ${shortName(p)}`)
    closeSheet()
}
const black = () => {
    const p = selected.value.player
    update(p.gi_id, { card_black: !p.card_black }, p.card_black ? `Negra retirada a ${shortName(p)}` : `Negra a ${shortName(p)}`)
    closeSheet()
}

// ───── Banca: cambios y ajustes ─────
const bench = ref([])
const benchLoading = ref(false)
const benchFor = ref(null) // jugador que sale (cambio) o null (agregar)

const openBench = (side, outPlayer = null) => {
    if (!selected.value) selected.value = { player: null, side }
    benchFor.value = outPlayer
    mode.value = 'bench'
    benchLoading.value = true
    bench.value = []
    axios.post(route('gameitems.players', props.game.id), { type: String(side) })
        .then(({ data: { result } }) => { bench.value = result })
        .catch(() => notify('No se pudo cargar la banca', { tone: 'error' }))
        .finally(() => { benchLoading.value = false })
}

const benchPending = (p) => p.santion || p.black

const enter = (benchPlayer) => {
    const out = benchFor.value
    const data = { player_id: benchPlayer.id, game_id: props.game.id, entered_in: out ? 'cambio' : 'ajuste' }
    if (out) data.change_player_id = out.id
    closeSheet()
    axios.post(route('gameitems.store'), data)
        .then(() => {
            notify(out ? `Cambio: entra ${shortName(benchPlayer)}, sale ${shortName(out)}` : `${shortName(benchPlayer)} entra al partido`)
            sync()
        })
        .catch(() => notify('No se pudo registrar el cambio', { tone: 'error' }))
}

const payBench = (p) => {
    const type = p.santion ? 'santion' : 'black'
    const id = type === 'santion' ? p.gi_santion_id : p.gi_black_id
    Swal.fire({
        title: 'Cobrar sanción',
        text: `¿Registrar el pago de la tarjeta ${type === 'santion' ? p.santion : 'negra'} de ${fullName(p)}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Sí, cobrar',
        cancelButtonText: 'Cancelar',
        ...swalConfig,
    }).then((result) => {
        if (!result.isConfirmed) return
        axios.patch(route('gameitems.patch', id), { patch: type })
            .then(() => {
                if (type === 'santion') p.santion = null
                else p.black = null
                notify(`Sanción cobrada a ${shortName(p)}`)
            })
            .catch(() => notify('No se pudo registrar el pago', { tone: 'error' }))
    })
}

// ───── Finalizar ─────
const ended = () => {
    Swal.fire({
        title: 'Finalizar partido',
        html: `<div style="font-size:1.4rem;font-weight:700;margin:.5rem 0">${props.game.c1_name} ${score(team1.value)} – ${score(team2.value)} ${props.game.c2_name}</div><small>Podrás seguir corrigiendo goles y tarjetas después.</small>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-flag-checkered"></i> Finalizar',
        cancelButtonText: 'Seguir jugando',
        ...swalConfig,
    }).then((result) => {
        if (!result.isConfirmed) return
        router.put(route('games.ended', props.game.id), { state: 'finalizado' }, {
            onError: () => notify('No se pudo finalizar el partido', { tone: 'error' })
        })
    })
}

const cardTone = (p) => p.card_black ? 'bg-slate-950 ring-1 ring-white/40' : p.santion === 'roja' ? 'bg-red-500' : p.santion === 'amarilla' ? 'bg-amber-400' : ''
const accent = (side) => side === 1 ? 'bg-white text-emerald-700 ring-1 ring-gray-200 dark:ring-0' : 'bg-blue-500 text-white'

</script>

<template>
    <AdminLayout :title="finished ? 'Partido finalizado' : 'En vivo'">
        <BackLink :href="route('games.index', game.category_id)" label="Partidos" />

        <!-- Marcador fijo -->
        <div class="sticky top-0 z-20 pb-3 bg-gray-50 dark:bg-slate-900">
            <div class="rounded-xl bg-white dark:bg-slate-800/80 border border-gray-200 dark:border-white/[0.06] px-3 sm:px-6 py-3 sm:py-4">
                <div class="flex justify-center mb-2">
                    <span v-if="finished" class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-gray-100 dark:bg-slate-700/60 text-gray-500 dark:text-slate-300">
                        <i class="fa-solid fa-flag-checkered text-[9px]"></i> Finalizado
                    </span>
                    <span v-else class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-red-500/10 text-red-600 dark:text-red-400">
                        <span class="relative flex w-1.5 h-1.5">
                            <span class="absolute inset-0 rounded-full bg-red-500 animate-ping opacity-75 motion-reduce:hidden"></span>
                            <span class="relative w-1.5 h-1.5 rounded-full bg-red-500"></span>
                        </span>
                        En vivo
                    </span>
                </div>
                <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-2 sm:gap-6">
                    <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                        <ClubBadge :club="teams[0].club" size="sm" class="hidden sm:inline-flex" />
                        <span class="truncate text-[13px] sm:text-base font-semibold text-gray-900 dark:text-white">{{ game.c1_name }}</span>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-3 text-3xl sm:text-4xl font-bold tabular-nums text-gray-900 dark:text-white" aria-live="polite">
                        <transition mode="out-in" enter-active-class="duration-300 ease-out" enter-from-class="scale-150 opacity-0" leave-active-class="duration-150" leave-to-class="opacity-0">
                            <span :key="score(team1)" class="inline-block">{{ score(team1) }}</span>
                        </transition>
                        <span class="text-gray-300 dark:text-slate-600 text-2xl">–</span>
                        <transition mode="out-in" enter-active-class="duration-300 ease-out" enter-from-class="scale-150 opacity-0" leave-active-class="duration-150" leave-to-class="opacity-0">
                            <span :key="score(team2)" class="inline-block">{{ score(team2) }}</span>
                        </transition>
                    </div>
                    <div class="flex items-center justify-end gap-2 sm:gap-3 min-w-0">
                        <span class="truncate text-right text-[13px] sm:text-base font-semibold text-gray-900 dark:text-white">{{ game.c2_name }}</span>
                        <ClubBadge :club="teams[1].club" size="sm" class="hidden sm:inline-flex" />
                    </div>
                </div>
                <!-- Goleadores -->
                <div v-if="scorers(team1).length || scorers(team2).length" class="mt-2 grid grid-cols-2 gap-4 text-[11px] text-gray-500 dark:text-slate-400">
                    <p class="truncate">
                        <span v-for="(p, i) in scorers(team1)" :key="p.gi_id">{{ i ? ', ' : '' }}<i class="fa-solid fa-futbol text-[9px] mr-1"></i>{{ shortName(p) }}<template v-if="p.goals > 1"> ×{{ p.goals }}</template></span>
                    </p>
                    <p class="truncate text-right">
                        <span v-for="(p, i) in scorers(team2)" :key="p.gi_id">{{ i ? ', ' : '' }}<i class="fa-solid fa-futbol text-[9px] mr-1"></i>{{ shortName(p) }}<template v-if="p.goals > 1"> ×{{ p.goals }}</template></span>
                    </p>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-[1fr_300px] gap-4 items-start">
            <!-- Cancha -->
            <div>
                <p v-if="started" class="mb-2 flex items-center gap-2 text-[12px] text-gray-500 dark:text-slate-400">
                    <i class="fa-solid fa-hand-pointer text-[11px]"></i>
                    Toca un jugador para marcar gol, tarjeta o hacer un cambio
                </p>
                <Pitch :home="onField(team1)" :away="onField(team2)"
                    :selected-id="selected?.player?.id ?? 0" @select="openPlayer">
                    <template #empty>
                        <div class="absolute inset-0 flex flex-col items-center justify-center gap-3 px-6 text-center">
                            <p class="text-sm font-medium text-white">{{ started ? 'No hay jugadores en cancha' : 'Este partido aún no tiene alineación' }}</p>
                            <Link v-if="!started" :href="route('games.select-players', game.id)" class="btn-primary">
                                <i class="fa-solid fa-users"></i> Elegir titulares
                            </Link>
                        </div>
                    </template>
                </Pitch>
            </div>

            <!-- Equipos: fuera de cancha y agregar -->
            <div class="grid sm:grid-cols-2 lg:grid-cols-1 gap-4">
                <section v-for="team in teams" :key="team.side"
                    class="rounded-xl bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] overflow-hidden">
                    <header class="relative flex items-center gap-3 px-4 py-3 border-b border-gray-200 dark:border-white/[0.06]">
                        <span class="absolute left-0 top-0 bottom-0 w-[3px] rounded-r-full" :class="team.side === 1 ? 'bg-emerald-500' : 'bg-blue-500'"></span>
                        <ClubBadge :club="team.club" size="sm" />
                        <div class="flex-1 min-w-0">
                            <h2 class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ team.club.name }}</h2>
                            <p class="text-[11px] text-gray-500 dark:text-slate-400 tabular-nums">{{ onField(team.players).length }} en cancha</p>
                        </div>
                        <button type="button" @click="openBench(team.side)" class="btn-add" title="Agregar jugador sin sustituir">
                            <i class="fa-solid fa-user-plus text-[11px]"></i><span class="hidden sm:inline lg:hidden xl:inline">Agregar</span>
                        </button>
                    </header>

                    <ul v-if="offField(team.players).length" class="divide-y divide-gray-100 dark:divide-white/[0.04]">
                        <li v-for="p in offField(team.players)" :key="p.gi_id">
                            <button type="button" @click="openPlayer(p, team.side)"
                                class="w-full flex items-center gap-3 px-4 py-2.5 text-left hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150">
                                <span class="relative shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-[11px] font-bold tabular-nums opacity-60" :class="accent(team.side)">
                                    {{ p.t_shirt ?? initials(p) }}
                                </span>
                                <span class="flex-1 min-w-0">
                                    <span class="block truncate text-[13px] text-gray-600 dark:text-slate-300">{{ fullName(p) }}</span>
                                    <span class="block truncate text-[11px]" :class="isExpelled(p) ? 'text-red-500 dark:text-red-400' : 'text-gray-400 dark:text-slate-500'">
                                        <template v-if="isExpelled(p)"><i class="fa-solid fa-square text-[8px] mr-1"></i>Expulsado</template>
                                        <template v-else><i class="fa-solid fa-arrow-right-arrow-left text-[9px] mr-1"></i>Salió por {{ nameOf(p.change_player_id) }}</template>
                                    </span>
                                </span>
                                <span v-if="p.goals" class="text-[11px] font-semibold tabular-nums text-gray-500 dark:text-slate-400"><i class="fa-solid fa-futbol text-[9px] mr-0.5"></i>{{ p.goals }}</span>
                            </button>
                        </li>
                    </ul>
                    <p v-else class="px-4 py-3 text-[12px] text-gray-400 dark:text-slate-500">Sin cambios ni expulsados</p>
                </section>

                <button v-if="!finished && started" type="button" @click="ended"
                    class="sm:col-span-2 lg:col-span-1 w-full inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-xl border border-gray-300 dark:border-white/[0.1] text-sm font-semibold text-gray-700 dark:text-slate-200 hover:bg-gray-100 dark:hover:bg-slate-800 active:scale-[0.97] transition duration-150">
                    <i class="fa-solid fa-flag-checkered"></i> Finalizar partido
                </button>
            </div>
        </div>

        <!-- Hoja de acciones del jugador / banca -->
        <Sheet :show="selected !== null" :label="mode === 'bench' ? 'Banca' : 'Acciones del jugador'" @close="closeSheet">
            <!-- Acciones -->
            <template v-if="mode === 'actions' && selected?.player">
                <div class="px-5 pt-3 sm:pt-5 pb-4 flex items-center gap-3 border-b border-gray-200 dark:border-white/[0.06]">
                    <span class="relative shrink-0 w-12 h-12 rounded-full flex items-center justify-center text-sm font-bold tabular-nums" :class="accent(selected.side)">
                        {{ selected.player.t_shirt ?? initials(selected.player) }}
                        <span v-if="cardTone(selected.player)" class="absolute -top-0.5 -left-0.5 w-2.5 h-3.5 rounded-[2px]" :class="cardTone(selected.player)"></span>
                    </span>
                    <div class="flex-1 min-w-0">
                        <h2 class="truncate text-[15px] font-semibold text-gray-900 dark:text-white">{{ fullName(selected.player) }}</h2>
                        <p class="truncate text-[12px] text-gray-500 dark:text-slate-400">
                            {{ selectedTeam.club.name }}
                            <span class="tabular-nums"> · {{ selected.player.goals }} {{ selected.player.goals === 1 ? 'gol' : 'goles' }}</span>
                        </p>
                    </div>
                    <button type="button" @click="closeSheet" aria-label="Cerrar"
                        class="btn-icon -mr-2">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="p-4 sm:p-5 space-y-3 pb-[calc(1rem+env(safe-area-inset-bottom))]">
                    <!-- Gol: la acción más frecuente, la más grande -->
                    <div class="flex gap-2">
                        <button type="button" @click="addGoal"
                            class="flex-1 h-16 inline-flex items-center justify-center gap-3 rounded-xl bg-emerald-500 text-white text-lg font-bold hover:bg-emerald-400 active:scale-[0.97] transition duration-150">
                            <i class="fa-solid fa-futbol"></i> Gol
                        </button>
                        <button type="button" @click="removeGoal" :disabled="!selected.player.goals"
                            class="w-20 h-16 inline-flex flex-col items-center justify-center rounded-xl border border-gray-200 dark:border-white/[0.08] text-gray-500 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-700/40 active:scale-[0.97] transition duration-150 disabled:opacity-30 disabled:pointer-events-none">
                            <i class="fa-solid fa-minus text-sm"></i>
                            <span class="text-[10px] font-medium mt-0.5">Anular</span>
                        </button>
                    </div>

                    <!-- Tarjetas -->
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" @click="yellow" :disabled="selected.player.santion === 'roja'"
                            class="h-20 flex flex-col items-center justify-center gap-1.5 rounded-xl border transition duration-150 active:scale-[0.97] disabled:opacity-30 disabled:pointer-events-none"
                            :class="selected.player.santion === 'amarilla' ? 'border-amber-400 bg-amber-400/10' : 'border-gray-200 dark:border-white/[0.08] hover:bg-amber-400/10'">
                            <span class="w-4 h-5 rounded-[3px] bg-amber-400 shadow-sm"></span>
                            <span class="text-[12px] font-medium text-gray-700 dark:text-slate-200">{{ selected.player.santion === 'amarilla' ? '2ª amarilla' : 'Amarilla' }}</span>
                        </button>
                        <button type="button" @click="red"
                            class="h-20 flex flex-col items-center justify-center gap-1.5 rounded-xl border transition duration-150 active:scale-[0.97]"
                            :class="selected.player.santion === 'roja' ? 'border-red-500 bg-red-500/10' : 'border-gray-200 dark:border-white/[0.08] hover:bg-red-500/10'">
                            <span class="w-4 h-5 rounded-[3px] bg-red-500 shadow-sm"></span>
                            <span class="text-[12px] font-medium text-gray-700 dark:text-slate-200">{{ selected.player.santion === 'roja' ? 'Quitar roja' : 'Roja' }}</span>
                        </button>
                        <button type="button" @click="black"
                            class="h-20 flex flex-col items-center justify-center gap-1.5 rounded-xl border transition duration-150 active:scale-[0.97]"
                            :class="selected.player.card_black ? 'border-slate-500 bg-slate-500/10' : 'border-gray-200 dark:border-white/[0.08] hover:bg-slate-500/10'">
                            <span class="w-4 h-5 rounded-[3px] bg-slate-950 ring-1 ring-white/30 shadow-sm"></span>
                            <span class="text-[12px] font-medium text-gray-700 dark:text-slate-200">{{ selected.player.card_black ? 'Quitar negra' : 'Negra' }}</span>
                        </button>
                    </div>

                    <!-- Cambio -->
                    <button v-if="!isSubbed(selected.player) && !isExpelled(selected.player)" type="button"
                        @click="openBench(selected.side, selected.player)"
                        class="w-full h-12 inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 dark:border-white/[0.08] text-sm font-semibold text-gray-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700/40 active:scale-[0.97] transition duration-150">
                        <i class="fa-solid fa-arrow-right-arrow-left"></i> Hacer cambio
                    </button>
                    <p v-else-if="isSubbed(selected.player)" class="text-center text-[12px] text-gray-400 dark:text-slate-500">
                        Salió del partido por {{ nameOf(selected.player.change_player_id) }}
                    </p>
                </div>
            </template>

            <!-- Banca -->
            <template v-else-if="mode === 'bench'">
                <div class="px-5 pt-3 sm:pt-5 pb-4 flex items-center gap-3 border-b border-gray-200 dark:border-white/[0.06]">
                    <button v-if="selected?.player" type="button" @click="mode = 'actions'" aria-label="Volver"
                        class="btn-icon -ml-2">
                        <i class="fa-solid fa-arrow-left"></i>
                    </button>
                    <div class="flex-1 min-w-0">
                        <h2 class="truncate text-[15px] font-semibold text-gray-900 dark:text-white">
                            {{ benchFor ? `¿Quién entra por ${shortName(benchFor)}?` : 'Agregar jugador' }}
                        </h2>
                        <p class="truncate text-[12px] text-gray-500 dark:text-slate-400">Banca de {{ selectedTeam?.club.name }}</p>
                    </div>
                    <button type="button" @click="closeSheet" aria-label="Cerrar"
                        class="btn-icon -mr-2">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto overscroll-y-contain pb-[env(safe-area-inset-bottom)]">
                    <div v-if="benchLoading" class="py-10 text-center text-[13px] text-gray-400 dark:text-slate-500">
                        <i class="fa-solid fa-spinner fa-spin mr-2"></i>Cargando banca…
                    </div>
                    <p v-else-if="!bench.length" class="px-6 py-10 text-center text-[13px] text-gray-500 dark:text-slate-400">
                        No quedan jugadores en la banca
                    </p>
                    <ul v-else class="divide-y divide-gray-100 dark:divide-white/[0.04]">
                        <li v-for="p in bench" :key="p.id">
                            <div v-if="benchPending(p)" class="flex items-center gap-3 px-5 py-3 bg-gray-50/60 dark:bg-slate-900/30">
                                <span class="shrink-0 w-10 h-10 rounded-full flex items-center justify-center bg-gray-100 dark:bg-slate-900/80 text-gray-400 dark:text-slate-600">
                                    <i class="fa-solid fa-ban text-sm"></i>
                                </span>
                                <span class="flex-1 min-w-0">
                                    <span class="block truncate text-[14px] text-gray-500 dark:text-slate-400">{{ fullName(p) }}</span>
                                    <span class="block text-[11px]" :class="p.santion === 'amarilla' ? 'text-amber-600 dark:text-amber-400' : 'text-red-500 dark:text-red-400'">
                                        Tarjeta {{ p.santion ?? 'negra' }} sin pagar
                                    </span>
                                </span>
                                <button type="button" @click="payBench(p)"
                                    class="shrink-0 inline-flex items-center min-h-[40px] px-3 rounded-lg text-xs font-medium bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20 active:scale-[0.97] transition duration-150">
                                    Cobrar
                                </button>
                            </div>
                            <button v-else type="button" @click="enter(p)"
                                class="w-full flex items-center gap-3 px-5 py-3 text-left hover:bg-gray-50 dark:hover:bg-white/[0.03] active:bg-gray-100 dark:active:bg-white/[0.05] transition-colors duration-150">
                                <span class="shrink-0 w-10 h-10 rounded-full flex items-center justify-center text-[13px] font-bold tabular-nums" :class="accent(selected?.side)">
                                    {{ p.t_shirt ?? initials(p) }}
                                </span>
                                <span class="flex-1 min-w-0 truncate text-[14px] font-medium text-gray-800 dark:text-slate-100">{{ fullName(p) }}</span>
                                <span class="shrink-0 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                    <i class="fa-solid fa-arrow-right-to-bracket mr-1"></i>Entra
                                </span>
                            </button>
                        </li>
                    </ul>
                </div>
            </template>
        </Sheet>

        <!-- Aviso con deshacer -->
        <teleport to="body">
            <transition enter-active-class="duration-300 ease-out" enter-from-class="translate-y-4 opacity-0"
                leave-active-class="duration-200 ease-out" leave-to-class="translate-y-4 opacity-0">
                <div v-if="toast" :key="toast.key" role="status"
                    class="fixed z-[60] left-4 right-4 sm:left-auto sm:right-6 sm:w-96 bottom-[calc(1rem+env(safe-area-inset-bottom))] flex items-center gap-3 pl-4 pr-2 py-2 rounded-xl shadow-xl border transform transition"
                    :class="toast.tone === 'error'
                        ? 'bg-red-600 border-red-500 text-white'
                        : 'bg-slate-900 dark:bg-slate-700 border-white/10 text-white'">
                    <i class="fa-solid" :class="toast.tone === 'error' ? 'fa-triangle-exclamation' : 'fa-circle-check text-emerald-400'"></i>
                    <span class="flex-1 min-w-0 text-[13px] font-medium">{{ toast.message }}</span>
                    <button v-if="toast.undo" type="button" @click="runUndo"
                        class="shrink-0 inline-flex items-center min-h-[40px] px-3 rounded-lg text-[13px] font-semibold text-emerald-300 hover:bg-white/10 active:scale-[0.97] transition duration-150">
                        Deshacer
                    </button>
                    <button v-else type="button" @click="toast = null" aria-label="Cerrar aviso"
                        class="shrink-0 w-9 h-9 inline-flex items-center justify-center rounded-lg text-white/60 hover:text-white hover:bg-white/10 transition-colors duration-150">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>
            </transition>
        </teleport>
    </AdminLayout>
</template>
