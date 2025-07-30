<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Plus, Eye, Edit, Trash2 } from 'lucide-vue-next';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

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
}

defineProps<{
    serviceReports: ServiceReportsData;
}>();

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

const getServiceBadges = (serviceReport: ServiceReport) => {
    const services = [];
    if (serviceReport.ac_work) services.push({ label: 'A/C', variant: 'default' });
    if (serviceReport.plumbing_work) services.push({ label: 'Plumbing', variant: 'secondary' });
    if (serviceReport.paint_work) services.push({ label: 'Paint', variant: 'outline' });
    if (serviceReport.electrical_work) services.push({ label: 'Electrical', variant: 'destructive' });
    if (serviceReport.civil_work) services.push({ label: 'Civil', variant: 'secondary' });
    return services;
};
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

            <!-- Service Reports Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Service Reports</CardTitle>
                    <CardDescription>A list of all service reports in your system</CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Report #</TableHead>
                                <TableHead>Client</TableHead>
                                <TableHead>Service Date</TableHead>
                                <TableHead>Services</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="serviceReport in serviceReports.data" :key="serviceReport.id">
                                <TableCell class="font-medium">
                                    {{ serviceReport.service_report_number }}
                                </TableCell>
                                <TableCell>
                                    {{ serviceReport.client.name }}
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
                                <TableCell class="text-right">
                                    <div class="flex justify-end space-x-2">
                                        <Button variant="ghost" size="sm" as-child>
                                            <Link :href="`/service-reports/${serviceReport.id}`">
                                                <Eye class="w-4 h-4" />
                                            </Link>
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
                                        <span class="font-medium">{{ serviceReports.from }}</span>
                                        to
                                        <span class="font-medium">{{ serviceReports.to }}</span>
                                        of
                                        <span class="font-medium">{{ serviceReports.total }}</span>
                                        results
                                    </p>
                                </div>
                                <div>
                                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                        <template v-for="link in serviceReports.links" :key="link.label">
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
