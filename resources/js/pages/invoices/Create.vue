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
import { computed, ref } from 'vue';

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
    product_id: string | null;
    description: string;
    quantity: number | string;
    unit_price: string;
    vat_rate: string;
    total_price: number | string;
    amount: number | string;
    tax?: number; // Optional tax field for total amount calculation
}

interface InvoicePaymentSchedule {
    id?: number;
    description: string;
    percentage?: string;
    amount: string;
    due_date: string;
}

interface Props {
    clients: Client[];
    projects: Project[];
    products: Product[];
    nextInvoiceNumber: string;
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
        title: 'Create',
        href: '/invoices/create',
    },
];

interface InvoiceForm {
    [key: string]: any;
    client_id: string;
    project_id: string | null;
    issue_date: string;
    due_date: string;
    payment_terms: string;
    status: string;
    notes: string;
    terms: string;
    tax_rate: string;
    discount_amount: string;
    invoice_number: string;
    items: InvoiceItem[];
    payment_schedules: InvoicePaymentSchedule[];
}

const form = useForm({
    client_id: '',
    project_id: 'no_project',
    issue_date: new Date().toISOString().split('T')[0],
    due_date: '',
    payment_terms: 'net_30',
    status: 'draft',
    notes: '',
    terms: '',
    tax_rate: '0',
    discount_amount: '0',
    invoice_number: props.nextInvoiceNumber,
    items: [
        {
            product_id: 'custom',
            description: '',
            quantity: 1,
            unit_price: '0',
            vat_rate: '5',      // Default 5% VAT
            total_price: 0,
            amount: 0,          // Total amount without VAT
            tax: 0, // Total tax amount
        }
    ],
    payment_schedules: [
        {
            description: 'Full Payment',
            percentage: '100',
            amount: '0',
            due_date: '',
        }
    ],
});

// Set default due date (30 days from issue date)
const updateDueDate = () => {
    if (form.issue_date) {
        const issueDate = new Date(form.issue_date);
        const dueDate = new Date(issueDate);
        
        switch (form.payment_terms) {
            case 'net_15':
                dueDate.setDate(issueDate.getDate() + 15);
                break;
            case 'net_30':
                dueDate.setDate(issueDate.getDate() + 30);
                break;
            case 'net_60':
                dueDate.setDate(issueDate.getDate() + 60);
                break;
            case 'due_on_receipt':
                dueDate.setDate(issueDate.getDate());
                break;
            default:
                dueDate.setDate(issueDate.getDate() + 30);
        }
        
        form.due_date = dueDate.toISOString().split('T')[0];
    }
};

// Initialize due date
updateDueDate();

const filteredProjects = computed(() => {
    if (!form.client_id) return [];
    return props.projects.filter(project => 
        project.client_id.toString() === form.client_id
    );
});

const addItem = () => {
    form.items.push({
        product_id: 'custom',
        description: '',
        quantity: 1,
        unit_price: '0',    // Unit price including VAT
        vat_rate: '5',      // Default 5% VAT
        total_price: 0,          // Total amount including VAT
        amount: 0,          // Total amount without VAT
        tax: 0, // Total tax amount
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

    // Calculate total price including VAT
    item.total_price = quantity * unitPrice * (1 + vatRate / 100);
};

const updateItemFromProduct = (index: number, productId: string) => {
    if (productId === 'custom') {
        // Don't auto-fill for custom items
        return;
    }
    
    const product = props.products.find(p => p.id.toString() === productId);
    if (product) {
        form.items[index].description = product.name;
        form.items[index].unit_price = product.price.toString();
        updateItemAmount(index);
    }
};

const subtotal = computed(() => {
    return form.items.reduce((sum, item) => sum + (parseFloat(item.amount as string) || 0), 0);
});

const taxAmount = computed(() => {
    return form.items.reduce((sum, item) => sum + (item.tax || 0), 0);
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
        currency: 'AED'
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
    // Prepare the data for submission
    const formData = {
        ...form.data(),
        project_id: form.project_id === 'no_project' ? null : form.project_id,
        items: form.items.map(item => ({
            ...item,
            product_id: item.product_id === 'custom' ? null : item.product_id,
        }))
    };

    form.transform(data => formData).post('/invoices', {
        onSuccess: () => {
            success('Success!', 'Invoice created successfully');
        },
        onError: () => {
            error('Error!', 'Failed to create invoice. Please check the form and try again.');
        }
    });
};
</script>

<template>
    <Head title="Create Invoice" />

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
                        <h1 class="text-3xl font-bold tracking-tight">Create Invoice</h1>
                        <p class="text-muted-foreground">Generate a new invoice for your client</p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Information -->
                <Card>
                    <CardHeader>
                        <CardTitle>Invoice Details</CardTitle>
                        <CardDescription>Enter the basic invoice information</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="client_id">Client *</Label>
                                <div class="flex space-x-2">
                                    <Select v-model="form.client_id" @update:model-value="form.project_id = 'no_project'">
                                        <SelectTrigger :class="{ 'border-red-500': form.errors.client_id }">
                                            <SelectValue placeholder="Select client" />
                                        </SelectTrigger>
                                        <SelectContent>
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
                                        <SelectItem value="no_project">No Project</SelectItem>
                                        <SelectItem v-for="project in filteredProjects" :key="project.id" :value="project.id.toString()">
                                            {{ project.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-4">
                            <div class="space-y-2">
                                <Label for="issue_date">Issue Date *</Label>
                                <Input
                                    id="issue_date"
                                    v-model="form.issue_date"
                                    type="date"
                                    @change="updateDueDate"
                                    :class="{ 'border-red-500': form.errors.issue_date }"
                                />
                                <InputError :message="form.errors.issue_date" />
                            </div>

                            <div class="space-y-2">
                                <Label for="payment_terms">Payment Terms</Label>
                                <Select v-model="form.payment_terms" @update:model-value="updateDueDate">
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="due_on_receipt">Due on Receipt</SelectItem>
                                        <SelectItem value="net_15">Net 15 Days</SelectItem>
                                        <SelectItem value="net_30">Net 30 Days</SelectItem>
                                        <SelectItem value="net_60">Net 60 Days</SelectItem>
                                    </SelectContent>
                                </Select>
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
                                <CardDescription>Add products and services to this invoice</CardDescription>
                            </div>
                            <Button type="button" @click="addItem" variant="outline" size="sm">
                                <Plus class="w-4 h-4 mr-2" />
                                Add Item
                            </Button>
                        </div>
                    </CardHeader>

                    <CardContent>
                        <div class="space-y-4">
                            <!-- Item loop -->
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
                                            @update:model-value="(value) => { item.product_id = value; updateItemFromProduct(index, value); }"
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select product" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="custom">Custom Item</SelectItem>
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
                                            :class="{ 'border-red-500': form.errors[`items.${index}.description`] }"
                                        />
                                        <p v-if="form.errors[`items.${index}.description`]" class="text-sm text-red-500">
                                            {{ form.errors[`items.${index}.description`] }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid gap-4 md:grid-cols-5">
                                    <div class="space-y-2">
                                        <Label>Quantity *</Label>
                                        <Input
                                            v-model="item.quantity"
                                            type="number"
                                            step="1"
                                            min="1"
                                            @input="updateItemAmount(index)"
                                            :class="{ 'border-red-500': form.errors[`items.${index}.quantity`] }"
                                        />
                                        <p v-if="(form.errors as any)[`items.${index}.quantity`]" class="text-sm text-red-500">
                                            {{ (form.errors as any)[`items.${index}.quantity`] }}
                                        </p>
                                    </div>

                                    <div class="space-y-2">
                                        <Label>Unit Price (incl. VAT) *</Label>
                                        <Input
                                            v-model="item.unit_price"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            @input="updateItemAmount(index)"
                                            :class="{ 'border-red-500': form.errors[`items.${index}.unit_price`] }"
                                        />
                                        <p v-if="form.errors[`items.${index}.unit_price`]" class="text-sm text-red-500">
                                            {{ form.errors[`items.${index}.unit_price`] }}
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
                                            :class="{ 'border-red-500': form.errors[`items.${index}.vat_rate`] }"
                                        />
                                        <p v-if="form.errors[`items.${index}.vat_rate`]" class="text-sm text-red-500">
                                            {{ form.errors[`items.${index}.vat_rate`] }}
                                        </p>
                                    </div>

                                    <div class="space-y-2 col-span-2 md:col-span-1">
                                        <Label>Total Amount (incl. VAT)</Label>
                                    
                                        <Input
                                            v-model="item.total_price"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            readonly
                                            @input="updateItemAmount(index)"
                                            :class="{ 'border-red-500': form.errors[`items.${index}.total_price`] }"
                                        />
                                        <p class="text-xs text-gray-500">
                                            VAT ({{ item.vat_rate }}%): {{
                                                formatCurrency(
                                                    (
                                                        (parseFloat(item.unit_price?.toString() || '0') * parseFloat(item.quantity?.toString() || '0')) *
                                                        parseFloat(item.vat_rate?.toString() || '0')
                                                    ) / (100 + parseFloat(item.vat_rate?.toString() || '0'))
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Totals Section -->
                            <div class="border-t pt-4 space-y-4">
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

                <!-- Form Actions -->
                <div class="flex justify-end space-x-4">
                    <Button variant="outline" type="button" as-child>
                        <Link href="/invoices">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Save class="w-4 h-4 mr-2" />
                        {{ form.processing ? 'Creating...' : 'Create Invoice' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
