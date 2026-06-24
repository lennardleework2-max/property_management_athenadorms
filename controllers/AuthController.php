<?php
/**
 * Auth Controller
 * Athena Dorms Property Management System
 * Handles user authentication
 */

class AuthController
{
    private $userModel;

    public function __construct()
    {
        require_once MODELS_PATH . '/UserModel.php';
        $this->userModel = new UserModel();
    }

    /**
     * Show login page
     */
    public function showLogin()
    {
        // Redirect to appropriate page if already logged in
        if (isLoggedIn()) {
            header('Location: index.php?action=' . getDefaultPage());
            exit;
        }

        $error = '';
        $flash = getFlashMessage();
        if ($flash && $flash['type'] === 'error') {
            $error = $flash['message'];
        }

        renderView('login', ['error' => $error]);
    }

    /**
     * Process login
     */
    public function login()
    {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            setFlashMessage('error', 'Invalid request. Please try again.');
            header('Location: index.php?action=auth.login');
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validate input
        if (empty($email) || empty($password)) {
            setFlashMessage('error', 'Email and password are required.');
            header('Location: index.php?action=auth.login');
            exit;
        }

        // Find user by email
        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            setFlashMessage('error', 'Invalid email or password.');
            header('Location: index.php?action=auth.login');
            exit;
        }

        // Verify password
        if (!$this->userModel->verifyPassword($password, $user['password_hash'])) {
            setFlashMessage('error', 'Invalid email or password.');
            header('Location: index.php?action=auth.login');
            exit;
        }

        // Check if user is active
        if ($user['user_status'] !== 'active') {
            setFlashMessage('error', 'Your account is inactive. Please contact administrator.');
            header('Location: index.php?action=auth.login');
            exit;
        }

        // Set session
        $_SESSION['user_recid'] = $user['recid'];
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['user_role'] = $user['user_role'];

        // Regenerate session ID for security
        session_regenerate_id(true);

        // Redirect to appropriate page based on role
        header('Location: index.php?action=' . getDefaultPage());
        exit;
    }

    /**
     * Logout user
     */
    public function logout()
    {
        // Clear session
        $_SESSION = [];

        // Destroy session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Destroy session
        session_destroy();

        // Redirect to login
        header('Location: index.php?action=auth.login');
        exit;
    }
}
