<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ArrowLeft, Save, Trash2, UserPlus } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import { ref } from 'vue';

interface Client {
    id: number;
    name: string;
    email: string;
    company?: string;
    phone?: string;
    address?: string;
}

interface Project {
    id: number;
    client_id: number;
    name: string;
    description?: string;
    status: string;
    start_date?: string;
    end_date?: string;
    estimated_cost?: number;
    actual_cost?: number;
    notes?: string;
    created_at: string;
    updated_at: string;
    client: Client;
}

interface Props {
    project: Project;
    clients: Client[];
}

const props = defineProps<Props>();
const { success, error } = useToast();
const showDeleteDialog = ref(false);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Projects',
        href: '/projects',
    },
    {
        title: props.project.name,
        href: `/projects/${props.project.id}`,
    },
    {
        title: 'Edit',
        href: `/projects/${props.project.id}/edit`,
    },
];

const form = useForm({
    client_id: props.project.client_id.toString(),
    name: props.project.name,
    description: props.project.description || '',
    status: props.project.status,
    start_date: props.project.start_date ? props.project.start_date.split('T')[0] : '',
    end_date: props.project.end_date ? props.project.end_date.split('T')[0] : '',
    estimated_cost: props.project.estimated_cost ? props.project.estimated_cost.toString() : '',
    notes: props.project.notes || '',
});

// Debug log to see what we're getting
console.log('Project estimated_cost:', props.project.estimated_cost);
console.log('Form estimated_cost:', props.project.estimated_cost ? props.project.estimated_cost.toString() : '');

const submit = () => {
    form.put(`/projects/${props.project.id}`, {
        onSuccess: () => {
            success('Success!', 'Project updated successfully');
        },
        onError: () => {
            error('Error!', 'Failed to update project. Please check the form and try again.');
        }
    });
};

const deleteProject = () => {
    router.delete(`/projects/${props.project.id}`, {
        onSuccess: () => {
            success('Success!', 'Project deleted successfully');
        },
        onError: () => {
            error('Error!', 'Failed to delete project. Please try again.');
        }
    });
};

const statusOptions = [
    { value: 'planning', label: 'Planning' },
    { value: 'in_progress', label: 'In Progress' },
    { value: 'on_hold', label: 'On Hold' },
    { value: 'completed', label: 'Completed' },
    { value: 'cancelled', label: 'Cancelled' },
];

const getStatusBadgeClass = (status: string) => {
    const classes = {
        planning: 'bg-yellow-100 text-yellow-800',
        in_progress: 'bg-blue-100 text-blue-800',
        on_hold: 'bg-orange-100 text-orange-800',
        completed: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
    };
    return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800';
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'AED'
    }).format(amount);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString();
};
</script>

<template>
    <Head :title="`Edit ${project.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="`/projects/${project.id}`">
                            <ArrowLeft class="w-4 h-4 mr-2" />
                            Back to Project
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">Edit Project</h1>
                        <p class="text-muted-foreground">Update project information</p>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <span :class="`px-2 py-1 text-xs font-medium rounded-full ${getStatusBadgeClass(project.status)}`">
                        {{ statusOptions.find(s => s.value === project.status)?.label || project.status }}
                    </span>
                    <Button variant="destructive" size="sm" @click="showDeleteDialog = true">
                        <Trash2 class="w-4 h-4 mr-2" />
                        Delete
                    </Button>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Information -->
                <Card>
                    <CardHeader>
                        <CardTitle>Project Details</CardTitle>
                        <CardDescription>Update the project information</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="client_id">Client *</Label>
                                <div class="flex space-x-2">
                                    <Select v-model="form.client_id">
                                        <SelectTrigger :class="{ 'border-red-500': form.errors.client_id }">
                                            <SelectValue placeholder="Select client" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="client in clients" :key="client.id" :value="client.id.toString()">
                                                {{ client.name }}{{ client.company ? ` (${client.company})` : '' }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <Button type="button" variant="outline" size="sm" as-child>
                                        <Link href="/clients/create">
                                            <UserPlus class="w-4 h-4" />
                                        </Link>
                                    </Button>
                                </div>
                                <InputError :message="form.errors.client_id" />
                            </div>

                            <div class="space-y-2">
                                <Label for="status">Status</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="option in statusOptions" :key="option.value" :value="option.value">
                                            {{ option.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="name">Project Name *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="Enter project name"
                                :class="{ 'border-red-500': form.errors.name }"
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                placeholder="Describe the project scope and objectives"
                                rows="4"
                                :class="{ 'border-red-500': form.errors.description }"
                            />
                            <InputError :message="form.errors.description" />
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="start_date">Start Date</Label>
                                <Input
                                    id="start_date"
                                    v-model="form.start_date"
                                    type="date"
                                    :class="{ 'border-red-500': form.errors.start_date }"
                                />
                                <InputError :message="form.errors.start_date" />
                            </div>

                            <div class="space-y-2">
                                <Label for="end_date">Expected End Date</Label>
                                <Input
                                    id="end_date"
                                    v-model="form.end_date"
                                    type="date"
                                    :class="{ 'border-red-500': form.errors.end_date }"
                                />
                                <InputError :message="form.errors.end_date" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="estimated_cost">Estimated Cost</Label>
                                <Input
                                    id="estimated_cost"
                                    v-model="form.estimated_cost"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                    :class="{ 'border-red-500': form.errors.estimated_cost }"
                                />
                                <InputError :message="form.errors.estimated_cost" />
                            </div>
                            <div class="space-y-2">
                                <Label>Actual Cost</Label>
                                <div class="flex items-center px-3 py-2 bg-gray-50 border rounded-md">
                                    <span class="text-sm text-gray-600">
                                        {{ project.actual_cost ? formatCurrency(project.actual_cost) : 'Not calculated' }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500">Calculated from expenses and invoice payments</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="notes">Notes</Label>
                            <Textarea
                                id="notes"
                                v-model="form.notes"
                                placeholder="Additional project notes, requirements, or special instructions"
                                rows="3"
                            />
                        </div>
                    </CardContent>
                </Card>

                <!-- Project Summary -->
                <Card>
                    <CardHeader>
                        <CardTitle>Project Summary</CardTitle>
                        <CardDescription>Quick overview of project metrics</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-4 md:grid-cols-4">
                            <div class="text-center">
                                <p class="text-2xl font-bold">{{ project.status }}</p>
                                <p class="text-sm text-gray-500">Status</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold">{{ project.estimated_cost ? formatCurrency(project.estimated_cost) : 'N/A' }}</p>
                                <p class="text-sm text-gray-500">Estimated Cost</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold">{{ project.actual_cost ? formatCurrency(project.actual_cost) : 'N/A' }}</p>
                                <p class="text-sm text-gray-500">Actual Cost</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold">{{ formatDate(project.created_at) }}</p>
                                <p class="text-sm text-gray-500">Created</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-4">
                    <Button variant="outline" type="button" as-child>
                        <Link :href="`/projects/${project.id}`">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Save class="w-4 h-4 mr-2" />
                        {{ form.processing ? 'Updating...' : 'Update Project' }}
                    </Button>
                </div>
            </form>
        </div>

        <!-- Delete Confirmation Dialog -->
        <div v-if="showDeleteDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold mb-4">Delete Project</h3>
                <p class="text-gray-600 mb-6">
                    Are you sure you want to delete "{{ project.name }}"? This action cannot be undone and will also delete all related invoices, quotations, and expenses.
                </p>
                <div class="flex justify-end space-x-4">
                    <Button variant="outline" @click="showDeleteDialog = false">Cancel</Button>
                    <Button variant="destructive" @click="deleteProject">Delete Project</Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
