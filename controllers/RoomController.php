<?php
/**
 * Room Controller
 * Athena Dorms Property Management System
 * Handles room CRUD operations
 */

class RoomController
{
    private $roomModel;
    private $propertyModel;
    private $bedspaceModel;

    public function __construct()
    {
        require_once MODELS_PATH . '/RoomModel.php';
        require_once MODELS_PATH . '/PropertyModel.php';
        require_once MODELS_PATH . '/BedspaceModel.php';

        $this->roomModel = new RoomModel();
        $this->propertyModel = new PropertyModel();
        $this->bedspaceModel = new BedspaceModel();
    }

    /**
     * Display room list
     */
    public function list()
    {
        requireLogin();

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $propertyRecid = isset($_GET['property']) ? (int)$_GET['property'] : null;

        $rooms = $this->roomModel->getAll($search, $propertyRecid);
        $properties = $this->propertyModel->getActiveProperties();

        // Get bedspaces for each room
        foreach ($rooms as &$room) {
            $room['bedspaces'] = $this->bedspaceModel->getByRoom($room['recid']);
        }

        renderWithLayout('room/list', [
            'rooms' => $rooms,
            'properties' => $properties,
            'search' => $search,
            'selectedProperty' => $propertyRecid
        ], 'Rooms & Bedspaces');
    }

    /**
     * Get single room (AJAX)
     */
    public function get()
    {
        requireLogin();

        $recid = isset($_POST['recid']) ? (int)$_POST['recid'] : 0;

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        $room = $this->roomModel->getById($recid);

        if (!$room) {
            jsonResponse(['success' => false, 'message' => 'Room not found.'], 404);
        }

        jsonResponse(['success' => true, 'data' => $room]);
    }

    /**
     * Get all rooms (AJAX)
     */
    public function getAll()
    {
        requireLogin();

        $rooms = $this->roomModel->getActiveRooms();

        jsonResponse(['success' => true, 'data' => $rooms]);
    }

    /**
     * Get rooms by property (AJAX)
     */
    public function getByProperty()
    {
        requireLogin();

        $propertyRecid = isset($_POST['property_recid']) ? (int)$_POST['property_recid'] : 0;

        $rooms = $this->roomModel->getByProperty($propertyRecid);

        jsonResponse(['success' => true, 'data' => $rooms]);
    }

    /**
     * Add room (AJAX)
     */
    public function add()
    {
        requireLogin();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        if (empty($_POST['room_name'])) {
            jsonResponse(['success' => false, 'message' => 'Room name is required.']);
        }

        if (empty($_POST['property_recid'])) {
            jsonResponse(['success' => false, 'message' => 'Property is required.']);
        }

        $roomId = $this->roomModel->getNextId();

        try {
            $result = $this->roomModel->add([
                'room_id' => $roomId,
                'property_recid' => (int)$_POST['property_recid'],
                'room_name' => trim($_POST['room_name']),
                'room_type' => $_POST['room_type'] ?? 'bedspace',
                'max_bedspace' => (int)($_POST['max_bedspace'] ?? 4),
                'monthly_room_rate' => (float)($_POST['monthly_room_rate'] ?? 0),
                'room_status' => $_POST['room_status'] ?? 'active',
                'remarks' => trim($_POST['remarks'] ?? '')
            ]);

            if ($result) {
                jsonResponse(['success' => true, 'message' => 'Room added successfully.']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Failed to add room.']);
            }
        } catch (Exception $e) {
            error_log("Room add error: " . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to add room.']);
        }
    }

    /**
     * Edit room (AJAX)
     */
    public function edit()
    {
        requireLogin();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        $recid = isset($_POST['recid']) ? (int)$_POST['recid'] : 0;

        if (!$recid) {
            jsonResponse(['success' => false, 'message' => 'Invalid room.']);
        }

        if (empty($_POST['room_name'])) {
            jsonResponse(['success' => false, 'message' => 'Room name is required.']);
        }

        try {
            $result = $this->roomModel->update($recid, [
                'property_recid' => (int)$_POST['property_recid'],
                'room_name' => trim($_POST['room_name']),
                'room_type' => $_POST['room_type'] ?? 'bedspace',
                'max_bedspace' => (int)($_POST['max_bedspace'] ?? 4),
                'monthly_room_rate' => (float)($_POST['monthly_room_rate'] ?? 0),
                'room_status' => $_POST['room_status'] ?? 'active',
                'remarks' => trim($_POST['remarks'] ?? '')
            ]);

            if ($result) {
                jsonResponse(['success' => true, 'message' => 'Room updated successfully.']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Failed to update room.']);
            }
        } catch (Exception $e) {
            error_log("Room update error: " . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to update room.']);
        }
    }

    /**
     * Delete room (AJAX)
     */
    public function delete()
    {
        requireLogin();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        $recid = isset($_POST['recid']) ? (int)$_POST['recid'] : 0;

        if (!$recid) {
            jsonResponse(['success' => false, 'message' => 'Invalid room.']);
        }

        try {
            $result = $this->roomModel->delete($recid);

            if ($result) {
                jsonResponse(['success' => true, 'message' => 'Room deleted successfully.']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Failed to delete room.']);
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

        $nextId = $this->roomModel->getNextId();

        jsonResponse(['success' => true, 'data' => ['room_id' => $nextId]]);
    }
}
