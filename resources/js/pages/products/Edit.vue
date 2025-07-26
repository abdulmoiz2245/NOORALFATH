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
import { ArrowLeft, Save, Trash2 } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import { computed } from 'vue';

interface Product {
    id: number;
    name: string;
    description?: string;
    sku: string;
    price: number;
    base_price?: number; // Will calculate from price if not available
    vat_rate?: number; // Will default to 5% if not available
    cost?: number;
    category: string;
    stock_quantity: number;
    min_stock_level?: number;
    unit: string;
    is_active: boolean;
}

interface Props {
    product: Product;
}

const props = defineProps<Props>();
const { success, error } = useToast();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Products',
        href: '/products',
    },
    {
        title: props.product.name,
        href: `/products/${props.product.id}`,
    },
    {
        title: 'Edit',
        href: `/products/${props.product.id}/edit`,
    },
];

// Calculate base price from existing price (assuming it includes 5% VAT)
const calculateBasePrice = (priceWithVat: number, vatRate: number = 5) => {
    return (priceWithVat * 100) / (100 + vatRate);
};

const existingBasePrice = props.product.base_price || calculateBasePrice(props.product.price);
const existingVatRate = props.product.vat_rate || 5;

const form = useForm({
    name: props.product.name,
    description: props.product.description || '',
    sku: props.product.sku,
    base_price: existingBasePrice.toFixed(2),
    vat_rate: existingVatRate.toString(),
    cost: props.product.cost?.toString() || '',
    category: props.product.category,
    stock_quantity: props.product.stock_quantity.toString(),
    min_stock_level: props.product.min_stock_level?.toString() || '',
    unit: props.product.unit,
    is_active: props.product.is_active,
});

// Computed properties for VAT calculations
const vatAmount = computed(() => {
    const basePrice = parseFloat(form.base_price) || 0;
    const vatRate = parseFloat(form.vat_rate) || 0;
    return (basePrice * vatRate) / 100;
});

const priceWithVat = computed(() => {
    const basePrice = parseFloat(form.base_price) || 0;
    return basePrice + vatAmount.value;
});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
};

const categories = [
    'Electronics',
    'Clothing',
    'Books',
    'Home & Garden',
    'Sports',
    'Automotive',
    'Health & Beauty',
    'Food & Beverages',
    'Toys & Games',
    'Office Supplies',
    'Other'
];

const units = [
    { value: 'piece', label: 'Piece' },
    { value: 'kg', label: 'Kilogram' },
    { value: 'g', label: 'Gram' },
    { value: 'lb', label: 'Pound' },
    { value: 'liter', label: 'Liter' },
    { value: 'ml', label: 'Milliliter' },
    { value: 'meter', label: 'Meter' },
    { value: 'cm', label: 'Centimeter' },
    { value: 'inch', label: 'Inch' },
    { value: 'foot', label: 'Foot' },
    { value: 'box', label: 'Box' },
    { value: 'pack', label: 'Pack' },
    { value: 'dozen', label: 'Dozen' },
    { value: 'set', label: 'Set' },
];

const submit = () => {
    // Create the data with calculated price including VAT
    const formData = {
        ...form.data(),
        price: priceWithVat.value.toFixed(2), // Send price with VAT to backend
    };
    
    form.transform((data) => formData).put(`/products/${props.product.id}`, {
        onSuccess: () => {
            success('Success!', 'Product updated successfully');
        },
        onError: () => {
            error('Error!', 'Failed to update product. Please check the form and try again.');
        }
    });
};

const deleteProduct = () => {
    if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
        form.delete(`/products/${props.product.id}`, {
            onSuccess: () => {
                success('Success!', 'Product deleted successfully');
            },
            onError: () => {
                error('Error!', 'Failed to delete product.');
            }
        });
    }
};
</script>

<template>
    <Head :title="`Edit ${product.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Button variant="outline" size="sm" as-child>
                        <Link href="/products">
                            <ArrowLeft class="w-4 h-4 mr-2" />
                            Back to Products
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">Edit Product</h1>
                        <p class="text-muted-foreground">Update product information</p>
                    </div>
                </div>
                <Button variant="destructive" @click="deleteProduct" :disabled="form.processing">
                    <Trash2 class="w-4 h-4 mr-2" />
                    Delete Product
                </Button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Basic Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Basic Information</CardTitle>
                            <CardDescription>Update the basic product details</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <Label for="name">Product Name *</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    placeholder="Enter product name"
                                    :class="{ 'border-red-500': form.errors.name }"
                                />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div class="space-y-2">
                                <Label for="description">Description</Label>
                                <Textarea
                                    id="description"
                                    v-model="form.description"
                                    placeholder="Enter product description"
                                    rows="3"
                                    :class="{ 'border-red-500': form.errors.description }"
                                />
                                <InputError :message="form.errors.description" />
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="sku">SKU *</Label>
                                    <Input
                                        id="sku"
                                        v-model="form.sku"
                                        placeholder="Product SKU"
                                        :class="{ 'border-red-500': form.errors.sku }"
                                    />
                                    <InputError :message="form.errors.sku" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="category">Category *</Label>
                                    <Select v-model="form.category">
                                        <SelectTrigger :class="{ 'border-red-500': form.errors.category }">
                                            <SelectValue placeholder="Select category" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="category in categories" :key="category" :value="category">
                                                {{ category }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.category" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Pricing & Inventory -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Pricing & Inventory</CardTitle>
                            <CardDescription>Update pricing and inventory levels</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="base_price">Base Price (excl. VAT) *</Label>
                                    <Input
                                        id="base_price"
                                        v-model="form.base_price"
                                        type="number"
                                        step="0.01"
                                        placeholder="0.00"
                                        :class="{ 'border-red-500': form.errors.base_price }"
                                    />
                                    <InputError :message="form.errors.base_price" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="vat_rate">VAT Rate (%)</Label>
                                    <Input
                                        id="vat_rate"
                                        v-model="form.vat_rate"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        placeholder="5.00"
                                        :class="{ 'border-red-500': form.errors.vat_rate }"
                                    />
                                    <InputError :message="form.errors.vat_rate" />
                                </div>
                            </div>

                            <!-- Price Breakdown Display -->
                            <div class="p-4 bg-gray-50 rounded-lg space-y-2">
                                <h4 class="font-medium text-sm">Price Breakdown</h4>
                                <div class="grid gap-2 text-sm">
                                    <div class="flex justify-between">
                                        <span>Base Price:</span>
                                        <span>{{ formatCurrency(parseFloat(form.base_price) || 0) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>VAT ({{ form.vat_rate }}%):</span>
                                        <span>{{ formatCurrency(vatAmount) }}</span>
                                    </div>
                                    <div class="flex justify-between border-t pt-2 font-medium">
                                        <span>Selling Price (incl. VAT):</span>
                                        <span>{{ formatCurrency(priceWithVat) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="cost">Cost Price</Label>
                                <Input
                                    id="cost"
                                    v-model="form.cost"
                                    type="number"
                                    step="0.01"
                                    placeholder="0.00"
                                    :class="{ 'border-red-500': form.errors.cost }"
                                />
                                <InputError :message="form.errors.cost" />
                                <p class="text-xs text-gray-500">Cost price for margin calculation</p>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="stock_quantity">Stock Quantity *</Label>
                                    <Input
                                        id="stock_quantity"
                                        v-model="form.stock_quantity"
                                        type="number"
                                        placeholder="0"
                                        :class="{ 'border-red-500': form.errors.stock_quantity }"
                                    />
                                    <InputError :message="form.errors.stock_quantity" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="min_stock_level">Minimum Stock Level</Label>
                                    <Input
                                        id="min_stock_level"
                                        v-model="form.min_stock_level"
                                        type="number"
                                        placeholder="0"
                                        :class="{ 'border-red-500': form.errors.min_stock_level }"
                                    />
                                    <InputError :message="form.errors.min_stock_level" />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="unit">Unit of Measurement</Label>
                                <Select v-model="form.unit">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select unit" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="unit in units" :key="unit.value" :value="unit.value">
                                            {{ unit.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="flex items-center space-x-2">
                                <input
                                    id="is_active"
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="rounded border-gray-300"
                                />
                                <Label for="is_active">Active Product</Label>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-4">
                    <Button variant="outline" type="button" as-child>
                        <Link :href="`/products/${product.id}`">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Save class="w-4 h-4 mr-2" />
                        {{ form.processing ? 'Updating...' : 'Update Product' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
