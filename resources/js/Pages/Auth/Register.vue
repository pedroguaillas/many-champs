<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Crear cuenta" />

    <AuthenticationCard title="Crear cuenta" description="Regístrate para organizar tu campeonato.">
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Nombre" />
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1.5 block w-full py-3"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Correo electrónico" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1.5 block w-full py-3"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Contraseña" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1.5 block w-full py-3"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Repite la contraseña" />
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1.5 block w-full py-3"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="mt-4">
                <InputLabel for="terms">
                    <div class="flex items-center">
                        <Checkbox id="terms" v-model:checked="form.terms" name="terms" required />

                        <div class="ml-2">
                            Acepto los <a target="_blank" :href="route('terms.show')" class="py-2 text-[13px] font-medium text-emerald-400 hover:text-emerald-300 rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40">Términos del servicio</a> y la <a target="_blank" :href="route('policy.show')" class="py-2 text-[13px] font-medium text-emerald-400 hover:text-emerald-300 rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40">Política de privacidad</a>
                        </div>
                    </div>
                    <InputError class="mt-2" :message="form.errors.terms" />
                </InputLabel>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 mt-5">
                <Link :href="route('login')" class="py-2 text-[13px] font-medium text-emerald-400 hover:text-emerald-300 rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40">
                    Ya tengo cuenta
                </Link>

                <PrimaryButton :disabled="form.processing">
                    Crear cuenta
                </PrimaryButton>
            </div>
        </form>
    </AuthenticationCard>
</template>
