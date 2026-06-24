<div class="content-wrapper">
    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h1 class="page-title">Properties</h1>
            <p class="page-subtitle">Manage your properties and buildings</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#propertyModal" onclick="openAddModal()">
            <i class="bi bi-plus-lg me-1"></i> Add Property
        </button>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="index.php" class="row g-3">
                <input type="hidden" name="action" value="property.list">
                <div class="col-md-6">
                    <div class="search-box">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" class="form-control" name="search" placeholder="Search properties..."
                               value="<?php echo e($search); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
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

    <!-- Property List -->
    <div class="row g-4">
        <?php if (empty($properties)): ?>
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="empty-state py-5">
                        <i class="bi bi-building"></i>
                        <h5>No properties found</h5>
                        <p>Add your first property to get started.</p>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <?php foreach ($properties as $property): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 hover-shadow">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge bg-secondary mb-2"><?php echo e($property['property_id']); ?></span>
                            <h5 class="card-title mb-1"><?php echo e($property['property_name']); ?></h5>
                        </div>
                        <span class="badge badge-<?php echo e($property['property_status']); ?>">
                            <?php echo ucfirst(e($property['property_status'])); ?>
                        </span>
                    </div>

                    <?php if (!empty($property['property_address'])): ?>
                    <p class="text-muted small mb-3">
                        <i class="bi bi-geo-alt me-1"></i><?php echo e($property['property_address']); ?>
                    </p>
                    <?php endif; ?>

                    <div class="row g-2 mb-3">
                        <div class="col-4 text-center">
                            <div class="border rounded p-2">
                                <div class="fs-5 fw-bold text-primary"><?php echo (int)$property['room_count']; ?></div>
                                <small class="text-muted">Rooms</small>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="border rounded p-2">
                                <div class="fs-5 fw-bold text-success"><?php echo (int)$property['full_room_count']; ?>/<?php echo (int)$property['room_count']; ?></div>
                                <small class="text-muted">Full Rooms</small>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="border rounded p-2">
                                <div class="fs-5 fw-bold text-info"><?php echo (int)$property['occupied_bedspace_count']; ?>/<?php echo (int)$property['total_bedspace_count']; ?></div>
                                <small class="text-muted">Occupied Beds</small>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="index.php?action=room.list&property=<?php echo $property['recid']; ?>" class="btn btn-sm btn-outline-primary flex-grow-1">
                            <i class="bi bi-door-open me-1"></i> View Rooms
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-icon"
                                onclick="openEditModal(<?php echo $property['recid']; ?>)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-icon"
                                onclick="deleteProperty(<?php echo $property['recid']; ?>, '<?php echo e($property['property_name']); ?>')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Property Modal -->
<div class="modal fade" id="propertyModal" tabindex="-1" aria-labelledby="propertyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="propertyModalLabel">Add Property</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="propertyForm">
                <div class="modal-body">
                    <input type="hidden" name="recid" id="propertyRecid">

                    <div class="mb-3">
                        <label class="form-label">Property Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="property_name" id="propertyName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="property_address" id="propertyAddress" rows="2"></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Property Type</label>
                            <select class="form-select" name="property_type" id="propertyType">
                                <option value="dormitory">Dormitory</option>
                                <option value="apartment">Apartment</option>
                                <option value="boarding_house">Boarding House</option>
                                <option value="condo">Condominium</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="property_status" id="propertyStatus">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" name="remarks" id="propertyRemarks" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="propertySubmitBtn">Save Property</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var isEditMode = false;

function openAddModal() {
    isEditMode = false;
    $('#propertyModalLabel').text('Add Property');
    $('#propertySubmitBtn').text('Save Property');
    resetForm('#propertyForm');
    $('#propertyStatus').val('active');
    $('#propertyType').val('dormitory');
}

function openEditModal(recid) {
    isEditMode = true;
    $('#propertyModalLabel').text('Edit Property');
    $('#propertySubmitBtn').text('Update Property');
    resetForm('#propertyForm');

    ajaxPost('property.get', { recid: recid }, function(response) {
        var property = response.data;
        $('#propertyRecid').val(property.recid);
        $('#propertyName').val(property.property_name);
        $('#propertyAddress').val(property.property_address);
        $('#propertyType').val(property.property_type);
        $('#propertyStatus').val(property.property_status);
        $('#propertyRemarks').val(property.remarks);

        var modalEl = document.getElementById('propertyModal');
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    });
}

function deleteProperty(recid, name) {
    if (confirm('Are you sure you want to delete property "' + name + '"?')) {
        ajaxPost('property.delete', { recid: recid }, function(response) {
            showSuccess(response.message);
            location.reload();
        });
    }
}

$('#propertyForm').on('submit', function(e) {
    e.preventDefault();

    var action = isEditMode ? 'property.edit' : 'property.add';

    ajaxPost(action, $(this).serialize(), function(response) {
        showSuccess(response.message);
        bootstrap.Modal.getInstance(document.getElementById('propertyModal')).hide();
        location.reload();
    });
});
</script>
