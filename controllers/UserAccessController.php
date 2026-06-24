<?php
/**
 * User Access Controller
 * Athena Dorms Property Management System
 * Handles user access permissions management
 */

class UserAccessController
{
    private $db;

    public function __construct()
    {
        $this->db = getDbConnection();
    }

    /**
     * List all users with their access permissions
     */
    public function list()
    {
        // Get all users with access summary
        $sql = "SELECT * FROM view_user_access_summary";
        $stmt = $this->db->query($sql);
        $users = $stmt->fetchAll();

        // Define modules
        $modules = [
            'dashboard' => 'Dashboard',
            'properties' => 'Properties',
            'rooms' => 'Units',
            'tenants' => 'Tenants',
            'contracts' => 'Contracts',
            'bills' => 'Bills',
            'staff' => 'Staff',
            'utilities' => 'Water & Electric',
            'user_access' => 'User Access'
        ];

        renderWithLayout('user_access/list', [
            'users' => $users,
            'modules' => $modules
        ], 'User Access Control');
    }

    /**
     * Update user access permissions (AJAX)
     */
    public function updateAccess()
    {
        header('Content-Type: application/json');

        // Validate CSRF
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        $userRecid = (int)($_POST['user_recid'] ?? 0);
        $module = trim($_POST['module'] ?? '');
        $hasAccess = $_POST['has_access'] === 'true' || $_POST['has_access'] === '1';

        if (!$userRecid || empty($module)) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            exit;
        }

        try {
            // Upsert the access permission
            $sql = "INSERT INTO utl_user_access (user_recid, module_name, has_access, date_updated)
                    VALUES (:user_recid, :module, :has_access, NOW())
                    ON CONFLICT (user_recid, module_name)
                    DO UPDATE SET has_access = :has_access, date_updated = NOW()";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'user_recid' => $userRecid,
                'module' => $module,
                'has_access' => $hasAccess ? 'true' : 'false'
            ]);

            echo json_encode(['success' => true, 'message' => 'Access updated']);
        } catch (Exception $e) {
            error_log("Error updating access: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
        exit;
    }

    /**
     * Get role label based on permissions
     */
    private function getRoleLabel($user)
    {
        // Count permissions
        $accessCount = 0;
        $accessCount += $user['access_dashboard'] ? 1 : 0;
        $accessCount += $user['access_properties'] ? 1 : 0;
        $accessCount += $user['access_rooms'] ? 1 : 0;
        $accessCount += $user['access_tenants'] ? 1 : 0;
        $accessCount += $user['access_contracts'] ? 1 : 0;
        $accessCount += $user['access_bills'] ? 1 : 0;
        $accessCount += $user['access_staff'] ? 1 : 0;
        $accessCount += $user['access_utilities'] ? 1 : 0;
        $accessCount += $user['access_user_access'] ? 1 : 0;

        if ($accessCount >= 9) return 'Admin';
        if ($accessCount >= 6) return 'Property Manager';
        if ($accessCount >= 4) return 'Billing Staff';
        return 'Viewer';
    }
}
