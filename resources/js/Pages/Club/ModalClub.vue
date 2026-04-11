<script setup>

// Imports
import DialogModal from '@/Components/DialogModal.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';

// Props
defineProps({
    club: { type: Object, default: () => ({}) },
    error: { type: Object, default: () => ({}) },
    groups: { type: Array, default: () => [] },
    show: { type: Boolean, default: false }
});

// Emits
defineEmits(['close', 'save'])

</script>

<template>
    <DialogModal :show="show" maxWidth="lg" @close="$emit('close')">
        <template #title>
            {{ `${club.id === undefined ? 'Añadir' : 'Editar'} club` }}
        </template>
        <template #content>
            <div class="mt-4">
                <form class="w-2xl">

                    <TextInput v-model="club.name" type="text" class="mt-2 block w-full" placeholder="Nombre de club"
                        minlength="3" maxlength="50" />
                    <InputError :message="error.name" class="mt-2" />

                    <TextInput v-model="club.address" type="text" class="mt-2 block w-full" placeholder="Dirección"
                        minlength="3" maxlength="50" />
                    <InputError :message="error.address" class="mt-2" />

                    <label for="Grupo" class="mt-4 inline-block text-[11px] font-semibold uppercase tracking-wider text-slate-500">Grupo</label>
                    <select v-if="groups.length > 0" v-model="club.group_id"
                        class="mt-2 block w-full px-3.5 py-2.5 rounded-lg bg-white dark:bg-slate-900/80 border border-gray-200 dark:border-white/[0.08] text-[13px] text-gray-700 dark:text-slate-200 focus:outline-none focus:border-emerald-500/40 focus:ring-1 focus:ring-emerald-500/20 transition-colors duration-200 [color-scheme:light] dark:[color-scheme:dark]">
                        <option v-for="group in groups" :value="group.id">{{ group.name }}</option>
                    </select>
                    <InputError :message="error.group" class="mt-2" />
                    
                    <TextInput v-model="club.extra_points" type="number" class="mt-2 block w-full" placeholder="Puntos extras" />
                    <InputError :message="error.extra_points" class="mt-2" />
                </form>
            </div>
        </template>
        <template #footer>
            <button @click="$emit('save')" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-emerald-500 text-xs font-semibold text-white hover:bg-emerald-400 transition-all duration-200">Guardar</button>
        </template>
    </DialogModal>
</template>