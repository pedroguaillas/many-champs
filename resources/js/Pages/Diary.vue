<script setup>

// Imports
import AdminLayout from '@/Layouts/AdminLayout.vue';

// Props
defineProps({
    category: { type: Object, default: () => { } },
    clubs: { type: Array, default: () => [] }
});

</script>

<template>
    <AdminLayout title="Ingresos diarios">

        <!-- Card -->
        <div class="p-4 bg-white rounded drop-shadow-md">

            <!-- Card header -->
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold uppercase">Diario</h2>
            </div>
        </div>
    </AdminLayout>
</template>