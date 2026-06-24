<?php
/**
 * Database Configuration
 * Athena Dorms Property Management System
 * Supabase PostgreSQL Connection
 */

// Use environment variables if available (for Vercel), otherwise use defaults
define('DB_HOST', getenv('DB_HOST') ?: 'aws-1-ap-northeast-1.pooler.supabase.com');
define('DB_PORT', getenv('DB_PORT') ?: '5432');
define('DB_NAME', getenv('DB_NAME') ?: 'postgres');
define('DB_USER', getenv('DB_USER') ?: 'postgres.adqraqouelwxtbicnenu');
define('DB_PASS', getenv('DB_PASS') ?: 'property_management_ruth10');

/**
 * Get PDO Database Connection
 * @return PDO
 */
function getDbConnection()
{
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";sslmode=require";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

        } catch (PDOException $e) {
            // Log error and show details for debugging
            error_log("Database Connection Error: " . $e->getMessage());

            // Check if pgsql extension is loaded
            if (!extension_loaded('pdo_pgsql')) {
                die("Database connection failed: PostgreSQL PDO extension (pdo_pgsql) is not loaded. Please enable it in php.ini and restart Apache.");
            }

            // Show actual error for debugging (remove in production)
            die("Database connection failed: " . $e->getMessage());
        }
    }

    return $pdo;
}
