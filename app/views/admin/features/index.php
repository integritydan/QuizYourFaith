<?php include __DIR__.'/../../partials/header.php'; ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>⚙️ Feature Management</h4>
        <div>
            <a href="/admin/features/audit" class="btn btn-outline-info btn-sm me-2">
                <i class="fas fa-history"></i> Audit Log
            </a>
            <a href="/admin/features/create" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Feature
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Categories</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="/admin/features" class="list-group-item list-group-item-action <?= !isset($_GET['category']) ? 'active' : '' ?>">
                        All Categories
                    </a>
                    <?php foreach ($categories as $category): ?>
                        <a href="/admin/features/category/<?= htmlspecialchars($category->name) ?>" 
                           class="list-group-item list-group-item-action <?= (isset($_GET['category']) && $_GET['category'] == $category->name) ? 'active' : '' ?>">
                            <i class="fas fa-<?= htmlspecialchars($category->icon) ?>"></i> 
                            <?= htmlspecialchars($category->display_name) ?>
                            <?php 
                            $categoryFeatures = array_filter($features, function($f) use ($category) {
                                return $f->category == $category->name;
                            });
                            $enabledCount = count(array_filter($categoryFeatures, function($f) {
                                return $f->is_enabled;
                            }));
                            ?>
                            <span class="badge bg-secondary float-end"><?= $enabledCount ?>/<?= count($categoryFeatures) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <button class="btn btn-sm btn-success w-100 mb-2" onclick="enableAllFeatures()">
                        <i class="fas fa-toggle-on"></i> Enable All
                    </button>
                    <button class="btn btn-sm btn-danger w-100 mb-2" onclick="disableAllFeatures()">
                        <i class="fas fa-toggle-off"></i> Disable All
                    </button>
                    <button class="btn btn-sm btn-warning w-100" onclick="resetToDefaults()">
                        <i class="fas fa-undo"></i> Reset Defaults
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Features</h6>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary" onclick="filterFeatures('all')">All</button>
                        <button type="button" class="btn btn-outline-success" onclick="filterFeatures('enabled')">Enabled</button>
                        <button type="button" class="btn btn-outline-danger" onclick="filterFeatures('disabled')">Disabled</button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($features)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-cog fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No features found</h5>
                            <p class="text-muted">Features will appear here once added.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Feature</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Permission</th>
                                        <th>Dependencies</th>
                                        <th width="120">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($features as $feature): ?>
                                        <tr class="feature-row" data-enabled="<?= $feature->is_enabled ? '1' : '0' ?>" data-category="<?= htmlspecialchars($feature->category) ?>">
                                            <td>
                                                <strong><?= htmlspecialchars($feature->display_name) ?></strong>
                                                <br><small class="text-muted">
                                                    <code><?= htmlspecialchars($feature->name) ?></code>
                                                </small>
                                                <?php if ($feature->description): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars(substr($feature->description, 0, 100)) ?>...</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge" style="background-color: <?= htmlspecialchars($feature->category_color) ?>">
                                                    <i class="fas fa-<?= htmlspecialchars($feature->category_icon) ?>"></i> 
                                                    <?= htmlspecialchars($feature->category_display_name) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $feature->is_enabled ? 'success' : 'danger' ?>">
                                                    <?= $feature->is_enabled ? 'Enabled' : 'Disabled' ?>
                                                </span>
                                                <?php if ($feature->is_enabled && $feature->enabled_at): ?>
                                                    <br><small class="text-muted">Since <?= date('M j, Y', strtotime($feature->enabled_at)) ?></small>
                                                <?php elseif (!$feature->is_enabled && $feature->disabled_at): ?>
                                                    <br><small class="text-muted">Since <?= date('M j, Y', strtotime($feature->disabled_at)) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($feature->requires_permission): ?>
                                                    <span class="badge bg-warning text-dark">
                                                        <?= htmlspecialchars($feature->requires_permission) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">None</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $dependencies = $feature->dependencies ? json_decode($feature->dependencies, true) : [];
                                                if (!empty($dependencies) && is_array($dependencies)):
                                                ?>
                                                    <small>
                                                        <?php foreach (array_slice($dependencies, 0, 2) as $dep): ?>
                                                            <span class="badge bg-info"><?= htmlspecialchars($dep) ?></span>
                                                        <?php endforeach; ?>
                                                        <?php if (count($dependencies) > 2): ?>
                                                            <span class="badge bg-secondary">+<?= count($dependencies) - 2 ?> more</span>
                                                        <?php endif; ?>
                                                    </small>
                                                <?php else: ?>
                                                    <span class="text-muted">None</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" 
                                                            class="btn btn-outline-<?= $feature->is_enabled ? 'warning' : 'success' ?>" 
                                                            onclick="toggleFeature('<?= htmlspecialchars($feature->name) ?>', <?= $feature->is_enabled ? 0 : 1 ?>)"
                                                            title="<?= $feature->is_enabled ? 'Disable' : 'Enable' ?>">
                                                        <i class="fas fa-<?= $feature->is_enabled ? 'pause' : 'play' ?>"></i>
                                                    </button>
                                                    <a href="/admin/features/edit/<?= $feature->id ?>" 
                                                       class="btn btn-outline-primary" 
                                                       title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if ($feature->category !== 'core'): ?>
                                                        <button type="button" 
                                                                class="btn btn-outline-danger" 
                                                                onclick="confirmDelete('<?= htmlspecialchars($feature->name) ?>', '<?= htmlspecialchars(addslashes($feature->display_name)) ?>')"
                                                                title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($auditLog)): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">Recent Activity</h6>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <?php foreach ($auditLog as $log): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-<?= $log->action === 'enabled' ? 'success' : ($log->action === 'disabled' ? 'danger' : 'primary') ?>">
                                        <i class="fas fa-<?= $log->action === 'enabled' ? 'play' : ($log->action === 'disabled' ? 'pause' : 'edit') ?>"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1"><?= htmlspecialchars($log->feature_name) ?></h6>
                                        <p class="mb-1">
                                            <span class="badge bg-<?= $log->action === 'enabled' ? 'success' : ($log->action === 'disabled' ? 'danger' : 'primary') ?>">
                                                <?= ucfirst($log->action) ?>
                                            </span>
                                            by <?= htmlspecialchars($log->user_name ?? 'System') ?>
                                        </p>
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i> <?= date('M j, Y g:i A', strtotime($log->created_at)) ?>
                                            <?php if ($log->ip_address): ?>
                                                <i class="fas fa-map-marker-alt ms-2"></i> <?= htmlspecialchars($log->ip_address) ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function toggleFeature(featureName, enable) {
    if (confirm(`Are you sure you want to ${enable ? 'enable' : 'disable'} this feature?`)) {
        fetch('/admin/features/toggle/' + featureName, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error toggling feature');
        });
    }
}

function filterFeatures(status) {
    const rows = document.querySelectorAll('.feature-row');
    
    rows.forEach(row => {
        const isEnabled = row.getAttribute('data-enabled') === '1';
        
        if (status === 'all' || 
            (status === 'enabled' && isEnabled) || 
            (status === 'disabled' && !isEnabled)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function enableAllFeatures() {
    if (confirm('Are you sure you want to enable ALL features? This may affect system performance.')) {
        // This would require a bulk operation endpoint
        alert('Bulk enable operation - implement in controller');
    }
}

function disableAllFeatures() {
    if (confirm('Are you sure you want to disable ALL features? This will disable most functionality.')) {
        // This would require a bulk operation endpoint
        alert('Bulk disable operation - implement in controller');
    }
}

function resetToDefaults() {
    if (confirm('Reset all features to their default settings? This will override current settings.')) {
        // This would require a reset endpoint
        alert('Reset operation - implement in controller');
    }
}

function confirmDelete(featureName, displayName) {
    if (confirm(`Are you sure you want to delete the feature "${displayName}"? This action cannot be undone.`)) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/features/delete/' + featureName;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include __DIR__.'/../../partials/footer.php'; ?>