<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Edit, Trash2, Mail, Phone, MapPin, Building, FileText, DollarSign, User } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';

interface Project {
    id: number;
    name: string;
    description?: string;
    status: string;
    created_at: string;
}

interface Invoice {
    id: number;
    invoice_number: string;
    issue_date: string;
    due_date: string;
    status: string;
    total_amount: string;
    items: any[];
}

interface Quotation {
    id: number;
    quotation_number: string;
    issue_date: string;
    expiry_date: string;
    status: string;
    total_amount: string;
}

interface Client {
    id: number;
    name: string;
    company_name?: string;
    email?: string;
    phone?: string;
    trn_number?: string;
    address?: string;
    city?: string;
    state?: string;
    postal_code?: string;
    country?: string;
    notes?: string;
    projects: Project[];
    invoices: Invoice[];
    quotations: Quotation[];
    created_at: string;
    updated_at: string;
}

interface Props {
    client: Client;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Clients',
        href: '/clients',
    },
    {
        title: props.client.name,
        href: `/clients/${props.client.id}`,
    },
];

const form = useForm({});
const { success, error } = useToast();

const deleteClient = () => {
    if (confirm('Are you sure you want to delete this client? This action cannot be undone.')) {
        form.delete(`/clients/${props.client.id}`, {
            onSuccess: () => {
                success('Success!', 'Client deleted successfully');
            },
            onError: () => {
                error('Error!', 'Failed to delete client.');
            }
        });
    }
};

const formatCurrency = (amount: string | number) => {
    const value = typeof amount === 'string' ? parseFloat(amount) : amount;
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(value);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString();
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'active': return 'default';
        case 'completed': return 'success';
        case 'on_hold': return 'secondary';
        case 'cancelled': return 'destructive';
        case 'draft': return 'secondary';
        case 'sent': return 'default';
        case 'paid': return 'success';
        case 'overdue': return 'destructive';
        case 'accepted': return 'success';
        case 'rejected': return 'destructive';
        case 'expired': return 'outline';
        default: return 'secondary';
    }
};

const totalInvoiceValue = props.client.invoices.reduce((sum, invoice) => sum + parseFloat(invoice.total_amount), 0);
const totalQuotationValue = props.client.quotations.reduce((sum, quotation) => sum + parseFloat(quotation.total_amount), 0);
</script>

<template>
    <Head :title="client.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Button variant="outline" size="sm" as-child>
                        <Link href="/clients">
                            <ArrowLeft class="w-4 h-4 mr-2" />
                            Back to Clients
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">{{ client.name }}</h1>
                        <p v-if="client.company_name" class="text-lg text-muted-foreground">{{ client.company_name }}</p>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <Button variant="outline" as-child>
                        <Link :href="`/clients/${client.id}/edit`">
                            <Edit class="w-4 h-4 mr-2" />
                            Edit
                        </Link>
                    </Button>
                    <Button variant="destructive" @click="deleteClient" :disabled="form.processing">
                        <Trash2 class="w-4 h-4 mr-2" />
                        Delete
                    </Button>
                </div>
            </div>

            <!-- Client Overview -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <!-- Contact Information -->
                <Card class="md:col-span-2">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <User class="w-5 h-5" />
                            Contact Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Client Name</label>
                            <p class="text-lg font-semibold">{{ client.name }}</p>
                        </div>
                        
                        <div v-if="client.company_name">
                            <label class="text-sm font-medium text-muted-foreground flex items-center gap-1">
                                <Building class="w-4 h-4" />
                                Company
                            </label>
                            <p>{{ client.company_name }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-if="client.email">
                                <label class="text-sm font-medium text-muted-foreground flex items-center gap-1">
                                    <Mail class="w-4 h-4" />
                                    Email
                                </label>
                                <p>{{ client.email }}</p>
                            </div>

                            <div v-if="client.phone">
                                <label class="text-sm font-medium text-muted-foreground flex items-center gap-1">
                                    <Phone class="w-4 h-4" />
                                    Phone
                                </label>
                                <p>{{ client.phone }}</p>
                            </div>
                        </div>

                        <div v-if="client.address || client.city || client.state || client.postal_code || client.country">
                            <label class="text-sm font-medium text-muted-foreground flex items-center gap-1">
                                <MapPin class="w-4 h-4" />
                                Address
                            </label>
                            <div class="text-sm space-y-1">
                                <p v-if="client.address">{{ client.address }}</p>
                                <p v-if="client.city || client.state || client.postal_code">
                                    {{ [client.city, client.state, client.postal_code].filter(Boolean).join(', ') }}
                                </p>
                                <p v-if="client.country">{{ client.country }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Statistics -->
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Client Name</label>
                            <p class="text-lg font-semibold">{{ client.name }}</p>
                        </div>

                        <div v-if="client.trn_number">
                            <label class="text-sm font-medium text-muted-foreground">TRN Number</label>
                            <p class="text-base">{{ client.trn_number }}</p>
                        </div>

                        <div v-if="client.company_name">
                            <label class="text-sm font-medium text-muted-foreground flex items-center gap-1">
                                <Building class="w-4 h-4" />
                                Company
                            </label>
                            <p class="text-base">{{ client.company_name }}</p>
                        </div>

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <DollarSign class="w-5 h-5" />
                            Revenue
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(totalInvoiceValue) }}</div>
                        <p class="text-xs text-muted-foreground">Total invoiced</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Projects -->
            <Card v-if="client.projects.length > 0">
                <CardHeader>
                    <CardTitle>Projects</CardTitle>
                    <CardDescription>Projects associated with this client</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div 
                            v-for="project in client.projects" 
                            :key="project.id"
                            class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            <div class="flex-1">
                                <h4 class="font-medium">{{ project.name }}</h4>
                                <p v-if="project.description" class="text-sm text-muted-foreground">{{ project.description }}</p>
                                <p class="text-xs text-muted-foreground mt-1">Created: {{ formatDate(project.created_at) }}</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <Badge :variant="getStatusColor(project.status)">
                                    {{ project.status.replace('_', ' ').replace(/\b\w/g, (l: string) => l.toUpperCase()) }}
                                </Badge>
                                <Button variant="outline" size="sm" as-child>
                                    <Link :href="`/projects/${project.id}`">View</Link>
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Invoices -->
            <Card v-if="client.invoices.length > 0">
                <CardHeader>
                    <CardTitle>Recent Invoices</CardTitle>
                    <CardDescription>Invoices for this client</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div 
                            v-for="invoice in client.invoices.slice(0, 5)" 
                            :key="invoice.id"
                            class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            <div class="flex-1">
                                <h4 class="font-medium">{{ invoice.invoice_number }}</h4>
                                <div class="text-sm text-muted-foreground space-y-1">
                                    <p>Issue Date: {{ formatDate(invoice.issue_date) }}</p>
                                    <p>Due Date: {{ formatDate(invoice.due_date) }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="text-right">
                                    <p class="font-semibold">{{ formatCurrency(invoice.total_amount) }}</p>
                                    <Badge :variant="getStatusColor(invoice.status)" class="text-xs">
                                        {{ invoice.status.charAt(0).toUpperCase() + invoice.status.slice(1) }}
                                    </Badge>
                                </div>
                                <Button variant="outline" size="sm" as-child>
                                    <Link :href="`/invoices/${invoice.id}`">View</Link>
                                </Button>
                            </div>
                        </div>
                        <div v-if="client.invoices.length > 5" class="text-center pt-4">
                            <Button variant="outline" as-child>
                                <Link :href="`/invoices?client_id=${client.id}`">View All Invoices</Link>
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Quotations -->
            <Card v-if="client.quotations.length > 0">
                <CardHeader>
                    <CardTitle>Recent Quotations</CardTitle>
                    <CardDescription>Quotations for this client</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div 
                            v-for="quotation in client.quotations.slice(0, 5)" 
                            :key="quotation.id"
                            class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            <div class="flex-1">
                                <h4 class="font-medium">{{ quotation.quotation_number }}</h4>
                                <div class="text-sm text-muted-foreground space-y-1">
                                    <p>Issue Date: {{ formatDate(quotation.issue_date) }}</p>
                                    <p>Expiry Date: {{ formatDate(quotation.expiry_date) }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="text-right">
                                    <p class="font-semibold">{{ formatCurrency(quotation.total_amount) }}</p>
                                    <Badge :variant="getStatusColor(quotation.status)" class="text-xs">
                                        {{ quotation.status.charAt(0).toUpperCase() + quotation.status.slice(1) }}
                                    </Badge>
                                </div>
                                <Button variant="outline" size="sm" as-child>
                                    <Link :href="`/quotations/${quotation.id}`">View</Link>
                                </Button>
                            </div>
                        </div>
                        <div v-if="client.quotations.length > 5" class="text-center pt-4">
                            <Button variant="outline" as-child>
                                <Link :href="`/quotations?client_id=${client.id}`">View All Quotations</Link>
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Notes -->
            <Card v-if="client.notes">
                <CardHeader>
                    <CardTitle>Notes</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="whitespace-pre-wrap">{{ client.notes }}</div>
                </CardContent>
            </Card>

            <!-- Quick Actions -->
            <Card>
                <CardHeader>
                    <CardTitle>Quick Actions</CardTitle>
                    <CardDescription>Common tasks for this client</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-wrap gap-2">
                        <Button variant="outline" as-child>
                            <Link :href="`/projects/create?client_id=${client.id}`">Create Project</Link>
                        </Button>
                        <Button variant="outline" as-child>
                            <Link :href="`/invoices/create?client_id=${client.id}`">Create Invoice</Link>
                        </Button>
                        <Button variant="outline" as-child>
                            <Link :href="`/quotations/create?client_id=${client.id}`">Create Quotation</Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
