<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
</script>

<template>
    <AdminLayout :title="'Panel de inicio'">

        <!-- Contenedor de targetas -->
        <div class="grid grid-cols-1 m-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 [&>div]:justify-between text-white">

            <!-- Card 1-->
            <div class="p-4 bg-green-600 rounded drop-shadow-md flex items-center gap-2">
                <div class="flex flex-col justify-end">
                    <h2 class="text-3xl font-bold">622</h2>
                    <p class="text-xl">Jugadores</p>
                </div>
                <i class="fa fa-users text-6xl"></i>
            </div>

            <!-- Card 2-->
            <div class="p-4 bg-green-400 rounded drop-shadow-md flex items-center gap-4">
                <div class="flex flex-col justify-end">
                    <h2 class="text-3xl font-bold">82</h2>
                    <p class="text-xl">Clubes</p>
                </div>
                <i class="fa fa-medal text-6xl"></i>
            </div>

            <!-- Card 3-->
            <div class="p-4 bg-yellow-500 rounded drop-shadow-md flex items-center gap-4">
                <div class="flex flex-col justify-end">
                    <h2 class="text-3xl font-bold">225</h2>
                    <p class="text-xl">Partidos</p>
                </div>
                <i class="far fa-futbol text-6xl"></i>
            </div>

            <!-- Card 4-->
            <div class="p-4 bg-red-600 rounded drop-shadow-md flex items-center gap-4">
                <div class="flex flex-col justify-end">
                    <h2 class="text-3xl font-bold">142</h2>
                    <p class="text-xl">Sanciones</p>
                </div>
                <i class="fa fa-bell text-6xl"></i>
            </div>
        </div>

    </AdminLayout>
</template>
