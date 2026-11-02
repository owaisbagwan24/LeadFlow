<?php

require_once __DIR__ . '/../models/User.php';

class UserController extends Controller
{
    /* ─────────────────────────────────────────
       GET /users — Admin only
    ───────────────────────────────────────── */
    public function index()
    {
        $this->requireAdmin();

        $userModel = new User();
        $usersList = $userModel->all();

        // Live stats
        $totalUsers  = count($usersList);
        $adminCount  = count(array_filter($usersList, fn($u) => $u['role'] === 'Admin'));
        $empCount    = $totalUsers - $adminCount;
        $activeCount = count(array_filter($usersList, fn($u) => $u['status'] === 'Active'));

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError   = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $this->renderView('users/index', [
            'pageTitle'          => 'Users & Team Access — LeadFlow',
            'activePage'         => 'users',
            'breadcrumbCategory' => 'Organization',
            'breadcrumbPage'     => 'Users & Team',
            'usersList'          => $usersList,
            'totalUsers'         => $totalUsers,
            'adminCount'         => $adminCount,
            'empCount'           => $empCount,
            'activeCount'        => $activeCount,
            'flashSuccess'       => $flashSuccess,
            'flashError'         => $flashError,
        ]);
    }

    /* ─────────────────────────────────────────
       POST /users/store — Admin only
    ───────────────────────────────────────── */
    public function store()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/leadflow/users');
        }

        $isAjax   = $this->isAjaxRequest();
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = in_array($_POST['role'] ?? '', ['Admin','Employee']) ? $_POST['role'] : 'Employee';

        // Validate
        $errors = [];
        if (empty($name))                        $errors[] = 'Full name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
        if (strlen($password) < 8)               $errors[] = 'Password must be at least 8 characters.';

        if (!empty($errors)) {
            $msg = implode(' ', $errors);
            if ($isAjax) return $this->json(['success' => false, 'error' => $msg]);
            $_SESSION['flash_error'] = $msg;
            $this->redirect('/leadflow/users');
        }

        $userModel = new User();

        // Check duplicate email
        if ($userModel->findByEmail($email)) {
            $msg = 'A user with this email already exists.';
            if ($isAjax) return $this->json(['success' => false, 'error' => $msg]);
            $_SESSION['flash_error'] = $msg;
            $this->redirect('/leadflow/users');
        }

        $newId = $userModel->create($name, $email, $password, $role, 'Active');

        if ($newId) {
            $newUser = $userModel->findById($newId);
            if ($isAjax) return $this->json(['success' => true, 'message' => "User {$name} created.", 'user' => $newUser]);
            $_SESSION['flash_success'] = "User {$name} was added successfully.";
        } else {
            if ($isAjax) return $this->json(['success' => false, 'error' => 'Database insert failed.']);
            $_SESSION['flash_error'] = 'Failed to create user.';
        }

        $this->redirect('/leadflow/users');
    }

    /* ─────────────────────────────────────────
       POST /users/update — Admin only
       Updates name, email, role
    ───────────────────────────────────────── */
    public function update()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/leadflow/users');
        }

        $isAjax = $this->isAjaxRequest();
        $id     = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $name   = trim($_POST['name'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $role   = in_array($_POST['role'] ?? '', ['Admin','Employee']) ? $_POST['role'] : 'Employee';

        if (!$id || empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $msg = 'Valid ID, name and email are required.';
            if ($isAjax) return $this->json(['success' => false, 'error' => $msg]);
            $_SESSION['flash_error'] = $msg;
            $this->redirect('/leadflow/users');
        }

        $userModel = new User();
        $userModel->updateProfile($id, $name, $email);
        $userModel->updateRole($id, $role);

        $updated = $userModel->findById($id);

        if ($isAjax) return $this->json(['success' => true, 'message' => 'User updated.', 'user' => $updated]);
        $_SESSION['flash_success'] = 'User updated successfully.';
        $this->redirect('/leadflow/users');
    }

    /* ─────────────────────────────────────────
       POST /users/toggle-status — Admin only
    ───────────────────────────────────────── */
    public function toggleStatus()
    {
        $this->requireAdmin();

        $isAjax = $this->isAjaxRequest();
        $id     = !empty($_POST['id']) ? (int)$_POST['id'] : null;

        // Prevent admin from deactivating their own account
        if ($id === (int)($_SESSION['user_id'] ?? 0)) {
            $msg = 'You cannot deactivate your own account.';
            if ($isAjax) return $this->json(['success' => false, 'error' => $msg]);
            $_SESSION['flash_error'] = $msg;
            $this->redirect('/leadflow/users');
        }

        $userModel = new User();
        $user = $userModel->findById($id);
        if (!$user) {
            if ($isAjax) return $this->json(['success' => false, 'error' => 'User not found.']);
            $this->redirect('/leadflow/users');
        }

        $newStatus = ($user['status'] === 'Active') ? 'Inactive' : 'Active';
        $userModel->updateStatus($id, $newStatus);

        if ($isAjax) return $this->json(['success' => true, 'newStatus' => $newStatus]);
        $_SESSION['flash_success'] = "{$user['name']}'s account is now {$newStatus}.";
        $this->redirect('/leadflow/users');
    }

    /* ─────────────────────────────────────────
       POST /users/delete — Admin only
    ───────────────────────────────────────── */
    public function destroy()
    {
        $this->requireAdmin();

        $isAjax = $this->isAjaxRequest();
        $id     = !empty($_POST['id']) ? (int)$_POST['id'] : null;

        // Prevent self-deletion
        if ($id === (int)($_SESSION['user_id'] ?? 0)) {
            $msg = 'You cannot delete your own account.';
            if ($isAjax) return $this->json(['success' => false, 'error' => $msg]);
            $_SESSION['flash_error'] = $msg;
            $this->redirect('/leadflow/users');
        }

        $userModel = new User();
        $user = $userModel->findById($id);

        if (!$user) {
            if ($isAjax) return $this->json(['success' => false, 'error' => 'User not found.']);
            $this->redirect('/leadflow/users');
        }

        $deleted = $userModel->delete($id);

        if ($deleted) {
            if ($isAjax) return $this->json(['success' => true, 'message' => "User {$user['name']} deleted."]);
            $_SESSION['flash_success'] = "User {$user['name']} has been deleted.";
        } else {
            if ($isAjax) return $this->json(['success' => false, 'error' => 'Delete failed.']);
            $_SESSION['flash_error'] = 'Failed to delete user.';
        }

        $this->redirect('/leadflow/users');
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
