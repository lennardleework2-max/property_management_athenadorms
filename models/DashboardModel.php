<?php
/**
 * Dashboard Model
 * Athena Dorms Property Management System
 * Retrieves dashboard summary data
 */

class DashboardModel
{
    private $db;

    public function __construct()
    {
        $this->db = getDbConnection();
    }

    /**
     * Get dashboard summary statistics
     * @return array
     */
    public function getSummary()
    {
        $sql = "SELECT * FROM view_dashboard";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();

        if (!$result) {
            // Return default values if no data
            return [
                'active_billed_tenants' => 0,
                'total_expected_collection' => 0,
                'total_verified_collected' => 0,
                'total_pending_verification' => 0,
                'total_outstanding_balance' => 0,
                'tenants_pending_verification' => 0,
                'overdue_tenants' => 0,
                'partial_payment_tenants' => 0,
                'fully_paid_tenants' => 0,
                'contracts_expiring_soon' => 0
            ];
        }

        return $result;
    }

    /**
     * Get pending payment verifications
     * @param int $limit
     * @return array
     */
    public function getPendingPayments($limit = 10)
    {
        $sql = "SELECT * FROM view_pending_payment LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get overdue tenants
     * @param int $limit
     * @return array
     */
    public function getOverdueTenants($limit = 10)
    {
        $sql = "SELECT * FROM view_overdue_tenant ORDER BY due_date ASC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get tenant balances
     * @param int $limit
     * @return array
     */
    public function getTenantBalances($limit = 20)
    {
        $sql = "SELECT * FROM view_tenant_balance ORDER BY total_balance DESC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get expiring contracts
     * @param int $limit
     * @return array
     */
    public function getExpiringContracts($limit = 10)
    {
        $sql = "SELECT * FROM view_expiring_contract
                WHERE contract_alert_status IN ('expiring_soon', 'expired')
                ORDER BY end_date ASC
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get quick stats for property counts
     * @return array
     */
    public function getPropertyStats()
    {
        $stats = [];

        // Total properties
        $sql = "SELECT COUNT(*) as count FROM mf_propertyfile WHERE property_status = 'active'";
        $stmt = $this->db->query($sql);
        $stats['total_properties'] = $stmt->fetch()['count'];

        // Total rooms
        $sql = "SELECT COUNT(*) as count FROM mf_roomfile WHERE room_status = 'active'";
        $stmt = $this->db->query($sql);
        $stats['total_rooms'] = $stmt->fetch()['count'];

        // Total bedspaces
        $sql = "SELECT COUNT(*) as count FROM mf_bedspacefile";
        $stmt = $this->db->query($sql);
        $stats['total_bedspaces'] = $stmt->fetch()['count'];

        // Occupied bedspaces
        $sql = "SELECT COUNT(*) as count FROM mf_bedspacefile WHERE bedspace_status = 'occupied'";
        $stmt = $this->db->query($sql);
        $stats['occupied_bedspaces'] = $stmt->fetch()['count'];

        // Available bedspaces
        $sql = "SELECT COUNT(*) as count FROM mf_bedspacefile WHERE bedspace_status = 'available'";
        $stmt = $this->db->query($sql);
        $stats['available_bedspaces'] = $stmt->fetch()['count'];

        // Active tenants
        $sql = "SELECT COUNT(*) as count FROM mf_tenantfile WHERE tenant_status = 'active'";
        $stmt = $this->db->query($sql);
        $stats['active_tenants'] = $stmt->fetch()['count'];

        return $stats;
    }
}
