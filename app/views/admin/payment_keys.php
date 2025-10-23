<?php include __DIR__.'/../partials/header.php'; ?>
<div class="container py-4">
  <h4>Payment Gateways</h4>
  <?php if($_GET['saved']??0): ?>
    <div class="alert alert-success">Keys saved & encrypted.</div>
  <?php endif; ?>
  <form method="post" action="/admin/payment-keys/save">
    <!-- Paystack -->
    <div class="card-dash p-3 mb-3">
      <h6>Paystack</h6>
      <div class="mb-3"><label class="form-label">Public Key</label><input name="paystack_public" class="form-control" value="<?=$paystack['public']?>"></div>
      <div class="mb-3"><label class="form-label">Secret Key</label><input name="paystack_secret" class="form-control" value="<?=$paystack['secret']?>"></div>
      <div class="form-check"><input class="form-check-input" type="checkbox" name="paystack_active" <?=($paystack['active']??0)?'checked':''?>><label class="form-check-label">Active</label></div>
    </div>

    <!-- PayPal -->
    <div class="card-dash p-3 mb-3">
      <h6>PayPal</h6>
      <div class="mb-3"><label class="form-label">Client ID</label><input name="paypal_public" class="form-control" value="<?=$paypal['public']?>"></div>
      <div class="mb-3"><label class="form-label">Client Secret</label><input name="paypal_secret" class="form-control" value="<?=$paypal['secret']?>"></div>
      <div class="form-check"><input class="form-check-input" type="checkbox" name="paypal_sandbox" <?=($paypal['sandbox']??0)?'checked':''?>><label class="form-check-label">Sandbox Mode</label></div>
      <div class="form-check"><input class="form-check-input" type="checkbox" name="paypal_active" <?=($paypal['active']??0)?'checked':''?>><label class="form-check-label">Active</label></div>
    </div>

    <!-- Flutterwave -->
    <div class="card-dash p-3 mb-3">
      <h6>Flutterwave</h6>
      <div class="mb-3"><label class="form-label">Public Key</label><input name="flutterwave_public" class="form-control" value="<?=$flutterwave['public']?>"></div>
      <div class="mb-3"><label class="form-label">Secret Key</label><input name="flutterwave_secret" class="form-control" value="<?=$flutterwave['secret']?>"></div>
      <div class="mb-3"><label class="form-label">Encryption Key</label><input name="flutterwave_encrypt" class="form-control" value="<?=$flutterwave['encrypt']?>"></div>
      <div class="form-check"><input class="form-check-input" type="checkbox" name="flutterwave_active" <?=($flutterwave['active']??0)?'checked':''?>><label class="form-check-label">Active</label></div>
    </div>

    <button class="btn btn-success">Save Keys</button>
  </form>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>
