<?php include APP_PATH.'/views/layouts/header.php'; ?>
<h4>Pick a Quiz</h4>
<div class="list-group">
<?php foreach($quizzes as $q): ?>
  <a href="/quiz/<?=$q->id?>" class="list-group-item list-group-item-action">
    <b><?=htmlspecialchars($q->title)?></b> – <?=htmlspecialchars($q->description)?>
  </a>
<?php endforeach; ?>
</div>
<?php include APP_PATH.'/views/layouts/footer.php'; ?>
