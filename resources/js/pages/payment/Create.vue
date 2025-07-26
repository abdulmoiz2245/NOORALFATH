<template>
  <Head title="Record Payment" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6 p-6">
      <!-- Payment Schedule Information -->
      <Card>
        <CardHeader>
          <CardTitle>Payment Schedule Details</CardTitle>
          <CardDescription>Recording payment for schedule #{{ schedule.id }}</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="font-semibold tracking-tight text-base">Invoice</label>
              <p class="tracking-tight text-sm font-normal">#{{ schedule.invoice.invoice_number }}</p>
            </div>
            <div>
              <label class="font-semibold tracking-tight text-base">Due Date</label>
              <p class="tracking-tight text-sm font-normal">{{ new Date(schedule.due_date).toLocaleDateString() }}</p>
            </div>
            <div>
              <label class="font-semibold tracking-tight text-base">Schedule Amount</label>
              <p class="tracking-tight text-sm font-normal">${{ formatCurrency(schedule.amount) }}</p>
            </div>
            <div>
              <label class="font-semibold tracking-tight text-base">Already Paid</label>
              <p class="tracking-tight text-sm font-normal">${{ formatCurrency(paidAmount) }}</p>
            </div>
            <div>
              <label class="font-semibold tracking-tight text-base">Remaining Amount</label>
              <p class="tracking-tight text-sm font-semibold text-green-600">${{ formatCurrency(remainingAmount) }}</p>
            </div>
            <div>
              <label class="font-semibold tracking-tight text-base">Status</label>
              <Badge 
                :variant="schedule.status === 'pending' ? 'secondary' : 
                         schedule.status === 'partial' ? 'default' : 
                         'outline'"
              >
                {{ schedule.status.charAt(0).toUpperCase() + schedule.status.slice(1) }}
              </Badge>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Payment Form -->
      <Card>
        <CardHeader>
          <CardTitle>Record Payment</CardTitle>
          <CardDescription>Enter payment details for this schedule</CardDescription>
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
                  :max="parseFloat(remainingAmount.toString())"
                  required
                  autofocus
                />
                <p class="mt-1 text-sm text-gray-500">Maximum: ${{ formatCurrency(remainingAmount) }}</p>
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
                <Label for="receipt">
                  <Upload class="w-4 h-4 inline mr-2" />
                  Payment Receipt
                </Label>
                <Input
                  id="receipt"
                  type="file"
                  class="mt-1"
                  accept=".pdf,.jpg,.jpeg,.png"
                  @change="handleFileChange"
                />
                <p class="mt-1 text-sm text-gray-500">
                  Upload receipt (PDF, JPG, PNG) - Max 5MB
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
                <Link :href="route('invoices.show', schedule.invoice.id)">
                  Cancel
                </Link>
              </Button>
              <Button type="submit" :disabled="form.processing">
                {{ form.processing ? 'Recording...' : 'Record Payment' }}
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
import { Badge } from '@/components/ui/badge';
import { Upload } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';

interface Schedule {
  id: number;
  amount: string | number;
  due_date: string;
  status: string;
  invoice: {
    id: number;
    invoice_number: string;
  };
}

interface Props {
  schedule: Schedule;
  remainingAmount: string | number;
  paidAmount: string | number;
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
  amount: parseFloat(props.remainingAmount.toString()),
  payment_method: '',
  payment_date: new Date().toISOString().split('T')[0],
  notes: '',
  receipt: null as File | null
});

const formatCurrency = (amount: string | number): string => {
  const value = typeof amount === 'string' ? parseFloat(amount) : amount;
  return isNaN(value) ? '0.00' : value.toFixed(2);
};

const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0] || null;
  form.receipt = file;
};

const submit = () => {
  form.post(route('payments.store'), {
    onFinish: () => form.reset('amount', 'payment_method', 'notes', 'receipt'),
  });
};
</script>
