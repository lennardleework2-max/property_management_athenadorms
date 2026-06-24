<?php
/**
 * Payment Controller
 * Athena Dorms Property Management System
 * Handles payment CRUD and verification operations
 * This is one of the most critical features
 */

class PaymentController
{
    private $paymentModel;
    private $leaseModel;
    private $tenantModel;

    public function __construct()
    {
        require_once MODELS_PATH . '/PaymentModel.php';
        require_once MODELS_PATH . '/LeaseModel.php';
        require_once MODELS_PATH . '/TenantModel.php';

        $this->paymentModel = new PaymentModel();
        $this->leaseModel = new LeaseModel();
        $this->tenantModel = new TenantModel();
    }

    /**
     * Display payment list
     */
    public function list()
    {
        requireLogin();

        $status = isset($_GET['status']) ? trim($_GET['status']) : '';
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        $payments = $this->paymentModel->getAll($status, $search);
        $leases = $this->leaseModel->getActiveLeases();

        // Count pending for sidebar badge
        $pendingCount = 0;
        foreach ($payments as $payment) {
            if ($payment['payment_status'] === 'pending_verification') {
                $pendingCount++;
            }
        }
        $_SESSION['pending_payments'] = $pendingCount;

        renderWithLayout('payment/list', [
            'payments' => $payments,
            'leases' => $leases,
            'status' => $status,
            'search' => $search
        ], 'Payment Verification');
    }

    /**
     * Get single payment (AJAX)
     */
    public function get()
    {
        requireLogin();

        $recid = isset($_POST['recid']) ? (int)$_POST['recid'] : 0;

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        $payment = $this->paymentModel->getById($recid);

        if (!$payment) {
            jsonResponse(['success' => false, 'message' => 'Payment not found.'], 404);
        }

        jsonResponse(['success' => true, 'data' => $payment]);
    }

    /**
     * Get all payments (AJAX)
     */
    public function getAll()
    {
        requireLogin();

        $status = isset($_POST['status']) ? trim($_POST['status']) : '';

        $payments = $this->paymentModel->getAll($status);

        jsonResponse(['success' => true, 'data' => $payments]);
    }

    /**
     * Add payment (AJAX)
     */
    public function add()
    {
        requireLogin();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        if (empty($_POST['lease_recid'])) {
            jsonResponse(['success' => false, 'message' => 'Lease/Tenant is required.']);
        }

        if (empty($_POST['payment_amount']) || $_POST['payment_amount'] <= 0) {
            jsonResponse(['success' => false, 'message' => 'Valid payment amount is required.']);
        }

        if (empty($_POST['payment_date'])) {
            jsonResponse(['success' => false, 'message' => 'Payment date is required.']);
        }

        if (empty($_POST['payment_method'])) {
            jsonResponse(['success' => false, 'message' => 'Payment method is required.']);
        }

        // Get tenant from lease
        $lease = $this->leaseModel->getById((int)$_POST['lease_recid']);
        if (!$lease) {
            jsonResponse(['success' => false, 'message' => 'Invalid lease.']);
        }

        $paymentId = $this->paymentModel->getNextId();

        try {
            $result = $this->paymentModel->add([
                'payment_id' => $paymentId,
                'tenant_recid' => $lease['tenant_recid'],
                'lease_recid' => (int)$_POST['lease_recid'],
                'bill_recid' => !empty($_POST['bill_recid']) ? (int)$_POST['bill_recid'] : null,
                'payment_amount' => (float)$_POST['payment_amount'],
                'payment_date' => $_POST['payment_date'],
                'payment_method' => $_POST['payment_method'],
                'reference_no' => trim($_POST['reference_no'] ?? ''),
                'check_no' => trim($_POST['check_no'] ?? ''),
                'check_date' => $_POST['check_date'] ?? null,
                'bank_name' => trim($_POST['bank_name'] ?? ''),
                'proof_url' => trim($_POST['proof_url'] ?? ''),
                'payment_status' => $_POST['payment_status'] ?? 'pending_verification',
                'remarks' => trim($_POST['remarks'] ?? '')
            ]);

            if ($result) {
                jsonResponse(['success' => true, 'message' => 'Payment added successfully.']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Failed to add payment.']);
            }
        } catch (Exception $e) {
            error_log("Payment add error: " . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to add payment.']);
        }
    }

    /**
     * Edit payment (AJAX)
     */
    public function edit()
    {
        requireLogin();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        $recid = isset($_POST['recid']) ? (int)$_POST['recid'] : 0;

        if (!$recid) {
            jsonResponse(['success' => false, 'message' => 'Invalid payment.']);
        }

        // Get tenant from lease
        $lease = $this->leaseModel->getById((int)$_POST['lease_recid']);
        if (!$lease) {
            jsonResponse(['success' => false, 'message' => 'Invalid lease.']);
        }

        try {
            $result = $this->paymentModel->update($recid, [
                'tenant_recid' => $lease['tenant_recid'],
                'lease_recid' => (int)$_POST['lease_recid'],
                'bill_recid' => !empty($_POST['bill_recid']) ? (int)$_POST['bill_recid'] : null,
                'payment_amount' => (float)$_POST['payment_amount'],
                'payment_date' => $_POST['payment_date'],
                'payment_method' => $_POST['payment_method'],
                'reference_no' => trim($_POST['reference_no'] ?? ''),
                'check_no' => trim($_POST['check_no'] ?? ''),
                'check_date' => $_POST['check_date'] ?? null,
                'bank_name' => trim($_POST['bank_name'] ?? ''),
                'proof_url' => trim($_POST['proof_url'] ?? ''),
                'remarks' => trim($_POST['remarks'] ?? '')
            ]);

            if ($result) {
                jsonResponse(['success' => true, 'message' => 'Payment updated successfully.']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Failed to update payment.']);
            }
        } catch (Exception $e) {
            error_log("Payment update error: " . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to update payment.']);
        }
    }

    /**
     * Verify payment (AJAX)
     */
    public function verify()
    {
        requireLogin();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        $recid = isset($_POST['recid']) ? (int)$_POST['recid'] : 0;

        if (!$recid) {
            jsonResponse(['success' => false, 'message' => 'Invalid payment.']);
        }

        $currentUser = getCurrentUser();

        try {
            $result = $this->paymentModel->verify($recid, $currentUser['recid']);

            if ($result) {
                jsonResponse(['success' => true, 'message' => 'Payment verified successfully.']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Failed to verify payment.']);
            }
        } catch (Exception $e) {
            error_log("Payment verify error: " . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to verify payment.']);
        }
    }

    /**
     * Reject payment (AJAX)
     */
    public function reject()
    {
        requireLogin();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        $recid = isset($_POST['recid']) ? (int)$_POST['recid'] : 0;
        $reason = trim($_POST['rejection_reason'] ?? '');

        if (!$recid) {
            jsonResponse(['success' => false, 'message' => 'Invalid payment.']);
        }

        if (empty($reason)) {
            jsonResponse(['success' => false, 'message' => 'Rejection reason is required.']);
        }

        $currentUser = getCurrentUser();

        try {
            $result = $this->paymentModel->reject($recid, $currentUser['recid'], $reason);

            if ($result) {
                jsonResponse(['success' => true, 'message' => 'Payment rejected.']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Failed to reject payment.']);
            }
        } catch (Exception $e) {
            error_log("Payment reject error: " . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to reject payment.']);
        }
    }

    /**
     * Delete payment (AJAX)
     */
    public function delete()
    {
        requireLogin();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        $recid = isset($_POST['recid']) ? (int)$_POST['recid'] : 0;

        if (!$recid) {
            jsonResponse(['success' => false, 'message' => 'Invalid payment.']);
        }

        try {
            $result = $this->paymentModel->delete($recid);

            if ($result) {
                jsonResponse(['success' => true, 'message' => 'Payment deleted successfully.']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Failed to delete payment.']);
            }
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Generate next ID (AJAX)
     */
    public function generateId()
    {
        requireLogin();

        $nextId = $this->paymentModel->getNextId();

        jsonResponse(['success' => true, 'data' => ['payment_id' => $nextId]]);
    }

    /**
     * Upload payment proof (AJAX)
     */
    public function uploadProof()
    {
        requireLogin();

        if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        if (!isset($_FILES['proof_file']) || $_FILES['proof_file']['error'] !== UPLOAD_ERR_OK) {
            jsonResponse(['success' => false, 'message' => 'No file uploaded or upload error.']);
        }

        $file = $_FILES['proof_file'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];

        if (!in_array($file['type'], $allowedTypes)) {
            jsonResponse(['success' => false, 'message' => 'Invalid file type. Allowed: JPG, PNG, GIF, PDF.']);
        }

        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            jsonResponse(['success' => false, 'message' => 'File too large. Max size: 5MB.']);
        }

        $uploadDir = UPLOADS_PATH . '/payment_proofs/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'proof_' . date('Ymd_His') . '_' . uniqid() . '.' . $ext;
        $filepath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $url = 'uploads/payment_proofs/' . $filename;
            jsonResponse(['success' => true, 'data' => ['url' => $url]]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Failed to save file.']);
        }
    }
}
