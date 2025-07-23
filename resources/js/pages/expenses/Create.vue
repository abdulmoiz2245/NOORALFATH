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
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ArrowLeft, Save, Upload, X } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import { ref } from 'vue';

interface Vendor {
    id: number;
    name: string;
    company?: string;
}

interface Project {
    id: number;
    name: string;
}

interface Props {
    vendors: Vendor[];
    projects: Project[];
}

const props = defineProps<Props>();
const { success, error } = useToast();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Expenses',
        href: '/expenses',
    },
    {
        title: 'Create Expense',
        href: '/expenses/create',
    },
];

const form = useForm({
    description: '',
    amount: '',
    date: new Date().toISOString().split('T')[0],
    category: '',
    vendor_id: '',
    project_id: '',
    receipt: null as File | null,
    is_billable: false,
    is_reimbursable: false,
    notes: '',
});

const fileInput = ref<HTMLInputElement | null>(null);
const selectedFileName = ref<string>('');

const categories = [
    'Office Supplies',
    'Travel',
    'Meals & Entertainment',
    'Software & Subscriptions',
    'Equipment',
    'Marketing',
    'Professional Services',
    'Utilities',
    'Rent',
    'Insurance',
    'Training & Education',
    'Telecommunications',
    'Maintenance & Repairs',
    'Banking & Fees',
    'Other'
];

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    
    if (file) {
        // Check file size (max 10MB)
        if (file.size > 10 * 1024 * 1024) {
            error('Error!', 'File size must be less than 10MB');
            return;
        }
        
        // Check file type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        if (!allowedTypes.includes(file.type)) {
            error('Error!', 'Please upload an image (JPEG, PNG, GIF) or PDF file');
            return;
        }
        
        form.receipt = file;
        selectedFileName.value = file.name;
    }
};

const removeFile = () => {
    form.receipt = null;
    selectedFileName.value = '';
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const submit = () => {
    form.post('/expenses', {
        onSuccess: () => {
            success('Success!', 'Expense created successfully');
        },
        onError: () => {
            error('Error!', 'Failed to create expense. Please check the form and try again.');
        }
    });
};
</script>

<template>
    <Head title="Create Expense" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Button variant="outline" size="sm" as-child>
                        <Link href="/expenses">
                            <ArrowLeft class="w-4 h-4 mr-2" />
                            Back to Expenses
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">Create Expense</h1>
                        <p class="text-muted-foreground">Record a new business expense</p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Basic Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Expense Details</CardTitle>
                            <CardDescription>Enter the basic expense information</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <Label for="description">Description *</Label>
                                <Input
                                    id="description"
                                    v-model="form.description"
                                    placeholder="Enter expense description"
                                    :class="{ 'border-red-500': form.errors.description }"
                                />
                                <InputError :message="form.errors.description" />
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="amount">Amount *</Label>
                                    <Input
                                        id="amount"
                                        v-model="form.amount"
                                        type="number"
                                        step="0.01"
                                        placeholder="0.00"
                                        :class="{ 'border-red-500': form.errors.amount }"
                                    />
                                    <InputError :message="form.errors.amount" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="date">Date *</Label>
                                    <Input
                                        id="date"
                                        v-model="form.date"
                                        type="date"
                                        :class="{ 'border-red-500': form.errors.date }"
                                    />
                                    <InputError :message="form.errors.date" />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="category">Category *</Label>
                                <Select v-model="form.category">
                                    <SelectTrigger :class="{ 'border-red-500': form.errors.category }">
                                        <SelectValue placeholder="Select category" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="category in categories" :key="category" :value="category">
                                            {{ category }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.category" />
                            </div>

                            <div class="space-y-4">
                                <div class="flex items-center space-x-2">
                                    <input
                                        id="is_billable"
                                        v-model="form.is_billable"
                                        type="checkbox"
                                        class="rounded border-gray-300"
                                    />
                                    <Label for="is_billable">Billable to client</Label>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <input
                                        id="is_reimbursable"
                                        v-model="form.is_reimbursable"
                                        type="checkbox"
                                        class="rounded border-gray-300"
                                    />
                                    <Label for="is_reimbursable">Reimbursable</Label>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Associations & Receipt -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Associations & Receipt</CardTitle>
                            <CardDescription>Link to vendor/project and upload receipt</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <Label for="vendor_id">Vendor</Label>
                                <Select v-model="form.vendor_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select vendor (optional)" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">No Vendor</SelectItem>
                                        <SelectItem v-for="vendor in vendors" :key="vendor.id" :value="vendor.id.toString()">
                                            {{ vendor.name }}{{ vendor.company ? ` - ${vendor.company}` : '' }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="space-y-2">
                                <Label for="project_id">Project</Label>
                                <Select v-model="form.project_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select project (optional)" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">No Project</SelectItem>
                                        <SelectItem v-for="project in projects" :key="project.id" :value="project.id.toString()">
                                            {{ project.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Receipt Upload -->
                            <div class="space-y-2">
                                <Label>Receipt</Label>
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6">
                                    <div v-if="!selectedFileName" class="text-center">
                                        <Upload class="mx-auto h-12 w-12 text-gray-400" />
                                        <div class="mt-4">
                                            <Button type="button" variant="outline" @click="fileInput?.click()">
                                                <Upload class="w-4 h-4 mr-2" />
                                                Upload Receipt
                                            </Button>
                                            <input
                                                ref="fileInput"
                                                type="file"
                                                class="hidden"
                                                accept="image/*,.pdf"
                                                @change="handleFileSelect"
                                            />
                                        </div>
                                        <p class="mt-2 text-sm text-gray-500">
                                            PNG, JPG, GIF or PDF up to 10MB
                                        </p>
                                    </div>
                                    <div v-else class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <Upload class="h-8 w-8 text-green-500" />
                                            <div>
                                                <p class="text-sm font-medium">{{ selectedFileName }}</p>
                                                <p class="text-xs text-gray-500">Receipt uploaded</p>
                                            </div>
                                        </div>
                                        <Button type="button" variant="ghost" size="sm" @click="removeFile">
                                            <X class="w-4 h-4" />
                                        </Button>
                                    </div>
                                </div>
                                <InputError :message="form.errors.receipt" />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Notes -->
                <Card>
                    <CardHeader>
                        <CardTitle>Additional Information</CardTitle>
                        <CardDescription>Notes and additional details</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2">
                            <Label for="notes">Notes</Label>
                            <Textarea
                                id="notes"
                                v-model="form.notes"
                                placeholder="Additional notes about this expense..."
                                rows="3"
                            />
                        </div>
                    </CardContent>
                </Card>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-4">
                    <Button variant="outline" type="button" as-child>
                        <Link href="/expenses">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Save class="w-4 h-4 mr-2" />
                        {{ form.processing ? 'Creating...' : 'Create Expense' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
