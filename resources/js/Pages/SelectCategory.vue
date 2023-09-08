<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    categories: { type: Array, default: () => [] },
    type: { type: String, default: () => '' },
});

const colors = ref(['bg-blue-800', 'bg-blue-700', 'bg-blue-600']);
const colorSTrans = ref(['bg-blue-900', 'bg-blue-800', 'bg-blue-700']);

</script>

<template>
    <AdminLayout :title="'Seleccionar Categoría'">

        <!-- Contenedor de targetas -->
        <div
            class="grid grid-cols-1 m-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 [&>div]:justify-between text-white">

            <!-- Card 1-->
            <div v-for="(category, i) in categories" :class=colors[i]
                class="rounded [&>div]:justify-between drop-shadow-md flex flex-col gap-2">
                <div class="flex px-4 pt-4">
                    <div class="flex flex-col">
                        <h2 class="text-3xl font-bold">622</h2>
                        <p class="text-xl">{{ category.name }}</p>
                    </div>
                    <i class="fa fa-users text-6xl"></i>
                </div>
                <Link :href="route(`${type}.index`, category.id)" :class=colorSTrans[i] class="w-full text-center">
                Ver mas <i class="fas fa-lg fa-arrow-circle-right"></i>
                </Link>
            </div>
        </div>
    </AdminLayout>
</template>