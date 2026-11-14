<script setup>

// Imports
import DialogModal from '@/Components/DialogModal.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import { JERSEY_COLORS, isHex, textOn } from '@/Shared/teamColors';

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

                    <!-- Color de camiseta: se usa en la cancha durante el partido -->
                    <fieldset class="mt-4">
                        <legend class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Color de camiseta</legend>
                        <div role="radiogroup" aria-label="Color de camiseta" class="mt-2 flex flex-wrap items-center gap-2">
                            <button v-for="c in JERSEY_COLORS" :key="c.hex" type="button" role="radio"
                                :aria-checked="club.color?.toLowerCase() === c.hex" :aria-label="c.name" :title="c.name"
                                @click="club.color = c.hex"
                                class="relative w-10 h-10 rounded-full ring-1 ring-inset ring-black/15 dark:ring-white/20 flex items-center justify-center transition duration-150 active:scale-[0.97]
                                       focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                                :class="club.color?.toLowerCase() === c.hex ? 'outline outline-2 outline-offset-2 outline-emerald-500' : ''"
                                :style="{ backgroundColor: c.hex, color: textOn(c.hex) }">
                                <i v-if="club.color?.toLowerCase() === c.hex" class="fa-solid fa-check text-xs"></i>
                            </button>

                            <!-- Otro color -->
                            <label title="Otro color"
                                class="relative w-10 h-10 rounded-full overflow-hidden cursor-pointer ring-1 ring-inset ring-black/15 dark:ring-white/20 flex items-center justify-center
                                       bg-[conic-gradient(#ef4444,#f59e0b,#22c55e,#0ea5e9,#8b5cf6,#ef4444)]"
                                :class="isHex(club.color) && !JERSEY_COLORS.some(c => c.hex === club.color.toLowerCase()) ? 'outline outline-2 outline-offset-2 outline-emerald-500' : ''">
                                <span class="sr-only">Otro color</span>
                                <input type="color" :value="isHex(club.color) ? club.color : '#ffffff'" @input="club.color = $event.target.value"
                                    class="absolute inset-0 opacity-0 cursor-pointer" />
                            </label>

                            <button v-if="club.color" type="button" @click="club.color = ''"
                                class="inline-flex items-center min-h-[40px] px-2 text-[12px] font-medium text-gray-500 dark:text-slate-400 hover:text-gray-800 dark:hover:text-slate-200 transition-colors duration-150">
                                Quitar
                            </button>
                        </div>
                        <p class="mt-1.5 text-[11px] text-gray-400 dark:text-slate-500">
                            {{ club.color ? 'Así se verán sus jugadores en la cancha.' : 'Sin color: local blanco, visita azul.' }}
                        </p>
                    </fieldset>
                    <InputError :message="error.color" class="mt-2" />

                    <TextInput v-model="club.address" type="text" class="mt-2 block w-full" placeholder="Dirección"
                        minlength="3" maxlength="50" />
                    <InputError :message="error.address" class="mt-2" />

                    <label for="Grupo" class="mt-4 inline-block text-[11px] font-semibold uppercase tracking-wider text-slate-500">Grupo</label>
                    <select v-if="groups.length > 0" v-model="club.group_id"
                        class="mt-2 block w-full px-3.5 py-2.5 rounded-lg bg-white dark:bg-slate-900/80 border border-gray-200 dark:border-white/[0.08] text-[13px] text-gray-700 dark:text-slate-200 focus:outline-none focus:border-emerald-500/40 focus:ring-1 focus:ring-emerald-500/20 transition-colors duration-200 [color-scheme:light] dark:[color-scheme:dark]">
                        <option v-for="group in groups" :value="group.id">{{ group.name }}</option>
                    </select>
                    <InputError :message="error.group" class="mt-2" />
                    
                    <TextInput v-model="club.extra_points" type="number" inputmode="numeric" class="mt-2 block w-full" placeholder="Puntos extras" />
                    <InputError :message="error.extra_points" class="mt-2" />
                </form>
            </div>
        </template>
        <template #footer>
            <button @click="$emit('save')" class="btn-primary">Guardar</button>
        </template>
    </DialogModal>
</template>