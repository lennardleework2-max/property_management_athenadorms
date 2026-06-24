<div class="content-wrapper">
    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h1 class="page-title">Tenants</h1>
            <p class="page-subtitle">Manage tenant information</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tenantModal" onclick="openAddModal()">
            <i class="bi bi-plus-lg me-1"></i> Add Tenant
        </button>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="index.php" class="row g-3">
                <input type="hidden" name="action" value="tenant.list">
                <div class="col-md-6">
                    <div class="search-box">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" class="form-control" name="search" placeholder="Search tenants..."
                               value="<?php echo e($search); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="moved_out" <?php echo $status === 'moved_out' ? 'selected' : ''; ?>>Moved Out</option>
                        <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tenant List -->
    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($tenants)): ?>
            <div class="empty-state py-5">
                <i class="bi bi-people"></i>
                <h5>No tenants found</h5>
                <p>Add your first tenant to get started.</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tenant Name</th>
                            <th>Contact</th>
                            <th>Property / Room</th>
                            <th>Move-in Date</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tenants as $tenant): ?>
                        <tr>
                            <td><span class="badge bg-secondary"><?php echo e($tenant['tenant_id']); ?></span></td>
                            <td>
                                <strong><?php echo e($tenant['tenant_name']); ?></strong>
                                <?php if (!empty($tenant['email'])): ?>
                                <br><small class="text-muted"><?php echo e($tenant['email']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($tenant['phone_no'])): ?>
                                <i class="bi bi-telephone me-1 text-muted"></i><?php echo e($tenant['phone_no']); ?>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($tenant['property_name'])): ?>
                                <?php echo e($tenant['property_name']); ?>
                                <br><small class="text-muted"><?php echo e($tenant['room_name']); ?> - <?php echo e($tenant['bedspace_name']); ?></small>
                                <?php else: ?>
                                <span class="text-muted">No active lease</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo formatDate($tenant['move_in_date']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo e($tenant['tenant_status']); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', e($tenant['tenant_status']))); ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary btn-icon"
                                        onclick="openEditModal(<?php echo $tenant['recid']; ?>)"
                                        data-bs-toggle="tooltip" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-icon"
                                        onclick="deleteTenant(<?php echo $tenant['recid']; ?>, '<?php echo e($tenant['tenant_name']); ?>')"
                                        data-bs-toggle="tooltip" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
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

<!-- Tenant Modal -->
<div class="modal fade" id="tenantModal" tabindex="-1" aria-labelledby="tenantModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tenantModalLabel">Add Tenant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="tenantForm">
                <div class="modal-body">
                    <input type="hidden" name="recid" id="tenantRecid">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tenant Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="tenant_name" id="tenantName" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control" name="phone_no" id="tenantPhone">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="tenantEmail">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="tenant_status" id="tenantStatus">
                                <option value="active">Active</option>
                                <option value="moved_out">Moved Out</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Move-in Date</label>
                            <input type="date" class="form-control" name="move_in_date" id="tenantMoveIn">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Move-out Date</label>
                            <input type="date" class="form-control" name="move_out_date" id="tenantMoveOut">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Emergency Contact Name</label>
                            <input type="text" class="form-control" name="emergency_contact_name" id="tenantEmergencyName">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Emergency Contact No.</label>
                            <input type="text" class="form-control" name="emergency_contact_no" id="tenantEmergencyNo">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" name="remarks" id="tenantRemarks" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="tenantSubmitBtn">Save Tenant</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var isEditMode = false;

function openAddModal() {
    isEditMode = false;
    $('#tenantModalLabel').text('Add Tenant');
    $('#tenantSubmitBtn').text('Save Tenant');
    resetForm('#tenantForm');
    $('#tenantStatus').val('active');
}

function openEditModal(recid) {
    isEditMode = true;
    $('#tenantModalLabel').text('Edit Tenant');
    $('#tenantSubmitBtn').text('Update Tenant');
    resetForm('#tenantForm');

    ajaxPost('tenant.get', { recid: recid }, function(response) {
        var tenant = response.data;
        $('#tenantRecid').val(tenant.recid);
        $('#tenantName').val(tenant.tenant_name);
        $('#tenantPhone').val(tenant.phone_no);
        $('#tenantEmail').val(tenant.email);
        $('#tenantStatus').val(tenant.tenant_status);
        $('#tenantMoveIn').val(tenant.move_in_date);
        $('#tenantMoveOut').val(tenant.move_out_date);
        $('#tenantEmergencyName').val(tenant.emergency_contact_name);
        $('#tenantEmergencyNo').val(tenant.emergency_contact_no);
        $('#tenantRemarks').val(tenant.remarks);

        var modalEl = document.getElementById('tenantModal');
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    });
}

function deleteTenant(recid, name) {
    if (confirm('Are you sure you want to delete tenant "' + name + '"?')) {
        ajaxPost('tenant.delete', { recid: recid }, function(response) {
            showSuccess(response.message);
            location.reload();
        });
    }
}

$('#tenantForm').on('submit', function(e) {
    e.preventDefault();

    var action = isEditMode ? 'tenant.edit' : 'tenant.add';

    ajaxPost(action, $(this).serialize(), function(response) {
        showSuccess(response.message);
        bootstrap.Modal.getInstance(document.getElementById('tenantModal')).hide();
        location.reload();
    });
});
</script>
