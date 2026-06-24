<?php
/**
 * Tenant Model
 * Athena Dorms Property Management System
 * Handles tenant CRUD operations
 */

class TenantModel
{
    private $db;

    public function __construct()
    {
        $this->db = getDbConnection();
    }

    /**
     * Get all tenants
     * @param string $search Optional search term
     * @param string $status Optional status filter
     * @return array
     */
    public function getAll($search = '', $status = '')
    {
        $sql = "SELECT t.*,
                       l.room_recid, l.bedspace_recid, l.monthly_rent, l.lease_status,
                       p.property_name, r.room_name, b.bedspace_name
                FROM mf_tenantfile t
                LEFT JOIN trn_leasefile1 l ON l.tenant_recid = t.recid AND l.lease_status = 'active'
                LEFT JOIN mf_propertyfile p ON p.recid = l.property_recid
                LEFT JOIN mf_roomfile r ON r.recid = l.room_recid
                LEFT JOIN mf_bedspacefile b ON b.recid = l.bedspace_recid
                WHERE 1=1";

        $params = [];

        if (!empty($search)) {
            $sql .= " AND (t.tenant_name LIKE :search OR t.phone_no LIKE :search OR t.email LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        if (!empty($status)) {
            $sql .= " AND t.tenant_status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY t.tenant_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Get tenant by recid
     * @param int $recid
     * @return array|false
     */
    public function getById($recid)
    {
        $sql = "SELECT * FROM mf_tenantfile WHERE recid = :recid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['recid' => $recid]);

        return $stmt->fetch();
    }

    /**
     * Get tenant by tenant_id
     * @param string $tenantId
     * @return array|false
     */
    public function getByTenantId($tenantId)
    {
        $sql = "SELECT * FROM mf_tenantfile WHERE tenant_id = :tenant_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId]);

        return $stmt->fetch();
    }

    /**
     * Get active tenants for dropdowns
     * @return array
     */
    public function getActiveTenants()
    {
        $sql = "SELECT recid, tenant_id, tenant_name FROM mf_tenantfile
                WHERE tenant_status = 'active'
                ORDER BY tenant_name";
        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    /**
     * Add new tenant
     * @param array $data
     * @return bool
     */
    public function add($data)
    {
        $sql = "INSERT INTO mf_tenantfile
                (tenant_id, tenant_name, phone_no, email, emergency_contact_name,
                 emergency_contact_no, tenant_status, move_in_date, move_out_date, remarks)
                VALUES
                (:tenant_id, :tenant_name, :phone_no, :email, :emergency_contact_name,
                 :emergency_contact_no, :tenant_status, :move_in_date, :move_out_date, :remarks)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'tenant_id' => $data['tenant_id'],
            'tenant_name' => $data['tenant_name'],
            'phone_no' => $data['phone_no'] ?? null,
            'email' => $data['email'] ?? null,
            'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
            'emergency_contact_no' => $data['emergency_contact_no'] ?? null,
            'tenant_status' => $data['tenant_status'] ?? 'active',
            'move_in_date' => !empty($data['move_in_date']) ? $data['move_in_date'] : null,
            'move_out_date' => !empty($data['move_out_date']) ? $data['move_out_date'] : null,
            'remarks' => $data['remarks'] ?? null
        ]);
    }

    /**
     * Update tenant
     * @param int $recid
     * @param array $data
     * @return bool
     */
    public function update($recid, $data)
    {
        $sql = "UPDATE mf_tenantfile SET
                tenant_name = :tenant_name,
                phone_no = :phone_no,
                email = :email,
                emergency_contact_name = :emergency_contact_name,
                emergency_contact_no = :emergency_contact_no,
                tenant_status = :tenant_status,
                move_in_date = :move_in_date,
                move_out_date = :move_out_date,
                remarks = :remarks,
                date_updated = NOW()
                WHERE recid = :recid";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'recid' => $recid,
            'tenant_name' => $data['tenant_name'],
            'phone_no' => $data['phone_no'] ?? null,
            'email' => $data['email'] ?? null,
            'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
            'emergency_contact_no' => $data['emergency_contact_no'] ?? null,
            'tenant_status' => $data['tenant_status'] ?? 'active',
            'move_in_date' => !empty($data['move_in_date']) ? $data['move_in_date'] : null,
            'move_out_date' => !empty($data['move_out_date']) ? $data['move_out_date'] : null,
            'remarks' => $data['remarks'] ?? null
        ]);
    }

    /**
     * Delete tenant
     * @param int $recid
     * @return bool
     */
    public function delete($recid)
    {
        // Check if tenant has active leases
        $sql = "SELECT COUNT(*) as count FROM trn_leasefile1
                WHERE tenant_recid = :recid AND lease_status = 'active'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['recid' => $recid]);
        $result = $stmt->fetch();

        if ($result['count'] > 0) {
            throw new Exception("Cannot delete tenant with active lease.");
        }

        $sql = "DELETE FROM mf_tenantfile WHERE recid = :recid";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['recid' => $recid]);
    }

    /**
     * Get next tenant ID
     * @return string
     */
    public function getNextId()
    {
        $sql = "SELECT MAX(CAST(tenant_id AS INTEGER)) as max_id FROM mf_tenantfile";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();

        $maxId = $result['max_id'] ?? 0;

        return generateNextId($maxId);
    }
}
