<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Recuperar contraseña" />

    <AuthenticationCard title="Recuperar contraseña">
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div class="mb-4 text-[13px] leading-relaxed text-slate-400">
            Escribe el correo de tu cuenta y te enviaremos un enlace para crear una contraseña nueva.
        </div>

        <div v-if="status" role="status" class="mb-4 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-3 py-2.5 text-[13px] text-emerald-300">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Correo electrónico" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1.5 block w-full py-3"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 mt-5">
                <Link :href="route('login')" class="py-2 text-[13px] font-medium text-slate-400 hover:text-slate-200 rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40"><i class="fa-solid fa-arrow-left text-[11px] mr-1.5" aria-hidden="true"></i>Volver a ingresar</Link>
                <PrimaryButton :disabled="form.processing">
                    Enviar enlace
                </PrimaryButton>
            </div>
        </form>
    </AuthenticationCard>
</template>
