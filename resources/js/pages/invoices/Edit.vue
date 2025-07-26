<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ArrowLeft, Save, Plus, Trash2, UserPlus } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import { computed } from 'vue';

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
    client_id: number;
    status: string;
}

interface Product {
    id: number;
    name: string;
    price: number;
    sku: string;
    description?: string;
}

interface InvoiceItem {
    id?: number;
    product_id?: number;
    description: string;
    quantity: number;
    unit_price: number;
    vat_rate: number;     // VAT rate percentage
    total_price: number;       // Total amount including VAT
    amount?: number; // Total amount without VAT
    tax?: number; // Calculated tax amount
}

interface InvoicePaymentSchedule {
    id?: number;
    description: string;
    percentage?: number;
    amount: number;
    due_date: string;
    status?: string;
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
    client: Client;
    project?: Project;
    items: InvoiceItem[];
    payment_schedules?: InvoicePaymentSchedule[];
    created_at: string;
    updated_at: string;
}

interface Props {
    invoice: Invoice;
    clients: Client[];
    projects: Project[];
    products: Product[];
}

const props = defineProps<Props>();
const { success, error } = useToast();

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
    {
        title: 'Edit',
        href: `/invoices/${props.invoice.id}/edit`,
    },
];

const form = useForm({
    client_id: props.invoice.client_id.toString(),
    project_id: props.invoice.project_id?.toString() || '',
    issue_date: props.invoice.issue_date ? props.invoice.issue_date.split('T')[0] : '',
    due_date: props.invoice.due_date ? props.invoice.due_date.split('T')[0] : '',
    status: props.invoice.status || 'draft',
    notes: props.invoice.notes || '',
    tax_rate: props.invoice.tax_rate || '5',
    discount_amount: props.invoice.discount_amount || '0',
    items: props.invoice.items.map(item => ({
        id: item.id,
        product_id: item.product_id?.toString() || '',
        description: item.description,
        quantity: item.quantity,
        unit_price: item.unit_price.toString(),
        vat_rate: (item.vat_rate || 5).toString(), // Default to 5% if not set
        total_price: item.total_price,
        amount: item.quantity * item.unit_price, // Total amount without VAT
        tax: (item.quantity * item.unit_price) * ((parseFloat(item.vat_rate?.toString() || '0')) / 100), // Calculated tax amount
    })),
    payment_schedules: props.invoice.payment_schedules?.map(schedule => ({
        id: schedule.id,
        description: schedule.description,
        percentage: schedule.percentage?.toString() || '',
        amount: schedule.amount.toString(),
        due_date: schedule.due_date ? schedule.due_date.split('T')[0] : '',
    })) || [
        {
            description: 'Full Payment',
            percentage: '100',
            amount: '0',
            due_date: props.invoice.due_date,
        }
    ],
});

// Helper function to get item errors safely
const getItemError = (index: number, field: string) => {
    const errorKey = `items.${index}.${field}`;
    return (form.errors as any)[errorKey] || null;
};

const filteredProjects = computed(() => {
    if (!form.client_id) return [];
    return props.projects.filter(project => 
        project.client_id.toString() === form.client_id
    );
});

const addItem = () => {
    form.items.push({
        id: undefined,
        product_id: '',
        description: '',
        quantity: 1,
        unit_price: '0',    // Unit price including VAT
        vat_rate: '5',      // Default 5% VAT
        total_price: 0,     // Total amount including VAT
        amount: 0,          // Total amount without VAT
        tax: 0,             // Total tax amount
    });
};

const removeItem = (index: number) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const updateItemAmount = (index: number) => {
    const item = form.items[index];
    const quantity = parseFloat(item.quantity.toString()) || 0;
    const unitPrice = parseFloat(item.unit_price.toString()) || 0;
    const vatRate = parseFloat(item.vat_rate.toString()) || 0;

    item.amount = quantity * unitPrice;

    item.tax = item.amount * (vatRate / 100);

    // Calculate total amount (quantity * unit price which already includes VAT)
    item.total_price = quantity * unitPrice;
};

const updateItemFromProduct = (index: number, productId: string) => {
    const product = props.products.find(p => p.id.toString() === productId);
    if (product) {
        form.items[index].description = product.name;
        form.items[index].unit_price = product.price.toString();
        updateItemAmount(index);
    }
};

const subtotal = computed(() => {
    console.log(form.items[0])
    return form.items.reduce((sum, item) => sum + parseFloat(item.amount?.toString() || '0'), 0);
});

const taxAmount = computed(() => {
    //sum it  up from items
    return form.items.reduce((sum, item) => sum + parseFloat(item.tax?.toString() || '0'), 0);
});

const discountAmount = computed(() => {
    return parseFloat(form.discount_amount) || 0;
});

const totalAmount = computed(() => {
    return subtotal.value + taxAmount.value - discountAmount.value;
});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
};

// Payment schedule functions
const addPaymentSchedule = () => {
    form.payment_schedules.push({
        id: undefined,
        description: `Payment ${form.payment_schedules.length + 1}`,
        percentage: '',
        amount: '0',
        due_date: form.due_date,
    });
};

const removePaymentSchedule = (index: number) => {
    if (form.payment_schedules.length > 1) {
        form.payment_schedules.splice(index, 1);
    }
};

const updatePaymentAmount = (index: number) => {
    const schedule = form.payment_schedules[index];
    const percentage = parseFloat(schedule.percentage || '0');
    if (percentage > 0) {
        schedule.amount = ((totalAmount.value * percentage) / 100).toString();
    }
};

const updatePaymentPercentage = (index: number) => {
    const schedule = form.payment_schedules[index];
    const amount = parseFloat(schedule.amount || '0');
    if (amount > 0 && totalAmount.value > 0) {
        schedule.percentage = ((amount / totalAmount.value) * 100).toFixed(2);
    }
};

const submit = () => {
    form.put(`/invoices/${props.invoice.id}`, {
        onSuccess: () => {
            success('Success!', 'Invoice updated successfully');
        },
        onError: () => {
            error('Error!', 'Failed to update invoice. Please check the form and try again.');
        }
    });
};

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
</script>

<template>
    <Head :title="`Edit ${invoice.invoice_number}`" />

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
                        <h1 class="text-3xl font-bold tracking-tight">Edit Invoice</h1>
                        <p class="text-muted-foreground">{{ invoice.invoice_number }}</p>
                    </div>
                </div>
                <Button variant="destructive" @click="deleteInvoice" :disabled="form.processing">
                    <Trash2 class="w-4 h-4 mr-2" />
                    Delete Invoice
                </Button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Information -->
                <Card>
                    <CardHeader>
                        <CardTitle>Invoice Details</CardTitle>
                        <CardDescription>Update the invoice information</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="client_id">Client *</Label>
                                <div class="flex space-x-2">
                                    <Select v-model="form.client_id" @update:model-value="form.project_id = ''">
                                        <SelectTrigger :class="{ 'border-red-500': form.errors.client_id }">
                                            <SelectValue placeholder="Select client" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="null">Select Client</SelectItem>
                                            <SelectItem v-for="client in clients" :key="client.id" :value="client.id.toString()">
                                                {{ client.name }}{{ client.company ? ` (${client.company})` : '' }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <Button type="button" variant="outline" size="sm" as-child>
                                        <Link href="/clients/create">
                                            <UserPlus class="w-4 h-4" />
                                        </Link>
                                    </Button>
                                </div>
                                <InputError :message="form.errors.client_id" />
                            </div>

                            <div class="space-y-2">
                                <Label for="project_id">Project (Optional)</Label>
                                <Select v-model="form.project_id" :disabled="!form.client_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select project" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="null">No Project</SelectItem>
                                        <SelectItem v-for="project in filteredProjects" :key="project.id" :value="project.id.toString()">
                                            {{ project.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="issue_date">Issue Date *</Label>
                                <Input
                                    id="issue_date"
                                    v-model="form.issue_date"
                                    type="date"
                                    :class="{ 'border-red-500': form.errors.issue_date }"
                                />
                                <InputError :message="form.errors.issue_date" />
                            </div>

                            <div class="space-y-2">
                                <Label for="due_date">Due Date *</Label>
                                <Input
                                    id="due_date"
                                    v-model="form.due_date"
                                    type="date"
                                    :class="{ 'border-red-500': form.errors.due_date }"
                                />
                                <InputError :message="form.errors.due_date" />
                            </div>

                            <div class="space-y-2">
                                <Label for="status">Status *</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger :class="{ 'border-red-500': form.errors.status }">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="draft">Draft</SelectItem>
                                        <SelectItem value="pending">Pending</SelectItem>
                                        <SelectItem value="paid">Paid</SelectItem>
                                        <SelectItem value="overdue">Overdue</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.status" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="notes">Notes</Label>
                            <Textarea
                                id="notes"
                                v-model="form.notes"
                                placeholder="Payment terms, special instructions, etc."
                                rows="3"
                            />
                        </div>
                    </CardContent>
                </Card>

                <!-- Items -->
                <Card>
                    <CardHeader>
                        <div class="flex justify-between items-center">
                            <div>
                                <CardTitle>Invoice Items</CardTitle>
                                <CardDescription>Update products and services in this invoice</CardDescription>
                            </div>
                            <Button type="button" @click="addItem" variant="outline" size="sm">
                                <Plus class="w-4 h-4 mr-2" />
                                Add Item
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="(item, index) in form.items" :key="index" class="p-4 border rounded-lg space-y-4">
                                <div class="flex justify-between items-start">
                                    <h4 class="font-medium">Item {{ index + 1 }}</h4>
                                    <Button
                                        v-if="form.items.length > 1"
                                        type="button"
                                        @click="removeItem(index)"
                                        variant="ghost"
                                        size="sm"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label>Product (Optional)</Label>
                                        <Select 
                                            :model-value="item.product_id" 
                                            @update:model-value="(value) => { 
                                                if (value) {
                                                    item.product_id = value as string; 
                                                    updateItemFromProduct(index, value as string); 
                                                }
                                            }"
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select product" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="null">Custom Item</SelectItem>
                                                <SelectItem v-for="product in products" :key="product.id" :value="product.id.toString()">
                                                    {{ product.name }} - {{ formatCurrency(product.price) }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div class="space-y-2">
                                        <Label>Description *</Label>
                                        <Input
                                            v-model="item.description"
                                            placeholder="Item description"
                                            :class="{ 'border-red-500': getItemError(index, 'description') }"
                                        />
                                        <p v-if="getItemError(index, 'description')" class="text-sm text-red-500">
                                            {{ getItemError(index, 'description') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid gap-4 md:grid-cols-5">
                                    <div class="space-y-2">
                                        <Label>Quantity *</Label>
                                        <Input
                                            v-model="item.quantity"
                                            type="number"
                                            min="1"
                                            @input="updateItemAmount(index)"
                                            :class="{ 'border-red-500': getItemError(index, 'quantity') }"
                                        />
                                        <p v-if="getItemError(index, 'quantity')" class="text-sm text-red-500">
                                            {{ getItemError(index, 'quantity') }}
                                        </p>
                                    </div>

                                    <div class="space-y-2">
                                        <Label>Unit Price (incl. VAT) *</Label>
                                        <Input
                                            v-model="item.unit_price"
                                            type="number"
                                            min="1"
                                            @input="updateItemAmount(index)"
                                            :class="{ 'border-red-500': getItemError(index, 'unit_price') }"
                                        />
                                        <p v-if="getItemError(index, 'unit_price')" class="text-sm text-red-500">
                                            {{ getItemError(index, 'unit_price') }}
                                        </p>
                                    </div>

                                    <div class="space-y-2">
                                        <Label>VAT Rate (%) *</Label>
                                        <Input
                                            v-model="item.vat_rate"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                            @input="updateItemAmount(index)"
                                            :class="{ 'border-red-500': getItemError(index, 'vat_rate') }"
                                        />
                                        <p v-if="getItemError(index, 'vat_rate')" class="text-sm text-red-500">
                                            {{ getItemError(index, 'vat_rate') }}
                                        </p>
                                    </div>

                                    <div class="space-y-2">
                                        <Label>Total Amount (incl. VAT)</Label>
                                        <div class="px-3 py-2 bg-muted rounded-md text-sm font-medium">
                                            {{ formatCurrency(item.total_price || 0) }}
                                        </div>
                                        <p class="text-xs text-gray-500">
                                            VAT ({{ item.vat_rate }}%): {{ formatCurrency(((parseFloat(item.unit_price?.toString() || '0') * parseFloat(item.quantity?.toString() || '0')) * parseFloat(item.vat_rate?.toString() || '0')) / (100 + parseFloat(item.vat_rate?.toString() || '0'))) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Payment Schedules -->
                <Card>
                    <CardHeader>
                        <div class="flex justify-between items-center">
                            <div>
                                <CardTitle>Payment Configuration</CardTitle>
                                <CardDescription>Configure how the client will pay this invoice</CardDescription>
                            </div>
                            <Button type="button" @click="addPaymentSchedule" variant="outline" size="sm">
                                <Plus class="w-4 h-4 mr-2" />
                                Add Payment
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="(schedule, index) in form.payment_schedules" :key="index" class="p-4 border rounded-lg space-y-4">
                                <div class="flex justify-between items-start">
                                    <h4 class="font-medium">Payment {{ index + 1 }}</h4>
                                    <Button
                                        v-if="form.payment_schedules.length > 1"
                                        type="button"
                                        @click="removePaymentSchedule(index)"
                                        variant="ghost"
                                        size="sm"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label>Description *</Label>
                                        <Input
                                            v-model="schedule.description"
                                            placeholder="e.g., First Payment (30%)"
                                        />
                                    </div>
                                    
                                    <div class="space-y-2">
                                        <Label>Due Date *</Label>
                                        <Input
                                            v-model="schedule.due_date"
                                            type="date"
                                        />
                                    </div>
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label>Percentage (%)</Label>
                                        <Input
                                            v-model="schedule.percentage"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                            placeholder="e.g., 30"
                                            @input="updatePaymentAmount(index)"
                                        />
                                    </div>
                                    
                                    <div class="space-y-2">
                                        <Label>Fixed Amount</Label>
                                        <Input
                                            v-model="schedule.amount"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            placeholder="Enter fixed amount"
                                            @input="updatePaymentPercentage(index)"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Totals -->
                <Card>
                    <CardContent class="pt-6">
                        <div class="space-y-4">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="discount_amount">Discount Amount</Label>
                                    <Input
                                        id="discount_amount"
                                        v-model="form.discount_amount"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                    />
                                </div>
                            </div>

                            <div class="space-y-2 text-right">
                                <div class="text-sm text-muted-foreground">
                                    Subtotal: {{ formatCurrency(subtotal) }}
                                </div>
                                <div class="text-sm text-muted-foreground">
                                    Total VAT: {{ formatCurrency(taxAmount) }}
                                </div>
                                <div v-if="discountAmount > 0" class="text-sm text-muted-foreground">
                                    Discount: -{{ formatCurrency(discountAmount) }}
                                </div>
                                <div class="text-lg font-semibold">
                                    Total: {{ formatCurrency(totalAmount) }}
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-4">
                    <Button variant="outline" type="button" as-child>
                        <Link :href="`/invoices/${invoice.id}`">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Save class="w-4 h-4 mr-2" />
                        {{ form.processing ? 'Updating...' : 'Update Invoice' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
