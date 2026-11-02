<?php

require_once __DIR__ . '/../models/Lead.php';
require_once __DIR__ . '/../models/FollowUp.php';
require_once __DIR__ . '/../models/Activity.php';

class DashboardController extends Controller
{
    public function index()
    {
        $this->requireAuth();

        $leadModel = new Lead();
        $followUpModel = new FollowUp();
        $activityModel = new Activity();

        // 1. Dynamic Metric Totals from MySQL
        $totalLeads = $leadModel->countAll();
        $newLeads = $leadModel->countByStatus('New');
        $contactedLeads = $leadModel->countByStatus('Contacted');
        $convertedLeads = $leadModel->countByStatus('Converted');
        $interestedLeads = $leadModel->countByStatus('Interested');
        $lostLeads = $leadModel->countByStatus('Lost');

        $conversionRate = ($totalLeads > 0) 
            ? round(($convertedLeads / $totalLeads) * 100, 1) 
            : 0;

        $pendingFollowups = $followUpModel->countPending();

        // 2. Real Opportunity Rows & Follow-up Task Lists
        $recentLeads = $leadModel->recent(5);
        $upcomingFollowups = $followUpModel->upcoming(4);
        $recentActivities = $activityModel->recent(5);

        // 3. Status distribution for Chart.js
        $statusDistribution = $leadModel->getStatusDistribution();

        $this->renderView('dashboard/index', [
            'pageTitle' => 'Dashboard Overview — LeadFlow SaaS',
            'activePage' => 'dashboard',
            'breadcrumbCategory' => 'Dashboard',
            'breadcrumbPage' => 'Overview',
            'metrics' => [
                'totalLeads' => $totalLeads,
                'newLeads' => $newLeads,
                'contactedLeads' => $contactedLeads,
                'convertedLeads' => $convertedLeads,
                'interestedLeads' => $interestedLeads,
                'lostLeads' => $lostLeads,
                'conversionRate' => $conversionRate,
                'pendingFollowups' => $pendingFollowups
            ],
            'recentLeads' => $recentLeads,
            'upcomingFollowups' => $upcomingFollowups,
            'recentActivities' => $recentActivities,
            'statusDistribution' => $statusDistribution,
            'extraScripts' => [
                'https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js',
                '/leadflow/assets/js/charts.js',
                '/leadflow/assets/js/leads.js'
            ]
        ]);
    }
}
