<script setup>

// Imports
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ModalPayment from './ModalPayment.vue';
import { router, useForm } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

// Props
const props = defineProps({
    club: { type: Object, default: () => { } },
    payments: { type: Array, default: () => [] }
})

// Refs
const modal = ref(false);

const initialPayment = {
    amount: '',
    note: ''
}

// Reactive
const payment = reactive({ ...initialPayment });
const errors = reactive({ ...initialPayment });

const newPayment = () => {
    if (payment.id !== undefined) {
        delete payment.id
    }
    Object.assign(payment, initialPayment);
    toggle();
}

const resetError = () => {
    Object.assign(errors, initialPayment);
}

const toggle = () => {
    modal.value = !modal.value
}

const save = () => {

    if (payment.id === undefined) {
        const data = { ...payment, club_id: props.club.id }
        axios
            .post(route('payments.store'), data)
            .then(() => {
                toggle();
                resetError();

                router.reload({ only: ['payments'] });
            }).catch(error => {
                resetError();

                Object.keys(error.response.data.errors).forEach(key => {
                    errors[key] = error.response.data.errors[key][0]
                });
            })
    } else {
        axios
            .put(route('payments.update', payment.id), payment)
            .then(() => {
                toggle();
                resetError();

                router.reload({ only: ['payments'] });
            }).catch(error => {
                resetError();

                Object.keys(error.response.data.errors).forEach(key => {
                    errors[key] = error.response.data.errors[key][0]
                });
            })
    }
}

const edit = (paymentEdit) => {
    resetError();
    Object.keys(paymentEdit).forEach(key => {
        payment[key] = paymentEdit[key]
    });
    payment.amount = '' + parseFloat(paymentEdit.amount)
    toggle();
}

// Utilizado para eliminar un club
const form = useForm({ id: '' });

const deletePlayer = (payment) => {

    const alert = Swal.mixin({
        buttonsStyling: true
    });

    alert.fire({
        title: `¿Esta seguro eliminar el pago de $${payment.amount}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Si, Eliminar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar',
        background: '#1e293b',
        color: '#e2e8f0',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#334155',
    }).then((result) => {
        if (result.isConfirmed) {
            form.delete(route('payments.destroy', payment.id), {
                onFinish: () => router.reload({ only: ['payments'] })
            })
        }
    });
}

</script>

<template>
    <AdminLayout :title="`Pagos de ${club.name}`">

        <!-- Card -->
        <div class="rounded-xl bg-white dark:bg-slate-800/60 border border-gray-200 dark:border-white/[0.06] overflow-hidden">

            <!-- Card Header -->
            <div class="px-5 py-4 border-b border-gray-200 dark:border-white/[0.06] flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                        <i class="fas fa-money-bill-wave text-emerald-400 text-[11px]"></i>
                    </div>
                    <h2 class="text-sm font-semibold tracking-tight text-white">{{ `Pagos de ${club.name}` }}</h2>
                </div>
                <button @click="newPayment"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-emerald-500/10 text-emerald-400 text-xs font-medium border border-emerald-500/20 hover:bg-emerald-500/20 hover:border-emerald-500/30 transition-all duration-200">
                    <i class="fa-solid fa-plus text-[11px]"></i> Agregar
                </button>
            </div>

            <!-- Responsive -->
            <div class="w-full overflow-x-auto">
                <!-- Tabla -->
                <table class="w-full text-center">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">N°</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Monto</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500">Nota</th>
                            <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="payment, i in payments" :key="payment.id"
                            class="border-b border-gray-100 dark:border-white/[0.04] hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150">
                            <td class="px-5 py-3.5 text-[13px] text-gray-500 dark:text-slate-400">{{ i + 1 }}</td>
                            <td class="px-5 py-3.5 text-[13px] text-emerald-400 font-medium">${{ payment.amount }}</td>
                            <td class="px-5 py-3.5 text-[13px] text-gray-500 dark:text-slate-400">{{ payment.note }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1">
                                    <button @click="edit(payment)"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 dark:text-slate-500 hover:text-gray-700 dark:hover:text-gray-700 dark:text-slate-200 hover:bg-gray-200 dark:hover:bg-gray-200 dark:bg-slate-700/50 transition-colors duration-150">
                                        <i class="fa fa-edit text-[11px]"></i>
                                    </button>
                                    <button @click="deletePlayer(payment)"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 dark:text-slate-500 hover:text-red-400 hover:bg-red-500/10 transition-colors duration-150">
                                        <i class="fa fa-trash text-[11px]"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
    <ModalPayment :payment="payment" :errors="errors" :show="modal" @close="toggle" @save="save" />
</template>
