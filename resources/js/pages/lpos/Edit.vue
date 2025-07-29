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
import { ArrowLeft, Save, Plus, Trash2 } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import { computed } from 'vue';

interface Vendor {
    id: number;
    name: string;
    email: string;
}

interface Product {
    id: number;
    name: string;
    price: number;
    unit: string;
}

interface LpoItem {
    id?: number;
    product_id?: number;
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
    subject?: string;
    description?: string;
    issue_date?: string;
    trn_number?: string;
    terms?: string;
    total_after_tax: number;
    vendor?: Vendor;
    items: LpoItem[];
    created_at: string;
    updated_at: string;
}

interface Props {
    lpo: LocalPurchaseOrder;
    vendors: Vendor[];
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
        title: 'Local Purchase Orders',
        href: '/lpos',
    },
    {
        title: props.lpo.lpo_number,
        href: `/lpos/${props.lpo.id}`,
    },
    {
        title: 'Edit',
        href: `/lpos/${props.lpo.id}/edit`,
    },
];

const form = useForm({
    vendor_id: props.lpo.vendor_id.toString(),
    subject: props.lpo.subject || '',
    description: props.lpo.description || '',
    issue_date: props.lpo.issue_date || '',
    trn_number: props.lpo.trn_number || '',
    terms: props.lpo.terms || '',
    items: props.lpo.items.map(item => ({
        id: item.id,
        product_id: item.product_id?.toString() || '',
        description: item.description,
        quantity: item.quantity,
        unit_price: item.unit_price.toString(),
        tax_rate: item.tax_rate ?? 0,
        total_amount: item.total_price_after_tax,
    })),
});

const subtotal = computed(() => {
    return form.items.reduce((sum, item) => {
        const quantity = parseFloat(item.quantity?.toString() || '0');
        const unitPrice = parseFloat(item.unit_price?.toString() || '0');
        return sum + (quantity * unitPrice);
    }, 0);
});

const totalTax = computed(() => {
    return form.items.reduce((sum, item) => {
        const quantity = parseFloat(item.quantity?.toString() || '0');
        const unitPrice = parseFloat(item.unit_price?.toString() || '0');
        const taxRate = parseFloat(item.tax_rate?.toString() || '0');
        const base = quantity * unitPrice;
        return sum + (base * taxRate / 100);
    }, 0);
});

const totalAmount = computed(() => {
    return subtotal.value + totalTax.value;
});

const addItem = () => {
    form.items.push({
        id: undefined,
        product_id: '',
        description: '',
        quantity: 1,
        unit_price: '',
        tax_rate: 0,
        total_amount: 0,
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
    const taxRate = parseFloat(item.tax_rate?.toString() || '0');
    const baseAmount = quantity * unitPrice;
    item.total_amount = baseAmount + (baseAmount * taxRate / 100);
};

const updateItemFromProduct = (index: number, productId: string) => {
    const product = props.products.find(p => p.id.toString() === productId);
    if (product) {
        form.items[index].description = product.name;
        form.items[index].unit_price = product.price.toString();
        updateItemAmount(index);
    }
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'AED'
    }).format(amount);
};

const submit = () => {
    form.put(`/lpos/${props.lpo.id}`, {
        onSuccess: () => {
            success('Success!', 'Local Purchase Order updated successfully');
        },
        onError: () => {
            error('Error!', 'Failed to update LPO. Please check the form and try again.');
        }
    });
};

const deleteLpo = () => {
    if (confirm('Are you sure you want to delete this Local Purchase Order? This action cannot be undone.')) {
        form.delete(`/lpos/${props.lpo.id}`, {
            onSuccess: () => {
                success('Success!', 'Local Purchase Order deleted successfully');
            },
            onError: () => {
                error('Error!', 'Failed to delete LPO.');
            }
        });
    }
};
</script>

<template>
    <Head :title="`Edit ${lpo.lpo_number}`" />

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
                        <h1 class="text-3xl font-bold tracking-tight">Edit Local Purchase Order</h1>
                        <p class="text-muted-foreground">{{ lpo.lpo_number }}</p>
                    </div>
                </div>
                <Button variant="destructive" @click="deleteLpo" :disabled="form.processing">
                    <Trash2 class="w-4 h-4 mr-2" />
                    Delete LPO
                </Button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Information -->
                <Card>
                    <CardHeader>
                        <CardTitle>LPO Details</CardTitle>
                        <CardDescription>Update the Local Purchase Order information</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="vendor_id">Vendor *</Label>
                                <Select v-model="form.vendor_id">
                                    <SelectTrigger :class="{ 'border-red-500': form.errors.vendor_id }">
                                        <SelectValue placeholder="Select vendor" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="vendor in vendors" :key="vendor.id" :value="vendor.id.toString()">
                                            {{ vendor.name }} - {{ vendor.email }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.vendor_id" />
                            </div>

                            <div class="space-y-2">
                                <Label for="issue_date">Issue Date</Label>
                                <Input
                                    id="issue_date"
                                    v-model="form.issue_date"
                                    type="date"
                                    :class="{ 'border-red-500': form.errors.issue_date }"
                                />
                                <InputError :message="form.errors.issue_date" />
                            </div>

                            <div class="space-y-2">
                                <Label for="trn_number">TRN Number</Label>
                                <Input
                                    id="trn_number"
                                    v-model="form.trn_number"
                                    placeholder="Enter TRN number"
                                    :class="{ 'border-red-500': form.errors.trn_number }"
                                />
                                <InputError :message="form.errors.trn_number" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="subject">Subject</Label>
                            <Input
                                id="subject"
                                v-model="form.subject"
                                placeholder="LPO subject"
                                :class="{ 'border-red-500': form.errors.subject }"
                            />
                            <InputError :message="form.errors.subject" />
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                placeholder="LPO description"
                                rows="3"
                                :class="{ 'border-red-500': form.errors.description }"
                            />
                            <InputError :message="form.errors.description" />
                        </div>

                        <div class="space-y-2">
                            <Label for="terms">Terms & Conditions</Label>
                            <Textarea
                                id="terms"
                                v-model="form.terms"
                                placeholder="Terms and conditions"
                                rows="2"
                            />
                        </div>
                    </CardContent>
                </Card>

                <!-- Items -->
                <Card>
                    <CardHeader>
                        <div class="flex justify-between items-center">
                            <div>
                                <CardTitle>LPO Items</CardTitle>
                                <CardDescription>Update products and services in this purchase order</CardDescription>
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
                                            @update:model-value="(value) => { item.product_id = value ?? ''; if (value) updateItemFromProduct(index, value); }"
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
                                            :class="{ 'border-red-500': (form.errors as any)[`items.${index}.description`] }"
                                        />
                                        <p v-if="(form.errors as any)[`items.${index}.description`]" class="text-sm text-red-500">
                                            {{ (form.errors as any)[`items.${index}.description`] }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid gap-4 md:grid-cols-5">
                                    <div class="space-y-2">
                                        <Label>Tax Rate (%)</Label>
                                        <Input
                                            v-model="item.tax_rate"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                            @input="updateItemAmount(index)"
                                            :class="{ 'border-red-500': (form.errors as any)[`items.${index}.tax_rate`] }"
                                        />
                                        <p v-if="(form.errors as any)[`items.${index}.tax_rate`]" class="text-sm text-red-500">
                                            {{ (form.errors as any)[`items.${index}.tax_rate`] }}
                                        </p>
                                    </div>
                                    <div>
                                        <Label>Quantity *</Label>
                                        <Input
                                            v-model="item.quantity"
                                            type="number"
                                            step="1"
                                            min="0"
                                            @input="updateItemAmount(index)"
                                            :class="{ 'border-red-500': (form.errors as any)[`items.${index}.quantity`] }"
                                        />
                                        <p v-if="(form.errors as any)[`items.${index}.quantity`]" class="text-sm text-red-500">
                                            {{ (form.errors as any)[`items.${index}.quantity`] }}
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
                                            :class="{ 'border-red-500': (form.errors as any)[`items.${index}.unit_price`] }"
                                        />
                                        <p v-if="(form.errors as any)[`items.${index}.unit_price`]" class="text-sm text-red-500">
                                            {{ (form.errors as any)[`items.${index}.unit_price`] }}
                                        </p>
                                    </div>

                                    <div class="space-y-2">
                                        <Label>Amount</Label>
                                        <!-- <div class="px-3 py-2 bg-muted rounded-md text-sm font-medium">
                                            {{ formatCurrency(item.total_amount) }}
                                        </div> -->
                                        <Input
                                            v-model="item.total_amount"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            disabled
                                            :class="{ 'border-red-500': (form.errors as any)[`items.${index}.quantity`] }"
                                        />
                                    </div>
                                </div>
                            </div>

                           <div class="border-t pt-4">
                                <div class="flex justify-end">
                                    <div class="text-right space-y-1">
                                        <div class="text-sm text-muted-foreground">
                                            Subtotal: {{ formatCurrency(subtotal) }}
                                        </div>
                                        <div class="text-sm text-muted-foreground">
                                            Total Tax: {{ formatCurrency(totalTax) }}
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
                        <Link :href="`/lpos/${lpo.id}`">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Save class="w-4 h-4 mr-2" />
                        {{ form.processing ? 'Updating...' : 'Update LPO' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
