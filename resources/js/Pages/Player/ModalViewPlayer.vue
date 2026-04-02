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

        <figure class="flex justify-between overflow-hidden">
            <div class="pt-6 p-8 text-left space-y-4 flex flex-col justify-between">
                <blockquote>
                    <p class="text-[15px] font-semibold tracking-tight text-white">
                        "{{ auth.user.current_team.name }}"
                    </p>
                </blockquote>
                <figcaption>
                    <div class="text-[13px] text-slate-500">
                        {{ player.cedula ?? null }}
                    </div>
                    <div class="text-[13px] font-medium text-emerald-400">
                        {{ `${player.first_name} ${player.last_name}` }}
                    </div>
                </figcaption>
                <blockquote>
                    <p class="text-[15px] font-semibold tracking-tight text-white">
                        "{{ club.name }}"
                    </p>
                </blockquote>
            </div>
            <div class="w-[35%] flex justify-end bg-slate-700/50">
                <img class="h-auto object-cover" :src="`/storage/avatars/${player.photo}`" alt="Avatar" />
            </div>
        </figure>

    </Modal>
</template>
