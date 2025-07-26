<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Plus, Search, Eye, Edit, Trash2, Package, DollarSign } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface Product {
    id: number;
    name: string;
    description?: string;
    sku?: string;
    price: number;
    cost?: number;
    category?: string;
    unit?: string;
    tax_rate?: number;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

interface Props {
    products: Product[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Products',
        href: '/products',
    },
];

const searchQuery = ref('');

const filteredProducts = computed(() => {
    if (!searchQuery.value) return props.products;
    return props.products.filter(product => 
        product.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        (product.sku && product.sku.toLowerCase().includes(searchQuery.value.toLowerCase())) ||
        (product.category && product.category.toLowerCase().includes(searchQuery.value.toLowerCase()))
    );
});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
};
</script>

<template>
    <Head title="Products" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Products</h1>
                    <p class="text-muted-foreground">Manage your product catalog</p>
                </div>
                <Button as-child>
                    <Link href="/products/create">
                        <Plus class="w-4 h-4 mr-2" />
                        Add Product
                    </Link>
                </Button>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Products</CardTitle>
                        <Package class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ props.products.length }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Active Products</CardTitle>
                        <Package class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ props.products.filter(p => p.is_active).length }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Avg. Price</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ props.products.length > 0 ? formatCurrency(props.products.reduce((sum, p) => sum + p.price, 0) / props.products.length) : '$0' }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Products Table -->
            <Card>
                <CardHeader>
                    <div class="flex justify-between items-center">
                        <div>
                            <CardTitle>All Products</CardTitle>
                            <CardDescription>Manage your product inventory</CardDescription>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="relative">
                                <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input
                                    placeholder="Search products..."
                                    v-model="searchQuery"
                                    class="pl-8"
                                />
                            </div>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="filteredProducts.length === 0" class="text-center py-8 text-muted-foreground">
                        <Package class="mx-auto h-12 w-12 mb-4" />
                        <p>No products found</p>
                        <Button as-child class="mt-4">
                            <Link href="/products/create">Create your first product</Link>
                        </Button>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left p-4">Product</th>
                                    <th class="text-left p-4">SKU</th>
                                    <th class="text-left p-4">Category</th>
                                    <th class="text-left p-4">Price</th>
                                    <th class="text-left p-4">Status</th>
                                    <th class="text-right p-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="product in filteredProducts" :key="product.id" class="border-b hover:bg-muted/50">
                                    <td class="p-4">
                                        <div>
                                            <p class="font-medium">{{ product.name }}</p>
                                            <p v-if="product.description" class="text-sm text-muted-foreground">{{ product.description }}</p>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <span class="text-sm font-mono">{{ product.sku || 'N/A' }}</span>
                                    </td>
                                    <td class="p-4">
                                        <span class="text-sm">{{ product.category || 'Uncategorized' }}</span>
                                    </td>
                                    <td class="p-4">
                                        <div>
                                            <p class="font-medium">{{ formatCurrency(product.price) }}</p>
                                            <p class="text-xs text-muted-foreground">Price (incl. VAT)</p>
                                            <p v-if="product.cost" class="text-sm text-muted-foreground">
                                                Cost: {{ formatCurrency(product.cost) }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <span 
                                            :class="product.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        >
                                            {{ product.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex justify-end space-x-2">
                                            <Button variant="outline" size="sm" as-child>
                                                <Link :href="`/products/${product.id}`">
                                                    <Eye class="w-4 h-4" />
                                                </Link>
                                            </Button>
                                            <Button variant="outline" size="sm" as-child>
                                                <Link :href="`/products/${product.id}/edit`">
                                                    <Edit class="w-4 h-4" />
                                                </Link>
                                            </Button>
                                            <Button variant="destructive" size="sm">
                                                <Trash2 class="w-4 h-4" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
