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
    toggle();
}

// Utilizado para eliminar un club
const form = useForm({ id: '' });

const deletePlayer = (payment) => {

    const alert = Swal.mixin({
        buttonsStyling: true
    });

    alert.fire({
        title: `¿Esta seguro eliminar el pago de ${payment.amount}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Si, Eliminar',
        cancelButtonText: '<i class="fa-solid fa-ban"></i> Cancelar'
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
        <div class="p-4 bg-white rounded drop-shadow-md">

            <!-- Card Header -->
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold">{{ `Pagos de ${club.name}` }}</h2>
                <button @click="newPayment" class="px-2 bg-green-500 text-2xl text-white rounded font-bold">
                    +
                </button>
            </div>

            <!-- Resposive -->
            <div class="w-full overflow-x-auto">
                <!-- Tabla -->
                <table class="mt-4 text-sm sm:text-xs table-auto w-full text-center text-gray-700">
                    <thead>
                        <tr class="[&>th]:py-2">
                            <th>N°</th>
                            <th>Monto</th>
                            <th>Nota</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="payment, i in payments" :key="payment.id" class="border-t [&>td]:py-2">
                            <td>{{ i + 1 }}</td>
                            <td>{{ payment.amount }}</td>
                            <td>{{ payment.note }}</td>
                            <td>
                                <div class="relative inline-flex [&>a>i]:text-white [&>button>i]:text-white">
                                    <button @click="edit(payment)" class="mx-1 rounded px-2 py-1 bg-blue-500">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button @click="deletePlayer(payment)" class="rounded px-2 py-1 bg-red-500">
                                        <i class="fa fa-trash"></i>
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
