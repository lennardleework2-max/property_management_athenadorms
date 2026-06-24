-- ============================================
-- Athena Dorms Property Management System
-- Sample Data & User Access Control
-- Run this AFTER supabase_setup.sql
-- ============================================

-- ============================================
-- USER ACCESS PERMISSIONS TABLE
-- ============================================

DROP TABLE IF EXISTS utl_user_access CASCADE;

CREATE TABLE IF NOT EXISTS utl_user_access (
    recid SERIAL PRIMARY KEY,
    user_recid INTEGER REFERENCES utl_userfile(recid) ON DELETE CASCADE,
    module_name VARCHAR(50) NOT NULL,
    has_access BOOLEAN DEFAULT false,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_recid, module_name)
);

CREATE INDEX IF NOT EXISTS idx_user_access_user ON utl_user_access(user_recid);
CREATE INDEX IF NOT EXISTS idx_user_access_module ON utl_user_access(module_name);

-- ============================================
-- SAMPLE USERS
-- All passwords: "password123"
-- ============================================

-- Clear existing sample users (except admin)
DELETE FROM utl_userfile WHERE email != 'admin@athenadorms.com';

-- Insert sample users
INSERT INTO utl_userfile (user_id, full_name, email, password_hash, user_role, user_status)
VALUES
    ('USR002', 'Juan Dela Cruz', 'juan@athenadorms.com',
     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active'),
    ('USR003', 'Maria Santos', 'maria@athenadorms.com',
     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 'active'),
    ('USR004', 'Pedro Reyes', 'pedro@athenadorms.com',
     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 'active'),
    ('USR005', 'Ana Garcia', 'ana@athenadorms.com',
     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 'active')
ON CONFLICT (email) DO UPDATE SET
    full_name = EXCLUDED.full_name,
    user_role = EXCLUDED.user_role;

-- ============================================
-- USER ACCESS PERMISSIONS
-- Modules: dashboard, properties, rooms, tenants, contracts, bills, staff, utilities, user_access
-- ============================================

-- Clear existing access permissions
DELETE FROM utl_user_access;

-- Admin (Juan Dela Cruz) - Full access
INSERT INTO utl_user_access (user_recid, module_name, has_access)
SELECT u.recid, m.module, true
FROM utl_userfile u
CROSS JOIN (
    VALUES ('dashboard'), ('properties'), ('rooms'), ('tenants'), ('contracts'),
           ('bills'), ('staff'), ('utilities'), ('user_access')
) AS m(module)
WHERE u.email = 'juan@athenadorms.com';

-- Billing Staff (Maria Santos) - Billing related access
INSERT INTO utl_user_access (user_recid, module_name, has_access)
SELECT u.recid, m.module,
    CASE WHEN m.module IN ('dashboard', 'tenants', 'contracts', 'bills', 'utilities') THEN true ELSE false END
FROM utl_userfile u
CROSS JOIN (
    VALUES ('dashboard'), ('properties'), ('rooms'), ('tenants'), ('contracts'),
           ('bills'), ('staff'), ('utilities'), ('user_access')
) AS m(module)
WHERE u.email = 'maria@athenadorms.com';

-- Property Manager (Pedro Reyes) - Property related access
INSERT INTO utl_user_access (user_recid, module_name, has_access)
SELECT u.recid, m.module,
    CASE WHEN m.module IN ('dashboard', 'properties', 'rooms', 'tenants', 'contracts', 'bills', 'utilities') THEN true ELSE false END
FROM utl_userfile u
CROSS JOIN (
    VALUES ('dashboard'), ('properties'), ('rooms'), ('tenants'), ('contracts'),
           ('bills'), ('staff'), ('utilities'), ('user_access')
) AS m(module)
WHERE u.email = 'pedro@athenadorms.com';

-- Viewer (Ana Garcia) - Dashboard only
INSERT INTO utl_user_access (user_recid, module_name, has_access)
SELECT u.recid, m.module,
    CASE WHEN m.module = 'dashboard' THEN true ELSE false END
FROM utl_userfile u
CROSS JOIN (
    VALUES ('dashboard'), ('properties'), ('rooms'), ('tenants'), ('contracts'),
           ('bills'), ('staff'), ('utilities'), ('user_access')
) AS m(module)
WHERE u.email = 'ana@athenadorms.com';

-- System Admin - Full access (update existing)
INSERT INTO utl_user_access (user_recid, module_name, has_access)
SELECT u.recid, m.module, true
FROM utl_userfile u
CROSS JOIN (
    VALUES ('dashboard'), ('properties'), ('rooms'), ('tenants'), ('contracts'),
           ('bills'), ('staff'), ('utilities'), ('user_access')
) AS m(module)
WHERE u.email = 'admin@athenadorms.com'
ON CONFLICT (user_recid, module_name) DO UPDATE SET has_access = true;

-- ============================================
-- SAMPLE PROPERTIES
-- ============================================

DELETE FROM mf_propertyfile;

INSERT INTO mf_propertyfile (property_id, property_name, property_address, property_type, property_status)
VALUES
    ('PROP001', 'Athena Dorms Main', '123 University Ave, Sampaloc, Manila', 'dormitory', 'active'),
    ('PROP002', 'Athena Dorms Annex', '456 College St, Sampaloc, Manila', 'dormitory', 'active'),
    ('PROP003', 'Athena Apartments', '789 Scholar Rd, Sampaloc, Manila', 'apartment', 'active');

-- ============================================
-- SAMPLE ROOMS
-- ============================================

DELETE FROM mf_roomfile;

INSERT INTO mf_roomfile (room_id, property_recid, room_name, room_type, max_bedspace, monthly_room_rate, room_status)
VALUES
    -- Main Building Rooms
    ('RM001', 1, 'Room 101', 'bedspace', 4, 3500.00, 'active'),
    ('RM002', 1, 'Room 102', 'bedspace', 4, 3500.00, 'active'),
    ('RM003', 1, 'Room 103', 'bedspace', 4, 3500.00, 'active'),
    ('RM004', 1, 'Room 201', 'bedspace', 4, 3800.00, 'active'),
    ('RM005', 1, 'Room 202', 'bedspace', 4, 3800.00, 'active'),
    -- Annex Rooms
    ('RM006', 2, 'Room A1', 'bedspace', 2, 4500.00, 'active'),
    ('RM007', 2, 'Room A2', 'bedspace', 2, 4500.00, 'active'),
    ('RM008', 2, 'Room A3', 'private', 1, 8000.00, 'active'),
    -- Apartment Units
    ('RM009', 3, 'Unit 1A', 'studio', 1, 12000.00, 'active'),
    ('RM010', 3, 'Unit 1B', 'studio', 1, 12000.00, 'active');

-- ============================================
-- SAMPLE BEDSPACES
-- ============================================

DELETE FROM mf_bedspacefile;

INSERT INTO mf_bedspacefile (bedspace_id, room_recid, bedspace_name, bedspace_status)
VALUES
    -- Room 101 (4 beds)
    ('BED001', 1, 'Bed A', 'occupied'),
    ('BED002', 1, 'Bed B', 'occupied'),
    ('BED003', 1, 'Bed C', 'available'),
    ('BED004', 1, 'Bed D', 'available'),
    -- Room 102 (4 beds)
    ('BED005', 2, 'Bed A', 'occupied'),
    ('BED006', 2, 'Bed B', 'available'),
    ('BED007', 2, 'Bed C', 'available'),
    ('BED008', 2, 'Bed D', 'reserved'),
    -- Room 103 (4 beds)
    ('BED009', 3, 'Bed A', 'occupied'),
    ('BED010', 3, 'Bed B', 'occupied'),
    ('BED011', 3, 'Bed C', 'occupied'),
    ('BED012', 3, 'Bed D', 'maintenance'),
    -- Room 201 (4 beds)
    ('BED013', 4, 'Bed A', 'occupied'),
    ('BED014', 4, 'Bed B', 'available'),
    ('BED015', 4, 'Bed C', 'available'),
    ('BED016', 4, 'Bed D', 'available'),
    -- Room A1 (2 beds)
    ('BED017', 6, 'Bed A', 'occupied'),
    ('BED018', 6, 'Bed B', 'occupied'),
    -- Room A2 (2 beds)
    ('BED019', 7, 'Bed A', 'available'),
    ('BED020', 7, 'Bed B', 'available');

-- ============================================
-- SAMPLE TENANTS
-- ============================================

DELETE FROM mf_tenantfile;

INSERT INTO mf_tenantfile (tenant_id, tenant_name, phone_no, email, emergency_contact_name, emergency_contact_no, tenant_status, move_in_date)
VALUES
    ('TEN001', 'Carlo Mendoza', '09171234567', 'carlo.mendoza@email.com', 'Rosa Mendoza', '09181234567', 'active', '2024-01-15'),
    ('TEN002', 'Sofia Reyes', '09182345678', 'sofia.reyes@email.com', 'Jose Reyes', '09192345678', 'active', '2024-02-01'),
    ('TEN003', 'Miguel Santos', '09193456789', 'miguel.santos@email.com', 'Ana Santos', '09203456789', 'active', '2024-01-20'),
    ('TEN004', 'Isabella Cruz', '09204567890', 'isabella.cruz@email.com', 'Pedro Cruz', '09214567890', 'active', '2024-03-01'),
    ('TEN005', 'Daniel Garcia', '09215678901', 'daniel.garcia@email.com', 'Maria Garcia', '09225678901', 'active', '2024-02-15'),
    ('TEN006', 'Andrea Lopez', '09226789012', 'andrea.lopez@email.com', 'Juan Lopez', '09236789012', 'active', '2024-03-10'),
    ('TEN007', 'Rafael Torres', '09237890123', 'rafael.torres@email.com', 'Carmen Torres', '09247890123', 'active', '2024-01-05'),
    ('TEN008', 'Camille Flores', '09248901234', 'camille.flores@email.com', 'Roberto Flores', '09258901234', 'active', '2024-04-01'),
    ('TEN009', 'Joshua Ramos', '09259012345', 'joshua.ramos@email.com', 'Elena Ramos', '09269012345', 'active', '2024-03-20'),
    ('TEN010', 'Patricia Villanueva', '09260123456', 'patricia.v@email.com', 'Antonio Villanueva', '09270123456', 'active', '2024-02-28');

-- ============================================
-- SAMPLE LEASES
-- ============================================

DELETE FROM trn_leasefile1;

INSERT INTO trn_leasefile1 (lease_id, tenant_recid, property_recid, room_recid, bedspace_recid, lease_type, start_date, end_date, monthly_rent, security_deposit, lease_status)
VALUES
    ('LSE001', 1, 1, 1, 1, 'monthly', '2024-01-15', '2025-01-14', 3500.00, 7000.00, 'active'),
    ('LSE002', 2, 1, 1, 2, 'monthly', '2024-02-01', '2025-01-31', 3500.00, 7000.00, 'active'),
    ('LSE003', 3, 1, 2, 5, 'monthly', '2024-01-20', '2025-01-19', 3500.00, 7000.00, 'active'),
    ('LSE004', 4, 1, 3, 9, 'monthly', '2024-03-01', '2025-02-28', 3500.00, 7000.00, 'active'),
    ('LSE005', 5, 1, 3, 10, 'monthly', '2024-02-15', '2025-02-14', 3500.00, 7000.00, 'active'),
    ('LSE006', 6, 1, 3, 11, 'monthly', '2024-03-10', '2025-03-09', 3500.00, 7000.00, 'active'),
    ('LSE007', 7, 1, 4, 13, 'semestral', '2024-01-05', '2024-07-04', 3800.00, 7600.00, 'active'),
    ('LSE008', 8, 2, 6, 17, 'monthly', '2024-04-01', '2025-03-31', 4500.00, 9000.00, 'active'),
    ('LSE009', 9, 2, 6, 18, 'monthly', '2024-03-20', '2025-03-19', 4500.00, 9000.00, 'active'),
    ('LSE010', 10, 3, 9, NULL, 'annual', '2024-02-28', '2025-02-27', 12000.00, 24000.00, 'active');

-- ============================================
-- SAMPLE PAYMENTS (Current Month)
-- ============================================

DELETE FROM trn_paymentfile1;

INSERT INTO trn_paymentfile1 (payment_id, tenant_recid, lease_recid, payment_amount, payment_date, payment_method, reference_no, payment_status)
VALUES
    -- Verified payments
    ('PAY001', 1, 1, 3500.00, CURRENT_DATE - INTERVAL '5 days', 'gcash', 'GC123456789', 'verified'),
    ('PAY002', 2, 2, 3500.00, CURRENT_DATE - INTERVAL '3 days', 'bank_transfer', 'BT987654321', 'verified'),
    ('PAY003', 3, 3, 3500.00, CURRENT_DATE - INTERVAL '7 days', 'cash', NULL, 'verified'),
    -- Pending verification
    ('PAY004', 4, 4, 3500.00, CURRENT_DATE - INTERVAL '1 day', 'gcash', 'GC111222333', 'pending_verification'),
    ('PAY005', 5, 5, 3500.00, CURRENT_DATE, 'gcash', 'GC444555666', 'pending_verification'),
    ('PAY006', 8, 8, 4500.00, CURRENT_DATE - INTERVAL '2 days', 'bank_transfer', 'BT777888999', 'pending_verification'),
    -- Partial payment
    ('PAY007', 6, 6, 2000.00, CURRENT_DATE - INTERVAL '4 days', 'gcash', 'GC000111222', 'verified');

-- ============================================
-- SAMPLE UTILITIES
-- ============================================

DELETE FROM trn_utilityfile1;

INSERT INTO trn_utilityfile1 (utility_id, property_recid, room_recid, utility_type, billing_month, previous_reading, current_reading, consumption, rate, total_amount, utility_status)
VALUES
    ('UTL001', 1, 1, 'electricity', DATE_TRUNC('month', CURRENT_DATE), 1000, 1150, 150, 12.50, 1875.00, 'posted'),
    ('UTL002', 1, 2, 'electricity', DATE_TRUNC('month', CURRENT_DATE), 800, 920, 120, 12.50, 1500.00, 'posted'),
    ('UTL003', 1, 3, 'electricity', DATE_TRUNC('month', CURRENT_DATE), 1200, 1380, 180, 12.50, 2250.00, 'posted'),
    ('UTL004', 1, 1, 'water', DATE_TRUNC('month', CURRENT_DATE), 50, 65, 15, 35.00, 525.00, 'posted'),
    ('UTL005', 1, 2, 'water', DATE_TRUNC('month', CURRENT_DATE), 45, 58, 13, 35.00, 455.00, 'posted');

-- ============================================
-- VIEW: User Access Summary
-- ============================================

DROP VIEW IF EXISTS view_user_access_summary CASCADE;

CREATE OR REPLACE VIEW view_user_access_summary AS
SELECT
    u.recid as user_recid,
    u.user_id,
    u.full_name,
    u.email,
    u.user_role,
    u.user_status,
    COALESCE(bool_or(CASE WHEN ua.module_name = 'dashboard' THEN ua.has_access END), false) as access_dashboard,
    COALESCE(bool_or(CASE WHEN ua.module_name = 'properties' THEN ua.has_access END), false) as access_properties,
    COALESCE(bool_or(CASE WHEN ua.module_name = 'rooms' THEN ua.has_access END), false) as access_rooms,
    COALESCE(bool_or(CASE WHEN ua.module_name = 'tenants' THEN ua.has_access END), false) as access_tenants,
    COALESCE(bool_or(CASE WHEN ua.module_name = 'contracts' THEN ua.has_access END), false) as access_contracts,
    COALESCE(bool_or(CASE WHEN ua.module_name = 'bills' THEN ua.has_access END), false) as access_bills,
    COALESCE(bool_or(CASE WHEN ua.module_name = 'staff' THEN ua.has_access END), false) as access_staff,
    COALESCE(bool_or(CASE WHEN ua.module_name = 'utilities' THEN ua.has_access END), false) as access_utilities,
    COALESCE(bool_or(CASE WHEN ua.module_name = 'user_access' THEN ua.has_access END), false) as access_user_access
FROM utl_userfile u
LEFT JOIN utl_user_access ua ON ua.user_recid = u.recid
WHERE u.user_status = 'active'
GROUP BY u.recid, u.user_id, u.full_name, u.email, u.user_role, u.user_status
ORDER BY u.full_name;

-- ============================================
-- SETUP COMPLETE!
-- Sample Users (password for all: "password123"):
-- - admin@athenadorms.com (Owner - Full Access)
-- - juan@athenadorms.com (Admin - Full Access)
-- - maria@athenadorms.com (Billing Staff)
-- - pedro@athenadorms.com (Property Manager)
-- - ana@athenadorms.com (Viewer - Dashboard Only)
-- ============================================
