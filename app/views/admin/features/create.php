<?php include __DIR__.'/../../partials/header.php'; ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>⚙️ Add New Feature</h4>
        <a href="/admin/features" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Features
        </a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form action="/admin/features/store" method="POST">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="name" class="form-label">Feature Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required 
                                   placeholder="e.g., youtube_videos" 
                                   pattern="[a-z0-9_]+" 
                                   title="Lowercase letters, numbers, and underscores only">
                            <div class="form-text">Unique identifier for this feature (lowercase, underscores)</div>
                        </div>

                        <div class="mb-3">
                            <label for="display_name" class="form-label">Display Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="display_name" name="display_name" required 
                                   placeholder="e.g., YouTube Video Messages">
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" 
                                      placeholder="Brief description of what this feature does"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                    <select class="form-select" id="category" name="category" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= htmlspecialchars($category->name) ?>">
                                                <?= htmlspecialchars($category->display_name) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="requires_permission" class="form-label">Required Permission</label>
                                    <select class="form-select" id="requires_permission" name="requires_permission">
                                        <option value="">None</option>
                                        <option value="admin">Admin</option>
                                        <option value="super_admin">Super Admin</option>
                                    </select>
                                    <div class="form-text">Who can manage this feature</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="config_data" class="form-label">Configuration (JSON)</label>
                            <textarea class="form-control" id="config_data" name="config_data" rows="3" 
                                      placeholder='{"key": "value"}'></textarea>
                            <div class="form-text">Optional JSON configuration for this feature</div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_enabled" name="is_enabled">
                                <label class="form-check-label" for="is_enabled">
                                    Enable feature immediately
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Feature Guidelines</h6>
                            </div>
                            <div class="card-body">
                                <h6>Naming Conventions</h6>
                                <ul class="small mb-3">
                                    <li>Use lowercase letters</li>
                                    <li>Use underscores for spaces</li>
                                    <li>Be descriptive but concise</li>
                                    <li>Follow existing patterns</li>
                                </ul>

                                <h6>Categories</h6>
                                <ul class="small mb-3">
                                    <li><strong>Core:</strong> Essential features</li>
                                    <li><strong>Content:</strong> Content management</li>
                                    <li><strong>Social:</strong> User interactions</li>
                                    <li><strong>Monetization:</strong> Payment features</li>
                                    <li><strong>Advanced:</strong> Optional features</li>
                                </ul>

                                <h6>Permissions</h6>
                                <ul class="small mb-0">
                                    <li><strong>None:</strong> All admins</li>
                                    <li><strong>Admin:</strong> Admin users only</li>
                                    <li><strong>Super Admin:</strong> Super admin only</li>
                                </ul>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="mb-0">Examples</h6>
                            </div>
                            <div class="card-body">
                                <p class="small mb-2"><strong>Name:</strong> youtube_videos</p>
                                <p class="small mb-2"><strong>Display:</strong> YouTube Video Messages</p>
                                <p class="small mb-2"><strong>Category:</strong> Content</p>
                                <p class="small mb-0"><strong>Description:</strong> Display YouTube video messages</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Feature
                        </button>
                        <a href="/admin/features" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-generate name from display name
document.getElementById('display_name').addEventListener('input', function() {
    const displayName = this.value;
    const name = displayName.toLowerCase()
        .replace(/[^a-z0-9]+/g, '_')
        .replace(/^-+|-+$/g, '');
    document.getElementById('name').value = name;
});

// Validate JSON configuration
document.getElementById('config_data').addEventListener('blur', function() {
    const value = this.value.trim();
    if (value) {
        try {
            JSON.parse(value);
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } catch (e) {
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
        }
    } else {
        this.classList.remove('is-valid', 'is-invalid');
    }
});
</script>

<?php include __DIR__.'/../../partials/footer.php'; ?>