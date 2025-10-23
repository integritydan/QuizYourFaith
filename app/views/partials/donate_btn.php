<?php
use App\Models\PaymentKey;
$pk=PaymentKey::get('paystack');
$pp=PaymentKey::get('paypal');
$fw=PaymentKey::get('flutterwave');
?>
<div class="card-dash p-3 mb-3 text-center">
  <h6>Support Us</h6>
  <div class="btn-group w-100" role="group">
    <?php if($pk['active']): ?>
      <a href="https://paystack.com/pay/<?=$pk['public']?>" class="btn btn-success">Paystack</a>
    <?php endif; ?>
    <?php if($pp['active']): ?>
      <a href="https://www.paypal.com/donate?hosted_button_id=<?=$pp['public']?>" class="btn btn-primary">PayPal</a>
    <?php endif; ?>
    <?php if($fw['active']): ?>
      <a href="https://checkout.flutterwave.com/pay/<?=$fw['public']?>" class="btn btn-warning">Flutterwave</a>
    <?php endif; ?>
  </div>
</div>
