<script setup>

// Imports
import { Link } from '@inertiajs/vue3';

// Props
defineProps({
    links: { type: Array, default: () => [] }
})

</script>

<template>
    <div v-if="links.length > 3">
        <div class="flex flex-wrap mt-2">
            <template v-for="(link, key) in links">
                <div v-if="link.url === null" :key="key"
                    class="px-2 sm:px-4 py-1 sm:py-3 text-gray-400 text-xs sm:text-sm leading-4 border rounded"
                    v-html="link.label" />
                <Link v-else :key="`link-${key}`"
                    class="px-2 sm:px-4 py-1 sm:py-3 text-xs sm:text-sm leading-4 rounded"
                    :class="{ 'bg-indigo-500 text-white': link.active }" :href="link.url" v-html="link.label" />
            </template>
        </div>
    </div>
</template>