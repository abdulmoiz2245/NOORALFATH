<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Plus, Search, Eye, Edit, Trash2, Briefcase, CalendarDays, DollarSign } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface Project {
    id: number;
    name: string;
    description?: string;
    client: string;
    client_id: number;
    status: string;
    start_date: string;
    end_date?: string;
    budget?: number;
    invoices_count: number;
    created_at: string;
}

interface Props {
    projects: Project[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Projects',
        href: '/projects',
    },
];

const searchQuery = ref('');

const filteredProjects = computed(() => {
    if (!searchQuery.value) return props.projects;
    return props.projects.filter(project => 
        project.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        project.client.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        (project.description && project.description.toLowerCase().includes(searchQuery.value.toLowerCase()))
    );
});

const getStatusColor = (status: string) => {
    switch (status) {
        case 'completed':
            return 'bg-green-100 text-green-800';
        case 'in_progress':
            return 'bg-blue-100 text-blue-800';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString();
};
</script>

<template>
    <Head title="Projects" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Projects</h1>
                    <p class="text-muted-foreground">Manage your client projects</p>
                </div>
                <Button as-child>
                    <Link href="/projects/create">
                        <Plus class="w-4 h-4 mr-2" />
                        Add Project
                    </Link>
                </Button>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Projects</CardTitle>
                        <Briefcase class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ props.projects.length }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">In Progress</CardTitle>
                        <CalendarDays class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ props.projects.filter(p => p.status === 'in_progress').length }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Completed</CardTitle>
                        <Briefcase class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ props.projects.filter(p => p.status === 'completed').length }}</div>
                    </CardContent>
                </Card>

                <!-- <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Budget</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            ${{ props.projects.reduce((sum, p) => sum + (p.budget || 0), 0).toLocaleString() }}
                        </div>
                    </CardContent>
                </Card> -->
            </div>

            <!-- Search and Filters -->
            <Card>
                <CardHeader>
                    <div class="flex justify-between items-center">
                        <div>
                            <CardTitle>All Projects</CardTitle>
                            <CardDescription>View and manage your project portfolio</CardDescription>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="relative">
                                <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input
                                    placeholder="Search projects..."
                                    v-model="searchQuery"
                                    class="pl-8"
                                />
                            </div>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="filteredProjects.length === 0" class="text-center py-8 text-muted-foreground">
                        <Briefcase class="mx-auto h-12 w-12 mb-4" />
                        <p>No projects found</p>
                        <Button as-child class="mt-4">
                            <Link href="/projects/create">Create your first project</Link>
                        </Button>
                    </div>

                    <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <Card v-for="project in filteredProjects" :key="project.id" class="hover:shadow-md transition-shadow">
                            <CardHeader>
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <CardTitle class="text-lg">{{ project.name }}</CardTitle>
                                        <CardDescription>{{ project.client }}</CardDescription>
                                    </div>
                                    <span 
                                        :class="getStatusColor(project.status)"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    >
                                        {{ project.status.replace('_', ' ') }}
                                    </span>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-2">
                                    <p v-if="project.description" class="text-sm text-muted-foreground line-clamp-2">
                                        {{ project.description }}
                                    </p>
                                    
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-muted-foreground">Start Date:</span>
                                        <span>{{ formatDate(project.start_date) }}</span>
                                    </div>
                                    
                                    <div v-if="project.end_date" class="flex justify-between items-center text-sm">
                                        <span class="text-muted-foreground">End Date:</span>
                                        <span>{{ formatDate(project.end_date) }}</span>
                                    </div>
                                    
                                    <div v-if="project.budget" class="flex justify-between items-center text-sm">
                                        <span class="text-muted-foreground">Budget:</span>
                                        <span class="font-medium">${{ project.budget.toLocaleString() }}</span>
                                    </div>
                                    
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-muted-foreground">Invoices:</span>
                                        <span>{{ project.invoices_count }}</span>
                                    </div>
                                </div>
                                
                                <div class="flex justify-end space-x-2 mt-4">
                                    <Button variant="outline" size="sm" as-child>
                                        <Link :href="`/projects/${project.id}`">
                                            <Eye class="w-4 h-4" />
                                        </Link>
                                    </Button>
                                    <Button variant="outline" size="sm" as-child>
                                        <Link :href="`/projects/${project.id}/edit`">
                                            <Edit class="w-4 h-4" />
                                        </Link>
                                    </Button>
                                    <Button variant="destructive" size="sm">
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
