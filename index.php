<?php
/**
 * Front Controller
 * Athena Dorms Property Management System
 * Main entry point for all requests
 */

// Load bootstrap configuration
require_once __DIR__ . '/config/bootstrap.php';

// Load routes
$routes = require_once __DIR__ . '/routes.php';

// Load and configure router
require_once __DIR__ . '/core/Router.php';
$router = new Router();
$router->setRoutes($routes);

// Get action from query string
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Handle default redirect for non-authenticated users
if (!isLoggedIn() && strpos($action, 'auth.') !== 0) {
    header('Location: index.php?action=auth.login');
    exit;
}

// If no action specified and user is logged in, redirect to their default page
if (empty($action) && isLoggedIn()) {
    header('Location: index.php?action=' . getDefaultPage());
    exit;
}

// Check access control for protected actions (not auth actions)
if (isLoggedIn() && strpos($action, 'auth.') !== 0 && !hasAccess($action)) {
    // Check if this is an AJAX request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Access denied. You do not have permission to perform this action.']);
        exit;
    }
    setFlashMessage('error', 'Access denied. You do not have permission to access this page.');
    header('Location: index.php?action=' . getDefaultPage());
    exit;
}

// Dispatch the request
$router->dispatch($action);
