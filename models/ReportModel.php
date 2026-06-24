<?php
/**
 * Report Model
 * Athena Dorms Property Management System
 * Handles report data retrieval from views
 */

class ReportModel
{
    private $db;

    public function __construct()
    {
        $this->db = getDbConnection();
    }

    /**
     * Get tenant balances
     * @param string $billingMonth Optional filter
     * @param string $status Optional status filter
     * @return array
     */
    public function getTenantBalances($billingMonth = '', $status = '')
    {
        $sql = "SELECT * FROM view_tenant_balance WHERE 1=1";

        $params = [];

        if (!empty($billingMonth)) {
            $sql .= " AND billing_month = :billing_month";
            $params['billing_month'] = $billingMonth;
        }

        if (!empty($status)) {
            $sql .= " AND display_status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY tenant_name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Get pending payments
     * @return array
     */
    public function getPendingPayments()
    {
        $sql = "SELECT * FROM view_pending_payment ORDER BY payment_date ASC";
        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    /**
     * Get overdue tenants
     * @return array
     */
    public function getOverdueTenants()
    {
        $sql = "SELECT * FROM view_overdue_tenant ORDER BY due_date ASC";
        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    /**
     * Get utility summary
     * @param string $billingMonth Optional filter
     * @param int $propertyRecid Optional property filter
     * @return array
     */
    public function getUtilitySummary($billingMonth = '', $propertyRecid = null)
    {
        $sql = "SELECT * FROM view_utility_summary WHERE 1=1";

        $params = [];

        if (!empty($billingMonth)) {
            $sql .= " AND billing_month = :billing_month";
            $params['billing_month'] = $billingMonth;
        }

        if (!empty($propertyRecid)) {
            // Need to join back to get property_recid filter
            // For now, filter by property_name would require subquery
        }

        $sql .= " ORDER BY billing_month DESC, property_name, room_name, tenant_name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Get expiring contracts
     * @param string $alertStatus Optional alert status filter
     * @return array
     */
    public function getExpiringContracts($alertStatus = '')
    {
        $sql = "SELECT * FROM view_expiring_contract WHERE 1=1";

        $params = [];

        if (!empty($alertStatus)) {
            $sql .= " AND contract_alert_status = :alert_status";
            $params['alert_status'] = $alertStatus;
        }

        $sql .= " ORDER BY end_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Get collection summary by month
     * @param string $billingMonth
     * @return array
     */
    public function getCollectionSummary($billingMonth)
    {
        $sql = "SELECT
                    COUNT(DISTINCT tenant_recid) as total_tenants,
                    COALESCE(SUM(total_due), 0) as total_due,
                    COALESCE(SUM(verified_paid), 0) as total_verified,
                    COALESCE(SUM(pending_payment), 0) as total_pending,
                    COALESCE(SUM(total_balance), 0) as total_balance,
                    SUM(CASE WHEN display_status = 'paid' THEN 1 ELSE 0 END) as paid_count,
                    SUM(CASE WHEN display_status = 'partial' THEN 1 ELSE 0 END) as partial_count,
                    SUM(CASE WHEN display_status = 'pending_verification' THEN 1 ELSE 0 END) as pending_count,
                    SUM(CASE WHEN display_status = 'overdue' THEN 1 ELSE 0 END) as overdue_count,
                    SUM(CASE WHEN display_status = 'unpaid' THEN 1 ELSE 0 END) as unpaid_count
                FROM view_tenant_balance
                WHERE billing_month = :billing_month";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['billing_month' => $billingMonth]);

        return $stmt->fetch();
    }

    /**
     * Get available billing months
     * @return array
     */
    public function getBillingMonths()
    {
        // Query directly from bills table for better performance
        try {
            $sql = "SELECT DISTINCT billing_month FROM trn_rentbillfile1 ORDER BY billing_month DESC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("getBillingMonths error: " . $e->getMessage());
            return [];
        }
    }
}
