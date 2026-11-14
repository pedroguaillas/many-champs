<script setup>
import { nextTick, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const recovery = ref(false);

const form = useForm({
    code: '',
    recovery_code: '',
});

const recoveryCodeInput = ref(null);
const codeInput = ref(null);

const toggleRecovery = async () => {
    recovery.value ^= true;

    await nextTick();

    if (recovery.value) {
        recoveryCodeInput.value.focus();
        form.code = '';
    } else {
        codeInput.value.focus();
        form.recovery_code = '';
    }
};

const submit = () => {
    form.post(route('two-factor.login'));
};
</script>

<template>
    <Head title="Verificación en dos pasos" />

    <AuthenticationCard title="Verificación en dos pasos">
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div class="mb-4 text-[13px] leading-relaxed text-slate-400">
            <template v-if="! recovery">
                Escribe el código de tu aplicación de autenticación.
            </template>

            <template v-else>
                Escribe uno de tus códigos de recuperación.
            </template>
        </div>

        <form @submit.prevent="submit">
            <div v-if="! recovery">
                <InputLabel for="code" value="Código" />
                <TextInput
                    id="code"
                    ref="codeInput"
                    v-model="form.code"
                    type="text"
                    inputmode="numeric"
                    class="mt-1.5 block w-full py-3"
                    autofocus
                    autocomplete="one-time-code"
                />
                <InputError class="mt-2" :message="form.errors.code" />
            </div>

            <div v-else>
                <InputLabel for="recovery_code" value="Código de recuperación" />
                <TextInput
                    id="recovery_code"
                    ref="recoveryCodeInput"
                    v-model="form.recovery_code"
                    type="text"
                    class="mt-1.5 block w-full py-3"
                    autocomplete="one-time-code"
                />
                <InputError class="mt-2" :message="form.errors.recovery_code" />
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 mt-5">
                <button type="button" class="py-2 text-left text-[13px] font-medium text-emerald-400 hover:text-emerald-300 rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40" @click.prevent="toggleRecovery">
                    <template v-if="! recovery">
                        Usar un código de recuperación
                    </template>

                    <template v-else>
                        Usar el código de la aplicación
                    </template>
                </button>

                <PrimaryButton :disabled="form.processing">
                    Ingresar
                </PrimaryButton>
            </div>
        </form>
    </AuthenticationCard>
</template>
