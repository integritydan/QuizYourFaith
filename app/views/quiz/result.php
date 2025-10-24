<?php include __DIR__.'/../partials/header.php'; ?>
<div class="container text-center py-5">
  <h2 class="mb-2">
    <?php if($score >= 80): ?>
      Excellent Work! 🎉
    <?php elseif($score >= 60): ?>
      Good Job! 👍
    <?php elseif($score >= 40): ?>
      Not Bad! 😊
    <?php else: ?>
      Keep Practicing! 💪
    <?php endif; ?>
  </h2>
  
  <div class="card mb-4">
    <div class="card-body">
      <div class="display-4 text-primary mb-2"><?=$score?>%</div>
      <p class="text-muted mb-3">You got <?=$correct_answers?> out of <?=$total_questions?> questions correct</p>
      
      <div class="progress mb-3" style="height: 10px;">
        <div class="progress-bar bg-primary" style="width: <?=$score?>%"></div>
      </div>
      
      <?php if($score >= 80): ?>
        <div class="alert alert-success">Outstanding performance! You're a quiz master!</div>
      <?php elseif($score >= 60): ?>
        <div class="alert alert-info">Well done! You have a good understanding of the material.</div>
      <?php elseif($score >= 40): ?>
        <div class="alert alert-warning">Not bad! There's room for improvement.</div>
      <?php else: ?>
        <div class="alert alert-danger">Keep studying and try again!</div>
      <?php endif; ?>
    </div>
  </div>
  
  <div class="d-grid gap-2">
    <a href="/quiz/<?=$quiz->id?>" class="btn btn-primary py-3">Try Again</a>
    <a href="/quizzes" class="btn btn-outline-primary py-3">Browse Other Quizzes</a>
    <a href="/dashboard" class="btn btn-outline-secondary py-3">Back to Dashboard</a>
  </div>
</div>

<style>
.display-4 { font-size: 3rem; font-weight: 300; }
</style>
<?php include __DIR__.'/../partials/footer.php'; ?>
