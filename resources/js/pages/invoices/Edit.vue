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
    amount: number;
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
    issue_date: props.invoice.issue_date,
    due_date: props.invoice.due_date,
    payment_terms: props.invoice.payment_terms || 'net_30',
    notes: props.invoice.notes || '',
    tax_rate: props.invoice.tax_rate || '0',
    discount_amount: props.invoice.discount_amount || '0',
    items: props.invoice.items.map(item => ({
        id: item.id,
        product_id: item.product_id?.toString() || '',
        description: item.description,
        quantity: item.quantity,
        unit_price: item.unit_price.toString(),
        amount: item.amount,
    })),
});

const filteredProjects = computed(() => {
    if (!form.client_id) return [];
    return props.projects.filter(project => 
        project.client_id.toString() === form.client_id
    );
});

const addItem = () => {
    form.items.push({
        product_id: '',
        description: '',
        quantity: 1,
        unit_price: '0',
        amount: 0,
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
    item.amount = quantity * unitPrice;
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
    return form.items.reduce((sum, item) => sum + (item.amount || 0), 0);
});

const taxAmount = computed(() => {
    const rate = parseFloat(form.tax_rate) || 0;
    return (subtotal.value * rate) / 100;
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
                                        <SelectItem value="">No Project</SelectItem>
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
                                <Label for="payment_terms">Payment Terms</Label>
                                <Select v-model="form.payment_terms">
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
                                            @update:model-value="(value) => { item.product_id = value; updateItemFromProduct(index, value); }"
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select product" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="">Custom Item</SelectItem>
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

                                <div class="grid gap-4 md:grid-cols-4">
                                    <div class="space-y-2">
                                        <Label>Quantity *</Label>
                                        <Input
                                            v-model="item.quantity"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            @input="updateItemAmount(index)"
                                            :class="{ 'border-red-500': form.errors[`items.${index}.quantity`] }"
                                        />
                                        <p v-if="form.errors[`items.${index}.quantity`]" class="text-sm text-red-500">
                                            {{ form.errors[`items.${index}.quantity`] }}
                                        </p>
                                    </div>

                                    <div class="space-y-2">
                                        <Label>Unit Price *</Label>
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
                                        <Label>Amount</Label>
                                        <div class="px-3 py-2 bg-muted rounded-md text-sm font-medium">
                                            {{ formatCurrency(item.amount) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Totals -->
                            <div class="border-t pt-4 space-y-4">
                                <div class="grid gap-4 md:grid-cols-3">
                                    <div class="space-y-2">
                                        <Label for="tax_rate">Tax Rate (%)</Label>
                                        <Input
                                            id="tax_rate"
                                            v-model="form.tax_rate"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                        />
                                    </div>

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

                                <div class="flex justify-end">
                                    <div class="text-right space-y-2">
                                        <div class="text-sm text-muted-foreground">
                                            Subtotal: {{ formatCurrency(subtotal) }}
                                        </div>
                                        <div v-if="taxAmount > 0" class="text-sm text-muted-foreground">
                                            Tax ({{ form.tax_rate }}%): {{ formatCurrency(taxAmount) }}
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
