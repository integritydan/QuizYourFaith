<?php include APP_PATH.'/views/layouts/header.php'; ?>
<h4>Dashboard</h4>
<div class="row">
  <div class="col-md-4"><div class="card"><div class="card-body"><h5><?=$stats->taken??0?></h5>Quizzes taken</div></div></div>
  <div class="col-md-4"><div class="card"><div class="card-body"><h5><?=number_format(($stats->acc??0)*100,1)?>%</h5>Average accuracy</div></div></div>
  <div class="col-md-4"><div class="card"><div class="card-body"><h5><?=$user->name??''?></h5>Welcome</div></div></div>
</div>
<?php include APP_PATH.'/views/layouts/footer.php'; ?>
