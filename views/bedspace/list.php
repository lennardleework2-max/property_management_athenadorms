<div class="content-wrapper">
    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h1 class="page-title">Bedspaces</h1>
            <p class="page-subtitle">View and manage bedspace status</p>
        </div>
    </div>

    <!-- Bedspace List -->
    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($bedspaces)): ?>
            <div class="empty-state py-5">
                <i class="bi bi-door-open"></i>
                <h5>No bedspaces found</h5>
                <p>Add bedspaces through the Rooms page.</p>
                <a href="index.php?action=room.list" class="btn btn-primary">Go to Rooms</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Property / Room</th>
                            <th>Bedspace</th>
                            <th>Tenant</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bedspaces as $bedspace): ?>
                        <tr>
                            <td><span class="badge bg-secondary"><?php echo e($bedspace['bedspace_id']); ?></span></td>
                            <td>
                                <?php echo e($bedspace['property_name']); ?>
                                <br><small class="text-muted"><?php echo e($bedspace['room_name']); ?></small>
                            </td>
                            <td><strong><?php echo e($bedspace['bedspace_name']); ?></strong></td>
                            <td>
                                <?php if (!empty($bedspace['tenant_name'])): ?>
                                <?php echo e($bedspace['tenant_name']); ?>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo e($bedspace['bedspace_status']); ?>">
                                    <?php echo ucfirst(e($bedspace['bedspace_status'])); ?>
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
