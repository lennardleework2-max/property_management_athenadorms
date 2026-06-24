<?php
/**
 * Utility Model
 * Athena Dorms Property Management System
 * Handles utility billing and allocation operations
 */

class UtilityModel
{
    private $db;

    public function __construct()
    {
        $this->db = getDbConnection();
    }

    /**
     * Get all utilities
     * @param int $propertyRecid Optional property filter
     * @param string $utilityType Optional type filter
     * @return array
     */
    public function getAll($propertyRecid = null, $utilityType = '')
    {
        $sql = "SELECT u.*, p.property_name, r.room_name,
                       (SELECT COUNT(*) FROM trn_utilityfile2 ua WHERE ua.utility_recid = u.recid) as allocation_count
                FROM trn_utilityfile1 u
                JOIN mf_propertyfile p ON p.recid = u.property_recid
                JOIN mf_roomfile r ON r.recid = u.room_recid
                WHERE 1=1";

        $params = [];

        if (!empty($propertyRecid)) {
            $sql .= " AND u.property_recid = :property_recid";
            $params['property_recid'] = $propertyRecid;
        }

        if (!empty($utilityType)) {
            $sql .= " AND u.utility_type = :utility_type";
            $params['utility_type'] = $utilityType;
        }

        $sql .= " ORDER BY u.billing_month DESC, p.property_name, r.room_name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Get utility by recid
     * @param int $recid
     * @return array|false
     */
    public function getById($recid)
    {
        $sql = "SELECT u.*, p.property_name, r.room_name
                FROM trn_utilityfile1 u
                JOIN mf_propertyfile p ON p.recid = u.property_recid
                JOIN mf_roomfile r ON r.recid = u.room_recid
                WHERE u.recid = :recid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['recid' => $recid]);

        return $stmt->fetch();
    }

    /**
     * Get utility allocations
     * @param int $utilityRecid
     * @return array
     */
    public function getAllocations($utilityRecid)
    {
        $sql = "SELECT ua.*, t.tenant_name
                FROM trn_utilityfile2 ua
                JOIN mf_tenantfile t ON t.recid = ua.tenant_recid
                WHERE ua.utility_recid = :utility_recid
                ORDER BY t.tenant_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['utility_recid' => $utilityRecid]);

        return $stmt->fetchAll();
    }

    /**
     * Add new utility header
     * @param array $data
     * @return int|false Last insert recid
     */
    public function add($data)
    {
        $sql = "INSERT INTO trn_utilityfile1
                (utility_id, property_recid, room_recid, utility_type, billing_month,
                 previous_reading, current_reading, consumption, rate, total_amount, split_method, utility_status, remarks)
                VALUES
                (:utility_id, :property_recid, :room_recid, :utility_type, :billing_month,
                 :previous_reading, :current_reading, :consumption, :rate, :total_amount, :split_method, :utility_status, :remarks)";

        $stmt = $this->db->prepare($sql);

        $consumption = ($data['current_reading'] ?? 0) - ($data['previous_reading'] ?? 0);
        $rate = $data['rate'] ?? 10.00;
        $totalAmount = $consumption * $rate;

        $result = $stmt->execute([
            'utility_id' => $data['utility_id'],
            'property_recid' => $data['property_recid'],
            'room_recid' => $data['room_recid'],
            'utility_type' => $data['utility_type'],
            'billing_month' => $data['billing_month'],
            'previous_reading' => $data['previous_reading'] ?? 0,
            'current_reading' => $data['current_reading'] ?? 0,
            'consumption' => $consumption,
            'rate' => $rate,
            'total_amount' => $totalAmount,
            'split_method' => $data['split_method'] ?? 'equal_active_tenants',
            'utility_status' => $data['utility_status'] ?? 'draft',
            'remarks' => $data['remarks'] ?? null
        ]);

        return $result ? (int)$this->db->lastInsertId() : false;
    }

    /**
     * Update utility header
     * @param int $recid
     * @param array $data
     * @return bool
     */
    public function update($recid, $data)
    {
        $sql = "UPDATE trn_utilityfile1 SET
                property_recid = :property_recid,
                room_recid = :room_recid,
                utility_type = :utility_type,
                billing_month = :billing_month,
                previous_reading = :previous_reading,
                current_reading = :current_reading,
                consumption = :consumption,
                rate = :rate,
                total_amount = :total_amount,
                split_method = :split_method,
                utility_status = :utility_status,
                remarks = :remarks,
                date_updated = NOW()
                WHERE recid = :recid";

        $stmt = $this->db->prepare($sql);

        $consumption = ($data['current_reading'] ?? 0) - ($data['previous_reading'] ?? 0);
        $rate = $data['rate'] ?? 10.00;
        $totalAmount = $consumption * $rate;

        return $stmt->execute([
            'recid' => $recid,
            'property_recid' => $data['property_recid'],
            'room_recid' => $data['room_recid'],
            'utility_type' => $data['utility_type'],
            'billing_month' => $data['billing_month'],
            'previous_reading' => $data['previous_reading'] ?? 0,
            'current_reading' => $data['current_reading'] ?? 0,
            'consumption' => $consumption,
            'rate' => $rate,
            'total_amount' => $totalAmount,
            'split_method' => $data['split_method'] ?? 'equal_active_tenants',
            'utility_status' => $data['utility_status'] ?? 'draft',
            'remarks' => $data['remarks'] ?? null
        ]);
    }

    /**
     * Add utility allocation
     * @param array $data
     * @return bool
     */
    public function addAllocation($data)
    {
        $sql = "INSERT INTO trn_utilityfile2
                (allocation_id, utility_recid, tenant_recid, lease_recid, base_amount, adjustment_amount, remarks)
                VALUES
                (:allocation_id, :utility_recid, :tenant_recid, :lease_recid, :base_amount, :adjustment_amount, :remarks)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'allocation_id' => $data['allocation_id'],
            'utility_recid' => $data['utility_recid'],
            'tenant_recid' => $data['tenant_recid'],
            'lease_recid' => $data['lease_recid'],
            'base_amount' => $data['base_amount'] ?? 0,
            'adjustment_amount' => $data['adjustment_amount'] ?? 0,
            'remarks' => $data['remarks'] ?? null
        ]);
    }

    /**
     * Update utility allocation
     * @param int $recid
     * @param array $data
     * @return bool
     */
    public function updateAllocation($recid, $data)
    {
        $sql = "UPDATE trn_utilityfile2 SET
                base_amount = :base_amount,
                adjustment_amount = :adjustment_amount,
                remarks = :remarks,
                date_updated = NOW()
                WHERE recid = :recid";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'recid' => $recid,
            'base_amount' => $data['base_amount'] ?? 0,
            'adjustment_amount' => $data['adjustment_amount'] ?? 0,
            'remarks' => $data['remarks'] ?? null
        ]);
    }

    /**
     * Delete utility allocations by utility
     * @param int $utilityRecid
     * @return bool
     */
    public function deleteAllocations($utilityRecid)
    {
        $sql = "DELETE FROM trn_utilityfile2 WHERE utility_recid = :utility_recid";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['utility_recid' => $utilityRecid]);
    }

    /**
     * Delete utility
     * @param int $recid
     * @return bool
     */
    public function delete($recid)
    {
        // Allocations will be deleted by cascade
        $sql = "DELETE FROM trn_utilityfile1 WHERE recid = :recid";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['recid' => $recid]);
    }

    /**
     * Get next utility ID
     * @return string
     */
    public function getNextId()
    {
        $sql = "SELECT MAX(CAST(utility_id AS INTEGER)) as max_id FROM trn_utilityfile1";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();

        $maxId = $result['max_id'] ?? 0;

        return generateNextId($maxId);
    }

    /**
     * Get next allocation ID
     * @return string
     */
    public function getNextAllocationId()
    {
        $sql = "SELECT MAX(CAST(allocation_id AS INTEGER)) as max_id FROM trn_utilityfile2";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();

        $maxId = $result['max_id'] ?? 0;

        return generateNextId($maxId);
    }

    /**
     * Update utility status
     * @param int $recid
     * @param string $status
     * @return bool
     */
    public function updateStatus($recid, $status)
    {
        $sql = "UPDATE trn_utilityfile1 SET utility_status = :status, date_updated = NOW() WHERE recid = :recid";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['recid' => $recid, 'status' => $status]);
    }
}
