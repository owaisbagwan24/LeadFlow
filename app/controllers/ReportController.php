<?php

require_once __DIR__ . '/../models/Lead.php';
require_once __DIR__ . '/../models/FollowUp.php';
require_once __DIR__ . '/../models/User.php';

class ReportController extends Controller
{
    public function index()
    {
        $this->requireAuth();

        $leadModel    = new Lead();
        $followUpModel = new FollowUp();
        $userModel    = new User();

        // ── Lead pipeline stats ──────────────────
        $totalLeads     = $leadModel->countAll();
        $convertedLeads = $leadModel->countByStatus('Converted');
        $newLeads       = $leadModel->countByStatus('New');
        $lostLeads      = $leadModel->countByStatus('Lost');
        $conversionRate = $totalLeads > 0
            ? round(($convertedLeads / $totalLeads) * 100, 1)
            : 0;

        $statusDist  = $leadModel->getStatusDistribution();

        // ── Source breakdown ─────────────────────
        $sourceDist  = $this->getSourceDistribution();

        // ── Follow-up stats ──────────────────────
        $totalFU     = $followUpModel->countAll();
        $upcomingFU  = $followUpModel->countByStatus('upcoming');
        $overdueFU   = $followUpModel->countByStatus('overdue');
        $completedFU = $followUpModel->countByStatus('completed');

        // ── Per-rep stats ────────────────────────
        $repStats    = $this->getRepStats();

        // ── All leads for detailed table ─────────
        $allLeads    = $leadModel->all();
        $allFollowups = $followUpModel->all([]);

        // ── Top lead source ──────────────────────
        $topSource   = !empty($sourceDist) ? array_keys($sourceDist, max($sourceDist))[0] : '—';

        $this->renderView('reports/index', [
            'pageTitle'          => 'Reports & Analytics — LeadFlow',
            'activePage'         => 'reports',
            'breadcrumbCategory' => 'Intelligence',
            'breadcrumbPage'     => 'Reports & Analytics',
            'totalLeads'         => $totalLeads,
            'convertedLeads'     => $convertedLeads,
            'newLeads'           => $newLeads,
            'lostLeads'          => $lostLeads,
            'conversionRate'     => $conversionRate,
            'statusDist'         => $statusDist,
            'sourceDist'         => $sourceDist,
            'topSource'          => $topSource,
            'totalFU'            => $totalFU,
            'upcomingFU'         => $upcomingFU,
            'overdueFU'          => $overdueFU,
            'completedFU'        => $completedFU,
            'repStats'           => $repStats,
            'allLeads'           => $allLeads,
            'allFollowups'       => $allFollowups,
            'extraScripts'       => [
                'https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js',
                '/leadflow/assets/js/charts.js'
            ]
        ]);
    }

    /* ─────────────────────────────────────────
       GET /reports/export — Download CSV
    ───────────────────────────────────────── */
    public function export()
    {
        $this->requireAuth();

        $type = $_GET['type'] ?? 'leads';

        if ($type === 'followups') {
            $this->exportFollowUps();
        } else {
            $this->exportLeads();
        }
    }

    private function exportLeads()
    {
        $leadModel = new Lead();
        $leads = $leadModel->all();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="LeadFlow_Leads_Report_' . date('Y-m-d') . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Lead ID', 'Name', 'Email', 'Phone', 'Company', 'Source', 'Status', 'Priority', 'Assigned To', 'Created Date', 'Follow-up Date', 'Notes']);

        foreach ($leads as $l) {
            fputcsv($out, [
                $l['lead_code'], $l['name'], $l['email'], $l['phone'] ?? '',
                $l['company'], $l['lead_source'], $l['status'], $l['priority'],
                $l['assigned_to_name'] ?? 'Unassigned',
                $l['created_at'], $l['follow_up_date'] ?? '', $l['notes'] ?? ''
            ]);
        }
        fclose($out);
        exit;
    }

    private function exportFollowUps()
    {
        $followUpModel = new FollowUp();
        $followups = $followUpModel->all([]);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="LeadFlow_FollowUps_Report_' . date('Y-m-d') . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID', 'Lead Name', 'Company', 'Activity Type', 'Due Date', 'Status', 'Assigned To', 'Notes', 'Completed At']);

        foreach ($followups as $f) {
            fputcsv($out, [
                $f['id'], $f['customer_name'] ?? '', $f['company_name'] ?? '',
                $f['activity_type'], $f['due_date'], $f['status'],
                $f['assigned_to_name'] ?? 'Unassigned',
                $f['notes'] ?? '', $f['completed_at'] ?? ''
            ]);
        }
        fclose($out);
        exit;
    }

    /* ─────────────────────────────────────────
       Private helpers
    ───────────────────────────────────────── */
    private function getSourceDistribution(): array
    {
        try {
            $db = \Database::getConnection();
            $rows = $db->query(
                "SELECT lead_source, COUNT(*) as cnt FROM leads GROUP BY lead_source ORDER BY cnt DESC"
            )->fetchAll();
            $result = [];
            foreach ($rows as $r) {
                $result[$r['lead_source']] = (int)$r['cnt'];
            }
            return $result;
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getRepStats(): array
    {
        try {
            $db = \Database::getConnection();
            $rows = $db->query(
                "SELECT u.name AS rep_name,
                        COUNT(l.id) AS total_leads,
                        SUM(CASE WHEN l.status = 'Converted' THEN 1 ELSE 0 END) AS converted,
                        SUM(CASE WHEN l.status = 'New' OR l.status = 'Contacted' OR l.status = 'Interested' THEN 1 ELSE 0 END) AS active
                 FROM users u
                 LEFT JOIN leads l ON l.assigned_to_id = u.id
                 WHERE u.role = 'Employee' OR u.role = 'Admin'
                 GROUP BY u.id, u.name
                 ORDER BY converted DESC"
            )->fetchAll();

            return array_map(function($r) {
                $rate = $r['total_leads'] > 0
                    ? round(($r['converted'] / $r['total_leads']) * 100, 1)
                    : 0;
                return array_merge($r, ['win_rate' => $rate]);
            }, $rows);
        } catch (\Exception $e) {
            return [];
        }
    }
}
