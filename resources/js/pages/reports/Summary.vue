<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { DollarSign, Download, Calendar, FileText, TrendingUp, BarChart3, PieChart } from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface Props {
    invoice_stats: {
        total_invoices: number;
        total_invoiced: number;
        paid_invoices: number;
        pending_invoices: number;
        overdue_invoices: number;
    };
    payment_stats: {
        total_payments: number;
        total_received: number;
        average_payment: number;
    };
    monthly_data: Array<{
        month: string;
        month_name: string;
        invoiced: number;
        received: number;
        invoices_count: number;
        payments_count: number;
    }>;
    filters: {
        date_from: string;
        date_to: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Reports', href: '/reports' },
    { title: 'Financial Summary', href: '/reports/summary' },
];

const dateFrom = ref(props.filters.date_from);
const dateTo = ref(props.filters.date_to);

const overallStats = computed(() => {
    const collectionRate = props.invoice_stats.total_invoiced > 0 
        ? (props.payment_stats.total_received / props.invoice_stats.total_invoiced) * 100 
        : 0;
    
    const outstandingAmount = props.invoice_stats.total_invoiced - props.payment_stats.total_received;
    
    return {
        collection_rate: collectionRate,
        outstanding_amount: outstandingAmount,
        average_invoice_value: props.invoice_stats.total_invoices > 0 
            ? props.invoice_stats.total_invoiced / props.invoice_stats.total_invoices 
            : 0,
    };
});

const monthlyTotals = computed(() => {
    return {
        total_invoiced: props.monthly_data.reduce((sum, month) => sum + month.invoiced, 0),
        total_received: props.monthly_data.reduce((sum, month) => sum + month.received, 0),
        total_invoices: props.monthly_data.reduce((sum, month) => sum + month.invoices_count, 0),
        total_payments: props.monthly_data.reduce((sum, month) => sum + month.payments_count, 0),
    };
});

const applyFilters = () => {
    router.get('/reports/summary', {
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
    
    window.location.href = `/reports/summary?${params.toString()}`;
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};

const formatPercentage = (value: number) => {
    return `${value.toFixed(1)}%`;
};
</script>

<template>
    <Head title="Financial Summary" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Financial Summary</h1>
                    <p class="text-muted-foreground">Overall financial performance and trends analysis</p>
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

            <!-- Date Range Filter -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Calendar class="w-5 h-5" />
                        Date Range
                    </CardTitle>
                    <CardDescription>Select the period for financial analysis</CardDescription>
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

            <!-- Overview Stats -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Invoiced</CardTitle>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(invoice_stats.total_invoiced) }}</div>
                        <p class="text-xs text-muted-foreground">{{ invoice_stats.total_invoices }} invoices</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Received</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(payment_stats.total_received) }}</div>
                        <p class="text-xs text-muted-foreground">{{ payment_stats.total_payments }} payments</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Collection Rate</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatPercentage(overallStats.collection_rate) }}</div>
                        <p class="text-xs text-muted-foreground">Overall collection</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Outstanding</CardTitle>
                        <PieChart class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(overallStats.outstanding_amount) }}</div>
                        <p class="text-xs text-muted-foreground">Amount pending</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Invoice Status Breakdown -->
            <div class="grid gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Invoice Status Breakdown</CardTitle>
                        <CardDescription>Distribution of invoice statuses</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm">Paid Invoices</span>
                                <div class="flex items-center gap-2">
                                    <div class="w-20 h-2 bg-gray-200 rounded">
                                        <div 
                                            class="h-2 bg-green-500 rounded" 
                                            :style="{ width: `${(invoice_stats.paid_invoices / invoice_stats.total_invoices) * 100}%` }"
                                        ></div>
                                    </div>
                                    <span class="text-sm font-medium">{{ invoice_stats.paid_invoices }}</span>
                                </div>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm">Pending Invoices</span>
                                <div class="flex items-center gap-2">
                                    <div class="w-20 h-2 bg-gray-200 rounded">
                                        <div 
                                            class="h-2 bg-yellow-500 rounded" 
                                            :style="{ width: `${(invoice_stats.pending_invoices / invoice_stats.total_invoices) * 100}%` }"
                                        ></div>
                                    </div>
                                    <span class="text-sm font-medium">{{ invoice_stats.pending_invoices }}</span>
                                </div>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm">Overdue Invoices</span>
                                <div class="flex items-center gap-2">
                                    <div class="w-20 h-2 bg-gray-200 rounded">
                                        <div 
                                            class="h-2 bg-red-500 rounded" 
                                            :style="{ width: `${(invoice_stats.overdue_invoices / invoice_stats.total_invoices) * 100}%` }"
                                        ></div>
                                    </div>
                                    <span class="text-sm font-medium">{{ invoice_stats.overdue_invoices }}</span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Key Metrics</CardTitle>
                        <CardDescription>Important financial indicators</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-sm">Average Invoice Value</span>
                                <span class="font-medium">{{ formatCurrency(overallStats.average_invoice_value) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm">Average Payment</span>
                                <span class="font-medium">{{ formatCurrency(payment_stats.average_payment) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm">Total Outstanding</span>
                                <span class="font-medium text-red-600">{{ formatCurrency(overallStats.outstanding_amount) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm">Collection Efficiency</span>
                                <span class="font-medium" :class="overallStats.collection_rate >= 80 ? 'text-green-600' : 'text-yellow-600'">
                                    {{ formatPercentage(overallStats.collection_rate) }}
                                </span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Monthly Breakdown -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <BarChart3 class="w-5 h-5" />
                        Monthly Breakdown
                    </CardTitle>
                    <CardDescription>Month-by-month financial performance</CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Month</TableHead>
                                <TableHead>Invoices</TableHead>
                                <TableHead>Amount Invoiced</TableHead>
                                <TableHead>Payments</TableHead>
                                <TableHead>Amount Received</TableHead>
                                <TableHead>Collection Rate</TableHead>
                                <TableHead>Net Difference</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="month in monthly_data" :key="month.month">
                                <TableCell class="font-medium">{{ month.month_name }}</TableCell>
                                <TableCell>{{ month.invoices_count }}</TableCell>
                                <TableCell>{{ formatCurrency(month.invoiced) }}</TableCell>
                                <TableCell>{{ month.payments_count }}</TableCell>
                                <TableCell>{{ formatCurrency(month.received) }}</TableCell>
                                <TableCell>
                                    <span :class="month.invoiced > 0 && (month.received / month.invoiced) >= 0.8 ? 'text-green-600' : 'text-yellow-600'">
                                        {{ month.invoiced > 0 ? formatPercentage((month.received / month.invoiced) * 100) : '0%' }}
                                    </span>
                                </TableCell>
                                <TableCell>
                                    <span :class="(month.received - month.invoiced) >= 0 ? 'text-green-600' : 'text-red-600'">
                                        {{ formatCurrency(month.received - month.invoiced) }}
                                    </span>
                                </TableCell>
                            </TableRow>
                            <!-- Summary Row -->
                            <TableRow class="border-t-2 font-medium bg-muted/50">
                                <TableCell>Total</TableCell>
                                <TableCell>{{ monthlyTotals.total_invoices }}</TableCell>
                                <TableCell>{{ formatCurrency(monthlyTotals.total_invoiced) }}</TableCell>
                                <TableCell>{{ monthlyTotals.total_payments }}</TableCell>
                                <TableCell>{{ formatCurrency(monthlyTotals.total_received) }}</TableCell>
                                <TableCell>
                                    {{ monthlyTotals.total_invoiced > 0 ? formatPercentage((monthlyTotals.total_received / monthlyTotals.total_invoiced) * 100) : '0%' }}
                                </TableCell>
                                <TableCell>
                                    <span :class="(monthlyTotals.total_received - monthlyTotals.total_invoiced) >= 0 ? 'text-green-600' : 'text-red-600'">
                                        {{ formatCurrency(monthlyTotals.total_received - monthlyTotals.total_invoiced) }}
                                    </span>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
