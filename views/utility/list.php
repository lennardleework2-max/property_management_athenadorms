<div class="content-wrapper">
    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h1 class="page-title">Utility Computation</h1>
            <p class="page-subtitle">Compute and allocate water/electricity bills</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#utilityModal" onclick="openAddModal()">
            <i class="bi bi-plus-lg me-1"></i> Add Utility Bill
        </button>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="index.php" class="row g-3">
                <input type="hidden" name="action" value="utility.list">
                <div class="col-md-4">
                    <select class="form-select" name="property">
                        <option value="">All Properties</option>
                        <?php foreach ($properties as $property): ?>
                        <option value="<?php echo $property['recid']; ?>" <?php echo $selectedProperty == $property['recid'] ? 'selected' : ''; ?>>
                            <?php echo e($property['property_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="type">
                        <option value="">All Types</option>
                        <option value="electricity" <?php echo $selectedType === 'electricity' ? 'selected' : ''; ?>>Electricity</option>
                        <option value="water" <?php echo $selectedType === 'water' ? 'selected' : ''; ?>>Water</option>
                        <option value="other" <?php echo $selectedType === 'other' ? 'selected' : ''; ?>>Other</option>
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

    <!-- Utility List -->
    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($utilities)): ?>
            <div class="empty-state py-5">
                <i class="bi bi-lightning-charge"></i>
                <h5>No utility bills found</h5>
                <p>Add utility bills to compute and allocate to tenants.</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Billing Month</th>
                            <th>Property / Room</th>
                            <th>Type</th>
                            <th>Reading</th>
                            <th>Consumption</th>
                            <th>Rate</th>
                            <th>Total Amount</th>
                            <th>Split Method</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($utilities as $utility): ?>
                        <tr>
                            <td><span class="badge bg-secondary"><?php echo e($utility['utility_id']); ?></span></td>
                            <td><?php echo date('F Y', strtotime($utility['billing_month'])); ?></td>
                            <td>
                                <?php echo e($utility['property_name']); ?>
                                <br><small class="text-muted"><?php echo e($utility['room_name']); ?></small>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $utility['utility_type'] === 'electricity' ? 'warning' : ($utility['utility_type'] === 'water' ? 'info' : 'secondary'); ?>">
                                    <?php echo ucfirst(e($utility['utility_type'])); ?>
                                </span>
                            </td>
                            <td>
                                <small>Prev: <?php echo number_format($utility['previous_reading'], 2); ?></small>
                                <br><small>Curr: <?php echo number_format($utility['current_reading'], 2); ?></small>
                            </td>
                            <td class="fw-bold"><?php echo number_format($utility['consumption'], 2); ?></td>
                            <td><?php echo formatCurrency($utility['rate'] ?? 0); ?></td>
                            <td class="text-primary fw-bold"><?php echo formatCurrency($utility['total_amount']); ?></td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?php echo ucfirst(str_replace('_', ' ', e($utility['split_method']))); ?>
                                </span>
                                <br><small class="text-muted"><?php echo (int)$utility['allocation_count']; ?> tenants</small>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo e($utility['utility_status']); ?>">
                                    <?php echo ucfirst(e($utility['utility_status'])); ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-info btn-icon"
                                        onclick="viewAllocations(<?php echo $utility['recid']; ?>)"
                                        data-bs-toggle="tooltip" title="View Allocations">
                                    <i class="bi bi-pie-chart"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary btn-icon"
                                        onclick="openEditModal(<?php echo $utility['recid']; ?>)"
                                        data-bs-toggle="tooltip" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-icon"
                                        onclick="deleteUtility(<?php echo $utility['recid']; ?>)"
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

<!-- Utility Modal -->
<div class="modal fade" id="utilityModal" tabindex="-1" aria-labelledby="utilityModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="utilityModalLabel">Add Utility Bill</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="utilityForm">
                <div class="modal-body">
                    <input type="hidden" name="recid" id="utilityRecid">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Property <span class="text-danger">*</span></label>
                            <select class="form-select" name="property_recid" id="utilityProperty" required onchange="loadRooms(this.value)">
                                <option value="">Select Property</option>
                                <?php foreach ($properties as $property): ?>
                                <option value="<?php echo $property['recid']; ?>"><?php echo e($property['property_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Room <span class="text-danger">*</span></label>
                            <select class="form-select" name="room_recid" id="utilityRoom" required>
                                <option value="">Select Room</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Utility Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="utility_type" id="utilityType" required>
                                <option value="">Select Type</option>
                                <option value="electricity">Electricity</option>
                                <option value="water">Water</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Billing Month <span class="text-danger">*</span></label>
                            <input type="month" class="form-control" name="billing_month" id="utilityMonth" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Previous Reading</label>
                            <input type="number" class="form-control" name="previous_reading" id="utilityPrevReading" step="0.01" value="0" onchange="computeConsumption()">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Current Reading</label>
                            <input type="number" class="form-control" name="current_reading" id="utilityCurrReading" step="0.01" value="0" onchange="computeConsumption()">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Consumption</label>
                            <input type="text" class="form-control" id="utilityConsumptionDisplay" readonly>
                            <input type="hidden" name="consumption" id="utilityConsumption">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Rate per Unit <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="rate" id="utilityRate" step="0.01" value="10.00" required onchange="computeTotalAmount()">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Total Amount</label>
                            <input type="number" class="form-control" name="total_amount" id="utilityAmount" step="0.01" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Split Method</label>
                            <select class="form-select" name="split_method" id="utilitySplitMethod">
                                <option value="equal_active_tenants">Equal Among Active Tenants</option>
                                <option value="manual_adjusted">Manual Adjusted</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="utility_status" id="utilityStatus">
                                <option value="draft">Draft</option>
                                <option value="computed">Computed</option>
                                <option value="posted">Posted</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" name="remarks" id="utilityRemarks" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="utilitySubmitBtn">Save Utility Bill</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Allocations Modal -->
<div class="modal fade" id="allocationsModal" tabindex="-1" aria-labelledby="allocationsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="allocationsModalLabel">Utility Allocations</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="allocationsContainer">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
var isEditMode = false;

function computeConsumption() {
    var prev = parseFloat($('#utilityPrevReading').val()) || 0;
    var curr = parseFloat($('#utilityCurrReading').val()) || 0;
    var consumption = curr - prev;
    $('#utilityConsumption').val(consumption.toFixed(2));
    $('#utilityConsumptionDisplay').val(consumption.toFixed(2));
    computeTotalAmount();
}

function computeTotalAmount() {
    var consumption = parseFloat($('#utilityConsumption').val()) || 0;
    var rate = parseFloat($('#utilityRate').val()) || 0;
    var totalAmount = consumption * rate;
    $('#utilityAmount').val(totalAmount.toFixed(2));
}

function loadRooms(propertyRecid) {
    if (!propertyRecid) {
        $('#utilityRoom').html('<option value="">Select Room</option>');
        return;
    }

    ajaxPost('room.get.by.property', { property_recid: propertyRecid }, function(response) {
        var html = '<option value="">Select Room</option>';
        response.data.forEach(function(room) {
            html += '<option value="' + room.recid + '">' + room.room_name + '</option>';
        });
        $('#utilityRoom').html(html);
    });
}

function openAddModal() {
    isEditMode = false;
    $('#utilityModalLabel').text('Add Utility Bill');
    $('#utilitySubmitBtn').text('Save Utility Bill');
    resetForm('#utilityForm');
    $('#utilityRoom').html('<option value="">Select Room</option>');
    $('#utilityStatus').val('draft');
    $('#utilitySplitMethod').val('equal_active_tenants');
    $('#utilityRate').val('10.00');
    $('#utilityConsumption').val('0');
    $('#utilityConsumptionDisplay').val('0.00');
    $('#utilityAmount').val('0.00');

    // Set current month
    var now = new Date();
    var month = (now.getMonth() + 1).toString().padStart(2, '0');
    $('#utilityMonth').val(now.getFullYear() + '-' + month);
}

function openEditModal(recid) {
    isEditMode = true;
    $('#utilityModalLabel').text('Edit Utility Bill');
    $('#utilitySubmitBtn').text('Update Utility Bill');
    resetForm('#utilityForm');

    ajaxPost('utility.get', { recid: recid }, function(response) {
        var utility = response.data;
        $('#utilityRecid').val(utility.recid);
        $('#utilityProperty').val(utility.property_recid);
        $('#utilityType').val(utility.utility_type);
        $('#utilityMonth').val(utility.billing_month.substring(0, 7));
        $('#utilityPrevReading').val(utility.previous_reading);
        $('#utilityCurrReading').val(utility.current_reading);
        $('#utilityRate').val(utility.rate || 10.00);
        $('#utilityAmount').val(utility.total_amount);
        $('#utilitySplitMethod').val(utility.split_method);
        $('#utilityStatus').val(utility.utility_status);
        $('#utilityRemarks').val(utility.remarks);

        computeConsumption();

        // Load rooms then set value
        loadRooms(utility.property_recid);
        setTimeout(function() {
            $('#utilityRoom').val(utility.room_recid);
        }, 500);

        var modalEl = document.getElementById('utilityModal');
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    });
}

function viewAllocations(recid) {
    $('#allocationsContainer').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');

    var modalEl = document.getElementById('allocationsModal');
    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();

    ajaxPost('utility.get', { recid: recid }, function(response) {
        var utility = response.data;
        var allocations = utility.allocations || [];

        var html = '<div class="mb-3">';
        html += '<strong>Room:</strong> ' + utility.room_name + '<br>';
        html += '<strong>Type:</strong> ' + utility.utility_type + '<br>';
        html += '<strong>Consumption:</strong> ' + parseFloat(utility.consumption || 0).toFixed(2) + ' units<br>';
        html += '<strong>Rate:</strong> ' + formatCurrency(utility.rate || 0) + ' per unit<br>';
        html += '<strong>Total Amount:</strong> ' + formatCurrency(utility.total_amount) + '<br>';
        html += '<strong>Split Method:</strong> ' + utility.split_method.replace(/_/g, ' ') + '</div>';

        if (allocations.length === 0) {
            html += '<div class="alert alert-info">No allocations yet.</div>';
        } else {
            html += '<table class="table table-sm"><thead><tr><th>Tenant</th><th>Base Amount</th><th>Adjustment</th><th>Final Amount</th><th>Remarks</th></tr></thead><tbody>';
            allocations.forEach(function(alloc) {
                html += '<tr>';
                html += '<td>' + alloc.tenant_name + '</td>';
                html += '<td>' + formatCurrency(alloc.base_amount) + '</td>';
                html += '<td>' + formatCurrency(alloc.adjustment_amount) + '</td>';
                html += '<td class="fw-bold">' + formatCurrency(alloc.final_amount) + '</td>';
                html += '<td>' + (alloc.remarks || '-') + '</td>';
                html += '</tr>';
            });
            html += '</tbody></table>';
        }

        $('#allocationsContainer').html(html);
    });
}

function deleteUtility(recid) {
    if (confirm('Are you sure you want to delete this utility bill?')) {
        ajaxPost('utility.delete', { recid: recid }, function(response) {
            showSuccess(response.message);
            location.reload();
        });
    }
}

$('#utilityForm').on('submit', function(e) {
    e.preventDefault();
    var action = isEditMode ? 'utility.edit' : 'utility.add';

    ajaxPost(action, $(this).serialize(), function(response) {
        showSuccess(response.message);
        bootstrap.Modal.getInstance(document.getElementById('utilityModal')).hide();
        location.reload();
    });
});
</script>
