-- ============================================
-- Athena Dorms Property Management System
-- Supabase PostgreSQL Database Setup
-- Run this script in Supabase SQL Editor
-- ============================================

-- ============================================
-- DROP EXISTING TABLES (Clean Setup)
-- Order matters due to foreign key constraints
-- ============================================

DROP TABLE IF EXISTS trn_utilityfile2 CASCADE;
DROP TABLE IF EXISTS trn_utilityfile1 CASCADE;
DROP TABLE IF EXISTS trn_paymentfile1 CASCADE;
DROP TABLE IF EXISTS trn_leasefile1 CASCADE;
DROP TABLE IF EXISTS mf_bedspacefile CASCADE;
DROP TABLE IF EXISTS mf_roomfile CASCADE;
DROP TABLE IF EXISTS mf_tenantfile CASCADE;
DROP TABLE IF EXISTS mf_propertyfile CASCADE;
DROP TABLE IF EXISTS utl_userfile CASCADE;

-- ============================================
-- CREATE TABLES
-- ============================================

-- 1. Users Table
CREATE TABLE IF NOT EXISTS utl_userfile (
    recid SERIAL PRIMARY KEY,
    user_id VARCHAR(10) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    user_role VARCHAR(50) DEFAULT 'staff' CHECK (user_role IN ('owner', 'admin', 'staff')),
    user_status VARCHAR(20) DEFAULT 'active' CHECK (user_status IN ('active', 'inactive')),
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Properties Table
CREATE TABLE IF NOT EXISTS mf_propertyfile (
    recid SERIAL PRIMARY KEY,
    property_id VARCHAR(10) UNIQUE NOT NULL,
    property_name VARCHAR(255) NOT NULL,
    property_address TEXT,
    property_type VARCHAR(50) DEFAULT 'dormitory',
    property_status VARCHAR(20) DEFAULT 'active',
    remarks TEXT,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Rooms Table
CREATE TABLE IF NOT EXISTS mf_roomfile (
    recid SERIAL PRIMARY KEY,
    room_id VARCHAR(10) UNIQUE NOT NULL,
    property_recid INTEGER REFERENCES mf_propertyfile(recid) ON DELETE RESTRICT,
    room_name VARCHAR(255) NOT NULL,
    room_type VARCHAR(50) DEFAULT 'bedspace',
    max_bedspace INTEGER DEFAULT 4,
    monthly_room_rate DECIMAL(12,2) DEFAULT 0,
    room_status VARCHAR(20) DEFAULT 'active',
    remarks TEXT,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Bedspaces Table
CREATE TABLE IF NOT EXISTS mf_bedspacefile (
    recid SERIAL PRIMARY KEY,
    bedspace_id VARCHAR(10) UNIQUE NOT NULL,
    room_recid INTEGER REFERENCES mf_roomfile(recid) ON DELETE RESTRICT,
    bedspace_name VARCHAR(100) NOT NULL,
    bedspace_status VARCHAR(20) DEFAULT 'available',
    remarks TEXT,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. Tenants Table
CREATE TABLE IF NOT EXISTS mf_tenantfile (
    recid SERIAL PRIMARY KEY,
    tenant_id VARCHAR(10) UNIQUE NOT NULL,
    tenant_name VARCHAR(255) NOT NULL,
    phone_no VARCHAR(50),
    email VARCHAR(255),
    emergency_contact_name VARCHAR(255),
    emergency_contact_no VARCHAR(50),
    tenant_status VARCHAR(20) DEFAULT 'active',
    move_in_date DATE,
    move_out_date DATE,
    remarks TEXT,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 6. Leases Table
CREATE TABLE IF NOT EXISTS trn_leasefile1 (
    recid SERIAL PRIMARY KEY,
    lease_id VARCHAR(10) UNIQUE NOT NULL,
    tenant_recid INTEGER REFERENCES mf_tenantfile(recid) ON DELETE RESTRICT,
    property_recid INTEGER REFERENCES mf_propertyfile(recid) ON DELETE RESTRICT,
    room_recid INTEGER REFERENCES mf_roomfile(recid) ON DELETE RESTRICT,
    bedspace_recid INTEGER REFERENCES mf_bedspacefile(recid) ON DELETE SET NULL,
    lease_type VARCHAR(50) DEFAULT 'monthly',
    start_date DATE NOT NULL,
    end_date DATE,
    monthly_rent DECIMAL(12,2) DEFAULT 0,
    security_deposit DECIMAL(12,2) DEFAULT 0,
    lease_status VARCHAR(20) DEFAULT 'active',
    remarks TEXT,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 7. Payments Table (with GCash support)
CREATE TABLE IF NOT EXISTS trn_paymentfile1 (
    recid SERIAL PRIMARY KEY,
    payment_id VARCHAR(10) UNIQUE NOT NULL,
    tenant_recid INTEGER REFERENCES mf_tenantfile(recid) ON DELETE RESTRICT,
    lease_recid INTEGER REFERENCES trn_leasefile1(recid) ON DELETE RESTRICT,
    bill_recid INTEGER,
    payment_amount DECIMAL(12,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'gcash',
    reference_no VARCHAR(100),
    check_no VARCHAR(50),
    check_date DATE,
    bank_name VARCHAR(100),
    proof_url TEXT,
    payment_status VARCHAR(30) DEFAULT 'pending_verification',
    verified_by_recid INTEGER REFERENCES utl_userfile(recid) ON DELETE SET NULL,
    verified_at TIMESTAMP,
    rejection_reason TEXT,
    remarks TEXT,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 8. Utilities Table
CREATE TABLE IF NOT EXISTS trn_utilityfile1 (
    recid SERIAL PRIMARY KEY,
    utility_id VARCHAR(10) UNIQUE NOT NULL,
    property_recid INTEGER REFERENCES mf_propertyfile(recid) ON DELETE RESTRICT,
    room_recid INTEGER REFERENCES mf_roomfile(recid) ON DELETE RESTRICT,
    utility_type VARCHAR(50) DEFAULT 'electricity',
    billing_month DATE NOT NULL,
    previous_reading DECIMAL(12,2) DEFAULT 0,
    current_reading DECIMAL(12,2) DEFAULT 0,
    consumption DECIMAL(12,2) DEFAULT 0,
    rate DECIMAL(12,4) DEFAULT 10.0000,
    total_amount DECIMAL(12,2) DEFAULT 0,
    split_method VARCHAR(50) DEFAULT 'equal_active_tenants',
    utility_status VARCHAR(20) DEFAULT 'draft',
    remarks TEXT,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 9. Utility Allocations Table
CREATE TABLE IF NOT EXISTS trn_utilityfile2 (
    recid SERIAL PRIMARY KEY,
    allocation_id VARCHAR(10) UNIQUE NOT NULL,
    utility_recid INTEGER REFERENCES trn_utilityfile1(recid) ON DELETE CASCADE,
    tenant_recid INTEGER REFERENCES mf_tenantfile(recid) ON DELETE RESTRICT,
    lease_recid INTEGER REFERENCES trn_leasefile1(recid) ON DELETE RESTRICT,
    base_amount DECIMAL(12,2) DEFAULT 0,
    adjustment_amount DECIMAL(12,2) DEFAULT 0,
    remarks TEXT,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- INDEXES FOR PERFORMANCE
-- ============================================

CREATE INDEX IF NOT EXISTS idx_payments_status ON trn_paymentfile1(payment_status);
CREATE INDEX IF NOT EXISTS idx_payments_tenant ON trn_paymentfile1(tenant_recid);
CREATE INDEX IF NOT EXISTS idx_payments_date ON trn_paymentfile1(payment_date);
CREATE INDEX IF NOT EXISTS idx_lease_tenant ON trn_leasefile1(tenant_recid);
CREATE INDEX IF NOT EXISTS idx_lease_status ON trn_leasefile1(lease_status);
CREATE INDEX IF NOT EXISTS idx_room_property ON mf_roomfile(property_recid);
CREATE INDEX IF NOT EXISTS idx_bedspace_room ON mf_bedspacefile(room_recid);

-- ============================================
-- VIEWS FOR DASHBOARD
-- ============================================

-- Drop existing views first (to handle column type changes)
DROP VIEW IF EXISTS view_dashboard CASCADE;
DROP VIEW IF EXISTS view_pending_payment CASCADE;
DROP VIEW IF EXISTS view_overdue_tenant CASCADE;
DROP VIEW IF EXISTS view_tenant_balance CASCADE;
DROP VIEW IF EXISTS view_expiring_contract CASCADE;

-- Dashboard Summary View
CREATE OR REPLACE VIEW view_dashboard AS
SELECT
    (SELECT COUNT(*) FROM trn_leasefile1 WHERE lease_status = 'active') AS active_billed_tenants,
    (SELECT COALESCE(SUM(monthly_rent), 0) FROM trn_leasefile1 WHERE lease_status = 'active') AS total_expected_collection,
    (SELECT COALESCE(SUM(payment_amount), 0) FROM trn_paymentfile1
     WHERE payment_status IN ('verified', 'cleared')
     AND EXTRACT(MONTH FROM payment_date) = EXTRACT(MONTH FROM CURRENT_DATE)
     AND EXTRACT(YEAR FROM payment_date) = EXTRACT(YEAR FROM CURRENT_DATE)) AS total_verified_collected,
    (SELECT COALESCE(SUM(payment_amount), 0) FROM trn_paymentfile1
     WHERE payment_status = 'pending_verification') AS total_pending_verification,
    (SELECT COALESCE(SUM(monthly_rent), 0) - COALESCE((
        SELECT SUM(payment_amount) FROM trn_paymentfile1
        WHERE payment_status IN ('verified', 'cleared')
        AND EXTRACT(MONTH FROM payment_date) = EXTRACT(MONTH FROM CURRENT_DATE)
        AND EXTRACT(YEAR FROM payment_date) = EXTRACT(YEAR FROM CURRENT_DATE)
    ), 0) FROM trn_leasefile1 WHERE lease_status = 'active') AS total_outstanding_balance,
    (SELECT COUNT(*) FROM trn_paymentfile1 WHERE payment_status = 'pending_verification') AS tenants_pending_verification,
    0 AS overdue_tenants,
    0 AS partial_payment_tenants,
    0 AS fully_paid_tenants,
    (SELECT COUNT(*) FROM trn_leasefile1 WHERE lease_status = 'active' AND end_date <= CURRENT_DATE + INTERVAL '30 days') AS contracts_expiring_soon;

-- Pending Payments View
CREATE OR REPLACE VIEW view_pending_payment AS
SELECT
    p.recid, p.payment_id, p.payment_amount, p.payment_date, p.payment_method, p.proof_url,
    t.tenant_name, t.phone_no,
    r.room_name
FROM trn_paymentfile1 p
JOIN mf_tenantfile t ON t.recid = p.tenant_recid
JOIN trn_leasefile1 l ON l.recid = p.lease_recid
JOIN mf_roomfile r ON r.recid = l.room_recid
WHERE p.payment_status = 'pending_verification'
ORDER BY p.payment_date DESC;

-- Overdue Tenants View
CREATE OR REPLACE VIEW view_overdue_tenant AS
SELECT
    l.recid as lease_recid,
    t.tenant_name, t.phone_no,
    r.room_name,
    l.monthly_rent AS total_balance,
    DATE_TRUNC('month', CURRENT_DATE)::DATE AS due_date
FROM trn_leasefile1 l
JOIN mf_tenantfile t ON t.recid = l.tenant_recid
JOIN mf_roomfile r ON r.recid = l.room_recid
WHERE l.lease_status = 'active'
AND NOT EXISTS (
    SELECT 1 FROM trn_paymentfile1 p
    WHERE p.lease_recid = l.recid
    AND p.payment_status IN ('verified', 'cleared')
    AND EXTRACT(MONTH FROM p.payment_date) = EXTRACT(MONTH FROM CURRENT_DATE)
    AND EXTRACT(YEAR FROM p.payment_date) = EXTRACT(YEAR FROM CURRENT_DATE)
);

-- Tenant Balance View
CREATE OR REPLACE VIEW view_tenant_balance AS
SELECT
    l.recid,
    t.tenant_name,
    p.property_name,
    r.room_name,
    b.bedspace_name,
    l.monthly_rent AS total_due,
    COALESCE((
        SELECT SUM(pay.payment_amount)
        FROM trn_paymentfile1 pay
        WHERE pay.lease_recid = l.recid
        AND pay.payment_status IN ('verified', 'cleared')
        AND EXTRACT(MONTH FROM pay.payment_date) = EXTRACT(MONTH FROM CURRENT_DATE)
        AND EXTRACT(YEAR FROM pay.payment_date) = EXTRACT(YEAR FROM CURRENT_DATE)
    ), 0) AS verified_paid,
    l.monthly_rent - COALESCE((
        SELECT SUM(pay.payment_amount)
        FROM trn_paymentfile1 pay
        WHERE pay.lease_recid = l.recid
        AND pay.payment_status IN ('verified', 'cleared')
        AND EXTRACT(MONTH FROM pay.payment_date) = EXTRACT(MONTH FROM CURRENT_DATE)
        AND EXTRACT(YEAR FROM pay.payment_date) = EXTRACT(YEAR FROM CURRENT_DATE)
    ), 0) AS total_balance,
    CASE
        WHEN l.monthly_rent <= COALESCE((
            SELECT SUM(pay.payment_amount)
            FROM trn_paymentfile1 pay
            WHERE pay.lease_recid = l.recid
            AND pay.payment_status IN ('verified', 'cleared')
            AND EXTRACT(MONTH FROM pay.payment_date) = EXTRACT(MONTH FROM CURRENT_DATE)
            AND EXTRACT(YEAR FROM pay.payment_date) = EXTRACT(YEAR FROM CURRENT_DATE)
        ), 0) THEN 'paid'
        WHEN EXISTS (SELECT 1 FROM trn_paymentfile1 pay WHERE pay.lease_recid = l.recid AND pay.payment_status = 'pending_verification') THEN 'pending_verification'
        ELSE 'unpaid'
    END AS display_status
FROM trn_leasefile1 l
JOIN mf_tenantfile t ON t.recid = l.tenant_recid
JOIN mf_propertyfile p ON p.recid = l.property_recid
JOIN mf_roomfile r ON r.recid = l.room_recid
LEFT JOIN mf_bedspacefile b ON b.recid = l.bedspace_recid
WHERE l.lease_status = 'active';

-- Expiring Contracts View
CREATE OR REPLACE VIEW view_expiring_contract AS
SELECT
    l.recid,
    t.tenant_name, t.phone_no,
    p.property_name,
    r.room_name,
    l.lease_type,
    l.end_date,
    CASE
        WHEN l.end_date < CURRENT_DATE THEN 'expired'
        WHEN l.end_date <= CURRENT_DATE + INTERVAL '30 days' THEN 'expiring_soon'
        ELSE 'active'
    END AS contract_alert_status
FROM trn_leasefile1 l
JOIN mf_tenantfile t ON t.recid = l.tenant_recid
JOIN mf_propertyfile p ON p.recid = l.property_recid
JOIN mf_roomfile r ON r.recid = l.room_recid
WHERE l.lease_status = 'active' AND l.end_date IS NOT NULL;

-- ============================================
-- DEFAULT ADMIN USER
-- Password: admin123 (bcrypt hashed)
-- ============================================

INSERT INTO utl_userfile (user_id, full_name, email, password_hash, user_role, user_status)
VALUES ('USR001', 'System Admin', 'admin@athenadorms.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'owner', 'active')
ON CONFLICT (email) DO NOTHING;

-- ============================================
-- SAMPLE DATA (OPTIONAL - Uncomment to use)
-- ============================================

-- Sample Property
-- INSERT INTO mf_propertyfile (property_id, property_name, property_address, property_type, property_status)
-- VALUES ('1', 'Athena Dorms Main', '123 University Ave, Manila', 'dormitory', 'active');

-- Sample Room
-- INSERT INTO mf_roomfile (room_id, property_recid, room_name, room_type, max_bedspace, monthly_room_rate, room_status)
-- VALUES ('1', 1, 'Room 101', 'bedspace', 4, 3500.00, 'active');

-- Sample Bedspace
-- INSERT INTO mf_bedspacefile (bedspace_id, room_recid, bedspace_name, bedspace_status)
-- VALUES ('1', 1, 'Bed A', 'available');

-- ============================================
-- SETUP COMPLETE!
-- Login with: admin@athenadorms.com / admin123
-- ============================================
