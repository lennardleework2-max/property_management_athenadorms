<?php
/**
 * Room Model
 * Athena Dorms Property Management System
 * Handles room CRUD operations
 */

class RoomModel
{
    private $db;

    public function __construct()
    {
        $this->db = getDbConnection();
    }

    /**
     * Get all rooms
     * @param string $search Optional search term
     * @param int $propertyRecid Optional property filter
     * @return array
     */
    public function getAll($search = '', $propertyRecid = null)
    {
        $sql = "SELECT r.*, p.property_name,
                       (SELECT COUNT(*) FROM mf_bedspacefile b WHERE b.room_recid = r.recid) as bedspace_count,
                       (SELECT COUNT(*) FROM mf_bedspacefile b WHERE b.room_recid = r.recid AND b.bedspace_status = 'occupied') as occupied_count,
                       (SELECT COUNT(*) FROM mf_bedspacefile b WHERE b.room_recid = r.recid AND b.bedspace_status = 'available') as available_count
                FROM mf_roomfile r
                JOIN mf_propertyfile p ON p.recid = r.property_recid
                WHERE 1=1";

        $params = [];

        if (!empty($search)) {
            $sql .= " AND (r.room_name LIKE :search OR p.property_name LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        if (!empty($propertyRecid)) {
            $sql .= " AND r.property_recid = :property_recid";
            $params['property_recid'] = $propertyRecid;
        }

        $sql .= " ORDER BY p.property_name, r.room_name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Get room by recid
     * @param int $recid
     * @return array|false
     */
    public function getById($recid)
    {
        $sql = "SELECT r.*, p.property_name
                FROM mf_roomfile r
                JOIN mf_propertyfile p ON p.recid = r.property_recid
                WHERE r.recid = :recid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['recid' => $recid]);

        return $stmt->fetch();
    }

    /**
     * Get rooms by property
     * @param int $propertyRecid
     * @return array
     */
    public function getByProperty($propertyRecid)
    {
        $sql = "SELECT recid, room_id, room_name, room_type, max_bedspace, monthly_room_rate
                FROM mf_roomfile
                WHERE property_recid = :property_recid AND room_status = 'active'
                ORDER BY room_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['property_recid' => $propertyRecid]);

        return $stmt->fetchAll();
    }

    /**
     * Get active rooms for dropdowns
     * @return array
     */
    public function getActiveRooms()
    {
        $sql = "SELECT r.recid, r.room_id, r.room_name, p.property_name
                FROM mf_roomfile r
                JOIN mf_propertyfile p ON p.recid = r.property_recid
                WHERE r.room_status = 'active'
                ORDER BY p.property_name, r.room_name";
        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    /**
     * Add new room
     * @param array $data
     * @return bool
     */
    public function add($data)
    {
        $sql = "INSERT INTO mf_roomfile
                (room_id, property_recid, room_name, room_type, max_bedspace, monthly_room_rate, room_status, remarks)
                VALUES
                (:room_id, :property_recid, :room_name, :room_type, :max_bedspace, :monthly_room_rate, :room_status, :remarks)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'room_id' => $data['room_id'],
            'property_recid' => $data['property_recid'],
            'room_name' => $data['room_name'],
            'room_type' => $data['room_type'] ?? 'bedspace',
            'max_bedspace' => $data['max_bedspace'] ?? 4,
            'monthly_room_rate' => $data['monthly_room_rate'] ?? 0,
            'room_status' => $data['room_status'] ?? 'active',
            'remarks' => $data['remarks'] ?? null
        ]);
    }

    /**
     * Update room
     * @param int $recid
     * @param array $data
     * @return bool
     */
    public function update($recid, $data)
    {
        $sql = "UPDATE mf_roomfile SET
                property_recid = :property_recid,
                room_name = :room_name,
                room_type = :room_type,
                max_bedspace = :max_bedspace,
                monthly_room_rate = :monthly_room_rate,
                room_status = :room_status,
                remarks = :remarks,
                date_updated = NOW()
                WHERE recid = :recid";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'recid' => $recid,
            'property_recid' => $data['property_recid'],
            'room_name' => $data['room_name'],
            'room_type' => $data['room_type'] ?? 'bedspace',
            'max_bedspace' => $data['max_bedspace'] ?? 4,
            'monthly_room_rate' => $data['monthly_room_rate'] ?? 0,
            'room_status' => $data['room_status'] ?? 'active',
            'remarks' => $data['remarks'] ?? null
        ]);
    }

    /**
     * Delete room
     * @param int $recid
     * @return bool
     */
    public function delete($recid)
    {
        // Check if room has bedspaces
        $sql = "SELECT COUNT(*) as count FROM mf_bedspacefile WHERE room_recid = :recid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['recid' => $recid]);
        $result = $stmt->fetch();

        if ($result['count'] > 0) {
            throw new Exception("Cannot delete room with existing bedspaces.");
        }

        $sql = "DELETE FROM mf_roomfile WHERE recid = :recid";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['recid' => $recid]);
    }

    /**
     * Get next room ID
     * @return string
     */
    public function getNextId()
    {
        $sql = "SELECT MAX(CAST(room_id AS INTEGER)) as max_id FROM mf_roomfile";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();

        $maxId = $result['max_id'] ?? 0;

        return generateNextId($maxId);
    }
}
