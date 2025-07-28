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
import { ArrowLeft, Save, User } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';

interface Client {
    id: number;
    name: string;
    company_name?: string;
    email?: string;
    phone?: string;
    trn_number?: string;
    address?: string;
    city?: string;
    state?: string;
    postal_code?: string;
    country?: string;
    notes?: string;
    created_at: string;
    updated_at: string;
}

interface Props {
    client: Client;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Clients',
        href: '/clients',
    },
    {
        title: props.client.name,
        href: `/clients/${props.client.id}`,
    },
    {
        title: 'Edit',
        href: `/clients/${props.client.id}/edit`,
    },
];

const { success, error } = useToast();

const form = useForm({
    name: props.client.name,
    company_name: props.client.company_name || '',
    email: props.client.email || '',
    phone: props.client.phone || '',
    trn_number: props.client.trn_number || '',
    address: props.client.address || '',
    city: props.client.city || '',
    state: props.client.state || '',
    postal_code: props.client.postal_code || '',
    country: props.client.country || '',
    notes: props.client.notes || '',
});

const submit = () => {
    form.put(`/clients/${props.client.id}`, {
        onSuccess: () => {
            success('Success!', 'Client updated successfully');
        },
        onError: () => {
            error('Error!', 'Failed to update client. Please check the form and try again.');
        }
    });
};
</script>

<template>
    <Head title="Edit Client" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="`/clients/${client.id}`">
                            <ArrowLeft class="w-4 h-4 mr-2" />
                            Back to Client
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">Edit Client</h1>
                        <p class="text-muted-foreground">Update client information</p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <User class="w-5 h-5" />
                            Basic Information
                        </CardTitle>
                        <CardDescription>
                            Update the client's basic contact information
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label for="name">Client Name *</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    required
                                    placeholder="Enter client name"
                                />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div>
                                <Label for="company_name">Company Name</Label>
                                <Input
                                    id="company_name"
                                    v-model="form.company_name"
                                    type="text"
                                    placeholder="Enter company name"
                                />
                                <InputError :message="form.errors.company_name" />
                            </div>
                        </div>

                        <div>
                            <Label for="trn_number">TRN Number</Label>
                            <Input
                                id="trn_number"
                                v-model="form.trn_number"
                                type="text"
                                placeholder="Enter TRN number"
                            />
                            <InputError :message="form.errors.trn_number" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label for="email">Email</Label>
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    placeholder="Enter email address"
                                />
                                <InputError :message="form.errors.email" />
                            </div>

                            <div>
                                <Label for="phone">Phone</Label>
                                <Input
                                    id="phone"
                                    v-model="form.phone"
                                    type="tel"
                                    placeholder="Enter phone number"
                                />
                                <InputError :message="form.errors.phone" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Address Information -->
                <Card>
                    <CardHeader>
                        <CardTitle>Address Information</CardTitle>
                        <CardDescription>
                            Client's address and location details
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <Label for="address">Street Address</Label>
                            <Textarea
                                id="address"
                                v-model="form.address"
                                placeholder="Enter street address"
                                rows="3"
                            />
                            <InputError :message="form.errors.address" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <Label for="city">City</Label>
                                <Input
                                    id="city"
                                    v-model="form.city"
                                    type="text"
                                    placeholder="Enter city"
                                />
                                <InputError :message="form.errors.city" />
                            </div>

                            <div>
                                <Label for="state">State/Province</Label>
                                <Input
                                    id="state"
                                    v-model="form.state"
                                    type="text"
                                    placeholder="Enter state or province"
                                />
                                <InputError :message="form.errors.state" />
                            </div>

                            <div>
                                <Label for="postal_code">Postal Code</Label>
                                <Input
                                    id="postal_code"
                                    v-model="form.postal_code"
                                    type="text"
                                    placeholder="Enter postal code"
                                />
                                <InputError :message="form.errors.postal_code" />
                            </div>

                            <div>
                                <Label for="country">Country</Label>
                                <Input
                                    id="country"
                                    v-model="form.country"
                                    type="text"
                                    placeholder="Enter country"
                                />
                                <InputError :message="form.errors.country" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Additional Information -->
                <Card>
                    <CardHeader>
                        <CardTitle>Additional Information</CardTitle>
                        <CardDescription>
                            Additional notes and information about the client
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div>
                            <Label for="notes">Notes</Label>
                            <Textarea
                                id="notes"
                                v-model="form.notes"
                                placeholder="Enter any additional notes about the client..."
                                rows="4"
                            />
                            <InputError :message="form.errors.notes" />
                        </div>
                    </CardContent>
                </Card>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-4">
                    <Button variant="outline" type="button" as-child>
                        <Link :href="`/clients/${client.id}`">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Save class="w-4 h-4 mr-2" />
                        {{ form.processing ? 'Updating...' : 'Update Client' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
