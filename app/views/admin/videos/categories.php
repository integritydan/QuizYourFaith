<?php include __DIR__.'/../../partials/header.php'; ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>🏷️ Video Categories</h4>
        <a href="/admin/videos" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Videos
        </a>
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
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Add New Category</h6>
                </div>
                <div class="card-body">
                    <form action="/admin/videos/categories/store" method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required 
                                   placeholder="e.g., Bible Teachings">
                        </div>

                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="slug" name="slug" required 
                                   placeholder="e.g., bible-teachings">
                            <div class="form-text">URL-friendly version of the name</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" 
                                      placeholder="Brief description of this category"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="color" class="form-label">Color</label>
                                    <input type="color" class="form-control form-control-color" id="color" name="color" 
                                           value="#007bff">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="icon" class="form-label">Icon</label>
                                    <input type="text" class="form-control" id="icon" name="icon" 
                                           value="video" placeholder="e.g., video, book, heart">
                                    <div class="form-text">Font Awesome icon name</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add Category
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Existing Categories</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($categories)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-tags fa-2x text-muted mb-3"></i>
                            <p class="text-muted">No categories found</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Slug</th>
                                        <th>Color</th>
                                        <th>Icon</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($category->name) ?></strong>
                                                <?php if ($category->description): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($category->description) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><code><?= htmlspecialchars($category->slug) ?></code></td>
                                            <td>
                                                <span class="badge" style="background-color: <?= htmlspecialchars($category->color) ?>">
                                                    <?= htmlspecialchars($category->color) ?>
                                                </span>
                                            </td>
                                            <td><i class="fas fa-<?= htmlspecialchars($category->icon) ?>"></i></td>
                                            <td>
                                                <span class="badge bg-<?= $category->is_active ? 'success' : 'danger' ?>">
                                                    <?= $category->is_active ? 'Active' : 'Inactive' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-<?= $category->is_active ? 'warning' : 'success' ?>" 
                                                            title="<?= $category->is_active ? 'Deactivate' : 'Activate' ?>">
                                                        <i class="fas fa-<?= $category->is_active ? 'pause' : 'play' ?>"></i>
                                                    </button>
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
        </div>
    </div>
</div>

<script>
// Auto-generate slug from name
document.getElementById('name').addEventListener('input', function() {
    const name = this.value;
    const slug = name.toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
    document.getElementById('slug').value = slug;
});
</script>

<?php include __DIR__.'/../../partials/footer.php'; ?>