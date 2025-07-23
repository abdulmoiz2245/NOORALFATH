<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
    ArrowLeft, 
    Edit3, 
    Building, 
    Mail, 
    Phone, 
    MapPin,
    Globe,
    CreditCard,
    FileText,
    Download,
    Eye,
    Hash,
    Calendar,
    Check,
    X
} from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface BankDetails {
    bank_name?: string;
    account_number?: string;
    routing_number?: string;
    account_holder?: string;
}

interface Company {
    id: number;
    name: string;
    address?: string;
    phone?: string;
    email?: string;
    website?: string;
    logo?: string;
    signature?: string;
    tax_number?: string;
    registration_number?: string;
    bank_details?: BankDetails;
    created_at: string;
    updated_at: string;
}

interface Props {
    company: Company;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Company Settings',
        href: '/companies',
    },
    {
        title: props.company.name,
        href: `/companies/${props.company.id}`,
    },
];

const logoUrl = computed(() => {
    return props.company.logo ? `/storage/${props.company.logo}` : null;
});

const signatureUrl = computed(() => {
    return props.company.signature ? `/storage/${props.company.signature}` : null;
});

const hasBasicInfo = computed(() => {
    return !!(props.company.address || props.company.phone || props.company.email || props.company.website);
});

const hasLegalInfo = computed(() => {
    return !!(props.company.tax_number || props.company.registration_number);
});

const hasBankDetails = computed(() => {
    const bank = props.company.bank_details;
    return !!(bank?.bank_name || bank?.account_number || bank?.routing_number || bank?.account_holder);
});

const hasBranding = computed(() => {
    return !!(props.company.logo || props.company.signature);
});

const completionPercentage = computed(() => {
    let completed = 1; // Name is required, so always 1
    let total = 5; // Name, basic info, legal info, bank details, branding
    
    if (hasBasicInfo.value) completed++;
    if (hasLegalInfo.value) completed++;
    if (hasBankDetails.value) completed++;
    if (hasBranding.value) completed++;
    
    return Math.round((completed / total) * 100);
});

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const formatBankAccount = (accountNumber?: string) => {
    if (!accountNumber) return '';
    // Mask account number showing only last 4 digits
    return '****' + accountNumber.slice(-4);
};

const downloadLogo = () => {
    if (logoUrl.value) {
        const link = document.createElement('a');
        link.href = logoUrl.value;
        link.download = `${props.company.name}-logo`;
        link.click();
    }
};

const downloadSignature = () => {
    if (signatureUrl.value) {
        const link = document.createElement('a');
        link.href = signatureUrl.value;
        link.download = `${props.company.name}-signature`;
        link.click();
    }
};
</script>

<template>
    <Head :title="company.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Button variant="outline" size="sm" as-child>
                        <Link href="/companies">
                            <ArrowLeft class="w-4 h-4 mr-2" />
                            Back to Companies
                        </Link>
                    </Button>
                    <div class="flex items-center space-x-4">
                        <div v-if="logoUrl" class="w-12 h-12 rounded border bg-white flex items-center justify-center">
                            <img :src="logoUrl" :alt="`${company.name} logo`" class="max-w-full max-h-full object-contain" />
                        </div>
                        <div v-else class="w-12 h-12 rounded border bg-gray-100 flex items-center justify-center">
                            <Building class="w-6 h-6 text-gray-400" />
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold tracking-tight">{{ company.name }}</h1>
                            <p class="text-muted-foreground">Company Profile</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="`/companies/${company.id}/edit`">
                            <Edit3 class="w-4 h-4 mr-2" />
                            Edit
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Completion Status -->
            <Card>
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold">Profile Completion</h3>
                            <p class="text-sm text-gray-500">Complete your company profile for better document generation</p>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold">{{ completionPercentage }}%</div>
                            <div class="w-32 bg-gray-200 rounded-full h-2 mt-2">
                                <div 
                                    class="bg-green-600 h-2 rounded-full transition-all duration-300"
                                    :style="`width: ${completionPercentage}%`"
                                ></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid gap-2 md:grid-cols-5 mt-4">
                        <div class="flex items-center space-x-2">
                            <Check class="w-4 h-4 text-green-600" />
                            <span class="text-sm">Basic Details</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <component :is="hasBasicInfo ? Check : X" :class="hasBasicInfo ? 'text-green-600' : 'text-red-400'" class="w-4 h-4" />
                            <span class="text-sm">Contact Info</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <component :is="hasLegalInfo ? Check : X" :class="hasLegalInfo ? 'text-green-600' : 'text-red-400'" class="w-4 h-4" />
                            <span class="text-sm">Legal Details</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <component :is="hasBankDetails ? Check : X" :class="hasBankDetails ? 'text-green-600' : 'text-red-400'" class="w-4 h-4" />
                            <span class="text-sm">Banking</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <component :is="hasBranding ? Check : X" :class="hasBranding ? 'text-green-600' : 'text-red-400'" class="w-4 h-4" />
                            <span class="text-sm">Branding</span>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Company Information Grid -->
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Basic Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center space-x-2">
                            <Building class="w-5 h-5" />
                            <span>Company Information</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <Label class="text-sm font-medium text-gray-500">Company Name</Label>
                            <p class="mt-1 font-medium">{{ company.name }}</p>
                        </div>

                        <div v-if="company.address">
                            <Label class="text-sm font-medium text-gray-500">Address</Label>
                            <div class="flex items-start space-x-2 mt-1">
                                <MapPin class="w-4 h-4 text-gray-400 mt-0.5" />
                                <p class="whitespace-pre-line">{{ company.address }}</p>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-1">
                            <div v-if="company.phone" class="flex items-center space-x-2">
                                <Phone class="w-4 h-4 text-gray-400" />
                                <a :href="`tel:${company.phone}`" class="text-blue-600 hover:underline">
                                    {{ company.phone }}
                                </a>
                            </div>

                            <div v-if="company.email" class="flex items-center space-x-2">
                                <Mail class="w-4 h-4 text-gray-400" />
                                <a :href="`mailto:${company.email}`" class="text-blue-600 hover:underline">
                                    {{ company.email }}
                                </a>
                            </div>

                            <div v-if="company.website" class="flex items-center space-x-2">
                                <Globe class="w-4 h-4 text-gray-400" />
                                <a :href="company.website" target="_blank" class="text-blue-600 hover:underline">
                                    {{ company.website }}
                                </a>
                            </div>
                        </div>

                        <div v-if="!hasBasicInfo" class="text-center py-4 text-gray-500">
                            <Building class="w-8 h-8 mx-auto mb-2 text-gray-300" />
                            <p class="text-sm">Contact information not provided</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Legal Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center space-x-2">
                            <FileText class="w-5 h-5" />
                            <span>Legal Information</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div v-if="company.tax_number">
                            <Label class="text-sm font-medium text-gray-500">Tax Number</Label>
                            <div class="flex items-center space-x-2 mt-1">
                                <Hash class="w-4 h-4 text-gray-400" />
                                <p class="font-mono">{{ company.tax_number }}</p>
                            </div>
                        </div>

                        <div v-if="company.registration_number">
                            <Label class="text-sm font-medium text-gray-500">Registration Number</Label>
                            <div class="flex items-center space-x-2 mt-1">
                                <Hash class="w-4 h-4 text-gray-400" />
                                <p class="font-mono">{{ company.registration_number }}</p>
                            </div>
                        </div>

                        <div v-if="!hasLegalInfo" class="text-center py-8 text-gray-500">
                            <FileText class="w-8 h-8 mx-auto mb-2 text-gray-300" />
                            <p class="text-sm">Legal information not provided</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Banking Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center space-x-2">
                            <CreditCard class="w-5 h-5" />
                            <span>Banking Information</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div v-if="company.bank_details?.bank_name">
                            <Label class="text-sm font-medium text-gray-500">Bank Name</Label>
                            <p class="mt-1">{{ company.bank_details.bank_name }}</p>
                        </div>

                        <div v-if="company.bank_details?.account_holder">
                            <Label class="text-sm font-medium text-gray-500">Account Holder</Label>
                            <p class="mt-1">{{ company.bank_details.account_holder }}</p>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div v-if="company.bank_details?.account_number">
                                <Label class="text-sm font-medium text-gray-500">Account Number</Label>
                                <p class="mt-1 font-mono">{{ formatBankAccount(company.bank_details.account_number) }}</p>
                            </div>

                            <div v-if="company.bank_details?.routing_number">
                                <Label class="text-sm font-medium text-gray-500">Routing Number</Label>
                                <p class="mt-1 font-mono">{{ company.bank_details.routing_number }}</p>
                            </div>
                        </div>

                        <div v-if="!hasBankDetails" class="text-center py-8 text-gray-500">
                            <CreditCard class="w-8 h-8 mx-auto mb-2 text-gray-300" />
                            <p class="text-sm">Banking information not provided</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Branding -->
                <Card>
                    <CardHeader>
                        <CardTitle>Branding Assets</CardTitle>
                        <CardDescription>Logo and signature for documents</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <!-- Logo -->
                        <div>
                            <Label class="text-sm font-medium text-gray-500">Company Logo</Label>
                            <div v-if="logoUrl" class="mt-2">
                                <div class="flex items-center space-x-4">
                                    <div class="w-20 h-20 border rounded bg-white flex items-center justify-center">
                                        <img :src="logoUrl" :alt="`${company.name} logo`" class="max-w-full max-h-full object-contain" />
                                    </div>
                                    <div class="flex space-x-2">
                                        <Button variant="outline" size="sm" @click="downloadLogo">
                                            <Download class="w-4 h-4 mr-2" />
                                            Download
                                        </Button>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="mt-2 text-sm text-gray-500">
                                No logo uploaded
                            </div>
                        </div>

                        <!-- Signature -->
                        <div>
                            <Label class="text-sm font-medium text-gray-500">Digital Signature</Label>
                            <div v-if="signatureUrl" class="mt-2">
                                <div class="flex items-center space-x-4">
                                    <div class="w-32 h-16 border rounded bg-white flex items-center justify-center">
                                        <img :src="signatureUrl" :alt="`${company.name} signature`" class="max-w-full max-h-full object-contain" />
                                    </div>
                                    <div class="flex space-x-2">
                                        <Button variant="outline" size="sm" @click="downloadSignature">
                                            <Download class="w-4 h-4 mr-2" />
                                            Download
                                        </Button>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="mt-2 text-sm text-gray-500">
                                No signature uploaded
                            </div>
                        </div>

                        <div v-if="!hasBranding" class="text-center py-4 text-gray-500">
                            <Eye class="w-8 h-8 mx-auto mb-2 text-gray-300" />
                            <p class="text-sm">No branding assets uploaded</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Metadata -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center space-x-2">
                        <Calendar class="w-5 h-5" />
                        <span>Company Timeline</span>
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <Label class="text-sm font-medium text-gray-500">Created</Label>
                            <p class="mt-1">{{ formatDate(company.created_at) }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-gray-500">Last Updated</Label>
                            <p class="mt-1">{{ formatDate(company.updated_at) }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Quick Actions -->
            <Card>
                <CardHeader>
                    <CardTitle>Quick Actions</CardTitle>
                    <CardDescription>Common tasks related to company management</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-3">
                        <Button variant="outline" as-child>
                            <Link href="/invoices/create">
                                <FileText class="w-4 h-4 mr-2" />
                                Create Invoice
                            </Link>
                        </Button>
                        <Button variant="outline" as-child>
                            <Link href="/quotations/create">
                                <FileText class="w-4 h-4 mr-2" />
                                Create Quotation
                            </Link>
                        </Button>
                        <Button variant="outline" as-child>
                            <Link href="/clients">
                                <Building class="w-4 h-4 mr-2" />
                                Manage Clients
                            </Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
