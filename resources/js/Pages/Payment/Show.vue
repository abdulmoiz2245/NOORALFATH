<template>
  <Head title="Payment Details" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Payment #{{ payment.id }}
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            
            <!-- Back Button -->
            <div class="mb-6">
              <Link
                :href="route('invoices.show', payment.invoice.id)"
                class="text-indigo-600 hover:text-indigo-900"
              >
                ← Back to Invoice
              </Link>
            </div>

            <!-- Payment Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-4">
                <h3 class="text-lg font-medium text-gray-900">Payment Information</h3>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700">Payment ID</label>
                  <p class="text-sm text-gray-900">#{{ payment.id }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Amount</label>
                  <p class="text-lg font-semibold text-green-600">${{ payment.amount.toFixed(2) }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ payment.payment_method.replace('_', ' ').toUpperCase() }}
                  </span>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Payment Date</label>
                  <p class="text-sm text-gray-900">{{ new Date(payment.payment_date).toLocaleDateString() }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Status</label>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        :class="{
                          'bg-green-100 text-green-800': payment.status === 'completed',
                          'bg-yellow-100 text-yellow-800': payment.status === 'pending',
                          'bg-red-100 text-red-800': payment.status === 'failed'
                        }">
                    {{ payment.status.charAt(0).toUpperCase() + payment.status.slice(1) }}
                  </span>
                </div>

                <div v-if="payment.notes">
                  <label class="block text-sm font-medium text-gray-700">Notes</label>
                  <p class="text-sm text-gray-900">{{ payment.notes }}</p>
                </div>
              </div>

              <div class="space-y-4">
                <h3 class="text-lg font-medium text-gray-900">Related Information</h3>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700">Invoice</label>
                  <Link 
                    :href="route('invoices.show', payment.invoice.id)"
                    class="text-indigo-600 hover:text-indigo-900"
                  >
                    #{{ payment.invoice.invoice_number }}
                  </Link>
                </div>

                <div v-if="payment.payment_schedule">
                  <label class="block text-sm font-medium text-gray-700">Payment Schedule</label>
                  <p class="text-sm text-gray-900">Schedule #{{ payment.payment_schedule.id }}</p>
                  <p class="text-sm text-gray-500">
                    Amount: ${{ payment.payment_schedule.amount.toFixed(2) }}
                  </p>
                  <p class="text-sm text-gray-500">
                    Due: {{ new Date(payment.payment_schedule.due_date).toLocaleDateString() }}
                  </p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Created</label>
                  <p class="text-sm text-gray-900">{{ new Date(payment.created_at).toLocaleString() }}</p>
                </div>

                <div v-if="payment.updated_at !== payment.created_at">
                  <label class="block text-sm font-medium text-gray-700">Last Updated</label>
                  <p class="text-sm text-gray-900">{{ new Date(payment.updated_at).toLocaleString() }}</p>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end mt-8 space-x-3">
              <Link
                :href="route('payments.edit', payment.id)"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
              >
                Edit Payment
              </Link>
              <button
                @click="deletePayment"
                class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
              >
                Delete Payment
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface Payment {
  id: number;
  amount: number;
  payment_method: string;
  payment_date: string;
  status: string;
  notes?: string;
  created_at: string;
  updated_at: string;
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

const deletePayment = () => {
  if (confirm('Are you sure you want to delete this payment? This action cannot be undone.')) {
    router.delete(route('payments.destroy', props.payment.id));
  }
};
</script>
