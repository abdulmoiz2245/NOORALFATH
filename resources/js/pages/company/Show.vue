<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Building, Phone, Mail, Globe, CreditCard, FileText, Edit } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

interface Props {
    company: {
        id: number;
        name: string;
        address?: string;
        phone?: string;
        email?: string;
        website?: string;
        tax_number?: string;
        registration_number?: string;
        bank_details?: any;
        created_at: string;
        updated_at: string;
    };
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Company Profile',
        href: '/company',
    },
];
</script>

<template>
    <Head title="Company Profile" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Company Profile</h1>
                    <p class="text-muted-foreground">Your business information for invoices and quotations</p>
                </div>
                <Button as-child>
                    <Link :href="`/company/${company.id}/edit`">
                        <Edit class="w-4 h-4 mr-2" />
                        Edit Profile
                    </Link>
                </Button>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Basic Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Building class="w-5 h-5" />
                            Basic Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-muted-foreground">Company Name</label>
                            <p class="text-lg font-semibold">{{ company.name }}</p>
                        </div>
                        
                        <div v-if="company.address">
                            <label class="text-sm font-medium text-muted-foreground">Address</label>
                            <p>{{ company.address }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-if="company.phone">
                                <label class="text-sm font-medium text-muted-foreground flex items-center gap-1">
                                    <Phone class="w-4 h-4" />
                                    Phone
                                </label>
                                <p>{{ company.phone }}</p>
                            </div>

                            <div v-if="company.email">
                                <label class="text-sm font-medium text-muted-foreground flex items-center gap-1">
                                    <Mail class="w-4 h-4" />
                                    Email
                                </label>
                                <p>{{ company.email }}</p>
                            </div>
                        </div>

                        <div v-if="company.website">
                            <label class="text-sm font-medium text-muted-foreground flex items-center gap-1">
                                <Globe class="w-4 h-4" />
                                Website
                            </label>
                            <p>
                                <a :href="company.website" target="_blank" class="text-primary hover:underline">
                                    {{ company.website }}
                                </a>
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Legal Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <FileText class="w-5 h-5" />
                            Legal Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div v-if="company.tax_number">
                            <label class="text-sm font-medium text-muted-foreground">Tax Number</label>
                            <p class="font-mono">{{ company.tax_number }}</p>
                        </div>

                        <div v-if="company.registration_number">
                            <label class="text-sm font-medium text-muted-foreground">Registration Number</label>
                            <p class="font-mono">{{ company.registration_number }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Banking Information -->
                <Card v-if="company.bank_details" class="md:col-span-2">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <CreditCard class="w-5 h-5" />
                            Banking Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div v-if="company.bank_details.bank_name">
                                <label class="text-sm font-medium text-muted-foreground">Bank Name</label>
                                <p>{{ company.bank_details.bank_name }}</p>
                            </div>

                            <div v-if="company.bank_details.account_number">
                                <label class="text-sm font-medium text-muted-foreground">Account Number</label>
                                <p class="font-mono">{{ company.bank_details.account_number }}</p>
                            </div>

                            <div v-if="company.bank_details.routing_number">
                                <label class="text-sm font-medium text-muted-foreground">Routing Number</label>
                                <p class="font-mono">{{ company.bank_details.routing_number }}</p>
                            </div>

                            <div v-if="company.bank_details.swift_code">
                                <label class="text-sm font-medium text-muted-foreground">SWIFT Code</label>
                                <p class="font-mono">{{ company.bank_details.swift_code }}</p>
                            </div>

                            <div v-if="company.bank_details.iban_number">
                                <label class="text-sm font-medium text-muted-foreground">IBAN#</label>
                                <p class="font-mono">{{ company.bank_details.iban_number }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick Actions -->
            <Card>
                <CardHeader>
                    <CardTitle>Quick Actions</CardTitle>
                    <CardDescription>Common tasks with your company profile</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-wrap gap-2">
                        <Button variant="outline" as-child>
                            <Link href="/invoices/create">Create Invoice</Link>
                        </Button>
                        <Button variant="outline" as-child>
                            <Link href="/quotations/create">Create Quotation</Link>
                        </Button>
                        <Button variant="outline" as-child>
                            <Link href="/reports">View Reports</Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
