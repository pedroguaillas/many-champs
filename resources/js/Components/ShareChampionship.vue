<script setup>
import Sheet from '@/Components/Sheet.vue';
import ShareButton from '@/Components/ShareButton.vue';
import { router, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { computed, ref } from 'vue';

// Compartir el campeonato (o un partido) con quien no tiene cuenta.
// Compartir por primera vez lo hace público; aquí mismo se puede dejar de compartir.
const props = defineProps({
    // Con partido se comparte la página de ese partido; sin él, la del campeonato
    gameId: { type: Number, default: null },
    // Texto que acompaña al enlace
    text: { type: String, default: '' },
    label: { type: String, default: 'Compartir' },
});

const swalConfig = {
    background: '#1e293b',
    color: '#e2e8f0',
    confirmButtonColor: '#10b981',
    cancelButtonColor: '#334155',
};

const page = usePage()
const team = computed(() => page.props.auth?.user?.current_team ?? null)
// Solo el dueño del campeonato decide si es público
const canShare = computed(() => !!team.value && team.value.user_id === page.props.auth.user.id)
const isPublic = computed(() => !!team.value?.is_public && !!team.value?.slug)

const url = computed(() => {
    if (!isPublic.value) return ''
    return props.gameId
        ? route('public.game', [team.value.slug, props.gameId])
        : route('public.championship', team.value.slug)
})

const open = ref(false)
const working = ref(false)

const failed = () => Swal.fire({ title: 'No se pudo cambiar', text: 'Revisa tu conexión e inténtalo de nuevo.', icon: 'error', ...swalConfig })

const start = async () => {
    if (isPublic.value) return open.value = true

    const { isConfirmed } = await Swal.fire({
        title: '¿Compartir el campeonato?',
        html: 'Cualquiera con el enlace podrá ver los partidos, resultados, posiciones y goleadores, sin poder cambiar nada.<br><br>'
            + '<b>No se muestran</b> fotos, cédulas, teléfonos, pagos ni sanciones por cobrar.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, compartir',
        cancelButtonText: 'Cancelar',
        ...swalConfig,
    })
    if (!isConfirmed) return

    working.value = true
    router.post(route('championship.share'), {}, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => { open.value = true },
        onError: failed,
        onFinish: () => { working.value = false },
    })
}

const stop = async () => {
    const { isConfirmed } = await Swal.fire({
        title: '¿Dejar de compartir?',
        text: 'Los enlaces que ya enviaste dejarán de funcionar. Puedes volver a compartir cuando quieras y seguirán siendo los mismos.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, dejar de compartir',
        cancelButtonText: 'Cancelar',
        ...swalConfig,
        confirmButtonColor: '#ef4444',
    })
    if (!isConfirmed) return

    working.value = true
    router.delete(route('championship.unshare'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => { open.value = false },
        onError: failed,
        onFinish: () => { working.value = false },
    })
}
</script>

<template>
    <button v-if="canShare" type="button" @click="start" :disabled="working" v-bind="$attrs">
        <i class="fa-solid text-[12px]" :class="working ? 'fa-spinner fa-spin' : 'fa-share-nodes'" aria-hidden="true"></i>
        <span>{{ label }}</span>
    </button>

    <Sheet :show="open" :label="gameId ? 'Compartir partido' : 'Compartir campeonato'" @close="open = false">
        <div class="px-5 pt-3 sm:pt-5 pb-[calc(1.25rem+env(safe-area-inset-bottom,0px))] overflow-y-auto overscroll-contain">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-[15px] font-semibold tracking-tight text-gray-900 dark:text-white">{{ gameId ? 'Compartir partido' : 'Compartir campeonato' }}</h2>
                    <p class="mt-1 text-[13px] text-gray-500 dark:text-slate-400">
                        {{ gameId ? 'Quien abra el enlace verá el marcador, la cancha, los goles y las tarjetas en vivo.' : 'Quien abra el enlace verá partidos, resultados, posiciones y goleadores.' }}
                        No necesita cuenta y no puede cambiar nada.
                    </p>
                </div>
                <button type="button" @click="open = false" aria-label="Cerrar" class="btn-icon shrink-0 -mr-2 -mt-1">
                    <i class="fa-solid fa-xmark text-sm" aria-hidden="true"></i>
                </button>
            </div>

            <p class="mt-4 rounded-lg bg-gray-100 dark:bg-slate-900/80 border border-gray-200 dark:border-white/[0.08] px-3 py-2.5 text-[13px] text-gray-700 dark:text-slate-200 break-all select-all">{{ url }}</p>

            <div class="mt-3 grid grid-cols-2 gap-2">
                <ShareButton :url="url" :text="text" class="btn-primary justify-center py-3 text-sm" />
                <a :href="url" target="_blank" rel="noopener"
                    class="inline-flex items-center justify-center gap-2 py-3 rounded-lg border border-gray-200 dark:border-white/[0.08] text-sm font-medium text-gray-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-white/[0.04] active:scale-[0.97] transition duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40">
                    <i class="fa-solid fa-arrow-up-right-from-square text-[11px]" aria-hidden="true"></i> Ver página
                </a>
            </div>

            <p class="mt-4 text-[12px] text-gray-500 dark:text-slate-400">
                <i class="fa-solid fa-lock text-[10px] mr-1" aria-hidden="true"></i>
                No se muestran fotos, cédulas, teléfonos, pagos ni sanciones por cobrar.
            </p>

            <div class="mt-4 pt-3 border-t border-gray-200 dark:border-white/[0.06]">
                <button type="button" @click="stop" :disabled="working"
                    class="min-h-[40px] text-[13px] font-medium text-red-600 dark:text-red-400 hover:underline rounded disabled:opacity-40 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500/40">
                    Dejar de compartir el campeonato
                </button>
            </div>
        </div>
    </Sheet>
</template>

<script>
// El botón recibe las clases del padre; la hoja va por teleport
export default { inheritAttrs: false }
</script>
