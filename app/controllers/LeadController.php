<?php

require_once __DIR__ . '/../models/Lead.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/FollowUp.php';
require_once __DIR__ . '/../models/Activity.php';

class LeadController extends Controller
{
    public function index()
    {
        $this->requireAuth();

        $leadModel = new Lead();
        $userModel = new User();

        $filters = [
            'status' => $_GET['status'] ?? 'all',
            'priority' => $_GET['priority'] ?? 'all',
            'assigned_to_id' => $_GET['assigned_to_id'] ?? 'all',
            'search' => trim($_GET['search'] ?? '')
        ];

        $leads = $leadModel->all($filters);
        $users = $userModel->all();

        $this->renderView('leads/index', [
            'pageTitle' => 'Leads Management — LeadFlow SaaS',
            'activePage' => 'leads',
            'breadcrumbCategory' => 'Pipeline',
            'breadcrumbPage' => 'All Leads',
            'leadsList' => $leads,
            'usersList' => $users,
            'filters' => $filters,
            'extraScripts' => [
                '/leadflow/assets/js/leads.js'
            ]
        ]);
    }

    public function store()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/leadflow/leads');
        }

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
               || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $company = trim($_POST['company'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $leadSource = $_POST['lead_source'] ?? 'Website Form';
        $status = $_POST['status'] ?? 'New';
        $priority = $_POST['priority'] ?? 'Medium';
        $assignedToId = !empty($_POST['assigned_to_id']) ? (int)$_POST['assigned_to_id'] : null;
        $followUpDate = !empty($_POST['follow_up_date']) ? $_POST['follow_up_date'] : null;
        $notes = trim($_POST['notes'] ?? '');

        if (empty($name) || empty($email) || empty($company)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Name, Email, and Company are required fields.']);
                exit;
            }
            $_SESSION['flash_error'] = 'Name, Email, and Company are required fields.';
            $this->redirect('/leadflow/leads');
        }

        $leadModel = new Lead();
        $leadId = $leadModel->create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'company' => $company,
            'lead_source' => $leadSource,
            'status' => $status,
            'priority' => $priority,
            'assigned_to_id' => $assignedToId,
            'created_by_id' => $_SESSION['user_id'],
            'notes' => $notes,
            'follow_up_date' => $followUpDate
        ]);

        if ($leadId) {
            $createdLead = $leadModel->findById($leadId);
            $leadCode = $createdLead['lead_code'] ?? "LF-$leadId";

            // Log activity
            $activityModel = new Activity();
            $activityModel->log($leadId, $_SESSION['user_id'], 'Lead Created', "New opportunity registered for {$company}");

            // Create initial follow-up task if reminder date was specified
            if (!empty($followUpDate)) {
                $followUpModel = new FollowUp();
                $followUpModel->create([
                    'lead_id' => $leadId,
                    'assigned_to_id' => $assignedToId,
                    'activity_type' => 'Phone Call',
                    'due_date' => $followUpDate,
                    'status' => 'upcoming',
                    'notes' => "Initial outreach for {$name}"
                ]);
            }

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => "Lead {$leadCode} created successfully.",
                    'lead' => $createdLead
                ]);
                exit;
            }

            $_SESSION['flash_success'] = "Lead {$leadCode} created successfully.";
        } else {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Failed to insert lead into database.']);
                exit;
            }
            $_SESSION['flash_error'] = 'Failed to create lead in database.';
        }

        $this->redirect('/leadflow/leads');
    }

    public function update()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/leadflow/leads');
        }

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        $id = $_POST['id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $company = trim($_POST['company'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $leadSource = $_POST['lead_source'] ?? 'Website Form';
        $status = $_POST['status'] ?? 'New';
        $priority = $_POST['priority'] ?? 'Medium';
        $assignedToId = !empty($_POST['assigned_to_id']) ? (int)$_POST['assigned_to_id'] : null;
        $followUpDate = !empty($_POST['follow_up_date']) ? $_POST['follow_up_date'] : null;
        $notes = trim($_POST['notes'] ?? '');

        if (empty($id) || empty($name) || empty($email) || empty($company)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Valid ID, Name, Email, and Company are required.']);
                exit;
            }
            $_SESSION['flash_error'] = 'Valid ID, Name, Email, and Company are required.';
            $this->redirect('/leadflow/leads');
        }

        $leadModel = new Lead();
        $updated = $leadModel->update($id, [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'company' => $company,
            'lead_source' => $leadSource,
            'status' => $status,
            'priority' => $priority,
            'assigned_to_id' => $assignedToId,
            'notes' => $notes,
            'follow_up_date' => $followUpDate
        ]);

        if ($updated) {
            $lead = $leadModel->findById($id);
            $activityModel = new Activity();
            $activityModel->log($lead['id'], $_SESSION['user_id'], 'Lead Updated', "Details and pipeline status ({$status}) updated.");

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Lead record updated successfully.', 'lead' => $lead]);
                exit;
            }
            $_SESSION['flash_success'] = 'Lead record updated successfully.';
        } else {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Failed to update lead record.']);
                exit;
            }
            $_SESSION['flash_error'] = 'Failed to update lead record.';
        }

        $this->redirect('/leadflow/leads');
    }

    public function destroy()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/leadflow/leads');
        }

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        $id = $_POST['id'] ?? null;

        if (empty($id)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Lead ID is required for deletion.']);
                exit;
            }
            $this->redirect('/leadflow/leads');
        }

        $leadModel = new Lead();
        $lead = $leadModel->findById($id);

        if ($lead) {
            $leadModel->delete($id);

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => "Lead {$lead['lead_code']} has been deleted."]);
                exit;
            }
            $_SESSION['flash_success'] = "Lead {$lead['lead_code']} has been deleted.";
        } else {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Lead not found.']);
                exit;
            }
            $_SESSION['flash_error'] = 'Lead not found.';
        }

        $this->redirect('/leadflow/leads');
    }

    public function getLead()
    {
        $this->requireAuth();

        $id = $_GET['id'] ?? null;
        if (empty($id)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Missing ID parameter.']);
            exit;
        }

        $leadModel = new Lead();
        $lead = $leadModel->findById($id);

        header('Content-Type: application/json');
        if ($lead) {
            echo json_encode(['success' => true, 'lead' => $lead]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Lead not found.']);
        }
        exit;
    }

    public function export()
    {
        $this->requireAuth();

        $leadModel = new Lead();
        $leads = $leadModel->all();

        $filename = 'LeadFlow_Leads_Export_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, [
            'Lead ID',
            'Customer Name',
            'Email',
            'Phone',
            'Company',
            'Lead Source',
            'Status',
            'Priority',
            'Assigned Representative',
            'Created Date',
            'Follow-up Due',
            'Notes'
        ]);

        foreach ($leads as $l) {
            fputcsv($output, [
                $l['lead_code'],
                $l['name'],
                $l['email'],
                $l['phone'] ?? '',
                $l['company'],
                $l['lead_source'],
                $l['status'],
                $l['priority'],
                $l['assigned_to_name'] ?? 'Unassigned',
                $l['created_at'],
                $l['follow_up_date'] ?? '',
                $l['notes'] ?? ''
            ]);
        }

        fclose($output);
        exit;
    }
}
