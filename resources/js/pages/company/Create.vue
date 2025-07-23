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
import { ArrowLeft, Save, Building, Upload, X } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import { ref, computed } from 'vue';

const { success, error } = useToast();
const logoPreview = ref<string | null>(null);
const signaturePreview = ref<string | null>(null);

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
        title: 'Create',
        href: '/company/create',
    },
];

const form = useForm({
    name: '',
    address: '',
    phone: '',
    email: '',
    website: '',
    logo: null as File | null,
    signature: null as File | null,
    tax_number: '',
    registration_number: '',
    bank_name: '',
    account_number: '',
    routing_number: '',
    account_holder: '',
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
        } else {
            formData.append(key, form[key] || '');
        }
    });

    // Add bank details as JSON
    const bankDetails = {
        bank_name: form.bank_name,
        account_number: form.account_number,
        routing_number: form.routing_number,
        account_holder: form.account_holder,
    };
    formData.append('bank_details', JSON.stringify(bankDetails));

    form.post('/companies', {
        data: formData,
        forceFormData: true,
        onSuccess: () => {
            success('Success!', 'Company created successfully');
        },
        onError: () => {
            error('Error!', 'Failed to create company. Please check the form and try again.');
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
        
        const reader = new FileReader();
        reader.onload = (e) => {
            signaturePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const removeLogo = () => {
    form.logo = null;
    logoPreview.value = null;
    // Reset file input
    const logoInput = document.getElementById('logo') as HTMLInputElement;
    if (logoInput) logoInput.value = '';
};

const removeSignature = () => {
    form.signature = null;
    signaturePreview.value = null;
    // Reset file input
    const signatureInput = document.getElementById('signature') as HTMLInputElement;
    if (signatureInput) signatureInput.value = '';
};
</script>

<template>
    <Head title="Create Company" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Button variant="outline" size="sm" as-child>
                        <Link href="/company">
                            <ArrowLeft class="w-4 h-4 mr-2" />
                            Back to Companies
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">Create Company</h1>
                        <p class="text-muted-foreground">Set up your company information for invoices and quotations</p>
                    </div>
                </div>
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
                        <CardDescription>Enter your company's basic details</CardDescription>
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
                                <Label for="routing_number">Routing Number</Label>
                                <Input
                                    id="routing_number"
                                    v-model="form.routing_number"
                                    placeholder="Enter routing number"
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
                                        :disabled="!!logoPreview"
                                    >
                                        <Upload class="w-4 h-4 mr-2" />
                                        Choose Logo
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
                                        :disabled="!!signaturePreview"
                                    >
                                        <Upload class="w-4 h-4 mr-2" />
                                        Choose Signature
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

                <!-- Form Actions -->
                <div class="flex justify-end space-x-4">
                    <Button variant="outline" type="button" as-child>
                        <Link href="/companies">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Save class="w-4 h-4 mr-2" />
                        {{ form.processing ? 'Creating...' : 'Create Company' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
