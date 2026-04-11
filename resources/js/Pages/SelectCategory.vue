<script setup>

// Imports
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link } from '@inertiajs/vue3';

// Props
defineProps({
    categories: { type: Array, default: () => [] },
    type: { type: String, default: () => '' },
});

</script>

<template>
    <AdminLayout :title="'Seleccionar Categoría'">

        <!-- Contenedor de tarjetas -->
        <div v-if="categories.length > 0"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">

            <!-- Card -->
            <div v-for="(category, i) in categories" :key="category.id"
                class="rounded-xl bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] overflow-hidden hover:bg-gray-50 dark:hover:bg-slate-800/80 transition-colors duration-200 group">
                <div class="p-5 flex justify-between items-start">
                    <div class="flex flex-col gap-1">
                        <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ category.count }}</span>
                        <span class="text-[13px] text-gray-600 dark:text-slate-300">{{ category.name }}</span>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                        <i class="fa fa-users text-emerald-400 text-sm"></i>
                    </div>
                </div>
                <Link :href="route(`${type}.index`, category.id)"
                    class="block w-full px-5 py-2.5 border-t border-gray-200 dark:border-white/[0.06] text-[12px] text-gray-400 dark:text-slate-500 hover:text-emerald-400 hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150 text-center">
                    Ver mas <i class="fas fa-arrow-right ml-1 text-[10px]"></i>
                </Link>
            </div>
        </div>

        <!-- Empty state -->
        <div v-else class="flex flex-col items-center justify-center py-12">
            <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-slate-800 border border-gray-200 dark:border-white/[0.06] flex items-center justify-center mb-4">
                <i class="fa fa-folder-open text-gray-400 dark:text-slate-500 text-lg"></i>
            </div>
            <p class="text-[13px] text-gray-500 dark:text-slate-400 text-center">
                Debe crear las categorias para ver los {{ type === 'games' ? 'juegos' : 'clubes' }} o haz clic
                <Link class="text-emerald-400 hover:text-emerald-300 transition-colors" :href="route('categories.index')">aqui</Link>
            </p>
        </div>
    </AdminLayout>
</template>
