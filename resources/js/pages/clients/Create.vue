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
import { Save, ArrowLeft } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

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
        title: 'Create',
        href: '/clients/create',
    },
];

const form = useForm({
    name: '',
    company_name: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    state: '',
    postal_code: '',
    country: '',
});

const submit = () => {
    form.post('/clients');
};
</script>

<template>
    <Head title="Create Client" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Create Client</h1>
                    <p class="text-muted-foreground">Add a new client to your system</p>
                </div>
                <Button variant="outline" as-child>
                    <Link href="/clients">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Back to Clients
                    </Link>
                </Button>
            </div>

            <!-- Form -->
            <Card class="max-w-4xl">
                <CardHeader>
                    <CardTitle>Client Information</CardTitle>
                    <CardDescription>Enter the details for the new client</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div class="space-y-2">
                                <Label for="name">Name *</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Enter client name"
                                    :class="{ 'border-red-500': form.errors.name }"
                                />
                                <InputError :message="form.errors.name" />
                            </div>

                            <!-- Company Name -->
                            <div class="space-y-2">
                                <Label for="company_name">Company Name</Label>
                                <Input
                                    id="company_name"
                                    v-model="form.company_name"
                                    type="text"
                                    placeholder="Enter company name"
                                    :class="{ 'border-red-500': form.errors.company_name }"
                                />
                                <InputError :message="form.errors.company_name" />
                            </div>

                            <!-- Email -->
                            <div class="space-y-2">
                                <Label for="email">Email</Label>
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    placeholder="Enter email address"
                                    :class="{ 'border-red-500': form.errors.email }"
                                />
                                <InputError :message="form.errors.email" />
                            </div>

                            <!-- Phone -->
                            <div class="space-y-2">
                                <Label for="phone">Phone</Label>
                                <Input
                                    id="phone"
                                    v-model="form.phone"
                                    type="text"
                                    placeholder="Enter phone number"
                                    :class="{ 'border-red-500': form.errors.phone }"
                                />
                                <InputError :message="form.errors.phone" />
                            </div>

                            <!-- City -->
                            <div class="space-y-2">
                                <Label for="city">City</Label>
                                <Input
                                    id="city"
                                    v-model="form.city"
                                    type="text"
                                    placeholder="Enter city"
                                    :class="{ 'border-red-500': form.errors.city }"
                                />
                                <InputError :message="form.errors.city" />
                            </div>

                            <!-- State -->
                            <div class="space-y-2">
                                <Label for="state">State</Label>
                                <Input
                                    id="state"
                                    v-model="form.state"
                                    type="text"
                                    placeholder="Enter state"
                                    :class="{ 'border-red-500': form.errors.state }"
                                />
                                <InputError :message="form.errors.state" />
                            </div>

                            <!-- Postal Code -->
                            <div class="space-y-2">
                                <Label for="postal_code">Postal Code</Label>
                                <Input
                                    id="postal_code"
                                    v-model="form.postal_code"
                                    type="text"
                                    placeholder="Enter postal code"
                                    :class="{ 'border-red-500': form.errors.postal_code }"
                                />
                                <InputError :message="form.errors.postal_code" />
                            </div>

                            <!-- Country -->
                            <div class="space-y-2">
                                <Label for="country">Country</Label>
                                <Input
                                    id="country"
                                    v-model="form.country"
                                    type="text"
                                    placeholder="Enter country"
                                    :class="{ 'border-red-500': form.errors.country }"
                                />
                                <InputError :message="form.errors.country" />
                            </div>
                        </div>

                        <!-- Address -->
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

                        <!-- Submit Button -->
                        <div class="flex justify-end space-x-4">
                            <Button type="button" variant="outline" as-child>
                                <Link href="/clients">Cancel</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                <Save class="w-4 h-4 mr-2" />
                                {{ form.processing ? 'Creating...' : 'Create Client' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
