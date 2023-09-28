<script setup>

// Imports
import { ref } from 'vue';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import { router } from '@inertiajs/vue3';

// Props
const props = defineProps({
    game: { type: Object, default: () => { } },
    club1_players: { type: Array, default: () => [] },
    club2_players: { type: Array, default: () => [] }
})

// Reactives
const clubs1 = ref([]);
const clubs2 = ref([]);

// Seleccionar y deseccionar los jugadores
const active1 = (id) => {
    if (!clubs1.value.some(el => el === id)) {
        clubs1.value = [...clubs1.value, id];
    } else {
        clubs1.value = clubs1.value.filter(el => el !== id);
    }
}

const active2 = (id) => {
    if (!clubs2.value.some(el => el === id)) {
        clubs2.value = [...clubs2.value, id];
    } else {
        clubs2.value = clubs2.value.filter(el => el !== id);
    }
}

// Verificar si esta seleccionado
const contiene1 = (id) => {
    return clubs1 && clubs1.value.some(el => el === id)
}

const contiene2 = (id) => {
    return clubs2 && clubs2.value.some(el => el === id)
}

const save = () => {
    router.post(route('play.store', props.game.id),
        { clubs1: clubs1.value, clubs2: clubs2.value },
        {
            onError: () => {
                console.log('Error al seleccionar los jugadores')
            }
        })
}

</script>

<template>
    <AdminLayout :title="'Seleccionar jugadores'">
        <div class="p-4 bg-white rounded drop-shadow-md">

            <!-- Card header -->
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold">{{ `${game.c1_name} ($${game.paid1 === null ? 0 : game.paid1})` }}</h2>
            </div>

            <!-- Resposive -->
            <div class="w-full overflow-x-auto">
                <!-- Tabla -->
                <table v-if="club1_players.length > 0"
                    class="mt-4 text-sm sm:text-xs table-auto w-full text-gray-700 [&>thead>tr>th]:text-left">
                    <thead>
                        <tr class="[&>th]:py-2">
                            <th class="w-1">N°</th>
                            <th>Jugador@s</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="c1, i in club1_players" :key="c1.id" class="border-t [&>td]:py-2">
                            <td>{{ i + 1 }}</td>
                            <td>{{ `${c1.first_name} ${c1.last_name}` }}</td>
                            <td>
                                <!-- Switch Container -->
                                <div @click="$event => active1(c1.id)"
                                    :class="{ 'bg-green-300': contiene1(c1.id), 'bg-gray-300': !contiene1(c1.id) }"
                                    class="w-10 h-6 flex items-center rounded-full p-1 cursor-pointer">

                                    <!-- Switch -->
                                    <div :class="{ 'translate-x-3': contiene1(c1.id) }"
                                        class="bg-white w-5 h-5 rounded-full shadow-md ease-out duration-200"></div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

            </div>
        </div>

        <div class="mt-4 p-4 bg-white rounded drop-shadow-md">

            <!-- Card header -->
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold">{{ `${game.c2_name} ($${game.paid2 === null ? 0 : game.paid2})` }}</h2>
            </div>

            <!-- Resposive -->
            <div class="w-full overflow-x-auto">
                <!-- Tabla -->
                <table v-if="club2_players.length > 0"
                    class="mt-4 text-sm sm:text-xs table-auto w-full text-gray-700 [&>thead>tr>th]:text-left">
                    <thead>
                        <tr class="[&>th]:py-2">
                            <th class="w-1">N°</th>
                            <th>Jugador@s</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="c2, i in club2_players" :key="c2.id" class="border-t [&>td]:py-2">
                            <td>{{ i + 1 }}</td>
                            <td>{{ `${c2.first_name} ${c2.last_name}` }}</td>
                            <td>
                                <!-- Switch Container -->
                                <div @click="$event => active2(c2.id)"
                                    :class="{ 'bg-green-300': contiene2(c2.id), 'bg-gray-300': !contiene2(c2.id) }"
                                    class="w-10 h-6 flex items-center rounded-full p-1 cursor-pointer">

                                    <!-- Switch -->
                                    <div :class="{ 'translate-x-3': contiene2(c2.id) }"
                                        class="bg-white w-5 h-5 rounded-full shadow-md ease-out duration-200"></div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

            </div>
        </div>

        <div class="py-2">
            <button @click="save" class="bg-green-500 px-3 py-1 rounded text-white">Jugar</button>
        </div>
    </AdminLayout>
</template>