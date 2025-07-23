<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Plus, Search, Eye, Edit, Trash2, Users } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface Client {
    id: number;
    name: string;
    company_name?: string;
    email?: string;
    phone?: string;
    address?: string;
    city?: string;
    state?: string;
    postal_code?: string;
    country?: string;
    projects_count: number;
    invoices_count: number;
    total_invoiced: number;
    created_at: string;
    updated_at: string;
}

interface Props {
    clients: Client[];
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
];

const searchQuery = ref('');

const filteredClients = computed(() => {
    if (!searchQuery.value) return props.clients;
    return props.clients.filter(client => 
        client.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        (client.company_name && client.company_name.toLowerCase().includes(searchQuery.value.toLowerCase())) ||
        (client.email && client.email.toLowerCase().includes(searchQuery.value.toLowerCase()))
    );
});
</script>

<template>
    <Head title="Clients" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Clients</h1>
                    <p class="text-muted-foreground">Manage your client relationships</p>
                </div>
                <Button as-child>
                    <Link href="/clients/create">
                        <Plus class="w-4 h-4 mr-2" />
                        Add Client
                    </Link>
                </Button>
            </div>

            <!-- Search and Filters -->
            <Card>
                <CardHeader>
                    <div class="flex items-center space-x-2">
                        <Search class="w-4 h-4 text-muted-foreground" />
                        <Input
                            v-model="searchQuery"
                            placeholder="Search clients..."
                            class="max-w-sm"
                        />
                    </div>
                </CardHeader>
            </Card>

            <!-- Clients List -->
            <div class="grid gap-4">
                <Card v-for="client in filteredClients" :key="client.id">
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center">
                                    <span class="text-lg font-semibold text-primary">
                                        {{ client.name.split(' ').map((n: string) => n[0]).join('') }}
                                    </span>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold">{{ client.name }}</h3>
                                    <p class="text-sm text-muted-foreground">{{ client.company_name }}</p>
                                    <div class="flex items-center space-x-4 mt-1">
                                        <span class="text-sm text-muted-foreground">{{ client.email }}</span>
                                        <span class="text-sm text-muted-foreground">{{ client.phone }}</span>
                                        <span class="text-sm text-muted-foreground">{{ client.city }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-4">
                                <div class="text-right">
                                    <p class="text-sm font-medium">{{ client.projects_count }} Projects</p>
                                    <p class="text-sm text-muted-foreground">${{ client.total_invoiced.toLocaleString() }} Total</p>
                                </div>
                                <div class="flex space-x-2">
                                    <Button variant="outline" size="sm" as-child>
                                        <Link :href="`/clients/${client.id}`">
                                            <Eye class="w-4 h-4" />
                                        </Link>
                                    </Button>
                                    <Button variant="outline" size="sm" as-child>
                                        <Link :href="`/clients/${client.id}/edit`">
                                            <Edit class="w-4 h-4" />
                                        </Link>
                                    </Button>
                                    <Button variant="outline" size="sm">
                                        <Trash2 class="w-4 h-4 text-red-600" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <Card v-if="filteredClients.length === 0" class="text-center py-12">
                <CardContent>
                    <Users class="w-12 h-12 text-muted-foreground mx-auto mb-4" />
                    <h3 class="text-lg font-semibold mb-2">No clients found</h3>
                    <p class="text-muted-foreground mb-4">
                        {{ searchQuery ? 'Try adjusting your search criteria' : 'Get started by adding your first client' }}
                    </p>
                    <Button v-if="!searchQuery" as-child>
                        <Link href="/clients/create">
                            <Plus class="w-4 h-4 mr-2" />
                            Add Client
                        </Link>
                    </Button>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
