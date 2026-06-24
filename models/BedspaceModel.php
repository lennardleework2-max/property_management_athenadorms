<?php
/**
 * Bedspace Model
 * Athena Dorms Property Management System
 * Handles bedspace CRUD operations
 */

class BedspaceModel
{
    private $db;

    public function __construct()
    {
        $this->db = getDbConnection();
    }

    /**
     * Get all bedspaces
     * @param int $roomRecid Optional room filter
     * @return array
     */
    public function getAll($roomRecid = null)
    {
        $sql = "SELECT b.*, r.room_name, p.property_name,
                       t.tenant_name, l.lease_status
                FROM mf_bedspacefile b
                JOIN mf_roomfile r ON r.recid = b.room_recid
                JOIN mf_propertyfile p ON p.recid = r.property_recid
                LEFT JOIN trn_leasefile1 l ON l.bedspace_recid = b.recid AND l.lease_status = 'active'
                LEFT JOIN mf_tenantfile t ON t.recid = l.tenant_recid
                WHERE 1=1";

        $params = [];

        if (!empty($roomRecid)) {
            $sql .= " AND b.room_recid = :room_recid";
            $params['room_recid'] = $roomRecid;
        }

        $sql .= " ORDER BY p.property_name, r.room_name, b.bedspace_name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Get bedspace by recid
     * @param int $recid
     * @return array|false
     */
    public function getById($recid)
    {
        $sql = "SELECT b.*, r.room_name, p.property_name
                FROM mf_bedspacefile b
                JOIN mf_roomfile r ON r.recid = b.room_recid
                JOIN mf_propertyfile p ON p.recid = r.property_recid
                WHERE b.recid = :recid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['recid' => $recid]);

        return $stmt->fetch();
    }

    /**
     * Get bedspaces by room
     * @param int $roomRecid
     * @return array
     */
    public function getByRoom($roomRecid)
    {
        $sql = "SELECT b.recid, b.bedspace_id, b.bedspace_name, b.bedspace_status
                FROM mf_bedspacefile b
                WHERE b.room_recid = :room_recid
                ORDER BY b.bedspace_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['room_recid' => $roomRecid]);

        return $stmt->fetchAll();
    }

    /**
     * Get available bedspaces by room
     * @param int $roomRecid
     * @return array
     */
    public function getAvailableByRoom($roomRecid)
    {
        $sql = "SELECT b.recid, b.bedspace_id, b.bedspace_name
                FROM mf_bedspacefile b
                WHERE b.room_recid = :room_recid AND b.bedspace_status = 'available'
                ORDER BY b.bedspace_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['room_recid' => $roomRecid]);

        return $stmt->fetchAll();
    }

    /**
     * Add new bedspace
     * @param array $data
     * @return bool
     */
    public function add($data)
    {
        $sql = "INSERT INTO mf_bedspacefile
                (bedspace_id, room_recid, bedspace_name, bedspace_status, remarks)
                VALUES
                (:bedspace_id, :room_recid, :bedspace_name, :bedspace_status, :remarks)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'bedspace_id' => $data['bedspace_id'],
            'room_recid' => $data['room_recid'],
            'bedspace_name' => $data['bedspace_name'],
            'bedspace_status' => $data['bedspace_status'] ?? 'available',
            'remarks' => $data['remarks'] ?? null
        ]);
    }

    /**
     * Update bedspace
     * @param int $recid
     * @param array $data
     * @return bool
     */
    public function update($recid, $data)
    {
        $sql = "UPDATE mf_bedspacefile SET
                room_recid = :room_recid,
                bedspace_name = :bedspace_name,
                bedspace_status = :bedspace_status,
                remarks = :remarks,
                date_updated = NOW()
                WHERE recid = :recid";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'recid' => $recid,
            'room_recid' => $data['room_recid'],
            'bedspace_name' => $data['bedspace_name'],
            'bedspace_status' => $data['bedspace_status'] ?? 'available',
            'remarks' => $data['remarks'] ?? null
        ]);
    }

    /**
     * Update bedspace status
     * @param int $recid
     * @param string $status
     * @return bool
     */
    public function updateStatus($recid, $status)
    {
        $sql = "UPDATE mf_bedspacefile SET bedspace_status = :status, date_updated = NOW() WHERE recid = :recid";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['recid' => $recid, 'status' => $status]);
    }

    /**
     * Delete bedspace
     * @param int $recid
     * @return bool
     */
    public function delete($recid)
    {
        // Check if bedspace has active lease
        $sql = "SELECT COUNT(*) as count FROM trn_leasefile1 WHERE bedspace_recid = :recid AND lease_status = 'active'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['recid' => $recid]);
        $result = $stmt->fetch();

        if ($result['count'] > 0) {
            throw new Exception("Cannot delete bedspace with active lease.");
        }

        $sql = "DELETE FROM mf_bedspacefile WHERE recid = :recid";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['recid' => $recid]);
    }

    /**
     * Get next bedspace ID
     * @return string
     */
    public function getNextId()
    {
        $sql = "SELECT MAX(CAST(bedspace_id AS INTEGER)) as max_id FROM mf_bedspacefile";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();

        $maxId = $result['max_id'] ?? 0;

        return generateNextId($maxId);
    }
}
