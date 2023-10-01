<script setup>

// Imports
import { router } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';

// Props
const props = defineProps({
    game: { type: Object, default: () => { } },
    c1_players: { type: Array, default: () => [] },
    c2_players: { type: Array, default: () => [] }
})

const sumGol = () => {
    console.log(gi_id);
    // router.post(route('gameitems.sum'), { gi_id },
    //     {
    //         onFinish: () => {
    //             router.reload(['c1_players', 'c2_players'])
    //         },
    //         onError: () => {
    //             console.log('Error al guardar el gol')
    //         }
    //     })
}

</script>

<template>
    <AdminLayout :title="'Seleccionar jugadores'">

        <div class="bg-white rounded drop-shadow-md">

            <!-- Card header -->
            <div class="flex px-4 py-2 justify-between items-center rounded-t bg-blue-500">
                <h2 class="text-xl text-white font-bold">{{ `${game.c1_name} ()` }}</h2>
            </div>

            <!-- Resposive -->
            <div class="w-full p-4 overflow-x-auto">

                <!-- form-group -->
                <div v-for="c1, i in c1_players" :key="c1.id" class="w-full pb-2">
                    <!-- input-group -->
                    <div class="flex">
                        <!-- Buttons -->
                        <div class="text-xl text-white flex">
                            <button class="px-2 py-1 bg-blue-500 rounded-l">{{ c1.goals }}</button>
                            <button class="px-3 py-1 bg-slate-500">-</button>
                        </div>
                        <div class="h-8 p-1 border-slate-500 flex-auto">{{ `${c1.t_shirt !== null ? `(${c1.t_shirt})` : ''}
                                                    ${c1.first_name} ${c1.last_name}`
                        }}
                        </div>
                        <div class="flex text-white">
                            <button @click="$event => sumGol()" class="px-2 py-1 bg-slate-500">
                                <i class="fa fa-check"></i>
                            </button>
                            <button class="px-2 py-1 bg-yellow-500"><i class="fa fa-check"></i></button>
                            <button class="px-2 py-1 bg-red-500"><i class="fa fa-check"></i></button>
                            <button class="px-2 py-1 bg-blue-500 rounded-r"><i class="fa fa-exchange-alt"></i></button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="mt-4 bg-white rounded drop-shadow-md">

            <!-- Card header -->
            <div class="flex px-4 py-2 justify-between items-center rounded-t bg-blue-500">
                <h2 class="text-xl text-white font-bold">{{ `${game.c2_name} ()` }}</h2>
            </div>

            <!-- Card Body -->
            <div class="w-full p-4 overflow-x-auto">

                <!-- form-group -->
                <div v-for="c2, i in c2_players" :key="c2.id" class="w-full pb-2">
                    <!-- input-group -->
                    <div class="flex">
                        <!-- Buttons -->
                        <div class="text-xl text-white flex">
                            <button class="px-2 py-1 bg-blue-500 rounded-l">+</button>
                            <button class="px-3 py-1 bg-slate-500">-</button>
                        </div>
                        <div class="h-8 p-1 border-slate-500 flex-auto">{{ `${c2.t_shirt} ${c2.first_name} ${c2.last_name}`
                        }}
                        </div>
                        <div class="flex text-white">
                            <button class="px-2 py-1 bg-slate-500"><i class="fa fa-check"></i></button>
                            <button class="px-2 py-1 bg-yellow-500"><i class="fa fa-check"></i></button>
                            <button class="px-2 py-1 bg-red-500"><i class="fa fa-check"></i></button>
                            <button class="px-2 py-1 bg-blue-500 rounded-r"><i class="fa fa-exchange-alt"></i></button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>