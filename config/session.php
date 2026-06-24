<?php
/**
 * Session Configuration
 * Athena Dorms Property Management System
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Session security settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS

    session_start();
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user data
 * @return array|null
 */
function getCurrentUser()
{
    if (!isLoggedIn()) {
        return null;
    }

    return [
        'recid' => $_SESSION['user_recid'],
        'user_id' => $_SESSION['user_id'],
        'full_name' => $_SESSION['full_name'],
        'email' => $_SESSION['email'],
        'user_role' => $_SESSION['user_role']
    ];
}

/**
 * Require user to be logged in
 * Redirects to login if not authenticated
 */
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: index.php?action=auth.login');
        exit;
    }
}

/**
 * Generate CSRF token
 * @return string
 */
function generateCsrfToken()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * @param string $token
 * @return bool
 */
function validateCsrfToken($token)
{
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF token input field HTML
 * @return string
 */
function csrfField()
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCsrfToken()) . '">';
}

/**
 * Get CSRF token value
 * @return string
 */
function getCsrfToken()
{
    return generateCsrfToken();
}

/**
 * Set flash message
 * @param string $type success|error|warning|info
 * @param string $message
 */
function setFlashMessage($type, $message)
{
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 * @return array|null
 */
function getFlashMessage()
{
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Role-based access control definitions
 * Owner: Full access to everything
 * Admin: Full access to everything
 * Staff: Limited access (no dashboard, no master file management)
 */
function getRolePermissions()
{
    return [
        'owner' => [
            'dashboard' => true,
            'tenant.list' => true, 'tenant.get' => true, 'tenant.get.all' => true,
            'tenant.add' => true, 'tenant.edit' => true, 'tenant.delete' => true, 'tenant.generate.id' => true,
            'property.list' => true, 'property.get' => true, 'property.get.all' => true,
            'property.add' => true, 'property.edit' => true, 'property.delete' => true, 'property.generate.id' => true,
            'room.list' => true, 'room.get' => true, 'room.get.all' => true, 'room.get.by.property' => true,
            'room.add' => true, 'room.edit' => true, 'room.delete' => true, 'room.generate.id' => true,
            'bedspace.list' => true, 'bedspace.get' => true, 'bedspace.get.all' => true, 'bedspace.get.by.room' => true,
            'bedspace.add' => true, 'bedspace.edit' => true, 'bedspace.delete' => true, 'bedspace.generate.id' => true,
            'lease.list' => true, 'lease.get' => true, 'lease.get.all' => true,
            'lease.add' => true, 'lease.edit' => true, 'lease.delete' => true, 'lease.generate.id' => true,
            'payment.list' => true, 'payment.get' => true, 'payment.get.all' => true,
            'payment.add' => true, 'payment.edit' => true, 'payment.delete' => true,
            'payment.verify' => true, 'payment.reject' => true, 'payment.generate.id' => true, 'payment.upload.proof' => true,
            'utility.list' => true, 'utility.get' => true, 'utility.get.all' => true,
            'utility.add' => true, 'utility.edit' => true, 'utility.delete' => true, 'utility.generate.id' => true, 'utility.allocate' => true,
            'report.index' => true, 'report.tenant.balance' => true, 'report.pending.payment' => true,
            'report.overdue' => true, 'report.utility' => true, 'report.expiring.contract' => true,
            'useraccess.list' => true, 'useraccess.update' => true,
        ],
        'admin' => [
            'dashboard' => true,
            'tenant.list' => true, 'tenant.get' => true, 'tenant.get.all' => true,
            'tenant.add' => true, 'tenant.edit' => true, 'tenant.delete' => true, 'tenant.generate.id' => true,
            'property.list' => true, 'property.get' => true, 'property.get.all' => true,
            'property.add' => true, 'property.edit' => true, 'property.delete' => true, 'property.generate.id' => true,
            'room.list' => true, 'room.get' => true, 'room.get.all' => true, 'room.get.by.property' => true,
            'room.add' => true, 'room.edit' => true, 'room.delete' => true, 'room.generate.id' => true,
            'bedspace.list' => true, 'bedspace.get' => true, 'bedspace.get.all' => true, 'bedspace.get.by.room' => true,
            'bedspace.add' => true, 'bedspace.edit' => true, 'bedspace.delete' => true, 'bedspace.generate.id' => true,
            'lease.list' => true, 'lease.get' => true, 'lease.get.all' => true,
            'lease.add' => true, 'lease.edit' => true, 'lease.delete' => true, 'lease.generate.id' => true,
            'payment.list' => true, 'payment.get' => true, 'payment.get.all' => true,
            'payment.add' => true, 'payment.edit' => true, 'payment.delete' => true,
            'payment.verify' => true, 'payment.reject' => true, 'payment.generate.id' => true, 'payment.upload.proof' => true,
            'utility.list' => true, 'utility.get' => true, 'utility.get.all' => true,
            'utility.add' => true, 'utility.edit' => true, 'utility.delete' => true, 'utility.generate.id' => true, 'utility.allocate' => true,
            'report.index' => true, 'report.tenant.balance' => true, 'report.pending.payment' => true,
            'report.overdue' => true, 'report.utility' => true, 'report.expiring.contract' => true,
            'useraccess.list' => true, 'useraccess.update' => true,
        ],
        'staff' => [
            // NO Dashboard access
            'dashboard' => false,
            // Tenants - view and manage
            'tenant.list' => true, 'tenant.get' => true, 'tenant.get.all' => true,
            'tenant.add' => true, 'tenant.edit' => true, 'tenant.delete' => false, 'tenant.generate.id' => true,
            // Properties - view only (no add/edit/delete)
            'property.list' => true, 'property.get' => true, 'property.get.all' => true,
            'property.add' => false, 'property.edit' => false, 'property.delete' => false, 'property.generate.id' => false,
            // Rooms - view only (no add/edit/delete)
            'room.list' => true, 'room.get' => true, 'room.get.all' => true, 'room.get.by.property' => true,
            'room.add' => false, 'room.edit' => false, 'room.delete' => false, 'room.generate.id' => false,
            // Bedspaces - view only (no add/edit/delete)
            'bedspace.list' => true, 'bedspace.get' => true, 'bedspace.get.all' => true, 'bedspace.get.by.room' => true,
            'bedspace.add' => false, 'bedspace.edit' => false, 'bedspace.delete' => false, 'bedspace.generate.id' => false,
            // Leases - full access
            'lease.list' => true, 'lease.get' => true, 'lease.get.all' => true,
            'lease.add' => true, 'lease.edit' => true, 'lease.delete' => false, 'lease.generate.id' => true,
            // Payments - full access
            'payment.list' => true, 'payment.get' => true, 'payment.get.all' => true,
            'payment.add' => true, 'payment.edit' => true, 'payment.delete' => false,
            'payment.verify' => true, 'payment.reject' => true, 'payment.generate.id' => true, 'payment.upload.proof' => true,
            // Utilities - full access
            'utility.list' => true, 'utility.get' => true, 'utility.get.all' => true,
            'utility.add' => true, 'utility.edit' => true, 'utility.delete' => false, 'utility.generate.id' => true, 'utility.allocate' => true,
            // Reports - full access
            'report.index' => true, 'report.tenant.balance' => true, 'report.pending.payment' => true,
            'report.overdue' => true, 'report.utility' => true, 'report.expiring.contract' => true,
        ],
    ];
}

/**
 * Check if current user has access to a specific action
 * @param string $action The action to check
 * @return bool
 */
function hasAccess($action)
{
    if (!isLoggedIn()) {
        return false;
    }

    $userRole = strtolower($_SESSION['user_role'] ?? 'staff');
    $permissions = getRolePermissions();

    // If role not defined, deny access
    if (!isset($permissions[$userRole])) {
        return false;
    }

    // If action not defined for role, deny access
    if (!isset($permissions[$userRole][$action])) {
        return false;
    }

    return $permissions[$userRole][$action];
}

/**
 * Require user to have access to a specific action
 * Redirects or returns error if not authorized
 * @param string $action The action to check
 */
function requireAccess($action)
{
    requireLogin();

    if (!hasAccess($action)) {
        // Check if it's an AJAX request
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Access denied. You do not have permission to perform this action.']);
            exit;
        }

        // For regular requests, redirect to appropriate page
        setFlashMessage('error', 'Access denied. You do not have permission to access this page.');

        // Redirect staff to tenant list (their default page)
        $userRole = strtolower($_SESSION['user_role'] ?? 'staff');
        if ($userRole === 'staff') {
            header('Location: index.php?action=tenant.list');
        } else {
            header('Location: index.php?action=dashboard');
        }
        exit;
    }
}

/**
 * Get default landing page for user role
 * @return string
 */
function getDefaultPage()
{
    $userRole = strtolower($_SESSION['user_role'] ?? 'staff');

    if ($userRole === 'staff') {
        return 'tenant.list';
    }

    return 'dashboard';
}
