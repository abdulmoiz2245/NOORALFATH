<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Plus, Search, Eye, Edit, Trash2, Receipt, DollarSign, Calendar, Building } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface Expense {
    id: number;
    description: string;
    amount: number;
    expense_date: string;
    category: string;
    vendor?: { name: string };
    project?: { name: string };
    receipt_file?: string;
    is_billable: boolean;
    is_reimbursable: boolean;
}

interface Props {
    expenses: Expense[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Expenses',
        href: '/expenses',
    },
];

const searchQuery = ref('');
const categoryFilter = ref('all');

const filteredExpenses = computed(() => {
    let filtered = props.expenses;

    if (categoryFilter.value !== 'all') {
        filtered = filtered.filter(expense => expense.category === categoryFilter.value);
    }

    if (searchQuery.value) {
        filtered = filtered.filter(expense => 
            expense.description.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            (expense.vendor && expense.vendor.name.toLowerCase().includes(searchQuery.value.toLowerCase())) ||
            expense.category.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }

    return filtered;
});

const stats = computed(() => ({
    total: props.expenses.length,
    totalValue: props.expenses.reduce((sum: number, exp: Expense) => sum + exp.amount, 0),
    billable: props.expenses.filter((exp: Expense) => exp.is_billable).length,
    reimbursable: props.expenses.filter((exp: Expense) => exp.is_reimbursable).length,
    withReceipts: props.expenses.filter((exp: Expense) => exp.receipt_file).length,
}));

const categories = computed(() => {
    const cats = [...new Set(props.expenses.map(e => e.category))];
    return cats.sort();
});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'AED'
    }).format(amount);
};

const formatDate = (date: string) => {
    console.log(date);
    return new Date(date).toLocaleDateString();
};
</script>

<template>
    <Head title="Expenses" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Expenses</h1>
                    <p class="text-muted-foreground">Track and manage business expenses</p>
                </div>
                <Button as-child>
                    <Link href="/expenses/create">
                        <Plus class="w-4 h-4 mr-2" />
                        Add Expense
                    </Link>
                </Button>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Expenses</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(stats.total) }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Billable</CardTitle>
                        <Receipt class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">{{ formatCurrency(stats.billable) }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Reimbursable</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-blue-600">{{ formatCurrency(stats.reimbursable) }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">With Receipts</CardTitle>
                        <Receipt class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.withReceipts }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Expenses Table -->
            <Card>
                <CardHeader>
                    <div class="flex justify-between items-center">
                        <div>
                            <CardTitle>All Expenses</CardTitle>
                            <CardDescription>Track and categorize your business expenses</CardDescription>
                        </div>
                        <div class="flex items-center space-x-2">
                            <select v-model="categoryFilter" class="border rounded px-2 py-1">
                                <option value="all">All Categories</option>
                                <option v-for="category in categories" :key="category" :value="category">
                                    {{ category }}
                                </option>
                            </select>
                            <div class="relative">
                                <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input
                                    placeholder="Search expenses..."
                                    v-model="searchQuery"
                                    class="pl-8"
                                />
                            </div>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="filteredExpenses.length === 0" class="text-center py-8 text-muted-foreground">
                        <Receipt class="mx-auto h-12 w-12 mb-4" />
                        <p>No expenses found</p>
                        <Button as-child class="mt-4">
                            <Link href="/expenses/create">Add your first expense</Link>
                        </Button>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left p-4">Description</th>
                                    <th class="text-left p-4">Date</th>
                                    <th class="text-left p-4">Category</th>
                                    <th class="text-left p-4">Vendor</th>
                                    <th class="text-left p-4">Amount</th>
                                    <th class="text-left p-4">Type</th>
                                    <th class="text-right p-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="expense in filteredExpenses" :key="expense.id" class="border-b hover:bg-muted/50">
                                    <td class="p-4">
                                        <div class="flex items-center space-x-2">
                                            <div>
                                                <p class="font-medium">{{ expense.description }}</p>
                                                <p v-if="expense.project" class="text-sm text-muted-foreground">
                                                    Project: {{ expense.project }}
                                                </p>
                                            </div>
                                            <Receipt v-if="expense.receipt_file" class="w-4 h-4 text-green-600" />
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center space-x-2">
                                            <Calendar class="w-4 h-4 text-muted-foreground" />
                                            <span>{{ formatDate(expense.expense_date) }}</span>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ expense.category }}
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <div v-if="expense.vendor" class="flex items-center space-x-2">
                                            <Building class="w-4 h-4 text-muted-foreground" />
                                            <span>{{ expense.vendor }}</span>
                                        </div>
                                        <span v-else class="text-muted-foreground">-</span>
                                    </td>
                                    <td class="p-4">
                                        <p class="font-medium">{{ formatCurrency(expense.amount) }}</p>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex flex-col space-y-1">
                                            <span v-if="expense.is_billable" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                Billable
                                            </span>
                                            <span v-if="expense.is_reimbursable" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                Reimbursable
                                            </span>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex justify-end space-x-2">
                                            <Button variant="outline" size="sm" as-child>
                                                <Link :href="`/expenses/${expense.id}`">
                                                    <Eye class="w-4 h-4" />
                                                </Link>
                                            </Button>
                                            <Button variant="outline" size="sm" as-child>
                                                <Link :href="`/expenses/${expense.id}/edit`">
                                                    <Edit class="w-4 h-4" />
                                                </Link>
                                            </Button>
                                            <Button variant="destructive" size="sm">
                                                <Trash2 class="w-4 h-4" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
