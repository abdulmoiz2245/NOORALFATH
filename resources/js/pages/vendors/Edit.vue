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
import { ArrowLeft, Save, Trash2 } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';

interface Vendor {
    id: number;
    name: string;
    company_name?: string;
    trn_number?: string;
    email?: string;
    phone?: string;
    address?: string;
    website?: string;
    contact_person?: string;
    notes?: string;
    is_active: boolean;
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
    {
        title: 'Edit',
        href: `/vendors/${props.vendor.id}/edit`,
    },
];

const form = useForm({
    name: props.vendor.name,
    company_name: props.vendor.company_name || '',
    trn_number: props.vendor.trn_number || '',
    email: props.vendor.email || '',
    phone: props.vendor.phone || '',
    address: props.vendor.address || '',
    website: props.vendor.website || '',
    contact_person: props.vendor.contact_person || '',
    notes: props.vendor.notes || '',
    is_active: props.vendor.is_active,
});

const submit = () => {
    form.put(`/vendors/${props.vendor.id}`, {
        onSuccess: () => {
            success('Success!', 'Vendor updated successfully');
        },
        onError: () => {
            error('Error!', 'Failed to update vendor. Please check the form and try again.');
        }
    });
};

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
</script>

<template>
    <Head :title="`Edit ${vendor.name}`" />

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
                        <h1 class="text-3xl font-bold tracking-tight">Edit Vendor</h1>
                        <p class="text-muted-foreground">Update vendor information</p>
                    </div>
                </div>
                <Button variant="destructive" @click="deleteVendor" :disabled="form.processing">
                    <Trash2 class="w-4 h-4 mr-2" />
                    Delete Vendor
                </Button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Basic Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Basic Information</CardTitle>
                            <CardDescription>Update the vendor's basic details</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <Label for="name">Vendor Name *</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    placeholder="Enter vendor name"
                                    :class="{ 'border-red-500': form.errors.name }"
                                />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div class="space-y-2">
                                <Label for="company">Company</Label>
                                <Input
                                    id="company"
                                    v-model="form.company_name"
                                    placeholder="Company name"
                                    :class="{ 'border-red-500': form.errors.company_name }"
                                />
                                <InputError :message="form.errors.company_name" />
                            </div>


                            <div class="space-y-2">
                                <Label for="trn_number">TRN Number</Label>
                                <Input
                                    id="trn_number"
                                    v-model="form.trn_number"
                                    placeholder="TRN number"
                                    :class="{ 'border-red-500': form.errors.trn_number }"
                                />
                                <InputError :message="form.errors.trn_number" />
                            </div>

                            <div class="space-y-2">
                                <Label for="contact_person">Contact Person</Label>
                                <Input
                                    id="contact_person"
                                    v-model="form.contact_person"
                                    placeholder="Primary contact person"
                                    :class="{ 'border-red-500': form.errors.contact_person }"
                                />
                                <InputError :message="form.errors.contact_person" />
                            </div>

                            <div class="flex items-center space-x-2">
                                <input
                                    id="is_active"
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="rounded border-gray-300"
                                />
                                <Label for="is_active">Active Vendor</Label>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Contact Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Contact Information</CardTitle>
                            <CardDescription>Update vendor contact details</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <Label for="email">Email</Label>
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    placeholder="vendor@example.com"
                                    :class="{ 'border-red-500': form.errors.email }"
                                />
                                <InputError :message="form.errors.email" />
                            </div>

                            <div class="space-y-2">
                                <Label for="phone">Phone</Label>
                                <Input
                                    id="phone"
                                    v-model="form.phone"
                                    placeholder="Phone number"
                                    :class="{ 'border-red-500': form.errors.phone }"
                                />
                                <InputError :message="form.errors.phone" />
                            </div>

                            <div class="space-y-2">
                                <Label for="website">Website</Label>
                                <Input
                                    id="website"
                                    v-model="form.website"
                                    placeholder="https://vendor-website.com"
                                    :class="{ 'border-red-500': form.errors.website }"
                                />
                                <InputError :message="form.errors.website" />
                            </div>

                            <div class="space-y-2">
                                <Label for="address">Address</Label>
                                <Textarea
                                    id="address"
                                    v-model="form.address"
                                    placeholder="Enter full address"
                                    rows="3"
                                    :class="{ 'border-red-500': form.errors.address }"
                                />
                                <InputError :message="form.errors.address" />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Additional Information -->
                <Card>
                    <CardHeader>
                        <CardTitle>Additional Information</CardTitle>
                        <CardDescription>Notes and additional details about the vendor</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2">
                            <Label for="notes">Notes</Label>
                            <Textarea
                                id="notes"
                                v-model="form.notes"
                                placeholder="Additional notes about the vendor..."
                                rows="4"
                                :class="{ 'border-red-500': form.errors.notes }"
                            />
                            <InputError :message="form.errors.notes" />
                        </div>
                    </CardContent>
                </Card>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-4">
                    <Button variant="outline" type="button" as-child>
                        <Link :href="`/vendors/${vendor.id}`">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Save class="w-4 h-4 mr-2" />
                        {{ form.processing ? 'Updating...' : 'Update Vendor' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
