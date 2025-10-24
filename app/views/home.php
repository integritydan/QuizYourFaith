<?php include __DIR__.'/partials/header.php'; ?>
<div class="container py-4">
  <!-- Video Messages Slider -->
  <div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5>📺 Life-Changing Bible Messages</h5>
        <a href="/videos" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <?php
    // Get latest videos for homepage slider
    $videos = \App\Models\Video::getActiveVideos(5);
    if (!empty($videos)):
    ?>
    <div id="homeVideoSlider" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php foreach ($videos as $index => $video): ?>
            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                <div class="row align-items-center">
                    <div class="col-md-5">
                        <div class="position-relative">
                            <img src="<?= htmlspecialchars($video->thumbnail_url) ?>"
                                 alt="<?= htmlspecialchars($video->title) ?>"
                                 class="img-fluid rounded shadow-sm"
                                 style="cursor: pointer; max-height: 200px; object-fit: cover;"
                                 onclick="window.location.href='/videos/watch/<?= $video->id ?>'">
                            <div class="position-absolute top-50 start-50 translate-middle">
                                <button class="btn btn-danger rounded-circle"
                                        onclick="window.location.href='/videos/watch/<?= $video->id ?>'"
                                        style="width: 40px; height: 40px;">
                                    <i class="fas fa-play"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="video-info">
                            <?php if ($video->category_name): ?>
                                <span class="badge mb-2" style="background-color: <?= htmlspecialchars($video->category_color) ?>">
                                    <?= htmlspecialchars($video->category_name) ?>
                                </span>
                            <?php endif; ?>
                            <h6 class="mb-2"><?= htmlspecialchars($video->title) ?></h6>
                            <?php if ($video->description): ?>
                                <p class="small text-muted mb-2"><?= htmlspecialchars(substr($video->description, 0, 150)) ?>...</p>
                            <?php endif; ?>
                            <div class="video-stats mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-eye"></i> <?= number_format($video->views_count) ?> views
                                </small>
                                <small class="text-muted ms-2">
                                    <i class="fas fa-calendar"></i> <?= date('M j, Y', strtotime($video->created_at)) ?>
                                </small>
                            </div>
                            <a href="/videos/watch/<?= $video->id ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-play"></i> Watch Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if (count($videos) > 1): ?>
        <button class="carousel-control-prev" type="button" data-bs-target="#homeVideoSlider" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#homeVideoSlider" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="text-center py-4 bg-light rounded">
        <i class="fas fa-video fa-2x text-muted mb-2"></i>
        <p class="text-muted mb-0">No video messages available yet. Check back soon!</p>
    </div>
    <?php endif; ?>
  </div>

  <!-- top stats row -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="card-dash p-3 text-center"><div class="icon-bible">📖</div><div class="h5 mt-2">845</div><small>Total Points</small></div></div>
    <div class="col-6 col-lg-3"><div class="card-dash p-3 text-center"><div class="icon-bible">⚡</div><div class="h5 mt-2">32</div><small>Quizzes Played</small></div></div>
    <div class="col-6 col-lg-3"><div class="card-dash p-3 text-center"><div class="icon-bible">🔥</div><div class="h5 mt-2">7</div><small>Day Streak</small></div></div>
    <div class="col-6 col-lg-3"><div class="card-dash p-3 text-center"><div class="icon-bible">🏆</div><div class="h5 mt-2">#41</div><small>Global Rank</small></div></div>
  </div>

  <!-- multi-player CTA -->
  <div class="card-dash p-4 text-center mb-4">
    <h5>Multi-Player Arena</h5>
    <p class="mb-3">Challenge a friend in real-time Bible trivia!</p>
    <a href="/multiplayer" class="btn btn-royal w-100 py-2">⚔️ Challenge a Friend</a>
  </div>

  <!-- categories -->
  <h5 class="mb-3">Quick Categories</h5>
  <div class="row g-2">
    <?php foreach(['Old Testament','New Testament','Miracles','Characters','Parables','Prophets'] as $c): ?>
      <div class="col-6 col-lg-4"><a class="btn btn-outline-light w-100 mb-2"><?=$c?></a></div>
    <?php endforeach; ?>
  </div>
</div>
<?php include __DIR__.'/partials/footer.php'; ?>
