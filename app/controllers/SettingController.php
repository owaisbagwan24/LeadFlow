<?php

require_once __DIR__ . '/../models/User.php';

class SettingController extends Controller
{
    /* ─────────────────────────────────────────
       GET /settings
    ───────────────────────────────────────── */
    public function index()
    {
        $this->requireAuth();

        $userModel = new User();
        $user = $userModel->findById($_SESSION['user_id']);

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError   = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $this->renderView('settings/index', [
            'pageTitle'          => 'Settings & Profile — LeadFlow',
            'activePage'         => 'settings',
            'breadcrumbCategory' => 'Workspace',
            'breadcrumbPage'     => 'Settings',
            'user'               => $user,
            'flashSuccess'       => $flashSuccess,
            'flashError'         => $flashError,
        ]);
    }

    /* ─────────────────────────────────────────
       POST /settings/profile
    ───────────────────────────────────────── */
    public function updateProfile()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/leadflow/settings');
        }

        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Valid name and email are required.';
            $this->redirect('/leadflow/settings');
        }

        $userModel = new User();

        // Check email conflict (another user with same email)
        $existing = $userModel->findByEmail($email);
        if ($existing && (int)$existing['id'] !== (int)$_SESSION['user_id']) {
            $_SESSION['flash_error'] = 'This email is already used by another account.';
            $this->redirect('/leadflow/settings');
        }

        $userModel->updateProfile($_SESSION['user_id'], $name, $email);

        // Update session
        $_SESSION['user_name']  = $name;
        $_SESSION['user_email'] = $email;

        $_SESSION['flash_success'] = 'Profile updated successfully.';
        $this->redirect('/leadflow/settings');
    }

    /* ─────────────────────────────────────────
       POST /settings/password
    ───────────────────────────────────────── */
    public function updatePassword()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/leadflow/settings');
        }

        $currentPassword     = $_POST['current_password'] ?? '';
        $newPassword         = $_POST['new_password'] ?? '';
        $newPasswordConfirm  = $_POST['new_password_confirmation'] ?? '';

        $userModel = new User();
        $user      = $userModel->findById($_SESSION['user_id']);

        if (!password_verify($currentPassword, $user['password_hash'])) {
            $_SESSION['flash_error'] = 'Current password is incorrect.';
            $this->redirect('/leadflow/settings');
        }

        if (strlen($newPassword) < 8) {
            $_SESSION['flash_error'] = 'New password must be at least 8 characters.';
            $this->redirect('/leadflow/settings');
        }

        if ($newPassword !== $newPasswordConfirm) {
            $_SESSION['flash_error'] = 'New passwords do not match.';
            $this->redirect('/leadflow/settings');
        }

        $userModel->updatePassword($_SESSION['user_id'], $newPassword);

        $_SESSION['flash_success'] = 'Password changed successfully. Please log in again.';
        // Force re-login for security
        session_destroy();
        header('Location: /leadflow/login');
        exit;
    }
}
