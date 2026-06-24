<?php
/**
 * Router Class
 * Athena Dorms Property Management System
 * Handles URL routing and controller dispatch
 */

class Router
{
    private $routes = [];

    /**
     * Set routes configuration
     * @param array $routes
     */
    public function setRoutes(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * Dispatch action to appropriate controller
     * @param string $action
     */
    public function dispatch($action)
    {
        // Check if route exists
        if (!isset($this->routes[$action])) {
            http_response_code(404);
            echo $this->render404($action);
            return;
        }

        $route = $this->routes[$action];
        $controllerName = $route[0];
        $methodName = $route[1];

        // Load controller file
        $controllerFile = CONTROLLERS_PATH . '/' . $controllerName . '.php';

        if (!file_exists($controllerFile)) {
            http_response_code(500);
            error_log("Controller file not found: " . $controllerName);
            echo $this->renderError("System error. Please contact administrator.");
            return;
        }

        require_once $controllerFile;

        // Check if class exists
        if (!class_exists($controllerName)) {
            http_response_code(500);
            error_log("Controller class not found: " . $controllerName);
            echo $this->renderError("System error. Please contact administrator.");
            return;
        }

        // Instantiate controller
        $controller = new $controllerName();

        // Check if method exists
        if (!method_exists($controller, $methodName)) {
            http_response_code(500);
            error_log("Method not found: " . $controllerName . '->' . $methodName);
            echo $this->renderError("System error. Please contact administrator.");
            return;
        }

        // Call the method
        $controller->$methodName();
    }

    /**
     * Render 404 error page
     * @param string $action
     * @return string
     */
    private function render404($action)
    {
        return '<!DOCTYPE html>
        <html>
        <head>
            <title>404 - Page Not Found</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="bg-light">
            <div class="container mt-5">
                <div class="row justify-content-center">
                    <div class="col-md-6 text-center">
                        <h1 class="display-1 text-muted">404</h1>
                        <h2>Page Not Found</h2>
                        <p class="text-muted">The page you requested does not exist.</p>
                        <a href="index.php" class="btn btn-primary">Go to Dashboard</a>
                    </div>
                </div>
            </div>
        </body>
        </html>';
    }

    /**
     * Render generic error page
     * @param string $message
     * @return string
     */
    private function renderError($message)
    {
        return '<!DOCTYPE html>
        <html>
        <head>
            <title>Error</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="bg-light">
            <div class="container mt-5">
                <div class="row justify-content-center">
                    <div class="col-md-6 text-center">
                        <h1 class="display-1 text-danger">Error</h1>
                        <p class="text-muted">' . htmlspecialchars($message) . '</p>
                        <a href="index.php" class="btn btn-primary">Go to Dashboard</a>
                    </div>
                </div>
            </div>
        </body>
        </html>';
    }
}
