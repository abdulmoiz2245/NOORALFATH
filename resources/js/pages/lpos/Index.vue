<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Plus, Search, Eye, Edit, Download, Send, Copy, FileText } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface LocalPurchaseOrder {
    id: number;
    lpo_number: string;
    vendor: {
        id: number;
        name: string;
    };
    vendor_id: number;
    trn_number?: string;
    issue_date: string;
    subtotal: number;
    total_tax: number;
    total_after_tax: number;
    items?: any[]; // This will be for item count
    created_at: string;
}

interface Props {
    lpos: LocalPurchaseOrder[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Local Purchase Orders',
        href: '/lpos',
    },
];

const searchQuery = ref('');

const filteredLpos = computed(() => {
    let filtered = props.lpos;

    if (searchQuery.value) {
        filtered = filtered.filter(lpo => 
            lpo.lpo_number.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            lpo.vendor.name.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }

    return filtered;
});

const stats = computed(() => ({
    total: props.lpos.reduce((sum: number, lpo: LocalPurchaseOrder) => sum + lpo.total_after_tax, 0),
    count: props.lpos.length,
}));

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'AED'
    }).format(amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString();
};
</script>

<template>
    <Head title="Local Purchase Orders" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Local Purchase Orders</h1>
                    <p class="text-muted-foreground">Manage your vendor purchase orders</p>
                </div>
                <Button as-child>
                    <Link href="/lpos/create">
                        <Plus class="w-4 h-4 mr-2" />
                        New LPO
                    </Link>
                </Button>
            </div>

            <!-- Stats Cards -->
            <!-- <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Value</CardTitle>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(stats.total) }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Pending</CardTitle>
                        <Send class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-yellow-600">{{ stats.pending }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Approved</CardTitle>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">{{ stats.approved }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Received</CardTitle>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-blue-600">{{ stats.received }}</div>
                    </CardContent>
                </Card>
            </div> -->

            <!-- LPOs Table -->
            <Card>
                <CardHeader>
                    <div class="flex justify-between items-center">
                        <div>
                            <CardTitle>All Local Purchase Orders</CardTitle>
                            <CardDescription>Track and manage your vendor purchase orders</CardDescription>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="relative">
                                <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input
                                    placeholder="Search LPOs..."
                                    v-model="searchQuery"
                                    class="pl-8"
                                />
                            </div>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="filteredLpos.length === 0" class="text-center py-8 text-muted-foreground">
                        <FileText class="mx-auto h-12 w-12 mb-4" />
                        <p>No Local Purchase Orders found</p>
                        <Button as-child class="mt-4">
                            <Link href="/lpos/create">Create your first LPO</Link>
                        </Button>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left p-4">LPO Number</th>
                                    <th class="text-left p-4">Vendor</th>
                                    <th class="text-left p-4">Issue Date</th>
                                    <th class="text-left p-4">TRN</th>
                                    <th class="text-left p-4">Amount</th>
                                    <th class="text-right p-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="lpo in filteredLpos" :key="lpo.id" class="border-b hover:bg-muted/50">
                                    <td class="p-4">
                                        <div>
                                            <p class="font-medium">{{ lpo.lpo_number }}</p>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div>
                                            <p class="font-medium">{{ lpo.vendor.name }}</p>
                                        </div>
                                    </td>
                                    <td class="p-4">{{ formatDate(lpo.issue_date) }}</td>
                                    <td class="p-4">{{ lpo.trn_number || 'N/A' }}</td>
                                    <td class="p-4">
                                        <div>
                                            <p class="font-medium">{{ formatCurrency(lpo.total_after_tax) }}</p>
                                            <p class="text-sm text-muted-foreground">
                                                {{ lpo.items?.length || 0 }} items
                                            </p>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex justify-end space-x-2">
                                            <Button variant="outline" size="sm" as-child>
                                                <Link :href="`/lpos/${lpo.id}`">
                                                    <Eye class="w-4 h-4" />
                                                </Link>
                                            </Button>
                                            <Button variant="outline" size="sm" as-child>
                                                <Link :href="`/lpos/${lpo.id}/edit`">
                                                    <Edit class="w-4 h-4" />
                                                </Link>
                                            </Button>
                                            <!-- <Button variant="outline" size="sm">
                                                <Copy class="w-4 h-4" />
                                            </Button>
                                            <Button variant="outline" size="sm">
                                                <Send class="w-4 h-4" />
                                            </Button>
                                            <Button variant="outline" size="sm">
                                                <Download class="w-4 h-4" />
                                            </Button> -->
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
