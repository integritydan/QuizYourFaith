<?php include __DIR__.'/../partials/header.php'; ?>
<div class="container py-3">
  <!-- top bar -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div class="timer" id="timer"><?=gmdate("i:s", $timer)?></div>
    <div class="text-end">
      <div class="points"><?=$points??0?></div><small>POINTS</small>
    </div>
  </div>

  <!-- progress -->
  <div class="progress mb-4" style="height:8px;">
    <div class="progress-bar bg-primary" style="width:<?=(($current_question + 1) / count($questions)) * 100?>%"></div>
  </div>

  <!-- quiz info -->
  <h4 class="mb-3"><?=htmlspecialchars($quiz->title)?></h4>
  
  <?php if(isset($questions[$current_question])):
    $question = $questions[$current_question];
    $answers = json_decode($question->answers, true) ?? [];
  ?>
  
  <!-- question card -->
  <form method="post" action="/result/<?=$quiz->id?>" id="quizForm">
    <input type="hidden" name="current_question" value="<?=$current_question?>">
    
    <div class="card p-3 mb-4">
      <div class="question mb-3">
        <span class="text-muted">Question <?=($current_question + 1)?> of <?=count($questions)?>:</span><br>
        <?=htmlspecialchars($question->question)?>
      </div>
      <div class="row g-2">
        <?php foreach($answers as $index => $answer): ?>
        <div class="col-12">
          <label class="answer-block bg-light text-dark d-block p-3 rounded cursor-pointer">
            <input type="radio" name="answers[<?=$question->id?>]" value="<?=$index?>" class="d-none">
            <?=htmlspecialchars($answer)?>
          </label>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- action row -->
    <div class="d-flex gap-2">
      <?php if($current_question > 0): ?>
      <button type="button" class="btn btn-outline-secondary flex-fill" onclick="previousQuestion()">Previous</button>
      <?php endif; ?>
      
      <?php if($current_question < count($questions) - 1): ?>
      <button type="button" class="btn btn-outline-primary flex-fill" onclick="nextQuestion()">Next</button>
      <?php else: ?>
      <button type="submit" class="btn btn-primary flex-fill">Submit Quiz</button>
      <?php endif; ?>
    </div>
  </form>
  
  <?php else: ?>
  <div class="alert alert-warning">No questions available for this quiz.</div>
  <?php endif; ?>
</div>

<script>
let currentQuestion = <?=$current_question?>;
let totalQuestions = <?=count($questions)?>;
let timeRemaining = <?=$timer?>;

// Timer countdown
const timerInterval = setInterval(() => {
  if(timeRemaining > 0) {
    timeRemaining--;
    const minutes = Math.floor(timeRemaining / 60);
    const seconds = timeRemaining % 60;
    document.getElementById('timer').textContent =
      String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
    
    // Auto-submit when time runs out
    if(timeRemaining === 0) {
      clearInterval(timerInterval);
      document.getElementById('quizForm').submit();
    }
  }
}, 1000);

// Answer selection
document.querySelectorAll('.answer-block').forEach(block => {
  block.addEventListener('click', function() {
    // Remove selection from all answers in this question
    this.closest('.card').querySelectorAll('.answer-block').forEach(b => {
      b.classList.remove('bg-primary', 'text-white');
      b.classList.add('bg-light', 'text-dark');
    });
    
    // Select this answer
    this.classList.remove('bg-light', 'text-dark');
    this.classList.add('bg-primary', 'text-white');
    this.querySelector('input[type="radio"]').checked = true;
  });
});

function nextQuestion() {
  // For now, submit the form to handle question navigation
  // In a future enhancement, this could be AJAX-based
  document.getElementById('quizForm').submit();
}

function previousQuestion() {
  // For now, submit the form to handle question navigation
  document.getElementById('quizForm').submit();
}
</script>
<?php include __DIR__.'/../partials/footer.php'; ?>
