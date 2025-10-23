<?php include __DIR__.'/../partials/header.php'; ?>
<div class="container py-3">
  <!-- top bar -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div class="timer" id="timer">12:33</div>
    <div class="text-end">
      <div class="points"><?=$points??0?></div><small>POINTS</small>
    </div>
  </div>

  <!-- progress -->
  <div class="progress mb-4" style="height:8px;"><div class="progress-bar bg-primary" style="width:40%"></div></div>

  <!-- question card -->
  <div class="card p-3 mb-4">
    <div class="question mb-3">Who was the father of David?</div>
    <div class="row g-2">
      <div class="col-12"><div class="answer-block bg-light text-dark">Jesse</div></div>
      <div class="col-12"><div class="answer-block bg-light text-dark">Saul</div></div>
      <div class="col-12"><div class="answer-block bg-light text-dark">Samuel</div></div>
      <div class="col-12"><div class="answer-block bg-light text-dark">Solomon</div></div>
    </div>
  </div>

  <!-- action row -->
  <div class="d-flex gap-2">
    <button class="btn btn-outline-light flex-fill">Skip</button>
    <button class="btn btn-primary flex-fill">Confirm</button>
  </div>
</div>

<script>
/* simple countdown demo */
let sec=parseInt("<?=$timer??753?>"); // 12:33 = 753 s
setInterval(()=>{
  let m=String(Math.floor(sec/60)).padStart(2,0),s=String(sec%60).padStart(2,0);
  document.getElementById('timer').textContent=`${m}:${s}`;
  if(sec)--sec;
},1000);
</script>
<?php include __DIR__.'/../partials/footer.php'; ?>
