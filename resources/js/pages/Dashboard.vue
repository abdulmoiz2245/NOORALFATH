<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Plus, FileText, Users, Briefcase, DollarSign, AlertCircle } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

interface Stats {
    totalClients: number;
    activeProjects: number;
    pendingInvoices: number;
    overdueInvoices: number;
    totalRevenue: number;
    monthlyRevenue: number;
}

interface RecentInvoice {
    id: number;
    number: string;
    client: string;
    amount: number;
    status: string;
    date: string;
}

interface RecentProject {
    id: number;
    name: string;
    client: string;
    status: string;
    progress: number;
}

interface Props {
    stats: Stats;
    recentInvoices: RecentInvoice[];
    recentProjects: RecentProject[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const getStatusColor = (status: string) => {
  switch (status) {
    case 'paid':
    case 'completed':
      return 'bg-green-100 text-green-800';
    case 'pending':
    case 'in_progress':
      return 'bg-yellow-100 text-yellow-800';
    case 'overdue':
      return 'bg-red-100 text-red-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Welcome Section -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Dashboard</h1>
                    <p class="text-muted-foreground">Welcome to Noor Alfath System</p>
                </div>
                <div class="flex gap-2">
                    <Button as-child>
                        <Link href="/invoices/create">
                            <Plus class="w-4 h-4 mr-2" />
                            New Invoice
                        </Link>
                    </Button>
                    <Button variant="outline" as-child>
                        <Link href="/quotations/create">
                            <FileText class="w-4 h-4 mr-2" />
                            New Quotation
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Clients</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ props.stats.totalClients }}</div>
                        <p class="text-xs text-muted-foreground">+2 from last month</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Active Projects</CardTitle>
                        <Briefcase class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ props.stats.activeProjects }}</div>
                        <p class="text-xs text-muted-foreground">+3 from last month</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Monthly Revenue</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">${{ props.stats.monthlyRevenue.toLocaleString() }}</div>
                        <p class="text-xs text-muted-foreground">+12% from last month</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Pending Invoices</CardTitle>
                        <AlertCircle class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ props.stats.pendingInvoices }}</div>
                        <p class="text-xs text-muted-foreground">{{ props.stats.overdueInvoices }} overdue</p>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <!-- Recent Invoices -->
                <Card>
                    <CardHeader>
                        <CardTitle>Recent Invoices</CardTitle>
                        <CardDescription>Latest invoices from your clients</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div v-for="invoice in props.recentInvoices" :key="invoice.id" class="flex items-center justify-between p-3 border rounded-lg">
                                <div>
                                    <p class="font-medium">{{ invoice.number }}</p>
                                    <p class="text-sm text-muted-foreground">{{ invoice.client }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium">${{ invoice.amount.toLocaleString() }}</p>
                                    <span 
                                        :class="getStatusColor(invoice.status)" 
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    >
                                        {{ invoice.status.replace('_', ' ') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <Button variant="outline" class="w-full" as-child>
                                <Link href="/invoices">View All Invoices</Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Projects -->
                <Card>
                    <CardHeader>
                        <CardTitle>Recent Projects</CardTitle>
                        <CardDescription>Current project status overview</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div v-for="project in props.recentProjects" :key="project.id" class="p-3 border rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="font-medium">{{ project.name }}</p>
                                    <span 
                                        :class="getStatusColor(project.status)" 
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    >
                                        {{ project.status.replace('_', ' ') }}
                                    </span>
                                </div>
                                <p class="text-sm text-muted-foreground mb-2">{{ project.client }}</p>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div 
                                        class="bg-primary h-2 rounded-full transition-all duration-300" 
                                        :style="{ width: `${project.progress}%` }"
                                    ></div>
                                </div>
                                <p class="text-xs text-muted-foreground mt-1">{{ project.progress }}% complete</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <Button variant="outline" class="w-full" as-child>
                                <Link href="/projects">View All Projects</Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
