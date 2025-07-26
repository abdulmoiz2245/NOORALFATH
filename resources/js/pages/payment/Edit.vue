<template>
  <Head title="Edit Payment" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6 p-6">
      <!-- Payment Context Information -->
      <Card>
        <CardHeader>
          <CardTitle>Payment Context</CardTitle>
          <CardDescription>Details about the payment being edited</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Payment ID</label>
              <p class="text-sm text-gray-900">#{{ payment.id }}</p>
            </div>
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
              <p class="text-sm text-gray-900">${{ formatCurrency(payment.payment_schedule.amount) }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Edit Form -->
      <Card>
        <CardHeader>
          <CardTitle>Edit Payment Details</CardTitle>
          <CardDescription>Update payment information</CardDescription>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="submit" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <Label for="amount">Payment Amount *</Label>
                <Input
                  id="amount"
                  type="number"
                  step="0.01"
                  class="mt-1"
                  v-model="form.amount"
                  required
                  autofocus
                />
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
                <Label for="current_receipt">Current Receipt</Label>
                <div v-if="payment.receipt_path" class="mt-1">
                  <a 
                    :href="`/storage/${payment.receipt_path}`" 
                    target="_blank"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    View Current Receipt
                  </a>
                </div>
                <p v-else class="mt-1 text-sm text-gray-500">No receipt uploaded</p>
              </div>

              <div>
                <Label for="receipt">Upload New Receipt</Label>
                <Input
                  id="receipt"
                  type="file"
                  class="mt-1"
                  accept=".pdf,.jpg,.jpeg,.png"
                  @change="handleFileChange"
                />
                <p class="mt-1 text-sm text-gray-500">
                  Upload new receipt (PDF, JPG, PNG) - Max 5MB (Optional)
                </p>
                <InputError class="mt-2" :message="form.errors.receipt" />
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

            <div class="flex items-center justify-end space-x-3">
              <Button variant="outline" as-child>
                <Link :href="route('payments.show', payment.id)">
                  Cancel
                </Link>
              </Button>
              <Button type="submit" :disabled="form.processing">
                {{ form.processing ? 'Updating...' : 'Update Payment' }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/InputError.vue';

interface Payment {
  id: number;
  amount: string | number;
  payment_method: string;
  payment_date: string;
  status: string;
  notes?: string;
  receipt_path?: string;
  invoice: {
    id: number;
    invoice_number: string;
  };
  payment_schedule?: {
    id: number;
    amount: string | number;
    due_date: string;
  };
}

interface Props {
  payment: Payment;
}

const props = defineProps<Props>();

// Utility function to format currency
const formatCurrency = (amount: string | number): string => {
  const numAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
  return numAmount.toFixed(2);
};

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'Payments',
    href: '/payments',
  },
  {
    title: `Payment #${props.payment.id}`,
    href: `/payments/${props.payment.id}`,
  },
  {
    title: 'Edit',
    href: '#',
  },
];

const form = useForm({
  amount: typeof props.payment.amount === 'string' ? parseFloat(props.payment.amount) : props.payment.amount,
  payment_method: props.payment.payment_method,
  payment_date: props.payment.payment_date,
  notes: props.payment.notes || '',
  receipt: null as File | null
});

const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0] || null;
  form.receipt = file;
};

const submit = () => {
  form.put(route('payments.update', props.payment.id), {
    onFinish: () => form.reset(),
  });
};
</script>
