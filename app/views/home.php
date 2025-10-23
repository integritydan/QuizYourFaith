<?php include __DIR__.'/partials/header.php'; ?>
<div class="container py-4">
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
