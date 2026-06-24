/**
 * Athena Dorms Property Management System
 * Main JavaScript File
 */

$(document).ready(function() {
    // Initialize components
    initSidebar();
    initTooltips();
});

/**
 * Get CSRF Token
 */
function getCsrfToken() {
    return $('#csrf_token').val();
}

/**
 * Initialize Sidebar Toggle
 */
function initSidebar() {
    // Toggle sidebar on mobile
    $('#sidebarToggle').on('click', function() {
        $('#sidebar').addClass('show');
        $('#sidebarOverlay').addClass('show');
    });

    // Close sidebar
    $('#sidebarClose, #sidebarOverlay').on('click', function() {
        $('#sidebar').removeClass('show');
        $('#sidebarOverlay').removeClass('show');
    });
}

/**
 * Initialize Bootstrap Tooltips
 */
function initTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Loading overlay timer
 */
var loadingTimer = null;

/**
 * Show loading overlay (disabled - do nothing while loading)
 */
function showLoading() {
    // Disabled - no loading overlay
}

/**
 * Hide loading overlay
 */
function hideLoading() {
    // Clear the timer if request completed before overlay was shown
    if (loadingTimer) {
        clearTimeout(loadingTimer);
        loadingTimer = null;
    }
    $('#loadingOverlay').fadeOut(200);
}

/**
 * Show toast notification
 */
function showToast(type, message) {
    var toastHtml = '<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">' +
        '<div class="toast align-items-center text-white bg-' + type + ' border-0" role="alert">' +
        '<div class="d-flex">' +
        '<div class="toast-body">' + message + '</div>' +
        '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
        '</div></div></div>';

    var $toast = $(toastHtml);
    $('body').append($toast);

    var toastEl = $toast.find('.toast')[0];
    var toast = new bootstrap.Toast(toastEl, { delay: 4000 });
    toast.show();

    $(toastEl).on('hidden.bs.toast', function() {
        $toast.remove();
    });
}

/**
 * Show success toast
 */
function showSuccess(message) {
    showToast('success', '<i class="bi bi-check-circle me-2"></i>' + message);
}

/**
 * Show error toast
 */
function showError(message) {
    showToast('danger', '<i class="bi bi-exclamation-circle me-2"></i>' + message);
}

/**
 * Show warning toast
 */
function showWarning(message) {
    showToast('warning', '<i class="bi bi-exclamation-triangle me-2"></i>' + message);
}

/**
 * Generic AJAX POST request
 */
function ajaxPost(action, data, successCallback, errorCallback) {
    data.csrf_token = getCsrfToken();

    $.ajax({
        url: 'index.php?action=' + action,
        type: 'POST',
        data: data,
        dataType: 'json',
        beforeSend: function() {
            showLoading();
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                if (typeof successCallback === 'function') {
                    successCallback(response);
                }
            } else {
                showError(response.message || 'An error occurred');
                if (typeof errorCallback === 'function') {
                    errorCallback(response);
                }
            }
        },
        error: function(xhr, status, error) {
            hideLoading();
            showError('Request failed. Please try again.');
            console.error('AJAX Error:', error);
            if (typeof errorCallback === 'function') {
                errorCallback({ success: false, message: error });
            }
        }
    });
}

/**
 * Generic AJAX GET request
 */
function ajaxGet(action, data, successCallback, errorCallback) {
    $.ajax({
        url: 'index.php?action=' + action,
        type: 'GET',
        data: data,
        dataType: 'json',
        beforeSend: function() {
            showLoading();
        },
        success: function(response) {
            hideLoading();
            if (typeof successCallback === 'function') {
                successCallback(response);
            }
        },
        error: function(xhr, status, error) {
            hideLoading();
            showError('Request failed. Please try again.');
            console.error('AJAX Error:', error);
            if (typeof errorCallback === 'function') {
                errorCallback({ success: false, message: error });
            }
        }
    });
}

/**
 * Confirm delete dialog
 */
function confirmDelete(message, callback) {
    if (confirm(message || 'Are you sure you want to delete this item?')) {
        callback();
    }
}

/**
 * Reset form
 */
function resetForm(formId) {
    var $form = $(formId);
    $form[0].reset();
    $form.find('.is-invalid').removeClass('is-invalid');
    $form.find('.invalid-feedback').remove();
}

/**
 * Format currency
 */
function formatCurrency(amount) {
    return '₱' + parseFloat(amount || 0).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

/**
 * Format date
 */
function formatDate(dateStr) {
    if (!dateStr) return '-';
    var date = new Date(dateStr);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

/**
 * Get status badge HTML
 */
function getStatusBadge(status) {
    var statusClass = 'badge-' + status.toLowerCase().replace(' ', '_');
    var statusText = status.replace(/_/g, ' ').replace(/\b\w/g, function(l) {
        return l.toUpperCase();
    });
    return '<span class="badge ' + statusClass + '">' + statusText + '</span>';
}

/**
 * Debounce function for search
 */
function debounce(func, wait) {
    var timeout;
    return function() {
        var context = this, args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            func.apply(context, args);
        }, wait);
    };
}

/**
 * Load table data via AJAX
 */
function loadTableData(action, containerId, renderCallback) {
    ajaxPost(action, {}, function(response) {
        if (response.data) {
            renderCallback(response.data, containerId);
        }
    });
}

/**
 * Validate required fields
 */
function validateRequired(formId) {
    var isValid = true;
    $(formId + ' [required]').each(function() {
        var $field = $(this);
        $field.removeClass('is-invalid');

        if (!$field.val() || $field.val().trim() === '') {
            $field.addClass('is-invalid');
            isValid = false;
        }
    });
    return isValid;
}

/**
 * Handle form submission with AJAX
 */
function submitForm(formId, action, successCallback) {
    var $form = $(formId);

    if (!validateRequired(formId)) {
        showError('Please fill in all required fields.');
        return false;
    }

    var formData = $form.serialize();

    ajaxPost(action, $form.serialize(), function(response) {
        if (response.success) {
            showSuccess(response.message || 'Operation completed successfully.');
            if (typeof successCallback === 'function') {
                successCallback(response);
            }
        }
    });

    return false;
}

// Add loading overlay styles
$('<style>')
    .prop('type', 'text/css')
    .html(`
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.8);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        .loading-overlay .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    `)
    .appendTo('head');
