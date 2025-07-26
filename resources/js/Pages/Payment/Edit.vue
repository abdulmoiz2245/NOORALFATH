<template>
  <Head title="Edit Payment" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Edit Payment #{{ payment.id }}
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            
            <!-- Back Button -->
            <div class="mb-6">
              <Link
                :href="route('payments.show', payment.id)"
                class="text-indigo-600 hover:text-indigo-900"
              >
                ← Back to Payment Details
              </Link>
            </div>

            <!-- Payment Context Information -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
              <h3 class="text-lg font-medium text-gray-900 mb-2">Payment Context</h3>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Invoice</label>
                  <p class="text-sm text-gray-900">#{{ payment.invoice.invoice_number }}</p>
                </div>
                <div v-if="payment.payment_schedule">
                  <label class="block text-sm font-medium text-gray-700">Payment Schedule</label>
                  <p class="text-sm text-gray-900">Schedule #{{ payment.payment_schedule.id }}</p>
                </div>
                <div v-if="payment.payment_schedule">
                  <label class="block text-sm font-medium text-gray-700">Schedule Amount</label>
                  <p class="text-sm text-gray-900">${{ payment.payment_schedule.amount.toFixed(2) }}</p>
                </div>
              </div>
            </div>

            <!-- Edit Form -->
            <form @submit.prevent="submit">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <InputLabel for="amount" value="Payment Amount *" />
                  <TextInput
                    id="amount"
                    type="number"
                    step="0.01"
                    class="mt-1 block w-full"
                    v-model="form.amount"
                    required
                    autofocus
                  />
                  <InputError class="mt-2" :message="form.errors.amount" />
                </div>

                <div>
                  <InputLabel for="payment_method" value="Payment Method *" />
                  <select
                    id="payment_method"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    v-model="form.payment_method"
                    required
                  >
                    <option value="">Select payment method</option>
                    <option value="cash">Cash</option>
                    <option value="check">Check</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="credit_card">Credit Card</option>
                    <option value="online">Online Payment</option>
                    <option value="other">Other</option>
                  </select>
                  <InputError class="mt-2" :message="form.errors.payment_method" />
                </div>

                <div>
                  <InputLabel for="payment_date" value="Payment Date *" />
                  <TextInput
                    id="payment_date"
                    type="date"
                    class="mt-1 block w-full"
                    v-model="form.payment_date"
                    required
                  />
                  <InputError class="mt-2" :message="form.errors.payment_date" />
                </div>

                <div>
                  <InputLabel for="notes" value="Notes" />
                  <textarea
                    id="notes"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    rows="3"
                    v-model="form.notes"
                    placeholder="Optional payment notes..."
                  ></textarea>
                  <InputError class="mt-2" :message="form.errors.notes" />
                </div>
              </div>

              <div class="flex items-center justify-end mt-6 space-x-3">
                <Link
                  :href="route('payments.show', payment.id)"
                  class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                >
                  Cancel
                </Link>
                <PrimaryButton class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                  Update Payment
                </PrimaryButton>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

interface Payment {
  id: number;
  amount: number;
  payment_method: string;
  payment_date: string;
  status: string;
  notes?: string;
  invoice: {
    id: number;
    invoice_number: string;
  };
  payment_schedule?: {
    id: number;
    amount: number;
    due_date: string;
  };
}

interface Props {
  payment: Payment;
}

const props = defineProps<Props>();

const form = useForm({
  amount: props.payment.amount,
  payment_method: props.payment.payment_method,
  payment_date: props.payment.payment_date,
  notes: props.payment.notes || ''
});

const submit = () => {
  form.put(route('payments.update', props.payment.id), {
    onFinish: () => form.reset(),
  });
};
</script>
