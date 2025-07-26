<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { DollarSign, Download, Calendar, CreditCard, TrendingUp, Hash } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    payments: Array<{
        id: number;
        invoice_number: string;
        client_name: string;
        client_id: number;
        amount: number;
        payment_date: string;
        payment_method: string;
        reference_number: string;
        status: string;
        notes: string;
    }>;
    stats: {
        total_payments: number;
        total_amount: number;
        average_payment: number;
        this_month_total: number;
    };
    clients: Array<{
        id: number;
        name: string;
    }>;
    payment_methods: string[];
    filters: {
        date_from?: string;
        date_to?: string;
        client_id?: number;
        payment_method?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Reports', href: '/reports' },
    { title: 'Payment Reports', href: '/reports/payments' },
];

const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');
const clientId = ref(props.filters.client_id?.toString() || '');
const paymentMethod = ref(props.filters.payment_method || '');

const applyFilters = () => {
    router.get('/reports/payments', {
        date_from: dateFrom.value,
        date_to: dateTo.value,
        client_id: clientId.value,
        payment_method: paymentMethod.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const exportData = (format: string) => {
    const params = new URLSearchParams({
        export: format,
        date_from: dateFrom.value,
        date_to: dateTo.value,
        client_id: clientId.value,
        payment_method: paymentMethod.value,
    });
    
    window.location.href = `/reports/payments?${params.toString()}`;
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString();
};

const getStatusBadge = (status: string) => {
    switch (status.toLowerCase()) {
        case 'completed':
        case 'confirmed':
            return 'bg-green-100 text-green-800';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'failed':
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const getMethodBadge = (method: string) => {
    switch (method.toLowerCase()) {
        case 'cash':
            return 'bg-green-100 text-green-800';
        case 'credit_card':
        case 'card':
            return 'bg-blue-100 text-blue-800';
        case 'bank_transfer':
            return 'bg-purple-100 text-purple-800';
        case 'check':
            return 'bg-orange-100 text-orange-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};
</script>

<template>
    <Head title="Payment Reports" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Payment Reports</h1>
                    <p class="text-muted-foreground">Track payments received and analyze payment patterns</p>
                </div>
                <div class="flex gap-2">
                    <Button @click="exportData('csv')" variant="outline">
                        <Download class="w-4 h-4 mr-2" />
                        Export CSV
                    </Button>
                    <Button @click="exportData('excel')" variant="outline">
                        <Download class="w-4 h-4 mr-2" />
                        Export Excel
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Payments</CardTitle>
                        <Hash class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_payments }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Amount</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(stats.total_amount) }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Average Payment</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(stats.average_payment) }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">This Month</CardTitle>
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(stats.this_month_total) }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Calendar class="w-5 h-5" />
                        Payment Filters
                    </CardTitle>
                    <CardDescription>Filter payments by date, client, and payment method</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-4 items-end">
                        <div>
                            <label class="text-sm font-medium">From Date</label>
                            <Input 
                                v-model="dateFrom"
                                type="date"
                                class="mt-1"
                            />
                        </div>
                        <div>
                            <label class="text-sm font-medium">To Date</label>
                            <Input 
                                v-model="dateTo"
                                type="date"
                                class="mt-1"
                            />
                        </div>
                        <div>
                            <label class="text-sm font-medium">Client</label>
                            <Select v-model="clientId">
                                <SelectTrigger class="mt-1">
                                    <SelectValue placeholder="All clients" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All clients</SelectItem>
                                    <SelectItem 
                                        v-for="client in clients" 
                                        :key="client.id" 
                                        :value="client.id.toString()"
                                    >
                                        {{ client.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div>
                            <label class="text-sm font-medium">Payment Method</label>
                            <Select v-model="paymentMethod">
                                <SelectTrigger class="mt-1">
                                    <SelectValue placeholder="All methods" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All methods</SelectItem>
                                    <SelectItem 
                                        v-for="method in payment_methods" 
                                        :key="method" 
                                        :value="method"
                                    >
                                        {{ method.replace('_', ' ').toUpperCase() }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <Button @click="applyFilters" class="col-span-full md:col-span-1">
                            Apply Filters
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Payments Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Payment History</CardTitle>
                    <CardDescription>Detailed payment records and transaction information</CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Invoice</TableHead>
                                <TableHead>Client</TableHead>
                                <TableHead>Amount</TableHead>
                                <TableHead>Payment Date</TableHead>
                                <TableHead>Method</TableHead>
                                <TableHead>Reference</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Notes</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="payment in payments" :key="payment.id">
                                <TableCell class="font-medium">{{ payment.invoice_number }}</TableCell>
                                <TableCell>{{ payment.client_name }}</TableCell>
                                <TableCell>{{ formatCurrency(payment.amount) }}</TableCell>
                                <TableCell>{{ formatDate(payment.payment_date) }}</TableCell>
                                <TableCell>
                                    <Badge :class="getMethodBadge(payment.payment_method)">
                                        {{ payment.payment_method.replace('_', ' ').toUpperCase() }}
                                    </Badge>
                                </TableCell>
                                <TableCell>{{ payment.reference_number || 'N/A' }}</TableCell>
                                <TableCell>
                                    <Badge :class="getStatusBadge(payment.status)">
                                        {{ payment.status.toUpperCase() }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="max-w-xs truncate" :title="payment.notes">
                                        {{ payment.notes || 'N/A' }}
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
