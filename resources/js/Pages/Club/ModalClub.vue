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

                    <TextInput v-model="club.address" type="text" class="mt-2 block w-full" placeholder="Comunidad"
                        minlength="3" maxlength="50" />
                    <InputError :message="error.address" class="mt-2" />

                    <label :for="'Grupo'" class="mt-4 inline-block">Grupo</label>
                    <select v-if="groups.length > 0" v-model="club.group_id"
                        class="mt-2 block w-full rounded border-gray-300">
                        <option v-for="group in groups" :value="group.id">{{ group.name }}</option>
                    </select>
                    <InputError :message="error.group_id" class="mt-2" />
                </form>
            </div>
        </template>
        <template #footer>
            <button @click="$emit('save')" class="px-6 py-2 ml-2 bg-blue-600 text-blue-100 rounded">Guardar</button>
        </template>
    </DialogModal>
</template>