<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Plus, Search, Eye, Edit, Trash2, Building, Mail, Phone } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface Vendor {
    id: number;
    name: string;
    company_name?: string;
    email?: string;
    phone?: string;
    address?: string;
    city?: string;
    state?: string;
    postal_code?: string;
    country?: string;
    expenses_count: number;
    created_at: string;
}

interface Props {
    vendors: Vendor[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Vendors',
        href: '/vendors',
    },
];

const searchQuery = ref('');

const filteredVendors = computed(() => {
    if (!searchQuery.value) return props.vendors;
    return props.vendors.filter(vendor => 
        vendor.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        (vendor.company_name && vendor.company_name.toLowerCase().includes(searchQuery.value.toLowerCase())) ||
        (vendor.email && vendor.email.toLowerCase().includes(searchQuery.value.toLowerCase()))
    );
});

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString();
};
</script>

<template>
    <Head title="Vendors" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Vendors</h1>
                    <p class="text-muted-foreground">Manage your supplier relationships</p>
                </div>
                <Button as-child>
                    <Link href="/vendors/create">
                        <Plus class="w-4 h-4 mr-2" />
                        Add Vendor
                    </Link>
                </Button>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Vendors</CardTitle>
                        <Building class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ props.vendors.length }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">With Email</CardTitle>
                        <Mail class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ props.vendors.filter(v => v.email).length }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Expenses</CardTitle>
                        <Building class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ props.vendors.reduce((sum, v) => sum + v.expenses_count, 0) }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Vendors Table -->
            <Card>
                <CardHeader>
                    <div class="flex justify-between items-center">
                        <div>
                            <CardTitle>All Vendors</CardTitle>
                            <CardDescription>Manage your vendor relationships</CardDescription>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="relative">
                                <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input
                                    placeholder="Search vendors..."
                                    v-model="searchQuery"
                                    class="pl-8"
                                />
                            </div>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="filteredVendors.length === 0" class="text-center py-8 text-muted-foreground">
                        <Building class="mx-auto h-12 w-12 mb-4" />
                        <p>No vendors found</p>
                        <Button as-child class="mt-4">
                            <Link href="/vendors/create">Add your first vendor</Link>
                        </Button>
                    </div>

                    <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <Card v-for="vendor in filteredVendors" :key="vendor.id" class="hover:shadow-md transition-shadow">
                            <CardHeader>
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <CardTitle class="text-lg">{{ vendor.name }}</CardTitle>
                                        <CardDescription v-if="vendor.company_name">{{ vendor.company_name }}</CardDescription>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-2">
                                    <div v-if="vendor.email" class="flex items-center space-x-2 text-sm">
                                        <Mail class="w-4 h-4 text-muted-foreground" />
                                        <span>{{ vendor.email }}</span>
                                    </div>
                                    
                                    <div v-if="vendor.phone" class="flex items-center space-x-2 text-sm">
                                        <Phone class="w-4 h-4 text-muted-foreground" />
                                        <span>{{ vendor.phone }}</span>
                                    </div>
                                    
                                    <div v-if="vendor.city || vendor.state" class="flex items-center space-x-2 text-sm">
                                        <Building class="w-4 h-4 text-muted-foreground" />
                                        <span>
                                            {{ [vendor.city, vendor.state].filter(Boolean).join(', ') }}
                                        </span>
                                    </div>
                                    
                                    <div class="flex justify-between items-center text-sm pt-2 border-t">
                                        <span class="text-muted-foreground">Expenses:</span>
                                        <span class="font-medium">{{ vendor.expenses_count }}</span>
                                    </div>
                                    
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-muted-foreground">Added:</span>
                                        <span>{{ formatDate(vendor.created_at) }}</span>
                                    </div>
                                </div>
                                
                                <div class="flex justify-end space-x-2 mt-4">
                                    <Button variant="outline" size="sm" as-child>
                                        <Link :href="`/vendors/${vendor.id}`">
                                            <Eye class="w-4 h-4" />
                                        </Link>
                                    </Button>
                                    <Button variant="outline" size="sm" as-child>
                                        <Link :href="`/vendors/${vendor.id}/edit`">
                                            <Edit class="w-4 h-4" />
                                        </Link>
                                    </Button>
                                    <Button variant="destructive" size="sm">
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
