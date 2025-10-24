<?php include APP_PATH.'/views/layouts/header.php'; ?>
<div class="container py-4">
  <h2 class="mb-4">Choose a Quiz</h2>
  
  <?php if(empty($quizzes)): ?>
    <div class="alert alert-info">No quizzes available at the moment. Please check back later!</div>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach($quizzes as $quiz): ?>
        <div class="col-md-6 col-lg-4">
          <div class="card h-100 shadow-sm">
            <div class="card-body">
              <h5 class="card-title"><?=htmlspecialchars($quiz->title)?></h5>
              <p class="card-text"><?=htmlspecialchars($quiz->description)?></p>
              
              <?php if(isset($quiz->difficulty)): ?>
                <span class="badge bg-<?=$quiz->difficulty=='easy'?'success':($quiz->difficulty=='medium'?'warning':'danger')?> mb-2">
                  <?=ucfirst($quiz->difficulty)?>
                </span>
              <?php endif; ?>
              
              <?php if(isset($quiz->question_count)): ?>
                <p class="text-muted small mb-3">
                  <i class="bi bi-question-circle"></i> <?=$quiz->question_count?> questions
                </p>
              <?php endif; ?>
            </div>
            <div class="card-footer bg-transparent">
              <a href="/quiz/<?=$quiz->id?>" class="btn btn-primary w-100">Start Quiz</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<style>
.card {
  transition: transform 0.2s;
}
.card:hover {
  transform: translateY(-5px);
}
</style>
<?php include APP_PATH.'/views/layouts/footer.php'; ?>
