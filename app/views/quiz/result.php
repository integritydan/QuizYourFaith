<?php include __DIR__.'/../partials/header.php'; ?>
<div class="container text-center py-5">
  <h2 class="mb-2">Great Job!</h2>
  <div class="points mb-2"><?=$score??780?></div>
  <p class="mb-4">Points earned this round</p>
  <a href="/quiz/play" class="btn btn-primary w-100 py-3 mb-2">Play Again</a>
  <a href="/challenge" class="btn btn-outline-light w-100 py-3">Challenge a Friend</a>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>
