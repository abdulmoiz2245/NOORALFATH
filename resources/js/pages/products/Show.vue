<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { ArrowLeft, Edit, Trash2, Package, DollarSign, TrendingUp, AlertTriangle } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import { computed } from 'vue';

interface Product {
    id: number;
    name: string;
    description?: string;
    sku: string;
    price: number;
    cost?: number;
    category: string;
    stock_quantity: number;
    min_stock_level?: number;
    unit: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;
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
];

const form = useForm({});

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

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString();
};

// VAT calculations (assuming current price includes 5% VAT)
const vatRate = 5; // Default 5% VAT
const basePrice = computed(() => {
    return (props.product.price * 100) / (100 + vatRate);
});

const vatAmount = computed(() => {
    return props.product.price - basePrice.value;
});

const profitMargin = computed(() => {
    if (props.product.cost && props.product.price > 0) {
        return ((props.product.price - props.product.cost) / props.product.price) * 100;
    }
    return 0;
});

const stockStatus = computed(() => {
    if (!props.product.min_stock_level) return 'normal';
    if (props.product.stock_quantity <= props.product.min_stock_level) return 'low';
    if (props.product.stock_quantity <= props.product.min_stock_level * 1.5) return 'warning';
    return 'normal';
});

const stockBadgeColor = computed(() => {
    switch (stockStatus.value) {
        case 'low': return 'destructive';
        case 'warning': return 'warning';
        default: return 'default';
    }
});
</script>

<template>
    <Head :title="product.name" />

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
                        <div class="flex items-center space-x-2">
                            <h1 class="text-3xl font-bold tracking-tight">{{ product.name }}</h1>
                            <Badge v-if="product.is_active" variant="default">Active</Badge>
                            <Badge v-else variant="secondary">Inactive</Badge>
                        </div>
                        <p class="text-muted-foreground">Product details and information</p>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <Button variant="outline" as-child>
                        <Link :href="`/products/${product.id}/edit`">
                            <Edit class="w-4 h-4 mr-2" />
                            Edit Product
                        </Link>
                    </Button>
                    <Button variant="destructive" @click="deleteProduct" :disabled="form.processing">
                        <Trash2 class="w-4 h-4 mr-2" />
                        Delete
                    </Button>
                </div>
            </div>

            <!-- Product Overview Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Pricing Breakdown</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div>
                            <div class="text-sm text-muted-foreground">Base Price (excl. VAT)</div>
                            <div class="text-lg font-semibold">{{ formatCurrency(basePrice) }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-muted-foreground">VAT ({{ vatRate }}%)</div>
                            <div class="text-lg font-semibold">{{ formatCurrency(vatAmount) }}</div>
                        </div>
                        <div class="border-t pt-2">
                            <div class="text-sm text-muted-foreground">Selling Price (incl. VAT)</div>
                            <div class="text-2xl font-bold">{{ formatCurrency(product.price) }}</div>
                        </div>
                    </CardContent>
                </Card>

                <Card v-if="product.cost">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Cost Price</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(product.cost) }}</div>
                    </CardContent>
                </Card>

                <Card v-if="product.cost">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Profit Margin</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">{{ profitMargin.toFixed(1) }}%</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Stock Level</CardTitle>
                        <Package class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center space-x-2">
                            <div class="text-2xl font-bold">{{ product.stock_quantity }}</div>
                            <div class="text-sm text-muted-foreground">{{ product.unit }}s</div>
                            <Badge v-if="stockStatus !== 'normal'" :variant="stockBadgeColor">
                                <AlertTriangle class="w-3 h-3 mr-1" />
                                {{ stockStatus === 'low' ? 'Low Stock' : 'Low Stock Warning' }}
                            </Badge>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Product Details -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Product Information -->
                <Card>
                    <CardHeader>
                        <CardTitle>Product Information</CardTitle>
                        <CardDescription>Basic product details and specifications</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4">
                            <div>
                                <div class="text-sm font-medium text-muted-foreground">Product Name</div>
                                <div class="text-base">{{ product.name }}</div>
                            </div>

                            <div v-if="product.description">
                                <div class="text-sm font-medium text-muted-foreground">Description</div>
                                <div class="text-base">{{ product.description }}</div>
                            </div>

                            <Separator />

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <div class="text-sm font-medium text-muted-foreground">SKU</div>
                                    <div class="text-base font-mono">{{ product.sku }}</div>
                                </div>

                                <div>
                                    <div class="text-sm font-medium text-muted-foreground">Category</div>
                                    <Badge variant="outline">{{ product.category }}</Badge>
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <div class="text-sm font-medium text-muted-foreground">Unit of Measurement</div>
                                    <div class="text-base">{{ product.unit }}</div>
                                </div>

                                <div>
                                    <div class="text-sm font-medium text-muted-foreground">Status</div>
                                    <Badge v-if="product.is_active" variant="default">Active</Badge>
                                    <Badge v-else variant="secondary">Inactive</Badge>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Inventory & Pricing -->
                <Card>
                    <CardHeader>
                        <CardTitle>Inventory & Pricing</CardTitle>
                        <CardDescription>Stock levels and pricing information</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <div class="text-sm font-medium text-muted-foreground">Selling Price</div>
                                    <div class="text-lg font-semibold">{{ formatCurrency(product.price) }}</div>
                                </div>

                                <div v-if="product.cost">
                                    <div class="text-sm font-medium text-muted-foreground">Cost Price</div>
                                    <div class="text-lg font-semibold">{{ formatCurrency(product.cost) }}</div>
                                </div>
                            </div>

                            <div v-if="product.cost">
                                <div class="text-sm font-medium text-muted-foreground">Profit per Unit</div>
                                <div class="text-lg font-semibold text-green-600">
                                    {{ formatCurrency(product.price - product.cost) }}
                                </div>
                            </div>

                            <Separator />

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <div class="text-sm font-medium text-muted-foreground">Current Stock</div>
                                    <div class="text-lg font-semibold">{{ product.stock_quantity }} {{ product.unit }}s</div>
                                </div>

                                <div v-if="product.min_stock_level">
                                    <div class="text-sm font-medium text-muted-foreground">Minimum Stock Level</div>
                                    <div class="text-lg font-semibold">{{ product.min_stock_level }} {{ product.unit }}s</div>
                                </div>
                            </div>

                            <div v-if="stockStatus !== 'normal'" class="p-3 rounded-lg bg-orange-50 border border-orange-200">
                                <div class="flex items-center space-x-2 text-orange-800">
                                    <AlertTriangle class="w-4 h-4" />
                                    <span class="text-sm font-medium">
                                        {{ stockStatus === 'low' ? 'Low Stock Alert' : 'Stock Warning' }}
                                    </span>
                                </div>
                                <p class="text-sm text-orange-700 mt-1">
                                    Stock level is {{ stockStatus === 'low' ? 'at or below' : 'approaching' }} the minimum threshold.
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Metadata -->
            <Card>
                <CardHeader>
                    <CardTitle>Metadata</CardTitle>
                    <CardDescription>Creation and modification dates</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <div class="text-sm font-medium text-muted-foreground">Created</div>
                            <div class="text-base">{{ formatDate(product.created_at) }}</div>
                        </div>

                        <div>
                            <div class="text-sm font-medium text-muted-foreground">Last Updated</div>
                            <div class="text-base">{{ formatDate(product.updated_at) }}</div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
