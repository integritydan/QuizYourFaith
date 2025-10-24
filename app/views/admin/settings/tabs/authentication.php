<div class="card shadow mb-4 settings-card">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">OAuth Providers</h6>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <h6><i class="fas fa-info-circle"></i> OAuth Configuration Guide</h6>
            <p>Configure OAuth providers to allow users to login with their existing accounts.</p>
            <small>
                <strong>Google:</strong> <a href="https://console.cloud.google.com/apis/credentials" target="_blank">Google Cloud Console</a><br>
                <strong>Facebook:</strong> <a href="https://developers.facebook.com/apps/" target="_blank">Facebook Developers</a><br>
                <strong>Twitter:</strong> <a href="https://developer.twitter.com/en/apps" target="_blank">Twitter Developer Portal</a>
            </small>
        </div>

        <?php foreach ($oauth_providers as $provider): ?>
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fab fa-<?= strtolower($provider->name) ?>"></i> 
                    <?= htmlspecialchars($provider->display_name) ?>
                </h6>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" 
                           id="oauth_<?= $provider->id ?>_enabled" 
                           <?= $provider->is_enabled ? 'checked' : '' ?>
                           onchange="toggleOAuthProvider(<?= $provider->id ?>)">
                    <label class="custom-control-label" for="oauth_<?= $provider->id ?>_enabled">
                        <?= $provider->is_enabled ? 'Enabled' : 'Disabled' ?>
                    </label>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/settings/update-oauth" class="oauth-form" data-provider-id="<?= $provider->id ?>">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="provider_id" value="<?= $provider->id ?>">
                    <input type="hidden" name="name" value="<?= $provider->name ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="client_id_<?= $provider->id ?>">Client ID *</label>
                                <input type="text" class="form-control" 
                                       id="client_id_<?= $provider->id ?>" 
                                       name="client_id" 
                                       value="<?= htmlspecialchars($provider->client_id ?? '') ?>" 
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="client_secret_<?= $provider->id ?>">Client Secret *</label>
                                <div class="input-group">
                                    <input type="password" class="form-control secret-field" 
                                           id="client_secret_<?= $provider->id ?>" 
                                           name="client_secret" 
                                           placeholder="Enter new client secret to update">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary toggle-secret" 
                                                data-target="#client_secret_<?= $provider->id ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <?php if (!empty($provider->client_secret)): ?>
                                <small class="form-text text-muted">Leave empty to keep current secret</small>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="redirect_uri_<?= $provider->id ?>">Redirect URI</label>
                                <input type="url" class="form-control" 
                                       id="redirect_uri_<?= $provider->id ?>" 
                                       name="redirect_uri" 
                                       value="<?= htmlspecialchars($provider->redirect_uri ?? $this->getOAuthRedirectUri($provider->name)) ?>" 
                                       readonly>
                                <small class="form-text text-muted">Use this URI in your OAuth app configuration</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="scopes_<?= $provider->id ?>">Scopes</label>
                                <input type="text" class="form-control" 
                                       id="scopes_<?= $provider->id ?>" 
                                       name="scopes" 
                                       value="<?= htmlspecialchars($provider->scopes ?? $this->getDefaultScopes($provider->name)) ?>">
                                <small class="form-text text-muted">Space-separated list of OAuth scopes</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="additional_params_<?= $provider->id ?>">Additional Parameters (JSON)</label>
                        <textarea class="form-control" 
                                  id="additional_params_<?= $provider->id ?>" 
                                  name="additional_params" 
                                  rows="3"><?= htmlspecialchars(json_encode($provider->additional_params ?? [], JSON_PRETTY_PRINT)) ?></textarea>
                        <small class="form-text text-muted">Additional OAuth parameters in JSON format</small>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save <?= htmlspecialchars($provider->display_name) ?> Settings
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="testOAuthConnection(<?= $provider->id ?>)">
                            <i class="fas fa-vial"></i> Test Connection
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Registration Settings -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Registration Settings</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="/admin/settings/update-general">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="registration_enabled" 
                                   name="registration_enabled" <?= $settings->get('registration_enabled', true) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="registration_enabled">
                                Allow New Registrations
                            </label>
                        </div>
                        <small class="form-text text-muted">Enable/disable new user registration</small>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="email_verification_required" 
                                   name="email_verification_required" <?= $settings->get('email_verification_required', false) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="email_verification_required">
                                Require Email Verification
                            </label>
                        </div>
                        <small class="form-text text-muted">Users must verify their email before accessing the site</small>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="default_user_role">Default User Role</label>
                        <select class="form-control" id="default_user_role" name="default_user_role">
                            <option value="user" selected>User</option>
                            <option value="admin">Admin</option>
                        </select>
                        <small class="form-text text-muted">Default role for new registrations</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="registration_redirect">Post-Registration Redirect</label>
                        <input type="text" class="form-control" id="registration_redirect" 
                               name="registration_redirect" 
                               value="<?= htmlspecialchars($settings->get('registration_redirect', '/dashboard')) ?>">
                        <small class="form-text text-muted">Where users are redirected after registration</small>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Registration Settings
            </button>
        </form>
    </div>
</div>

<!-- Session Settings -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Session & Security Settings</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="/admin/settings/update-security">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="session_timeout_minutes">Session Timeout (minutes)</label>
                        <input type="number" class="form-control" id="session_timeout_minutes" 
                               name="session_timeout_minutes" 
                               value="<?= $settings->get('session_timeout_minutes', 60) ?>" 
                               min="15" max="1440" required>
                        <small class="form-text text-muted">How long before users are automatically logged out</small>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="enable_2fa" 
                                   name="enable_2fa" <?= $settings->get('enable_2fa', false) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="enable_2fa">
                                Enable Two-Factor Authentication
                            </label>
                        </div>
                        <small class="form-text text-muted">Require 2FA for admin accounts</small>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="max_login_attempts">Max Login Attempts</label>
                        <input type="number" class="form-control" id="max_login_attempts" 
                               name="max_login_attempts" 
                               value="<?= $settings->get('max_login_attempts', 5) ?>" 
                               min="3" max="10" required>
                        <small class="form-text text-muted">Maximum failed login attempts before lockout</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="lockout_duration_minutes">Lockout Duration (minutes)</label>
                        <input type="number" class="form-control" id="lockout_duration_minutes" 
                               name="lockout_duration_minutes" 
                               value="<?= $settings->get('lockout_duration_minutes', 15) ?>" 
                               min="5" max="60" required>
                        <small class="form-text text-muted">How long to lock account after failed attempts</small>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Security Settings
            </button>
        </form>
    </div>
</div>

<script>
function toggleOAuthProvider(providerId) {
    const checkbox = document.getElementById('oauth_' + providerId + '_enabled');
    const isEnabled = checkbox.checked;
    
    // Update the hidden field in the form
    const form = document.querySelector(`form[data-provider-id="${providerId}"]`);
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
        if (confirm(`Are you sure you want to ${isEnabled ? 'enable' : 'disable'} this OAuth provider?`)) {
            form.submit();
        } else {
            checkbox.checked = !isEnabled;
        }
    }
}
</script>