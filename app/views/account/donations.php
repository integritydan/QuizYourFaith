<?php include __DIR__.'/../partials/header.php'; ?>
<div class="container py-4">
  <h4>My Donations</h4>
  <div class="card-dash p-3 mb-4">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h5 class="mb-0">Total Contributed</h5>
        <small class="text-muted">Across all gateways</small>
      </div>
      <div class="h3 text-success">$ 47.50</div>
    </div>
  </div>
  <div class="table-responsive">
    <table class="table table-dark table-striped">
      <thead><tr><th>Date</th><th>Gateway</th><th>Amount</th><th>Status</th></tr></thead>
      <tbody>
        <tr><td>2025-10-22</td><td>Paystack</td><td>$ 20.00</td><td><span class="badge bg-success">Paid</span></td></tr>
        <tr><td>2025-10-15</td><td>PayPal</td><td>$ 27.50</td><td><span class="badge bg-success">Paid</span></td></tr>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>
