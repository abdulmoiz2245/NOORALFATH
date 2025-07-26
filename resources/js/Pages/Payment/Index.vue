<template>
  <Head title="Payments" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6 p-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Payments</h1>
          <p class="text-muted-foreground">Manage all payment records</p>
        </div>
      </div>

      <!-- Payments Table -->
      <Card>
        <CardHeader>
          <CardTitle>Payment History</CardTitle>
          <CardDescription>All payments received against payment schedules</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Payment Details
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Invoice
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Amount
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Method
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Date
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th scope="col" class="relative px-6 py-3">
                    <span class="sr-only">Actions</span>
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="payment in payments.data" :key="payment.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">Payment #{{ payment.id }}</div>
                    <div v-if="payment.payment_schedule" class="text-sm text-gray-500">
                      Schedule #{{ payment.payment_schedule.id }}
                    </div>
                    <div v-if="payment.notes" class="text-sm text-gray-500 mt-1">
                      {{ payment.notes }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Link 
                      :href="route('invoices.show', payment.invoice.id)"
                      class="text-indigo-600 hover:text-indigo-900"
                    >
                      #{{ payment.invoice.invoice_number }}
                    </Link>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${{ payment.amount.toFixed(2) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Badge variant="secondary">
                      {{ payment.payment_method.replace('_', ' ').toUpperCase() }}
                    </Badge>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ new Date(payment.payment_date).toLocaleDateString() }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Badge 
                      :variant="payment.status === 'completed' ? 'default' : 
                               payment.status === 'pending' ? 'secondary' : 
                               'destructive'"
                    >
                      {{ payment.status.charAt(0).toUpperCase() + payment.status.slice(1) }}
                    </Badge>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <DropdownMenu>
                      <DropdownMenuTrigger as-child>
                        <Button variant="ghost" class="h-8 w-8 p-0">
                          <span class="sr-only">Open menu</span>
                          <MoreHorizontal class="h-4 w-4" />
                        </Button>
                      </DropdownMenuTrigger>
                      <DropdownMenuContent align="end">
                        <DropdownMenuItem as-child>
                          <Link :href="route('payments.show', payment.id)">
                            <Eye class="mr-2 h-4 w-4" />
                            View
                          </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem as-child>
                          <Link :href="route('payments.edit', payment.id)">
                            <Edit class="mr-2 h-4 w-4" />
                            Edit
                          </Link>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem
                          @click="deletePayment(payment.id)"
                          class="text-red-600"
                        >
                          <Trash2 class="mr-2 h-4 w-4" />
                          Delete
                        </DropdownMenuItem>
                      </DropdownMenuContent>
                    </DropdownMenu>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="payments.links.length > 3" class="mt-6">
            <nav class="flex items-center justify-between">
              <div class="flex-1 flex justify-between sm:hidden">
                <Link
                  v-if="payments.prev_page_url"
                  :href="payments.prev_page_url"
                  class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                >
                  Previous
                </Link>
                <Link
                  v-if="payments.next_page_url"
                  :href="payments.next_page_url"
                  class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                >
                  Next
                </Link>
              </div>
              <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                  <p class="text-sm text-gray-700">
                    Showing
                    <span class="font-medium">{{ payments.from }}</span>
                    to
                    <span class="font-medium">{{ payments.to }}</span>
                    of
                    <span class="font-medium">{{ payments.total }}</span>
                    results
                  </p>
                </div>
                <div>
                  <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    <Link
                      v-for="link in payments.links"
                      :key="link.label"
                      :href="link.url"
                      v-html="link.label"
                      class="relative inline-flex items-center px-2 py-2 border text-sm font-medium"
                      :class="{
                        'z-10 bg-indigo-50 border-indigo-500 text-indigo-600': link.active,
                        'bg-white border-gray-300 text-gray-500 hover:bg-gray-50': !link.active,
                        'cursor-not-allowed opacity-50': !link.url
                      }"
                    />
                  </nav>
                </div>
              </div>
            </nav>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DropdownMenu from '@/Components/DropdownMenu.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

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
  };
}

interface PaginatedPayments {
  data: Payment[];
  links: Array<{ label: string; url: string | null; active: boolean }>;
  from: number;
  to: number;
  total: number;
  prev_page_url: string | null;
  next_page_url: string | null;
}

interface Props {
  payments: PaginatedPayments;
}

defineProps<Props>();

const deletePayment = (id: number) => {
  if (confirm('Are you sure you want to delete this payment?')) {
    router.delete(route('payments.destroy', id));
  }
};
</script>
