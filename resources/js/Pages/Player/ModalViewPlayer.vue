<script setup>

// Imports
import Modal from '@/Components/Modal.vue';

// Props
defineProps({
    show: { type: Boolean, default: false },
    auth: { type: Object, default: () => ({}) },
    player: { type: Object, default: () => ({}) },
    club: { type: Object, default: () => ({}) }
});

// Emits
defineEmits(['close'])

</script>

<template>
    <Modal :show="show" maxWidth="sm" @close="$emit('close')">

        <figure class="flex justify-between bg-slate-100 rounded-xl p-0 dark:bg-slate-800">
            <div class="pt-6 p-8 text-left space-y-4 flex flex-col justify-between">
                <blockquote>
                    <p class="text-lg font-medium">
                        “{{ auth.user.current_team.name }}”
                    </p>
                </blockquote>
                <figcaption class="font-medium">
                    <div class="text-slate-700 dark:text-slate-500">
                        {{ player.cedula ?? null }}
                    </div>
                    <div class="text-sky-500 dark:text-sky-400">
                        {{ `${player.first_name} ${player.last_name}` }}
                    </div>
                </figcaption>
                <blockquote>
                    <p class="text-lg font-medium">
                        “{{ club.name }}”
                    </p>
                </blockquote>
            </div>
            <div class="w-[35%] flex justify-end bg-slate-600">
                <img class="h-auto rounded ml-0" :src="`/storage/avatars/${player.photo}`" alt="Avatar" />
            </div>
        </figure>
        
    </Modal>
</template>