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
import { Checkbox } from '@/components/ui/checkbox';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Save, ArrowLeft } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

interface Client {
    id: number;
    name: string;
}

defineProps<{
    clients: Client[];
    serviceReportNumber: string;
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
        title: 'Create',
        href: '/service-reports/create',
    },
];

const form = useForm({
    client_id: '',
    service_date: new Date().toISOString().split('T')[0],
    ac_work: false,
    plumbing_work: false,
    paint_work: false,
    electrical_work: false,
    civil_work: false,
    job_details: '',
});

const submit = () => {
    form.post('/service-reports');
};
</script>

<template>
    <Head title="Create Service Report" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Create Service Report</h1>
                    <p class="text-muted-foreground">Create a new service report for work performed</p>
                </div>
                <Button variant="outline" as-child>
                    <Link href="/service-reports">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Back to Service Reports
                    </Link>
                </Button>
            </div>

            <!-- Form -->
            <Card class="max-w-4xl">
                <CardHeader>
                    <CardTitle>Service Report Information</CardTitle>
                    <CardDescription>Enter the details for the new service report</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Service Report Number -->
                            <div class="space-y-2 md:col-span-2">
                                <Label for="service_report_number">Service Report Number</Label>
                                <Input
                                    id="service_report_number"
                                    :value="serviceReportNumber"
                                    type="text"
                                    class="bg-muted"
                                    readonly
                                />
                            </div>

                            <!-- Client Selection -->
                            <div class="space-y-2">
                                <Label for="client_id">Client *</Label>
                                <Select v-model="form.client_id">
                                    <SelectTrigger :class="{ 'border-red-500': form.errors.client_id }">
                                        <SelectValue placeholder="Select a client" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="client in clients" :key="client.id" :value="client.id.toString()">
                                            {{ client.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.client_id" />
                            </div>

                            <!-- Service Date -->
                            <div class="space-y-2">
                                <Label for="service_date">Service Date *</Label>
                                <Input
                                    id="service_date"
                                    v-model="form.service_date"
                                    type="date"
                                    :class="{ 'border-red-500': form.errors.service_date }"
                                />
                                <InputError :message="form.errors.service_date" />
                            </div>

                            <!-- Service Types -->
                            <div class="space-y-4 md:col-span-2">
                                <Label>Type of Work</Label>
                                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                                    <div class="flex items-center space-x-2">
                                        <Checkbox 
                                            id="ac_work"
                                            v-model:checked="form.ac_work"
                                        />
                                        <Label for="ac_work" class="text-sm font-normal">A/C Work</Label>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <Checkbox 
                                            id="plumbing_work"
                                            v-model:checked="form.plumbing_work"
                                        />
                                        <Label for="plumbing_work" class="text-sm font-normal">Plumbing</Label>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <Checkbox 
                                            id="paint_work"
                                            v-model:checked="form.paint_work"
                                        />
                                        <Label for="paint_work" class="text-sm font-normal">Paint Work</Label>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <Checkbox 
                                            id="electrical_work"
                                            v-model:checked="form.electrical_work"
                                        />
                                        <Label for="electrical_work" class="text-sm font-normal">Electrical Work</Label>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <Checkbox 
                                            id="civil_work"
                                            v-model:checked="form.civil_work"
                                        />
                                        <Label for="civil_work" class="text-sm font-normal">Civil Work</Label>
                                    </div>
                                </div>
                            </div>

                            <!-- Job Details -->
                            <div class="space-y-2 md:col-span-2">
                                <Label for="job_details">Job Details *</Label>
                                <Textarea
                                    id="job_details"
                                    v-model="form.job_details"
                                    placeholder="Enter detailed description of the work performed..."
                                    rows="6"
                                    :class="{ 'border-red-500': form.errors.job_details }"
                                />
                                <InputError :message="form.errors.job_details" />
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end space-x-4">
                            <Button type="button" variant="outline" as-child>
                                <Link href="/service-reports">Cancel</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                <Save class="w-4 h-4 mr-2" />
                                {{ form.processing ? 'Creating...' : 'Create Service Report' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
