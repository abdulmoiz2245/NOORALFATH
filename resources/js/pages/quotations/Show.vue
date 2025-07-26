<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import { 
    ArrowLeft, 
    Edit3, 
    Copy,
    Mail,
    Download,
    Calendar,
    DollarSign,
    FileText
} from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';

interface Client {
    id: number;
    name: string;
    email: string;
    company?: string;
    phone?: string;
    address?: string;
}

interface Project {
    id: number;
    name: string;
    description?: string;
}

interface QuotationItem {
    id: number;
    description: string;
    quantity: number;
    unit_price: number;
    total: number;
}

interface Quotation {
    id: number;
    quotation_number: string;
    client_id: number;
    project_id?: number;
    status: string;
    issue_date: string;
    valid_until?: string;
    subtotal: number;
    tax_rate: number;
    tax_amount: number;
    discount_amount: number;
    total: number;
    notes?: string;
    terms?: string;
    created_at: string;
    updated_at: string;
    client: Client;
    project?: Project;
    items: QuotationItem[];
}

interface Props {
    quotation: Quotation;
}

const props = defineProps<Props>();
const { success, error } = useToast();

const form = useForm({});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Quotations',
        href: '/quotations',
    },
    {
        title: props.quotation.quotation_number,
        href: `/quotations/${props.quotation.id}`,
    },
];

const sendQuotation = () => {
    form.post(`/quotations/${props.quotation.id}/send`, {
        onSuccess: () => {
            success('Success!', 'Quotation sent successfully');
        },
        onError: () => {
            error('Error!', 'Failed to send quotation');
        }
    });
};

const duplicateQuotation = () => {
    form.post(`/quotations/${props.quotation.id}/duplicate`, {
        onSuccess: () => {
            success('Success!', 'Quotation duplicated successfully');
        },
        onError: () => {
            error('Error!', 'Failed to duplicate quotation');
        }
    });
};

const downloadPdf = () => {
    window.open(`/quotations/${props.quotation.id}/pdf`, '_blank');
};

const getStatusBadgeClass = (status: string) => {
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
        currency: 'AED'
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

const isExpired = () => {
    if (!props.quotation.valid_until) return false;
    return new Date(props.quotation.valid_until) < new Date();
};
</script>

<template>
    <Head :title="quotation.quotation_number" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Button variant="outline" size="sm" as-child>
                        <Link href="/quotations">
                            <ArrowLeft class="w-4 h-4 mr-2" />
                            Back to Quotations
                        </Link>
                    </Button>
                    <div>
                        <div class="flex items-center space-x-3">
                            <h1 class="text-3xl font-bold tracking-tight">{{ quotation.quotation_number }}</h1>
                            <Badge :class="getStatusBadgeClass(quotation.status)">{{ quotation.status }}</Badge>
                            <Badge v-if="isExpired()" variant="destructive">Expired</Badge>
                        </div>
                        <p class="text-muted-foreground">{{ quotation.client.name }}{{ quotation.client.company ? ` • ${quotation.client.company}` : '' }}</p>
                    </div>
                </div>

                <div class="flex space-x-2">
                    <!-- <Button variant="outline" size="sm" @click="duplicateQuotation" :disabled="form.processing">
                        <Copy class="w-4 h-4 mr-2" />
                        Duplicate
                    </Button>
                    <Button variant="outline" size="sm" @click="sendQuotation" :disabled="form.processing">
                        <Mail class="w-4 h-4 mr-2" />
                        Send Email
                    </Button>
                    <Button variant="outline" size="sm" @click="downloadPdf" :disabled="form.processing">
                        <Download class="w-4 h-4 mr-2" />
                        Download PDF
                    </Button> -->
                    <Button variant="outline" as-child>
                        <Link :href="`/quotations/${quotation.id}/edit`">
                            <Edit3 class="w-4 h-4 mr-2" />
                            Edit
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Quotation Overview -->
            <div class="grid gap-6 md:grid-cols-3">
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-2">
                            <DollarSign class="w-5 h-5 text-green-600" />
                            <div>
                                <p class="text-2xl font-bold">{{ formatCurrency(quotation.total) }}</p>
                                <p class="text-sm text-gray-500">Total Amount</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-2">
                            <Calendar class="w-5 h-5 text-blue-600" />
                            <div>
                                <p class="text-2xl font-bold">{{ quotation.valid_until ? formatDate(quotation.valid_until) : 'No expiry' }}</p>
                                <p class="text-sm text-gray-500">Valid Until</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-2">
                            <FileText class="w-5 h-5 text-purple-600" />
                            <div>
                                <p class="text-2xl font-bold">{{ quotation.items.length }}</p>
                                <p class="text-sm text-gray-500">Items</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Main Content -->
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Quotation Details -->
                <Card>
                    <CardHeader>
                        <CardTitle>Quotation Details</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label class="text-sm font-medium text-gray-500">Issue Date</Label>
                                <p class="mt-1">{{ formatDateLong(quotation.issue_date) }}</p>
                            </div>
                            <div>
                                <Label class="text-sm font-medium text-gray-500">Valid Until</Label>
                                <p class="mt-1" :class="isExpired() ? 'text-red-600 font-medium' : ''">
                                    {{ quotation.valid_until ? formatDateLong(quotation.valid_until) : 'No expiry date' }}
                                </p>
                            </div>
                        </div>

                        <div v-if="quotation.project">
                            <Label class="text-sm font-medium text-gray-500">Project</Label>
                            <div class="mt-1">
                                <Link :href="`/projects/${quotation.project.id}`" class="text-blue-600 hover:underline">
                                    {{ quotation.project.name }}
                                </Link>
                            </div>
                        </div>

                        <div v-if="quotation.notes">
                            <Label class="text-sm font-medium text-gray-500">Notes</Label>
                            <p class="mt-1 whitespace-pre-line">{{ quotation.notes }}</p>
                        </div>

                        <div v-if="quotation.terms">
                            <Label class="text-sm font-medium text-gray-500">Terms & Conditions</Label>
                            <p class="mt-1 whitespace-pre-line">{{ quotation.terms }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Client Information -->
                <Card>
                    <CardHeader>
                        <CardTitle>Client Information</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <Label class="text-sm font-medium text-gray-500">Client Name</Label>
                            <div class="mt-1">
                                <Link :href="`/clients/${quotation.client.id}`" class="text-blue-600 hover:underline font-medium">
                                    {{ quotation.client.name }}
                                </Link>
                                <p v-if="quotation.client.company" class="text-sm text-gray-500">{{ quotation.client.company }}</p>
                            </div>
                        </div>

                        <div v-if="quotation.client.email">
                            <Label class="text-sm font-medium text-gray-500">Email</Label>
                            <p class="mt-1">
                                <a :href="`mailto:${quotation.client.email}`" class="text-blue-600 hover:underline">
                                    {{ quotation.client.email }}
                                </a>
                            </p>
                        </div>

                        <div v-if="quotation.client.phone">
                            <Label class="text-sm font-medium text-gray-500">Phone</Label>
                            <p class="mt-1">
                                <a :href="`tel:${quotation.client.phone}`" class="text-blue-600 hover:underline">
                                    {{ quotation.client.phone }}
                                </a>
                            </p>
                        </div>

                        <div v-if="quotation.client.address">
                            <Label class="text-sm font-medium text-gray-500">Address</Label>
                            <p class="mt-1 whitespace-pre-line">{{ quotation.client.address }}</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Items -->
            <Card>
                <CardHeader>
                    <CardTitle>Quotation Items</CardTitle>
                    <CardDescription>Items and services included in this quotation</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left p-4">Description</th>
                                    <th class="text-right p-4 w-24">Quantity</th>
                                    <th class="text-right p-4 w-32">Unit Price</th>
                                    <th class="text-right p-4 w-32">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in quotation.items" :key="item.id" class="border-b">
                                    <td class="p-4">{{ item.description }}</td>
                                    <td class="p-4 text-right">{{ item.quantity }}</td>
                                    <td class="p-4 text-right">{{ formatCurrency(item.unit_price) }}</td>
                                    <td class="p-4 text-right font-medium">{{ formatCurrency(item.total) }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="border-b">
                                    <td colspan="3" class="p-4 text-right font-medium">Subtotal:</td>
                                    <td class="p-4 text-right">{{ formatCurrency(quotation.subtotal) }}</td>
                                </tr>
                                <tr v-if="quotation.discount_amount > 0" class="border-b">
                                    <td colspan="3" class="p-4 text-right font-medium">Discount:</td>
                                    <td class="p-4 text-right text-red-600">-{{ formatCurrency(quotation.discount_amount) }}</td>
                                </tr>
                                <tr v-if="quotation.tax_amount > 0" class="border-b">
                                    <td colspan="3" class="p-4 text-right font-medium">Tax ({{ quotation.tax_rate }}%):</td>
                                    <td class="p-4 text-right">{{ formatCurrency(quotation.tax_amount) }}</td>
                                </tr>
                                <tr class="border-b-2 border-gray-800">
                                    <td colspan="3" class="p-4 text-right text-lg font-bold">Total:</td>
                                    <td class="p-4 text-right text-lg font-bold">{{ formatCurrency(quotation.total) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <!-- Timeline -->
            <Card>
                <CardHeader>
                    <CardTitle>Timeline</CardTitle>
                    <CardDescription>Important dates for this quotation</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
                            <div>
                                <p class="font-medium">Created</p>
                                <p class="text-sm text-gray-500">{{ formatDateLong(quotation.created_at) }}</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-green-600 rounded-full"></div>
                            <div>
                                <p class="font-medium">Issue Date</p>
                                <p class="text-sm text-gray-500">{{ formatDateLong(quotation.issue_date) }}</p>
                            </div>
                        </div>

                        <div v-if="quotation.valid_until" class="flex items-center space-x-3">
                            <div :class="`w-2 h-2 rounded-full ${isExpired() ? 'bg-red-600' : 'bg-orange-600'}`"></div>
                            <div>
                                <p class="font-medium">Valid Until</p>
                                <p class="text-sm" :class="isExpired() ? 'text-red-600' : 'text-gray-500'">
                                    {{ formatDateLong(quotation.valid_until) }}
                                    <span v-if="isExpired()" class="font-medium"> (Expired)</span>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-purple-600 rounded-full"></div>
                            <div>
                                <p class="font-medium">Last Updated</p>
                                <p class="text-sm text-gray-500">{{ formatDateLong(quotation.updated_at) }}</p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
