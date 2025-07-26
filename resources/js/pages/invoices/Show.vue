<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { ArrowLeft, Edit, Trash2, Mail, Copy, Download, FileText, CheckCircle, DollarSign } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import { usePdfGenerator } from '@/composables/usePdfGenerator';
import EmailDialog from '@/components/EmailDialog.vue';
import { ref, computed } from 'vue';
import type { Invoice as InvoiceType } from '@/types';

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
    status: string;
}

interface InvoiceItem {
    id: number;
    description: string;
    quantity: number;
    unit_price: number;
    vat_rate: number;
    total_price: number;
    total_price_w_tax: number;
}

interface PaymentSchedule {
    id: number;
    amount: number;
    due_date: string;
    status: string;
    description?: string;
    payments?: Payment[];
}

interface Payment {
    id: number;
    amount: number;
    payment_method: string;
    payment_date: string;
    status: string;
    notes?: string;
    receipt_path?: string;
}

interface Invoice {
    id: number;
    invoice_number: string;
    client_id: number;
    project_id?: number;
    issue_date: string;
    due_date: string;
    status: string;
    subtotal: string;
    tax_rate?: string;
    tax_amount?: string;
    discount_amount?: string;
    total_amount: string;
    notes?: string;
    payment_terms?: string;
    client: Client;
    project?: Project;
    items: InvoiceItem[];
    payment_schedules?: PaymentSchedule[];
    created_at: string;
    updated_at: string;
}

interface Props {
    invoice: Invoice;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Invoices',
        href: '/invoices',
    },
    {
        title: props.invoice.invoice_number,
        href: `/invoices/${props.invoice.id}`,
    },
];

const form = useForm({});
const { success, error } = useToast();
const { generateInvoicePdf } = usePdfGenerator();

// Email dialog state
const showEmailDialog = ref(false);

// Convert local Invoice to InvoiceType for the components that need it
const convertToInvoiceType = (): InvoiceType => ({
    ...props.invoice,
    client: {
        id: props.invoice.client.id,
        name: props.invoice.client.name,
        email: props.invoice.client.email || '',
        phone: props.invoice.client.phone,
        address: props.invoice.client.address,
        company: props.invoice.client.company,
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString(),
    },
    items: props.invoice.items.map(item => ({
        id: item.id,
        invoice_id: props.invoice.id,
        product_id: 1, // fallback
        quantity: item.quantity,
        unit_price: String(item.unit_price),
        vat_rate: item.vat_rate,
        total_price: item.total_price,
        total_price_w_tax: item.total_price_w_tax,
        product: {
            id: 1, // fallback
            name: item.description,
            description: '',
            sku: '',
            price: String(item.unit_price),
            cost_price: '0',
            stock_quantity: 0,
            min_stock_level: 0,
            category: '',
            is_active: true,
            created_at: new Date().toISOString(),
            updated_at: new Date().toISOString(),
        }
    }))
});

const deleteInvoice = () => {
    if (confirm('Are you sure you want to delete this invoice? This action cannot be undone.')) {
        form.delete(`/invoices/${props.invoice.id}`, {
            onSuccess: () => {
                success('Success!', 'Invoice deleted successfully');
            },
            onError: () => {
                error('Error!', 'Failed to delete invoice.');
            }
        });
    }
};

const sendInvoice = () => {
    showEmailDialog.value = true;
};

const downloadPdf = async () => {
    try {
        const result = await generateInvoicePdf(convertToInvoiceType());
        if (result.success) {
            success('Success!', `PDF downloaded as ${result.filename}`);
        } else {
            throw new Error(result.error);
        }
    } catch (err) {
        error('Error!', 'Failed to generate PDF.');
    }
};

const downloadPaymentPdf = async (payment: Payment) => {
    try {
        const response = await fetch(`/invoices/${props.invoice.id}/payments/${payment.id}/download-pdf`, {
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
        const filename = contentDisposition?.match(/filename="(.+)"/)?.[1] || `invoice-${props.invoice.invoice_number}-payment-${payment.id}.pdf`;

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

const downloadSchedulePdf = async (schedule: PaymentSchedule) => {
    try {
        const response = await fetch(`/invoices/${props.invoice.id}/schedules/${schedule.id}/download-pdf`, {
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
        const filename = contentDisposition?.match(/filename="(.+)"/)?.[1] || `invoice-${props.invoice.invoice_number}-schedule-${schedule.id}.pdf`;

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

const duplicateInvoice = () => {
    form.post(`/invoices/${props.invoice.id}/duplicate`, {
        onSuccess: () => {
            success('Success!', 'Invoice duplicated successfully');
        },
        onError: () => {
            error('Error!', 'Failed to duplicate invoice.');
        }
    });
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
        case 'draft': return 'secondary';
        case 'sent': return 'default';
        case 'paid': return 'default';
        case 'overdue': return 'destructive';
        case 'cancelled': return 'outline';
        default: return 'secondary';
    }
};

const isOverdue = computed(() => {
    if (props.invoice.status === 'paid' || props.invoice.status === 'cancelled') return false;
    return new Date(props.invoice.due_date) < new Date();
});

const daysUntilDue = computed(() => {
    const today = new Date();
    const dueDate = new Date(props.invoice.due_date);
    const timeDiff = dueDate.getTime() - today.getTime();
    const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
    return daysDiff;
});

// Helper functions for payments
const getAllPayments = () => {
    if (!props.invoice.payment_schedules) return [];
    return props.invoice.payment_schedules
        .flatMap(schedule => schedule.payments || [])
        .sort((a, b) => new Date(b.payment_date).getTime() - new Date(a.payment_date).getTime());
};

const getTotalPaid = () => {
    return getAllPayments().reduce((sum, payment) => sum + payment.amount, 0);
};
</script>

<template>
    <Head :title="invoice.invoice_number" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Button variant="outline" size="sm" as-child>
                        <Link href="/invoices">
                            <ArrowLeft class="w-4 h-4 mr-2" />
                            Back to Invoices
                        </Link>
                    </Button>
                    <div>
                        <div class="flex items-center space-x-2">
                            <h1 class="text-3xl font-bold tracking-tight">{{ invoice.invoice_number }}</h1>
                            <Badge :variant="getStatusColor(invoice.status)">
                                {{ invoice.status.charAt(0).toUpperCase() + invoice.status.slice(1) }}
                            </Badge>
                            <Badge v-if="isOverdue" variant="destructive">
                                {{ Math.abs(daysUntilDue) }} days overdue
                            </Badge>
                            <Badge v-else-if="daysUntilDue <= 7 && invoice.status !== 'paid'" variant="outline">
                                Due in {{ daysUntilDue }} days
                            </Badge>
                        </div>
                        <p class="text-muted-foreground">Invoice details and status</p>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <Button variant="outline" size="sm" @click="duplicateInvoice" :disabled="form.processing">
                        <Copy class="w-4 h-4 mr-2" />
                        Duplicate
                    </Button>
                    <!-- <Button variant="outline" size="sm" @click="sendInvoice" :disabled="form.processing">
                        <Mail class="w-4 h-4 mr-2" />
                        Send Email
                    </Button> -->
                    <Button variant="outline" as-child>
                        <Link :href="`/invoices/${invoice.id}/edit`">
                            <Edit class="w-4 h-4 mr-2" />
                            Edit
                        </Link>
                    </Button>
                    <Button variant="destructive" @click="deleteInvoice" :disabled="form.processing">
                        <Trash2 class="w-4 h-4 mr-2" />
                        Delete
                    </Button>
                </div>
            </div>

            <!-- Invoice Overview -->
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Client Information -->
                <Card>
                    <CardHeader>
                        <CardTitle>Bill To</CardTitle>
                        <CardDescription>Client information</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div class="font-semibold">{{ invoice.client.name }}</div>
                        <div v-if="invoice.client.company" class="text-sm text-muted-foreground">
                            {{ invoice.client.company }}
                        </div>
                        <div v-if="invoice.client.email" class="text-sm">{{ invoice.client.email }}</div>
                        <div v-if="invoice.client.phone" class="text-sm">{{ invoice.client.phone }}</div>
                        <div v-if="invoice.client.address" class="text-sm text-muted-foreground">
                            {{ invoice.client.address }}
                        </div>
                    </CardContent>
                </Card>

                <!-- Invoice Details -->
                <Card>
                    <CardHeader>
                        <CardTitle>Invoice Details</CardTitle>
                        <CardDescription>Invoice information</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-muted-foreground">Invoice Number:</span>
                            <span class="font-medium">{{ invoice.invoice_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-muted-foreground">Issue Date:</span>
                            <span>{{ formatDate(invoice.issue_date) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-muted-foreground">Due Date:</span>
                            <span>{{ formatDate(invoice.due_date) }}</span>
                        </div>
                        <div v-if="invoice.project" class="flex justify-between">
                            <span class="text-sm text-muted-foreground">Project:</span>
                            <Link :href="`/projects/${invoice.project.id}`" class="text-blue-600 hover:underline">
                                {{ invoice.project.name }}
                            </Link>
                        </div>
                        <div v-if="invoice.payment_terms" class="flex justify-between">
                            <span class="text-sm text-muted-foreground">Payment Terms:</span>
                            <span>{{ invoice.payment_terms.replace('_', ' ').toUpperCase() }}</span>
                        </div>
                        <Separator />
                        <div class="flex justify-between text-lg font-semibold">
                            <span>Total Amount:</span>
                            <span>{{ formatCurrency(invoice.total_amount) }}</span>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Invoice Items -->
            <Card>
                <CardHeader>
                    <CardTitle>Invoice Items</CardTitle>
                    <CardDescription>Products and services included in this invoice</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left p-4 font-medium">Description</th>
                                    <th class="text-right p-4 font-medium">Quantity</th>
                                    <th class="text-right p-4 font-medium">Unit Price</th>
                                    <th class="text-right p-4 font-medium">Vat %</th>

                                    <th class="text-right p-4 font-medium">Total (without Tax)</th>
                                    <th class="text-right p-4 font-medium">Total (w Tax)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in invoice.items" :key="item.id" class="border-b">
                                    <td class="p-4">{{ item.description }}</td>
                                    <td class="p-4 text-right">{{ item.quantity }}</td>
                                    <td class="p-4 text-right">{{ formatCurrency(item.unit_price) }}</td>
                                    <td class="p-4 text-right">{{ item.vat_rate }}%</td>
                                    <td class="p-4 text-right">{{ formatCurrency(item.total_price) }}</td>
                                    <td class="p-4 text-right font-medium">{{ formatCurrency(item.total_price_w_tax) }}</td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <!-- Invoice Totals -->
                        <div class="mt-6 space-y-2">
                            <div class="flex justify-between text-right">
                                <span></span>
                                <div class="w-64 space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-muted-foreground">Subtotal:</span>
                                        <span>
                                            {{
                                                formatCurrency(
                                                    invoice.items.reduce((sum, item) => sum + item.total_price, 0)
                                                )
                                            }}
                                        </span>
                                    </div>
                                    
                                    <div v-if="invoice.items.some(item => item.vat_rate > 0)" class="flex justify-between">
                                        <span class="text-muted-foreground">
                                            Tax:
                                        </span>
                                        <span>
                                            {{
                                                formatCurrency(
                                                    invoice.tax_amount ? parseFloat(invoice.tax_amount) : 0
                                                )
                                            }}
                                        </span>
                                    </div>
                                    
                                    <div v-if="invoice.discount_amount && parseFloat(invoice.discount_amount) > 0" class="flex justify-between">
                                        <span class="text-muted-foreground">Discount:</span>
                                        <span>-{{ formatCurrency(invoice.discount_amount) }}</span>
                                    </div>
                                    
                                    <Separator />
                                    
                                    <div class="flex justify-between text-lg font-bold">
                                        <span>Total:</span>
                                        <span>{{ formatCurrency(invoice.total_amount) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Payments Summary
            <Card v-if="getAllPayments().length > 0">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <DollarSign class="w-5 h-5" />
                        Payments Received
                    </CardTitle>
                    <CardDescription>
                        Total received: {{ formatCurrency(getTotalPaid()) }} of {{ formatCurrency(invoice.total_amount) }}
                        ({{ ((getTotalPaid() / parseFloat(invoice.total_amount)) * 100).toFixed(1) }}%)
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-3">
                        <div 
                            v-for="payment in getAllPayments()" 
                            :key="payment.id"
                            class="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            <div class="flex items-center gap-4">
                                <div class="text-lg font-semibold text-green-600">
                                    {{ formatCurrency(payment.amount) }}
                                </div>
                                <div class="text-sm text-gray-600">
                                    <div>{{ payment.payment_method.replace(/_/g, ' ').replace(/\b\w/g, (l: string) => l.toUpperCase()) }}</div>
                                    <div>{{ formatDate(payment.payment_date) }}</div>
                                </div>
                                <Badge variant="default" class="text-xs">
                                    {{ payment.status }}
                                </Badge>
                                <div v-if="payment.receipt_path" class="flex items-center gap-1 text-blue-600">
                                    <FileText class="w-4 h-4" />
                                    <span class="text-xs">Receipt</span>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <Button 
                                    v-if="payment.receipt_path"
                                    size="sm" 
                                    variant="outline"
                                    as-child
                                >
                                    <a :href="`/storage/${payment.receipt_path}`" target="_blank">
                                        View Receipt
                                    </a>
                                </Button>
                                <Button 
                                    size="sm" 
                                    variant="outline"
                                    @click="downloadPaymentPdf(payment)"
                                    :disabled="form.processing"
                                >
                                    <Download class="w-4 h-4 mr-2" />
                                    Download PDF
                                </Button>
                                <Button 
                                    size="sm" 
                                    variant="outline"
                                    as-child
                                >
                                    <Link :href="`/payments/${payment.id}`">
                                        View Details
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card> -->

            <!-- Payment Schedules -->
            <Card v-if="invoice.payment_schedules && invoice.payment_schedules.length > 0">
                <CardHeader>
                    <CardTitle>Payment Schedules</CardTitle>
                    <CardDescription>Configured payment schedule for this invoice</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div 
                            v-for="schedule in invoice.payment_schedules" 
                            :key="schedule.id"
                            class="border rounded-lg p-4"
                        >
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-3">
                                    <h4 class="font-medium">Schedule #{{ schedule.id }}</h4>
                                    <Badge 
                                        :variant="schedule.status === 'paid' ? 'success' : schedule.status === 'partial' ? 'secondary' : 'outline'"
                                    >
                                        {{ schedule.status.charAt(0).toUpperCase() + schedule.status.slice(1) }}
                                    </Badge>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold">{{ formatCurrency(schedule.amount) }}</div>
                                    <div class="text-sm text-muted-foreground">
                                        Due: {{ formatDate(schedule.due_date) }}
                                    </div>
                                </div>
                            </div>
                            
                            <div v-if="schedule.description" class="text-sm text-muted-foreground mb-3">
                                {{ schedule.description }}
                            </div>

                            <!-- Payments for this schedule -->
                            <div v-if="schedule.payments && schedule.payments.length > 0" class="mb-3">
                                <h5 class="text-sm font-medium mb-2">Payments Received:</h5>
                                <div class="space-y-2">
                                    <div 
                                        v-for="payment in schedule.payments" 
                                        :key="payment.id"
                                        class="border rounded-lg p-3 hover:bg-gray-50 transition-colors"
                                    >
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-3 mb-2">
                                                    <span class="font-semibold text-lg text-green-600">
                                                        {{ formatCurrency(payment.amount) }}
                                                    </span>
                                                    <Badge 
                                                        variant="default" 
                                                        class="text-xs"
                                                    >
                                                        {{ payment.status }}
                                                    </Badge>
                                                </div>
                                                <div class="text-sm text-gray-600 space-y-1">
                                                    <div class="flex items-center gap-4">
                                                        <span>
                                                            <strong>Method:</strong> {{ payment.payment_method.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                                                        </span>
                                                        <span>
                                                            <strong>Date:</strong> {{ formatDate(payment.payment_date) }}
                                                        </span>
                                                    </div>
                                                    <div v-if="payment.notes" class="text-sm">
                                                        <strong>Notes:</strong> {{ payment.notes }}
                                                    </div>
                                                    <div v-if="payment.receipt_path" class="flex items-center gap-2">
                                                        <FileText class="w-4 h-4" />
                                                        <a 
                                                            :href="`/storage/${payment.receipt_path}`" 
                                                            target="_blank"
                                                            class="text-blue-600 hover:text-blue-800 underline text-sm"
                                                        >
                                                            View Receipt
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex gap-2">
                                                <!-- <Button 
                                                    size="sm" 
                                                    variant="outline"
                                                    @click="downloadPaymentPdf(payment)"
                                                    :disabled="form.processing"
                                                >
                                                    <Download class="w-4 h-4 mr-2" />
                                                    Download PDF
                                                </Button> -->
                                                <Button 
                                                    size="sm" 
                                                    variant="outline"
                                                    as-child
                                                >
                                                    <Link :href="`/payments/${payment.id}`">
                                                        View Details
                                                    </Link>
                                                </Button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment action button -->
                            <div class="flex justify-between items-center pt-2 border-t">
                                <div class="text-sm">
                                    <span class="text-muted-foreground">Paid: </span>
                                    <span class="font-medium">
                                        {{ formatCurrency(schedule.payments?.reduce((sum: number, p: Payment) => sum + p.amount, 0) || 0) }}
                                    </span>
                                    <span class="text-muted-foreground ml-2">of {{ formatCurrency(schedule.amount) }}</span>
                                </div>
                                <div class="flex gap-2">
                                    <!-- Show invoice actions only for unpaid schedules -->
                                    <template v-if="schedule.status !== 'paid'">
                                        <Button 
                                            size="sm" 
                                            variant="outline"
                                            @click="downloadSchedulePdf(schedule)"
                                            :disabled="form.processing"
                                        >
                                            <Download class="w-4 h-4 mr-2" />
                                            Download Invoice
                                        </Button>
                                        <Button 
                                            size="sm" 
                                            variant="outline"
                                            @click="sendInvoice"
                                            :disabled="form.processing"
                                        >
                                            <Mail class="w-4 h-4 mr-2" />
                                            Send Invoice
                                        </Button>
                                    </template>
                                    <Button 
                                        v-if="schedule.status !== 'paid'"
                                        size="sm" 
                                        variant="outline"
                                        as-child
                                    >
                                        <Link :href="`/payment-schedules/${schedule.id}/pay`">
                                            <DollarSign class="w-4 h-4 mr-2" />
                                            Record Payment
                                        </Link>
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Notes -->
            <Card v-if="invoice.notes">
                <CardHeader>
                    <CardTitle>Notes</CardTitle>
                    <CardDescription>Additional terms and conditions</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="whitespace-pre-wrap">{{ invoice.notes }}</div>
                </CardContent>
            </Card>
        </div>

        <!-- Email Dialog -->
        <EmailDialog
            v-model:open="showEmailDialog"
            type="invoice"
            :data="convertToInvoiceType()"
            @close="showEmailDialog = false"
        />
    </AppLayout>
</template>
