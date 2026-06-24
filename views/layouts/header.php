<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle ?? 'Athena Dorms'); ?> - Property Management</title>
    <link rel="icon" type="image/png" href="/public/assets/images/athena_logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/public/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top top-navbar">
        <div class="container-fluid">
            <!-- Sidebar Toggle -->
            <button class="btn btn-link sidebar-toggle d-lg-none" type="button" id="sidebarToggle">
                <i class="bi bi-list fs-4"></i>
            </button>

            <!-- Brand -->
            <a class="navbar-brand d-flex align-items-center" href="index.php?action=dashboard">
                <img src="/public/assets/images/athena_logo.png" alt="Athena Dorms" height="40">
                <span class="ms-2 brand-text d-none d-md-inline">Athena Dorms</span>
            </a>

            <!-- Right side -->
            <div class="d-flex align-items-center gap-2">
                <!-- User dropdown -->
                <div class="dropdown">
                    <button class="btn btn-link dropdown-toggle user-dropdown" type="button"
                            id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-avatar">
                            <i class="bi bi-person"></i>
                        </div>
                        <div class="user-info d-none d-md-block">
                            <span class="user-name"><?php echo e($currentUser['full_name'] ?? 'User'); ?></span>
                            <span class="user-role"><?php echo ucfirst(e($currentUser['user_role'] ?? 'user')); ?></span>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg" aria-labelledby="userDropdown">
                        <li class="px-3 py-2">
                            <div class="d-flex align-items-center gap-3">
                                <div class="user-avatar" style="width: 48px; height: 48px;">
                                    <i class="bi bi-person fs-5"></i>
                                </div>
                                <div>
                                    <strong><?php echo e($currentUser['full_name'] ?? 'User'); ?></strong>
                                    <br>
                                    <small class="text-muted"><?php echo e($currentUser['email'] ?? ''); ?></small>
                                </div>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <span class="dropdown-item d-flex align-items-center justify-content-between">
                                <span><i class="bi bi-shield-check me-2"></i>Role</span>
                                <span class="badge bg-primary"><?php echo ucfirst(e($currentUser['user_role'] ?? 'user')); ?></span>
                            </span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="index.php?action=auth.logout">
                                <i class="bi bi-box-arrow-right me-2"></i>Sign Out
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar Overlay for mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
