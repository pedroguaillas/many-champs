<script setup>

// Imports
import { router } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import Swal from 'sweetalert2';
import { reactive, ref } from 'vue';
import axios from 'axios';
import SelectPlayersToChange from './SelectPlayersToChange.vue';

// Props
const props = defineProps({
    game: { type: Object, default: () => { } },
    c1_players: { type: Array, default: () => [] },
    c2_players: { type: Array, default: () => [] }
})

// Reactive
const modal = ref(false);
const players = reactive([]);
const change_player_id = ref(0);

const swalConfig = {
    background: '#1e293b',
    color: '#e2e8f0',
    confirmButtonColor: '#10b981',
    cancelButtonColor: '#334155',
};

const sumGol = (gi) => {
    Swal.fire({
        title: `¿Esta seguro Agregar un Gol al jugador ${gi.first_name} ${gi.last_name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Si, Agregar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar',
        ...swalConfig,
    }).then((result) => {
        if (result.isConfirmed) {
            router.put(route('gameitems.update', gi.gi_id), { goals: ++gi.goals },
                {
                    onFinish: () => {
                        router.reload(['c1_players', 'c2_players'])
                    },
                    onError: () => {
                        console.log('Error al agregar el gol')
                    }
                })
        }
    });
}

const minusGol = (gi) => {
    Swal.fire({
        title: `¿Esta seguro Reducir un Gol al jugador ${gi.first_name} ${gi.last_name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Si, Agregar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar',
        ...swalConfig,
    }).then((result) => {
        if (result.isConfirmed) {
            if (gi.goals > 0) {
                router.put(route('gameitems.update', gi.gi_id), { goals: --gi.goals },
                    {
                        onFinish: () => {
                            router.reload(['c1_players', 'c2_players'])
                        },
                        onError: () => {
                            console.log('Error al reducir el gol')
                        }
                    })
            }
        }
    });
}

const addCardBlack = (gi) => {
    Swal.fire({
        title: `¿Confirmar la targeta Negra al jugador ${gi.first_name} ${gi.last_name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Si, Agregar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar',
        ...swalConfig,
    }).then((result) => {
        if (result.isConfirmed) {
            router.put(route('gameitems.update', gi.gi_id), { card_black: true },
                {
                    onFinish: () => {
                        router.reload(['c1_players', 'c2_players'])
                    },
                    onError: () => {
                        console.log('Error al registrar targeta negra')
                    }
                })
        }
    });
}

const addCardYellow = (gi) => {
    Swal.fire({
        title: `¿Confirmar la targeta Amarilla al jugador ${gi.first_name} ${gi.last_name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Si, Agregar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar',
        ...swalConfig,
    }).then((result) => {
        if (result.isConfirmed) {
            const santion = gi.santion === null ? 'amarilla' : 'roja';
            router.put(route('gameitems.update', gi.gi_id), { santion },
                {
                    onFinish: () => {
                        router.reload(['c1_players', 'c2_players'])
                    },
                    onError: () => {
                        console.log('Error al registrar targeta amarilla')
                    }
                })
        }
    });
}

const addCardRed = (gi) => {
    Swal.fire({
        title: `¿${gi.santion === 'roja' ? 'Quitar' : 'Confirmar'} la targeta Roja al jugador ${gi.first_name} ${gi.last_name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Si, Agregar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar',
        ...swalConfig,
    }).then((result) => {
        if (result.isConfirmed) {
            const santion = gi.santion === 'roja' ? null : 'roja';
            router.put(route('gameitems.update', gi.gi_id), { santion },
                {
                    onFinish: () => {
                        router.reload(['c1_players', 'c2_players'])
                    },
                    onError: () => {
                        console.log('Error al registrar targeta roja')
                    }
                })
        }
    });
}

const changePlayer = (type, player_id = 0) => {
    change_player_id.value = player_id
    axios.post(route('gameitems.players', props.game), { type })
        .then(({ data: { result } }) => {
            players.length = 0;
            const iterator = result.keys()
            for (const key of iterator) {
                players.push(result[key]);
            }
            toggle()
        })
}

const selectPlayer = (player_id) => {
    const data = { player_id, game_id: props.game.id, entered_in: change_player_id.value > 0 ? 'cambio' : 'ajuste' }

    // Solo cuando sea cambio
    if (change_player_id.value > 0) {
        data.change_player_id = change_player_id.value
    }

    router.post(route('gameitems.store'),
        data,
        {
            onFinish: () => {
                router.reload(['c1_players', 'c2_players'])
                toggle();
            },
            onError: () => {
                console.log('Error al guardar los jugadores')
            }
        })
}

const paySantion = (gi) => {
    Swal.fire({
        title: `Cobro de sanción`,
        text: `¿Esta seguro cobrar la targete ${gi.santion} de ${gi.first_name} ${gi.last_name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Si, Cobrar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar',
        ...swalConfig,
    }).then((result) => {
        if (result.isConfirmed) {
            // Registra el pago
            axios.patch(route('gameitems.patch', gi.gi_santion_id), { patch: 'santion' })
                .then(() => {
                    // Pone en null la sancion pagada
                    players.forEach(player => {
                        if (player.santion && player.gi_santion_id === gi.gi_santion_id)
                            player.santion = null
                    })
                })
        }
    });
}

const toggle = () => {
    modal.value = !modal.value
}

// Suma los goles
const total = (arr) => arr.reduce((sum, obj) => sum + obj.goals, 0);

// Finalizar el partido
const ended = () => {
    router.put(route('games.ended', props.game.id),
        { state: 'finalizado' },
        {
            onError: () => {
                console.log('Error al guardar los jugadores')
            }
        })
}

</script>

<template>
    <AdminLayout :title="'Jugando'">

        <!-- Club 1 Card -->
        <div class="rounded-xl bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] overflow-hidden">

            <!-- Card header -->
            <div class="px-5 py-4 border-b border-gray-200 dark:border-white/[0.06] flex justify-between items-center relative">
                <span class="absolute left-0 top-0 bottom-0 w-[3px] bg-emerald-500 rounded-r-full"></span>
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                        <i class="fa-solid fa-shield-halved text-emerald-400 text-[11px]"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold tracking-tight text-white">{{ game.c1_name }}</h2>
                        <span class="text-[11px] font-semibold text-emerald-400">{{ total(props.c1_players) }} goles</span>
                    </div>
                </div>
                <button @click="$event => changePlayer('1')"
                    class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 dark:text-slate-500 hover:text-emerald-400 hover:bg-emerald-500/10 transition-colors duration-150">
                    <i class="fa fa-user-plus text-[11px]"></i>
                </button>
            </div>

            <!-- Players -->
            <div class="p-4 space-y-2">
                <div v-for="c1, i in c1_players" :key="c1.id"
                    class="rounded-lg border border-gray-100 dark:border-white/[0.04] hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150"
                    :class="c1.change_player_id !== null ? 'opacity-40' : ''">
                    <div class="flex items-center">
                        <!-- Goal counter -->
                        <div class="flex">
                            <button @click="$event => sumGol(c1)"
                                class="h-10 px-3 rounded-l-lg bg-emerald-500/10 text-emerald-400 text-[13px] font-semibold border-r border-gray-200 dark:border-white/[0.06] hover:bg-emerald-500/20 transition-colors duration-150"
                                :disabled="c1.change_player_id !== null">
                                {{ c1.goals }}
                            </button>
                            <button @click="$event => minusGol(c1)"
                                class="h-10 px-2.5 bg-gray-200 dark:bg-slate-700/40 text-gray-500 dark:text-slate-400 text-[11px] hover:text-gray-700 dark:hover:text-gray-700 dark:text-slate-200 hover:bg-slate-700/60 transition-colors duration-150"
                                :disabled="c1.change_player_id !== null">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                        <!-- Player name -->
                        <div class="h-10 flex-auto flex items-center px-3 text-[13px] text-gray-700 dark:text-slate-200 overflow-x-auto">
                            <span v-if="c1.t_shirt !== null" class="text-gray-400 dark:text-slate-500 mr-1.5">({{ c1.t_shirt }})</span>
                            {{ `${c1.first_name} ${c1.last_name}` }}
                        </div>
                        <!-- Cards -->
                        <div class="flex items-center">
                            <button @click="$event => addCardYellow(c1)"
                                :class="c1.santion === 'amarilla' ? 'bg-amber-500 text-white' : 'text-amber-500/60 hover:text-amber-400 hover:bg-amber-500/10'"
                                class="h-10 px-2.5 text-[11px] transition-colors duration-150"
                                :disabled="c1.change_player_id !== null">
                                <i class="fa fa-square"></i>
                            </button>
                            <button @click="$event => addCardRed(c1)"
                                :class="c1.santion === 'roja' ? 'bg-red-500 text-white' : 'text-red-500/60 hover:text-red-400 hover:bg-red-500/10'"
                                class="h-10 px-2.5 text-[11px] transition-colors duration-150"
                                :disabled="c1.change_player_id !== null">
                                <i class="fa" :class="c1.santion === 'roja' ? 'fa-close' : 'fa-square'"></i>
                            </button>
                            <button @click="$event => changePlayer('1', c1.id)"
                                class="h-10 px-2.5 rounded-r-lg text-gray-400 dark:text-slate-500 hover:text-gray-700 dark:hover:text-gray-700 dark:text-slate-200 hover:bg-gray-200 dark:hover:bg-gray-200 dark:bg-slate-700/50 text-[11px] transition-colors duration-150"
                                :hidden="c1.change_player_id !== null">
                                <i class="fa fa-user-edit"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Club 2 Card -->
        <div class="mt-4 rounded-xl bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] overflow-hidden">

            <!-- Card header -->
            <div class="px-5 py-4 border-b border-gray-200 dark:border-white/[0.06] flex justify-between items-center relative">
                <span class="absolute left-0 top-0 bottom-0 w-[3px] bg-blue-500 rounded-r-full"></span>
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center">
                        <i class="fa-solid fa-shield-halved text-blue-400 text-[11px]"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold tracking-tight text-white">{{ game.c2_name }}</h2>
                        <span class="text-[11px] font-semibold text-blue-400">{{ total(props.c2_players) }} goles</span>
                    </div>
                </div>
                <button @click="$event => changePlayer('2')"
                    class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 dark:text-slate-500 hover:text-blue-400 hover:bg-blue-500/10 transition-colors duration-150">
                    <i class="fa fa-user-plus text-[11px]"></i>
                </button>
            </div>

            <!-- Players -->
            <div class="p-4 space-y-2">
                <div v-for="c2, i in c2_players" :key="c2.id"
                    class="rounded-lg border border-gray-100 dark:border-white/[0.04] hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150"
                    :class="c2.change_player_id !== null ? 'opacity-40' : ''">
                    <div class="flex items-center">
                        <!-- Goal counter -->
                        <div class="flex">
                            <button @click="$event => sumGol(c2)"
                                class="h-10 px-3 rounded-l-lg bg-blue-500/10 text-blue-400 text-[13px] font-semibold border-r border-gray-200 dark:border-white/[0.06] hover:bg-blue-500/20 transition-colors duration-150"
                                :disabled="c2.change_player_id !== null">
                                {{ c2.goals }}
                            </button>
                            <button @click="$event => minusGol(c2)"
                                class="h-10 px-2.5 bg-gray-200 dark:bg-slate-700/40 text-gray-500 dark:text-slate-400 text-[11px] hover:text-gray-700 dark:hover:text-gray-700 dark:text-slate-200 hover:bg-slate-700/60 transition-colors duration-150"
                                :disabled="c2.change_player_id !== null">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                        <!-- Player name -->
                        <div class="h-10 flex-auto flex items-center px-3 text-[13px] text-gray-700 dark:text-slate-200 overflow-x-auto">
                            <span v-if="c2.t_shirt !== null" class="text-gray-400 dark:text-slate-500 mr-1.5">({{ c2.t_shirt }})</span>
                            {{ `${c2.first_name} ${c2.last_name}` }}
                        </div>
                        <!-- Cards -->
                        <div class="flex items-center">
                            <button @click="$event => addCardYellow(c2)"
                                :class="c2.santion === 'amarilla' ? 'bg-amber-500 text-white' : 'text-amber-500/60 hover:text-amber-400 hover:bg-amber-500/10'"
                                class="h-10 px-2.5 text-[11px] transition-colors duration-150"
                                :disabled="c2.change_player_id !== null">
                                <i class="fa fa-square"></i>
                            </button>
                            <button @click="$event => addCardRed(c2)"
                                :class="c2.santion === 'roja' ? 'bg-red-500 text-white' : 'text-red-500/60 hover:text-red-400 hover:bg-red-500/10'"
                                class="h-10 px-2.5 text-[11px] transition-colors duration-150"
                                :disabled="c2.change_player_id !== null">
                                <i :class="c2.santion === 'roja' ? 'fa-close' : 'fa-square'" class="fa"></i>
                            </button>
                            <button @click="$event => changePlayer('2', c2.id)"
                                class="h-10 px-2.5 rounded-r-lg text-gray-400 dark:text-slate-500 hover:text-gray-700 dark:hover:text-gray-700 dark:text-slate-200 hover:bg-gray-200 dark:hover:bg-gray-200 dark:bg-slate-700/50 text-[11px] transition-colors duration-150"
                                :hidden="c2.change_player_id !== null">
                                <i class="fa fa-user-edit"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Finalizar el partido -->
        <div class="mt-4 flex items-center gap-4">
            <div @click="$event => ended()"
                :class="game.state === 'finalizado' ? 'bg-emerald-500' : 'bg-slate-700'"
                class="w-10 h-6 flex items-center rounded-full p-1 cursor-pointer transition-colors duration-200">
                <div :class="{ 'translate-x-3': game.state === 'finalizado' }"
                    class="bg-white w-5 h-5 rounded-full shadow-md ease-out duration-200"></div>
            </div>
            <span class="text-[13px] text-gray-500 dark:text-slate-400">Finalizar el partido</span>
        </div>
    </AdminLayout>
    <SelectPlayersToChange :players="players" :show="modal" @close="toggle" @selectPlayer="selectPlayer"
        @paySantion="paySantion" />
</template>
