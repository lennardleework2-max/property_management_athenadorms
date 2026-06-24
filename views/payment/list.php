<div class="content-wrapper">
    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h1 class="page-title">Payment Verification</h1>
            <p class="page-subtitle">Verify and manage tenant payments</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#paymentModal" onclick="openAddModal()">
            <i class="bi bi-plus-lg me-1"></i> Record Payment
        </button>
    </div>

    <!-- Quick Stats -->
    <div class="row g-3 mb-4">
        <?php
        $pending = 0; $verified = 0; $rejected = 0;
        foreach ($payments as $p) {
            if ($p['payment_status'] === 'pending_verification') $pending++;
            elseif ($p['payment_status'] === 'verified' || $p['payment_status'] === 'cleared') $verified++;
            elseif ($p['payment_status'] === 'rejected' || $p['payment_status'] === 'bounced') $rejected++;
        }
        ?>
        <div class="col-md-4">
            <div class="card stat-card warning">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-value"><?php echo $pending; ?></div>
                            <div class="stat-label">Pending Verification</div>
                        </div>
                        <i class="bi bi-hourglass-split fs-1 text-warning opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card success">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-value"><?php echo $verified; ?></div>
                            <div class="stat-label">Verified/Cleared</div>
                        </div>
                        <i class="bi bi-check-circle fs-1 text-success opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card danger">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-value"><?php echo $rejected; ?></div>
                            <div class="stat-label">Rejected/Bounced</div>
                        </div>
                        <i class="bi bi-x-circle fs-1 text-danger opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="index.php" class="row g-3">
                <input type="hidden" name="action" value="payment.list">
                <div class="col-md-5">
                    <div class="search-box">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" class="form-control" name="search" placeholder="Search tenant or reference..."
                               value="<?php echo e($search); ?>">
                    </div>
                </div>
                <div class="col-md-5">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="pending_verification" <?php echo $status === 'pending_verification' ? 'selected' : ''; ?>>Pending Verification</option>
                        <option value="verified" <?php echo $status === 'verified' ? 'selected' : ''; ?>>Verified</option>
                        <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        <option value="cleared" <?php echo $status === 'cleared' ? 'selected' : ''; ?>>Cleared</option>
                        <option value="bounced" <?php echo $status === 'bounced' ? 'selected' : ''; ?>>Bounced</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Payment List -->
    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($payments)): ?>
            <div class="empty-state py-5">
                <i class="bi bi-credit-card"></i>
                <h5>No payments found</h5>
                <p>Record payments as they come in.</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tenant</th>
                            <th>Room</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th>Date</th>
                            <th>Proof</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                        <tr class="<?php echo $payment['payment_status'] === 'pending_verification' ? 'table-warning' : ''; ?>">
                            <td><span class="badge bg-secondary"><?php echo e($payment['payment_id']); ?></span></td>
                            <td>
                                <strong><?php echo e($payment['tenant_name']); ?></strong>
                                <br><small class="text-muted"><?php echo e($payment['phone_no']); ?></small>
                            </td>
                            <td>
                                <small><?php echo e($payment['room_name']); ?></small>
                                <?php if (!empty($payment['bedspace_name'])): ?>
                                <br><small class="text-muted"><?php echo e($payment['bedspace_name']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold text-primary"><?php echo formatCurrency($payment['payment_amount']); ?></td>
                            <td>
                                <span class="badge bg-info"><?php echo ucfirst(str_replace('_', ' ', e($payment['payment_method']))); ?></span>
                            </td>
                            <td>
                                <?php if (!empty($payment['reference_no'])): ?>
                                <small><?php echo e($payment['reference_no']); ?></small>
                                <?php elseif (!empty($payment['check_no'])): ?>
                                <small>Check: <?php echo e($payment['check_no']); ?></small>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo formatDate($payment['payment_date']); ?></td>
                            <td>
                                <?php if (!empty($payment['proof_url'])): ?>
                                <?php
                                $proofUrl = e($payment['proof_url']);
                                $isPdf = str_ends_with(strtolower($proofUrl), '.pdf');
                                ?>
                                <?php if ($isPdf): ?>
                                <a href="<?php echo $proofUrl; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-file-pdf me-1"></i>View PDF
                                </a>
                                <?php else: ?>
                                <img src="<?php echo $proofUrl; ?>" alt="Proof" class="proof-thumbnail"
                                     onclick="viewProof('<?php echo $proofUrl; ?>')" title="Click to enlarge">
                                <?php endif; ?>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo e($payment['payment_status']); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', e($payment['payment_status']))); ?>
                                </span>
                                <?php if (!empty($payment['rejection_reason'])): ?>
                                <br><small class="text-danger"><?php echo e($payment['rejection_reason']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ($payment['payment_status'] === 'pending_verification'): ?>
                                <button type="button" class="btn btn-sm btn-success btn-icon"
                                        onclick="verifyPayment(<?php echo $payment['recid']; ?>)"
                                        data-bs-toggle="tooltip" title="Verify">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger btn-icon"
                                        onclick="openRejectModal(<?php echo $payment['recid']; ?>)"
                                        data-bs-toggle="tooltip" title="Reject">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                                <?php endif; ?>
                                <button type="button" class="btn btn-sm btn-outline-primary btn-icon"
                                        onclick="openEditModal(<?php echo $payment['recid']; ?>)"
                                        data-bs-toggle="tooltip" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-icon"
                                        onclick="deletePayment(<?php echo $payment['recid']; ?>)"
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

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Record Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="paymentForm">
                <div class="modal-body">
                    <input type="hidden" name="recid" id="paymentRecid">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tenant/Lease <span class="text-danger">*</span></label>
                            <select class="form-select" name="lease_recid" id="paymentLease" required>
                                <option value="">Select Tenant</option>
                                <?php foreach ($leases as $lease): ?>
                                <option value="<?php echo $lease['recid']; ?>">
                                    <?php echo e($lease['tenant_name']); ?> - <?php echo e($lease['room_name']); ?>
                                    <?php if (!empty($lease['bedspace_name'])): ?>(<?php echo e($lease['bedspace_name']); ?>)<?php endif; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Amount <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="payment_amount" id="paymentAmount" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="payment_date" id="paymentDate" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select class="form-select" name="payment_method" id="paymentMethod" required onchange="toggleMethodFields()">
                                <option value="">Select Method</option>
                                <option value="gcash">GCash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cash">Cash</option>
                                <option value="post_dated_check">Post-Dated Check</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="referenceNoGroup">
                            <label class="form-label">Reference No.</label>
                            <input type="text" class="form-control" name="reference_no" id="paymentRefNo">
                        </div>
                        <div class="col-md-6" id="bankNameGroup" style="display:none;">
                            <label class="form-label">Bank Name</label>
                            <input type="text" class="form-control" name="bank_name" id="paymentBank">
                        </div>
                        <div class="col-md-6" id="checkNoGroup" style="display:none;">
                            <label class="form-label">Check No.</label>
                            <input type="text" class="form-control" name="check_no" id="paymentCheckNo">
                        </div>
                        <div class="col-md-6" id="checkDateGroup" style="display:none;">
                            <label class="form-label">Check Date</label>
                            <input type="date" class="form-control" name="check_date" id="paymentCheckDate">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="payment_status" id="paymentStatus">
                                <option value="pending_verification">Pending Verification</option>
                                <option value="verified">Verified</option>
                                <option value="cleared">Cleared</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Payment Proof</label>
                            <input type="file" class="form-control" name="proof_file" id="paymentProofFile"
                                   accept="image/jpeg,image/png,image/webp,application/pdf">
                            <small class="text-muted">Upload GCash screenshot or payment proof (JPG, PNG, WEBP, PDF - Max 5MB)</small>
                            <div id="existingProof" class="mt-2" style="display: none;">
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Proof already uploaded</span>
                                <a href="#" id="existingProofLink" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                                    <i class="bi bi-eye me-1"></i>View Current Proof
                                </a>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Or Proof URL (if external link)</label>
                            <input type="url" class="form-control" name="proof_url" id="paymentProof" placeholder="https://...">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" name="remarks" id="paymentRemarks" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="paymentSubmitBtn">Save Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Proof View Modal -->
<div class="modal fade" id="proofModal" tabindex="-1" aria-labelledby="proofModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="proofModalLabel"><i class="bi bi-image me-2"></i>Payment Proof</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <img src="" id="proofImage" class="img-fluid rounded shadow" alt="Payment Proof" style="max-height: 70vh;">
            </div>
            <div class="modal-footer">
                <a href="" id="proofDownloadLink" target="_blank" class="btn btn-primary">
                    <i class="bi bi-download me-1"></i>Open in New Tab
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectModalLabel"><i class="bi bi-x-circle me-2"></i>Reject Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectForm">
                <div class="modal-body">
                    <input type="hidden" name="recid" id="rejectRecid">
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="rejection_reason" id="rejectionReason" rows="3" required
                                  placeholder="e.g., Screenshot unclear, Amount doesn't match, Fake proof, etc."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var isEditMode = false;

// View proof image in modal
function viewProof(url) {
    $('#proofImage').attr('src', url);
    $('#proofDownloadLink').attr('href', url);
    var proofModal = new bootstrap.Modal(document.getElementById('proofModal'));
    proofModal.show();
}

function toggleMethodFields() {
    var method = $('#paymentMethod').val();
    $('#bankNameGroup, #checkNoGroup, #checkDateGroup').hide();

    if (method === 'bank_transfer') {
        $('#bankNameGroup').show();
    } else if (method === 'post_dated_check') {
        $('#bankNameGroup, #checkNoGroup, #checkDateGroup').show();
    }
}

function openAddModal() {
    isEditMode = false;
    $('#paymentModalLabel').text('Record Payment');
    $('#paymentSubmitBtn').text('Save Payment');
    resetForm('#paymentForm');
    $('#paymentDate').val(new Date().toISOString().split('T')[0]);
    $('#paymentStatus').val('pending_verification');
    $('#paymentProofFile').val('');
    $('#existingProof').hide();
    toggleMethodFields();
}

function openEditModal(recid) {
    isEditMode = true;
    $('#paymentModalLabel').text('Edit Payment');
    $('#paymentSubmitBtn').text('Update Payment');
    resetForm('#paymentForm');
    $('#paymentProofFile').val('');

    ajaxPost('payment.get', { recid: recid }, function(response) {
        var payment = response.data;
        $('#paymentRecid').val(payment.recid);
        $('#paymentLease').val(payment.lease_recid);
        $('#paymentAmount').val(payment.payment_amount);
        $('#paymentDate').val(payment.payment_date);
        $('#paymentMethod').val(payment.payment_method);
        $('#paymentRefNo').val(payment.reference_no);
        $('#paymentBank').val(payment.bank_name);
        $('#paymentCheckNo').val(payment.check_no);
        $('#paymentCheckDate').val(payment.check_date);
        $('#paymentStatus').val(payment.payment_status);
        $('#paymentProof').val(payment.proof_url);
        $('#paymentRemarks').val(payment.remarks);
        toggleMethodFields();

        // Show existing proof if available
        if (payment.proof_url) {
            $('#existingProof').show();
            $('#existingProofLink').attr('href', payment.proof_url);
        } else {
            $('#existingProof').hide();
        }

        var modalEl = document.getElementById('paymentModal');
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    });
}

function verifyPayment(recid) {
    if (confirm('Are you sure you want to VERIFY this payment?')) {
        ajaxPost('payment.verify', { recid: recid }, function(response) {
            showSuccess(response.message);
            location.reload();
        });
    }
}

function openRejectModal(recid) {
    $('#rejectRecid').val(recid);
    $('#rejectionReason').val('');
    var modalEl = document.getElementById('rejectModal');
    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
}

function deletePayment(recid) {
    if (confirm('Are you sure you want to delete this payment?')) {
        ajaxPost('payment.delete', { recid: recid }, function(response) {
            showSuccess(response.message);
            location.reload();
        });
    }
}

$('#paymentForm').on('submit', function(e) {
    e.preventDefault();
    var action = isEditMode ? 'payment.edit' : 'payment.add';
    var fileInput = document.getElementById('paymentProofFile');
    var file = fileInput ? fileInput.files[0] : null;

    // If there's a file, upload it first
    if (file) {
        var formData = new FormData();
        formData.append('proof_file', file);
        formData.append('csrf_token', $('input[name="csrf_token"]').val());

        $.ajax({
            url: 'index.php?action=payment.upload.proof',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Set the proof URL and submit the form
                    $('#paymentProof').val(response.data.url);
                    submitPaymentForm(action);
                } else {
                    showError(response.message || 'Failed to upload proof.');
                }
            },
            error: function() {
                showError('Failed to upload proof file.');
            }
        });
    } else {
        // No file, just submit the form
        submitPaymentForm(action);
    }
});

function submitPaymentForm(action) {
    ajaxPost(action, $('#paymentForm').serialize(), function(response) {
        showSuccess(response.message);
        bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
        location.reload();
    });
}

$('#rejectForm').on('submit', function(e) {
    e.preventDefault();

    ajaxPost('payment.reject', $(this).serialize(), function(response) {
        showSuccess(response.message);
        bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
        location.reload();
    });
});
</script>
