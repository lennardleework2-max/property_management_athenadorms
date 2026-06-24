<?php
/**
 * Utility Controller
 * Athena Dorms Property Management System
 * Handles utility billing and allocation
 */

class UtilityController
{
    private $utilityModel;
    private $propertyModel;
    private $roomModel;
    private $leaseModel;

    public function __construct()
    {
        require_once MODELS_PATH . '/UtilityModel.php';
        require_once MODELS_PATH . '/PropertyModel.php';
        require_once MODELS_PATH . '/RoomModel.php';
        require_once MODELS_PATH . '/LeaseModel.php';

        $this->utilityModel = new UtilityModel();
        $this->propertyModel = new PropertyModel();
        $this->roomModel = new RoomModel();
        $this->leaseModel = new LeaseModel();
    }

    /**
     * Display utility list
     */
    public function list()
    {
        requireLogin();

        $propertyRecid = isset($_GET['property']) ? (int)$_GET['property'] : null;
        $utilityType = isset($_GET['type']) ? trim($_GET['type']) : '';

        $utilities = $this->utilityModel->getAll($propertyRecid, $utilityType);
        $properties = $this->propertyModel->getActiveProperties();

        renderWithLayout('utility/list', [
            'utilities' => $utilities,
            'properties' => $properties,
            'selectedProperty' => $propertyRecid,
            'selectedType' => $utilityType
        ], 'Utility Computation');
    }

    /**
     * Get single utility (AJAX)
     */
    public function get()
    {
        requireLogin();

        $recid = isset($_POST['recid']) ? (int)$_POST['recid'] : 0;

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        $utility = $this->utilityModel->getById($recid);

        if (!$utility) {
            jsonResponse(['success' => false, 'message' => 'Utility not found.'], 404);
        }

        // Get allocations
        $allocations = $this->utilityModel->getAllocations($recid);
        $utility['allocations'] = $allocations;

        jsonResponse(['success' => true, 'data' => $utility]);
    }

    /**
     * Get all utilities (AJAX)
     */
    public function getAll()
    {
        requireLogin();

        $utilities = $this->utilityModel->getAll();

        jsonResponse(['success' => true, 'data' => $utilities]);
    }

    /**
     * Add utility (AJAX)
     */
    public function add()
    {
        requireLogin();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        if (empty($_POST['property_recid'])) {
            jsonResponse(['success' => false, 'message' => 'Property is required.']);
        }

        if (empty($_POST['room_recid'])) {
            jsonResponse(['success' => false, 'message' => 'Room is required.']);
        }

        if (empty($_POST['utility_type'])) {
            jsonResponse(['success' => false, 'message' => 'Utility type is required.']);
        }

        if (empty($_POST['billing_month'])) {
            jsonResponse(['success' => false, 'message' => 'Billing month is required.']);
        }

        $utilityId = $this->utilityModel->getNextId();

        try {
            $utilityRecid = $this->utilityModel->add([
                'utility_id' => $utilityId,
                'property_recid' => (int)$_POST['property_recid'],
                'room_recid' => (int)$_POST['room_recid'],
                'utility_type' => $_POST['utility_type'],
                'billing_month' => $_POST['billing_month'] . '-01',
                'previous_reading' => (float)($_POST['previous_reading'] ?? 0),
                'current_reading' => (float)($_POST['current_reading'] ?? 0),
                'rate' => (float)($_POST['rate'] ?? 10.00),
                'total_amount' => (float)($_POST['total_amount'] ?? 0),
                'split_method' => $_POST['split_method'] ?? 'equal_active_tenants',
                'utility_status' => $_POST['utility_status'] ?? 'draft',
                'remarks' => trim($_POST['remarks'] ?? '')
            ]);

            if ($utilityRecid) {
                // Auto-allocate if equal split
                if ($_POST['split_method'] === 'equal_active_tenants') {
                    $this->autoAllocate($utilityRecid, (int)$_POST['room_recid'], (float)($_POST['total_amount'] ?? 0));
                }

                jsonResponse(['success' => true, 'message' => 'Utility bill added successfully.', 'data' => ['recid' => $utilityRecid]]);
            } else {
                jsonResponse(['success' => false, 'message' => 'Failed to add utility bill.']);
            }
        } catch (Exception $e) {
            error_log("Utility add error: " . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to add utility bill.']);
        }
    }

    /**
     * Auto allocate utility to active tenants in room
     */
    private function autoAllocate($utilityRecid, $roomRecid, $totalAmount)
    {
        $leases = $this->leaseModel->getActiveByRoom($roomRecid);

        if (empty($leases)) {
            return;
        }

        $baseAmount = $totalAmount / count($leases);

        foreach ($leases as $lease) {
            $allocationId = $this->utilityModel->getNextAllocationId();

            $this->utilityModel->addAllocation([
                'allocation_id' => $allocationId,
                'utility_recid' => $utilityRecid,
                'tenant_recid' => $lease['tenant_recid'],
                'lease_recid' => $lease['recid'],
                'base_amount' => round($baseAmount, 2),
                'adjustment_amount' => 0,
                'remarks' => 'Equal split'
            ]);
        }
    }

    /**
     * Edit utility (AJAX)
     */
    public function edit()
    {
        requireLogin();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        $recid = isset($_POST['recid']) ? (int)$_POST['recid'] : 0;

        if (!$recid) {
            jsonResponse(['success' => false, 'message' => 'Invalid utility.']);
        }

        try {
            $result = $this->utilityModel->update($recid, [
                'property_recid' => (int)$_POST['property_recid'],
                'room_recid' => (int)$_POST['room_recid'],
                'utility_type' => $_POST['utility_type'],
                'billing_month' => $_POST['billing_month'] . '-01',
                'previous_reading' => (float)($_POST['previous_reading'] ?? 0),
                'current_reading' => (float)($_POST['current_reading'] ?? 0),
                'rate' => (float)($_POST['rate'] ?? 10.00),
                'total_amount' => (float)($_POST['total_amount'] ?? 0),
                'split_method' => $_POST['split_method'] ?? 'equal_active_tenants',
                'utility_status' => $_POST['utility_status'] ?? 'draft',
                'remarks' => trim($_POST['remarks'] ?? '')
            ]);

            if ($result) {
                jsonResponse(['success' => true, 'message' => 'Utility bill updated successfully.']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Failed to update utility bill.']);
            }
        } catch (Exception $e) {
            error_log("Utility update error: " . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to update utility bill.']);
        }
    }

    /**
     * Allocate utility to tenants (AJAX)
     */
    public function allocate()
    {
        requireLogin();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        $utilityRecid = isset($_POST['utility_recid']) ? (int)$_POST['utility_recid'] : 0;

        if (!$utilityRecid) {
            jsonResponse(['success' => false, 'message' => 'Invalid utility.']);
        }

        // Delete existing allocations
        $this->utilityModel->deleteAllocations($utilityRecid);

        // Add new allocations
        $allocations = isset($_POST['allocations']) ? json_decode($_POST['allocations'], true) : [];

        if (empty($allocations)) {
            jsonResponse(['success' => false, 'message' => 'No allocations provided.']);
        }

        try {
            foreach ($allocations as $alloc) {
                $allocationId = $this->utilityModel->getNextAllocationId();

                $this->utilityModel->addAllocation([
                    'allocation_id' => $allocationId,
                    'utility_recid' => $utilityRecid,
                    'tenant_recid' => (int)$alloc['tenant_recid'],
                    'lease_recid' => (int)$alloc['lease_recid'],
                    'base_amount' => (float)$alloc['base_amount'],
                    'adjustment_amount' => (float)($alloc['adjustment_amount'] ?? 0),
                    'remarks' => $alloc['remarks'] ?? ''
                ]);
            }

            // Update utility status
            $this->utilityModel->updateStatus($utilityRecid, 'computed');

            jsonResponse(['success' => true, 'message' => 'Utility allocations saved successfully.']);
        } catch (Exception $e) {
            error_log("Utility allocate error: " . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to save allocations.']);
        }
    }

    /**
     * Delete utility (AJAX)
     */
    public function delete()
    {
        requireLogin();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        $recid = isset($_POST['recid']) ? (int)$_POST['recid'] : 0;

        if (!$recid) {
            jsonResponse(['success' => false, 'message' => 'Invalid utility.']);
        }

        try {
            $result = $this->utilityModel->delete($recid);

            if ($result) {
                jsonResponse(['success' => true, 'message' => 'Utility bill deleted successfully.']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Failed to delete utility bill.']);
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

        $nextId = $this->utilityModel->getNextId();

        jsonResponse(['success' => true, 'data' => ['utility_id' => $nextId]]);
    }
}
