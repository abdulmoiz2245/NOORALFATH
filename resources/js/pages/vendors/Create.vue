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
import { ArrowLeft, Save, Building } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';

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
        title: 'Create Vendor',
        href: '/vendors/create',
    },
];

const form = useForm({
    name: '',
    company: '',
    email: '',
    phone: '',
    address: '',
    website: '',
    contact_person: '',
    notes: '',
    is_active: true,
});

const submit = () => {
    form.post('/vendors', {
        onSuccess: () => {
            success('Success!', 'Vendor created successfully');
        },
        onError: () => {
            error('Error!', 'Failed to create vendor. Please check the form and try again.');
        }
    });
};
</script>

<template>
    <Head title="Create Vendor" />

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
                        <h1 class="text-3xl font-bold tracking-tight">Create Vendor</h1>
                        <p class="text-muted-foreground">Add a new vendor to your system</p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Basic Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Basic Information</CardTitle>
                            <CardDescription>Enter the vendor's basic details</CardDescription>
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
                                    v-model="form.company"
                                    placeholder="Company name"
                                    :class="{ 'border-red-500': form.errors.company }"
                                />
                                <InputError :message="form.errors.company" />
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
                            <CardDescription>Vendor contact details</CardDescription>
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
                        <Link href="/vendors">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Save class="w-4 h-4 mr-2" />
                        {{ form.processing ? 'Creating...' : 'Create Vendor' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
