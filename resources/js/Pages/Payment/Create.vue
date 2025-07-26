<template>
  <Head title="Record Payment" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6 p-6">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
          <!-- Schedule Information -->
          <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Payment Schedule Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Invoice</label>
                <p class="text-sm text-gray-900">#{{ schedule.invoice.invoice_number }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Due Date</label>
                <p class="text-sm text-gray-900">{{ new Date(schedule.due_date).toLocaleDateString() }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Schedule Amount</label>
                <p class="text-sm text-gray-900">${{ schedule.amount.toFixed(2) }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Already Paid</label>
                <p class="text-sm text-gray-900">${{ paidAmount.toFixed(2) }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Remaining Amount</label>
                <p class="text-sm font-semibold text-green-600">${{ remainingAmount.toFixed(2) }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="{
                        'bg-yellow-100 text-yellow-800': schedule.status === 'pending',
                        'bg-blue-100 text-blue-800': schedule.status === 'partial',
                        'bg-green-100 text-green-800': schedule.status === 'paid'
                      }">
                  {{ schedule.status.charAt(0).toUpperCase() + schedule.status.slice(1) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Payment Form -->
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <Label for="amount">Payment Amount *</Label>
                <Input
                  id="amount"
                  type="number"
                  step="0.01"
                  class="mt-1"
                  v-model="form.amount"
                  :max="remainingAmount"
                  required
                  autofocus
                />
                <p class="mt-1 text-sm text-gray-500">Maximum: ${{ remainingAmount.toFixed(2) }}</p>
                <InputError class="mt-2" :message="form.errors.amount" />
              </div>

              <div>
                <Label for="payment_method">Payment Method *</Label>
                <Select v-model="form.payment_method" required>
                  <SelectTrigger class="mt-1">
                    <SelectValue placeholder="Select payment method" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="cash">Cash</SelectItem>
                    <SelectItem value="check">Check</SelectItem>
                    <SelectItem value="bank_transfer">Bank Transfer</SelectItem>
                    <SelectItem value="credit_card">Credit Card</SelectItem>
                    <SelectItem value="online">Online Payment</SelectItem>
                    <SelectItem value="other">Other</SelectItem>
                  </SelectContent>
                </Select>
                <InputError class="mt-2" :message="form.errors.payment_method" />
              </div>

              <div>
                <Label for="payment_date">Payment Date *</Label>
                <Input
                  id="payment_date"
                  type="date"
                  class="mt-1"
                  v-model="form.payment_date"
                  required
                />
                <InputError class="mt-2" :message="form.errors.payment_date" />
              </div>

              <div>
                <Label for="notes">Notes</Label>
                <Textarea
                  id="notes"
                  class="mt-1"
                  rows="3"
                  v-model="form.notes"
                  placeholder="Optional payment notes..."
                />
                <InputError class="mt-2" :message="form.errors.notes" />
              </div>
            </div>

            <div class="flex items-center justify-end mt-6 space-x-3">
              <Button variant="outline" as-child>
                <Link :href="route('invoices.show', schedule.invoice.id)">
                  Cancel
                </Link>
              </Button>
              <Button type="submit" :disabled="form.processing">
                {{ form.processing ? 'Recording...' : 'Record Payment' }}
              </Button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/InputError.vue';

interface Schedule {
  id: number;
  amount: number;
  due_date: string;
  status: string;
  invoice: {
    id: number;
    invoice_number: string;
  };
}

interface Props {
  schedule: Schedule;
  remainingAmount: number;
  paidAmount: number;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'Invoices',
    href: '/invoices',
  },
  {
    title: `Invoice ${props.schedule.invoice.invoice_number}`,
    href: `/invoices/${props.schedule.invoice.id}`,
  },
  {
    title: 'Record Payment',
    href: '#',
  },
];

const form = useForm({
  invoice_payment_schedule_id: props.schedule.id,
  amount: props.remainingAmount,
  payment_method: '',
  payment_date: new Date().toISOString().split('T')[0],
  notes: ''
});

const submit = () => {
  form.post(route('payments.store'), {
    onFinish: () => form.reset('amount', 'payment_method', 'notes'),
  });
};
</script>
