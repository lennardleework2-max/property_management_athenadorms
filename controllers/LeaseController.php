<?php
/**
 * Lease Controller
 * Athena Dorms Property Management System
 * Handles lease/contract CRUD operations
 */

class LeaseController
{
    private $leaseModel;
    private $tenantModel;
    private $propertyModel;
    private $roomModel;
    private $bedspaceModel;

    public function __construct()
    {
        require_once MODELS_PATH . '/LeaseModel.php';
        require_once MODELS_PATH . '/TenantModel.php';
        require_once MODELS_PATH . '/PropertyModel.php';
        require_once MODELS_PATH . '/RoomModel.php';
        require_once MODELS_PATH . '/BedspaceModel.php';

        $this->leaseModel = new LeaseModel();
        $this->tenantModel = new TenantModel();
        $this->propertyModel = new PropertyModel();
        $this->roomModel = new RoomModel();
        $this->bedspaceModel = new BedspaceModel();
    }

    /**
     * Display lease list
     */
    public function list()
    {
        requireLogin();

        $status = isset($_GET['status']) ? trim($_GET['status']) : '';

        $leases = $this->leaseModel->getAll($status);
        $tenants = $this->tenantModel->getActiveTenants();
        $properties = $this->propertyModel->getActiveProperties();

        renderWithLayout('lease/list', [
            'leases' => $leases,
            'tenants' => $tenants,
            'properties' => $properties,
            'status' => $status
        ], 'Leases & Contracts');
    }

    /**
     * Get single lease (AJAX)
     */
    public function get()
    {
        requireLogin();

        $recid = isset($_POST['recid']) ? (int)$_POST['recid'] : 0;

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        $lease = $this->leaseModel->getById($recid);

        if (!$lease) {
            jsonResponse(['success' => false, 'message' => 'Lease not found.'], 404);
        }

        jsonResponse(['success' => true, 'data' => $lease]);
    }

    /**
     * Get all active leases (AJAX)
     */
    public function getAll()
    {
        requireLogin();

        $leases = $this->leaseModel->getActiveLeases();

        jsonResponse(['success' => true, 'data' => $leases]);
    }

    /**
     * Add lease (AJAX)
     */
    public function add()
    {
        requireLogin();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        if (empty($_POST['tenant_recid'])) {
            jsonResponse(['success' => false, 'message' => 'Tenant is required.']);
        }

        if (empty($_POST['property_recid'])) {
            jsonResponse(['success' => false, 'message' => 'Property is required.']);
        }

        if (empty($_POST['room_recid'])) {
            jsonResponse(['success' => false, 'message' => 'Room is required.']);
        }

        if (empty($_POST['start_date'])) {
            jsonResponse(['success' => false, 'message' => 'Start date is required.']);
        }

        $leaseId = $this->leaseModel->getNextId();

        try {
            $leaseRecid = $this->leaseModel->add([
                'lease_id' => $leaseId,
                'tenant_recid' => (int)$_POST['tenant_recid'],
                'property_recid' => (int)$_POST['property_recid'],
                'room_recid' => (int)$_POST['room_recid'],
                'bedspace_recid' => !empty($_POST['bedspace_recid']) ? (int)$_POST['bedspace_recid'] : null,
                'lease_type' => $_POST['lease_type'] ?? 'monthly',
                'start_date' => $_POST['start_date'],
                'end_date' => $_POST['end_date'] ?? null,
                'monthly_rent' => (float)($_POST['monthly_rent'] ?? 0),
                'security_deposit' => (float)($_POST['security_deposit'] ?? 0),
                'lease_status' => $_POST['lease_status'] ?? 'active',
                'remarks' => trim($_POST['remarks'] ?? '')
            ]);

            if ($leaseRecid) {
                // Update bedspace status to occupied if bedspace was selected
                if (!empty($_POST['bedspace_recid'])) {
                    $this->bedspaceModel->updateStatus((int)$_POST['bedspace_recid'], 'occupied');
                }

                jsonResponse(['success' => true, 'message' => 'Lease added successfully.']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Failed to add lease.']);
            }
        } catch (Exception $e) {
            error_log("Lease add error: " . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to add lease.']);
        }
    }

    /**
     * Edit lease (AJAX)
     */
    public function edit()
    {
        requireLogin();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        $recid = isset($_POST['recid']) ? (int)$_POST['recid'] : 0;

        if (!$recid) {
            jsonResponse(['success' => false, 'message' => 'Invalid lease.']);
        }

        try {
            $result = $this->leaseModel->update($recid, [
                'tenant_recid' => (int)$_POST['tenant_recid'],
                'property_recid' => (int)$_POST['property_recid'],
                'room_recid' => (int)$_POST['room_recid'],
                'bedspace_recid' => !empty($_POST['bedspace_recid']) ? (int)$_POST['bedspace_recid'] : null,
                'lease_type' => $_POST['lease_type'] ?? 'monthly',
                'start_date' => $_POST['start_date'],
                'end_date' => $_POST['end_date'] ?? null,
                'monthly_rent' => (float)($_POST['monthly_rent'] ?? 0),
                'security_deposit' => (float)($_POST['security_deposit'] ?? 0),
                'lease_status' => $_POST['lease_status'] ?? 'active',
                'remarks' => trim($_POST['remarks'] ?? '')
            ]);

            if ($result) {
                jsonResponse(['success' => true, 'message' => 'Lease updated successfully.']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Failed to update lease.']);
            }
        } catch (Exception $e) {
            error_log("Lease update error: " . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to update lease.']);
        }
    }

    /**
     * Delete lease (AJAX)
     */
    public function delete()
    {
        requireLogin();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        $recid = isset($_POST['recid']) ? (int)$_POST['recid'] : 0;

        if (!$recid) {
            jsonResponse(['success' => false, 'message' => 'Invalid lease.']);
        }

        try {
            $result = $this->leaseModel->delete($recid);

            if ($result) {
                jsonResponse(['success' => true, 'message' => 'Lease deleted successfully.']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Failed to delete lease.']);
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

        $nextId = $this->leaseModel->getNextId();

        jsonResponse(['success' => true, 'data' => ['lease_id' => $nextId]]);
    }
}
