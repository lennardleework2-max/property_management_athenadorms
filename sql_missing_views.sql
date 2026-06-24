-- Missing Views for MySQL/MariaDB
-- Athena Dorms Property Management System
-- Run this SQL after importing the main database

-- ============================================
-- VIEW: view_tenant_balance
-- Shows tenant billing and payment status
-- ============================================
DROP VIEW IF EXISTS `view_tenant_balance`;
CREATE VIEW `view_tenant_balance` AS
SELECT
    b.recid as bill_recid,
    b.bill_id,
    b.billing_month,
    b.due_date,
    l.tenant_recid,
    t.tenant_name,
    t.phone_no,
    p.property_name,
    r.room_name,
    bs.bedspace_name,
    b.total_due,
    COALESCE((
        SELECT SUM(pay.payment_amount)
        FROM trn_paymentfile1 pay
        WHERE pay.bill_recid = b.recid
        AND pay.payment_status = 'verified'
    ), 0) as verified_paid,
    COALESCE((
        SELECT SUM(pay.payment_amount)
        FROM trn_paymentfile1 pay
        WHERE pay.bill_recid = b.recid
        AND pay.payment_status = 'pending_verification'
    ), 0) as pending_payment,
    b.total_due - COALESCE((
        SELECT SUM(pay.payment_amount)
        FROM trn_paymentfile1 pay
        WHERE pay.bill_recid = b.recid
        AND pay.payment_status = 'verified'
    ), 0) as total_balance,
    CASE
        WHEN b.total_due <= COALESCE((
            SELECT SUM(pay.payment_amount)
            FROM trn_paymentfile1 pay
            WHERE pay.bill_recid = b.recid
            AND pay.payment_status = 'verified'
        ), 0) THEN 'paid'
        WHEN COALESCE((
            SELECT SUM(pay.payment_amount)
            FROM trn_paymentfile1 pay
            WHERE pay.bill_recid = b.recid
            AND pay.payment_status = 'pending_verification'
        ), 0) > 0 THEN 'pending_verification'
        WHEN COALESCE((
            SELECT SUM(pay.payment_amount)
            FROM trn_paymentfile1 pay
            WHERE pay.bill_recid = b.recid
            AND pay.payment_status = 'verified'
        ), 0) > 0 THEN 'partial'
        WHEN b.due_date < CURDATE() THEN 'overdue'
        ELSE 'unpaid'
    END as display_status
FROM trn_rentbillfile1 b
JOIN trn_leasefile1 l ON l.recid = b.lease_recid
JOIN mf_tenantfile t ON t.recid = l.tenant_recid
JOIN mf_propertyfile p ON p.recid = l.property_recid
JOIN mf_roomfile r ON r.recid = l.room_recid
LEFT JOIN mf_bedspacefile bs ON bs.recid = l.bedspace_recid
ORDER BY b.billing_month DESC, t.tenant_name;

-- ============================================
-- VIEW: view_overdue_tenant
-- Shows tenants with overdue bills
-- ============================================
DROP VIEW IF EXISTS `view_overdue_tenant`;
CREATE VIEW `view_overdue_tenant` AS
SELECT
    b.recid as bill_recid,
    b.bill_id,
    b.billing_month,
    b.due_date,
    DATEDIFF(CURDATE(), b.due_date) as days_overdue,
    l.tenant_recid,
    t.tenant_name,
    t.phone_no,
    t.email,
    p.property_name,
    r.room_name,
    bs.bedspace_name,
    b.total_due,
    COALESCE((
        SELECT SUM(pay.payment_amount)
        FROM trn_paymentfile1 pay
        WHERE pay.bill_recid = b.recid
        AND pay.payment_status = 'verified'
    ), 0) as verified_paid,
    b.total_due - COALESCE((
        SELECT SUM(pay.payment_amount)
        FROM trn_paymentfile1 pay
        WHERE pay.bill_recid = b.recid
        AND pay.payment_status = 'verified'
    ), 0) as total_balance
FROM trn_rentbillfile1 b
JOIN trn_leasefile1 l ON l.recid = b.lease_recid
JOIN mf_tenantfile t ON t.recid = l.tenant_recid
JOIN mf_propertyfile p ON p.recid = l.property_recid
JOIN mf_roomfile r ON r.recid = l.room_recid
LEFT JOIN mf_bedspacefile bs ON bs.recid = l.bedspace_recid
WHERE b.due_date < CURDATE()
AND b.total_due > COALESCE((
    SELECT SUM(pay.payment_amount)
    FROM trn_paymentfile1 pay
    WHERE pay.bill_recid = b.recid
    AND pay.payment_status = 'verified'
), 0)
ORDER BY b.due_date ASC;

-- ============================================
-- VIEW: view_dashboard
-- Summary statistics for dashboard
-- ============================================
DROP VIEW IF EXISTS `view_dashboard`;
CREATE VIEW `view_dashboard` AS
SELECT
    (SELECT COUNT(*) FROM mf_tenantfile WHERE tenant_status = 'active') as active_billed_tenants,

    (SELECT COALESCE(SUM(total_due), 0)
     FROM trn_rentbillfile1 b
     WHERE DATE_FORMAT(b.billing_month, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
    ) as total_expected_collection,

    (SELECT COALESCE(SUM(pay.payment_amount), 0)
     FROM trn_paymentfile1 pay
     WHERE pay.payment_status = 'verified'
     AND DATE_FORMAT(pay.payment_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
    ) as total_verified_collected,

    (SELECT COALESCE(SUM(pay.payment_amount), 0)
     FROM trn_paymentfile1 pay
     WHERE pay.payment_status = 'pending_verification'
    ) as total_pending_verification,

    (SELECT COALESCE(SUM(b.total_due), 0) - COALESCE(SUM((
        SELECT COALESCE(SUM(pay.payment_amount), 0)
        FROM trn_paymentfile1 pay
        WHERE pay.bill_recid = b.recid
        AND pay.payment_status = 'verified'
    )), 0)
     FROM trn_rentbillfile1 b
    ) as total_outstanding_balance,

    (SELECT COUNT(*)
     FROM trn_paymentfile1
     WHERE payment_status = 'pending_verification'
    ) as tenants_pending_verification,

    (SELECT COUNT(DISTINCT b.lease_recid)
     FROM trn_rentbillfile1 b
     WHERE b.due_date < CURDATE()
     AND b.total_due > COALESCE((
         SELECT SUM(pay.payment_amount)
         FROM trn_paymentfile1 pay
         WHERE pay.bill_recid = b.recid
         AND pay.payment_status = 'verified'
     ), 0)
    ) as overdue_tenants,

    (SELECT COUNT(DISTINCT b.lease_recid)
     FROM trn_rentbillfile1 b
     WHERE COALESCE((
         SELECT SUM(pay.payment_amount)
         FROM trn_paymentfile1 pay
         WHERE pay.bill_recid = b.recid
         AND pay.payment_status = 'verified'
     ), 0) > 0
     AND COALESCE((
         SELECT SUM(pay.payment_amount)
         FROM trn_paymentfile1 pay
         WHERE pay.bill_recid = b.recid
         AND pay.payment_status = 'verified'
     ), 0) < b.total_due
    ) as partial_payment_tenants,

    (SELECT COUNT(DISTINCT b.lease_recid)
     FROM trn_rentbillfile1 b
     WHERE b.total_due <= COALESCE((
         SELECT SUM(pay.payment_amount)
         FROM trn_paymentfile1 pay
         WHERE pay.bill_recid = b.recid
         AND pay.payment_status = 'verified'
     ), 0)
    ) as fully_paid_tenants,

    (SELECT COUNT(*)
     FROM trn_leasefile1
     WHERE end_date IS NOT NULL
     AND end_date <= DATE_ADD(CURDATE(), INTERVAL 60 DAY)
     AND end_date >= CURDATE()
     AND lease_status = 'active'
    ) as contracts_expiring_soon;
