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

const sumGol = (gi) => {

    const alert = Swal.mixin({
        buttonsStyling: true
    });

    alert.fire({
        title: `¿Esta seguro Agregar un Gol al jugador ${gi.first_name} ${gi.last_name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Si, Agregar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar'
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

    const alert = Swal.mixin({
        buttonsStyling: true
    });

    alert.fire({
        title: `¿Esta seguro Reducir un Gol al jugador ${gi.first_name} ${gi.last_name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Si, Agregar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar'
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

    const alert = Swal.mixin({
        buttonsStyling: true
    });

    alert.fire({
        title: `¿Confirmar la targeta Negra al jugador ${gi.first_name} ${gi.last_name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Si, Agregar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar'
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

    const alert = Swal.mixin({
        buttonsStyling: true
    });

    alert.fire({
        title: `¿Confirmar la targeta Amarilla al jugador ${gi.first_name} ${gi.last_name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Si, Agregar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar'
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

    const alert = Swal.mixin({
        buttonsStyling: true
    });

    alert.fire({
        title: `¿${gi.santion === 'roja' ? 'Quitar' : 'Confirmar'} la targeta Roja al jugador ${gi.first_name} ${gi.last_name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Si, Agregar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar'
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

    const alert = Swal.mixin({
        buttonsStyling: true
    });

    alert.fire({
        title: `Cobro de sanción`,
        text: `¿Esta seguro cobrar la targete ${gi.santion} de ${gi.first_name} ${gi.last_name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Si, Cobrar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar'
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
    <AdminLayout :title="'Jungando'">

        <div class="bg-white rounded drop-shadow-md">

            <!-- Card header -->
            <div class="flex px-2 sm:px-4 py-2 justify-between items-center rounded-t bg-blue-500">
                <h2 class="text-sm sm:text-lg text-white font-bold">{{ `${game.c1_name} (${total(props.c1_players)})` }}
                </h2>
                <button @click="$event => changePlayer('1')" class="text-sm sm:text-xl text-white">
                    <i class="fa fa-user-plus"></i>
                </button>
            </div>

            <!-- Resposive -->
            <div class="w-full p-4 overflow-x-auto">

                <!-- form-group -->
                <div v-for="c1, i in c1_players" :key="c1.id" class="w-full pb-2">
                    <!-- input-group -->
                    <div class="flex">
                        <!-- Buttons -->
                        <div class="text-white flex">
                            <button @click="$event => sumGol(c1)" class="text-sm px-2 py-1 bg-blue-500 rounded-l"
                                :disabled="c1.change_player_id !== null">
                                {{ c1.goals }}
                            </button>
                            <button @click="$event => minusGol(c1)" class="text-xs px-2 py-1 bg-slate-500"
                                :disabled="c1.change_player_id !== null">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                        <div class="h-9 p-1 border border-slate-500 flex-auto overflow-x-auto text-xs sm:text-sm">{{
                            `${c1.t_shirt !== null ?
                                `(${c1.t_shirt})`
                                : ''}
                                                    ${c1.first_name} ${c1.last_name}`
                        }}
                        </div>
                        <div class="flex text-xs sm:text-sm">
                            <!-- <button @click="$event => addCardBlack(c1)"
                                :class="c1.card_black ? 'bg-slate-500 text-white' : 'border-2 border-slate-500 text-slate-500'"
                                class="px-2 py-1"><i class="fa fa-check"></i></button> -->
                            <button @click="$event => addCardYellow(c1)"
                                :class="c1.santion === 'amarilla' ? 'bg-yellow-500 text-white' : 'border-2 border-yellow-500 text-yellow-500'"
                                class="px-2 py-1" :disabled="c1.change_player_id !== null">
                                <i class="fa fa-check"></i>
                            </button>
                            <button @click="$event => addCardRed(c1)"
                                :class="c1.santion === 'roja' ? 'bg-red-500 text-white' : 'border-2 border-red-500 text-red-500'"
                                class="px-2 py-1" :disabled="c1.change_player_id !== null">
                                <i class="fa" :class="c1.santion === 'roja' ? 'fa-close' : 'fa-check'"></i>
                            </button>
                            <button @click="$event => changePlayer('1', c1.id)"
                                class="px-2 py-1 bg-blue-500 rounded-r text-white" :hidden="c1.change_player_id !== null">
                                <i class="fa fa-user-edit"></i>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="mt-4 bg-white rounded drop-shadow-md">

            <!-- Card header -->
            <div class="flex px-2 sm:px-4 py-2 justify-between items-center rounded-t bg-blue-500">
                <h2 class="text-sm sm:text-lg text-white font-bold">{{ `${game.c2_name} (${total(props.c2_players)})` }}
                </h2>
                <button @click="$event => changePlayer('2')" class="text-sm sm:text-xl text-white">
                    <i class="fa fa-user-plus"></i>
                </button>
            </div>

            <!-- Card Body -->
            <div class="w-full p-4 overflow-x-auto">

                <!-- form-group -->
                <div v-for="c2, i in c2_players" :key="c2.id" class="w-full pb-2">
                    <!-- input-group -->
                    <div class="flex">
                        <!-- Buttons -->
                        <div class="text-white flex">
                            <button @click="$event => sumGol(c2)" class="text-sm px-2 py-1 bg-blue-500 rounded-l"
                                :disabled="c2.change_player_id !== null">{{
                                    c2.goals
                                }}</button>
                            <button @click="$event => minusGol(c2)" class="text-xs px-2 py-1 bg-slate-500"
                                :disabled="c2.change_player_id !== null">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                        <div class="h-9 p-1 border border-slate-500 flex-auto overflow-x-auto text-xs sm:text-sm">{{
                            `${c2.t_shirt !== null ?
                                `(${c2.t_shirt})`
                                : ''}
                                                    ${c2.first_name} ${c2.last_name}`
                        }}
                        </div>
                        <div class="flex text-xs sm:text-sm">
                            <!-- <button @click="$event => addCardBlack(c2)"
                                :class="c2.card_black ? 'bg-slate-500 text-white' : 'border-2 border-slate-500 text-slate-500'"
                                class="px-2 py-1"><i class="fa fa-check"></i></button> -->
                            <button @click="$event => addCardYellow(c2)"
                                :class="c2.santion === 'amarilla' ? 'bg-yellow-500 text-white' : 'border-2 border-yellow-500 text-yellow-500'"
                                class="px-2 py-1" :disabled="c2.change_player_id !== null">
                                <i class="fa fa-check"></i>
                            </button>
                            <button @click="$event => addCardRed(c2)"
                                :class="c2.santion === 'roja' ? 'bg-red-500 text-white' : 'border-2 border-red-500 text-red-500'"
                                class="px-2 py-1" :disabled="c2.change_player_id !== null">
                                <i :class="c2.santion === 'roja' ? 'fa-close' : 'fa-check'" class="fa"></i>
                            </button>
                            <button @click="$event => changePlayer('2', c2.id)"
                                class="px-2 py-1 rounded-r bg-blue-500 text-white" :hidden="c2.change_player_id !== null">
                                <i class="fa fa-user-edit"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Finalizar el partido -->
        <div class="mt-4 flex">
            <!-- Switch Container -->
            <div @click="$event => ended()"
                :class="{ 'bg-green-300': game.state === 'finalizado', 'bg-gray-300': game.state !== 'finalizado' }"
                class="w-10 h-6 flex items-center rounded-full p-1 cursor-pointer">

                <!-- Switch -->
                <div :class="{ 'translate-x-3': game.state === 'finalizado' }"
                    class="bg-white w-5 h-5 rounded-full shadow-md ease-out duration-200"></div>
            </div>
            <div class="ml-4 text-xs sm:text-sm">Finalizar el partido</div>
        </div>
    </AdminLayout>
    <SelectPlayersToChange :players="players" :show="modal" @close="toggle" @selectPlayer="selectPlayer"
        @paySantion="paySantion" />
</template>