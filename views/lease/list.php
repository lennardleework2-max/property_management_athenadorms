<div class="content-wrapper">
    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h1 class="page-title">Leases & Contracts</h1>
            <p class="page-subtitle">Manage tenant lease agreements</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#leaseModal" onclick="openAddModal()">
            <i class="bi bi-plus-lg me-1"></i> Add Lease
        </button>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="index.php" class="row g-3">
                <input type="hidden" name="action" value="lease.list">
                <div class="col-md-8">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="expiring_soon" <?php echo $status === 'expiring_soon' ? 'selected' : ''; ?>>Expiring Soon</option>
                        <option value="expired" <?php echo $status === 'expired' ? 'selected' : ''; ?>>Expired</option>
                        <option value="terminated" <?php echo $status === 'terminated' ? 'selected' : ''; ?>>Terminated</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lease List -->
    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($leases)): ?>
            <div class="empty-state py-5">
                <i class="bi bi-file-earmark-text"></i>
                <h5>No leases found</h5>
                <p>Create a lease to assign tenants to rooms.</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tenant</th>
                            <th>Property / Room</th>
                            <th>Lease Type</th>
                            <th>Period</th>
                            <th>Monthly Rent</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leases as $lease): ?>
                        <tr>
                            <td><span class="badge bg-secondary"><?php echo e($lease['lease_id']); ?></span></td>
                            <td>
                                <strong><?php echo e($lease['tenant_name']); ?></strong>
                                <?php if (!empty($lease['phone_no'])): ?>
                                <br><small class="text-muted"><?php echo e($lease['phone_no']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo e($lease['property_name']); ?>
                                <br><small class="text-muted"><?php echo e($lease['room_name']); ?>
                                <?php if (!empty($lease['bedspace_name'])): ?> - <?php echo e($lease['bedspace_name']); ?><?php endif; ?>
                                </small>
                            </td>
                            <td><span class="badge bg-info"><?php echo ucfirst(e($lease['lease_type'])); ?></span></td>
                            <td>
                                <?php echo formatDate($lease['start_date']); ?>
                                <?php if (!empty($lease['end_date'])): ?>
                                <br><small class="text-muted">to <?php echo formatDate($lease['end_date']); ?></small>
                                <?php else: ?>
                                <br><small class="text-muted">Ongoing</small>
                                <?php endif; ?>
                            </td>
                            <td class="text-primary fw-bold"><?php echo formatCurrency($lease['monthly_rent']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo e($lease['lease_status']); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', e($lease['lease_status']))); ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary btn-icon"
                                        onclick="openEditModal(<?php echo $lease['recid']; ?>)"
                                        data-bs-toggle="tooltip" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-icon"
                                        onclick="deleteLease(<?php echo $lease['recid']; ?>, '<?php echo e($lease['tenant_name']); ?>')"
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

<!-- Lease Modal -->
<div class="modal fade" id="leaseModal" tabindex="-1" aria-labelledby="leaseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="leaseModalLabel">Add Lease</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="leaseForm">
                <div class="modal-body">
                    <input type="hidden" name="recid" id="leaseRecid">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tenant <span class="text-danger">*</span></label>
                            <select class="form-select" name="tenant_recid" id="leaseTenant" required>
                                <option value="">Select Tenant</option>
                                <?php foreach ($tenants as $tenant): ?>
                                <option value="<?php echo $tenant['recid']; ?>"><?php echo e($tenant['tenant_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Property <span class="text-danger">*</span></label>
                            <select class="form-select" name="property_recid" id="leaseProperty" required onchange="loadRooms(this.value)">
                                <option value="">Select Property</option>
                                <?php foreach ($properties as $property): ?>
                                <option value="<?php echo $property['recid']; ?>"><?php echo e($property['property_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Room <span class="text-danger">*</span></label>
                            <select class="form-select" name="room_recid" id="leaseRoom" required onchange="loadBedspaces(this.value)">
                                <option value="">Select Room</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bedspace</label>
                            <select class="form-select" name="bedspace_recid" id="leaseBedspace">
                                <option value="">Select Bedspace (Optional)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lease Type</label>
                            <select class="form-select" name="lease_type" id="leaseType">
                                <option value="monthly">Monthly</option>
                                <option value="annual">Annual</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="lease_status" id="leaseStatus">
                                <option value="active">Active</option>
                                <option value="expiring_soon">Expiring Soon</option>
                                <option value="expired">Expired</option>
                                <option value="terminated">Terminated</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="start_date" id="leaseStartDate" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date" id="leaseEndDate">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Monthly Rent</label>
                            <input type="number" class="form-control" name="monthly_rent" id="leaseRent" step="0.01" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Security Deposit</label>
                            <input type="number" class="form-control" name="security_deposit" id="leaseDeposit" step="0.01" value="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" name="remarks" id="leaseRemarks" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="leaseSubmitBtn">Save Lease</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var isEditMode = false;

function openAddModal() {
    isEditMode = false;
    $('#leaseModalLabel').text('Add Lease');
    $('#leaseSubmitBtn').text('Save Lease');
    resetForm('#leaseForm');
    $('#leaseRoom').html('<option value="">Select Room</option>');
    $('#leaseBedspace').html('<option value="">Select Bedspace (Optional)</option>');
    $('#leaseStatus').val('active');
    $('#leaseType').val('monthly');
}

function openEditModal(recid) {
    isEditMode = true;
    $('#leaseModalLabel').text('Edit Lease');
    $('#leaseSubmitBtn').text('Update Lease');
    resetForm('#leaseForm');

    ajaxPost('lease.get', { recid: recid }, function(response) {
        var lease = response.data;
        $('#leaseRecid').val(lease.recid);
        $('#leaseTenant').val(lease.tenant_recid);
        $('#leaseProperty').val(lease.property_recid);
        $('#leaseType').val(lease.lease_type);
        $('#leaseStatus').val(lease.lease_status);
        $('#leaseStartDate').val(lease.start_date);
        $('#leaseEndDate').val(lease.end_date);
        $('#leaseRent').val(lease.monthly_rent);
        $('#leaseDeposit').val(lease.security_deposit);
        $('#leaseRemarks').val(lease.remarks);

        // Load rooms then set value
        loadRooms(lease.property_recid, function() {
            $('#leaseRoom').val(lease.room_recid);
            loadBedspaces(lease.room_recid, function() {
                $('#leaseBedspace').val(lease.bedspace_recid);
            });
        });

        var modalEl = document.getElementById('leaseModal');
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    });
}

function loadRooms(propertyRecid, callback) {
    if (!propertyRecid) {
        $('#leaseRoom').html('<option value="">Select Room</option>');
        $('#leaseBedspace').html('<option value="">Select Bedspace (Optional)</option>');
        return;
    }

    ajaxPost('room.get.by.property', { property_recid: propertyRecid }, function(response) {
        var html = '<option value="">Select Room</option>';
        response.data.forEach(function(room) {
            html += '<option value="' + room.recid + '">' + room.room_name + ' (₱' + parseFloat(room.monthly_room_rate).toFixed(2) + ')</option>';
        });
        $('#leaseRoom').html(html);

        if (typeof callback === 'function') callback();
    });
}

function loadBedspaces(roomRecid, callback) {
    if (!roomRecid) {
        $('#leaseBedspace').html('<option value="">Select Bedspace (Optional)</option>');
        return;
    }

    ajaxPost('bedspace.get.by.room', { room_recid: roomRecid }, function(response) {
        var html = '<option value="">Select Bedspace (Optional)</option>';
        response.data.forEach(function(bed) {
            html += '<option value="' + bed.recid + '">' + bed.bedspace_name + ' (' + bed.bedspace_status + ')</option>';
        });
        $('#leaseBedspace').html(html);

        if (typeof callback === 'function') callback();
    });
}

function deleteLease(recid, name) {
    if (confirm('Are you sure you want to delete lease for "' + name + '"?')) {
        ajaxPost('lease.delete', { recid: recid }, function(response) {
            showSuccess(response.message);
            location.reload();
        });
    }
}

$('#leaseForm').on('submit', function(e) {
    e.preventDefault();

    var action = isEditMode ? 'lease.edit' : 'lease.add';

    ajaxPost(action, $(this).serialize(), function(response) {
        showSuccess(response.message);
        bootstrap.Modal.getInstance(document.getElementById('leaseModal')).hide();
        location.reload();
    });
});
</script>
