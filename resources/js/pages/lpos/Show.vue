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

interface Vendor {
    id: number;
    name: string;
    email: string;
    company?: string;
    phone?: string;
    address?: string;
}

interface LpoItem {
    id: number;
    description: string;
    quantity: number;
    unit_price: number;
    tax_rate?: number;
    total_price_before_tax: number;
    total_price_after_tax: number;
}

interface LocalPurchaseOrder {
    id: number;
    lpo_number: string;
    vendor_id: number;
    issue_date: string;
    subject?: string;
    description?: string;
    trn_number?: string;
    terms?: string;
    subtotal: number;
    total_tax: number;
    total_before_tax: number;
    total_after_tax: number;
    created_at: string;
    updated_at: string;
    vendor: Vendor;
    items: LpoItem[];
}

interface Props {
    lpo: LocalPurchaseOrder;
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
        title: 'Local Purchase Orders',
        href: '/lpos',
    },
    {
        title: props.lpo.lpo_number,
        href: `/lpos/${props.lpo.id}`,
    },
];

const sendLpo = () => {
    form.post(`/lpos/${props.lpo.id}/send`, {
        onSuccess: () => {
            success('Success!', 'LPO sent successfully');
        },
        onError: () => {
            error('Error!', 'Failed to send LPO');
        }
    });
};

const duplicateLpo = () => {
    form.post(`/lpos/${props.lpo.id}/duplicate`, {
        onSuccess: () => {
            success('Success!', 'LPO duplicated successfully');
        },
        onError: () => {
            error('Error!', 'Failed to duplicate LPO');
        }
    });
};

const downloadPdf = async () => {
    try {
        const response = await fetch(`/lpos/${props.lpo.id}/download-pdf`, {
            method: 'GET',
            headers: {
                'Accept': 'application/pdf',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        if (!response.ok) {
            throw new Error('Failed to download PDF');
        }

        // Get the filename from response headers or use default
        const contentDisposition = response.headers.get('Content-Disposition');
        const filename = contentDisposition?.match(/filename="(.+)"/)?.[1] || `lpo-${props.lpo.lpo_number}.pdf`;

        // Create blob and download
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);

        success('Success!', `PDF downloaded as ${filename}`);
    } catch (err) {
        error('Error!', 'Failed to download PDF.');
        console.error('Download error:', err);
    }
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
</script>

<template>
    <Head :title="lpo.lpo_number" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Button variant="outline" size="sm" as-child>
                        <Link href="/lpos">
                            <ArrowLeft class="w-4 h-4 mr-2" />
                            Back to LPOs
                        </Link>
                    </Button>
                    <div>
                        <div class="flex items-center space-x-3">
                            <h1 class="text-3xl font-bold tracking-tight">{{ lpo.lpo_number }}</h1>
                        </div>
                        <p class="text-muted-foreground">{{ lpo.vendor.name }}{{ lpo.vendor.company ? ` • ${lpo.vendor.company}` : '' }}</p>
                    </div>
                </div>

                <div class="flex space-x-2">
                    <!-- <Button variant="outline" size="sm" @click="duplicateLpo" :disabled="form.processing">
                        <Copy class="w-4 h-4 mr-2" />
                        Duplicate
                    </Button>
                    <Button variant="outline" size="sm" @click="sendLpo" :disabled="form.processing">
                        <Mail class="w-4 h-4 mr-2" />
                        Send Email
                    </Button>-->
                    <Button variant="outline" size="sm" @click="downloadPdf" :disabled="form.processing">
                        <Download class="w-4 h-4 mr-2" />
                        Download PDF
                    </Button> 
                    <Button variant="outline" as-child>
                        <Link :href="`/lpos/${lpo.id}/edit`">
                            <Edit3 class="w-4 h-4 mr-2" />
                            Edit
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- LPO Overview -->
            <div class="grid gap-6 md:grid-cols-3">
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-2">
                            <DollarSign class="w-5 h-5 text-green-600" />
                            <div>
                                <p class="text-2xl font-bold">{{ formatCurrency(lpo.total_after_tax) }}</p>
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
                                <p class="text-2xl font-bold">{{ formatDate(lpo.issue_date) }}</p>
                                <p class="text-sm text-gray-500">Issue Date</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-2">
                            <FileText class="w-5 h-5 text-purple-600" />
                            <div>
                                <p class="text-2xl font-bold">{{ lpo.items.length }}</p>
                                <p class="text-sm text-gray-500">Items</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Main Content -->
            <div class="grid gap-6 md:grid-cols-2">
                <!-- LPO Details -->
                <Card>
                    <CardHeader>
                        <CardTitle>LPO Details</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label class="text-sm font-medium text-gray-500">Issue Date</Label>
                                <p class="mt-1">{{ formatDateLong(lpo.issue_date) }}</p>
                            </div>
                            <div v-if="lpo.trn_number">
                                <Label class="text-sm font-medium text-gray-500">TRN Number</Label>
                                <p class="mt-1">{{ lpo.trn_number }}</p>
                            </div>
                        </div>

                        <div v-if="lpo.subject">
                            <Label class="text-sm font-medium text-gray-500">Subject</Label>
                            <p class="mt-1">{{ lpo.subject }}</p>
                        </div>

                        <div v-if="lpo.description">
                            <Label class="text-sm font-medium text-gray-500">Description</Label>
                            <p class="mt-1 whitespace-pre-line">{{ lpo.description }}</p>
                        </div>

                        <div v-if="lpo.terms">
                            <Label class="text-sm font-medium text-gray-500">Terms & Conditions</Label>
                            <p class="mt-1 whitespace-pre-line">{{ lpo.terms }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Vendor Information -->
                <Card>
                    <CardHeader>
                        <CardTitle>Vendor Information</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <Label class="text-sm font-medium text-gray-500">Vendor Name</Label>
                            <div class="mt-1">
                                <Link :href="`/vendors/${lpo.vendor.id}`" class="text-blue-600 hover:underline font-medium">
                                    {{ lpo.vendor.name }}
                                </Link>
                                <p v-if="lpo.vendor.company" class="text-sm text-gray-500">{{ lpo.vendor.company }}</p>
                            </div>
                        </div>

                        <div v-if="lpo.vendor.email">
                            <Label class="text-sm font-medium text-gray-500">Email</Label>
                            <p class="mt-1">
                                <a :href="`mailto:${lpo.vendor.email}`" class="text-blue-600 hover:underline">
                                    {{ lpo.vendor.email }}
                                </a>
                            </p>
                        </div>

                        <div v-if="lpo.vendor.phone">
                            <Label class="text-sm font-medium text-gray-500">Phone</Label>
                            <p class="mt-1">
                                <a :href="`tel:${lpo.vendor.phone}`" class="text-blue-600 hover:underline">
                                    {{ lpo.vendor.phone }}
                                </a>
                            </p>
                        </div>

                        <div v-if="lpo.vendor.address">
                            <Label class="text-sm font-medium text-gray-500">Address</Label>
                            <p class="mt-1 whitespace-pre-line">{{ lpo.vendor.address }}</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Items -->
            <Card>
                <CardHeader>
                    <CardTitle>LPO Items</CardTitle>
                    <CardDescription>Items and services included in this purchase order</CardDescription>
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
                                <tr v-for="item in lpo.items" :key="item.id" class="border-b">
                                    <td class="p-4">{{ item.description }}</td>
                                    <td class="p-4 text-right">{{ item.quantity }}</td>
                                    <td class="p-4 text-right">{{ formatCurrency(item.unit_price) }}</td>
                                    <td class="p-4 text-right font-medium">{{ formatCurrency(item.total_price_after_tax) }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="border-b">
                                    <td colspan="3" class="p-4 text-right font-medium">Subtotal:</td>
                                    <td class="p-4 text-right">{{ formatCurrency(lpo.subtotal) }}</td>
                                </tr>
                                <tr v-if="lpo.total_tax > 0" class="border-b">
                                    <td colspan="3" class="p-4 text-right font-medium">Tax:</td>
                                    <td class="p-4 text-right">{{ formatCurrency(lpo.total_tax) }}</td>
                                </tr>
                                <tr class="border-b-2 border-gray-800">
                                    <td colspan="3" class="p-4 text-right text-lg font-bold">Total:</td>
                                    <td class="p-4 text-right text-lg font-bold">{{ formatCurrency(lpo.total_after_tax) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <!-- Timeline -->
            <!-- <Card>
                <CardHeader>
                    <CardTitle>Timeline</CardTitle>
                    <CardDescription>Important dates for this LPO</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
                            <div>
                                <p class="font-medium">Created</p>
                                <p class="text-sm text-gray-500">{{ formatDateLong(lpo.created_at) }}</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-green-600 rounded-full"></div>
                            <div>
                                <p class="font-medium">Issue Date</p>
                                <p class="text-sm text-gray-500">{{ formatDateLong(lpo.issue_date) }}</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-purple-600 rounded-full"></div>
                            <div>
                                <p class="font-medium">Last Updated</p>
                                <p class="text-sm text-gray-500">{{ formatDateLong(lpo.updated_at) }}</p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card> -->
        </div>
    </AppLayout>
</template>
