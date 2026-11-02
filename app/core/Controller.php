<?php

require_once __DIR__ . '/Database.php';

class Controller
{
    protected function view($view, $data = [])
    {
        extract($data);

        $viewPath = __DIR__ . '/../views/' . $view . '.php';

        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            die("View not found: " . htmlspecialchars($view));
        }
    }

    protected function renderView($view, $data = [])
    {
        if (!isset($data['user'])) {
            $data['user'] = $this->currentUser();
        }

        if (!isset($data['sidebarLeadCount'])) {
            try {
                $db = Database::getConnection();
                $data['sidebarLeadCount'] = (int)$db->query("SELECT COUNT(*) FROM leads")->fetchColumn();
                $data['sidebarFollowupCount'] = (int)$db->query("SELECT COUNT(*) FROM followups WHERE status != 'completed'")->fetchColumn();
            } catch (Exception $e) {
                $data['sidebarLeadCount'] = 0;
                $data['sidebarFollowupCount'] = 0;
            }
        }

        extract($data);

        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/layouts/sidebar.php';
        require __DIR__ . '/../views/layouts/topbar.php';
        require __DIR__ . '/../views/' . $view . '.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }

    protected function redirect($url)
    {
        header("Location: " . $url);
        exit;
    }

    protected function isLoggedIn()
    {
        return !empty($_SESSION['user_id']);
    }

    protected function requireAuth()
    {
        if (!$this->isLoggedIn()) {
            $_SESSION['flash_error'] = 'Please sign in to access this page.';
            $this->redirect('/leadflow/login');
        }
    }

    protected function requireAdmin()
    {
        $this->requireAuth();
        if (($_SESSION['user_role'] ?? '') !== 'Admin') {
            http_response_code(403);
            die('Access Denied: Administrator role required.');
        }
    }

    protected function currentUser()
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'] ?? null,
            'name' => $_SESSION['user_name'] ?? 'User',
            'email' => $_SESSION['user_email'] ?? '',
            'role' => $_SESSION['user_role'] ?? 'Employee'
        ];
    }
}
