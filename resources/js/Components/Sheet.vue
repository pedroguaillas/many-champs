<script setup>
import { nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

// Bottom sheet en móvil, diálogo centrado desde sm
const props = defineProps({
    show: { type: Boolean, default: false },
    label: { type: String, default: '' },
    maxWidth: { type: String, default: 'sm:max-w-md' },
});

const emit = defineEmits(['close', 'opened'])

const panel = ref(null)
let returnFocus = null

const focusable = () => panel.value
    ? [...panel.value.querySelectorAll('button:not([disabled]), a[href], input:not([disabled]), select, textarea, [tabindex]:not([tabindex="-1"])')]
    : []

const onKeydown = (e) => {
    if (!props.show) return
    if (e.key === 'Escape') emit('close')
    // El foco no sale del diálogo
    if (e.key === 'Tab') {
        const items = focusable()
        if (!items.length) return e.preventDefault()
        const first = items[0], last = items[items.length - 1]
        if (!panel.value.contains(document.activeElement)) { e.preventDefault(); first.focus() }
        else if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus() }
        else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus() }
    }
}

watch(() => props.show, async (show) => {
    document.body.style.overflow = show ? 'hidden' : null
    if (show) {
        returnFocus = document.activeElement
        await nextTick()
        panel.value?.focus({ preventScroll: true })
        emit('opened')
    } else {
        // Devuelve el foco a quien abrió el diálogo
        if (returnFocus?.isConnected) returnFocus.focus({ preventScroll: true })
        returnFocus = null
    }
})

onMounted(() => document.addEventListener('keydown', onKeydown))
onUnmounted(() => {
    document.removeEventListener('keydown', onKeydown)
    document.body.style.overflow = null
})
</script>

<template>
    <teleport to="body">
        <transition enter-active-class="duration-300 ease-out" enter-from-class="opacity-0"
            leave-active-class="duration-200 ease-out" leave-to-class="opacity-0">
            <div v-if="show" class="fixed inset-0 z-50 bg-gray-900/50 dark:bg-slate-950/80 transition-opacity" @click="emit('close')" />
        </transition>

        <transition enter-active-class="duration-300 ease-out"
            enter-from-class="translate-y-full sm:translate-y-2 sm:opacity-0"
            leave-active-class="duration-200 ease-out"
            leave-to-class="translate-y-full sm:translate-y-2 sm:opacity-0">
            <div v-if="show" ref="panel" role="dialog" aria-modal="true" :aria-label="label" tabindex="-1"
                class="fixed z-50 inset-x-0 bottom-0 sm:inset-x-auto sm:bottom-auto sm:left-1/2 sm:top-[10vh] sm:-translate-x-1/2 sm:w-full
                       flex flex-col max-h-[88dvh] sm:max-h-[80vh]
                       bg-white dark:bg-slate-800 border border-gray-200 dark:border-white/[0.06]
                       rounded-t-xl sm:rounded-xl shadow-xl transform transition focus:outline-none"
                :class="maxWidth">
                <div class="sm:hidden flex justify-center pt-2.5 pb-1 shrink-0" aria-hidden="true">
                    <span class="h-1 w-10 rounded-full bg-gray-300 dark:bg-slate-600"></span>
                </div>
                <slot />
            </div>
        </transition>
    </teleport>
</template>
