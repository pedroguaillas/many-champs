<script setup>

import DialogModal from '@/Components/DialogModal.vue';
import InputError from '@/Components/InputError.vue';

defineProps({
    form: { type: Object, default: () => ({}) },
    errors: { type: Object, default: () => ({}) },
    show: { type: Boolean, default: false }
});

defineEmits(['close', 'save']);

</script>

<template>
    <DialogModal :show="show" maxWidth="lg" @close="$emit('close')">
        <template #title>
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                    <i class="fa-solid fa-pen text-emerald-400 text-xs"></i>
                </div>
                <span>Editar campeonato</span>
            </div>
        </template>
        <template #content>
            <div class="mt-4">
                <form @submit.prevent class="space-y-4">
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-2">
                            Nombre del campeonato
                        </label>
                        <input v-model="form.name" type="text"
                            class="w-full px-3.5 py-2.5 rounded-lg bg-slate-100 border border-slate-200 text-[13px] text-slate-800 placeholder-slate-400 focus:outline-none focus:border-emerald-500/60 focus:ring-1 focus:ring-emerald-500/20 transition-colors duration-200"
                            placeholder="Nombre del campeonato" minlength="3" maxlength="255" />
                        <InputError :message="errors.name" class="mt-1.5" />
                    </div>
                </form>
            </div>
        </template>
        <template #footer>
            <div class="flex items-center gap-3">
                <button @click="$emit('close')"
                    class="px-5 py-2.5 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-all duration-200">
                    Cancelar
                </button>
                <button @click="$emit('save')"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-emerald-500 text-xs font-semibold text-white hover:bg-emerald-400 transition-all duration-200">
                    <i class="fa-solid fa-check text-[10px]"></i>
                    Guardar
                </button>
            </div>
        </template>
    </DialogModal>
</template>
