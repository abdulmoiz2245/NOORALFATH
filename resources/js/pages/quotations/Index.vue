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

interface Quotation {
    id: number;
    quotation_number: string;
    client: string;
    client_id: number;
    project?: string;
    project_id?: number;
    status: string;
    issue_date: string;
    valid_until: string;
    subtotal: number;
    tax_amount: number;
    total_amount: number;
    items_count: number;
    created_at: string;
}

interface Props {
    quotations: Quotation[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Quotations',
        href: '/quotations',
    },
];

const searchQuery = ref('');
const statusFilter = ref('all');

const filteredQuotations = computed(() => {
    let filtered = props.quotations;

    if (statusFilter.value !== 'all') {
        filtered = filtered.filter(quotation => quotation.status === statusFilter.value);
    }

    if (searchQuery.value) {
        filtered = filtered.filter(quotation => 
            quotation.quotation_number.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            quotation.client.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }

    return filtered;
});

const stats = computed(() => ({
    total: props.quotations.reduce((sum: number, quo: Quotation) => sum + quo.total_amount, 0),
    sent: props.quotations.filter((quo: Quotation) => quo.status === 'sent').length,
    accepted: props.quotations.filter((quo: Quotation) => quo.status === 'accepted').length,
    rejected: props.quotations.filter((quo: Quotation) => quo.status === 'rejected').length,
}));

const getStatusColor = (status: string) => {
    switch (status) {
        case 'accepted':
            return 'bg-green-100 text-green-800';
        case 'sent':
            return 'bg-blue-100 text-blue-800';
        case 'rejected':
            return 'bg-red-100 text-red-800';
        case 'expired':
            return 'bg-orange-100 text-orange-800';
        case 'draft':
            return 'bg-gray-100 text-gray-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

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
    <Head title="Quotations" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Quotations</h1>
                    <p class="text-muted-foreground">Manage your project quotes</p>
                </div>
                <Button as-child>
                    <Link href="/quotations/create">
                        <Plus class="w-4 h-4 mr-2" />
                        New Quotation
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
                        <CardTitle class="text-sm font-medium">Sent</CardTitle>
                        <Send class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-blue-600">{{ stats.sent }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Accepted</CardTitle>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">{{ stats.accepted }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Rejected</CardTitle>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-600">{{ stats.rejected }}</div>
                    </CardContent>
                </Card>
            </div> -->

            <!-- Quotations Table -->
            <Card>
                <CardHeader>
                    <div class="flex justify-between items-center">
                        <div>
                            <CardTitle>All Quotations</CardTitle>
                            <CardDescription>Track and manage your client quotations</CardDescription>
                        </div>
                        <div class="flex items-center space-x-2">
                            <select v-model="statusFilter" class="border rounded px-2 py-1">
                                <option value="all">All Status</option>
                                <option value="draft">Draft</option>
                                <option value="sent">Sent</option>
                                <option value="accepted">Accepted</option>
                                <option value="rejected">Rejected</option>
                                <option value="expired">Expired</option>
                            </select>
                            <div class="relative">
                                <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input
                                    placeholder="Search quotations..."
                                    v-model="searchQuery"
                                    class="pl-8"
                                />
                            </div>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="filteredQuotations.length === 0" class="text-center py-8 text-muted-foreground">
                        <FileText class="mx-auto h-12 w-12 mb-4" />
                        <p>No quotations found</p>
                        <Button as-child class="mt-4">
                            <Link href="/quotations/create">Create your first quotation</Link>
                        </Button>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left p-4">Number</th>
                                    <th class="text-left p-4">Client</th>
                                    <th class="text-left p-4">Issue Date</th>
                                    <th class="text-left p-4">Valid Until</th>
                                    <th class="text-left p-4">Amount</th>
                                    <th class="text-left p-4">Status</th>
                                    <th class="text-right p-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="quotation in filteredQuotations" :key="quotation.id" class="border-b hover:bg-muted/50">
                                    <td class="p-4">
                                        <div>
                                            <p class="font-medium">{{ quotation.quotation_number }}</p>
                                            <p class="text-sm text-muted-foreground">{{ quotation.project || 'No project' }}</p>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div>
                                            <p class="font-medium">{{ quotation.client }}</p>
                                        </div>
                                    </td>
                                    <td class="p-4">{{ formatDate(quotation.issue_date) }}</td>
                                    <td class="p-4">{{ formatDate(quotation.valid_until) }}</td>
                                    <td class="p-4">
                                        <div>
                                            <p class="font-medium">{{ formatCurrency(quotation.total_amount) }}</p>
                                            <p class="text-sm text-muted-foreground">
                                                {{ quotation.items_count }} items
                                            </p>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <span 
                                            :class="getStatusColor(quotation.status)" 
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                                        >
                                            {{ quotation.status }}
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex justify-end space-x-2">
                                            <Button variant="outline" size="sm" as-child>
                                                <Link :href="`/quotations/${quotation.id}`">
                                                    <Eye class="w-4 h-4" />
                                                </Link>
                                            </Button>
                                            <Button variant="outline" size="sm" as-child>
                                                <Link :href="`/quotations/${quotation.id}/edit`">
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
