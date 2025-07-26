<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Users, Download, Calendar, DollarSign, FileText, TrendingUp } from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface Props {
    clients: Array<{
        id: number;
        name: string;
        company_name: string;
        email: string;
        phone: string;
        invoices_count: number;
        projects_count: number;
        total_invoiced: number;
        total_received: number;
        outstanding_amount: number;
        collection_rate: number;
        last_invoice_date: string;
        last_payment_date: string;
    }>;
    filters: {
        date_from?: string;
        date_to?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Reports', href: '/reports' },
    { title: 'Client Reports', href: '/reports/clients' },
];

const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');

const stats = computed(() => {
    return {
        total_clients: props.clients.length,
        total_invoiced: props.clients.reduce((sum, client) => sum + client.total_invoiced, 0),
        total_received: props.clients.reduce((sum, client) => sum + client.total_received, 0),
        average_collection_rate: props.clients.length > 0 
            ? props.clients.reduce((sum, client) => sum + client.collection_rate, 0) / props.clients.length 
            : 0,
    };
});

const applyFilters = () => {
    router.get('/reports/clients', {
        date_from: dateFrom.value,
        date_to: dateTo.value,
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
    });
    
    window.location.href = `/reports/clients?${params.toString()}`;
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};

const formatDate = (date: string) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString();
};

const getCollectionBadge = (rate: number) => {
    if (rate >= 90) return 'bg-green-100 text-green-800';
    if (rate >= 70) return 'bg-yellow-100 text-yellow-800';
    return 'bg-red-100 text-red-800';
};
</script>

<template>
    <Head title="Client Reports" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Client Reports</h1>
                    <p class="text-muted-foreground">Analyze client performance and payment history</p>
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
                        <CardTitle class="text-sm font-medium">Total Clients</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_clients }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Invoiced</CardTitle>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(stats.total_invoiced) }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Received</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(stats.total_received) }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Avg Collection Rate</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.average_collection_rate.toFixed(1) }}%</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Calendar class="w-5 h-5" />
                        Date Range Filter
                    </CardTitle>
                    <CardDescription>Filter clients by invoice date range</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="flex gap-4 items-end">
                        <div class="flex-1">
                            <label class="text-sm font-medium">From Date</label>
                            <Input 
                                v-model="dateFrom"
                                type="date"
                                class="mt-1"
                            />
                        </div>
                        <div class="flex-1">
                            <label class="text-sm font-medium">To Date</label>
                            <Input 
                                v-model="dateTo"
                                type="date"
                                class="mt-1"
                            />
                        </div>
                        <Button @click="applyFilters">Apply Filters</Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Clients Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Client Performance</CardTitle>
                    <CardDescription>Detailed client analysis with payment performance</CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Client</TableHead>
                                <TableHead>Contact</TableHead>
                                <TableHead>Invoices</TableHead>
                                <TableHead>Projects</TableHead>
                                <TableHead>Total Invoiced</TableHead>
                                <TableHead>Total Received</TableHead>
                                <TableHead>Outstanding</TableHead>
                                <TableHead>Collection Rate</TableHead>
                                <TableHead>Last Invoice</TableHead>
                                <TableHead>Last Payment</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="client in clients" :key="client.id">
                                <TableCell>
                                    <div>
                                        <div class="font-medium">{{ client.name }}</div>
                                        <div class="text-sm text-muted-foreground" v-if="client.company_name">
                                            {{ client.company_name }}
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="text-sm">
                                        <div>{{ client.email }}</div>
                                        <div class="text-muted-foreground">{{ client.phone }}</div>
                                    </div>
                                </TableCell>
                                <TableCell>{{ client.invoices_count }}</TableCell>
                                <TableCell>{{ client.projects_count }}</TableCell>
                                <TableCell>{{ formatCurrency(client.total_invoiced) }}</TableCell>
                                <TableCell>{{ formatCurrency(client.total_received) }}</TableCell>
                                <TableCell>{{ formatCurrency(client.outstanding_amount) }}</TableCell>
                                <TableCell>
                                    <Badge :class="getCollectionBadge(client.collection_rate)">
                                        {{ client.collection_rate.toFixed(1) }}%
                                    </Badge>
                                </TableCell>
                                <TableCell>{{ formatDate(client.last_invoice_date) }}</TableCell>
                                <TableCell>{{ formatDate(client.last_payment_date) }}</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
