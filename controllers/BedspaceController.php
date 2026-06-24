<?php
/**
 * Bedspace Controller
 * Athena Dorms Property Management System
 * Handles bedspace CRUD operations
 */

class BedspaceController
{
    private $bedspaceModel;
    private $roomModel;

    public function __construct()
    {
        require_once MODELS_PATH . '/BedspaceModel.php';
        require_once MODELS_PATH . '/RoomModel.php';

        $this->bedspaceModel = new BedspaceModel();
        $this->roomModel = new RoomModel();
    }

    /**
     * Display bedspace list
     */
    public function list()
    {
        requireLogin();

        $roomRecid = isset($_GET['room']) ? (int)$_GET['room'] : null;

        $bedspaces = $this->bedspaceModel->getAll($roomRecid);
        $rooms = $this->roomModel->getActiveRooms();

        renderWithLayout('bedspace/list', [
            'bedspaces' => $bedspaces,
            'rooms' => $rooms,
            'selectedRoom' => $roomRecid
        ], 'Bedspaces');
    }

    /**
     * Get single bedspace (AJAX)
     */
    public function get()
    {
        requireLogin();

        $recid = isset($_POST['recid']) ? (int)$_POST['recid'] : 0;

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        $bedspace = $this->bedspaceModel->getById($recid);

        if (!$bedspace) {
            jsonResponse(['success' => false, 'message' => 'Bedspace not found.'], 404);
        }

        jsonResponse(['success' => true, 'data' => $bedspace]);
    }

    /**
     * Get all bedspaces (AJAX)
     */
    public function getAll()
    {
        requireLogin();

        $bedspaces = $this->bedspaceModel->getAll();

        jsonResponse(['success' => true, 'data' => $bedspaces]);
    }

    /**
     * Get bedspaces by room (AJAX)
     */
    public function getByRoom()
    {
        requireLogin();

        $roomRecid = isset($_POST['room_recid']) ? (int)$_POST['room_recid'] : 0;

        $bedspaces = $this->bedspaceModel->getByRoom($roomRecid);

        jsonResponse(['success' => true, 'data' => $bedspaces]);
    }

    /**
     * Add bedspace (AJAX)
     */
    public function add()
    {
        requireLogin();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        if (empty($_POST['bedspace_name'])) {
            jsonResponse(['success' => false, 'message' => 'Bedspace name is required.']);
        }

        if (empty($_POST['room_recid'])) {
            jsonResponse(['success' => false, 'message' => 'Room is required.']);
        }

        $bedspaceId = $this->bedspaceModel->getNextId();

        try {
            $result = $this->bedspaceModel->add([
                'bedspace_id' => $bedspaceId,
                'room_recid' => (int)$_POST['room_recid'],
                'bedspace_name' => trim($_POST['bedspace_name']),
                'bedspace_status' => $_POST['bedspace_status'] ?? 'available',
                'remarks' => trim($_POST['remarks'] ?? '')
            ]);

            if ($result) {
                jsonResponse(['success' => true, 'message' => 'Bedspace added successfully.']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Failed to add bedspace.']);
            }
        } catch (Exception $e) {
            error_log("Bedspace add error: " . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to add bedspace.']);
        }
    }

    /**
     * Edit bedspace (AJAX)
     */
    public function edit()
    {
        requireLogin();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        $recid = isset($_POST['recid']) ? (int)$_POST['recid'] : 0;

        if (!$recid) {
            jsonResponse(['success' => false, 'message' => 'Invalid bedspace.']);
        }

        if (empty($_POST['bedspace_name'])) {
            jsonResponse(['success' => false, 'message' => 'Bedspace name is required.']);
        }

        try {
            $result = $this->bedspaceModel->update($recid, [
                'room_recid' => (int)$_POST['room_recid'],
                'bedspace_name' => trim($_POST['bedspace_name']),
                'bedspace_status' => $_POST['bedspace_status'] ?? 'available',
                'remarks' => trim($_POST['remarks'] ?? '')
            ]);

            if ($result) {
                jsonResponse(['success' => true, 'message' => 'Bedspace updated successfully.']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Failed to update bedspace.']);
            }
        } catch (Exception $e) {
            error_log("Bedspace update error: " . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to update bedspace.']);
        }
    }

    /**
     * Delete bedspace (AJAX)
     */
    public function delete()
    {
        requireLogin();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        $recid = isset($_POST['recid']) ? (int)$_POST['recid'] : 0;

        if (!$recid) {
            jsonResponse(['success' => false, 'message' => 'Invalid bedspace.']);
        }

        try {
            $result = $this->bedspaceModel->delete($recid);

            if ($result) {
                jsonResponse(['success' => true, 'message' => 'Bedspace deleted successfully.']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Failed to delete bedspace.']);
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

        $nextId = $this->bedspaceModel->getNextId();

        jsonResponse(['success' => true, 'data' => ['bedspace_id' => $nextId]]);
    }
}
