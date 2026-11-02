<?php

require_once __DIR__ . '/../models/FollowUp.php';
require_once __DIR__ . '/../models/Lead.php';
require_once __DIR__ . '/../models/Activity.php';

class FollowUpController extends Controller
{
    public function index()
    {
        $this->requireAuth();

        $followUpModel = new FollowUp();
        $leadModel     = new Lead();

        // Auto-mark overdue follow-ups
        $this->updateOverdueStatus();

        $tab      = $_GET['tab'] ?? 'all';
        $leadId   = !empty($_GET['lead_id']) ? (int)$_GET['lead_id'] : null;

        $filters = [
            'status'  => in_array($tab, ['upcoming', 'overdue', 'completed']) ? $tab : null,
            'lead_id' => $leadId
        ];

        $followups = $followUpModel->all($filters);
        $leads     = $leadModel->all();

        // Counts for tabs
        $counts = [
            'all'       => $followUpModel->countAll(),
            'upcoming'  => $followUpModel->countByStatus('upcoming'),
            'overdue'   => $followUpModel->countByStatus('overdue'),
            'completed' => $followUpModel->countByStatus('completed'),
        ];

        $this->renderView('followups/index', [
            'pageTitle'         => 'Follow-ups & Outreach — LeadFlow',
            'activePage'        => 'followups',
            'breadcrumbCategory'=> 'Pipeline',
            'breadcrumbPage'    => 'Follow-ups',
            'followupsList'     => $followups,
            'leadsList'         => $leads,
            'tabCounts'         => $counts,
            'activeTab'         => $tab,
            'extraScripts'      => ['/leadflow/assets/js/followups.js']
        ]);
    }

    /**
     * Auto-update overdue: any upcoming follow-up past its due_date becomes overdue.
     */
    private function updateOverdueStatus()
    {
        try {
            $db = \Database::getConnection();
            $db->exec("UPDATE followups SET status = 'overdue'
                       WHERE status = 'upcoming' AND due_date < NOW()");
        } catch (\Exception $e) {
            // Non-fatal, continue
        }
    }

    /* ─────────────────────────────────────────
       POST /followups/store
    ───────────────────────────────────────── */
    public function store()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/leadflow/followups');
        }

        $isAjax = $this->isAjaxRequest();

        $leadId      = !empty($_POST['lead_id']) ? (int)$_POST['lead_id'] : null;
        $actType     = trim($_POST['activity_type'] ?? 'Phone Call');
        $assignedTo  = !empty($_POST['assigned_to_id']) ? (int)$_POST['assigned_to_id'] : null;
        $dueDate     = trim($_POST['due_date'] ?? '');
        $notes       = trim($_POST['notes'] ?? '');

        // Validate
        if (empty($leadId) || empty($dueDate)) {
            $err = 'Lead and Due Date are required.';
            if ($isAjax) {
                return $this->json(['success' => false, 'error' => $err]);
            }
            $_SESSION['flash_error'] = $err;
            $this->redirect('/leadflow/followups');
        }

        // Determine status based on due date
        $status = (strtotime($dueDate) < time()) ? 'overdue' : 'upcoming';

        $followUpModel = new FollowUp();
        $newId = $followUpModel->create([
            'lead_id'        => $leadId,
            'assigned_to_id' => $assignedTo ?? $_SESSION['user_id'],
            'activity_type'  => $actType,
            'due_date'       => $dueDate,
            'status'         => $status,
            'notes'          => $notes
        ]);

        if ($newId) {
            // Log activity
            $actModel = new Activity();
            $actModel->log($leadId, $_SESSION['user_id'], 'Follow-up Scheduled',
                "A {$actType} follow-up was scheduled.");

            if ($isAjax) {
                $item = $followUpModel->findById($newId);
                return $this->json(['success' => true, 'message' => 'Follow-up scheduled.', 'followup' => $item]);
            }
            $_SESSION['flash_success'] = 'Follow-up scheduled successfully.';
        } else {
            if ($isAjax) {
                return $this->json(['success' => false, 'error' => 'Database insert failed.']);
            }
            $_SESSION['flash_error'] = 'Failed to schedule follow-up.';
        }

        $this->redirect('/leadflow/followups');
    }

    /* ─────────────────────────────────────────
       POST /followups/update
    ───────────────────────────────────────── */
    public function update()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/leadflow/followups');
        }

        $isAjax = $this->isAjaxRequest();

        $id          = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $actType     = trim($_POST['activity_type'] ?? 'Phone Call');
        $assignedTo  = !empty($_POST['assigned_to_id']) ? (int)$_POST['assigned_to_id'] : null;
        $dueDate     = trim($_POST['due_date'] ?? '');
        $statusPost  = trim($_POST['status'] ?? '');
        $notes       = trim($_POST['notes'] ?? '');

        if (empty($id) || empty($dueDate)) {
            $err = 'ID and Due Date are required.';
            if ($isAjax) return $this->json(['success' => false, 'error' => $err]);
            $_SESSION['flash_error'] = $err;
            $this->redirect('/leadflow/followups');
        }

        $followUpModel = new FollowUp();
        $updated = $followUpModel->update($id, [
            'activity_type'  => $actType,
            'assigned_to_id' => $assignedTo,
            'due_date'       => $dueDate,
            'status'         => $statusPost,
            'notes'          => $notes
        ]);

        if ($updated) {
            $item = $followUpModel->findById($id);
            if ($isAjax) return $this->json(['success' => true, 'message' => 'Follow-up updated.', 'followup' => $item]);
            $_SESSION['flash_success'] = 'Follow-up updated successfully.';
        } else {
            if ($isAjax) return $this->json(['success' => false, 'error' => 'Update failed.']);
            $_SESSION['flash_error'] = 'Failed to update follow-up.';
        }

        $this->redirect('/leadflow/followups');
    }

    /* ─────────────────────────────────────────
       POST /followups/complete  (toggle)
    ───────────────────────────────────────── */
    public function complete()
    {
        $this->requireAuth();

        $isAjax = $this->isAjaxRequest();
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;

        if (empty($id)) {
            if ($isAjax) return $this->json(['success' => false, 'error' => 'ID required.']);
            $this->redirect('/leadflow/followups');
        }

        $followUpModel = new FollowUp();
        $item = $followUpModel->findById($id);
        if (!$item) {
            if ($isAjax) return $this->json(['success' => false, 'error' => 'Follow-up not found.']);
            $this->redirect('/leadflow/followups');
        }

        $result = $followUpModel->toggleComplete($id);

        if ($result) {
            $updated = $followUpModel->findById($id);
            $actModel = new Activity();
            $action = ($updated['status'] === 'completed') ? 'Follow-up Completed' : 'Follow-up Reopened';
            $actModel->log($item['lead_id'], $_SESSION['user_id'], $action, $item['activity_type'] . ' task status changed.');

            if ($isAjax) return $this->json(['success' => true, 'newStatus' => $updated['status']]);
            $_SESSION['flash_success'] = 'Follow-up status updated.';
        } else {
            if ($isAjax) return $this->json(['success' => false, 'error' => 'Toggle failed.']);
        }

        $this->redirect('/leadflow/followups');
    }

    /* ─────────────────────────────────────────
       POST /followups/delete
    ───────────────────────────────────────── */
    public function destroy()
    {
        $this->requireAuth();

        $isAjax = $this->isAjaxRequest();
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;

        if (empty($id)) {
            if ($isAjax) return $this->json(['success' => false, 'error' => 'ID required.']);
            $this->redirect('/leadflow/followups');
        }

        $followUpModel = new FollowUp();
        $deleted = $followUpModel->delete($id);

        if ($deleted) {
            if ($isAjax) return $this->json(['success' => true, 'message' => 'Follow-up deleted.']);
            $_SESSION['flash_success'] = 'Follow-up deleted.';
        } else {
            if ($isAjax) return $this->json(['success' => false, 'error' => 'Delete failed.']);
            $_SESSION['flash_error'] = 'Failed to delete follow-up.';
        }

        $this->redirect('/leadflow/followups');
    }

    /* ─────────────────────────────────────────
       GET /followups/get?id=...
    ───────────────────────────────────────── */
    public function get()
    {
        $this->requireAuth();
        $id = !empty($_GET['id']) ? (int)$_GET['id'] : null;

        if (!$id) {
            return $this->json(['success' => false, 'error' => 'ID required.']);
        }

        $followUpModel = new FollowUp();
        $item = $followUpModel->findById($id);

        if ($item) {
            return $this->json(['success' => true, 'followup' => $item]);
        }
        return $this->json(['success' => false, 'error' => 'Not found.']);
    }

    /* ─────────────────────────────────────────
       GET /followups/export
    ───────────────────────────────────────── */
    public function export()
    {
        $this->requireAuth();

        $followUpModel = new FollowUp();
        $followups = $followUpModel->all([]);

        $filename = 'LeadFlow_FollowUps_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID', 'Lead Name', 'Company', 'Activity Type', 'Due Date', 'Status', 'Assigned To', 'Notes', 'Completed At']);

        foreach ($followups as $f) {
            fputcsv($out, [
                $f['id'],
                $f['customer_name'] ?? '',
                $f['company_name'] ?? '',
                $f['activity_type'],
                $f['due_date'],
                $f['status'],
                $f['assigned_to_name'] ?? 'Unassigned',
                $f['notes'] ?? '',
                $f['completed_at'] ?? ''
            ]);
        }
        fclose($out);
        exit;
    }

    // ── Helpers ──────────────────────────────

    private function isAjaxRequest(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT'])
                && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }

    private function json(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
