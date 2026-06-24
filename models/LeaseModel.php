<?php
/**
 * Lease Model
 * Athena Dorms Property Management System
 * Handles lease/contract CRUD operations
 */

class LeaseModel
{
    private $db;

    public function __construct()
    {
        $this->db = getDbConnection();
    }

    /**
     * Get all leases
     * @param string $status Optional status filter
     * @return array
     */
    public function getAll($status = '')
    {
        $sql = "SELECT l.*, t.tenant_name, t.phone_no,
                       p.property_name, r.room_name, b.bedspace_name
                FROM trn_leasefile1 l
                JOIN mf_tenantfile t ON t.recid = l.tenant_recid
                JOIN mf_propertyfile p ON p.recid = l.property_recid
                JOIN mf_roomfile r ON r.recid = l.room_recid
                LEFT JOIN mf_bedspacefile b ON b.recid = l.bedspace_recid
                WHERE 1=1";

        $params = [];

        if (!empty($status)) {
            $sql .= " AND l.lease_status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY l.start_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Get lease by recid
     * @param int $recid
     * @return array|false
     */
    public function getById($recid)
    {
        $sql = "SELECT l.*, t.tenant_name, p.property_name, r.room_name, b.bedspace_name
                FROM trn_leasefile1 l
                JOIN mf_tenantfile t ON t.recid = l.tenant_recid
                JOIN mf_propertyfile p ON p.recid = l.property_recid
                JOIN mf_roomfile r ON r.recid = l.room_recid
                LEFT JOIN mf_bedspacefile b ON b.recid = l.bedspace_recid
                WHERE l.recid = :recid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['recid' => $recid]);

        return $stmt->fetch();
    }

    /**
     * Get active lease by tenant
     * @param int $tenantRecid
     * @return array|false
     */
    public function getActiveByTenant($tenantRecid)
    {
        $sql = "SELECT l.*, p.property_name, r.room_name, b.bedspace_name
                FROM trn_leasefile1 l
                JOIN mf_propertyfile p ON p.recid = l.property_recid
                JOIN mf_roomfile r ON r.recid = l.room_recid
                LEFT JOIN mf_bedspacefile b ON b.recid = l.bedspace_recid
                WHERE l.tenant_recid = :tenant_recid AND l.lease_status = 'active'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_recid' => $tenantRecid]);

        return $stmt->fetch();
    }

    /**
     * Get active leases for dropdown
     * @return array
     */
    public function getActiveLeases()
    {
        $sql = "SELECT l.recid, l.lease_id, t.tenant_name, r.room_name, b.bedspace_name
                FROM trn_leasefile1 l
                JOIN mf_tenantfile t ON t.recid = l.tenant_recid
                JOIN mf_roomfile r ON r.recid = l.room_recid
                LEFT JOIN mf_bedspacefile b ON b.recid = l.bedspace_recid
                WHERE l.lease_status = 'active'
                ORDER BY t.tenant_name";
        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    /**
     * Get active leases by room for utility allocation
     * @param int $roomRecid
     * @return array
     */
    public function getActiveByRoom($roomRecid)
    {
        $sql = "SELECT l.recid, l.lease_id, l.tenant_recid, t.tenant_name
                FROM trn_leasefile1 l
                JOIN mf_tenantfile t ON t.recid = l.tenant_recid
                WHERE l.room_recid = :room_recid AND l.lease_status = 'active'
                ORDER BY t.tenant_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['room_recid' => $roomRecid]);

        return $stmt->fetchAll();
    }

    /**
     * Add new lease
     * @param array $data
     * @return int|false Last insert recid
     */
    public function add($data)
    {
        $sql = "INSERT INTO trn_leasefile1
                (lease_id, tenant_recid, property_recid, room_recid, bedspace_recid,
                 lease_type, start_date, end_date, monthly_rent, security_deposit, lease_status, remarks)
                VALUES
                (:lease_id, :tenant_recid, :property_recid, :room_recid, :bedspace_recid,
                 :lease_type, :start_date, :end_date, :monthly_rent, :security_deposit, :lease_status, :remarks)";

        $stmt = $this->db->prepare($sql);

        $result = $stmt->execute([
            'lease_id' => $data['lease_id'],
            'tenant_recid' => $data['tenant_recid'],
            'property_recid' => $data['property_recid'],
            'room_recid' => $data['room_recid'],
            'bedspace_recid' => !empty($data['bedspace_recid']) ? $data['bedspace_recid'] : null,
            'lease_type' => $data['lease_type'] ?? 'monthly',
            'start_date' => $data['start_date'],
            'end_date' => !empty($data['end_date']) ? $data['end_date'] : null,
            'monthly_rent' => $data['monthly_rent'] ?? 0,
            'security_deposit' => $data['security_deposit'] ?? 0,
            'lease_status' => $data['lease_status'] ?? 'active',
            'remarks' => $data['remarks'] ?? null
        ]);

        return $result ? (int)$this->db->lastInsertId() : false;
    }

    /**
     * Update lease
     * @param int $recid
     * @param array $data
     * @return bool
     */
    public function update($recid, $data)
    {
        $sql = "UPDATE trn_leasefile1 SET
                tenant_recid = :tenant_recid,
                property_recid = :property_recid,
                room_recid = :room_recid,
                bedspace_recid = :bedspace_recid,
                lease_type = :lease_type,
                start_date = :start_date,
                end_date = :end_date,
                monthly_rent = :monthly_rent,
                security_deposit = :security_deposit,
                lease_status = :lease_status,
                remarks = :remarks,
                date_updated = NOW()
                WHERE recid = :recid";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'recid' => $recid,
            'tenant_recid' => $data['tenant_recid'],
            'property_recid' => $data['property_recid'],
            'room_recid' => $data['room_recid'],
            'bedspace_recid' => !empty($data['bedspace_recid']) ? $data['bedspace_recid'] : null,
            'lease_type' => $data['lease_type'] ?? 'monthly',
            'start_date' => $data['start_date'],
            'end_date' => !empty($data['end_date']) ? $data['end_date'] : null,
            'monthly_rent' => $data['monthly_rent'] ?? 0,
            'security_deposit' => $data['security_deposit'] ?? 0,
            'lease_status' => $data['lease_status'] ?? 'active',
            'remarks' => $data['remarks'] ?? null
        ]);
    }

    /**
     * Delete lease
     * @param int $recid
     * @return bool
     */
    public function delete($recid)
    {
        $sql = "DELETE FROM trn_leasefile1 WHERE recid = :recid";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['recid' => $recid]);
    }

    /**
     * Get next lease ID
     * @return string
     */
    public function getNextId()
    {
        $sql = "SELECT MAX(CAST(lease_id AS INTEGER)) as max_id FROM trn_leasefile1";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();

        $maxId = $result['max_id'] ?? 0;

        return generateNextId($maxId);
    }
}
