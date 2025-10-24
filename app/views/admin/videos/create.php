<?php include __DIR__.'/../../partials/header.php'; ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>📺 Add New YouTube Video</h4>
        <a href="/admin/videos" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Videos
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
            <form action="/admin/videos/store" method="POST" id="videoForm">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="title" class="form-label">Video Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required 
                                   placeholder="Enter video title">
                        </div>

                        <div class="mb-3">
                            <label for="youtube_url" class="form-label">YouTube URL <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" id="youtube_url" name="youtube_url" required 
                                   placeholder="https://www.youtube.com/watch?v=VIDEO_ID" 
                                   onchange="extractVideoInfo()">
                            <div class="form-text">Paste the full YouTube URL here</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" 
                                      placeholder="Enter video description (optional)"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                    <select class="form-select" id="category_id" name="category_id" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category->id ?>">
                                                <?= htmlspecialchars($category->name) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="duration" class="form-label">Duration</label>
                                    <input type="text" class="form-control" id="duration" name="duration" 
                                           placeholder="e.g., 15:30" pattern="[0-9]{1,2}:[0-5][0-9]">
                                    <div class="form-text">Format: MM:SS</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tags" class="form-label">Tags</label>
                                    <input type="text" class="form-control" id="tags" name="tags" 
                                           placeholder="bible, teaching, inspiration">
                                    <div class="form-text">Separate tags with commas</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="display_order" class="form-label">Display Order</label>
                                    <input type="number" class="form-control" id="display_order" name="display_order" 
                                           value="0" min="0">
                                    <div class="form-text">Lower numbers appear first</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                <label class="form-check-label" for="is_active">
                                    Active (show in slider)
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Video Preview</h6>
                            </div>
                            <div class="card-body text-center">
                                <div id="videoPreview" class="d-none">
                                    <img id="thumbnailPreview" src="" alt="Video Thumbnail" 
                                         class="img-fluid mb-3" style="max-height: 200px;">
                                    <div id="videoInfo" class="text-start"></div>
                                </div>
                                <div id="noPreview" class="text-muted">
                                    <i class="fas fa-video fa-3x mb-3"></i>
                                    <p>Enter a YouTube URL to see preview</p>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="mb-0">Quick Tips</h6>
                            </div>
                            <div class="card-body">
                                <ul class="small mb-0">
                                    <li>Use descriptive titles that explain the message</li>
                                    <li>Add relevant tags for better organization</li>
                                    <li>Set display order to control slider sequence</li>
                                    <li>Choose appropriate categories for grouping</li>
                                    <li>Test the video link before saving</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Video
                        </button>
                        <a href="/admin/videos" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function extractVideoInfo() {
    const url = document.getElementById('youtube_url').value.trim();
    const videoId = extractYouTubeId(url);
    
    if (videoId) {
        // Show preview
        document.getElementById('videoPreview').classList.remove('d-none');
        document.getElementById('noPreview').classList.add('d-none');
        
        // Set thumbnail
        const thumbnailUrl = `https://img.youtube.com/vi/${videoId}/maxresdefault.jpg`;
        document.getElementById('thumbnailPreview').src = thumbnailUrl;
        
        // Show video info
        document.getElementById('videoInfo').innerHTML = `
            <p><strong>Video ID:</strong> ${videoId}</p>
            <p><strong>Embed URL:</strong><br><small class="text-break">https://www.youtube.com/embed/${videoId}</small></p>
        `;
        
        // Try to fetch video title (requires YouTube API key)
        // For now, we'll just show the video ID
    } else {
        // Hide preview
        document.getElementById('videoPreview').classList.add('d-none');
        document.getElementById('noPreview').classList.remove('d-none');
    }
}

function extractYouTubeId(url) {
    const regex = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i;
    const match = url.match(regex);
    return match ? match[1] : null;
}

// Auto-extract when URL is pasted
document.getElementById('youtube_url').addEventListener('input', function() {
    if (this.value.trim()) {
        extractVideoInfo();
    }
});

// Form validation
document.getElementById('videoForm').addEventListener('submit', function(e) {
    const url = document.getElementById('youtube_url').value.trim();
    const videoId = extractYouTubeId(url);
    
    if (!videoId) {
        e.preventDefault();
        alert('Please enter a valid YouTube URL');
        return false;
    }
});
</script>

<?php include __DIR__.'/../../partials/footer.php'; ?>