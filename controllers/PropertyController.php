<?php
/**
 * Property Controller
 * Athena Dorms Property Management System
 * Handles property CRUD operations
 */

class PropertyController
{
    private $propertyModel;

    public function __construct()
    {
        require_once MODELS_PATH . '/PropertyModel.php';
        $this->propertyModel = new PropertyModel();
    }

    /**
     * Display property list
     */
    public function list()
    {
        requireLogin();

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $status = isset($_GET['status']) ? trim($_GET['status']) : '';

        $properties = $this->propertyModel->getAll($search, $status);

        renderWithLayout('property/list', [
            'properties' => $properties,
            'search' => $search,
            'status' => $status
        ], 'Properties');
    }

    /**
     * Get single property (AJAX)
     */
    public function get()
    {
        requireLogin();

        $recid = isset($_POST['recid']) ? (int)$_POST['recid'] : 0;

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        $property = $this->propertyModel->getById($recid);

        if (!$property) {
            jsonResponse(['success' => false, 'message' => 'Property not found.'], 404);
        }

        jsonResponse(['success' => true, 'data' => $property]);
    }

    /**
     * Get all properties (AJAX)
     */
    public function getAll()
    {
        requireLogin();

        $properties = $this->propertyModel->getActiveProperties();

        jsonResponse(['success' => true, 'data' => $properties]);
    }

    /**
     * Add property (AJAX)
     */
    public function add()
    {
        requireLogin();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        if (empty($_POST['property_name'])) {
            jsonResponse(['success' => false, 'message' => 'Property name is required.']);
        }

        $propertyId = $this->propertyModel->getNextId();

        try {
            $result = $this->propertyModel->add([
                'property_id' => $propertyId,
                'property_name' => trim($_POST['property_name']),
                'property_address' => trim($_POST['property_address'] ?? ''),
                'property_type' => $_POST['property_type'] ?? 'dormitory',
                'property_status' => $_POST['property_status'] ?? 'active',
                'remarks' => trim($_POST['remarks'] ?? '')
            ]);

            if ($result) {
                jsonResponse(['success' => true, 'message' => 'Property added successfully.']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Failed to add property.']);
            }
        } catch (Exception $e) {
            error_log("Property add error: " . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to add property.']);
        }
    }

    /**
     * Edit property (AJAX)
     */
    public function edit()
    {
        requireLogin();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        $recid = isset($_POST['recid']) ? (int)$_POST['recid'] : 0;

        if (!$recid) {
            jsonResponse(['success' => false, 'message' => 'Invalid property.']);
        }

        if (empty($_POST['property_name'])) {
            jsonResponse(['success' => false, 'message' => 'Property name is required.']);
        }

        try {
            $result = $this->propertyModel->update($recid, [
                'property_name' => trim($_POST['property_name']),
                'property_address' => trim($_POST['property_address'] ?? ''),
                'property_type' => $_POST['property_type'] ?? 'dormitory',
                'property_status' => $_POST['property_status'] ?? 'active',
                'remarks' => trim($_POST['remarks'] ?? '')
            ]);

            if ($result) {
                jsonResponse(['success' => true, 'message' => 'Property updated successfully.']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Failed to update property.']);
            }
        } catch (Exception $e) {
            error_log("Property update error: " . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to update property.']);
        }
    }

    /**
     * Delete property (AJAX)
     */
    public function delete()
    {
        requireLogin();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        $recid = isset($_POST['recid']) ? (int)$_POST['recid'] : 0;

        if (!$recid) {
            jsonResponse(['success' => false, 'message' => 'Invalid property.']);
        }

        try {
            $result = $this->propertyModel->delete($recid);

            if ($result) {
                jsonResponse(['success' => true, 'message' => 'Property deleted successfully.']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Failed to delete property.']);
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

        $nextId = $this->propertyModel->getNextId();

        jsonResponse(['success' => true, 'data' => ['property_id' => $nextId]]);
    }
}
