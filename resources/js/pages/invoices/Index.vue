<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Badge } from '@/components/ui/badge';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
import { Plus, Search, Eye, Edit, Download, Send, Copy, Filter, MoreHorizontal, Trash2, X } from 'lucide-vue-next';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import { useToast } from '@/composables/useToast';

interface Invoice {
    id: number;
    invoice_number: string;
    client: string;
    client_id: number;
    project?: string;
    project_id?: number;
    status: string;
    issue_date: string;
    due_date: string;
    subtotal: number;
    tax_amount: number;
    total_amount: number;
    items_count: number;
    created_at: string;
}

interface Client {
    id: number;
    name: string;
}

interface Status {
    value: string;
    label: string;
}

interface FilterData {
    clients: Client[];
    statuses: Status[];
}

interface Props {
    invoices: Invoice[];
    stats: {
        total: number;
        pending: number;
        overdue: number;
        paid: number;
        paid_amount: number;
        pending_amount: number;
        overdue_amount: number;
        draft: number;
    };
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
];

const searchQuery = ref('');
const statusFilter = ref('');
const clientFilter = ref('');
const filterDialogOpen = ref(false);
const filterData = ref<FilterData>({ clients: [], statuses: [] });
const isLoadingFilters = ref(false);

// Applied filters for display
const appliedFilters = computed(() => {
    const filters = [];
    if (statusFilter.value) {
        const status = filterData.value.statuses.find(s => s.value === statusFilter.value);
        if (status) {
            filters.push({ type: 'status', label: status.label, value: statusFilter.value });
        }
    }
    if (clientFilter.value) {
        const client = filterData.value.clients.find(c => c.id.toString() === clientFilter.value);
        if (client) {
            filters.push({ type: 'client', label: client.name, value: clientFilter.value });
        }
    }
    return filters;
});

// Fetch filter data from backend
const fetchFilterData = async () => {
    if (filterData.value.clients.length > 0) return; // Already loaded
    
    isLoadingFilters.value = true;
    try {
        const response = await fetch('/api/invoices/filter-data');
        if (response.ok) {
            const data = await response.json();
            filterData.value = data;
        }
    } catch (err) {
        error('Error!', 'Failed to load filter data');
    } finally {
        isLoadingFilters.value = false;
    }
};

// Apply filters function
const applyFilters = () => {
    const params = new URLSearchParams();
    
    if (searchQuery.value) params.append('search', searchQuery.value);
    if (statusFilter.value) params.append('status', statusFilter.value);
    if (clientFilter.value) params.append('client_id', clientFilter.value);
    
    const url = params.toString() ? `/invoices?${params.toString()}` : '/invoices';
    router.get(url);
    filterDialogOpen.value = false;
};

// Clear filters function
const clearFilters = () => {
    statusFilter.value = '';
    clientFilter.value = '';
    router.get('/invoices');
};

// Remove individual filter
const removeFilter = (filterType: string) => {
    if (filterType === 'status') {
        statusFilter.value = '';
    } else if (filterType === 'client') {
        clientFilter.value = '';
    }
    applyFilters();
};

// Watch for search query changes and apply debounced search
let searchTimeout: ReturnType<typeof setTimeout>;
watch(searchQuery, (newValue) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
});

// Load filter data when dialog opens
watch(filterDialogOpen, (isOpen) => {
    if (isOpen) {
        fetchFilterData();
    }
});

// Initialize filters from URL parameters
onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    searchQuery.value = urlParams.get('search') || '';
    statusFilter.value = urlParams.get('status') || '';
    clientFilter.value = urlParams.get('client_id') || '';
});

// Status change function
const changeInvoiceStatus = (invoiceId: number, newStatus: string) => {
    router.patch(`/invoices/${invoiceId}/status`, {
        status: newStatus
    }, {
        onSuccess: () => {
            success('Success!', `Invoice status changed to ${newStatus}`);
        },
        onError: () => {
            error('Error!', 'Failed to change invoice status');
        }
    });
};

// Delete function
const deleteInvoice = (invoiceId: number, invoiceNumber: string) => {
    if (confirm(`Are you sure you want to delete invoice ${invoiceNumber}? This action cannot be undone.`)) {
        router.delete(`/invoices/${invoiceId}`, {
            onSuccess: () => {
                success('Success!', 'Invoice deleted successfully');
            },
            onError: () => {
                error('Error!', 'Failed to delete invoice');
            }
        });
    }
};

const filteredInvoices = computed(() => {
    // Since filtering is now done server-side, just return the invoices
    return props.invoices;
});

const getStatusColor = (status: string) => {
    switch (status) {
        case 'paid':
            return 'bg-green-100 text-green-800';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'overdue':
            return 'bg-red-100 text-red-800';
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
    <Head title="Invoices" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Invoices</h1>
                    <p class="text-muted-foreground">Manage your invoices and track payments</p>
                </div>
                <Button as-child>
                    <Link href="/invoices/create">
                        <Plus class="w-4 h-4 mr-2" />
                        Create Invoice
                    </Link>
                </Button>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Total Value</p>
                                <p class="text-2xl font-bold">{{ formatCurrency(props.stats.total) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Amount Paid</p>
                                <p class="text-2xl font-bold text-green-600">{{ formatCurrency(props.stats.paid_amount) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Pending</p>
                                <p class="text-2xl font-bold text-yellow-600">{{ props.stats.pending }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Overdue</p>
                                <p class="text-2xl font-bold text-red-600">{{ props.stats.overdue }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Search and Filters -->
            <Card>
                <CardHeader>
                    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
                        <div class="flex items-center space-x-2 flex-1">
                            <Search class="w-4 h-4 text-muted-foreground" />
                            <Input
                                v-model="searchQuery"
                                placeholder="Search invoices..."
                                class="max-w-sm"
                            />
                        </div>
                        <div class="flex items-center space-x-2">
                            <Dialog v-model:open="filterDialogOpen">
                                <DialogTrigger asChild>
                                    <Button variant="outline">
                                        <Filter class="w-4 h-4 mr-2" />
                                        Filters
                                        <Badge v-if="appliedFilters.length > 0" variant="secondary" class="ml-2">
                                            {{ appliedFilters.length }}
                                        </Badge>
                                    </Button>
                                </DialogTrigger>
                                <DialogContent class="sm:max-w-md">
                                    <DialogHeader>
                                        <DialogTitle>Filter Invoices</DialogTitle>
                                        <DialogDescription>
                                            Apply filters to narrow down your invoice list
                                        </DialogDescription>
                                    </DialogHeader>
                                    <div class="space-y-4 py-4">
                                        <!-- Status Filter -->
                                        <div class="space-y-2">
                                            <label class="text-sm font-medium">Status</label>
                                            <Select v-model="statusFilter">
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Select status" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="all">All Statuses</SelectItem>
                                                    <SelectItem 
                                                        v-for="status in filterData.statuses" 
                                                        :key="status.value" 
                                                        :value="status.value"
                                                    >
                                                        {{ status.label }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>

                                        <!-- Client Filter -->
                                        <div class="space-y-2">
                                            <label class="text-sm font-medium">Client</label>
                                            <Select v-model="clientFilter">
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Select client" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="all">All Clients</SelectItem>
                                                    <SelectItem 
                                                        v-for="client in filterData.clients" 
                                                        :key="client.id" 
                                                        :value="client.id.toString()"
                                                    >
                                                        {{ client.name }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                    </div>
                                    <div class="flex justify-between">
                                        <Button variant="outline" @click="clearFilters">
                                            Clear All
                                        </Button>
                                        <Button @click="applyFilters">
                                            Apply Filters
                                        </Button>
                                    </div>
                                </DialogContent>
                            </Dialog>
                        </div>
                    </div>

                    <!-- Applied Filters Display -->
                    <div v-if="appliedFilters.length > 0" class="flex flex-wrap gap-2 mt-4">
                        <Badge 
                            v-for="filter in appliedFilters" 
                            :key="filter.type" 
                            variant="secondary" 
                            class="flex items-center gap-1"
                        >
                            {{ filter.label }}
                            <X 
                                class="w-3 h-3 cursor-pointer hover:text-destructive" 
                                @click="removeFilter(filter.type)" 
                            />
                        </Badge>
                    </div>
                </CardHeader>
            </Card>

            <!-- Invoices Table -->
            <Card>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b bg-muted/50">
                                    <th class="text-left p-4 font-medium">Invoice #</th>
                                    <th class="text-left p-4 font-medium">Client</th>
                                    <th class="text-left p-4 font-medium">Issue Date</th>
                                    <th class="text-left p-4 font-medium">Due Date</th>
                                    <th class="text-left p-4 font-medium">Amount</th>
                                    <th class="text-left p-4 font-medium">Status</th>
                                    <th class="text-right p-4 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="invoice in filteredInvoices" :key="invoice.id" class="border-b hover:bg-muted/25">
                                    <td class="p-4">
                                        <Link :href="`/invoices/${invoice.id}`" class="font-medium text-primary hover:underline">
                                            {{ invoice.invoice_number }}
                                        </Link>
                                    </td>
                                    <td class="p-4">
                                        <div>
                                            <p class="font-medium">{{ invoice.client }}</p>
                                            <p class="text-sm text-muted-foreground">{{ invoice.project || 'No project' }}</p>
                                        </div>
                                    </td>
                                    <td class="p-4">{{ formatDate(invoice.issue_date) }}</td>
                                    <td class="p-4">{{ formatDate(invoice.due_date) }}</td>
                                    <td class="p-4">
                                        <div>
                                            <p class="font-medium">{{ formatCurrency(invoice.total_amount) }}</p>
                                            <p class="text-sm text-muted-foreground">
                                                {{ invoice.items_count }} items
                                            </p>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <span 
                                            :class="getStatusColor(invoice.status)" 
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                                        >
                                            {{ invoice.status }}
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex justify-end space-x-2">
                                            <Button variant="outline" size="sm" as-child>
                                                <Link :href="`/invoices/${invoice.id}`">
                                                    <Eye class="w-4 h-4" />
                                                </Link>
                                            </Button>
                                            <Button variant="outline" size="sm" as-child>
                                                <Link :href="`/invoices/${invoice.id}/edit`">
                                                    <Edit class="w-4 h-4" />
                                                </Link>
                                            </Button>
                                            <!-- <Button variant="outline" size="sm">
                                                <Download class="w-4 h-4" />
                                            </Button> -->
                                            <!-- <Button variant="outline" size="sm" v-if="invoice.status !== 'draft'">
                                                <Send class="w-4 h-4" />
                                            </Button> -->
                                            
                                            <!-- Status Change & More Actions Dropdown -->
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button variant="outline" size="sm">
                                                        <MoreHorizontal class="w-4 h-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuItem 
                                                        v-if="invoice.status !== 'draft'"
                                                        @click="changeInvoiceStatus(invoice.id, 'draft')"
                                                    >
                                                        Mark as Draft
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem 
                                                        v-if="invoice.status !== 'pending'"
                                                        @click="changeInvoiceStatus(invoice.id, 'pending')"
                                                    >
                                                        Mark as Pending
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem 
                                                        v-if="invoice.status !== 'paid'"
                                                        @click="changeInvoiceStatus(invoice.id, 'paid')"
                                                    >
                                                        Mark as Paid
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem 
                                                        v-if="invoice.status !== 'overdue'"
                                                        @click="changeInvoiceStatus(invoice.id, 'overdue')"
                                                    >
                                                        Mark as Overdue
                                                    </DropdownMenuItem>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem 
                                                        @click="deleteInvoice(invoice.id, invoice.invoice_number)"
                                                        class="text-red-600 focus:text-red-600"
                                                    >
                                                        <Trash2 class="w-4 h-4 mr-2" />
                                                        Delete Invoice
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <!-- Empty State -->
            <Card v-if="filteredInvoices.length === 0" class="text-center py-12">
                <CardContent>
                    <div class="w-12 h-12 rounded-full bg-muted flex items-center justify-center mx-auto mb-4">
                        <Plus class="w-6 h-6 text-muted-foreground" />
                    </div>
                    <h3 class="text-lg font-semibold mb-2">No invoices found</h3>
                    <p class="text-muted-foreground mb-4">
                        {{ searchQuery || appliedFilters.length > 0 ? 'Try adjusting your search criteria or filters' : 'Get started by creating your first invoice' }}
                    </p>
                    <Button v-if="!searchQuery && appliedFilters.length === 0" as-child>
                        <Link href="/invoices/create">
                            <Plus class="w-4 h-4 mr-2" />
                            Create Invoice
                        </Link>
                    </Button>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
