<script setup>
defineProps({
    value: String,
});
</script>

<template>
    <label class="block text-[13px] font-medium text-gray-700 dark:text-slate-300">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
