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
import { FormControl, FormDescription, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { ArrowLeft, Save } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import { watch } from 'vue';

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
        title: 'Create Product',
        href: '/products/create',
    },
];

const form = useForm({
    name: '',
    description: '',
    sku: '',
    price: '',
    cost: '',
    category: '',
    stock_quantity: '',
    min_stock_level: '',
    unit: 'piece',
    is_active: true,
});

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
    form.post('/products', {
        onSuccess: () => {
            success('Success!', 'Product created successfully');
        },
        onError: () => {
            error('Error!', 'Failed to create product. Please check the form and try again.');
        }
    });
};

// Generate SKU automatically when name changes
watch(() => form.name, (newName) => {
    if (newName && !form.sku) {
        const sku = newName
            .toUpperCase()
            .replace(/[^A-Z0-9\s]/g, '')
            .replace(/\s+/g, '-')
            .substring(0, 10);
        form.sku = sku + '-' + Math.random().toString(36).substring(2, 5).toUpperCase();
    }
});
</script>

<template>
    <Head title="Create Product" />

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
                        <h1 class="text-3xl font-bold tracking-tight">Create Product</h1>
                        <p class="text-muted-foreground">Add a new product to your inventory</p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Basic Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Basic Information</CardTitle>
                            <CardDescription>Enter the basic product details</CardDescription>
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
                            <CardDescription>Set pricing and inventory levels</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="price">Selling Price *</Label>
                                    <Input
                                        id="price"
                                        v-model="form.price"
                                        type="number"
                                        step="0.01"
                                        placeholder="0.00"
                                        :class="{ 'border-red-500': form.errors.price }"
                                    />
                                    <InputError :message="form.errors.price" />
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
                                </div>
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
                        <Link href="/products">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Save class="w-4 h-4 mr-2" />
                        {{ form.processing ? 'Creating...' : 'Create Product' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
