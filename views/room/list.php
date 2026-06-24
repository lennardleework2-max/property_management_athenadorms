<div class="content-wrapper">
    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h1 class="page-title">Rooms & Bedspaces</h1>
            <p class="page-subtitle">Manage rooms and bedspace occupancy</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#roomModal" onclick="openAddRoomModal()">
                <i class="bi bi-plus-lg me-1"></i> Add Room
            </button>
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#bedspaceModal" onclick="openAddBedspaceModal()">
                <i class="bi bi-plus-lg me-1"></i> Add Bedspace
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="index.php" class="row g-3">
                <input type="hidden" name="action" value="room.list">
                <div class="col-md-5">
                    <div class="search-box">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" class="form-control" name="search" placeholder="Search rooms..."
                               value="<?php echo e($search); ?>">
                    </div>
                </div>
                <div class="col-md-5">
                    <select class="form-select" name="property">
                        <option value="">All Properties</option>
                        <?php foreach ($properties as $property): ?>
                        <option value="<?php echo $property['recid']; ?>" <?php echo $selectedProperty == $property['recid'] ? 'selected' : ''; ?>>
                            <?php echo e($property['property_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Room List -->
    <div class="row g-4">
        <?php if (empty($rooms)): ?>
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="empty-state py-5">
                        <i class="bi bi-door-open"></i>
                        <h5>No rooms found</h5>
                        <p>Add your first room to get started.</p>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <?php foreach ($rooms as $room): ?>
        <div class="col-md-6 col-xl-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-secondary me-2"><?php echo e($room['room_id']); ?></span>
                        <strong><?php echo e($room['room_name']); ?></strong>
                    </div>
                    <span class="badge badge-<?php echo e($room['room_status']); ?>">
                        <?php echo ucfirst(e($room['room_status'])); ?>
                    </span>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        <i class="bi bi-building me-1"></i><?php echo e($room['property_name']); ?>
                    </p>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <small class="text-muted d-block">Monthly Rate</small>
                            <strong class="text-primary"><?php echo formatCurrency($room['monthly_room_rate']); ?></strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Occupancy</small>
                            <strong><?php echo (int)$room['occupied_count']; ?>/<?php echo (int)$room['bedspace_count']; ?> beds</strong>
                        </div>
                    </div>

                    <!-- Bedspaces -->
                    <div class="mb-3">
                        <small class="text-muted d-block mb-2">Bedspaces:</small>
                        <div class="d-flex flex-wrap gap-2">
                            <?php if (empty($room['bedspaces'])): ?>
                            <span class="text-muted small">No bedspaces added</span>
                            <?php else: ?>
                            <?php foreach ($room['bedspaces'] as $bed): ?>
                            <span class="badge badge-<?php echo e($bed['bedspace_status']); ?> cursor-pointer"
                                  onclick="openEditBedspaceModal(<?php echo $bed['recid']; ?>)"
                                  data-bs-toggle="tooltip" title="Click to edit">
                                <?php echo e($bed['bedspace_name']); ?>
                            </span>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary flex-grow-1"
                                onclick="openAddBedspaceModalForRoom(<?php echo $room['recid']; ?>)">
                            <i class="bi bi-plus me-1"></i> Add Bed
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-icon"
                                onclick="openEditRoomModal(<?php echo $room['recid']; ?>)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-icon"
                                onclick="deleteRoom(<?php echo $room['recid']; ?>, '<?php echo e($room['room_name']); ?>')">
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

<!-- Room Modal -->
<div class="modal fade" id="roomModal" tabindex="-1" aria-labelledby="roomModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roomModalLabel">Add Room</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="roomForm">
                <div class="modal-body">
                    <input type="hidden" name="recid" id="roomRecid">

                    <div class="mb-3">
                        <label class="form-label">Property <span class="text-danger">*</span></label>
                        <select class="form-select" name="property_recid" id="roomProperty" required>
                            <option value="">Select Property</option>
                            <?php foreach ($properties as $property): ?>
                            <option value="<?php echo $property['recid']; ?>"><?php echo e($property['property_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Room Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="room_name" id="roomName" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Room Type</label>
                            <select class="form-select" name="room_type" id="roomType">
                                <option value="bedspace">Bedspace</option>
                                <option value="private">Private Room</option>
                                <option value="studio">Studio</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Max Bedspaces</label>
                            <input type="number" class="form-control" name="max_bedspace" id="roomMaxBedspace" value="4" min="1">
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label">Monthly Rate</label>
                            <input type="number" class="form-control" name="monthly_room_rate" id="roomRate" step="0.01" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="room_status" id="roomStatus">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" name="remarks" id="roomRemarks" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="roomSubmitBtn">Save Room</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bedspace Modal -->
<div class="modal fade" id="bedspaceModal" tabindex="-1" aria-labelledby="bedspaceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bedspaceModalLabel">Add Bedspace</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bedspaceForm">
                <div class="modal-body">
                    <input type="hidden" name="recid" id="bedspaceRecid">

                    <div class="mb-3">
                        <label class="form-label">Room <span class="text-danger">*</span></label>
                        <select class="form-select" name="room_recid" id="bedspaceRoom" required>
                            <option value="">Select Room</option>
                            <?php foreach ($rooms as $room): ?>
                            <option value="<?php echo $room['recid']; ?>"><?php echo e($room['property_name']); ?> - <?php echo e($room['room_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bedspace Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="bedspace_name" id="bedspaceName" required placeholder="e.g., Bed A, Bed 1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="bedspace_status" id="bedspaceStatus">
                            <option value="available">Available</option>
                            <option value="occupied">Occupied</option>
                            <option value="reserved">Reserved</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" name="remarks" id="bedspaceRemarks" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="bedspaceSubmitBtn">Save Bedspace</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var isRoomEditMode = false;
var isBedspaceEditMode = false;

// Room functions
function openAddRoomModal() {
    isRoomEditMode = false;
    $('#roomModalLabel').text('Add Room');
    $('#roomSubmitBtn').text('Save Room');
    resetForm('#roomForm');
    $('#roomStatus').val('active');
    $('#roomType').val('bedspace');
}

function openEditRoomModal(recid) {
    isRoomEditMode = true;
    $('#roomModalLabel').text('Edit Room');
    $('#roomSubmitBtn').text('Update Room');
    resetForm('#roomForm');

    ajaxPost('room.get', { recid: recid }, function(response) {
        var room = response.data;
        $('#roomRecid').val(room.recid);
        $('#roomProperty').val(room.property_recid);
        $('#roomName').val(room.room_name);
        $('#roomType').val(room.room_type);
        $('#roomMaxBedspace').val(room.max_bedspace);
        $('#roomRate').val(room.monthly_room_rate);
        $('#roomStatus').val(room.room_status);
        $('#roomRemarks').val(room.remarks);

        var modalEl = document.getElementById('roomModal');
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    });
}

function deleteRoom(recid, name) {
    if (confirm('Are you sure you want to delete room "' + name + '"?')) {
        ajaxPost('room.delete', { recid: recid }, function(response) {
            showSuccess(response.message);
            location.reload();
        });
    }
}

$('#roomForm').on('submit', function(e) {
    e.preventDefault();
    var action = isRoomEditMode ? 'room.edit' : 'room.add';

    ajaxPost(action, $(this).serialize(), function(response) {
        showSuccess(response.message);
        bootstrap.Modal.getInstance(document.getElementById('roomModal')).hide();
        location.reload();
    });
});

// Bedspace functions
function openAddBedspaceModal() {
    isBedspaceEditMode = false;
    $('#bedspaceModalLabel').text('Add Bedspace');
    $('#bedspaceSubmitBtn').text('Save Bedspace');
    resetForm('#bedspaceForm');
    $('#bedspaceStatus').val('available');
}

function openAddBedspaceModalForRoom(roomRecid) {
    openAddBedspaceModal();
    $('#bedspaceRoom').val(roomRecid);
    var modalEl = document.getElementById('bedspaceModal');
    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
}

function openEditBedspaceModal(recid) {
    isBedspaceEditMode = true;
    $('#bedspaceModalLabel').text('Edit Bedspace');
    $('#bedspaceSubmitBtn').text('Update Bedspace');
    resetForm('#bedspaceForm');

    ajaxPost('bedspace.get', { recid: recid }, function(response) {
        var bedspace = response.data;
        $('#bedspaceRecid').val(bedspace.recid);
        $('#bedspaceRoom').val(bedspace.room_recid);
        $('#bedspaceName').val(bedspace.bedspace_name);
        $('#bedspaceStatus').val(bedspace.bedspace_status);
        $('#bedspaceRemarks').val(bedspace.remarks);

        var modalEl = document.getElementById('bedspaceModal');
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    });
}

$('#bedspaceForm').on('submit', function(e) {
    e.preventDefault();
    var action = isBedspaceEditMode ? 'bedspace.edit' : 'bedspace.add';

    ajaxPost(action, $(this).serialize(), function(response) {
        showSuccess(response.message);
        bootstrap.Modal.getInstance(document.getElementById('bedspaceModal')).hide();
        location.reload();
    });
});
</script>
