<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Get statistics
        $stats = [
            'totalClients' => Client::count(),
            'activeProjects' => Project::where('status', 'in_progress')->count(),
            'pendingInvoices' => Invoice::where('status', 'pending')->count(),
            'overdueInvoices' => Invoice::where('status', 'overdue')->count(),
            'totalRevenue' => Payment::sum('amount'),
            'monthlyRevenue' => Payment::whereMonth('created_at', Carbon::now()->month)
                                     ->whereYear('created_at', Carbon::now()->year)
                                     ->sum('amount'),
        ];

        // Get recent invoices
        $recentInvoices = Invoice::with('client')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'number' => $invoice->invoice_number,
                    'client' => $invoice->client->name,
                    'amount' => $invoice->total_amount,
                    'status' => $invoice->status,
                    'date' => $invoice->created_at->format('Y-m-d'),
                ];
            });

        // Get recent projects
        $recentProjects = Project::with('client')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'client' => $project->client->name,
                    'status' => $project->status,
                    'progress' => $this->calculateProgress($project),
                ];
            });

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentInvoices' => $recentInvoices,
            'recentProjects' => $recentProjects,
        ]);
    }

    private function calculateProgress($project)
    {
        // Simple progress calculation based on status
        switch ($project->status) {
            case 'completed':
                return 100;
            case 'in_progress':
                return 50; // Could be more sophisticated based on actual progress tracking
            case 'pending':
                return 0;
            default:
                return 0;
        }
    }
}
