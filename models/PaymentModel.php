<?php
/**
 * Payment Model
 * Athena Dorms Property Management System
 * Handles payment CRUD and verification operations
 */

class PaymentModel
{
    private $db;

    public function __construct()
    {
        $this->db = getDbConnection();
    }

    /**
     * Get all payments
     * @param string $status Optional status filter
     * @param string $search Optional search term
     * @return array
     */
    public function getAll($status = '', $search = '')
    {
        $sql = "SELECT pay.*, t.tenant_name, t.phone_no,
                       p.property_name, r.room_name, b.bedspace_name,
                       u.full_name as verified_by_name
                FROM trn_paymentfile1 pay
                JOIN mf_tenantfile t ON t.recid = pay.tenant_recid
                JOIN trn_leasefile1 l ON l.recid = pay.lease_recid
                JOIN mf_propertyfile p ON p.recid = l.property_recid
                JOIN mf_roomfile r ON r.recid = l.room_recid
                LEFT JOIN mf_bedspacefile b ON b.recid = l.bedspace_recid
                LEFT JOIN utl_userfile u ON u.recid = pay.verified_by_recid
                WHERE 1=1";

        $params = [];

        if (!empty($status)) {
            $sql .= " AND pay.payment_status = :status";
            $params['status'] = $status;
        }

        if (!empty($search)) {
            $sql .= " AND (t.tenant_name LIKE :search OR pay.reference_no LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY pay.payment_date DESC, pay.recid DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Get payment by recid
     * @param int $recid
     * @return array|false
     */
    public function getById($recid)
    {
        $sql = "SELECT pay.*, t.tenant_name, t.phone_no,
                       p.property_name, r.room_name, b.bedspace_name,
                       u.full_name as verified_by_name
                FROM trn_paymentfile1 pay
                JOIN mf_tenantfile t ON t.recid = pay.tenant_recid
                JOIN trn_leasefile1 l ON l.recid = pay.lease_recid
                JOIN mf_propertyfile p ON p.recid = l.property_recid
                JOIN mf_roomfile r ON r.recid = l.room_recid
                LEFT JOIN mf_bedspacefile b ON b.recid = l.bedspace_recid
                LEFT JOIN utl_userfile u ON u.recid = pay.verified_by_recid
                WHERE pay.recid = :recid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['recid' => $recid]);

        return $stmt->fetch();
    }

    /**
     * Get pending verifications
     * @return array
     */
    public function getPendingVerifications()
    {
        return $this->getAll('pending_verification');
    }

    /**
     * Add new payment
     * @param array $data
     * @return bool
     */
    public function add($data)
    {
        $sql = "INSERT INTO trn_paymentfile1
                (payment_id, tenant_recid, lease_recid, bill_recid, payment_amount, payment_date,
                 payment_method, reference_no, check_no, check_date, bank_name, proof_url,
                 payment_status, remarks)
                VALUES
                (:payment_id, :tenant_recid, :lease_recid, :bill_recid, :payment_amount, :payment_date,
                 :payment_method, :reference_no, :check_no, :check_date, :bank_name, :proof_url,
                 :payment_status, :remarks)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'payment_id' => $data['payment_id'],
            'tenant_recid' => $data['tenant_recid'],
            'lease_recid' => $data['lease_recid'],
            'bill_recid' => !empty($data['bill_recid']) ? $data['bill_recid'] : null,
            'payment_amount' => $data['payment_amount'],
            'payment_date' => $data['payment_date'],
            'payment_method' => $data['payment_method'],
            'reference_no' => $data['reference_no'] ?? null,
            'check_no' => $data['check_no'] ?? null,
            'check_date' => !empty($data['check_date']) ? $data['check_date'] : null,
            'bank_name' => $data['bank_name'] ?? null,
            'proof_url' => $data['proof_url'] ?? null,
            'payment_status' => $data['payment_status'] ?? 'pending_verification',
            'remarks' => $data['remarks'] ?? null
        ]);
    }

    /**
     * Update payment
     * @param int $recid
     * @param array $data
     * @return bool
     */
    public function update($recid, $data)
    {
        $sql = "UPDATE trn_paymentfile1 SET
                tenant_recid = :tenant_recid,
                lease_recid = :lease_recid,
                bill_recid = :bill_recid,
                payment_amount = :payment_amount,
                payment_date = :payment_date,
                payment_method = :payment_method,
                reference_no = :reference_no,
                check_no = :check_no,
                check_date = :check_date,
                bank_name = :bank_name,
                proof_url = :proof_url,
                remarks = :remarks,
                date_updated = NOW()
                WHERE recid = :recid";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'recid' => $recid,
            'tenant_recid' => $data['tenant_recid'],
            'lease_recid' => $data['lease_recid'],
            'bill_recid' => !empty($data['bill_recid']) ? $data['bill_recid'] : null,
            'payment_amount' => $data['payment_amount'],
            'payment_date' => $data['payment_date'],
            'payment_method' => $data['payment_method'],
            'reference_no' => $data['reference_no'] ?? null,
            'check_no' => $data['check_no'] ?? null,
            'check_date' => !empty($data['check_date']) ? $data['check_date'] : null,
            'bank_name' => $data['bank_name'] ?? null,
            'proof_url' => $data['proof_url'] ?? null,
            'remarks' => $data['remarks'] ?? null
        ]);
    }

    /**
     * Verify payment
     * @param int $recid
     * @param int $verifiedByRecid
     * @return bool
     */
    public function verify($recid, $verifiedByRecid)
    {
        $sql = "UPDATE trn_paymentfile1 SET
                payment_status = 'verified',
                verified_by_recid = :verified_by,
                verified_at = NOW(),
                date_updated = NOW()
                WHERE recid = :recid";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'recid' => $recid,
            'verified_by' => $verifiedByRecid
        ]);
    }

    /**
     * Reject payment
     * @param int $recid
     * @param int $verifiedByRecid
     * @param string $reason
     * @return bool
     */
    public function reject($recid, $verifiedByRecid, $reason)
    {
        $sql = "UPDATE trn_paymentfile1 SET
                payment_status = 'rejected',
                verified_by_recid = :verified_by,
                verified_at = NOW(),
                rejection_reason = :reason,
                date_updated = NOW()
                WHERE recid = :recid";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'recid' => $recid,
            'verified_by' => $verifiedByRecid,
            'reason' => $reason
        ]);
    }

    /**
     * Mark payment as cleared (for checks)
     * @param int $recid
     * @param int $verifiedByRecid
     * @return bool
     */
    public function markCleared($recid, $verifiedByRecid)
    {
        $sql = "UPDATE trn_paymentfile1 SET
                payment_status = 'cleared',
                verified_by_recid = :verified_by,
                verified_at = NOW(),
                date_updated = NOW()
                WHERE recid = :recid";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'recid' => $recid,
            'verified_by' => $verifiedByRecid
        ]);
    }

    /**
     * Mark payment as bounced (for checks)
     * @param int $recid
     * @param int $verifiedByRecid
     * @param string $reason
     * @return bool
     */
    public function markBounced($recid, $verifiedByRecid, $reason)
    {
        $sql = "UPDATE trn_paymentfile1 SET
                payment_status = 'bounced',
                verified_by_recid = :verified_by,
                verified_at = NOW(),
                rejection_reason = :reason,
                date_updated = NOW()
                WHERE recid = :recid";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'recid' => $recid,
            'verified_by' => $verifiedByRecid,
            'reason' => $reason
        ]);
    }

    /**
     * Update proof URL
     * @param int $recid
     * @param string $proofUrl
     * @return bool
     */
    public function updateProofUrl($recid, $proofUrl)
    {
        $sql = "UPDATE trn_paymentfile1 SET proof_url = :proof_url, date_updated = NOW() WHERE recid = :recid";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['recid' => $recid, 'proof_url' => $proofUrl]);
    }

    /**
     * Delete payment
     * @param int $recid
     * @return bool
     */
    public function delete($recid)
    {
        $sql = "DELETE FROM trn_paymentfile1 WHERE recid = :recid";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['recid' => $recid]);
    }

    /**
     * Get next payment ID
     * @return string
     */
    public function getNextId()
    {
        $sql = "SELECT MAX(CAST(payment_id AS INTEGER)) as max_id FROM trn_paymentfile1";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();

        $maxId = $result['max_id'] ?? 0;

        return generateNextId($maxId);
    }
}
