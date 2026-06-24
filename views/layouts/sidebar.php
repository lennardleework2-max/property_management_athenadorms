    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-content">
            <!-- Logo area -->
            <div class="sidebar-header">
                <img src="/public/assets/images/athena_logo.png" alt="Athena Dorms" class="sidebar-logo">
                <button class="btn btn-link sidebar-close d-lg-none" id="sidebarClose">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="sidebar-nav">
                <ul class="nav flex-column">
                    <!-- Dashboard (Owner/Admin only) -->
                    <?php if (hasAccess('dashboard')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (isset($_GET['action']) && $_GET['action'] == 'dashboard') || !isset($_GET['action']) ? 'active' : ''; ?>"
                           href="index.php?action=dashboard">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <!-- Section: Property Management (Only show if user has access to any property actions) -->
                    <?php if (hasAccess('property.list') || hasAccess('room.list')): ?>
                    <li class="nav-section">
                        <span>Property Management</span>
                    </li>

                    <?php if (hasAccess('property.list')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (isset($_GET['action']) && strpos($_GET['action'], 'property.') === 0) ? 'active' : ''; ?>"
                           href="index.php?action=property.list">
                            <i class="bi bi-buildings"></i>
                            <span>Properties</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (hasAccess('room.list')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (isset($_GET['action']) && strpos($_GET['action'], 'room.') === 0) ? 'active' : ''; ?>"
                           href="index.php?action=room.list">
                            <i class="bi bi-door-open"></i>
                            <span>Rooms & Bedspaces</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php endif; ?>

                    <!-- Section: Tenants -->
                    <?php if (hasAccess('tenant.list') || hasAccess('lease.list')): ?>
                    <li class="nav-section">
                        <span>Tenants</span>
                    </li>

                    <?php if (hasAccess('tenant.list')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (isset($_GET['action']) && strpos($_GET['action'], 'tenant.') === 0) ? 'active' : ''; ?>"
                           href="index.php?action=tenant.list">
                            <i class="bi bi-people"></i>
                            <span>Tenants</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (hasAccess('lease.list')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (isset($_GET['action']) && strpos($_GET['action'], 'lease.') === 0) ? 'active' : ''; ?>"
                           href="index.php?action=lease.list">
                            <i class="bi bi-file-earmark-text"></i>
                            <span>Leases/Contracts</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php endif; ?>

                    <!-- Section: Billing -->
                    <?php if (hasAccess('payment.list') || hasAccess('utility.list')): ?>
                    <li class="nav-section">
                        <span>Billing & Payments</span>
                    </li>

                    <?php if (hasAccess('payment.list')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (isset($_GET['action']) && strpos($_GET['action'], 'payment.') === 0) ? 'active' : ''; ?>"
                           href="index.php?action=payment.list">
                            <i class="bi bi-credit-card"></i>
                            <span>Payments</span>
                            <?php
                            // Show pending badge if there are pending verifications
                            $pendingCount = $_SESSION['pending_payments'] ?? 0;
                            if ($pendingCount > 0):
                            ?>
                            <span class="badge bg-warning text-dark ms-auto"><?php echo $pendingCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (hasAccess('utility.list')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (isset($_GET['action']) && strpos($_GET['action'], 'utility.') === 0) ? 'active' : ''; ?>"
                           href="index.php?action=utility.list">
                            <i class="bi bi-lightning-charge"></i>
                            <span>Utilities</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php endif; ?>

                    <!-- Section: Reports -->
                    <?php if (hasAccess('report.index')): ?>
                    <li class="nav-section">
                        <span>Reports</span>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?php echo (isset($_GET['action']) && strpos($_GET['action'], 'report.') === 0) ? 'active' : ''; ?>"
                           href="index.php?action=report.index">
                            <i class="bi bi-bar-chart-line"></i>
                            <span>Reports</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <!-- Section: Administration -->
                    <?php if (hasAccess('useraccess.list')): ?>
                    <li class="nav-section">
                        <span>Administration</span>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?php echo (isset($_GET['action']) && strpos($_GET['action'], 'useraccess.') === 0) ? 'active' : ''; ?>"
                           href="index.php?action=useraccess.list">
                            <i class="bi bi-shield-lock"></i>
                            <span>User Access</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>

            <!-- Logout Link -->
            <div class="sidebar-nav mt-auto" style="padding-bottom: 0;">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?action=auth.logout" style="color: #f87171 !important;">
                            <i class="bi bi-box-arrow-left"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Sidebar Footer -->
            <div class="sidebar-footer">
                <div class="powered-by">
                    <small>Powered by</small>
                    <img src="/public/assets/images/avax_logo.png" alt="AvaxTech" class="avax-logo-small">
                    <small>AvaxTech Solutions</small>
                </div>
            </div>
        </div>
    </aside>
