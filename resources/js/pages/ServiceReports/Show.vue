<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Edit, Trash2, ArrowLeft, CheckCircle, Download } from 'lucide-vue-next';

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

const props = defineProps<{
    serviceReport: ServiceReport;
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
    {
        title: 'View Report',
        href: `/service-reports/${props.serviceReport.id}`,
    },
];

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const formatDateTime = (dateTime: string) => {
    return new Date(dateTime).toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const deleteServiceReport = () => {
    if (confirm('Are you sure you want to delete this service report? This action cannot be undone.')) {
        router.delete(`/service-reports/${props.serviceReport.id}`);
    }
};

const downloadPdf = () => {
    window.open(`/service-reports/${props.serviceReport.id}/download-pdf`, '_blank');
};

const openImageModal = (imageUrl: string) => {
    window.open(imageUrl, '_blank');
};

const getServiceList = () => {
    const services = [];
    if (props.serviceReport.ac_work) services.push('A/C Work');
    if (props.serviceReport.plumbing_work) services.push('Plumbing');
    if (props.serviceReport.paint_work) services.push('Paint Work');
    if (props.serviceReport.electrical_work) services.push('Electrical Work');
    if (props.serviceReport.civil_work) services.push('Civil Work');
    return services;
};
</script>

<template>
    <Head title="Service Report Details" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Service Report Details</h1>
                    <p class="text-muted-foreground">{{ serviceReport.service_report_number }}</p>
                </div>
                <div class="flex space-x-3">
                    <Button variant="default" @click="downloadPdf">
                        <Download class="w-4 h-4 mr-2" />
                        Download PDF
                    </Button>
                    <Button variant="outline" as-child>
                        <Link :href="`/service-reports/${serviceReport.id}/edit`">
                            <Edit class="w-4 h-4 mr-2" />
                            Edit
                        </Link>
                    </Button>
                    <Button variant="outline" as-child>
                        <Link href="/service-reports">
                            <ArrowLeft class="w-4 h-4 mr-2" />
                            Back to List
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Service Report Information -->
                <div class="lg:col-span-2 space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Service Report Information</CardTitle>
                            <CardDescription>Basic details about this service report</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground">Report Number</h4>
                                    <p class="text-sm font-mono">{{ serviceReport.service_report_number }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground">Client</h4>
                                    <p class="text-sm">{{ serviceReport.client.name }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground">Service Date</h4>
                                    <p class="text-sm">{{ formatDate(serviceReport.service_date) }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground">Created Date</h4>
                                    <p class="text-sm">{{ formatDateTime(serviceReport.created_at) }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Job Details -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Job Details</CardTitle>
                            <CardDescription>Detailed description of the work performed</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="bg-muted rounded-lg p-4">
                                <p class="text-sm whitespace-pre-wrap">{{ serviceReport.job_details }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Before Pictures -->
                    <Card v-if="serviceReport.before_pictures && serviceReport.before_pictures.length > 0">
                        <CardHeader>
                            <CardTitle>Before Pictures</CardTitle>
                            <CardDescription>Pictures taken before the work began</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-2 gap-4">
                                <div v-for="(picture, index) in serviceReport.before_pictures" :key="index" 
                                     class="relative group">
                                    <img 
                                        :src="`/storage/${picture}`" 
                                        :alt="`Before picture ${index + 1}`"
                                        class="w-full h-48 object-cover rounded-lg cursor-pointer hover:opacity-80 transition-opacity"
                                        @click="openImageModal(`/storage/${picture}`)"
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- After Pictures -->
                    <Card v-if="serviceReport.after_pictures && serviceReport.after_pictures.length > 0">
                        <CardHeader>
                            <CardTitle>After Pictures</CardTitle>
                            <CardDescription>Pictures taken after the work was completed</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-2 gap-4">
                                <div v-for="(picture, index) in serviceReport.after_pictures" :key="index" 
                                     class="relative group">
                                    <img 
                                        :src="`/storage/${picture}`" 
                                        :alt="`After picture ${index + 1}`"
                                        class="w-full h-48 object-cover rounded-lg cursor-pointer hover:opacity-80 transition-opacity"
                                        @click="openImageModal(`/storage/${picture}`)"
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Services Performed -->
                <div class="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Services Performed</CardTitle>
                            <CardDescription>Types of work completed</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div v-if="getServiceList().length > 0">
                                    <div v-for="service in getServiceList()" :key="service" class="flex items-center space-x-2">
                                        <CheckCircle class="w-4 h-4 text-green-600" />
                                        <span class="text-sm">{{ service }}</span>
                                    </div>
                                </div>
                                <div v-else class="text-muted-foreground text-sm italic">
                                    No services were selected for this report
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Actions -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Actions</CardTitle>
                            <CardDescription>Manage this service report</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <Button variant="outline" class="w-full" @click="downloadPdf">
                                <Download class="w-4 h-4 mr-2" />
                                Download PDF
                            </Button>
                            <Button variant="outline" class="w-full" as-child>
                                <Link :href="`/service-reports/${serviceReport.id}/edit`">
                                    <Edit class="w-4 h-4 mr-2" />
                                    Edit Report
                                </Link>
                            </Button>
                            <Button 
                                variant="destructive" 
                                class="w-full"
                                @click="deleteServiceReport"
                            >
                                <Trash2 class="w-4 h-4 mr-2" />
                                Delete Report
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
