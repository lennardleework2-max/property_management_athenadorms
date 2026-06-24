<?php
/**
 * Bootstrap Configuration
 * Athena Dorms Property Management System
 * Loads all required configurations
 */

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1); // Set to 1 for debugging
ini_set('log_errors', 1);

// Define base path
define('BASE_PATH', dirname(__DIR__));
define('CONFIG_PATH', BASE_PATH . '/config');
define('CONTROLLERS_PATH', BASE_PATH . '/controllers');
define('MODELS_PATH', BASE_PATH . '/models');
define('VIEWS_PATH', BASE_PATH . '/views');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('UPLOADS_PATH', BASE_PATH . '/uploads');

// Define base URL (adjust if needed)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$script = dirname($_SERVER['SCRIPT_NAME']);
define('BASE_URL', $protocol . '://' . $host . $script);

// Load configurations
require_once CONFIG_PATH . '/database.php';
require_once CONFIG_PATH . '/session.php';

/**
 * Simple autoloader for models
 */
spl_autoload_register(function ($class) {
    // Check in models directory
    $modelFile = MODELS_PATH . '/' . $class . '.php';
    if (file_exists($modelFile)) {
        require_once $modelFile;
        return;
    }

    // Check in controllers directory
    $controllerFile = CONTROLLERS_PATH . '/' . $class . '.php';
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        return;
    }

    // Check in core directory
    $coreFile = BASE_PATH . '/core/' . $class . '.php';
    if (file_exists($coreFile)) {
        require_once $coreFile;
        return;
    }
});

/**
 * Helper function to render views
 * @param string $view View file path relative to views folder
 * @param array $data Data to pass to view
 */
function renderView($view, $data = [])
{
    extract($data);
    $viewFile = VIEWS_PATH . '/' . $view . '.php';

    if (!file_exists($viewFile)) {
        throw new Exception("View not found: " . $view);
    }

    require $viewFile;
}

/**
 * Helper function to render views with layout
 * @param string $view View file path relative to views folder
 * @param array $data Data to pass to view
 * @param string $pageTitle Page title
 */
function renderWithLayout($view, $data = [], $pageTitle = 'Athena Dorms')
{
    $data['pageTitle'] = $pageTitle;
    $data['currentUser'] = getCurrentUser();

    // Start output buffering to capture view content
    ob_start();
    extract($data);
    require VIEWS_PATH . '/' . $view . '.php';
    $content = ob_get_clean();

    // Render with layout
    require VIEWS_PATH . '/layouts/header.php';
    require VIEWS_PATH . '/layouts/sidebar.php';
    echo '<main class="main-content">';
    echo $content;
    echo '</main>';
    require VIEWS_PATH . '/layouts/footer.php';
}

/**
 * Send JSON response
 * @param array $data
 * @param int $statusCode
 */
function jsonResponse($data, $statusCode = 200)
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Sanitize output for display
 * @param string $str
 * @return string
 */
function e($str)
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Format currency
 * @param float $amount
 * @return string
 */
function formatCurrency($amount)
{
    return '₱' . number_format((float)$amount, 2);
}

/**
 * Format date
 * @param string $date
 * @param string $format
 * @return string
 */
function formatDate($date, $format = 'M d, Y')
{
    if (empty($date)) {
        return '-';
    }
    return date($format, strtotime($date));
}

/**
 * Generate next ID in format 001, 002, etc.
 * @param int $currentMax
 * @return string
 */
function generateNextId($currentMax)
{
    return str_pad($currentMax + 1, 3, '0', STR_PAD_LEFT);
}
