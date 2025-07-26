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
import { ArrowLeft, Save, Building, Upload, X, Trash2 } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import { ref, computed } from 'vue';

interface BankDetails {
    bank_name?: string;
    account_number?: string;
    swift_code?: string;
    account_holder?: string;
    iban_number?: string;

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
const { success, error } = useToast();
const logoPreview = ref<string | null>(props.company.logo ? `/storage/${props.company.logo}` : null);
const signaturePreview = ref<string | null>(props.company.signature ? `/storage/${props.company.signature}` : null);
const showDeleteDialog = ref(false);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Company Settings',
        href: '/company',
    },
    {
        title: props.company.name,
        href: `/company/${props.company.id}`,
    },
    {
        title: 'Edit',
        href: `/company/${props.company.id}/edit`,
    },
];

const form = useForm({
    name: props.company.name,
    address: props.company.address || '',
    phone: props.company.phone || '',
    email: props.company.email || '',
    website: props.company.website || '',
    logo: null as File | null,
    signature: null as File | null,
    tax_number: props.company.tax_number || '',
    registration_number: props.company.registration_number || '',
    bank_name: props.company.bank_details?.bank_name || '',
    account_number: props.company.bank_details?.account_number || '',
    swift_code: props.company.bank_details?.swift_code || '',
    account_holder: props.company.bank_details?.account_holder || '',
    iban_number: props.company.bank_details?.iban_number || '',
    remove_logo: false,
    remove_signature: false,
});

const submit = () => {
    const formData = new FormData();
    
    // Add all form fields
    Object.keys(form.data()).forEach(key => {
        if (key === 'logo' || key === 'signature') {
            if (form[key]) {
                formData.append(key, form[key]);
            }
        } else if (key.startsWith('bank_') || key === 'account_holder') {
            // These will be grouped into bank_details
            return;
        } else if (key === 'remove_logo' || key === 'remove_signature') {
            formData.append(key, form[key] ? '1' : '0');
        } else {
            formData.append(key, form[key] || '');
        }
    });

    // Add bank details as JSON
    const bankDetails = {
        bank_name: form.bank_name,
        account_number: form.account_number,
        swift_code: form.swift_code,
        account_holder: form.account_holder,
        iban_number: form.iban_number,
    };
    formData.append('bank_details', JSON.stringify(bankDetails));
    formData.append('_method', 'PUT');

    form.post(`/company/${props.company.id}`, {
        data: formData,
        forceFormData: true,
        onSuccess: () => {
            success('Success!', 'Company updated successfully');
        },
        onError: () => {
            error('Error!', 'Failed to update company. Please check the form and try again.');
        }
    });
};

const deleteCompany = () => {
    router.delete(`/company/${props.company.id}`, {
        onSuccess: () => {
            success('Success!', 'Company deleted successfully');
        },
        onError: () => {
            error('Error!', 'Failed to delete company. Please try again.');
        }
    });
};

const handleLogoUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    
    if (file) {
        if (file.size > 5 * 1024 * 1024) { // 5MB limit
            error('Error!', 'Logo file size must be less than 5MB');
            return;
        }
        
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            error('Error!', 'Logo must be a valid image file (JPEG, PNG, GIF)');
            return;
        }
        
        form.logo = file;
        form.remove_logo = false;
        
        const reader = new FileReader();
        reader.onload = (e) => {
            logoPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const handleSignatureUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    
    if (file) {
        if (file.size > 2 * 1024 * 1024) { // 2MB limit
            error('Error!', 'Signature file size must be less than 2MB');
            return;
        }
        
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!allowedTypes.includes(file.type)) {
            error('Error!', 'Signature must be a valid image file (JPEG, PNG)');
            return;
        }
        
        form.signature = file;
        form.remove_signature = false;
        
        const reader = new FileReader();
        reader.onload = (e) => {
            signaturePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const removeLogo = () => {
    form.logo = null;
    form.remove_logo = true;
    logoPreview.value = null;
    // Reset file input
    const logoInput = document.getElementById('logo') as HTMLInputElement;
    if (logoInput) logoInput.value = '';
};

const removeSignature = () => {
    form.signature = null;
    form.remove_signature = true;
    signaturePreview.value = null;
    // Reset file input
    const signatureInput = document.getElementById('signature') as HTMLInputElement;
    if (signatureInput) signatureInput.value = '';
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

<template>
    <Head :title="`Edit ${company.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="`/company`">
                            <ArrowLeft class="w-4 h-4 mr-2" />
                            Back to Company
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">Edit Company</h1>
                        <p class="text-muted-foreground">Update your company information</p>
                    </div>
                </div>

                <Button variant="destructive" size="sm" @click="showDeleteDialog = true">
                    <Trash2 class="w-4 h-4 mr-2" />
                    Delete
                </Button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center space-x-2">
                            <Building class="w-5 h-5" />
                            <span>Company Information</span>
                        </CardTitle>
                        <CardDescription>Update your company's basic details</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="name">Company Name *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="Enter company name"
                                :class="{ 'border-red-500': form.errors.name }"
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="space-y-2">
                            <Label for="address">Address</Label>
                            <Textarea
                                id="address"
                                v-model="form.address"
                                placeholder="Enter company address"
                                rows="3"
                                :class="{ 'border-red-500': form.errors.address }"
                            />
                            <InputError :message="form.errors.address" />
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="phone">Phone</Label>
                                <Input
                                    id="phone"
                                    v-model="form.phone"
                                    placeholder="Enter phone number"
                                    :class="{ 'border-red-500': form.errors.phone }"
                                />
                                <InputError :message="form.errors.phone" />
                            </div>

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
                        </div>

                        <div class="space-y-2">
                            <Label for="website">Website</Label>
                            <Input
                                id="website"
                                v-model="form.website"
                                placeholder="https://example.com"
                                :class="{ 'border-red-500': form.errors.website }"
                            />
                            <InputError :message="form.errors.website" />
                        </div>
                    </CardContent>
                </Card>

                <!-- Legal Information -->
                <Card>
                    <CardHeader>
                        <CardTitle>Legal Information</CardTitle>
                        <CardDescription>Tax and registration details</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="tax_number">Tax Number</Label>
                                <Input
                                    id="tax_number"
                                    v-model="form.tax_number"
                                    placeholder="Enter tax identification number"
                                    :class="{ 'border-red-500': form.errors.tax_number }"
                                />
                                <InputError :message="form.errors.tax_number" />
                            </div>

                            <div class="space-y-2">
                                <Label for="registration_number">Registration Number</Label>
                                <Input
                                    id="registration_number"
                                    v-model="form.registration_number"
                                    placeholder="Enter business registration number"
                                    :class="{ 'border-red-500': form.errors.registration_number }"
                                />
                                <InputError :message="form.errors.registration_number" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Bank Details -->
                <Card>
                    <CardHeader>
                        <CardTitle>Banking Information</CardTitle>
                        <CardDescription>Payment and banking details for invoices</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="bank_name">Bank Name</Label>
                                <Input
                                    id="bank_name"
                                    v-model="form.bank_name"
                                    placeholder="Enter bank name"
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="account_holder">Account Holder</Label>
                                <Input
                                    id="account_holder"
                                    v-model="form.account_holder"
                                    placeholder="Enter account holder name"
                                />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="account_number">Account Number</Label>
                                <Input
                                    id="account_number"
                                    v-model="form.account_number"
                                    placeholder="Enter account number"
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="swift_code">Swift Code</Label>
                                <Input
                                    id="swift_code"
                                    v-model="form.swift_code"
                                    placeholder="Enter swift code"
                                />
                            </div>
                        </div>
                         <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="iban_number">IBAN#</Label>
                                <Input
                                    id="iban_number"
                                    v-model="form.iban_number"
                                    placeholder="Enter IBAN number"
                                />
                            </div>

                           
                        </div>
                    </CardContent>
                </Card>

                <!-- Branding -->
                <Card>
                    <CardHeader>
                        <CardTitle>Branding</CardTitle>
                        <CardDescription>Logo and signature for documents</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <!-- Logo Upload -->
                        <div class="space-y-4">
                            <Label for="logo">Company Logo</Label>
                            <div class="flex items-start space-x-4">
                                <div class="flex-1">
                                    <input
                                        id="logo"
                                        type="file"
                                        accept="image/*"
                                        @change="handleLogoUpload"
                                        class="hidden"
                                    />
                                    <Button
                                        type="button"
                                        variant="outline"
                                        @click="document.getElementById('logo')?.click()"
                                    >
                                        <Upload class="w-4 h-4 mr-2" />
                                        {{ logoPreview ? 'Change Logo' : 'Choose Logo' }}
                                    </Button>
                                    <p class="text-xs text-gray-500 mt-1">Max 5MB. Formats: JPEG, PNG, GIF</p>
                                </div>
                                
                                <div v-if="logoPreview" class="relative">
                                    <img :src="logoPreview" alt="Logo preview" class="w-24 h-24 object-contain border rounded" />
                                    <Button
                                        type="button"
                                        variant="destructive"
                                        size="sm"
                                        @click="removeLogo"
                                        class="absolute -top-2 -right-2 w-6 h-6 p-0"
                                    >
                                        <X class="w-3 h-3" />
                                    </Button>
                                </div>
                            </div>
                            <InputError :message="form.errors.logo" />
                        </div>

                        <!-- Signature Upload -->
                        <div class="space-y-4">
                            <Label for="signature">Digital Signature</Label>
                            <div class="flex items-start space-x-4">
                                <div class="flex-1">
                                    <input
                                        id="signature"
                                        type="file"
                                        accept="image/jpeg,image/jpg,image/png"
                                        @change="handleSignatureUpload"
                                        class="hidden"
                                    />
                                    <Button
                                        type="button"
                                        variant="outline"
                                        @click="document.getElementById('signature')?.click()"
                                    >
                                        <Upload class="w-4 h-4 mr-2" />
                                        {{ signaturePreview ? 'Change Signature' : 'Choose Signature' }}
                                    </Button>
                                    <p class="text-xs text-gray-500 mt-1">Max 2MB. Formats: JPEG, PNG</p>
                                </div>
                                
                                <div v-if="signaturePreview" class="relative">
                                    <img :src="signaturePreview" alt="Signature preview" class="w-32 h-16 object-contain border rounded bg-white" />
                                    <Button
                                        type="button"
                                        variant="destructive"
                                        size="sm"
                                        @click="removeSignature"
                                        class="absolute -top-2 -right-2 w-6 h-6 p-0"
                                    >
                                        <X class="w-3 h-3" />
                                    </Button>
                                </div>
                            </div>
                            <InputError :message="form.errors.signature" />
                        </div>
                    </CardContent>
                </Card>

                <!-- Company Summary -->
                <Card>
                    <CardHeader>
                        <CardTitle>Company Summary</CardTitle>
                        <CardDescription>Quick overview of company details</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="text-center">
                                <p class="text-2xl font-bold">{{ formatDate(company.created_at) }}</p>
                                <p class="text-sm text-gray-500">Created</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold">{{ formatDate(company.updated_at) }}</p>
                                <p class="text-sm text-gray-500">Last Updated</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-4">
                    <Button variant="outline" type="button" as-child>
                        <Link :href="`/company/${company.id}`">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Save class="w-4 h-4 mr-2" />
                        {{ form.processing ? 'Updating...' : 'Update Company' }}
                    </Button>
                </div>
            </form>
        </div>

        <!-- Delete Confirmation Dialog -->
        <div v-if="showDeleteDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold mb-4">Delete Company</h3>
                <p class="text-gray-600 mb-6">
                    Are you sure you want to delete "{{ company.name }}"? This action cannot be undone and will affect all related invoices and quotations.
                </p>
                <div class="flex justify-end space-x-4">
                    <Button variant="outline" @click="showDeleteDialog = false">Cancel</Button>
                    <Button variant="destructive" @click="deleteCompany">Delete Company</Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
