<?php include __DIR__.'/../partials/header.php'; ?>
<div class="container py-4">
    <!-- Hidden elements for modal data extraction -->
    <div id="videoTitle" style="display: none;"><?= htmlspecialchars($video->title) ?></div>
    <div id="videoEmbedUrl" style="display: none;"><?= htmlspecialchars(\App\Models\Video::getYouTubeEmbedUrl($video->youtube_video_id)) ?></div>
    <div id="videoStats" style="display: none;">
        <small class="text-muted">
            <i class="fas fa-eye"></i> <?= number_format($stats->views_count) ?> views
        </small>
        <small class="text-muted ms-2">
            <i class="fas fa-thumbs-up"></i> <?= number_format($stats->likes) ?>
        </small>
        <small class="text-muted ms-2">
            <i class="fas fa-thumbs-down"></i> <?= number_format($stats->dislikes) ?>
        </small>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body p-0">
                    <div class="ratio ratio-16x9">
                        <iframe src="<?= htmlspecialchars(\App\Models\Video::getYouTubeEmbedUrl($video->youtube_video_id)) ?>" 
                                title="<?= htmlspecialchars($video->title) ?>" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen>
                        </iframe>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <?php if ($video->category_name): ?>
                                <span class="badge mb-2" style="background-color: <?= htmlspecialchars($video->category_color) ?>">
                                    <i class="fas fa-<?= htmlspecialchars($video->category_icon) ?>"></i> 
                                    <?= htmlspecialchars($video->category_name) ?>
                                </span>
                            <?php endif; ?>
                            <h4><?= htmlspecialchars($video->title) ?></h4>
                            <?php if ($video->description): ?>
                                <p class="text-muted"><?= nl2br(htmlspecialchars($video->description)) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <div class="reaction-buttons">
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary <?= $userReaction === 'like' ? 'active' : '' ?>" 
                                        onclick="toggleReaction('like')"
                                        id="likeBtn">
                                    <i class="fas fa-thumbs-up"></i> 
                                    <span id="likeCount"><?= number_format($stats->likes) ?></span>
                                </button>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-danger <?= $userReaction === 'dislike' ? 'active' : '' ?>" 
                                        onclick="toggleReaction('dislike')"
                                        id="dislikeBtn">
                                    <i class="fas fa-thumbs-down"></i> 
                                    <span id="dislikeCount"><?= number_format($stats->dislikes) ?></span>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="video-stats border-top pt-3">
                        <div class="row text-center">
                            <div class="col-4">
                                <h5><?= number_format($stats->views_count) ?></h5>
                                <small class="text-muted">Views</small>
                            </div>
                            <div class="col-4">
                                <h5><?= number_format($stats->likes) ?></h5>
                                <small class="text-muted">Likes</small>
                            </div>
                            <div class="col-4">
                                <h5><?= number_format($stats->dislikes) ?></h5>
                                <small class="text-muted">Dislikes</small>
                            </div>
                        </div>
                    </div>

                    <?php if ($video->tags): ?>
                        <div class="border-top pt-3 mt-3">
                            <h6>Tags</h6>
                            <div class="tags">
                                <?php foreach (explode(',', $video->tags) as $tag): ?>
                                    <span class="badge bg-secondary me-1"><?= htmlspecialchars(trim($tag)) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Video Information</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <strong>Added:</strong> <?= date('F j, Y', strtotime($video->created_at)) ?>
                        </li>
                        <li class="mb-2">
                            <strong>Duration:</strong> <?= htmlspecialchars($video->duration ?: 'Not specified') ?>
                        </li>
                        <?php if ($video->category_name): ?>
                            <li class="mb-2">
                                <strong>Category:</strong> 
                                <span class="badge" style="background-color: <?= htmlspecialchars($video->category_color) ?>">
                                    <?= htmlspecialchars($video->category_name) ?>
                                </span>
                            </li>
                        <?php endif; ?>
                        <li class="mb-2">
                            <strong>Added by:</strong> <?= htmlspecialchars($video->created_by_name) ?>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Share This Video</h6>
                </div>
                <div class="card-body">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" 
                               value="<?= htmlspecialchars('https://' . $_SERVER['HTTP_HOST'] . '/videos/watch/' . $video->id) ?>" 
                               readonly id="shareUrl">
                        <button class="btn btn-outline-secondary" type="button" onclick="copyShareUrl()">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary btn-sm" onclick="shareOnFacebook()">
                            <i class="fab fa-facebook"></i> Share on Facebook
                        </button>
                        <button class="btn btn-info btn-sm" onclick="shareOnTwitter()">
                            <i class="fab fa-twitter"></i> Share on Twitter
                        </button>
                    </div>
                </div>
            </div>

            <?php
            // Get related videos from same category
            $relatedVideos = \App\Models\Video::getVideosByCategory($video->category_id, 5);
            $relatedVideos = array_filter($relatedVideos, function($v) use ($video) {
                return $v->id != $video->id;
            });
            ?>
            
            <?php if (!empty($relatedVideos)): ?>
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">Related Videos</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php foreach (array_slice($relatedVideos, 0, 3) as $related): ?>
                                <a href="/videos/watch/<?= $related->id ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex">
                                        <img src="<?= htmlspecialchars($related->thumbnail_url) ?>" 
                                             alt="<?= htmlspecialchars($related->title) ?>" 
                                             class="me-3" style="width: 80px; height: 60px; object-fit: cover;">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 small"><?= htmlspecialchars($related->title) ?></h6>
                                            <small class="text-muted">
                                                <i class="fas fa-eye"></i> <?= number_format($related->views_count) ?>
                                            </small>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function toggleReaction(reaction) {
    const videoId = <?= $video->id ?>;
    const likeBtn = document.getElementById('likeBtn');
    const dislikeBtn = document.getElementById('dislikeBtn');
    
    // Check if already reacted with this type
    const isCurrentlyActive = reaction === 'like' ? likeBtn.classList.contains('active') : dislikeBtn.classList.contains('active');
    
    if (isCurrentlyActive) {
        // Remove reaction
        fetch('/videos/remove-reaction', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ video_id: videoId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove active class from both buttons
                likeBtn.classList.remove('active');
                dislikeBtn.classList.remove('active');
                
                // Update counts
                document.getElementById('likeCount').textContent = data.stats.likes;
                document.getElementById('dislikeCount').textContent = data.stats.dislikes;
            }
        });
    } else {
        // Add reaction
        fetch('/videos/react', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ video_id: videoId, reaction: reaction })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update button states
                if (reaction === 'like') {
                    likeBtn.classList.add('active');
                    dislikeBtn.classList.remove('active');
                } else {
                    dislikeBtn.classList.add('active');
                    likeBtn.classList.remove('active');
                }
                
                // Update counts
                document.getElementById('likeCount').textContent = data.stats.likes;
                document.getElementById('dislikeCount').textContent = data.stats.dislikes;
            }
        });
    }
}

function copyShareUrl() {
    const shareUrl = document.getElementById('shareUrl');
    shareUrl.select();
    document.execCommand('copy');
    
    // Show feedback
    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i>';
    btn.classList.remove('btn-outline-secondary');
    btn.classList.add('btn-success');
    
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-secondary');
    }, 2000);
}

function shareOnFacebook() {
    const url = encodeURIComponent('<?= 'https://' . $_SERVER['HTTP_HOST'] . '/videos/watch/' . $video->id ?>');
    const title = encodeURIComponent('<?= htmlspecialchars($video->title) ?>');
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}&quote=${title}`, '_blank', 'width=600,height=400');
}

function shareOnTwitter() {
    const url = encodeURIComponent('<?= 'https://' . $_SERVER['HTTP_HOST'] . '/videos/watch/' . $video->id ?>');
    const title = encodeURIComponent('<?= htmlspecialchars($video->title) ?>');
    window.open(`https://twitter.com/intent/tweet?url=${url}&text=${title}`, '_blank', 'width=600,height=400');
}
</script>

<?php include __DIR__.'/../partials/footer.php'; ?>