<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Ingresar" />

    <AuthenticationCard title="Ingresar" description="Entra para gestionar tu campeonato.">
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <p v-if="status" role="status"
            class="mb-4 flex items-start gap-2 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-3 py-2.5 text-[13px] text-emerald-300">
            <i class="fa-solid fa-circle-check mt-0.5 text-xs" aria-hidden="true"></i>
            <span>{{ status }}</span>
        </p>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="email" value="Correo electrónico" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    inputmode="email"
                    class="mt-1.5 block w-full py-3"
                    placeholder="tucorreo@ejemplo.com"
                    required
                    autofocus
                    autocomplete="username"
                    autocapitalize="none"
                    autocorrect="off"
                    spellcheck="false"
                    enterkeyhint="next"
                    :aria-invalid="!!form.errors.email"
                />
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <div>
                <div class="flex items-center justify-between gap-3">
                    <InputLabel for="password" value="Contraseña" />
                    <Link v-if="canResetPassword" :href="route('password.request')"
                        class="-my-2 py-2 text-[12px] font-medium text-emerald-400 hover:text-emerald-300 rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40">
                        ¿La olvidaste?
                    </Link>
                </div>
                <div class="relative mt-1.5">
                    <TextInput
                        id="password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        class="block w-full py-3 pr-12"
                        required
                        autocomplete="current-password"
                        autocapitalize="none"
                        autocorrect="off"
                        spellcheck="false"
                        enterkeyhint="go"
                        :aria-invalid="!!form.errors.password"
                    />
                    <button type="button" @click="showPassword = !showPassword"
                        :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'" :aria-pressed="showPassword"
                        class="absolute inset-y-0 right-0 w-12 flex items-center justify-center rounded-r-lg text-slate-500 hover:text-slate-200 active:scale-[0.95] transition duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40">
                        <i class="fa-solid text-sm" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'" aria-hidden="true"></i>
                    </button>
                </div>
                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <label class="flex items-center gap-2.5 py-1 cursor-pointer select-none">
                <Checkbox v-model:checked="form.remember" name="remember" />
                <span class="text-[13px] text-slate-300">Mantener la sesión en este dispositivo</span>
            </label>

            <button type="submit" :disabled="form.processing" class="btn-primary w-full justify-center py-3.5 text-sm">
                <i v-if="form.processing" class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>
                {{ form.processing ? 'Ingresando…' : 'Ingresar' }}
            </button>
        </form>

        <template #footer>
            <p v-if="route().has('register')">
                ¿Aún no tienes cuenta?
                <Link :href="route('register')" class="inline-block py-2 font-medium text-emerald-400 hover:text-emerald-300 rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40">
                    Crear cuenta
                </Link>
            </p>
        </template>
    </AuthenticationCard>
</template>
