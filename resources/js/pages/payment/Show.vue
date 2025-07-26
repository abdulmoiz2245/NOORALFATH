<template>
  <Head title="Payment Details" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6 p-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Payment #{{ payment.id }}</h1>
          <p class="text-muted-foreground">Payment details and information</p>
        </div>
        <div class="flex space-x-2">
          <Button variant="outline" as-child>
            <Link :href="route('payments.edit', payment.id)">
              <Edit class="w-4 h-4 mr-2" />
              Edit Payment
            </Link>
          </Button>
          <Button variant="destructive" @click="deletePayment">
            <Trash2 class="w-4 h-4 mr-2" />
            Delete Payment
          </Button>
        </div>
      </div>

      <div class="grid gap-6 md:grid-cols-2">
        <!-- Payment Information -->
        <Card>
          <CardHeader>
            <CardTitle>Payment Information</CardTitle>
            <CardDescription>Details about this payment</CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Payment ID</label>
              <p class="text-sm text-gray-900">#{{ payment.id }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Amount</label>
              <p class="text-lg font-semibold text-green-600">${{ formatCurrency(payment.amount) }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Payment Method</label>
              <Badge variant="secondary">
                {{ payment.payment_method.replace('_', ' ').toUpperCase() }}
              </Badge>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Payment Date</label>
              <p class="text-sm text-gray-900">{{ new Date(payment.payment_date).toLocaleDateString() }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Status</label>
              <Badge 
                :variant="payment.status === 'completed' ? 'default' : 
                         payment.status === 'pending' ? 'secondary' : 
                         'destructive'"
              >
                {{ payment.status.charAt(0).toUpperCase() + payment.status.slice(1) }}
              </Badge>
            </div>

            <div v-if="payment.notes">
              <label class="block text-sm font-medium text-gray-700">Notes</label>
              <p class="text-sm text-gray-900">{{ payment.notes }}</p>
            </div>

            <div v-if="payment.receipt_path">
              <label class="block text-sm font-medium text-gray-700">Payment Receipt</label>
              <div class="mt-1">
                <a 
                  :href="`/storage/${payment.receipt_path}`" 
                  target="_blank"
                  class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  View Receipt
                </a>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Related Information -->
        <Card>
          <CardHeader>
            <CardTitle>Related Information</CardTitle>
            <CardDescription>Invoice and schedule details</CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
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
                Amount: ${{ formatCurrency(payment.payment_schedule.amount) }}
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
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Edit, Trash2 } from 'lucide-vue-next';

interface Payment {
  id: number;
  amount: string | number;
  payment_method: string;
  payment_date: string;
  status: string;
  notes?: string;
  receipt_path?: string;
  created_at: string;
  updated_at: string;
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
    href: '#',
  },
];

const deletePayment = () => {
  if (confirm('Are you sure you want to delete this payment? This action cannot be undone.')) {
    router.delete(route('payments.destroy', props.payment.id));
  }
};
</script>
