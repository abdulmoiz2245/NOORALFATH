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

const props = defineProps<{
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
    service_report_number: props.serviceReportNumber,
    client_id: '',
    service_date: new Date().toISOString().split('T')[0],
    ac_work: false as boolean,
    plumbing_work: false as boolean,
    paint_work: false as boolean,
    electrical_work: false as boolean,
    civil_work: false as boolean,
    job_details: '',
    before_pictures: [] as File[],
    after_pictures: [] as File[],
});

const submit = () => {
    // Debug: Log form data before submission
    console.log('Form data being submitted:', {
        service_report_number: form.service_report_number,
        client_id: form.client_id,
        service_date: form.service_date,
        ac_work: form.ac_work,
        plumbing_work: form.plumbing_work,
        paint_work: form.paint_work,
        electrical_work: form.electrical_work,
        civil_work: form.civil_work,
        job_details: form.job_details,
        before_pictures: form.before_pictures.length,
        after_pictures: form.after_pictures.length,
    });
    
    // Transform the form data to send proper checkbox values
    form.transform((data) => ({
        ...data,
        ac_work: data.ac_work ? '1' : '0',
        plumbing_work: data.plumbing_work ? '1' : '0',
        paint_work: data.paint_work ? '1' : '0',
        electrical_work: data.electrical_work ? '1' : '0',
        civil_work: data.civil_work ? '1' : '0',
    }));
    
    form.post('/service-reports', {
        forceFormData: true,
        onSuccess: () => {
            console.log('Form submitted successfully');
        },
        onError: (errors) => {
            console.log('Form submission errors:', errors);
        }
    });
};

const handleBeforePictureChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        const files = Array.from(target.files).slice(0, 4);
        form.before_pictures = files;
    }
};

const handleAfterPictureChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        const files = Array.from(target.files).slice(0, 4);
        form.after_pictures = files;
    }
};

const removeBeforePicture = (index: number) => {
    form.before_pictures = form.before_pictures.filter((_, i) => i !== index);
};

const removeAfterPicture = (index: number) => {
    form.after_pictures = form.after_pictures.filter((_, i) => i !== index);
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
                                    v-model="form.service_report_number"
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
                                            :model-value="form.ac_work"
                                            @update:model-value="(value: boolean | 'indeterminate') => form.ac_work = value === true"
                                        />
                                        <Label for="ac_work" class="text-sm font-normal">A/C Work</Label>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <Checkbox 
                                            id="plumbing_work"
                                            :model-value="form.plumbing_work"
                                            @update:model-value="(value: boolean | 'indeterminate') => form.plumbing_work = value === true"
                                        />
                                        <Label for="plumbing_work" class="text-sm font-normal">Plumbing</Label>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <Checkbox 
                                            id="paint_work"
                                            :model-value="form.paint_work"
                                            @update:model-value="(value: boolean | 'indeterminate') => form.paint_work = value === true"
                                        />
                                        <Label for="paint_work" class="text-sm font-normal">Paint Work</Label>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <Checkbox 
                                            id="electrical_work"
                                            :model-value="form.electrical_work"
                                            @update:model-value="(value: boolean | 'indeterminate') => form.electrical_work = value === true"
                                        />
                                        <Label for="electrical_work" class="text-sm font-normal">Electrical Work</Label>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <Checkbox 
                                            id="civil_work"
                                            :model-value="form.civil_work"
                                            @update:model-value="(value: boolean | 'indeterminate') => form.civil_work = value === true"
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

                            <!-- Before Pictures -->
                            <div class="space-y-2 md:col-span-1">
                                <Label for="before_pictures">Before Pictures (Max 4)</Label>
                                <Input
                                    id="before_pictures"
                                    type="file"
                                    multiple
                                    accept="image/jpeg,image/jpg,image/png"
                                    @change="handleBeforePictureChange"
                                    :class="{ 'border-red-500': form.errors.before_pictures }"
                                />
                                <InputError :message="form.errors.before_pictures" />
                                <div v-if="form.before_pictures.length > 0" class="mt-2">
                                    <p class="text-sm text-muted-foreground mb-2">Selected files:</p>
                                    <div class="space-y-1">
                                        <div v-for="(file, index) in form.before_pictures" :key="index" 
                                             class="flex items-center justify-between text-sm bg-muted p-2 rounded">
                                            <span>{{ file.name }}</span>
                                            <Button 
                                                type="button" 
                                                variant="ghost" 
                                                size="sm" 
                                                @click="removeBeforePicture(index)"
                                            >
                                                ×
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- After Pictures -->
                            <div class="space-y-2 md:col-span-1">
                                <Label for="after_pictures">After Pictures (Max 4)</Label>
                                <Input
                                    id="after_pictures"
                                    type="file"
                                    multiple
                                    accept="image/jpeg,image/jpg,image/png"
                                    @change="handleAfterPictureChange"
                                    :class="{ 'border-red-500': form.errors.after_pictures }"
                                />
                                <InputError :message="form.errors.after_pictures" />
                                <div v-if="form.after_pictures.length > 0" class="mt-2">
                                    <p class="text-sm text-muted-foreground mb-2">Selected files:</p>
                                    <div class="space-y-1">
                                        <div v-for="(file, index) in form.after_pictures" :key="index" 
                                             class="flex items-center justify-between text-sm bg-muted p-2 rounded">
                                            <span>{{ file.name }}</span>
                                            <Button 
                                                type="button" 
                                                variant="ghost" 
                                                size="sm" 
                                                @click="removeAfterPicture(index)"
                                            >
                                                ×
                                            </Button>
                                        </div>
                                    </div>
                                </div>
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
