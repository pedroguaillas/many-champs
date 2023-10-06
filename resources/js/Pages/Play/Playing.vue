<script setup>

// Imports
import { router } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import Swal from 'sweetalert2';
import { ref } from 'vue';

// Props
const props = defineProps({
    game: { type: Object, default: () => { } },
    c1_players: { type: Array, default: () => [] },
    c2_players: { type: Array, default: () => [] }
})

// Reactive
const players = [];

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
        title: `¿Confirmar la targeta Roja al jugador ${gi.first_name} ${gi.last_name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Si, Agregar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            router.put(route('gameitems.update', gi.gi_id), { santion: 'roja' },
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

const changePlayer = (type) => {
    router.post(route('gameitems.players', props.game), { type }, {
        onSuccess: (res) => {
            console.log(res)
            // players.value = players
        },
        onError: (err) => console.log(err)
    })
}

// Suma los goles
const total = (arr) => arr.reduce((sum, obj) => sum + obj.goals, 0);

</script>

<template>
    <AdminLayout :title="'Seleccionar jugadores'">

        <div class="bg-white rounded drop-shadow-md">

            <!-- Card header -->
            <div class="flex px-4 py-2 justify-between items-center rounded-t bg-blue-500">
                <h2 class="text-xl text-white font-bold">{{ `${game.c1_name} (${total(props.c1_players)})` }}</h2>
                <button @click="$event => changePlayer('1')" class="text-2xl text-white">+</button>
            </div>

            <!-- Resposive -->
            <div class="w-full p-4 overflow-x-auto">

                <!-- form-group -->
                <div v-for="c1, i in c1_players" :key="c1.id" class="w-full pb-2">
                    <!-- input-group -->
                    <div class="flex">
                        <!-- Buttons -->
                        <div class="text-xl text-white flex">
                            <button @click="$event => sumGol(c1)" class="px-2 py-1 bg-blue-500 rounded-l">{{ c1.goals
                            }}</button>
                            <button @click="$event => minusGol(c1)" class="px-3 py-1 bg-slate-500">-</button>
                        </div>
                        <div class="h-8 p-1 border-slate-500 flex-auto">{{ `${c1.t_shirt !== null ? `(${c1.t_shirt})` : ''}
                                                    ${c1.first_name} ${c1.last_name}`
                        }}
                        </div>
                        <div class="flex">
                            <button @click="$event => addCardBlack(c1)"
                                :class="c1.card_black ? 'bg-slate-500 text-white' : 'border-2 border-slate-500 text-slate-500'"
                                class="px-2 py-1"><i class="fa fa-check"></i></button>
                            <button @click="$event => addCardYellow(c1)"
                                :class="c1.santion === 'amarilla' ? 'bg-yellow-500 text-white' : 'border-2 border-yellow-500 text-yellow-500'"
                                class="px-2 py-1"><i class="fa fa-check"></i></button>
                            <button @click="$event => addCardRed(c1)"
                                :class="c1.santion === 'roja' ? 'bg-red-500 text-white' : 'border-2 border-red-500 text-red-500'"
                                class="px-2 py-1"><i class="fa fa-check"></i></button>
                            <button class="px-2 py-1 bg-blue-500 rounded-r"><i class="fa fa-exchange-alt"></i></button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="mt-4 bg-white rounded drop-shadow-md">

            <!-- Card header -->
            <div class="flex px-4 py-2 justify-between items-center rounded-t bg-blue-500">
                <h2 class="text-xl text-white font-bold">{{ `${game.c2_name} (${total(props.c2_players)})` }}</h2>
                <button @click="$event => changePlayer('2')" class="text-2xl text-white">+</button>
            </div>

            <!-- Card Body -->
            <div class="w-full p-4 overflow-x-auto">

                <!-- form-group -->
                <div v-for="c2, i in c2_players" :key="c2.id" class="w-full pb-2">
                    <!-- input-group -->
                    <div class="flex">
                        <!-- Buttons -->
                        <div class="text-xl text-white flex">
                            <button @click="$event => sumGol(c2)" class="px-2 py-1 bg-blue-500 rounded-l">{{ c2.goals
                            }}</button>
                            <button @click="$event => minusGol(c2)" class="px-3 py-1 bg-slate-500">-</button>
                        </div>
                        <div class="h-8 p-1 border-slate-500 flex-auto">{{ `${c2.t_shirt !== null ? `(${c2.t_shirt})` : ''}
                                                    ${c2.first_name} ${c2.last_name}`
                        }}
                        </div>
                        <div class="flex">
                            <button @click="$event => addCardBlack(c2)"
                                :class="c2.card_black ? 'bg-slate-500 text-white' : 'border-2 border-slate-500 text-slate-500'"
                                class="px-2 py-1"><i class="fa fa-check"></i></button>
                            <button @click="$event => addCardYellow(c2)"
                                :class="c2.santion === 'amarilla' ? 'bg-yellow-500 text-white' : 'border-2 border-yellow-500 text-yellow-500'"
                                class="px-2 py-1"><i class="fa fa-check"></i></button>
                            <button @click="$event => addCardRed(c2)"
                                :class="c2.santion === 'roja' ? 'bg-red-500 text-white' : 'border-2 border-red-500 text-red-500'"
                                class="px-2 py-1"><i class="fa fa-check"></i></button>
                            <button class="px-2 py-1 bg-blue-500 rounded-r"><i class="fa fa-exchange-alt"></i></button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>