<script setup>
import { computed } from 'vue';

const emit = defineEmits(['update:checked']);

const props = defineProps({
    checked: {
        type: [Array, Boolean],
        default: false,
    },
    value: {
        type: String,
        default: null,
    },
});

const proxyChecked = computed({
    get() {
        return props.checked;
    },

    set(val) {
        emit('update:checked', val);
    },
});
</script>


<template>
    <input
        v-model="proxyChecked"
        type="checkbox"
        :value="value"
        class="w-[18px] h-[18px] rounded bg-white dark:bg-slate-900 border-gray-300 dark:border-white/[0.15] text-emerald-500 focus:ring-emerald-500/40 focus:ring-offset-0"
    >
</template>
