<?php
/**
 * Tenant Controller
 * Athena Dorms Property Management System
 * Handles tenant CRUD operations
 */

class TenantController
{
    private $tenantModel;

    public function __construct()
    {
        require_once MODELS_PATH . '/TenantModel.php';
        $this->tenantModel = new TenantModel();
    }

    /**
     * Display tenant list
     */
    public function list()
    {
        requireLogin();

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $status = isset($_GET['status']) ? trim($_GET['status']) : '';

        $tenants = $this->tenantModel->getAll($search, $status);

        renderWithLayout('tenant/list', [
            'tenants' => $tenants,
            'search' => $search,
            'status' => $status
        ], 'Tenants');
    }

    /**
     * Get single tenant (AJAX)
     */
    public function get()
    {
        requireLogin();

        $recid = isset($_POST['recid']) ? (int)$_POST['recid'] : 0;

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        $tenant = $this->tenantModel->getById($recid);

        if (!$tenant) {
            jsonResponse(['success' => false, 'message' => 'Tenant not found.'], 404);
        }

        jsonResponse(['success' => true, 'data' => $tenant]);
    }

    /**
     * Get all tenants (AJAX)
     */
    public function getAll()
    {
        requireLogin();

        $search = isset($_POST['search']) ? trim($_POST['search']) : '';
        $status = isset($_POST['status']) ? trim($_POST['status']) : '';

        $tenants = $this->tenantModel->getAll($search, $status);

        jsonResponse(['success' => true, 'data' => $tenants]);
    }

    /**
     * Add tenant (AJAX)
     */
    public function add()
    {
        requireLogin();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        // Validate required fields
        if (empty($_POST['tenant_name'])) {
            jsonResponse(['success' => false, 'message' => 'Tenant name is required.']);
        }

        // Generate next ID
        $tenantId = $this->tenantModel->getNextId();

        try {
            $result = $this->tenantModel->add([
                'tenant_id' => $tenantId,
                'tenant_name' => trim($_POST['tenant_name']),
                'phone_no' => trim($_POST['phone_no'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'emergency_contact_name' => trim($_POST['emergency_contact_name'] ?? ''),
                'emergency_contact_no' => trim($_POST['emergency_contact_no'] ?? ''),
                'tenant_status' => $_POST['tenant_status'] ?? 'active',
                'move_in_date' => $_POST['move_in_date'] ?? null,
                'move_out_date' => $_POST['move_out_date'] ?? null,
                'remarks' => trim($_POST['remarks'] ?? '')
            ]);

            if ($result) {
                jsonResponse(['success' => true, 'message' => 'Tenant added successfully.']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Failed to add tenant.']);
            }
        } catch (Exception $e) {
            error_log("Tenant add error: " . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to add tenant.']);
        }
    }

    /**
     * Edit tenant (AJAX)
     */
    public function edit()
    {
        requireLogin();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        $recid = isset($_POST['recid']) ? (int)$_POST['recid'] : 0;

        if (!$recid) {
            jsonResponse(['success' => false, 'message' => 'Invalid tenant.']);
        }

        // Validate required fields
        if (empty($_POST['tenant_name'])) {
            jsonResponse(['success' => false, 'message' => 'Tenant name is required.']);
        }

        try {
            $result = $this->tenantModel->update($recid, [
                'tenant_name' => trim($_POST['tenant_name']),
                'phone_no' => trim($_POST['phone_no'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'emergency_contact_name' => trim($_POST['emergency_contact_name'] ?? ''),
                'emergency_contact_no' => trim($_POST['emergency_contact_no'] ?? ''),
                'tenant_status' => $_POST['tenant_status'] ?? 'active',
                'move_in_date' => $_POST['move_in_date'] ?? null,
                'move_out_date' => $_POST['move_out_date'] ?? null,
                'remarks' => trim($_POST['remarks'] ?? '')
            ]);

            if ($result) {
                jsonResponse(['success' => true, 'message' => 'Tenant updated successfully.']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Failed to update tenant.']);
            }
        } catch (Exception $e) {
            error_log("Tenant update error: " . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to update tenant.']);
        }
    }

    /**
     * Delete tenant (AJAX)
     */
    public function delete()
    {
        requireLogin();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        $recid = isset($_POST['recid']) ? (int)$_POST['recid'] : 0;

        if (!$recid) {
            jsonResponse(['success' => false, 'message' => 'Invalid tenant.']);
        }

        try {
            $result = $this->tenantModel->delete($recid);

            if ($result) {
                jsonResponse(['success' => true, 'message' => 'Tenant deleted successfully.']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Failed to delete tenant.']);
            }
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Generate next ID (AJAX)
     */
    public function generateId()
    {
        requireLogin();

        $nextId = $this->tenantModel->getNextId();

        jsonResponse(['success' => true, 'data' => ['tenant_id' => $nextId]]);
    }
}
