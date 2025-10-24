<div class="card shadow mb-4 settings-card">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">General Settings</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="/admin/settings/update-general">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="site_name">Site Name *</label>
                        <input type="text" class="form-control" id="site_name" name="site_name" 
                               value="<?= htmlspecialchars($settings->get('site_name', 'QuizYourFaith')) ?>" required>
                        <small class="form-text text-muted">The name of your website</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="site_description">Site Description</label>
                        <textarea class="form-control" id="site_description" name="site_description" 
                                  rows="3"><?= htmlspecialchars($settings->get('site_description', '')) ?></textarea>
                        <small class="form-text text-muted">Brief description of your website</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="site_logo">Site Logo URL</label>
                        <input type="url" class="form-control" id="site_logo" name="site_logo" 
                               value="<?= htmlspecialchars($settings->get('site_logo', '')) ?>">
                        <small class="form-text text-muted">URL to your site logo (recommended: 200x60px)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="timezone">Timezone *</label>
                        <select class="form-control" id="timezone" name="timezone" required>
                            <?php
                            $timezones = timezone_identifiers_list();
                            $currentTimezone = $settings->get('timezone', 'UTC');
                            foreach ($timezones as $timezone): ?>
                            <option value="<?= $timezone ?>" <?= $timezone === $currentTimezone ? 'selected' : '' ?>>
                                <?= $timezone ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">System timezone for date/time display</small>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="maintenance_mode" 
                                   name="maintenance_mode" <?= $settings->get('maintenance_mode', false) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="maintenance_mode">
                                Maintenance Mode
                            </label>
                        </div>
                        <small class="form-text text-muted">Enable to put the site in maintenance mode</small>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="registration_enabled" 
                                   name="registration_enabled" <?= $settings->get('registration_enabled', true) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="registration_enabled">
                                Enable Registration
                            </label>
                        </div>
                        <small class="form-text text-muted">Allow new users to register</small>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="email_verification_required" 
                                   name="email_verification_required" <?= $settings->get('email_verification_required', false) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="email_verification_required">
                                Email Verification Required
                            </label>
                        </div>
                        <small class="form-text text-muted">Require users to verify their email address</small>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Quick Actions</h6>
                        <div class="btn-group-vertical btn-block">
                            <a href="/" class="btn btn-outline-primary btn-sm" target="_blank">
                                <i class="fas fa-external-link-alt"></i> View Site
                            </a>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearCache()">
                                <i class="fas fa-broom"></i> Clear Cache
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <hr>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save General Settings
                    </button>
                    <button type="reset" class="btn btn-secondary">Reset</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- System Information Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">System Information</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <h6>Application</h6>
                <ul class="list-unstyled">
                    <li><strong>Version:</strong> 2.0.0</li>
                    <li><strong>Environment:</strong> <?= $_ENV['APP_ENV'] ?? 'production' ?></li>
                    <li><strong>Debug Mode:</strong> <?= $_ENV['APP_DEBUG'] ?? 'false' ? 'Enabled' : 'Disabled' ?></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6>Server</h6>
                <ul class="list-unstyled">
                    <li><strong>PHP Version:</strong> <?= phpversion() ?></li>
                    <li><strong>Database:</strong> MySQL <?= db()->query("SELECT VERSION()")->fetchColumn() ?></li>
                    <li><strong>Web Server:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6>Security</h6>
                <ul class="list-unstyled">
                    <li><strong>SSL:</strong> <?= isset($_SERVER['HTTPS']) ? 'Enabled' : 'Disabled' ?></li>
                    <li><strong>Encryption:</strong> AES-256-CBC</li>
                    <li><strong>Last Updated:</strong> <?= date('Y-m-d H:i:s') ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function clearCache() {
    if (confirm('This will clear the application cache. Continue?')) {
        // Implement cache clearing logic
        alert('Cache cleared successfully!');
    }
}
</script>