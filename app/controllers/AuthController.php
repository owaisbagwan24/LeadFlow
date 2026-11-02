<?php

require_once __DIR__ . '/../models/User.php';

class AuthController extends Controller
{
    public function login()
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/leadflow/dashboard');
        }

        $error = null;
        $old = ['email' => ''];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $old['email'] = $email;

            if (empty($email) || empty($password)) {
                $error = 'Please enter both email and password.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address.';
            } else {
                $userModel = new User();
                $user = $userModel->findByEmail($email);

                if (!$user || !password_verify($password, $user['password_hash'])) {
                    $error = 'Invalid email or password.';
                } elseif ($user['status'] !== 'Active') {
                    $error = 'Your account has been deactivated. Please contact an administrator.';
                } else {
                    // Regenerate session ID for security against session fixation
                    session_regenerate_id(true);

                    $_SESSION['user_id'] = (int)$user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];

                    $this->redirect('/leadflow/dashboard');
                }
            }
        }

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $this->view('auth/login', [
            'error' => $error ?? $flashError,
            'success' => $flashSuccess,
            'old' => $old
        ]);
    }

    public function register()
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/leadflow/dashboard');
        }

        $error = null;
        $old = [
            'name' => '',
            'email' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['password_confirmation'] ?? '';
            $terms = isset($_POST['terms']);

            $old['name'] = $name;
            $old['email'] = $email;

            if (empty($name)) {
                $error = 'Please enter your full name.';
            } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please provide a valid work email address.';
            } elseif (strlen($password) < 8) {
                $error = 'Password must be at least 8 characters long.';
            } elseif ($password !== $confirmPassword) {
                $error = 'Passwords do not match.';
            } elseif (!$terms) {
                $error = 'You must accept the Terms of Service to create an account.';
            } else {
                $userModel = new User();
                $existing = $userModel->findByEmail($email);

                if ($existing) {
                    $error = 'An account with this email address already exists. Please sign in instead.';
                } else {
                    $created = $userModel->create($name, $email, $password, 'Employee', 'Active');

                    if ($created) {
                        $_SESSION['flash_success'] = 'Account registered successfully! You may now sign in.';
                        $this->redirect('/leadflow/login');
                    } else {
                        $error = 'Failed to create your account due to a server error. Please try again.';
                    }
                }
            }
        }

        $this->view('auth/register', [
            'error' => $error,
            'old' => $old
        ]);
    }

    public function logout()
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        session_start();
        $_SESSION['flash_success'] = 'You have been signed out successfully.';
        $this->redirect('/leadflow/login');
    }
}
