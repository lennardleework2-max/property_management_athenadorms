<div class="content-wrapper">
    <!-- Welcome Section -->
    <div class="welcome-section">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2>Good <?php
                    $hour = date('H');
                    if ($hour < 12) echo 'Morning';
                    elseif ($hour < 17) echo 'Afternoon';
                    else echo 'Evening';
                ?>, <?php echo e($currentUser['full_name']); ?>!</h2>
                <p>Here's what's happening with your properties today.</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <span class="welcome-date"><?php echo date('l, F j, Y'); ?></span>
            </div>
        </div>
    </div>

    <!-- Summary Cards - Clean Blue Theme -->
    <div class="row g-4 mb-4">
        <!-- Total Expected -->
        <div class="col-md-6 col-lg-3">
            <div class="card stat-card-clean">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon-box bg-primary-soft">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div>
                        <div class="stat-value"><?php echo formatCurrency($summary['total_expected_collection']); ?></div>
                        <div class="stat-label">Expected Collection</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Verified Collected -->
        <div class="col-md-6 col-lg-3">
            <div class="card stat-card-clean">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon-box bg-success-soft">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <div class="stat-value"><?php echo formatCurrency($summary['total_verified_collected']); ?></div>
                        <div class="stat-label">Verified Collected</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Verification -->
        <div class="col-md-6 col-lg-3">
            <div class="card stat-card-clean">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon-box bg-warning-soft">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <div class="stat-value"><?php echo formatCurrency($summary['total_pending_verification']); ?></div>
                        <div class="stat-label">Pending Verification</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Outstanding Balance -->
        <div class="col-md-6 col-lg-3">
            <div class="card stat-card-clean">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon-box bg-danger-soft">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div>
                        <div class="stat-value"><?php echo formatCurrency($summary['total_outstanding_balance']); ?></div>
                        <div class="stat-label">Outstanding Balance</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Row -->
    <div class="row g-4 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-mini">
                <div class="card-body text-center py-3">
                    <div class="stat-mini-value text-primary"><?php echo (int)$propertyStats['total_properties']; ?></div>
                    <div class="stat-mini-label">Properties</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-mini">
                <div class="card-body text-center py-3">
                    <div class="stat-mini-value text-primary"><?php echo (int)$propertyStats['active_tenants']; ?></div>
                    <div class="stat-mini-label">Active Tenants</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-mini">
                <div class="card-body text-center py-3">
                    <div class="stat-mini-value text-primary"><?php echo (int)$propertyStats['occupied_bedspaces']; ?>/<?php echo (int)$propertyStats['total_bedspaces']; ?></div>
                    <div class="stat-mini-label">Occupied Bedspaces</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-mini">
                <div class="card-body text-center py-3">
                    <div class="stat-mini-value text-warning"><?php echo (int)$summary['contracts_expiring_soon']; ?></div>
                    <div class="stat-mini-label">Expiring Contracts</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Pending Payment Verification -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-hourglass-split text-primary me-2"></i>Pending Payment Verification</span>
                    <a href="index.php?action=payment.list" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($pendingPayments)): ?>
                    <div class="empty-state py-4">
                        <i class="bi bi-check-circle text-success"></i>
                        <p class="mb-0 mt-2">No pending verifications</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Tenant</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingPayments as $payment): ?>
                                <tr class="cursor-pointer" onclick="window.location='index.php?action=payment.list'">
                                    <td>
                                        <strong><?php echo e($payment['tenant_name']); ?></strong>
                                        <br><small class="text-muted"><?php echo e($payment['room_name']); ?></small>
                                    </td>
                                    <td><?php echo formatCurrency($payment['payment_amount']); ?></td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary"><?php echo e(ucfirst(str_replace('_', ' ', $payment['payment_method']))); ?></span>
                                    </td>
                                    <td><?php echo formatDate($payment['payment_date']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Overdue Tenants -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-exclamation-triangle text-primary me-2"></i>Overdue Tenants</span>
                    <a href="index.php?action=report.overdue" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($overdueTenants)): ?>
                    <div class="empty-state py-4">
                        <i class="bi bi-check-circle text-success"></i>
                        <p class="mb-0 mt-2">No overdue tenants</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Tenant</th>
                                    <th>Room</th>
                                    <th>Balance</th>
                                    <th>Due Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($overdueTenants as $tenant): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo e($tenant['tenant_name']); ?></strong>
                                        <br><small class="text-muted"><?php echo e($tenant['phone_no']); ?></small>
                                    </td>
                                    <td><?php echo e($tenant['room_name']); ?></td>
                                    <td class="text-danger fw-bold"><?php echo formatCurrency($tenant['total_balance']); ?></td>
                                    <td><?php echo formatDate($tenant['due_date']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Tenant Balances -->
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-wallet2 text-primary me-2"></i>Tenant Balances (Current Month)</span>
                    <a href="index.php?action=report.tenant.balance" class="btn btn-sm btn-outline-primary">Full Report</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($tenantBalances)): ?>
                    <div class="empty-state py-4">
                        <i class="bi bi-inbox"></i>
                        <p class="mb-0 mt-2">No billing data for current month</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Tenant</th>
                                    <th>Property / Room</th>
                                    <th>Total Due</th>
                                    <th>Paid</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tenantBalances as $balance): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo e($balance['tenant_name']); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo e($balance['property_name']); ?>
                                        <br><small class="text-muted"><?php echo e($balance['room_name']); ?> - <?php echo e($balance['bedspace_name']); ?></small>
                                    </td>
                                    <td><?php echo formatCurrency($balance['total_due']); ?></td>
                                    <td class="text-success"><?php echo formatCurrency($balance['verified_paid']); ?></td>
                                    <td class="<?php echo $balance['total_balance'] > 0 ? 'text-danger fw-bold' : 'text-success'; ?>">
                                        <?php echo formatCurrency($balance['total_balance']); ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo e($balance['display_status']); ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', e($balance['display_status']))); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Expiring Contracts -->
    <?php if (!empty($expiringContracts)): ?>
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-calendar-x text-primary me-2"></i>Expiring Contracts</span>
                    <a href="index.php?action=report.expiring.contract" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Tenant</th>
                                    <th>Property / Room</th>
                                    <th>Lease Type</th>
                                    <th>End Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($expiringContracts as $contract): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo e($contract['tenant_name']); ?></strong>
                                        <br><small class="text-muted"><?php echo e($contract['phone_no']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo e($contract['property_name']); ?>
                                        <br><small class="text-muted"><?php echo e($contract['room_name']); ?></small>
                                    </td>
                                    <td><span class="badge bg-primary-subtle text-primary"><?php echo ucfirst(e($contract['lease_type'])); ?></span></td>
                                    <td><?php echo formatDate($contract['end_date']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo e($contract['contract_alert_status']); ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', e($contract['contract_alert_status']))); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
