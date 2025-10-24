<?php include __DIR__ . '/../../layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-none d-md-block bg-light sidebar">
            <div class="sidebar-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/super">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/super/users">
                            <i class="fas fa-users"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/super/settings">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/admin/update">
                            <i class="fas fa-upload"></i> System Update
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">System Update</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group mr-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="checkForUpdates()">
                            <i class="fas fa-sync-alt"></i> Check for Updates
                        </button>
                    </div>
                </div>
            </div>

            <!-- Alert Messages -->
            <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Current System Info -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Current System Information</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Current Version:</strong></td>
                                    <td><?= htmlspecialchars($current_version) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Last Update:</strong></td>
                                    <td><?= $last_update ? date('Y-m-d H:i', strtotime($last_update->applied_at)) : 'Never' ?></td>
                                </tr>
                                <tr>
                                    <td><strong>PHP Version:</strong></td>
                                    <td><?= phpversion() ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Database Version:</strong></td>
                                    <td><?= db()->query("SELECT VERSION()")->fetchColumn() ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Update Safety Features</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check-circle text-success"></i> Automatic backup before update</li>
                                <li><i class="fas fa-check-circle text-success"></i> User data preservation</li>
                                <li><i class="fas fa-check-circle text-success"></i> Progress and settings maintained</li>
                                <li><i class="fas fa-check-circle text-success"></i> Rollback capability</li>
                                <li><i class="fas fa-check-circle text-success"></i> Database migration support</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Section -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Upload New Version</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="/admin/update/upload" enctype="multipart/form-data" id="updateForm">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        
                        <div class="form-group">
                            <label for="update_file">Select ZIP File:</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="update_file" name="update_file" accept=".zip" required>
                                <label class="custom-file-label" for="update_file">Choose update file...</label>
                            </div>
                            <small class="form-text text-muted">
                                Only ZIP files are accepted. Maximum size: 50MB
                            </small>
                        </div>

                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle"></i> Important Update Instructions:</h6>
                            <ul>
                                <li>Ensure you have a current backup before proceeding</li>
                                <li>The update process will preserve all user data and progress</li>
                                <li>Settings and configurations will be maintained</li>
                                <li>Database migrations will run automatically if needed</li>
                                <li>The system will be temporarily unavailable during update</li>
                            </ul>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="confirm_backup" required>
                                <label class="custom-control-label" for="confirm_backup">
                                    I confirm I have created a backup of the current system
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg" id="uploadButton">
                            <i class="fas fa-upload"></i> Upload & Install Update
                        </button>
                    </form>
                </div>
            </div>

            <!-- Update History -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Update History</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($update_history)): ?>
                        <p class="text-muted">No updates have been applied yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Version</th>
                                        <th>Applied By</th>
                                        <th>Date</th>
                                        <th>Backup</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($update_history as $update): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($update->version) ?></td>
                                        <td><?= htmlspecialchars($update->applied_by_name) ?></td>
                                        <td><?= date('Y-m-d H:i', strtotime($update->applied_at)) ?></td>
                                        <td>
                                            <?php if ($update->backup_path && is_dir($update->backup_path)): ?>
                                                <button class="btn btn-sm btn-outline-primary" onclick="downloadBackup('<?= $update->id ?>')">
                                                    <i class="fas fa-download"></i> Download
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted">Not available</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-success">Success</span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Backup Management -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Backup Management</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Create New Backup</h6>
                            <p class="text-muted">Create a backup of your current system before updating</p>
                            <button type="button" class="btn btn-outline-primary" onclick="createBackup()">
                                <i class="fas fa-save"></i> Create Backup
                            </button>
                        </div>
                        <div class="col-md-6">
                            <h6>Restore from Backup</h6>
                            <p class="text-muted">Restore system to a previous version if needed</p>
                            <button type="button" class="btn btn-outline-warning" onclick="showRestoreModal()">
                                <i class="fas fa-undo"></i> Restore Backup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Restore Backup Modal -->
<div class="modal fade" id="restoreModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Restore from Backup</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <strong>Warning:</strong> This will restore your system to a previous state. All data and changes made after the backup date will be lost.
                </div>
                <p>Select a backup to restore from:</p>
                <div id="backupList">
                    <!-- Backup list will be populated here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="confirmRestore()">
                    <i class="fas fa-undo"></i> Restore Selected Backup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// File input label update
document.getElementById('update_file').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name || 'Choose update file...';
    const label = e.target.nextElementSibling;
    label.textContent = fileName;
});

// Form submission with progress
document.getElementById('updateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const confirmBackup = document.getElementById('confirm_backup').checked;
    if (!confirmBackup) {
        alert('Please confirm you have created a backup before proceeding.');
        return;
    }
    
    const fileInput = document.getElementById('update_file');
    if (!fileInput.files[0]) {
        alert('Please select an update file.');
        return;
    }
    
    // Show progress
    const button = document.getElementById('uploadButton');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
    button.disabled = true;
    
    // Submit form
    const formData = new FormData(this);
    
    fetch('/admin/update/upload', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Update completed successfully!');
            location.reload();
        } else {
            alert('❌ Update failed: ' + (data.error || 'Unknown error'));
            button.innerHTML = originalText;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Update error:', error);
        alert('❌ Update failed: ' + error.message);
        button.innerHTML = originalText;
        button.disabled = false;
    });
});

function checkForUpdates() {
    alert('Checking for updates... (This would connect to update server)');
}

function createBackup() {
    if (confirm('Create a backup of the current system?')) {
        alert('Backup creation started... (This would trigger backup process)');
    }
}

function showRestoreModal() {
    $('#restoreModal').modal('show');
    // Populate backup list
    loadBackupList();
}

function loadBackupList() {
    // This would fetch available backups from server
    const backupList = document.getElementById('backupList');
    backupList.innerHTML = '<p class="text-muted">Loading available backups...</p>';
    
    // Simulate loading backups
    setTimeout(() => {
        backupList.innerHTML = `
            <div class="list-group">
                <label class="list-group-item">
                    <input type="radio" name="backup" value="1" class="mr-2">
                    <strong>Version 1.5.0</strong> - 2024-01-15 14:30
                </label>
                <label class="list-group-item">
                    <input type="radio" name="backup" value="2" class="mr-2">
                    <strong>Version 1.4.2</strong> - 2024-01-10 09:15
                </label>
            </div>
        `;
    }, 1000);
}

function confirmRestore() {
    const selectedBackup = document.querySelector('input[name="backup"]:checked');
    if (!selectedBackup) {
        alert('Please select a backup to restore.');
        return;
    }
    
    if (confirm('Are you sure you want to restore from this backup? All current data will be replaced.')) {
        alert('Restoring from backup... (This would trigger restore process)');
        $('#restoreModal').modal('hide');
    }
}

function downloadBackup(updateId) {
    alert('Downloading backup... (This would trigger download)');
}
</script>

<style>
.update-section {
    border-left: 4px solid #007bff;
}

.custom-file-input:lang(en) ~ .custom-file-label::after {
    content: "Browse";
}

.btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1.1rem;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.table-hover tbody tr:hover {
    background-color: #f5f5f5;
}

.list-group-item {
    border: 1px solid #dee2e6;
    margin-bottom: 5px;
}

.list-group-item:hover {
    background-color: #f8f9fa;
}

.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.alert-warning {
    border-left: 4px solid #ffc107;
}
</style>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>