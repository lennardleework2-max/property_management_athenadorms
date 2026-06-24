<div class="content-wrapper">
    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title d-flex align-items-center gap-2">
                <span class="title-bar"></span>
                User Access Control
            </h1>
            <p class="page-subtitle">Manage permissions for staff members</p>
        </div>
        <div class="welcome-date badge bg-light text-dark px-3 py-2">
            <?php echo date('D, M d, Y'); ?>
        </div>
    </div>

    <!-- Access Permissions Card -->
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center gap-2">
            <i class="bi bi-shield-check text-success"></i>
            <span>Access Permissions</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 access-table">
                    <thead>
                        <tr>
                            <th class="ps-4" style="min-width: 200px;">USER</th>
                            <?php foreach ($modules as $key => $label): ?>
                            <th class="text-center" style="min-width: 100px;"><?php echo strtoupper($label); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr data-user-id="<?php echo $user['user_recid']; ?>">
                            <td class="ps-4">
                                <div class="d-flex flex-column">
                                    <strong><?php echo e($user['full_name']); ?></strong>
                                    <small class="text-muted"><?php echo getRoleLabel($user); ?></small>
                                </div>
                            </td>
                            <?php foreach ($modules as $key => $label):
                                $accessKey = 'access_' . $key;
                                $hasAccess = isset($user[$accessKey]) && $user[$accessKey];
                            ?>
                            <td class="text-center">
                                <div class="access-checkbox-wrapper">
                                    <input type="checkbox"
                                           class="access-checkbox"
                                           data-user="<?php echo $user['user_recid']; ?>"
                                           data-module="<?php echo $key; ?>"
                                           <?php echo $hasAccess ? 'checked' : ''; ?>
                                           <?php echo ($user['user_role'] === 'owner') ? 'disabled' : ''; ?>>
                                </div>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Access Levels Guide -->
    <div class="card">
        <div class="card-header d-flex align-items-center gap-2">
            <i class="bi bi-info-circle text-primary"></i>
            <span>Access Levels Guide</span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary mb-2">Admin</h6>
                    <p class="text-muted small mb-4">Full access to all modules including user management and configurations.</p>

                    <h6 class="text-primary mb-2">Property Manager</h6>
                    <p class="text-muted small mb-0">Access to property management: Properties, Units, Tenants, Contracts, Bills.</p>
                </div>
                <div class="col-md-6">
                    <h6 class="text-primary mb-2">Billing Staff</h6>
                    <p class="text-muted small mb-4">Access to billing-related modules: Bills, Payments, Water & Electric, Tenants.</p>

                    <h6 class="text-primary mb-2">Viewer</h6>
                    <p class="text-muted small mb-0">Read-only access to Dashboard only. Cannot modify any data.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.title-bar {
    width: 4px;
    height: 28px;
    background: var(--primary-blue);
    border-radius: 2px;
}

.access-table th {
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.05em;
    color: var(--primary-blue);
    background: #F8FAFC;
    padding: 1rem;
    border-bottom: 2px solid var(--border-color);
}

.access-table td {
    padding: 1rem;
    vertical-align: middle;
}

.access-checkbox-wrapper {
    display: flex;
    justify-content: center;
}

.access-checkbox {
    width: 20px;
    height: 20px;
    cursor: pointer;
    accent-color: var(--success);
    border: 2px solid var(--border-color);
    border-radius: 4px;
}

.access-checkbox:checked {
    background-color: var(--success);
    border-color: var(--success);
}

.access-checkbox:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.access-checkbox:not(:disabled):hover {
    transform: scale(1.1);
    transition: transform 0.15s ease;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.access-checkbox:not(:disabled)');

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const userRecid = this.dataset.user;
            const module = this.dataset.module;
            const hasAccess = this.checked;

            // Send AJAX request
            fetch('index.php?action=useraccess.update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `csrf_token=<?php echo getCsrfToken(); ?>&user_recid=${userRecid}&module=${module}&has_access=${hasAccess}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Visual feedback
                    this.parentElement.classList.add('flash-success');
                    setTimeout(() => {
                        this.parentElement.classList.remove('flash-success');
                    }, 300);
                } else {
                    // Revert on error
                    this.checked = !hasAccess;
                    alert('Failed to update: ' + data.message);
                }
            })
            .catch(error => {
                this.checked = !hasAccess;
                alert('Error updating access');
            });
        });
    });
});
</script>

<?php
// Helper function to determine role label
function getRoleLabel($user) {
    $accessCount = 0;
    $accessCount += !empty($user['access_dashboard']) ? 1 : 0;
    $accessCount += !empty($user['access_properties']) ? 1 : 0;
    $accessCount += !empty($user['access_rooms']) ? 1 : 0;
    $accessCount += !empty($user['access_tenants']) ? 1 : 0;
    $accessCount += !empty($user['access_contracts']) ? 1 : 0;
    $accessCount += !empty($user['access_bills']) ? 1 : 0;
    $accessCount += !empty($user['access_staff']) ? 1 : 0;
    $accessCount += !empty($user['access_utilities']) ? 1 : 0;
    $accessCount += !empty($user['access_user_access']) ? 1 : 0;

    if ($user['user_role'] === 'owner') return 'Owner';
    if ($accessCount >= 9) return 'Admin';
    if ($accessCount >= 6) return 'Property Manager';
    if ($accessCount >= 4) return 'Billing Staff';
    return 'Viewer';
}
?>
