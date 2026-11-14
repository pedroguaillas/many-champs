<script setup>
import { onUnmounted, ref } from 'vue';

// Comparte un enlace con el menú del teléfono (WhatsApp, etc.); donde no existe, lo copia.
const props = defineProps({
    url: { type: String, required: true },
    title: { type: String, default: '' },
    text: { type: String, default: '' },
    label: { type: String, default: 'Compartir' },
});

const copied = ref(false)
let timer = null

const copy = async () => {
    try {
        await navigator.clipboard.writeText(props.url)
    } catch {
        // Sin permiso de portapapeles: se muestra para copiarlo a mano
        window.prompt('Copia el enlace', props.url)
        return
    }
    copied.value = true
    clearTimeout(timer)
    timer = setTimeout(() => { copied.value = false }, 2000)
}

const share = async () => {
    if (!navigator.share) return copy()
    try {
        await navigator.share({ title: props.title, text: props.text, url: props.url })
    } catch (error) {
        // Cerrar el menú no es un error
        if (error?.name !== 'AbortError') copy()
    }
}

onUnmounted(() => clearTimeout(timer))
</script>

<template>
    <button type="button" @click="share">
        <i class="fa-solid text-[12px]" :class="copied ? 'fa-check' : 'fa-share-nodes'" aria-hidden="true"></i>
        <span aria-live="polite">{{ copied ? 'Enlace copiado' : label }}</span>
    </button>
</template>
