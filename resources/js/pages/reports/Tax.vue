<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { CalendarIcon, Filter, FileText, DollarSign, Receipt, Calculator } from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface InvoiceItem {
    id: number;
    description: string;
    quantity: number;
    unit_price: number;
    tax_amount: number;
    total_amount: number;
}

interface Invoice {
    id: number;
    invoice_number: string;
    total_amount: number;
    client: {
        id: number;
        name: string;
    };
}

interface Payment {
    id: number;
    amount: number;
    payment_date: string;
    payment_method: string;
}

interface InvoiceBreakdown {
    invoice: Invoice;
    payments: Array<{
        payment: Payment;
        tax_amount: number;
        percentage_of_invoice: number;
    }>;
    total_paid: number;
    total_tax_collected: number;
}

interface TaxCollectedData {
    total_revenue: number;
    total_tax: number;
    invoice_breakdown: InvoiceBreakdown[];
}

interface Expense {
    id: number;
    amount: number;
    description: string;
    expense_date: string;
    vendor: {
        id: number;
        name: string;
    } | null;
    project: {
        id: number;
        name: string;
    } | null;
}

interface Summary {
    total_revenue: number;
    total_tax_collected: number;
    total_expenses: number;
    tax_on_expenses: number;
    net_tax_liability: number;
    period: {
        start: string;
        end: string;
        days: number;
    };
}

interface Props {
    startDate: string;
    endDate: string;
    taxCollectedData: TaxCollectedData;
    expenses: Expense[];
    totalExpenses: number;
    netTaxLiability: number;
    summary: Summary;
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
        title: 'Tax Reports',
        href: '/reports/tax',
    },
];

const filterStartDate = ref(props.startDate);
const filterEndDate = ref(props.endDate);

const applyFilters = () => {
    const query: Record<string, string> = {};
    
    if (filterStartDate.value) {
        query.start_date = filterStartDate.value;
    }
    
    if (filterEndDate.value) {
        query.end_date = filterEndDate.value;
    }
    
    router.get('/reports/tax', query, { preserveState: true });
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString();
};

const formatPercentage = (percentage: number) => {
    return `${percentage.toFixed(2)}%`;
};
</script>

<template>
    <Head title="Tax Reports" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Tax Reports</h1>
                    <p class="text-muted-foreground">
                        Calculate government tax obligations based on paid invoices and deductible expenses
                    </p>
                </div>
            </div>

            <!-- Filters -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Filter class="h-5 w-5" />
                        Date Range Filter
                    </CardTitle>
                    <CardDescription>
                        Select the date range for tax calculation
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <Label for="start_date">Start Date</Label>
                            <Input
                                id="start_date"
                                type="date"
                                v-model="filterStartDate"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="end_date">End Date</Label>
                            <Input
                                id="end_date"
                                type="date"
                                v-model="filterEndDate"
                            />
                        </div>
                        <div class="flex items-end">
                            <Button @click="applyFilters" class="w-full">
                                Apply Filters
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Revenue</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(summary.total_revenue) }}</div>
                        <p class="text-xs text-muted-foreground">
                            From {{ summary.period.days }} days
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Tax Collected</CardTitle>
                        <Receipt class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(summary.total_tax_collected) }}</div>
                        <p class="text-xs text-muted-foreground">
                            VAT collected from customers
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Deductible Expenses</CardTitle>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(summary.total_expenses) }}</div>
                        <p class="text-xs text-muted-foreground">
                            Tax deduction: {{ formatCurrency(summary.tax_on_expenses) }}
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Net Tax Liability</CardTitle>
                        <Calculator class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-600">{{ formatCurrency(summary.net_tax_liability) }}</div>
                        <p class="text-xs text-muted-foreground">
                            Amount due to government
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Tax Calculation Breakdown -->
            <Card>
                <CardHeader>
                    <CardTitle>Tax Calculation Breakdown</CardTitle>
                    <CardDescription>
                        Detailed breakdown of tax calculations for the selected period
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div class="border rounded-lg p-4 bg-muted/50">
                            <h4 class="font-semibold mb-2">Calculation Summary</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <div class="flex justify-between">
                                        <span>Total Revenue:</span>
                                        <span>{{ formatCurrency(summary.total_revenue) }}</span>
                                    </div>
                                    <div class="flex justify-between font-semibold">
                                        <span>Tax Collected:</span>
                                        <span>{{ formatCurrency(summary.total_tax_collected) }}</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between">
                                        <span>Deductible Expenses:</span>
                                        <span>{{ formatCurrency(summary.total_expenses) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Tax on Expenses (15%):</span>
                                        <span>-{{ formatCurrency(summary.tax_on_expenses) }}</span>
                                    </div>
                                    <hr class="my-1">
                                    <div class="flex justify-between font-bold text-red-600">
                                        <span>Net Tax Liability:</span>
                                        <span>{{ formatCurrency(summary.net_tax_liability) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Invoice Tax Collection Details -->
            <Card>
                <CardHeader>
                    <CardTitle>Invoice Tax Collection Details</CardTitle>
                    <CardDescription>
                        Tax collected from each invoice based on payments received
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Invoice</TableHead>
                                    <TableHead>Client</TableHead>
                                    <TableHead>Total Paid</TableHead>
                                    <TableHead>Tax Collected</TableHead>
                                    <TableHead>Payment Count</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="!taxCollectedData.invoice_breakdown || taxCollectedData.invoice_breakdown.length === 0">
                                    <TableCell colspan="5" class="text-center text-muted-foreground">
                                        No invoice data found for the selected period
                                    </TableCell>
                                </TableRow>
                                <TableRow v-for="breakdown in taxCollectedData.invoice_breakdown" :key="breakdown.invoice.id">
                                    <TableCell class="font-medium">
                                        {{ breakdown.invoice.invoice_number }}
                                    </TableCell>
                                    <TableCell>{{ breakdown.invoice.client.name }}</TableCell>
                                    <TableCell>{{ formatCurrency(breakdown.total_paid) }}</TableCell>
                                    <TableCell class="font-semibold">{{ formatCurrency(breakdown.total_tax_collected) }}</TableCell>
                                    <TableCell>
                                        <Badge variant="secondary">
                                            {{ breakdown.payments.length }} payment(s)
                                        </Badge>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>

            <!-- Deductible Expenses -->
            <Card>
                <CardHeader>
                    <CardTitle>Deductible Expenses</CardTitle>
                    <CardDescription>
                        Non-billable expenses eligible for tax deduction (15% VAT)
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Date</TableHead>
                                    <TableHead>Description</TableHead>
                                    <TableHead>Vendor</TableHead>
                                    <TableHead>Project</TableHead>
                                    <TableHead>Amount</TableHead>
                                    <TableHead>Tax Deduction</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="!expenses || expenses.length === 0">
                                    <TableCell colspan="6" class="text-center text-muted-foreground">
                                        No deductible expenses found for the selected period
                                    </TableCell>
                                </TableRow>
                                <TableRow v-for="expense in expenses" :key="expense.id">
                                    <TableCell>{{ formatDate(expense.expense_date) }}</TableCell>
                                    <TableCell class="font-medium">{{ expense.description }}</TableCell>
                                    <TableCell>{{ expense.vendor?.name || 'N/A' }}</TableCell>
                                    <TableCell>{{ expense.project?.name || 'N/A' }}</TableCell>
                                    <TableCell>{{ formatCurrency(expense.amount) }}</TableCell>
                                    <TableCell class="font-semibold text-green-600">
                                        -{{ formatCurrency(expense.amount * 0.15) }}
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
