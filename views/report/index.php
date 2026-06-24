<div class="content-wrapper">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <h1 class="page-title">Reports</h1>
        <p class="page-subtitle">View tenant balances, pending payments, and more</p>
    </div>

    <!-- Report Tabs -->
    <ul class="nav nav-tabs mb-4" id="reportTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="balance-tab" data-bs-toggle="tab" data-bs-target="#balance-panel"
                    type="button" role="tab" onclick="loadBalanceReport()">
                <i class="bi bi-wallet2 me-1"></i> Tenant Balances
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending-panel"
                    type="button" role="tab" onclick="loadPendingReport()">
                <i class="bi bi-hourglass-split me-1"></i> Pending Verification
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="overdue-tab" data-bs-toggle="tab" data-bs-target="#overdue-panel"
                    type="button" role="tab" onclick="loadOverdueReport()">
                <i class="bi bi-exclamation-triangle me-1"></i> Overdue Tenants
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="utility-tab" data-bs-toggle="tab" data-bs-target="#utility-panel"
                    type="button" role="tab" onclick="loadUtilityReport()">
                <i class="bi bi-lightning-charge me-1"></i> Utility Summary
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="expiring-tab" data-bs-toggle="tab" data-bs-target="#expiring-panel"
                    type="button" role="tab" onclick="loadExpiringReport()">
                <i class="bi bi-calendar-x me-1"></i> Expiring Contracts
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="reportTabContent">
        <!-- Tenant Balances -->
        <div class="tab-pane fade show active" id="balance-panel" role="tabpanel">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Billing Month</label>
                            <select class="form-select" id="balanceMonth" onchange="loadBalanceReport()">
                                <option value="">All Months</option>
                                <?php foreach ($billingMonths as $month): ?>
                                <option value="<?php echo e($month['billing_month']); ?>">
                                    <?php echo date('F Y', strtotime($month['billing_month'])); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="balanceStatus" onchange="loadBalanceReport()">
                                <option value="">All Status</option>
                                <option value="paid">Paid</option>
                                <option value="partial">Partial</option>
                                <option value="pending_verification">Pending Verification</option>
                                <option value="overdue">Overdue</option>
                                <option value="unpaid">Unpaid</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <button type="button" class="btn btn-outline-success" onclick="exportToExcel('balanceTable', 'tenant_balances')">
                                <i class="bi bi-file-earmark-excel me-1"></i> Excel
                            </button>
                            <button type="button" class="btn btn-outline-danger" onclick="exportToPDF('balanceTable', 'Tenant Balances Report')">
                                <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                            </button>
                            <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                                <i class="bi bi-printer me-1"></i> Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div id="balanceSummary" class="row g-3 mb-4"></div>

            <div class="card">
                <div class="card-body p-0">
                    <div id="balanceTable" class="table-responsive">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Verification -->
        <div class="tab-pane fade" id="pending-panel" role="tabpanel">
            <div class="card mb-3">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="exportToExcel('pendingTable', 'pending_verification')">
                            <i class="bi bi-file-earmark-excel me-1"></i> Excel
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="exportToPDF('pendingTable', 'Pending Verification Report')">
                            <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                        </button>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body p-0">
                    <div id="pendingTable" class="table-responsive">
                        <div class="empty-state py-5">
                            <i class="bi bi-hourglass-split"></i>
                            <p>Click to load pending payments</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overdue Tenants -->
        <div class="tab-pane fade" id="overdue-panel" role="tabpanel">
            <div class="card mb-3">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="exportToExcel('overdueTable', 'overdue_tenants')">
                            <i class="bi bi-file-earmark-excel me-1"></i> Excel
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="exportToPDF('overdueTable', 'Overdue Tenants Report')">
                            <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                        </button>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body p-0">
                    <div id="overdueTable" class="table-responsive">
                        <div class="empty-state py-5">
                            <i class="bi bi-exclamation-triangle"></i>
                            <p>Click to load overdue tenants</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Utility Summary -->
        <div class="tab-pane fade" id="utility-panel" role="tabpanel">
            <div class="card mb-3">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="exportToExcel('utilityTable', 'utility_summary')">
                            <i class="bi bi-file-earmark-excel me-1"></i> Excel
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="exportToPDF('utilityTable', 'Utility Summary Report')">
                            <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                        </button>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body p-0">
                    <div id="utilityTable" class="table-responsive">
                        <div class="empty-state py-5">
                            <i class="bi bi-lightning-charge"></i>
                            <p>Click to load utility summary</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expiring Contracts -->
        <div class="tab-pane fade" id="expiring-panel" role="tabpanel">
            <div class="card mb-3">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="exportToExcel('expiringTable', 'expiring_contracts')">
                            <i class="bi bi-file-earmark-excel me-1"></i> Excel
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="exportToPDF('expiringTable', 'Expiring Contracts Report')">
                            <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                        </button>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body p-0">
                    <div id="expiringTable" class="table-responsive">
                        <div class="empty-state py-5">
                            <i class="bi bi-calendar-x"></i>
                            <p>Click to load expiring contracts</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadBalanceReport();
});

function loadBalanceReport() {
    var month = $('#balanceMonth').val();
    var status = $('#balanceStatus').val();

    $('#balanceTable').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');

    ajaxPost('report.tenant.balance', { billing_month: month, status: status }, function(response) {
        var data = response.data;
        var summary = response.summary;

        // Render summary if available
        if (summary) {
            var summaryHtml = '<div class="col-md-3"><div class="card stat-card primary"><div class="card-body py-2">';
            summaryHtml += '<div class="stat-value">' + formatCurrency(summary.total_due) + '</div>';
            summaryHtml += '<div class="stat-label small">Total Due</div></div></div></div>';

            summaryHtml += '<div class="col-md-3"><div class="card stat-card success"><div class="card-body py-2">';
            summaryHtml += '<div class="stat-value">' + formatCurrency(summary.total_verified) + '</div>';
            summaryHtml += '<div class="stat-label small">Verified</div></div></div></div>';

            summaryHtml += '<div class="col-md-3"><div class="card stat-card warning"><div class="card-body py-2">';
            summaryHtml += '<div class="stat-value">' + formatCurrency(summary.total_pending) + '</div>';
            summaryHtml += '<div class="stat-label small">Pending</div></div></div></div>';

            summaryHtml += '<div class="col-md-3"><div class="card stat-card danger"><div class="card-body py-2">';
            summaryHtml += '<div class="stat-value">' + formatCurrency(summary.total_balance) + '</div>';
            summaryHtml += '<div class="stat-label small">Outstanding</div></div></div></div>';

            $('#balanceSummary').html(summaryHtml);
        } else {
            $('#balanceSummary').html('');
        }

        // Render table
        if (data.length === 0) {
            $('#balanceTable').html('<div class="empty-state py-5"><i class="bi bi-inbox"></i><p>No records found</p></div>');
            return;
        }

        var html = '<table class="table table-hover mb-0"><thead><tr>';
        html += '<th>Tenant</th><th>Property/Room</th><th>Total Due</th><th>Paid</th><th>Pending</th><th>Balance</th><th>Status</th>';
        html += '</tr></thead><tbody>';

        data.forEach(function(row) {
            html += '<tr>';
            html += '<td><strong>' + row.tenant_name + '</strong><br><small class="text-muted">' + (row.phone_no || '') + '</small></td>';
            html += '<td>' + row.property_name + '<br><small class="text-muted">' + row.room_name + '</small></td>';
            html += '<td>' + formatCurrency(row.total_due) + '</td>';
            html += '<td class="text-success">' + formatCurrency(row.verified_paid) + '</td>';
            html += '<td class="text-warning">' + formatCurrency(row.pending_payment) + '</td>';
            html += '<td class="' + (parseFloat(row.total_balance) > 0 ? 'text-danger fw-bold' : 'text-success') + '">' + formatCurrency(row.total_balance) + '</td>';
            html += '<td>' + getStatusBadge(row.display_status) + '</td>';
            html += '</tr>';
        });

        html += '</tbody></table>';
        $('#balanceTable').html(html);
    });
}

function loadPendingReport() {
    $('#pendingTable').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');

    ajaxPost('report.pending.payment', {}, function(response) {
        var data = response.data;

        if (data.length === 0) {
            $('#pendingTable').html('<div class="empty-state py-5"><i class="bi bi-check-circle text-success"></i><h5>No pending verifications</h5></div>');
            return;
        }

        var html = '<table class="table table-hover mb-0"><thead><tr>';
        html += '<th>Tenant</th><th>Room</th><th>Amount</th><th>Method</th><th>Reference</th><th>Date</th><th>Proof</th>';
        html += '</tr></thead><tbody>';

        data.forEach(function(row) {
            html += '<tr class="table-warning">';
            html += '<td><strong>' + row.tenant_name + '</strong><br><small>' + (row.phone_no || '') + '</small></td>';
            html += '<td>' + row.room_name + '</td>';
            html += '<td class="fw-bold">' + formatCurrency(row.payment_amount) + '</td>';
            html += '<td><span class="badge bg-info">' + row.payment_method.replace(/_/g, ' ') + '</span></td>';
            html += '<td>' + (row.reference_no || '-') + '</td>';
            html += '<td>' + formatDate(row.payment_date) + '</td>';
            html += '<td>' + (row.proof_url ? '<a href="' + row.proof_url + '" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-image"></i></a>' : '-') + '</td>';
            html += '</tr>';
        });

        html += '</tbody></table>';
        $('#pendingTable').html(html);
    });
}

function loadOverdueReport() {
    $('#overdueTable').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');

    ajaxPost('report.overdue', {}, function(response) {
        var data = response.data;

        if (data.length === 0) {
            $('#overdueTable').html('<div class="empty-state py-5"><i class="bi bi-check-circle text-success"></i><h5>No overdue tenants</h5></div>');
            return;
        }

        var html = '<table class="table table-hover mb-0"><thead><tr>';
        html += '<th>Tenant</th><th>Property/Room</th><th>Due Date</th><th>Total Due</th><th>Paid</th><th>Balance</th>';
        html += '</tr></thead><tbody>';

        data.forEach(function(row) {
            html += '<tr class="table-danger">';
            html += '<td><strong>' + row.tenant_name + '</strong><br><small>' + (row.phone_no || '') + '</small></td>';
            html += '<td>' + row.property_name + '<br><small class="text-muted">' + row.room_name + '</small></td>';
            html += '<td class="text-danger">' + formatDate(row.due_date) + '</td>';
            html += '<td>' + formatCurrency(row.total_due) + '</td>';
            html += '<td class="text-success">' + formatCurrency(row.verified_paid) + '</td>';
            html += '<td class="text-danger fw-bold">' + formatCurrency(row.total_balance) + '</td>';
            html += '</tr>';
        });

        html += '</tbody></table>';
        $('#overdueTable').html(html);
    });
}

function loadUtilityReport() {
    $('#utilityTable').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');

    ajaxPost('report.utility', {}, function(response) {
        var data = response.data;

        if (data.length === 0) {
            $('#utilityTable').html('<div class="empty-state py-5"><i class="bi bi-inbox"></i><h5>No utility records</h5></div>');
            return;
        }

        var html = '<table class="table table-hover mb-0"><thead><tr>';
        html += '<th>Month</th><th>Property/Room</th><th>Type</th><th>Consumption</th><th>Amount</th><th>Tenant</th><th>Allocation</th>';
        html += '</tr></thead><tbody>';

        data.forEach(function(row) {
            html += '<tr>';
            html += '<td>' + formatDate(row.billing_month) + '</td>';
            html += '<td>' + row.property_name + '<br><small>' + row.room_name + '</small></td>';
            html += '<td><span class="badge bg-' + (row.utility_type === 'electricity' ? 'warning' : 'info') + '">' + row.utility_type + '</span></td>';
            html += '<td>' + parseFloat(row.consumption).toFixed(2) + '</td>';
            html += '<td>' + formatCurrency(row.total_amount) + '</td>';
            html += '<td>' + row.tenant_name + '</td>';
            html += '<td class="fw-bold">' + formatCurrency(row.final_amount) + '</td>';
            html += '</tr>';
        });

        html += '</tbody></table>';
        $('#utilityTable').html(html);
    });
}

function loadExpiringReport() {
    $('#expiringTable').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');

    ajaxPost('report.expiring.contract', {}, function(response) {
        var data = response.data;

        if (data.length === 0) {
            $('#expiringTable').html('<div class="empty-state py-5"><i class="bi bi-check-circle text-success"></i><h5>No expiring contracts</h5></div>');
            return;
        }

        var html = '<table class="table table-hover mb-0"><thead><tr>';
        html += '<th>Tenant</th><th>Property/Room</th><th>Lease Type</th><th>Start Date</th><th>End Date</th><th>Status</th>';
        html += '</tr></thead><tbody>';

        data.forEach(function(row) {
            var rowClass = row.contract_alert_status === 'expired' ? 'table-danger' : (row.contract_alert_status === 'expiring_soon' ? 'table-warning' : '');
            html += '<tr class="' + rowClass + '">';
            html += '<td><strong>' + row.tenant_name + '</strong><br><small>' + (row.phone_no || '') + '</small></td>';
            html += '<td>' + row.property_name + '<br><small class="text-muted">' + row.room_name + '</small></td>';
            html += '<td><span class="badge bg-secondary">' + row.lease_type + '</span></td>';
            html += '<td>' + formatDate(row.start_date) + '</td>';
            html += '<td>' + formatDate(row.end_date) + '</td>';
            html += '<td>' + getStatusBadge(row.contract_alert_status) + '</td>';
            html += '</tr>';
        });

        html += '</tbody></table>';
        $('#expiringTable').html(html);
    });
}

// Export to Excel (CSV format)
function exportToExcel(containerId, filename) {
    var table = document.querySelector('#' + containerId + ' table');
    if (!table) {
        showError('No data to export');
        return;
    }

    var csv = [];
    var rows = table.querySelectorAll('tr');

    rows.forEach(function(row) {
        var cols = row.querySelectorAll('th, td');
        var rowData = [];
        cols.forEach(function(col) {
            // Get text content and clean it
            var text = col.innerText.replace(/[\n\r]+/g, ' ').replace(/,/g, ';').trim();
            rowData.push('"' + text + '"');
        });
        csv.push(rowData.join(','));
    });

    // Create and download CSV file
    var csvContent = '\uFEFF' + csv.join('\n'); // BOM for Excel UTF-8
    var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    var link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename + '_' + new Date().toISOString().slice(0,10) + '.csv';
    link.click();

    showSuccess('Excel file downloaded successfully');
}

// Export to PDF
function exportToPDF(containerId, title) {
    var table = document.querySelector('#' + containerId + ' table');
    if (!table) {
        showError('No data to export');
        return;
    }

    // Create print window
    var printWindow = window.open('', '_blank');
    printWindow.document.write('<html><head><title>' + title + '</title>');
    printWindow.document.write('<style>');
    printWindow.document.write('body { font-family: Arial, sans-serif; padding: 20px; }');
    printWindow.document.write('h1 { font-size: 18px; margin-bottom: 5px; }');
    printWindow.document.write('p { font-size: 12px; color: #666; margin-bottom: 20px; }');
    printWindow.document.write('table { width: 100%; border-collapse: collapse; font-size: 11px; }');
    printWindow.document.write('th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }');
    printWindow.document.write('th { background-color: #4F46E5; color: white; }');
    printWindow.document.write('tr:nth-child(even) { background-color: #f9f9f9; }');
    printWindow.document.write('@media print { body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }');
    printWindow.document.write('</style></head><body>');
    printWindow.document.write('<h1>' + title + '</h1>');
    printWindow.document.write('<p>Generated on: ' + new Date().toLocaleString() + '</p>');
    printWindow.document.write(table.outerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();

    // Wait for content to load then print
    printWindow.onload = function() {
        printWindow.print();
    };

    showSuccess('PDF export ready - use Save as PDF in print dialog');
}
</script>
