<?php
/**
 * Dashboard Controller
 * Athena Dorms Property Management System
 * Main dashboard with summary statistics
 */

class DashboardController
{
    private $dashboardModel;

    public function __construct()
    {
        require_once MODELS_PATH . '/DashboardModel.php';
        $this->dashboardModel = new DashboardModel();
    }

    /**
     * Display dashboard
     */
    public function index()
    {
        requireAccess('dashboard');

        // Get dashboard summary
        $summary = $this->dashboardModel->getSummary();

        // Get property stats
        $propertyStats = $this->dashboardModel->getPropertyStats();

        // Get pending payments for quick view
        $pendingPayments = $this->dashboardModel->getPendingPayments(5);

        // Store pending count in session for sidebar badge
        $_SESSION['pending_payments'] = count($pendingPayments);

        // Get overdue tenants
        $overdueTenants = $this->dashboardModel->getOverdueTenants(5);

        // Get tenant balances
        $tenantBalances = $this->dashboardModel->getTenantBalances(10);

        // Get expiring contracts
        $expiringContracts = $this->dashboardModel->getExpiringContracts(5);

        // Render dashboard
        renderWithLayout('dashboard/index', [
            'summary' => $summary,
            'propertyStats' => $propertyStats,
            'pendingPayments' => $pendingPayments,
            'overdueTenants' => $overdueTenants,
            'tenantBalances' => $tenantBalances,
            'expiringContracts' => $expiringContracts
        ], 'Dashboard');
    }
}
