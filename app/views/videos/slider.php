<?php include __DIR__.'/../partials/header.php'; ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>📺 Life-Changing Bible Messages</h4>
        <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-primary" onclick="filterVideos('all')">All</button>
            <?php foreach ($categories as $category): ?>
                <button type="button" class="btn btn-outline-primary" onclick="filterVideos('<?= $category->slug ?>')">
                    <?= htmlspecialchars($category->name) ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (empty($videos)): ?>
        <div class="text-center py-5">
            <i class="fas fa-video fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No videos available</h5>
            <p class="text-muted">Check back later for inspiring Bible messages.</p>
        </div>
    <?php else: ?>
        <!-- Video Slider -->
        <div id="videoSlider" class="carousel slide mb-4" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php foreach ($videos as $index => $video): ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>" data-category="<?= htmlspecialchars($video->category_name ?? 'uncategorized') ?>">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="position-relative">
                                    <img src="<?= htmlspecialchars($video->thumbnail_url) ?>" 
                                         alt="<?= htmlspecialchars($video->title) ?>" 
                                         class="img-fluid rounded shadow-sm"
                                         style="cursor: pointer;"
                                         onclick="watchVideo(<?= $video->id ?>)">
                                    <div class="position-absolute top-50 start-50 translate-middle">
                                        <button class="btn btn-danger btn-lg rounded-circle" 
                                                onclick="watchVideo(<?= $video->id ?>)"
                                                style="width: 60px; height: 60px;">
                                            <i class="fas fa-play"></i>
                                        </button>
                                    </div>
                                    <?php if ($video->duration): ?>
                                        <div class="position-absolute bottom-0 end-0 m-2">
                                            <span class="badge bg-dark"><?= htmlspecialchars($video->duration) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="video-info">
                                    <?php if ($video->category_name): ?>
                                        <span class="badge mb-2" style="background-color: <?= htmlspecialchars($video->category_color) ?>">
                                            <i class="fas fa-<?= htmlspecialchars($video->category_icon) ?>"></i> 
                                            <?= htmlspecialchars($video->category_name) ?>
                                        </span>
                                    <?php endif; ?>
                                    <h5 class="mb-2"><?= htmlspecialchars($video->title) ?></h5>
                                    <?php if ($video->description): ?>
                                        <p class="text-muted mb-3"><?= htmlspecialchars(substr($video->description, 0, 200)) ?>...</p>
                                    <?php endif; ?>
                                    <div class="video-stats mb-3">
                                        <small class="text-muted me-3">
                                            <i class="fas fa-eye"></i> <?= number_format($video->views_count) ?> views
                                        </small>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar"></i> <?= date('M j, Y', strtotime($video->created_at)) ?>
                                        </small>
                                    </div>
                                    <button class="btn btn-primary" onclick="watchVideo(<?= $video->id ?>)">
                                        <i class="fas fa-play"></i> Watch Now
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (count($videos) > 1): ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#videoSlider" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#videoSlider" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            <?php endif; ?>
        </div>

        <!-- Video Grid for Mobile/Smaller Screens -->
        <div class="row g-4 d-md-none">
            <?php foreach ($videos as $video): ?>
                <div class="col-12 video-item" data-category="<?= htmlspecialchars($video->category_name ?? 'uncategorized') ?>">
                    <div class="card h-100">
                        <div class="position-relative">
                            <img src="<?= htmlspecialchars($video->thumbnail_url) ?>" 
                                 alt="<?= htmlspecialchars($video->title) ?>" 
                                 class="card-img-top"
                                 style="cursor: pointer; height: 200px; object-fit: cover;"
                                 onclick="watchVideo(<?= $video->id ?>)">
                            <div class="position-absolute top-50 start-50 translate-middle">
                                <button class="btn btn-danger rounded-circle" 
                                        onclick="watchVideo(<?= $video->id ?>)"
                                        style="width: 40px; height: 40px;">
                                    <i class="fas fa-play"></i>
                                </button>
                            </div>
                            <?php if ($video->duration): ?>
                                <div class="position-absolute bottom-0 end-0 m-2">
                                    <span class="badge bg-dark"><?= htmlspecialchars($video->duration) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <?php if ($video->category_name): ?>
                                <span class="badge mb-2" style="background-color: <?= htmlspecialchars($video->category_color) ?>">
                                    <i class="fas fa-<?= htmlspecialchars($video->category_icon) ?>"></i> 
                                    <?= htmlspecialchars($video->category_name) ?>
                                </span>
                            <?php endif; ?>
                            <h6 class="card-title"><?= htmlspecialchars($video->title) ?></h6>
                            <?php if ($video->description): ?>
                                <p class="card-text small text-muted"><?= htmlspecialchars(substr($video->description, 0, 100)) ?>...</p>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-eye"></i> <?= number_format($video->views_count) ?>
                                </small>
                                <button class="btn btn-sm btn-primary" onclick="watchVideo(<?= $video->id ?>)">
                                    Watch
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Video Player Modal -->
<div class="modal fade" id="videoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="videoModalTitle">Loading...</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="videoPlayerContainer"></div>
            </div>
            <div class="modal-footer">
                <div id="videoStats" class="me-auto"></div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function watchVideo(videoId) {
    // Show loading state
    document.getElementById('videoModalTitle').textContent = 'Loading...';
    document.getElementById('videoPlayerContainer').innerHTML = '<div class="text-center p-5"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('videoModal'));
    modal.show();
    
    // Fetch video data
    fetch(`/videos/watch/${videoId}`)
        .then(response => response.text())
        .then(html => {
            // Create a temporary div to parse the HTML
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            
            // Extract video data from the response
            const videoTitle = tempDiv.querySelector('#videoTitle')?.textContent || 'Video';
            const videoEmbedUrl = tempDiv.querySelector('#videoEmbedUrl')?.textContent || '';
            const videoStats = tempDiv.querySelector('#videoStats')?.innerHTML || '';
            
            // Update modal content
            document.getElementById('videoModalTitle').textContent = videoTitle;
            document.getElementById('videoPlayerContainer').innerHTML = `
                <div class="ratio ratio-16x9">
                    <iframe src="${videoEmbedUrl}" 
                            title="${videoTitle}" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                    </iframe>
                </div>
            `;
            document.getElementById('videoStats').innerHTML = videoStats;
        })
        .catch(error => {
            document.getElementById('videoPlayerContainer').innerHTML = 
                '<div class="text-center p-5 text-danger">Error loading video. Please try again.</div>';
        });
}

function filterVideos(category) {
    const items = document.querySelectorAll('.carousel-item, .video-item');
    
    items.forEach(item => {
        const itemCategory = item.getAttribute('data-category');
        if (category === 'all' || itemCategory === category) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
    
    // Reset carousel to first active slide
    const carousel = document.getElementById('videoSlider');
    if (carousel) {
        const carouselInstance = bootstrap.Carousel.getInstance(carousel);
        if (carouselInstance) {
            carouselInstance.to(0);
        }
    }
}

// Auto-advance carousel
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('videoSlider');
    if (carousel) {
        // Set interval for auto-advance (5 seconds)
        setInterval(function() {
            const carouselInstance = bootstrap.Carousel.getInstance(carousel);
            if (carouselInstance) {
                carouselInstance.next();
            }
        }, 5000);
    }
});
</script>

<?php include __DIR__.'/../partials/footer.php'; ?>