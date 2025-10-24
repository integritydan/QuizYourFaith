<?php include __DIR__ . '/../../layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-none d-md-block bg-light sidebar">
            <div class="sidebar-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= $active_tab === 'general' ? 'active' : '' ?>" href="/admin/settings?tab=general">
                            <i class="fas fa-cog"></i> General Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $active_tab === 'authentication' ? 'active' : '' ?>" href="/admin/settings?tab=authentication">
                            <i class="fas fa-key"></i> Authentication
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $active_tab === 'payment' ? 'active' : '' ?>" href="/admin/settings?tab=payment">
                            <i class="fas fa-credit-card"></i> Payment Gateways
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $active_tab === 'email' ? 'active' : '' ?>" href="/admin/settings?tab=email">
                            <i class="fas fa-envelope"></i> Email Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $active_tab === 'social' ? 'active' : '' ?>" href="/admin/settings?tab=social">
                            <i class="fas fa-share-alt"></i> Social Media
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $active_tab === 'security' ? 'active' : '' ?>" href="/admin/settings?tab=security">
                            <i class="fas fa-shield-alt"></i> Security
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $active_tab === 'multiplayer' ? 'active' : '' ?>" href="/admin/settings?tab=multiplayer">
                            <i class="fas fa-gamepad"></i> Multiplayer
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $active_tab === 'api' ? 'active' : '' ?>" href="/admin/settings?tab=api">
                            <i class="fas fa-plug"></i> API Keys
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/settings/history">
                            <i class="fas fa-history"></i> Settings History
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">System Settings</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group mr-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportSettings()">
                            <i class="fas fa-download"></i> Export
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#importModal">
                            <i class="fas fa-upload"></i> Import
                        </button>
                    </div>
                </div>
            </div>

            <!-- Alert Messages -->
            <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Tab Content -->
            <?php if ($active_tab === 'general'): ?>
                <?php include 'tabs/general.php'; ?>
            <?php elseif ($active_tab === 'authentication'): ?>
                <?php include 'tabs/authentication.php'; ?>
            <?php elseif ($active_tab === 'payment'): ?>
                <?php include 'tabs/payment.php'; ?>
            <?php elseif ($active_tab === 'email'): ?>
                <?php include 'tabs/email.php'; ?>
            <?php elseif ($active_tab === 'social'): ?>
                <?php include 'tabs/social.php'; ?>
            <?php elseif ($active_tab === 'security'): ?>
                <?php include 'tabs/security.php'; ?>
            <?php elseif ($active_tab === 'multiplayer'): ?>
                <?php include 'tabs/multiplayer.php'; ?>
            <?php elseif ($active_tab === 'api'): ?>
                <?php include 'tabs/api.php'; ?>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Settings</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST" action="/admin/settings/import" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <div class="form-group">
                        <label>Select Settings File:</label>
                        <input type="file" class="form-control" name="settings_file" accept=".json" required>
                        <small class="form-text text-muted">
                            Only JSON files are accepted. Sensitive settings will be skipped during import.
                        </small>
                    </div>
                    <div class="alert alert-warning">
                        <strong>Warning:</strong> This will overwrite existing settings. Make sure you have a backup.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function exportSettings() {
    window.location.href = '/admin/settings/export';
}

function testOAuthConnection(providerId) {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';
    button.disabled = true;
    
    fetch('/admin/settings/test-oauth', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            provider_id: providerId,
            csrf_token: '<?= $_SESSION['csrf_token'] ?? '' ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
        } else {
            alert('❌ ' + data.error);
        }
    })
    .catch(error => {
        alert('❌ Test failed: ' + error.message);
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function testPaymentGateway(gatewayId) {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';
    button.disabled = true;
    
    fetch('/admin/settings/test-payment-gateway', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            gateway_id: gatewayId,
            csrf_token: '<?= $_SESSION['csrf_token'] ?? '' ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
        } else {
            alert('❌ ' + data.error);
        }
    })
    .catch(error => {
        alert('❌ Test failed: ' + error.message);
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function testEmailProvider(providerId) {
    const testEmail = prompt('Enter email address to send test to:');
    if (!testEmail || !validateEmail(testEmail)) {
        alert('Please enter a valid email address');
        return;
    }
    
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';
    button.disabled = true;
    
    fetch('/admin/settings/test-email', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            provider_id: providerId,
            test_email: testEmail,
            csrf_token: '<?= $_SESSION['csrf_token'] ?? '' ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
        } else {
            alert('❌ ' + data.error);
        }
    })
    .catch(error => {
        alert('❌ Test failed: ' + error.message);
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function generateAPIKey() {
    const name = prompt('Enter API key name:');
    if (!name) return;
    
    const service = prompt('Enter service name:');
    if (!service) return;
    
    const expiresIn = prompt('Enter expiration in days (leave empty for no expiration):');
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/admin/settings/generate-api-key';
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    csrfInput.value = '<?= $_SESSION['csrf_token'] ?? '' ?>';
    form.appendChild(csrfInput);
    
    const nameInput = document.createElement('input');
    nameInput.type = 'hidden';
    nameInput.name = 'name';
    nameInput.value = name;
    form.appendChild(nameInput);
    
    const serviceInput = document.createElement('input');
    serviceInput.type = 'hidden';
    serviceInput.name = 'service';
    serviceInput.value = service;
    form.appendChild(serviceInput);
    
    if (expiresIn) {
        const expiresInput = document.createElement('input');
        expiresInput.type = 'hidden';
        expiresInput.name = 'expires_in';
        expiresInput.value = expiresIn;
        form.appendChild(expiresInput);
    }
    
    document.body.appendChild(form);
    form.submit();
}

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Show/hide sensitive fields
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toggle-secret').forEach(button => {
        button.addEventListener('click', function() {
            const target = document.querySelector(this.getAttribute('data-target'));
            if (target.type === 'password') {
                target.type = 'text';
                this.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                target.type = 'password';
                this.innerHTML = '<i class="fas fa-eye"></i>';
            }
        });
    });
});
</script>

<style>
.settings-card {
    border-left: 4px solid #007bff;
}

.settings-card.security {
    border-left-color: #dc3545;
}

.settings-card.payment {
    border-left-color: #28a745;
}

.settings-card.email {
    border-left-color: #ffc107;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.table-sm td {
    padding: 0.5rem;
}

.secret-field {
    font-family: 'Courier New', monospace;
}

.toggle-secret {
    cursor: pointer;
}
</style>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>