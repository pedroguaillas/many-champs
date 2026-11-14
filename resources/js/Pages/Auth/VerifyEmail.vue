<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    status: String,
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <Head title="Verifica tu correo" />

    <AuthenticationCard title="Verifica tu correo">
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div class="mb-4 text-[13px] leading-relaxed text-slate-400">
            Te enviamos un enlace a tu correo. Ábrelo para activar tu cuenta. Si no llegó, revisa la carpeta de spam o pide otro.
        </div>

        <div v-if="verificationLinkSent" role="status" class="mb-4 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-3 py-2.5 text-[13px] text-emerald-300">
            Enviamos un enlace nuevo a tu correo.
        </div>

        <form @submit.prevent="submit">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <PrimaryButton :disabled="form.processing">
                    Reenviar enlace
                </PrimaryButton>

                <div>
                    <Link
                        :href="route('profile.show')"
                        class="py-2 text-[13px] font-medium text-emerald-400 hover:text-emerald-300 rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40"
                    >
                        Editar perfil</Link>

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="py-2 text-[13px] font-medium text-emerald-400 hover:text-emerald-300 rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40 ml-2"
                    >
                        Cerrar sesión
                    </Link>
                </div>
            </div>
        </form>
    </AuthenticationCard>
</template>
