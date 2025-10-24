<?php include __DIR__.'/../partials/header.php'; ?>
<div class="container py-4">
  <h4 class="mb-4">Admin Dashboard</h4>
  <div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="card-dash p-3 text-center"><div class="h5">1 234</div><small>Total Users</small></div></div>
    <div class="col-6 col-lg-3"><div class="card-dash p-3 text-center"><div class="h5">5 678</div><small>Quizzes Taken</small></div></div>
    <div class="col-6 col-lg-3"><div class="card-dash p-3 text-center"><div class="h5">$432</div><small>Donations</small></div></div>
    <div class="col-6 col-lg-3"><div class="card-dash p-3 text-center"><div class="h5">89 %</div><small>Avg Accuracy</small></div></div>
  </div>

  <!-- Admin Quick Links -->
  <div class="row g-3">
    <div class="col-md-6 col-lg-4">
      <div class="card h-100">
        <div class="card-body text-center">
          <i class="fas fa-video fa-2x text-primary mb-3"></i>
          <h6>Video Messages</h6>
          <p class="small text-muted">Manage YouTube video messages and categories</p>
          <a href="/admin/videos" class="btn btn-primary btn-sm">Manage Videos</a>
        </div>
      </div>
    </div>
    <div class="col-md-6 col-lg-4">
      <div class="card h-100">
        <div class="card-body text-center">
          <i class="fas fa-users fa-2x text-info mb-3"></i>
          <h6>User Management</h6>
          <p class="small text-muted">View and manage user accounts</p>
          <a href="/admin/users" class="btn btn-info btn-sm">Manage Users</a>
        </div>
      </div>
    </div>
    <div class="col-md-6 col-lg-4">
      <div class="card h-100">
        <div class="card-body text-center">
          <i class="fas fa-cog fa-2x text-secondary mb-3"></i>
          <h6>System Settings</h6>
          <p class="small text-muted">Configure system settings and preferences</p>
          <a href="/admin/settings" class="btn btn-secondary btn-sm">Settings</a>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>
