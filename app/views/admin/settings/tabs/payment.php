<div class="card shadow mb-4 settings-card payment">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Payment Gateway Configuration</h6>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <h6><i class="fas fa-info-circle"></i> Payment Gateway Setup Guide</h6>
            <p>Configure payment gateways to accept donations and process tournament entry fees.</p>
            <small>
                <strong>Paystack:</strong> <a href="https://dashboard.paystack.com/#/settings/developer" target="_blank">Paystack Dashboard</a><br>
                <strong>PayPal:</strong> <a href="https://developer.paypal.com/developer/applications" target="_blank">PayPal Developer</a><br>
                <strong>Stripe:</strong> <a href="https://dashboard.stripe.com/apikeys" target="_blank">Stripe Dashboard</a><br>
                <strong>Flutterwave:</strong> <a href="https://dashboard.flutterwave.com/dashboard/settings/apis" target="_blank">Flutterwave Dashboard</a>
            </small>
        </div>

        <?php foreach ($payment_gateways as $gateway): ?>
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-credit-card"></i> 
                    <?= htmlspecialchars($gateway->display_name) ?>
                    <?php if ($gateway->test_mode): ?>
                    <span class="badge badge-warning">Test Mode</span>
                    <?php endif; ?>
                </h6>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" 
                           id="gateway_<?= $gateway->id ?>_enabled" 
                           <?= $gateway->is_enabled ? 'checked' : '' ?>
                           onchange="togglePaymentGateway(<?= $gateway->id ?>)">
                    <label class="custom-control-label" for="gateway_<?= $gateway->id ?>_enabled">
                        <?= $gateway->is_enabled ? 'Enabled' : 'Disabled' ?>
                    </label>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/settings/update-payment-gateway" class="payment-form" data-gateway-id="<?= $gateway->id ?>">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="gateway_id" value="<?= $gateway->id ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="public_key_<?= $gateway->id ?>">Public Key *</label>
                                <input type="text" class="form-control" 
                                       id="public_key_<?= $gateway->id ?>" 
                                       name="public_key" 
                                       value="<?= htmlspecialchars($gateway->public_key ?? '') ?>" 
                                       required>
                                <small class="form-text text-muted">Your public/ publishable API key</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="secret_key_<?= $gateway->id ?>">Secret Key *</label>
                                <div class="input-group">
                                    <input type="password" class="form-control secret-field" 
                                           id="secret_key_<?= $gateway->id ?>" 
                                           name="secret_key" 
                                           placeholder="Enter new secret key to update">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary toggle-secret" 
                                                data-target="#secret_key_<?= $gateway->id ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <?php if (!empty($gateway->secret_key)): ?>
                                <small class="form-text text-muted">Leave empty to keep current secret key</small>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (in_array($gateway->gateway_type, ['paystack', 'flutterwave'])): ?>
                            <div class="form-group">
                                <label for="webhook_secret_<?= $gateway->id ?>">Webhook Secret</label>
                                <div class="input-group">
                                    <input type="password" class="form-control secret-field" 
                                           id="webhook_secret_<?= $gateway->id ?>" 
                                           name="webhook_secret" 
                                           placeholder="Enter webhook secret">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary toggle-secret" 
                                                data-target="#webhook_secret_<?= $gateway->id ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="form-text text-muted">For webhook verification (optional)</small>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-6">
                            <?php if (in_array($gateway->gateway_type, ['paystack', 'flutterwave'])): ?>
                            <div class="form-group">
                                <label for="merchant_id_<?= $gateway->id ?>">Merchant ID</label>
                                <input type="text" class="form-control" 
                                       id="merchant_id_<?= $gateway->id ?>" 
                                       name="merchant_id" 
                                       value="<?= htmlspecialchars($gateway->merchant_id ?? '') ?>">
                                <small class="form-text text-muted">Your merchant/ business ID</small>
                            </div>
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" 
                                           id="test_mode_<?= $gateway->id ?>" 
                                           name="test_mode" <?= $gateway->test_mode ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="test_mode_<?= $gateway->id ?>">
                                        Test Mode
                                    </label>
                                </div>
                                <small class="form-text text-muted">Use sandbox/test environment</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="currency_<?= $gateway->id ?>">Default Currency</label>
                                <select class="form-control" id="currency_<?= $gateway->id ?>" name="additional_config[currency]">
                                    <?php
                                    $currencies = ['USD', 'EUR', 'GBP', 'NGN', 'CAD', 'AUD'];
                                    $currentCurrency = json_decode($gateway->additional_config, true)['currency'] ?? 'USD';
                                    foreach ($currencies as $currency): ?>
                                    <option value="<?= $currency ?>" <?= $currency === $currentCurrency ? 'selected' : '' ?>>
                                        <?= $currency ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="additional_config_<?= $gateway->id ?>">Additional Configuration (JSON)</label>
                        <textarea class="form-control" 
                                  id="additional_config_<?= $gateway->id ?>" 
                                  name="additional_config" 
                                  rows="3"><?= htmlspecialchars(json_encode($gateway->additional_config ?? [], JSON_PRETTY_PRINT)) ?></textarea>
                        <small class="form-text text-muted">Additional gateway-specific configuration</small>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save <?= htmlspecialchars($gateway->display_name) ?> Settings
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="testPaymentGateway(<?= $gateway->id ?>)">
                            <i class="fas fa-vial"></i> Test Gateway
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Payment Settings -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Global Payment Settings</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="/admin/settings/update-general">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="default_payment_gateway">Default Payment Gateway</label>
                        <select class="form-control" id="default_payment_gateway" name="default_payment_gateway">
                            <?php foreach ($payment_gateways as $gateway): ?>
                            <option value="<?= $gateway->name ?>" <?= $gateway->name === $settings->get('default_payment_gateway', 'paystack') ? 'selected' : '' ?>>
                                <?= htmlspecialchars($gateway->display_name) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">Primary payment gateway for transactions</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="currency">Default Currency</label>
                        <select class="form-control" id="currency" name="currency">
                            <?php
                            $currencies = ['USD' => 'US Dollar ($)', 'EUR' => 'Euro (€)', 'GBP' => 'British Pound (£)', 
                                         'NGN' => 'Nigerian Naira (₦)', 'CAD' => 'Canadian Dollar (C$)', 'AUD' => 'Australian Dollar (A$)'];
                            $currentCurrency = $settings->get('currency', 'USD');
                            foreach ($currencies as $code => $name): ?>
                            <option value="<?= $code ?>" <?= $code === $currentCurrency ? 'selected' : '' ?>>
                                <?= $name ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">Default currency for payments</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="currency_symbol">Currency Symbol</label>
                        <input type="text" class="form-control" id="currency_symbol" name="currency_symbol" 
                               value="<?= htmlspecialchars($settings->get('currency_symbol', '$')) ?>" maxlength="3">
                        <small class="form-text text-muted">Symbol to display with amounts (e.g., $, €, ₦)</small>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="min_donation_amount">Minimum Donation Amount</label>
                        <input type="number" class="form-control" id="min_donation_amount" name="min_donation_amount" 
                               value="<?= $settings->get('min_donation_amount', '1') ?>" min="0.01" step="0.01">
                        <small class="form-text text-muted">Minimum amount users can donate</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="max_donation_amount">Maximum Donation Amount</label>
                        <input type="number" class="form-control" id="max_donation_amount" name="max_donation_amount" 
                               value="<?= $settings->get('max_donation_amount', '1000') ?>" min="0.01" step="0.01">
                        <small class="form-text text-muted">Maximum amount users can donate (0 for unlimited)</small>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="enable_donations" 
                                   name="enable_donations" <?= $settings->get('enable_donations', true) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="enable_donations">
                                Enable Donations
                            </label>
                        </div>
                        <small class="form-text text-muted">Allow users to make donations</small>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label for="donation_message">Donation Message</label>
                        <textarea class="form-control" id="donation_message" name="donation_message" 
                                  rows="3"><?= htmlspecialchars($settings->get('donation_message', 'Support our ministry with your donation.')) ?></textarea>
                        <small class="form-text text-muted">Message displayed on donation page</small>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Payment Settings
            </button>
        </form>
    </div>
</div>

<!-- Transaction Settings -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Transaction Settings</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="/admin/settings/update-general">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="auto_approve_donations" 
                                   name="auto_approve_donations" <?= $settings->get('auto_approve_donations', true) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="auto_approve_donations">
                                Auto-Approve Donations
                            </label>
                        </div>
                        <small class="form-text text-muted">Automatically approve successful donations</small>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="send_donation_receipts" 
                                   name="send_donation_receipts" <?= $settings->get('send_donation_receipts', true) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="send_donation_receipts">
                                Send Donation Receipts
                            </label>
                        </div>
                        <small class="form-text text-muted">Send email receipts for donations</small>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="webhook_url">Webhook URL</label>
                        <input type="url" class="form-control" id="webhook_url" 
                               value="<?= htmlspecialchars($this->getWebhookUrl()) ?>" readonly>
                        <small class="form-text text-muted">Use this URL for payment gateway webhooks</small>
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="copyWebhookUrl()">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Transaction Settings
            </button>
        </form>
    </div>
</div>

<script>
function togglePaymentGateway(gatewayId) {
    const checkbox = document.getElementById('gateway_' + gatewayId + '_enabled');
    const isEnabled = checkbox.checked;
    
    // Update the hidden field in the form
    const form = document.querySelector(`form[data-gateway-id="${gatewayId}"]`);
    if (form) {
        let enabledInput = form.querySelector('input[name="is_enabled"]');
        if (!enabledInput) {
            enabledInput = document.createElement('input');
            enabledInput.type = 'hidden';
            enabledInput.name = 'is_enabled';
            form.appendChild(enabledInput);
        }
        enabledInput.value = isEnabled ? '1' : '0';
        
        // Auto-submit the form
        if (confirm(`Are you sure you want to ${isEnabled ? 'enable' : 'disable'} this payment gateway?`)) {
            form.submit();
        } else {
            checkbox.checked = !isEnabled;
        }
    }
}

function copyWebhookUrl() {
    const webhookUrl = document.getElementById('webhook_url');
    webhookUrl.select();
    document.execCommand('copy');
    
    // Show feedback
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i> Copied!';
    button.classList.remove('btn-outline-secondary');
    button.classList.add('btn-success');
    
    setTimeout(() => {
        button.innerHTML = originalText;
        button.classList.remove('btn-success');
        button.classList.add('btn-outline-secondary');
    }, 2000);
}
</script>