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
        <div class="rounded-xl bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] overflow-hidden">

            <!-- Card header -->
            <div class="px-5 py-4 border-b border-gray-200 dark:border-white/[0.06] flex items-center gap-2">
                <i class="fa fa-coins text-emerald-400 text-sm"></i>
                <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white uppercase">Diario</h2>
            </div>

            <!-- Responsive -->
            <div class="w-full overflow-x-auto">
                <!-- Tabla -->
                <table class="table-auto w-full text-center">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-white/[0.06]">
                            <th class="w-1 px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">N°</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Nombre</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="payment, i in payments" :key="payment.id" class="border-b border-gray-100 dark:border-white/[0.04] hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150">
                            <td class="px-5 py-3 text-[13px] text-gray-400 dark:text-slate-500 tabular-nums">{{ i + 1 }}</td>
                            <td class="px-5 py-3 text-[13px] text-gray-700 dark:text-slate-200">{{ payment.name }}</td>
                            <td class="px-5 py-3 text-[13px] text-gray-700 dark:text-slate-200 tabular-nums">{{ payment.amount }}</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-white/[0.04] hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150">
                            <td class="px-5 py-3 text-[13px] text-gray-400 dark:text-slate-500 tabular-nums">{{ payments.length + 1 }}</td>
                            <td class="px-5 py-3 text-[13px] text-gray-700 dark:text-slate-200">Targetas amarillas</td>
                            <td class="px-5 py-3 text-[13px] text-gray-700 dark:text-slate-200 tabular-nums">{{ (cardyellow ?? 0) * .5 }}</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-white/[0.04] hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150">
                            <td class="px-5 py-3 text-[13px] text-gray-400 dark:text-slate-500 tabular-nums">{{ payments.length + 2 }}</td>
                            <td class="px-5 py-3 text-[13px] text-gray-700 dark:text-slate-200">Targetas rojas</td>
                            <td class="px-5 py-3 text-[13px] text-gray-700 dark:text-slate-200 tabular-nums">{{ cardred ?? 0 }}</td>
                        </tr>
                        <!-- <tr class="border-b border-gray-100 dark:border-white/[0.04] hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150">
                            <td class="px-5 py-3 text-[13px] text-gray-400 dark:text-slate-500 tabular-nums">{{ payments.length + 3 }}</td>
                            <td class="px-5 py-3 text-[13px] text-gray-700 dark:text-slate-200">Targetas negras</td>
                            <td class="px-5 py-3 text-[13px] text-gray-700 dark:text-slate-200 tabular-nums">{{ (cardblack ?? 0) * .5 }}</td>
                        </tr> -->
                    </tbody>
                    <!-- <tfoot>
                        <tr>
                            <th colspan="2">TOTAL</th>
                            <th>{{ payments.sum(p => p.amount) }}</th>
                        </tr>
                    </tfoot> -->
                </table>
            </div>
        </div>
    </AdminLayout>
</template>