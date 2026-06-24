<?php
/**
 * Routes Configuration
 * Athena Dorms Property Management System
 * Maps actions to controller methods
 */

return [
    // Authentication
    'auth.login' => ['AuthController', 'showLogin'],
    'auth.do.login' => ['AuthController', 'login'],
    'auth.logout' => ['AuthController', 'logout'],

    // Dashboard
    'dashboard' => ['DashboardController', 'index'],

    // Tenants
    'tenant.list' => ['TenantController', 'list'],
    'tenant.get' => ['TenantController', 'get'],
    'tenant.get.all' => ['TenantController', 'getAll'],
    'tenant.add' => ['TenantController', 'add'],
    'tenant.edit' => ['TenantController', 'edit'],
    'tenant.delete' => ['TenantController', 'delete'],
    'tenant.generate.id' => ['TenantController', 'generateId'],

    // Properties
    'property.list' => ['PropertyController', 'list'],
    'property.get' => ['PropertyController', 'get'],
    'property.get.all' => ['PropertyController', 'getAll'],
    'property.add' => ['PropertyController', 'add'],
    'property.edit' => ['PropertyController', 'edit'],
    'property.delete' => ['PropertyController', 'delete'],
    'property.generate.id' => ['PropertyController', 'generateId'],

    // Rooms
    'room.list' => ['RoomController', 'list'],
    'room.get' => ['RoomController', 'get'],
    'room.get.all' => ['RoomController', 'getAll'],
    'room.get.by.property' => ['RoomController', 'getByProperty'],
    'room.add' => ['RoomController', 'add'],
    'room.edit' => ['RoomController', 'edit'],
    'room.delete' => ['RoomController', 'delete'],
    'room.generate.id' => ['RoomController', 'generateId'],

    // Bedspaces
    'bedspace.list' => ['BedspaceController', 'list'],
    'bedspace.get' => ['BedspaceController', 'get'],
    'bedspace.get.all' => ['BedspaceController', 'getAll'],
    'bedspace.get.by.room' => ['BedspaceController', 'getByRoom'],
    'bedspace.add' => ['BedspaceController', 'add'],
    'bedspace.edit' => ['BedspaceController', 'edit'],
    'bedspace.delete' => ['BedspaceController', 'delete'],
    'bedspace.generate.id' => ['BedspaceController', 'generateId'],

    // Leases
    'lease.list' => ['LeaseController', 'list'],
    'lease.get' => ['LeaseController', 'get'],
    'lease.get.all' => ['LeaseController', 'getAll'],
    'lease.add' => ['LeaseController', 'add'],
    'lease.edit' => ['LeaseController', 'edit'],
    'lease.delete' => ['LeaseController', 'delete'],
    'lease.generate.id' => ['LeaseController', 'generateId'],

    // Payments
    'payment.list' => ['PaymentController', 'list'],
    'payment.get' => ['PaymentController', 'get'],
    'payment.get.all' => ['PaymentController', 'getAll'],
    'payment.add' => ['PaymentController', 'add'],
    'payment.edit' => ['PaymentController', 'edit'],
    'payment.delete' => ['PaymentController', 'delete'],
    'payment.verify' => ['PaymentController', 'verify'],
    'payment.reject' => ['PaymentController', 'reject'],
    'payment.generate.id' => ['PaymentController', 'generateId'],
    'payment.upload.proof' => ['PaymentController', 'uploadProof'],

    // Utilities
    'utility.list' => ['UtilityController', 'list'],
    'utility.get' => ['UtilityController', 'get'],
    'utility.get.all' => ['UtilityController', 'getAll'],
    'utility.add' => ['UtilityController', 'add'],
    'utility.edit' => ['UtilityController', 'edit'],
    'utility.delete' => ['UtilityController', 'delete'],
    'utility.generate.id' => ['UtilityController', 'generateId'],
    'utility.allocate' => ['UtilityController', 'allocate'],

    // Reports
    'report.index' => ['ReportController', 'index'],
    'report.tenant.balance' => ['ReportController', 'tenantBalance'],
    'report.pending.payment' => ['ReportController', 'pendingPayment'],
    'report.overdue' => ['ReportController', 'overdue'],
    'report.utility' => ['ReportController', 'utility'],
    'report.expiring.contract' => ['ReportController', 'expiringContract'],

    // User Access
    'useraccess.list' => ['UserAccessController', 'list'],
    'useraccess.update' => ['UserAccessController', 'updateAccess'],
];
