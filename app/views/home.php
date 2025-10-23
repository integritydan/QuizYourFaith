<?php include __DIR__.'/partials/header.php'; ?>
<div class="container py-4">
  <div class="row g-3">
    <div class="col-6"><div class="card p-3 text-center"><div class="points">456</div><small>Total Points</small></div></div>
    <div class="col-6"><div class="card p-3 text-center"><div class="points">38</div><small>Quizzes Played</small></div></div>
  </div>
  <h5 class="mt-4 mb-3">Quick Play</h5>
  <a href="/quiz/play" class="btn btn-primary w-100 py-3 mb-3">▶ Start Daily Challenge</a>
  <h5 class="mt-3 mb-3">Categories</h5>
  <div class="row g-2">
    <?php foreach(['Old Testament','New Testament','Miracles','Characters'] as $c): ?>
      <div class="col-6"><a class="btn btn-outline-light w-100"><?=$c?></a></div>
    <?php endforeach; ?>
  </div>
</div>
<?php include __DIR__.'/partials/footer.php'; ?>
