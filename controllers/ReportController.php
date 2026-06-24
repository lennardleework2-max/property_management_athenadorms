<?php
/**
 * Report Controller
 * Athena Dorms Property Management System
 * Handles report generation
 */

class ReportController
{
    private $reportModel;

    public function __construct()
    {
        require_once MODELS_PATH . '/ReportModel.php';
        $this->reportModel = new ReportModel();
    }

    /**
     * Display reports index
     */
    public function index()
    {
        requireLogin();

        try {
            $billingMonths = $this->reportModel->getBillingMonths();
        } catch (Exception $e) {
            error_log("Report index error: " . $e->getMessage());
            $billingMonths = [];
        }

        renderWithLayout('report/index', [
            'billingMonths' => $billingMonths
        ], 'Reports');
    }

    /**
     * Tenant balance report (AJAX)
     */
    public function tenantBalance()
    {
        requireLogin();

        $billingMonth = isset($_POST['billing_month']) ? $_POST['billing_month'] : '';
        $status = isset($_POST['status']) ? $_POST['status'] : '';

        $balances = $this->reportModel->getTenantBalances($billingMonth, $status);
        $summary = null;

        if (!empty($billingMonth)) {
            $summary = $this->reportModel->getCollectionSummary($billingMonth);
        }

        jsonResponse([
            'success' => true,
            'data' => $balances,
            'summary' => $summary
        ]);
    }

    /**
     * Pending payment report (AJAX)
     */
    public function pendingPayment()
    {
        requireLogin();

        $payments = $this->reportModel->getPendingPayments();

        jsonResponse(['success' => true, 'data' => $payments]);
    }

    /**
     * Overdue tenants report (AJAX)
     */
    public function overdue()
    {
        requireLogin();

        $tenants = $this->reportModel->getOverdueTenants();

        jsonResponse(['success' => true, 'data' => $tenants]);
    }

    /**
     * Utility summary report (AJAX)
     */
    public function utility()
    {
        requireLogin();

        $billingMonth = isset($_POST['billing_month']) ? $_POST['billing_month'] : '';

        $utilities = $this->reportModel->getUtilitySummary($billingMonth);

        jsonResponse(['success' => true, 'data' => $utilities]);
    }

    /**
     * Expiring contracts report (AJAX)
     */
    public function expiringContract()
    {
        requireLogin();

        $contracts = $this->reportModel->getExpiringContracts();

        jsonResponse(['success' => true, 'data' => $contracts]);
    }
}
