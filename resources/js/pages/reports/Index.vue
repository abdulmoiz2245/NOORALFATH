<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { FileText, Users, TrendingUp, DollarSign, BarChart3, Calendar } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { ChartContainer, BarChart, DonutChart, AreaChart } from '@/components/ui/chart';

interface DashboardData {
    monthlyRevenue: Array<{ month: string; revenue: number }>;
    invoiceStatus: Array<{ status: string; count: number }>;
    topClients: Array<{ client: string; amount: number }>;
    recentPayments: Array<{ day: string; amount: number }>;
    totalStats: {
        totalInvoices: number;
        totalClients: number;
        totalRevenue: number;
        pendingAmount: number;
    };
}

interface Props {
    dashboardData: DashboardData;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Reports',
        href: '/reports',
    },
];

interface ReportItem {
    title: string;
    description: string;
    href: string;
    icon: any;
    color: string;
}

const reports: ReportItem[] = [
    {
        title: 'Invoice Reports',
        description: 'View invoice details with payment information and collection rates',
        href: '/reports/invoices',
        icon: FileText,
        color: 'text-blue-600',
    },
    {
        title: 'Client Reports',
        description: 'Analyze client performance and payment history',
        href: '/reports/clients',
        icon: Users,
        color: 'text-green-600',
    },
    {
        title: 'Payment Reports',
        description: 'Track payments received and outstanding amounts',
        href: '/reports/payments',
        icon: DollarSign,
        color: 'text-yellow-600',
    },
    {
        title: 'Financial Summary',
        description: 'Overall financial performance and trends',
        href: '/reports/summary',
        icon: TrendingUp,
        color: 'text-purple-600',
    },
];

// Chart configurations
const monthlyRevenueConfig = {
    categories: ['month'],
    series: [{ dataKey: 'revenue' }]
};

const invoiceStatusConfig = {
    categories: ['status'],
    series: [{ dataKey: 'count' }]
};

const topClientsConfig = {
    categories: ['client'],
    series: [{ dataKey: 'amount' }]
};

const recentPaymentsConfig = {
    categories: ['day'],
    series: [{ dataKey: 'amount' }]
};

// Format currency
const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
};
</script>

<template>
    <Head title="Reports" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Reports</h1>
                    <p class="text-muted-foreground">Generate and view business reports and analytics</p>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Revenue</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(props.dashboardData.totalStats.totalRevenue) }}</div>
                    </CardContent>
                </Card>
                
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Invoices</CardTitle>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ props.dashboardData.totalStats.totalInvoices }}</div>
                    </CardContent>
                </Card>
                
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Clients</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ props.dashboardData.totalStats.totalClients }}</div>
                    </CardContent>
                </Card>
                
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Pending Amount</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(props.dashboardData.totalStats.pendingAmount) }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Charts -->
          

            <!-- Report Cards -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-2">
                <Card v-for="report in reports" :key="report.title" class="hover:shadow-lg transition-shadow">
                    <CardHeader>
                        <div class="flex items-center space-x-4">
                            <div class="p-2 rounded-lg bg-muted">
                                <component :is="report.icon" :class="['w-6 h-6', report.color]" />
                            </div>
                            <div>
                                <CardTitle>{{ report.title }}</CardTitle>
                                <CardDescription>{{ report.description }}</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <Button as-child class="w-full">
                            <Link :href="report.href">
                                <BarChart3 class="w-4 h-4 mr-2" />
                                View Report
                            </Link>
                        </Button>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick Stats -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Calendar class="w-5 h-5" />
                        Quick Overview
                    </CardTitle>
                    <CardDescription>
                        Access your most important reports quickly
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-3">
                        <Button variant="outline" as-child>
                            <Link href="/reports/invoices">
                                <FileText class="w-4 h-4 mr-2" />
                                This Month's Invoices
                            </Link>
                        </Button>
                        <Button variant="outline" as-child>
                            <Link href="/reports/payments">
                                <DollarSign class="w-4 h-4 mr-2" />
                                Recent Payments
                            </Link>
                        </Button>
                        <Button variant="outline" as-child>
                            <Link href="/reports/clients">
                                <Users class="w-4 h-4 mr-2" />
                                Top Clients
                            </Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
