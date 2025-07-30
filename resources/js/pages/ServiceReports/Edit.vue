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
import { Save, ArrowLeft, Eye, Upload, X, Image as ImageIcon } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Client {
    id: number;
    name: string;
}

interface ServiceReport {
    id: number;
    service_report_number: string;
    client_id: number;
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
}

const props = defineProps<{
    serviceReport: ServiceReport;
    clients: Client[];
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
        title: 'Edit',
        href: `/service-reports/${props.serviceReport.id}/edit`,
    },
];

const form = useForm({
    service_report_number: props.serviceReport.service_report_number,
    client_id: props.serviceReport.client_id.toString(),
    service_date: props.serviceReport.service_date,
    ac_work: props.serviceReport.ac_work as boolean,
    plumbing_work: props.serviceReport.plumbing_work as boolean,
    paint_work: props.serviceReport.paint_work as boolean,
    electrical_work: props.serviceReport.electrical_work as boolean,
    civil_work: props.serviceReport.civil_work as boolean,
    job_details: props.serviceReport.job_details,
    before_pictures: [] as File[],
    after_pictures: [] as File[],
});

// Reactive refs for picture previews
const beforePreviews = ref<string[]>([]);
const afterPreviews = ref<string[]>([]);
const existingBeforePictures = ref<string[]>(props.serviceReport.before_pictures || []);
const existingAfterPictures = ref<string[]>(props.serviceReport.after_pictures || []);

// File input refs
const beforeFileInput = ref<HTMLInputElement | null>(null);
const afterFileInput = ref<HTMLInputElement | null>(null);

// File handling functions
const handleBeforeFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const files = Array.from(target.files || []);
    
    if (form.before_pictures.length + files.length > 4) {
        alert('You can upload a maximum of 4 before pictures');
        return;
    }
    
    files.forEach(file => {
        if (file.type.startsWith('image/')) {
            form.before_pictures.push(file);
            
            const reader = new FileReader();
            reader.onload = (e) => {
                beforePreviews.value.push(e.target?.result as string);
            };
            reader.readAsDataURL(file);
        }
    });
    
    if (beforeFileInput.value) {
        beforeFileInput.value.value = '';
    }
};

const handleAfterFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const files = Array.from(target.files || []);
    
    if (form.after_pictures.length + files.length > 4) {
        alert('You can upload a maximum of 4 after pictures');
        return;
    }
    
    files.forEach(file => {
        if (file.type.startsWith('image/')) {
            form.after_pictures.push(file);
            
            const reader = new FileReader();
            reader.onload = (e) => {
                afterPreviews.value.push(e.target?.result as string);
            };
            reader.readAsDataURL(file);
        }
    });
    
    if (afterFileInput.value) {
        afterFileInput.value.value = '';
    }
};

const removeBeforePicture = (index: number) => {
    form.before_pictures.splice(index, 1);
    beforePreviews.value.splice(index, 1);
};

const removeAfterPicture = (index: number) => {
    form.after_pictures.splice(index, 1);
    afterPreviews.value.splice(index, 1);
};

const removeExistingBeforePicture = (index: number) => {
    existingBeforePictures.value.splice(index, 1);
};

const removeExistingAfterPicture = (index: number) => {
    existingAfterPictures.value.splice(index, 1);
};

const openBeforeFileDialog = () => {
    beforeFileInput.value?.click();
};

const openAfterFileDialog = () => {
    afterFileInput.value?.click();
};

const openImageModal = (imageUrl: string) => {
    window.open(imageUrl, '_blank');
};

const submit = () => {
    // Debug: Log form data before submission
    console.log('Edit form data being submitted:', {
        service_report_number: form.service_report_number,
        client_id: form.client_id,
        service_date: form.service_date,
        ac_work: form.ac_work,
        plumbing_work: form.plumbing_work,
        paint_work: form.paint_work,
        electrical_work: form.electrical_work,
        civil_work: form.civil_work,
        job_details: form.job_details,
        before_pictures: form.before_pictures,
        after_pictures: form.after_pictures,
        existing_before_pictures: existingBeforePictures.value,
        existing_after_pictures: existingAfterPictures.value,
    });
    
    // Transform the form data to send proper checkbox values
    form.transform((data) => ({
        ...data,
        ac_work: data.ac_work ? '1' : '0',
        plumbing_work: data.plumbing_work ? '1' : '0',
        paint_work: data.paint_work ? '1' : '0',
        electrical_work: data.electrical_work ? '1' : '0',
        civil_work: data.civil_work ? '1' : '0',
        existing_before_pictures: existingBeforePictures.value,
        existing_after_pictures: existingAfterPictures.value,
        _method: 'PUT',
    }));
    
    form.post(`/service-reports/${props.serviceReport.id}`, {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Edit Service Report" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Edit Service Report</h1>
                    <p class="text-muted-foreground">{{ serviceReport.service_report_number }}</p>
                </div>
                <div class="flex space-x-3">
                    <Button variant="outline" as-child>
                        <Link :href="`/service-reports/${serviceReport.id}`">
                            <Eye class="w-4 h-4 mr-2" />
                            View Report
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

            <!-- Form -->
            <Card class="max-w-4xl">
                <CardHeader>
                    <CardTitle>Service Report Information</CardTitle>
                    <CardDescription>Update the details for this service report</CardDescription>
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

                            <!-- Before Pictures Section -->
                            <div class="space-y-4 md:col-span-2">
                                <Label>Before Pictures (Max 4)</Label>
                                
                                <!-- Existing Before Pictures -->
                                <div v-if="existingBeforePictures.length > 0" class="space-y-2">
                                    <p class="text-sm text-muted-foreground">Current before pictures:</p>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <div v-for="(picture, index) in existingBeforePictures" :key="`existing-before-${index}`" class="relative">
                                            <img :src="`/storage/${picture}`" :alt="`Before picture ${index + 1}`" class="w-full h-24 object-cover rounded border cursor-pointer" @click="openImageModal(`/storage/${picture}`)">
                                            <Button
                                                type="button"
                                                variant="destructive"
                                                size="sm"
                                                class="absolute -top-2 -right-2 h-6 w-6 rounded-full p-0"
                                                @click="removeExistingBeforePicture(index)"
                                            >
                                                <X class="h-3 w-3" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>

                                <!-- New Before Pictures -->
                                <div v-if="beforePreviews.length > 0" class="space-y-2">
                                    <p class="text-sm text-muted-foreground">New before pictures to upload:</p>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <div v-for="(preview, index) in beforePreviews" :key="`new-before-${index}`" class="relative">
                                            <img :src="preview" :alt="`Before picture ${index + 1}`" class="w-full h-24 object-cover rounded border cursor-pointer">
                                            <Button
                                                type="button"
                                                variant="destructive"
                                                size="sm"
                                                class="absolute -top-2 -right-2 h-6 w-6 rounded-full p-0"
                                                @click="removeBeforePicture(index)"
                                            >
                                                <X class="h-3 w-3" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Upload Button for Before Pictures -->
                                <div v-if="(existingBeforePictures.length + form.before_pictures.length) < 4" class="space-y-2">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        @click="openBeforeFileDialog"
                                        class="w-full border-dashed"
                                    >
                                        <Upload class="w-4 h-4 mr-2" />
                                        Upload Before Pictures
                                    </Button>
                                    <input
                                        ref="beforeFileInput"
                                        type="file"
                                        accept="image/*"
                                        multiple
                                        @change="handleBeforeFileSelect"
                                        class="hidden"
                                    >
                                </div>
                                <InputError :message="form.errors.before_pictures" />
                            </div>

                            <!-- After Pictures Section -->
                            <div class="space-y-4 md:col-span-2">
                                <Label>After Pictures (Max 4)</Label>
                                
                                <!-- Existing After Pictures -->
                                <div v-if="existingAfterPictures.length > 0" class="space-y-2">
                                    <p class="text-sm text-muted-foreground">Current after pictures:</p>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <div v-for="(picture, index) in existingAfterPictures" :key="`existing-after-${index}`" class="relative">
                                            <img :src="`/storage/${picture}`" :alt="`After picture ${index + 1}`" class="w-full h-24 object-cover rounded border cursor-pointer" @click="openImageModal(`/storage/${picture}`)">
                                            <Button
                                                type="button"
                                                variant="destructive"
                                                size="sm"
                                                class="absolute -top-2 -right-2 h-6 w-6 rounded-full p-0"
                                                @click="removeExistingAfterPicture(index)"
                                            >
                                                <X class="h-3 w-3" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>

                                <!-- New After Pictures -->
                                <div v-if="afterPreviews.length > 0" class="space-y-2">
                                    <p class="text-sm text-muted-foreground">New after pictures to upload:</p>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <div v-for="(preview, index) in afterPreviews" :key="`new-after-${index}`" class="relative">
                                            <img :src="preview" :alt="`After picture ${index + 1}`" class="w-full h-24 object-cover rounded border cursor-pointer">
                                            <Button
                                                type="button"
                                                variant="destructive"
                                                size="sm"
                                                class="absolute -top-2 -right-2 h-6 w-6 rounded-full p-0"
                                                @click="removeAfterPicture(index)"
                                            >
                                                <X class="h-3 w-3" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Upload Button for After Pictures -->
                                <div v-if="(existingAfterPictures.length + form.after_pictures.length) < 4" class="space-y-2">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        @click="openAfterFileDialog"
                                        class="w-full border-dashed"
                                    >
                                        <Upload class="w-4 h-4 mr-2" />
                                        Upload After Pictures
                                    </Button>
                                    <input
                                        ref="afterFileInput"
                                        type="file"
                                        accept="image/*"
                                        multiple
                                        @change="handleAfterFileSelect"
                                        class="hidden"
                                    >
                                </div>
                                <InputError :message="form.errors.after_pictures" />
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end space-x-4">
                            <Button type="button" variant="outline" as-child>
                                <Link :href="`/service-reports/${serviceReport.id}`">Cancel</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                <Save class="w-4 h-4 mr-2" />
                                {{ form.processing ? 'Updating...' : 'Update Service Report' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
