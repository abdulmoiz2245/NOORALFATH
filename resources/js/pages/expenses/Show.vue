<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { ArrowLeft, Edit, Trash2, Download, Receipt, DollarSign, Calendar, Building, FolderOpen, FileText } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';

interface Vendor {
    id: number;
    name: string;
    company?: string;
}

interface Project {
    id: number;
    name: string;
}

interface Expense {
    id: number;
    description: string;
    amount: number;
    date: string;
    category: string;
    vendor_id?: number;
    vendor?: Vendor;
    project_id?: number;
    project?: Project;
    receipt_path?: string;
    is_billable: boolean;
    is_reimbursable: boolean;
    notes?: string;
    created_at: string;
    updated_at: string;
}

interface Props {
    expense: Expense;
}

const props = defineProps<Props>();
const { success, error } = useToast();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Expenses',
        href: '/expenses',
    },
    {
        title: props.expense.description,
        href: `/expenses/${props.expense.id}`,
    },
];

const form = useForm({});

const deleteExpense = () => {
    if (confirm('Are you sure you want to delete this expense? This action cannot be undone.')) {
        form.delete(`/expenses/${props.expense.id}`, {
            onSuccess: () => {
                success('Success!', 'Expense deleted successfully');
            },
            onError: () => {
                error('Error!', 'Failed to delete expense.');
            }
        });
    }
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString();
};
</script>

<template>
    <Head :title="expense.description" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Button variant="outline" size="sm" as-child>
                        <Link href="/expenses">
                            <ArrowLeft class="w-4 h-4 mr-2" />
                            Back to Expenses
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">{{ expense.description }}</h1>
                        <p class="text-muted-foreground">Expense details and information</p>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <Button v-if="expense.receipt_path" variant="outline" size="sm">
                        <Download class="w-4 h-4 mr-2" />
                        Download Receipt
                    </Button>
                    <Button variant="outline" as-child>
                        <Link :href="`/expenses/${expense.id}/edit`">
                            <Edit class="w-4 h-4 mr-2" />
                            Edit Expense
                        </Link>
                    </Button>
                    <Button variant="destructive" @click="deleteExpense" :disabled="form.processing">
                        <Trash2 class="w-4 h-4 mr-2" />
                        Delete
                    </Button>
                </div>
            </div>

            <!-- Expense Overview Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Amount</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(expense.amount) }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Date</CardTitle>
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatDate(expense.date) }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Category</CardTitle>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ expense.category }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Receipt</CardTitle>
                        <Receipt class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ expense.receipt_path ? 'Yes' : 'No' }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Expense Details -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Basic Information -->
                <Card>
                    <CardHeader>
                        <CardTitle>Expense Information</CardTitle>
                        <CardDescription>Details about this expense</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <div class="text-sm font-medium text-muted-foreground">Description</div>
                            <div class="text-base font-semibold">{{ expense.description }}</div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <div class="text-sm font-medium text-muted-foreground">Amount</div>
                                <div class="text-lg font-semibold">{{ formatCurrency(expense.amount) }}</div>
                            </div>

                            <div>
                                <div class="text-sm font-medium text-muted-foreground">Date</div>
                                <div class="text-lg">{{ formatDate(expense.date) }}</div>
                            </div>
                        </div>

                        <div>
                            <div class="text-sm font-medium text-muted-foreground">Category</div>
                            <Badge variant="outline">{{ expense.category }}</Badge>
                        </div>

                        <Separator />

                        <div class="flex flex-wrap gap-2">
                            <Badge v-if="expense.is_billable" variant="default">
                                Billable
                            </Badge>
                            <Badge v-if="expense.is_reimbursable" variant="default">
                                Reimbursable
                            </Badge>
                            <Badge v-if="expense.receipt_path" variant="default">
                                <Receipt class="w-3 h-3 mr-1" />
                                Has Receipt
                            </Badge>
                        </div>
                    </CardContent>
                </Card>

                <!-- Associations -->
                <Card>
                    <CardHeader>
                        <CardTitle>Associations</CardTitle>
                        <CardDescription>Related vendor and project information</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div v-if="expense.vendor">
                            <div class="text-sm font-medium text-muted-foreground">Vendor</div>
                            <div class="flex items-center space-x-2">
                                <Building class="w-4 h-4 text-muted-foreground" />
                                <Link :href="`/vendors/${expense.vendor.id}`" class="text-base text-blue-600 hover:underline">
                                    {{ expense.vendor.name }}{{ expense.vendor.company ? ` - ${expense.vendor.company}` : '' }}
                                </Link>
                            </div>
                        </div>

                        <div v-if="expense.project">
                            <div class="text-sm font-medium text-muted-foreground">Project</div>
                            <div class="flex items-center space-x-2">
                                <FolderOpen class="w-4 h-4 text-muted-foreground" />
                                <Link :href="`/projects/${expense.project.id}`" class="text-base text-blue-600 hover:underline">
                                    {{ expense.project.name }}
                                </Link>
                            </div>
                        </div>

                        <div v-if="!expense.vendor && !expense.project">
                            <div class="text-center py-4 text-muted-foreground">
                                <Building class="mx-auto h-8 w-8 mb-2" />
                                <p>No vendor or project associations</p>
                            </div>
                        </div>

                        <Separator />

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <div class="text-sm font-medium text-muted-foreground">Created</div>
                                <div class="text-base">{{ formatDate(expense.created_at) }}</div>
                            </div>

                            <div>
                                <div class="text-sm font-medium text-muted-foreground">Last Updated</div>
                                <div class="text-base">{{ formatDate(expense.updated_at) }}</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Receipt -->
            <Card v-if="expense.receipt_path">
                <CardHeader>
                    <div class="flex justify-between items-center">
                        <div>
                            <CardTitle>Receipt</CardTitle>
                            <CardDescription>Uploaded receipt for this expense</CardDescription>
                        </div>
                        <Button variant="outline" size="sm">
                            <Download class="w-4 h-4 mr-2" />
                            Download
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="flex items-center space-x-4 p-4 border rounded-lg">
                        <Receipt class="h-12 w-12 text-blue-500" />
                        <div>
                            <div class="font-medium">Receipt File</div>
                            <div class="text-sm text-muted-foreground">
                                Uploaded receipt for {{ expense.description }}
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Notes -->
            <Card v-if="expense.notes">
                <CardHeader>
                    <CardTitle>Notes</CardTitle>
                    <CardDescription>Additional information about this expense</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="whitespace-pre-wrap">{{ expense.notes }}</div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
