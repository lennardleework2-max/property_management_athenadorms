<?php
/**
 * Property Model
 * Athena Dorms Property Management System
 * Handles property CRUD operations
 */

class PropertyModel
{
    private $db;

    public function __construct()
    {
        $this->db = getDbConnection();
    }

    /**
     * Get all properties
     * @param string $search Optional search term
     * @param string $status Optional status filter
     * @return array
     */
    public function getAll($search = '', $status = '')
    {
        $sql = "SELECT p.*,
                       (SELECT COUNT(*) FROM mf_roomfile r WHERE r.property_recid = p.recid) as room_count,
                       (SELECT COUNT(*) FROM mf_roomfile r
                        WHERE r.property_recid = p.recid
                        AND r.recid IN (
                            SELECT b.room_recid FROM mf_bedspacefile b
                            WHERE b.room_recid = r.recid
                            GROUP BY b.room_recid
                            HAVING COUNT(*) = COUNT(CASE WHEN b.bedspace_status = 'occupied' THEN 1 END)
                            AND COUNT(*) > 0
                        )) as full_room_count,
                       (SELECT COUNT(*) FROM mf_roomfile r
                        JOIN mf_bedspacefile b ON b.room_recid = r.recid
                        WHERE r.property_recid = p.recid) as total_bedspace_count,
                       (SELECT COUNT(*) FROM mf_roomfile r
                        JOIN mf_bedspacefile b ON b.room_recid = r.recid
                        WHERE r.property_recid = p.recid AND b.bedspace_status = 'occupied') as occupied_bedspace_count
                FROM mf_propertyfile p
                WHERE 1=1";

        $params = [];

        if (!empty($search)) {
            $sql .= " AND (p.property_name LIKE :search OR p.property_address LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        if (!empty($status)) {
            $sql .= " AND p.property_status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY p.property_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Get property by recid
     * @param int $recid
     * @return array|false
     */
    public function getById($recid)
    {
        $sql = "SELECT * FROM mf_propertyfile WHERE recid = :recid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['recid' => $recid]);

        return $stmt->fetch();
    }

    /**
     * Get active properties for dropdowns
     * @return array
     */
    public function getActiveProperties()
    {
        $sql = "SELECT recid, property_id, property_name FROM mf_propertyfile
                WHERE property_status = 'active'
                ORDER BY property_name";
        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    /**
     * Add new property
     * @param array $data
     * @return bool
     */
    public function add($data)
    {
        $sql = "INSERT INTO mf_propertyfile
                (property_id, property_name, property_address, property_type, property_status, remarks)
                VALUES
                (:property_id, :property_name, :property_address, :property_type, :property_status, :remarks)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'property_id' => $data['property_id'],
            'property_name' => $data['property_name'],
            'property_address' => $data['property_address'] ?? null,
            'property_type' => $data['property_type'] ?? 'dormitory',
            'property_status' => $data['property_status'] ?? 'active',
            'remarks' => $data['remarks'] ?? null
        ]);
    }

    /**
     * Update property
     * @param int $recid
     * @param array $data
     * @return bool
     */
    public function update($recid, $data)
    {
        $sql = "UPDATE mf_propertyfile SET
                property_name = :property_name,
                property_address = :property_address,
                property_type = :property_type,
                property_status = :property_status,
                remarks = :remarks,
                date_updated = NOW()
                WHERE recid = :recid";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'recid' => $recid,
            'property_name' => $data['property_name'],
            'property_address' => $data['property_address'] ?? null,
            'property_type' => $data['property_type'] ?? 'dormitory',
            'property_status' => $data['property_status'] ?? 'active',
            'remarks' => $data['remarks'] ?? null
        ]);
    }

    /**
     * Delete property
     * @param int $recid
     * @return bool
     */
    public function delete($recid)
    {
        // Check if property has rooms
        $sql = "SELECT COUNT(*) as count FROM mf_roomfile WHERE property_recid = :recid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['recid' => $recid]);
        $result = $stmt->fetch();

        if ($result['count'] > 0) {
            throw new Exception("Cannot delete property with existing rooms.");
        }

        $sql = "DELETE FROM mf_propertyfile WHERE recid = :recid";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['recid' => $recid]);
    }

    /**
     * Get next property ID
     * @return string
     */
    public function getNextId()
    {
        $sql = "SELECT MAX(CAST(property_id AS INTEGER)) as max_id FROM mf_propertyfile";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();

        $maxId = $result['max_id'] ?? 0;

        return generateNextId($maxId);
    }
}
