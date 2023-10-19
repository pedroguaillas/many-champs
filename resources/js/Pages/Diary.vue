<script setup>

// Imports
import AdminLayout from '@/Layouts/AdminLayout.vue';

// Props
defineProps({
    payments: { type: Array, default: () => [] },
    cardred: { type: Number, default: () => 0 },
    cardyellow: { type: Number, default: () => 0 },
    cardblack: { type: Number, default: () => 0 }
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

            <!-- Resposive -->
            <div class="w-full overflow-x-auto">
                <!-- Tabla -->
                <table class="mt-4 text-sm sm:text-xs table-auto w-full text-center text-gray-700">
                    <thead>
                        <tr class="[&>th]:py-2">
                            <th class="w-1">N°</th>
                            <th>Nombre</th>
                            <th>Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="payment, i in payments" :key="payment.id" class="border-t [&>td]:py-2">
                            <td>{{ i + 1 }}</td>
                            <td>{{ payment.name }}</td>
                            <td>{{ payment.amount }}</td>
                        </tr>
                        <tr class="border-t [&>td]:py-2">
                            <td>{{ payments.length + 1 }}</td>
                            <td>Targetas negras</td>
                            <td>{{ (cardblack ?? 0) * .5 }}</td>
                        </tr>
                        <tr class="border-t [&>td]:py-2">
                            <td>{{ payments.length + 2 }}</td>
                            <td>Targetas amarillas</td>
                            <td>{{ (cardblack ?? 0) * .5 }}</td>
                        </tr>
                        <tr class="border-t [&>td]:py-2">
                            <td>{{ payments.length + 3 }}</td>
                            <td>Targetas rojas</td>
                            <td>{{ cardred ?? 0 }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>