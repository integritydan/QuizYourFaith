<?php include __DIR__.'/../../partials/header.php'; ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>📺 YouTube Video Management</h4>
        <div>
            <a href="/admin/videos/categories" class="btn btn-outline-primary btn-sm me-2">
                <i class="fas fa-tags"></i> Categories
            </a>
            <a href="/admin/videos/create" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add New Video
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

    <div class="card">
        <div class="card-body">
            <?php if (empty($videos)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-video fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No videos found</h5>
                    <p class="text-muted">Start by adding your first YouTube video message.</p>
                    <a href="/admin/videos/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add First Video
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="80">Thumbnail</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Duration</th>
                                <th>Views</th>
                                <th>Status</th>
                                <th>Order</th>
                                <th>Created</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($videos as $video): ?>
                                <tr>
                                    <td>
                                        <?php if ($video->thumbnail_url): ?>
                                            <img src="<?= htmlspecialchars($video->thumbnail_url) ?>" 
                                                 alt="Thumbnail" 
                                                 class="img-thumbnail" 
                                                 style="width: 60px; height: 45px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                                 style="width: 60px; height: 45px;">
                                                <i class="fas fa-video text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($video->title) ?></strong>
                                        <?php if ($video->description): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars(substr($video->description, 0, 100)) ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($video->category_name): ?>
                                            <span class="badge" style="background-color: <?= htmlspecialchars($video->category_color) ?>">
                                                <?= htmlspecialchars($video->category_name) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Uncategorized</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($video->duration ?: 'N/A') ?></td>
                                    <td><?= number_format($video->views_count) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $video->is_active ? 'success' : 'danger' ?>">
                                            <?= $video->is_active ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td><?= $video->display_order ?></td>
                                    <td>
                                        <small><?= date('M j, Y', strtotime($video->created_at)) ?></small>
                                        <br><small class="text-muted">by <?= htmlspecialchars($video->created_by_name) ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="/admin/videos/edit/<?= $video->id ?>" 
                                               class="btn btn-outline-primary" 
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-outline-<?= $video->is_active ? 'warning' : 'success' ?>" 
                                                    onclick="toggleStatus(<?= $video->id ?>, <?= $video->is_active ? 0 : 1 ?>)"
                                                    title="<?= $video->is_active ? 'Deactivate' : 'Activate' ?>">
                                                <i class="fas fa-<?= $video->is_active ? 'pause' : 'play' ?>"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-outline-danger" 
                                                    onclick="confirmDelete(<?= $video->id ?>, '<?= htmlspecialchars(addslashes($video->title)) ?>')"
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
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

<script>
function toggleStatus(id, newStatus) {
    if (confirm('Are you sure you want to ' + (newStatus ? 'activate' : 'deactivate') + ' this video?')) {
        fetch('/admin/videos/toggle-status/' + id, {
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
            alert('Error updating status');
        });
    }
}

function confirmDelete(id, title) {
    if (confirm('Are you sure you want to delete the video "' + title + '"? This action cannot be undone.')) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/videos/delete/' + id;
        
        // Add CSRF token if available
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'csrf_token';
            input.value = csrfToken.content;
            form.appendChild(input);
        }
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include __DIR__.'/../../partials/footer.php'; ?>