<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import { 
    ArrowLeft, 
    Edit3, 
    Calendar, 
    DollarSign, 
    FileText, 
    Users, 
    Mail, 
    Phone, 
    MapPin,
    Plus,
    Eye,
    Download,
    Clock,
    CheckCircle2,
    AlertCircle,
    XCircle
} from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Client {
    id: number;
    name: string;
    email: string;
    company?: string;
    phone?: string;
    address?: string;
}

interface Invoice {
    id: number;
    invoice_number: string;
    status: string;
    total: number;
    due_date?: string;
    created_at: string;
}

interface Quotation {
    id: number;
    quotation_number: string;
    status: string;
    total: number;
    valid_until?: string;
    created_at: string;
}

interface Expense {
    id: number;
    description: string;
    amount: number;
    expense_date: string;
    category?: string;
}

interface Project {
    id: number;
    client_id: number;
    name: string;
    description?: string;
    status: string;
    start_date?: string;
    end_date?: string;
    estimated_cost?: number;
    actual_cost?: number;
    notes?: string;
    created_at: string;
    updated_at: string;
    client: Client;
    invoices: Invoice[];
    quotations: Quotation[];
    expenses: Expense[];
}

interface Props {
    project: Project;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Projects',
        href: '/projects',
    },
    {
        title: props.project.name,
        href: `/projects/${props.project.id}`,
    },
];

const statusOptions = [
    { value: 'planning', label: 'Planning', icon: Clock, color: 'yellow' },
    { value: 'in_progress', label: 'In Progress', icon: AlertCircle, color: 'blue' },
    { value: 'on_hold', label: 'On Hold', icon: XCircle, color: 'orange' },
    { value: 'completed', label: 'Completed', icon: CheckCircle2, color: 'green' },
    { value: 'cancelled', label: 'Cancelled', icon: XCircle, color: 'red' },
];

const currentStatus = computed(() => {
    return statusOptions.find(s => s.value === props.project.status) || statusOptions[0];
});

const getStatusBadgeClass = (status: string) => {
    const classes = {
        planning: 'bg-yellow-100 text-yellow-800 border-yellow-200',
        in_progress: 'bg-blue-100 text-blue-800 border-blue-200',
        on_hold: 'bg-orange-100 text-orange-800 border-orange-200',
        completed: 'bg-green-100 text-green-800 border-green-200',
        cancelled: 'bg-red-100 text-red-800 border-red-200',
    };
    return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800 border-gray-200';
};

const getInvoiceStatusBadge = (status: string) => {
    const classes = {
        draft: 'bg-gray-100 text-gray-800',
        sent: 'bg-blue-100 text-blue-800',
        paid: 'bg-green-100 text-green-800',
        overdue: 'bg-red-100 text-red-800',
        cancelled: 'bg-red-100 text-red-800',
    };
    return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800';
};

const getQuotationStatusBadge = (status: string) => {
    const classes = {
        draft: 'bg-gray-100 text-gray-800',
        sent: 'bg-blue-100 text-blue-800',
        accepted: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800',
        expired: 'bg-orange-100 text-orange-800',
    };
    return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800';
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

const formatDateLong = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const totalInvoices = computed(() => {
    return props.project.invoices.reduce((sum, invoice) => sum + invoice.total, 0);
});

const totalQuotations = computed(() => {
    return props.project.quotations.reduce((sum, quotation) => sum + quotation.total, 0);
});

const totalExpenses = computed(() => {
    return props.project.expenses.reduce((sum, expense) => sum + expense.amount, 0);
});

const profitMargin = computed(() => {
    if (!totalInvoices.value) return 0;
    return ((totalInvoices.value - totalExpenses.value) / totalInvoices.value) * 100;
});

const projectProgress = computed(() => {
    if (props.project.status === 'completed') return 100;
    if (props.project.status === 'cancelled') return 0;
    if (props.project.status === 'planning') return 10;
    if (props.project.status === 'in_progress') return 50;
    if (props.project.status === 'on_hold') return 25;
    return 0;
});

const isOverdue = computed(() => {
    if (!props.project.end_date || props.project.status === 'completed') return false;
    return new Date(props.project.end_date) < new Date();
});

const daysRemaining = computed(() => {
    if (!props.project.end_date || props.project.status === 'completed') return null;
    const today = new Date();
    const endDate = new Date(props.project.end_date);
    const diffTime = endDate.getTime() - today.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
});
</script>

<template>
    <Head :title="project.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Button variant="outline" size="sm" as-child>
                        <Link href="/projects">
                            <ArrowLeft class="w-4 h-4 mr-2" />
                            Back to Projects
                        </Link>
                    </Button>
                    <div>
                        <div class="flex items-center space-x-3">
                            <h1 class="text-3xl font-bold tracking-tight">{{ project.name }}</h1>
                            <Badge :class="`${getStatusBadgeClass(project.status)} border`">
                                <component :is="currentStatus.icon" class="w-3 h-3 mr-1" />
                                {{ currentStatus.label }}
                            </Badge>
                        </div>
                        <p class="text-muted-foreground">{{ project.client.name }}{{ project.client.company ? ` • ${project.client.company}` : '' }}</p>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="`/projects/${project.id}/edit`">
                            <Edit3 class="w-4 h-4 mr-2" />
                            Edit
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Project Overview Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-2">
                            <DollarSign class="w-5 h-5 text-green-600" />
                            <div>
                                <p class="text-2xl font-bold">{{ formatCurrency(totalInvoices) }}</p>
                                <p class="text-sm text-gray-500">Total Invoiced</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-2">
                            <FileText class="w-5 h-5 text-blue-600" />
                            <div>
                                <p class="text-2xl font-bold">{{ formatCurrency(totalExpenses) }}</p>
                                <p class="text-sm text-gray-500">Total Expenses</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-2">
                            <CheckCircle2 class="w-5 h-5 text-purple-600" />
                            <div>
                                <p class="text-2xl font-bold">{{ profitMargin.toFixed(1) }}%</p>
                                <p class="text-sm text-gray-500">Profit Margin</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-2">
                            <Calendar class="w-5 h-5" :class="isOverdue ? 'text-red-600' : 'text-orange-600'" />
                            <div>
                                <p class="text-2xl font-bold" :class="isOverdue ? 'text-red-600' : ''">
                                    {{ daysRemaining !== null ? (daysRemaining > 0 ? `${daysRemaining}d` : 'Overdue') : 'N/A' }}
                                </p>
                                <p class="text-sm text-gray-500">{{ daysRemaining !== null && daysRemaining > 0 ? 'Days Remaining' : 'Timeline Status' }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Progress Bar -->
            <Card>
                <CardContent class="p-6">
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span>Project Progress</span>
                            <span>{{ projectProgress }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div 
                                class="h-2 rounded-full transition-all duration-300"
                                :class="{
                                    'bg-green-600': project.status === 'completed',
                                    'bg-red-600': project.status === 'cancelled',
                                    'bg-blue-600': project.status === 'in_progress',
                                    'bg-yellow-600': project.status === 'planning',
                                    'bg-orange-600': project.status === 'on_hold'
                                }"
                                :style="`width: ${projectProgress}%`"
                            ></div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Main Content -->
            <div class="space-y-6">
                <!-- Project Details -->
                <div class="grid gap-6 md:grid-cols-2">
                    <!-- Project Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Project Information</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <Label class="text-sm font-medium text-gray-500">Description</Label>
                                <p class="mt-1">{{ project.description || 'No description provided' }}</p>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <Label class="text-sm font-medium text-gray-500">Start Date</Label>
                                    <p class="mt-1">{{ project.start_date ? formatDateLong(project.start_date) : 'Not set' }}</p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-gray-500">End Date</Label>
                                    <p class="mt-1" :class="isOverdue ? 'text-red-600 font-medium' : ''">
                                        {{ project.end_date ? formatDateLong(project.end_date) : 'Not set' }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <Label class="text-sm font-medium text-gray-500">Estimated Cost</Label>
                                    <p class="mt-1">{{ project.estimated_cost ? formatCurrency(project.estimated_cost) : 'Not set' }}</p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-gray-500">Actual Cost</Label>
                                    <p class="mt-1">{{ project.actual_cost ? formatCurrency(project.actual_cost) : formatCurrency(totalExpenses) }}</p>
                                </div>
                            </div>

                            <div v-if="project.notes">
                                <Label class="text-sm font-medium text-gray-500">Notes</Label>
                                <p class="mt-1 whitespace-pre-line">{{ project.notes }}</p>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2 text-sm text-gray-500">
                                <div>
                                    <Label class="text-sm font-medium text-gray-500">Created</Label>
                                    <p class="mt-1">{{ formatDateLong(project.created_at) }}</p>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-gray-500">Last Updated</Label>
                                    <p class="mt-1">{{ formatDateLong(project.updated_at) }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Client Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Client Information</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center space-x-3">
                                <Users class="w-5 h-5 text-gray-400" />
                                <div>
                                    <p class="font-medium">{{ project.client.name }}</p>
                                    <p class="text-sm text-gray-500" v-if="project.client.company">{{ project.client.company }}</p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3" v-if="project.client.email">
                                <Mail class="w-5 h-5 text-gray-400" />
                                <a :href="`mailto:${project.client.email}`" class="text-blue-600 hover:underline">
                                    {{ project.client.email }}
                                </a>
                            </div>

                            <div class="flex items-center space-x-3" v-if="project.client.phone">
                                <Phone class="w-5 h-5 text-gray-400" />
                                <a :href="`tel:${project.client.phone}`" class="text-blue-600 hover:underline">
                                    {{ project.client.phone }}
                                </a>
                            </div>

                            <div class="flex items-start space-x-3" v-if="project.client.address">
                                <MapPin class="w-5 h-5 text-gray-400 mt-0.5" />
                                <p class="whitespace-pre-line">{{ project.client.address }}</p>
                            </div>

                            <div class="pt-4">
                                <Button variant="outline" size="sm" as-child>
                                    <Link :href="`/clients/${project.client.id}`">
                                        <Eye class="w-4 h-4 mr-2" />
                                        View Client
                                    </Link>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Invoices -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle>Project Invoices ({{ project.invoices.length }})</CardTitle>
                                <CardDescription>All invoices related to this project</CardDescription>
                            </div>
                            <Button size="sm" as-child>
                                <Link :href="`/invoices/create?project_id=${project.id}`">
                                    <Plus class="w-4 h-4 mr-2" />
                                    Create Invoice
                                </Link>
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div v-if="project.invoices.length === 0" class="text-center py-8 text-gray-500">
                            <FileText class="w-12 h-12 mx-auto mb-4 text-gray-300" />
                            <p>No invoices found for this project</p>
                        </div>
                        <div v-else class="space-y-4">
                            <div v-for="invoice in project.invoices" :key="invoice.id" 
                                 class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50">
                                <div>
                                    <p class="font-medium">{{ invoice.invoice_number }}</p>
                                    <p class="text-sm text-gray-500">Created {{ formatDate(invoice.created_at) }}</p>
                                    <p class="text-sm text-gray-500" v-if="invoice.due_date">Due {{ formatDate(invoice.due_date) }}</p>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <div class="text-right">
                                        <p class="font-medium">{{ formatCurrency(invoice.total) }}</p>
                                        <Badge :class="getInvoiceStatusBadge(invoice.status)">{{ invoice.status }}</Badge>
                                    </div>
                                    <Button variant="outline" size="sm" as-child>
                                        <Link :href="`/invoices/${invoice.id}`">
                                            <Eye class="w-4 h-4" />
                                        </Link>
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Quotations -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle>Project Quotations ({{ project.quotations.length }})</CardTitle>
                                <CardDescription>All quotations related to this project</CardDescription>
                            </div>
                            <Button size="sm" as-child>
                                <Link :href="`/quotations/create?project_id=${project.id}`">
                                    <Plus class="w-4 h-4 mr-2" />
                                    Create Quotation
                                </Link>
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div v-if="project.quotations.length === 0" class="text-center py-8 text-gray-500">
                            <FileText class="w-12 h-12 mx-auto mb-4 text-gray-300" />
                            <p>No quotations found for this project</p>
                        </div>
                        <div v-else class="space-y-4">
                            <div v-for="quotation in project.quotations" :key="quotation.id" 
                                 class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50">
                                <div>
                                    <p class="font-medium">{{ quotation.quotation_number }}</p>
                                    <p class="text-sm text-gray-500">Created {{ formatDate(quotation.created_at) }}</p>
                                    <p class="text-sm text-gray-500" v-if="quotation.valid_until">Valid until {{ formatDate(quotation.valid_until) }}</p>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <div class="text-right">
                                        <p class="font-medium">{{ formatCurrency(quotation.total) }}</p>
                                        <Badge :class="getQuotationStatusBadge(quotation.status)">{{ quotation.status }}</Badge>
                                    </div>
                                    <Button variant="outline" size="sm" as-child>
                                        <Link :href="`/quotations/${quotation.id}`">
                                            <Eye class="w-4 h-4" />
                                        </Link>
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Expenses -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle>Project Expenses ({{ project.expenses.length }})</CardTitle>
                                <CardDescription>All expenses related to this project</CardDescription>
                            </div>
                            <Button size="sm" as-child>
                                <Link :href="`/expenses/create?project_id=${project.id}`">
                                    <Plus class="w-4 h-4 mr-2" />
                                    Add Expense
                                </Link>
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div v-if="project.expenses.length === 0" class="text-center py-8 text-gray-500">
                            <DollarSign class="w-12 h-12 mx-auto mb-4 text-gray-300" />
                            <p>No expenses found for this project</p>
                        </div>
                        <div v-else class="space-y-4">
                            <div v-for="expense in project.expenses" :key="expense.id" 
                                 class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50">
                                <div>
                                    <p class="font-medium">{{ expense.description }}</p>
                                    <p class="text-sm text-gray-500">{{ formatDate(expense.expense_date) }}</p>
                                    <p class="text-sm text-gray-500" v-if="expense.category">{{ expense.category }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium">{{ formatCurrency(expense.amount) }}</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
