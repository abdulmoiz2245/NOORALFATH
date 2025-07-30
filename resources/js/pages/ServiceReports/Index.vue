<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Plus, Eye, Edit, Trash2, Download, Filter, Search, X, SortAsc, SortDesc } from 'lucide-vue-next';
import { ref, reactive, watch } from 'vue';

interface Client {
    id: number;
    name: string;
}

interface ServiceReport {
    id: number;
    service_report_number: string;
    client: Client;
    service_date: string;
    ac_work: boolean;
    plumbing_work: boolean;
    paint_work: boolean;
    electrical_work: boolean;
    civil_work: boolean;
    job_details: string;
    before_pictures?: string[];
    after_pictures?: string[];
    created_at: string;
}

interface ServiceReportsData {
    data: ServiceReport[];
    links: any[];
    from: number;
    to: number;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    current_page: number;
    last_page: number;
}

interface Filters {
    client_id: string;
    service_date_from: string;
    service_date_to: string;
    service_report_number: string;
    ac_work: boolean;
    plumbing_work: boolean;
    paint_work: boolean;
    electrical_work: boolean;
    civil_work: boolean;
    sort_by: string;
    sort_direction: string;
}

const props = defineProps<{
    serviceReports: ServiceReportsData;
    clients?: Client[];
    filters?: Partial<Filters>;
}>();

// Filter state
const showFilterDialog = ref(false);
const filterForm = reactive<Filters>({
    client_id: props.filters?.client_id || '',
    service_date_from: props.filters?.service_date_from || '',
    service_date_to: props.filters?.service_date_to || '',
    service_report_number: props.filters?.service_report_number || '',
    ac_work: props.filters?.ac_work || false,
    plumbing_work: props.filters?.plumbing_work || false,
    paint_work: props.filters?.paint_work || false,
    electrical_work: props.filters?.electrical_work || false,
    civil_work: props.filters?.civil_work || false,
    sort_by: props.filters?.sort_by || 'created_at',
    sort_direction: props.filters?.sort_direction || 'desc',
});

// Search state
const searchQuery = ref(props.filters?.service_report_number || '');

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Service Reports',
        href: '/service-reports',
    },
];

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const deleteServiceReport = (id: number) => {
    if (confirm('Are you sure you want to delete this service report?')) {
        router.delete(`/service-reports/${id}`);
    }
};

const downloadPdf = (id: number) => {
    window.open(`/service-reports/${id}/download-pdf`, '_blank');
};

const openImageModal = (imageUrl: string) => {
    window.open(imageUrl, '_blank');
};

const getServiceBadges = (serviceReport: ServiceReport) => {
    const services = [];
    if (serviceReport.ac_work) services.push({ label: 'A/C', variant: 'default' as const });
    if (serviceReport.plumbing_work) services.push({ label: 'Plumbing', variant: 'secondary' as const });
    if (serviceReport.paint_work) services.push({ label: 'Paint', variant: 'outline' as const });
    if (serviceReport.electrical_work) services.push({ label: 'Electrical', variant: 'destructive' as const });
    if (serviceReport.civil_work) services.push({ label: 'Civil', variant: 'secondary' as const });
    return services;
};

// Filter functions
const applyFilters = () => {
    const query: any = {};
    
    if (filterForm.client_id) query.client_id = filterForm.client_id;
    if (filterForm.service_date_from) query.service_date_from = filterForm.service_date_from;
    if (filterForm.service_date_to) query.service_date_to = filterForm.service_date_to;
    if (filterForm.service_report_number) query.service_report_number = filterForm.service_report_number;
    if (filterForm.ac_work) query.ac_work = '1';
    if (filterForm.plumbing_work) query.plumbing_work = '1';
    if (filterForm.paint_work) query.paint_work = '1';
    if (filterForm.electrical_work) query.electrical_work = '1';
    if (filterForm.civil_work) query.civil_work = '1';
    if (filterForm.sort_by !== 'created_at') query.sort_by = filterForm.sort_by;
    if (filterForm.sort_direction !== 'desc') query.sort_direction = filterForm.sort_direction;

    // Close dialog first, then navigate
    showFilterDialog.value = false;
    
    // Use setTimeout to ensure dialog closes before navigation
    setTimeout(() => {
        router.get('/service-reports', query, { preserveState: true });
    }, 100);
};

const clearFilters = () => {
    Object.assign(filterForm, {
        client_id: '',
        service_date_from: '',
        service_date_to: '',
        service_report_number: '',
        ac_work: false,
        plumbing_work: false,
        paint_work: false,
        electrical_work: false,
        civil_work: false,
        sort_by: 'created_at',
        sort_direction: 'desc',
    });
    searchQuery.value = '';
    
    // Close dialog first, then navigate
    showFilterDialog.value = false;
    
    // Use setTimeout to ensure dialog closes before navigation
    setTimeout(() => {
        router.get('/service-reports', {}, { preserveState: true });
    }, 100);
};

const quickSearch = () => {
    filterForm.service_report_number = searchQuery.value;
    applyFilters();
};

const sort = (column: string) => {
    if (filterForm.sort_by === column) {
        filterForm.sort_direction = filterForm.sort_direction === 'asc' ? 'desc' : 'asc';
    } else {
        filterForm.sort_by = column;
        filterForm.sort_direction = 'asc';
    }
    applyFilters();
};

const hasActiveFilters = () => {
    return filterForm.client_id !== '' ||
           filterForm.service_date_from !== '' ||
           filterForm.service_date_to !== '' ||
           filterForm.service_report_number !== '' ||
           filterForm.ac_work ||
           filterForm.plumbing_work ||
           filterForm.paint_work ||
           filterForm.electrical_work ||
           filterForm.civil_work ||
           filterForm.sort_by !== 'created_at' ||
           filterForm.sort_direction !== 'desc';
};

// Watch for Enter key on search
watch(searchQuery, (newValue) => {
    filterForm.service_report_number = newValue;
});
</script>

<template>
    <Head title="Service Reports" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Service Reports</h1>
                    <p class="text-muted-foreground">Manage your service reports and work orders</p>
                </div>
                <Button as-child>
                    <Link href="/service-reports/create">
                        <Plus class="w-4 h-4 mr-2" />
                        Create Service Report
                    </Link>
                </Button>
            </div>

            <!-- Search and Filter Bar -->
            <Card>
                <CardHeader class="pb-4">
                    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                        <!-- Search -->
                        <div class="flex gap-2 flex-1 max-w-md">
                            <div class="relative flex-1">
                                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                <Input
                                    v-model="searchQuery"
                                    placeholder="Search by report number..."
                                    class="pl-10"
                                    @keyup.enter="quickSearch"
                                />
                            </div>
                            <Button @click="quickSearch" size="sm">
                                Search
                            </Button>
                        </div>

                        <!-- Filter Actions -->
                        <div class="flex gap-2">
                            <!-- Clear Filters -->
                            <Button 
                                v-if="hasActiveFilters()" 
                                variant="outline" 
                                size="sm" 
                                @click="clearFilters"
                            >
                                <X class="w-4 h-4 mr-2" />
                                Clear
                            </Button>

                            <!-- Filter Dialog -->
                            <Dialog v-model:open="showFilterDialog">
                                <DialogTrigger as-child>
                                    <Button variant="outline" size="sm">
                                        <Filter class="w-4 h-4 mr-2" />
                                        Filters
                                        <Badge v-if="hasActiveFilters()" variant="secondary" class="ml-2">
                                            Active
                                        </Badge>
                                    </Button>
                                </DialogTrigger>
                                <DialogContent class="max-w-md">
                                    <DialogHeader>
                                        <DialogTitle>Filter Service Reports</DialogTitle>
                                        <DialogDescription>
                                            Apply filters to narrow down your search results.
                                        </DialogDescription>
                                    </DialogHeader>

                                    <div class="space-y-4">
                                        <!-- Client Filter -->
                                        <div class="space-y-2">
                                            <Label for="filter_client">Client</Label>
                                            <Select v-model="filterForm.client_id">
                                                <SelectTrigger>
                                                    <SelectValue placeholder="All clients" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="null">All clients</SelectItem>
                                                    <SelectItem 
                                                        v-for="client in (clients || [])" 
                                                        :key="client.id" 
                                                        :value="client.id.toString()"
                                                    >
                                                        {{ client.name }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>

                                        <!-- Date Range -->
                                        <div class="grid grid-cols-2 gap-2">
                                            <div class="space-y-2">
                                                <Label for="filter_date_from">From Date</Label>
                                                <Input
                                                    id="filter_date_from"
                                                    v-model="filterForm.service_date_from"
                                                    type="date"
                                                />
                                            </div>
                                            <div class="space-y-2">
                                                <Label for="filter_date_to">To Date</Label>
                                                <Input
                                                    id="filter_date_to"
                                                    v-model="filterForm.service_date_to"
                                                    type="date"
                                                />
                                            </div>
                                        </div>

                                        <!-- Service Types -->
                                        <div class="space-y-3">
                                            <Label>Service Types</Label>
                                            <div class="space-y-2">
                                                <div class="flex items-center space-x-2">
                                                    <Checkbox 
                                                        id="filter_ac_work"
                                                        :model-value="filterForm.ac_work"
                                                        @update:model-value="(value: boolean | 'indeterminate') => filterForm.ac_work = value === true"
                                                    />
                                                    <Label for="filter_ac_work" class="text-sm font-normal">A/C Work</Label>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <Checkbox 
                                                        id="filter_plumbing_work"
                                                        :model-value="filterForm.plumbing_work"
                                                        @update:model-value="(value: boolean | 'indeterminate') => filterForm.plumbing_work = value === true"
                                                    />
                                                    <Label for="filter_plumbing_work" class="text-sm font-normal">Plumbing</Label>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <Checkbox 
                                                        id="filter_paint_work"
                                                        :model-value="filterForm.paint_work"
                                                        @update:model-value="(value: boolean | 'indeterminate') => filterForm.paint_work = value === true"
                                                    />
                                                    <Label for="filter_paint_work" class="text-sm font-normal">Paint Work</Label>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <Checkbox 
                                                        id="filter_electrical_work"
                                                        :model-value="filterForm.electrical_work"
                                                        @update:model-value="(value: boolean | 'indeterminate') => filterForm.electrical_work = value === true"
                                                    />
                                                    <Label for="filter_electrical_work" class="text-sm font-normal">Electrical Work</Label>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <Checkbox 
                                                        id="filter_civil_work"
                                                        :model-value="filterForm.civil_work"
                                                        @update:model-value="(value: boolean | 'indeterminate') => filterForm.civil_work = value === true"
                                                    />
                                                    <Label for="filter_civil_work" class="text-sm font-normal">Civil Work</Label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Sort Options -->
                                        <div class="space-y-2">
                                            <Label for="filter_sort">Sort By</Label>
                                            <div class="grid grid-cols-2 gap-2">
                                                <Select v-model="filterForm.sort_by">
                                                    <SelectTrigger>
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="created_at">Created Date</SelectItem>
                                                        <SelectItem value="service_date">Service Date</SelectItem>
                                                        <SelectItem value="service_report_number">Report Number</SelectItem>
                                                    </SelectContent>
                                                </Select>
                                                <Select v-model="filterForm.sort_direction">
                                                    <SelectTrigger>
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="desc">Descending</SelectItem>
                                                        <SelectItem value="asc">Ascending</SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                        </div>
                                    </div>

                                    <DialogFooter class="gap-2">
                                        <Button variant="outline" @click="clearFilters">
                                            Clear All
                                        </Button>
                                        <Button @click="applyFilters">
                                            Apply Filters
                                        </Button>
                                    </DialogFooter>
                                </DialogContent>
                            </Dialog>
                        </div>
                    </div>
                </CardHeader>
            </Card>

            <!-- Service Reports Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Service Reports</CardTitle>
                    <CardDescription>
                        A list of all service reports in your system
                        <span v-if="serviceReports.total > 0">
                            ({{ serviceReports.total }} total)
                        </span>
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="cursor-pointer" @click="sort('service_report_number')">
                                    <div class="flex items-center gap-1">
                                        Report #
                                        <SortAsc v-if="filterForm.sort_by === 'service_report_number' && filterForm.sort_direction === 'asc'" class="h-4 w-4" />
                                        <SortDesc v-else-if="filterForm.sort_by === 'service_report_number' && filterForm.sort_direction === 'desc'" class="h-4 w-4" />
                                    </div>
                                </TableHead>
                                <TableHead>Client</TableHead>
                                <TableHead class="cursor-pointer" @click="sort('service_date')">
                                    <div class="flex items-center gap-1">
                                        Service Date
                                        <SortAsc v-if="filterForm.sort_by === 'service_date' && filterForm.sort_direction === 'asc'" class="h-4 w-4" />
                                        <SortDesc v-else-if="filterForm.sort_by === 'service_date' && filterForm.sort_direction === 'desc'" class="h-4 w-4" />
                                    </div>
                                </TableHead>
                                <TableHead>Services</TableHead>
                                <TableHead>Pictures</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="!serviceReports.data || serviceReports.data.length === 0">
                                <TableCell colspan="6" class="text-center py-8 text-muted-foreground">
                                    No service reports found matching your criteria.
                                </TableCell>
                            </TableRow>
                            <TableRow v-for="serviceReport in (serviceReports.data || [])" :key="serviceReport.id">
                                <TableCell class="font-medium">
                                    {{ serviceReport.service_report_number }}
                                </TableCell>
                                <TableCell>
                                    {{ serviceReport.client?.name || 'N/A' }}
                                </TableCell>
                                <TableCell>
                                    {{ formatDate(serviceReport.service_date) }}
                                </TableCell>
                                <TableCell>
                                    <div class="flex flex-wrap gap-1">
                                        <Badge 
                                            v-for="service in getServiceBadges(serviceReport)" 
                                            :key="service.label"
                                            :variant="service.variant"
                                            class="text-xs"
                                        >
                                            {{ service.label }}
                                        </Badge>
                                        <span v-if="getServiceBadges(serviceReport).length === 0" class="text-muted-foreground text-sm">
                                            No services
                                        </span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex gap-1">
                                        <!-- Before Pictures -->
                                        <div v-if="serviceReport.before_pictures && serviceReport.before_pictures.length > 0" class="flex gap-1">
                                            <img 
                                                v-for="(picture, index) in serviceReport.before_pictures.slice(0, 2)" 
                                                :key="`before-${index}`"
                                                :src="`/storage/${picture}`" 
                                                :alt="`Before ${index + 1}`"
                                                class="w-8 h-8 object-cover rounded border cursor-pointer"
                                                @click="openImageModal(`/storage/${picture}`)"
                                            >
                                            <span v-if="serviceReport.before_pictures.length > 2" class="text-xs text-muted-foreground">
                                                +{{ serviceReport.before_pictures.length - 2 }}
                                            </span>
                                        </div>
                                        
                                        <!-- After Pictures -->
                                        <div v-if="serviceReport.after_pictures && serviceReport.after_pictures.length > 0" class="flex gap-1 ml-2">
                                            <img 
                                                v-for="(picture, index) in serviceReport.after_pictures.slice(0, 2)" 
                                                :key="`after-${index}`"
                                                :src="`/storage/${picture}`" 
                                                :alt="`After ${index + 1}`"
                                                class="w-8 h-8 object-cover rounded border cursor-pointer"
                                                @click="openImageModal(`/storage/${picture}`)"
                                            >
                                            <span v-if="serviceReport.after_pictures.length > 2" class="text-xs text-muted-foreground">
                                                +{{ serviceReport.after_pictures.length - 2 }}
                                            </span>
                                        </div>
                                        
                                        <span v-if="(!serviceReport.before_pictures || serviceReport.before_pictures.length === 0) && 
                                                    (!serviceReport.after_pictures || serviceReport.after_pictures.length === 0)" 
                                              class="text-muted-foreground text-sm">
                                            No pictures
                                        </span>
                                    </div>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end space-x-2">
                                        <Button variant="ghost" size="sm" as-child>
                                            <Link :href="`/service-reports/${serviceReport.id}`">
                                                <Eye class="w-4 h-4" />
                                            </Link>
                                        </Button>
                                        <Button 
                                            variant="ghost" 
                                            size="sm" 
                                            @click="downloadPdf(serviceReport.id)"
                                        >
                                            <Download class="w-4 h-4" />
                                        </Button>
                                        <Button variant="ghost" size="sm" as-child>
                                            <Link :href="`/service-reports/${serviceReport.id}/edit`">
                                                <Edit class="w-4 h-4" />
                                            </Link>
                                        </Button>
                                        <Button 
                                            variant="ghost" 
                                            size="sm" 
                                            @click="deleteServiceReport(serviceReport.id)"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Pagination -->
                    <div v-if="serviceReports.links && serviceReports.links.length > 3" class="mt-6">
                        <nav class="flex items-center justify-between">
                            <div class="flex-1 flex justify-between sm:hidden">
                                <Button variant="outline" as-child v-if="serviceReports.prev_page_url">
                                    <Link :href="serviceReports.prev_page_url">Previous</Link>
                                </Button>
                                <Button variant="outline" as-child v-if="serviceReports.next_page_url">
                                    <Link :href="serviceReports.next_page_url">Next</Link>
                                </Button>
                            </div>
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-muted-foreground">
                                        Showing
                                        <span class="font-medium">{{ serviceReports.from || 0 }}</span>
                                        to
                                        <span class="font-medium">{{ serviceReports.to || 0 }}</span>
                                        of
                                        <span class="font-medium">{{ serviceReports.total || 0 }}</span>
                                        results
                                    </p>
                                </div>
                                <div>
                                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                        <template v-for="link in (serviceReports.links || [])" :key="link.label">
                                            <Button
                                                v-if="link.url"
                                                variant="outline"
                                                size="sm"
                                                as-child
                                                :class="{ 'bg-primary text-primary-foreground': link.active }"
                                            >
                                                <Link :href="link.url" v-html="link.label" />
                                            </Button>
                                            <Button
                                                v-else
                                                variant="outline"
                                                size="sm"
                                                disabled
                                                v-html="link.label"
                                            />
                                        </template>
                                    </nav>
                                </div>
                            </div>
                        </nav>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
