<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { ArrowLeft, Edit, Trash2, Building, Mail, Phone, Globe, MapPin, User, FileText } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';

interface Expense {
    id: number;
    description: string;
    amount: number;
    date: string;
}

interface Vendor {
    id: number;
    name: string;
    company?: string;
    email?: string;
    phone?: string;
    address?: string;
    website?: string;
    contact_person?: string;
    notes?: string;
    is_active: boolean;
    expenses?: Expense[];
    total_expenses?: number;
    created_at: string;
    updated_at: string;
}

interface Props {
    vendor: Vendor;
}

const props = defineProps<Props>();
const { success, error } = useToast();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Vendors',
        href: '/vendors',
    },
    {
        title: props.vendor.name,
        href: `/vendors/${props.vendor.id}`,
    },
];

const form = useForm({});

const deleteVendor = () => {
    if (confirm('Are you sure you want to delete this vendor? This action cannot be undone.')) {
        form.delete(`/vendors/${props.vendor.id}`, {
            onSuccess: () => {
                success('Success!', 'Vendor deleted successfully');
            },
            onError: () => {
                error('Error!', 'Failed to delete vendor.');
            }
        });
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
    <Head :title="vendor.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Button variant="outline" size="sm" as-child>
                        <Link href="/vendors">
                            <ArrowLeft class="w-4 h-4 mr-2" />
                            Back to Vendors
                        </Link>
                    </Button>
                    <div>
                        <div class="flex items-center space-x-2">
                            <h1 class="text-3xl font-bold tracking-tight">{{ vendor.name }}</h1>
                            <Badge v-if="vendor.is_active" variant="default">Active</Badge>
                            <Badge v-else variant="secondary">Inactive</Badge>
                        </div>
                        <p class="text-muted-foreground">{{ vendor.company || 'Vendor details' }}</p>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <Button variant="outline" as-child>
                        <Link :href="`/vendors/${vendor.id}/edit`">
                            <Edit class="w-4 h-4 mr-2" />
                            Edit Vendor
                        </Link>
                    </Button>
                    <Button variant="destructive" @click="deleteVendor" :disabled="form.processing">
                        <Trash2 class="w-4 h-4 mr-2" />
                        Delete
                    </Button>
                </div>
            </div>

            <!-- Vendor Overview Cards -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Expenses</CardTitle>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(vendor.total_expenses || 0) }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Number of Expenses</CardTitle>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ vendor.expenses?.length || 0 }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Status</CardTitle>
                        <Building class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ vendor.is_active ? 'Active' : 'Inactive' }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Vendor Details -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Basic Information -->
                <Card>
                    <CardHeader>
                        <CardTitle>Basic Information</CardTitle>
                        <CardDescription>Vendor details and identification</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <div class="text-sm font-medium text-muted-foreground">Vendor Name</div>
                            <div class="text-base font-semibold">{{ vendor.name }}</div>
                        </div>

                        <div v-if="vendor.company">
                            <div class="text-sm font-medium text-muted-foreground">Company</div>
                            <div class="flex items-center space-x-2">
                                <Building class="w-4 h-4 text-muted-foreground" />
                                <span class="text-base">{{ vendor.company }}</span>
                            </div>
                        </div>

                        <div v-if="vendor.contact_person">
                            <div class="text-sm font-medium text-muted-foreground">Contact Person</div>
                            <div class="flex items-center space-x-2">
                                <User class="w-4 h-4 text-muted-foreground" />
                                <span class="text-base">{{ vendor.contact_person }}</span>
                            </div>
                        </div>

                        <Separator />

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <div class="text-sm font-medium text-muted-foreground">Created</div>
                                <div class="text-base">{{ formatDate(vendor.created_at) }}</div>
                            </div>

                            <div>
                                <div class="text-sm font-medium text-muted-foreground">Last Updated</div>
                                <div class="text-base">{{ formatDate(vendor.updated_at) }}</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Contact Information -->
                <Card>
                    <CardHeader>
                        <CardTitle>Contact Information</CardTitle>
                        <CardDescription>How to reach this vendor</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div v-if="vendor.email">
                            <div class="text-sm font-medium text-muted-foreground">Email</div>
                            <div class="flex items-center space-x-2">
                                <Mail class="w-4 h-4 text-muted-foreground" />
                                <a :href="`mailto:${vendor.email}`" class="text-base text-blue-600 hover:underline">
                                    {{ vendor.email }}
                                </a>
                            </div>
                        </div>

                        <div v-if="vendor.phone">
                            <div class="text-sm font-medium text-muted-foreground">Phone</div>
                            <div class="flex items-center space-x-2">
                                <Phone class="w-4 h-4 text-muted-foreground" />
                                <a :href="`tel:${vendor.phone}`" class="text-base text-blue-600 hover:underline">
                                    {{ vendor.phone }}
                                </a>
                            </div>
                        </div>

                        <div v-if="vendor.website">
                            <div class="text-sm font-medium text-muted-foreground">Website</div>
                            <div class="flex items-center space-x-2">
                                <Globe class="w-4 h-4 text-muted-foreground" />
                                <a :href="vendor.website" target="_blank" class="text-base text-blue-600 hover:underline">
                                    {{ vendor.website }}
                                </a>
                            </div>
                        </div>

                        <div v-if="vendor.address">
                            <div class="text-sm font-medium text-muted-foreground">Address</div>
                            <div class="flex items-start space-x-2">
                                <MapPin class="w-4 h-4 text-muted-foreground mt-0.5" />
                                <div class="text-base whitespace-pre-line">{{ vendor.address }}</div>
                            </div>
                        </div>

                        <div v-if="!vendor.email && !vendor.phone && !vendor.website && !vendor.address">
                            <div class="text-center py-4 text-muted-foreground">
                                <Building class="mx-auto h-8 w-8 mb-2" />
                                <p>No contact information available</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Expenses -->
            <Card v-if="vendor.expenses && vendor.expenses.length > 0">
                <CardHeader>
                    <div class="flex justify-between items-center">
                        <div>
                            <CardTitle>Recent Expenses</CardTitle>
                            <CardDescription>Latest expenses from this vendor</CardDescription>
                        </div>
                        <Button variant="outline" size="sm" as-child>
                            <Link :href="`/expenses?vendor=${vendor.id}`">View All</Link>
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left p-4">Description</th>
                                    <th class="text-left p-4">Date</th>
                                    <th class="text-right p-4">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="expense in vendor.expenses.slice(0, 5)" :key="expense.id" class="border-b hover:bg-muted/50">
                                    <td class="p-4">
                                        <div class="font-medium">{{ expense.description }}</div>
                                    </td>
                                    <td class="p-4">{{ formatDate(expense.date) }}</td>
                                    <td class="p-4 text-right font-medium">{{ formatCurrency(expense.amount) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <!-- Notes -->
            <Card v-if="vendor.notes">
                <CardHeader>
                    <CardTitle>Notes</CardTitle>
                    <CardDescription>Additional information about this vendor</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="whitespace-pre-wrap">{{ vendor.notes }}</div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
