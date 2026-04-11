<script setup>

// Imports
import DialogModal from '@/Components/DialogModal.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';

// Props
defineProps({
    category: { type: Object, default: () => ({}) },
    error: { type: Object, default: () => ({}) },
    show: { type: Boolean, default: false }
});

// Emits
defineEmits(['close', 'save'])

</script>

<template>
    <DialogModal :show="show" maxWidth="lg" @close="$emit('close')">
        <template #title>
            {{ `${category.id === undefined ? 'Añadir' : 'Editar'} categoría` }}
        </template>
        <template #content>
            <div class="mt-4">
                <form @submit.prevent="category.id === undefined ? save() : update()" class="w-2xl">

                    <TextInput v-model="category.name" type="text" class="mt-2 block w-full" placeholder="Categoría"
                        minlength="3" maxlength="50" />
                    <InputError :message="error.name" class="mt-2" />

                    <TextInput v-model="category.inscription" type="text" class="mt-2 block w-full"
                        placeholder="Costo de inscripción" minlength="1" />
                    <InputError :message="error.inscription" class="mt-2" />

                    <select v-model="category.gender" class="mt-2 block w-full px-3.5 py-2.5 rounded-lg bg-white dark:bg-slate-900/80 border border-gray-200 dark:border-white/[0.08] text-[13px] text-gray-700 dark:text-slate-200 focus:outline-none focus:border-emerald-500/40 focus:ring-1 focus:ring-emerald-500/20 transition-colors duration-200 [color-scheme:light] dark:[color-scheme:dark]">
                        <option value="femenino">Femenino</option>
                        <option value="masculino">Masculino</option>
                    </select>
                    <InputError :message="error.gender" class="mt-2" />
                </form>
            </div>
        </template>
        <template #footer>
            <button @click="$emit('save')" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-emerald-500 text-xs font-semibold text-white hover:bg-emerald-400 transition-all duration-200">Guardar</button>
        </template>
    </DialogModal>
</template>