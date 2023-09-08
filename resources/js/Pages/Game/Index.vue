<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

defineProps({
    category: { type: Object, default: () => { } },
    games: { type: Array, default: () => [] },
    groups: { type: Object, default: () => [] }
});

const form = useForm({ id: '' });

const deleteGame = (game) => {

    const alert = Swal.mixin({
        buttonsStyling: true
    });

    alert.fire({
        title: `¿Esta seguro eliminar el partido entre ${game.c1name} vs ${game.c2name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Si, Eliminar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            form.delete(route('games.destroy', game.id), {
                onFinish: () => {
                    router.reload({ only: ['clubs'] });
                }
            })
        }
    });
}

</script>

<template>
    <AdminLayout :title="'Partidos'">
        <!-- Card -->
        <div class="m-4 p-4 bg-white rounded drop-shadow-md">

            <!-- Card header -->
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold">{{ category.name }}</h2>

                <Link :href="route('games.create', category.id)"
                    class="px-2 bg-green-500 text-2xl text-white rounded font-bold">
                +
                </Link>
            </div>

            <!-- Resposive -->
            <div class="w-full overflow-x-auto">
                <!-- Tabla -->
                <table class="mt-4 text-sm sm:text-xs table-auto w-full text-center text-gray-700">
                    <thead>
                        <tr class="[&>th]:py-2">
                            <th>N°</th>
                            <th>Equipo 1</th>
                            <th>VS</th>
                            <th>Equipo 2</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="game, i in games" :key="game.id" class="border-t [&>td]:py-2">
                            <td>{{ i + 1 }}</td>
                            <td>{{ game.c1name }}</td>
                            <td>VS</td>
                            <td>{{ game.c2name }}</td>
                            <td>
                                <div class="relative inline-flex [&>a>i]:text-white [&>button>i]:text-white">
                                    <Link :href="route('games.edit', game.id)" class="rounded px-2 py-2 bg-blue-500">
                                    <i class="fa fa-edit"></i>
                                    </Link>
                                    <button @click="$event => deleteGame(game)" class="rounded px-2 py-1 ml-1 bg-red-500">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p v-if="games.length === 0">Si quieres crear todos los partidos de fase de grupos haz clic
                    <button @click="toggle" class="text-indigo-500">
                        aqui
                    </button>
                </p>
            </div>
        </div>
    </AdminLayout>
</template>