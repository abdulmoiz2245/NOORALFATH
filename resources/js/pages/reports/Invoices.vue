<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Filter, Download, Eye, Calendar, DollarSign, TrendingUp, Users, FileText } from 'lucide-vue-next';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

interface Invoice {
    id: number;
    invoice_number: string;
    client_name: string;
    client_id: number;
    issue_date: string;
    due_date: string;
    status: string;
    total_amount: number;
    amount_received: number;
    amount_remaining: number;
    payment_percentage: number;
    last_payment_date: string | null;
    payment_count: number;
    items_count: number;
}

interface Client {
    id: number;
    name: string;
}

interface Stats {
    total_invoices: number;
    total_invoice_amount: number;
    total_amount_received: number;
    total_amount_remaining: number;
    overall_collection_rate: number;
    fully_paid_invoices: number;
    partially_paid_invoices: number;
    unpaid_invoices: number;
}

interface Filters {
    date_from?: string;
    date_to?: string;
    client_id?: string;
    status?: string;
}

interface Props {
    invoices: Invoice[];
    stats: Stats;
    clients: Client[];
    statuses: string[];
    filters: Filters;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Reports',
        href: '/reports',
    },
    {
        title: 'Invoice Reports',
        href: '/reports/invoices',
    },
];

// Filter reactive variables
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');
const selectedClient = ref(props.filters.client_id || '');
const selectedStatus = ref(props.filters.status || '');

// Apply filters
const applyFilters = () => {
    const params = new URLSearchParams();
    
    if (dateFrom.value) params.append('date_from', dateFrom.value);
    if (dateTo.value) params.append('date_to', dateTo.value);
    if (selectedClient.value) params.append('client_id', selectedClient.value);
    if (selectedStatus.value) params.append('status', selectedStatus.value);
    
    const url = params.toString() ? `/reports/invoices?${params.toString()}` : '/reports/invoices';
    router.get(url);
};

// Clear filters
const clearFilters = () => {
    dateFrom.value = '';
    dateTo.value = '';
    selectedClient.value = '';
    selectedStatus.value = '';
    router.get('/reports/invoices');
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'AED'
    }).format(amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString();
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'paid': return 'bg-green-100 text-green-800';
        case 'pending': return 'bg-yellow-100 text-yellow-800';
        case 'overdue': return 'bg-red-100 text-red-800';
        case 'draft': return 'bg-gray-100 text-gray-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const getPaymentStatusColor = (percentage: number) => {
    if (percentage === 100) return 'bg-green-100 text-green-800';
    if (percentage > 0) return 'bg-blue-100 text-blue-800';
    return 'bg-red-100 text-red-800';
};

const getPaymentStatusText = (percentage: number) => {
    if (percentage === 100) return 'Fully Paid';
    if (percentage > 0) return 'Partially Paid';
    return 'Unpaid';
};

// Export functionality (placeholder)
const exportData = (format: string) => {
    const params = new URLSearchParams({
        export: format,
        date_from: dateFrom.value,
        date_to: dateTo.value,
        client_id: selectedClient.value,
        status: selectedStatus.value,
    });
    
    window.location.href = `/reports/invoices?${params.toString()}`;
};
</script>

<template>
    <Head title="Invoice Reports" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Button variant="outline" size="sm" as-child>
                        <Link href="/reports">
                            <ArrowLeft class="w-4 h-4 mr-2" />
                            Back to Reports
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">Invoice Reports</h1>
                        <p class="text-muted-foreground">Track invoice performance and payment collection</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" @click="exportData('csv')">
                        <Download class="w-4 h-4 mr-2" />
                        Export CSV
                    </Button>
                    <Button variant="outline" @click="exportData('excel')">
                        <Download class="w-4 h-4 mr-2" />
                        Export Excel
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Total Invoices</p>
                                <p class="text-2xl font-bold">{{ stats.total_invoices }}</p>
                            </div>
                            <FileText class="w-8 h-8 text-blue-600 ml-auto" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Total Invoiced</p>
                                <p class="text-2xl font-bold">{{ formatCurrency(stats.total_invoice_amount) }}</p>
                            </div>
                            <DollarSign class="w-8 h-8 text-green-600 ml-auto" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Amount Received</p>
                                <p class="text-2xl font-bold text-green-600">{{ formatCurrency(stats.total_amount_received) }}</p>
                            </div>
                            <TrendingUp class="w-8 h-8 text-green-600 ml-auto" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Collection Rate</p>
                                <p class="text-2xl font-bold">{{ stats.overall_collection_rate }}%</p>
                            </div>
                            <Users class="w-8 h-8 text-purple-600 ml-auto" />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Payment Status Summary -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardContent class="p-6">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-green-600">{{ stats.fully_paid_invoices }}</p>
                            <p class="text-sm text-muted-foreground">Fully Paid</p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-blue-600">{{ stats.partially_paid_invoices }}</p>
                            <p class="text-sm text-muted-foreground">Partially Paid</p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-red-600">{{ stats.unpaid_invoices }}</p>
                            <p class="text-sm text-muted-foreground">Unpaid</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Filter class="w-5 h-5" />
                        Filters
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-5">
                        <div>
                            <label class="text-sm font-medium">From Date</label>
                            <Input v-model="dateFrom" type="date" />
                        </div>
                        <div>
                            <label class="text-sm font-medium">To Date</label>
                            <Input v-model="dateTo" type="date" />
                        </div>
                        <div>
                            <label class="text-sm font-medium">Client</label>
                            <Select v-model="selectedClient">
                                <SelectTrigger>
                                    <SelectValue placeholder="All Clients" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Clients</SelectItem>
                                    <SelectItem v-for="client in clients" :key="client.id" :value="client.id.toString()">
                                        {{ client.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div>
                            <label class="text-sm font-medium">Status</label>
                            <Select v-model="selectedStatus">
                                <SelectTrigger>
                                    <SelectValue placeholder="All Statuses" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Statuses</SelectItem>
                                    <SelectItem v-for="status in statuses" :key="status" :value="status">
                                        {{ status.charAt(0).toUpperCase() + status.slice(1) }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="flex items-end gap-2">
                            <Button @click="applyFilters">Apply</Button>
                            <Button variant="outline" @click="clearFilters">Clear</Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Invoice Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Invoice Details</CardTitle>
                    <CardDescription>Detailed view of invoices with payment information</CardDescription>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b bg-muted/50">
                                    <th class="text-left p-4 font-medium">Invoice #</th>
                                    <th class="text-left p-4 font-medium">Client</th>
                                    <th class="text-left p-4 font-medium">Issue Date</th>
                                    <th class="text-left p-4 font-medium">Total Amount</th>
                                    <th class="text-left p-4 font-medium">Amount Received</th>
                                    <th class="text-left p-4 font-medium">Remaining</th>
                                    <th class="text-left p-4 font-medium">Payment Status</th>
                                    <th class="text-left p-4 font-medium">Last Payment</th>
                                    <th class="text-left p-4 font-medium">Status</th>
                                    <th class="text-right p-4 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="invoice in invoices" :key="invoice.id" class="border-b hover:bg-muted/25">
                                    <td class="p-4">
                                        <Link :href="`/invoices/${invoice.id}`" class="font-medium text-primary hover:underline">
                                            {{ invoice.invoice_number }}
                                        </Link>
                                    </td>
                                    <td class="p-4">
                                        <Link :href="`/clients/${invoice.client_id}`" class="text-primary hover:underline">
                                            {{ invoice.client_name }}
                                        </Link>
                                    </td>
                                    <td class="p-4">{{ formatDate(invoice.issue_date) }}</td>
                                    <td class="p-4 font-medium">{{ formatCurrency(invoice.total_amount) }}</td>
                                    <td class="p-4 text-green-600 font-medium">
                                        {{ formatCurrency(invoice.amount_received) }}
                                        <span v-if="invoice.payment_count > 0" class="text-xs text-muted-foreground block">
                                            ({{ invoice.payment_count }} payments)
                                        </span>
                                    </td>
                                    <td class="p-4 text-red-600 font-medium">{{ formatCurrency(invoice.amount_remaining) }}</td>
                                    <td class="p-4">
                                        <Badge :class="getPaymentStatusColor(invoice.payment_percentage)">
                                            {{ getPaymentStatusText(invoice.payment_percentage) }}
                                        </Badge>
                                        <span class="text-xs text-muted-foreground block mt-1">
                                            {{ invoice.payment_percentage }}%
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <span v-if="invoice.last_payment_date" class="text-sm">
                                            {{ formatDate(invoice.last_payment_date) }}
                                        </span>
                                        <span v-else class="text-sm text-muted-foreground">No payments</span>
                                    </td>
                                    <td class="p-4">
                                        <Badge :class="getStatusColor(invoice.status)">
                                            {{ invoice.status.charAt(0).toUpperCase() + invoice.status.slice(1) }}
                                        </Badge>
                                    </td>
                                    <td class="p-4 text-right">
                                        <Button variant="outline" size="sm" as-child>
                                            <Link :href="`/invoices/${invoice.id}`">
                                                <Eye class="w-4 h-4" />
                                            </Link>
                                        </Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <!-- Empty State -->
            <Card v-if="invoices.length === 0" class="text-center py-12">
                <CardContent>
                    <div class="w-12 h-12 rounded-full bg-muted flex items-center justify-center mx-auto mb-4">
                        <FileText class="w-6 h-6 text-muted-foreground" />
                    </div>
                    <h3 class="text-lg font-semibold mb-2">No invoices found</h3>
                    <p class="text-muted-foreground mb-4">
                        Try adjusting your filters to see more results.
                    </p>
                    <Button variant="outline" @click="clearFilters">
                        Clear All Filters
                    </Button>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
