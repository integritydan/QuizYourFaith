<?php include __DIR__.'/../partials/header.php'; ?>
<div class="container py-4">
  <div class="row g-4">
    <!-- left nav -->
    <div class="col-lg-3">
      <div class="card-dash p-3">
        <div class="text-center mb-3">
          <img src="<?=$user['avatar']??'/uploads/default.png'?>" class="rounded-circle" width="80" height="80" alt="avatar">
          <h6 class="mt-2"><?=$user['name']??'Guest'?></h6>
        </div>
        <nav class="nav flex-column">
          <a class="nav-link" href="/account">Overview</a>
          <a class="nav-link" href="/account/settings">Settings</a>
          <a class="nav-link" href="/account/profile">Profile</a>
          <a class="nav-link" href="/account/donations">My Donations</a>
        </nav>
      </div>
    </div>

    <!-- right content -->
    <div class="col-lg-9">
      <h4>Account Overview</h4>
      <div class="row g-3 mt-1">
        <div class="col-md-4"><div class="card-dash p-3 text-center"><div class="h5">845</div><small>Total Points</small></div></div>
        <div class="col-md-4"><div class="card-dash p-3 text-center"><div class="h5">$47.50</div><small>Total Donated</small></div></div>
        <div class="col-md-4"><div class="card-dash p-3 text-center"><div class="h5">7</div><small>Day Streak</small></div></div>
      </div>

      <!-- public support badge -->
      <div class="card-dash p-3 mt-4">
        <h6>Public Support Badge</h6>
        <p>Share this link to receive donations:</p>
        <div class="input-group mb-3">
          <input class="form-control" value="https://quizyourfaith.com/support/<?=$user['id']??123?>" readonly>
          <button class="btn btn-royal">Copy</button>
        </div>
        <!-- social -->
        <div class="d-flex gap-2">
          <a class="btn btn-sm btn-primary" href="#">📘</a>
          <a class="btn btn-sm btn-info"   href="#">🐦</a>
          <a class="btn btn-sm btn-success" href="#">📱</a>
          <a class="btn btn-sm btn-danger"  href="#">📧</a>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>
