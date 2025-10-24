<?php
/**
 * QuizYourFaith Admin Activation Code Generator
 * Use this page to generate new activation codes
 * PROTECT THIS FILE - Delete after generating codes or add authentication
 */

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/activation.php';

// Simple password protection - CHANGE THIS PASSWORD!
$adminPassword = 'Admin@2024!'; // Change this to a secure password

$message = '';
$error = '';
$generatedCode = '';

// Check if admin is accessing (simple protection)
if ($_POST['admin_login'] ?? false) {
    if ($_POST['password'] === $adminPassword) {
        $_SESSION['admin_authenticated'] = true;
    } else {
        $error = 'Invalid password';
    }
}

// Handle code generation
if (($_POST['generate'] ?? false) && ($_SESSION['admin_authenticated'] ?? false)) {
    $type = $_POST['type'] ?? 'standard';
    $expires = $_POST['expires'] ?? '+1 year';
    
    $generatedCode = ActivationSystem::generateCode($type, $expires);
    $message = "New activation code generated: <strong>$generatedCode</strong>";
}

// Handle activation status check
if (($_POST['check_status'] ?? false) && ($_SESSION['admin_authenticated'] ?? false)) {
    $code = trim($_POST['check_code'] ?? '');
    if (!empty($code)) {
        if (isset(ActivationSystem::$activationCodes[$code])) {
            $codeData = ActivationSystem::$activationCodes[$code];
            $isExpired = strtotime($codeData['expires']) < time();
            $message = "Code Status: " . ($isExpired ? 'EXPIRED' : 'VALID') . 
                      "<br>Type: " . $codeData['type'] . 
                      "<br>Expires: " . $codeData['expires'];
        } else {
            $error = 'Code not found in system';
        }
    }
}

// Logout admin
if ($_POST['logout'] ?? false) {
    unset($_SESSION['admin_authenticated']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QuizYourFaith - Admin Activation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .admin-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 600px;
            width: 90%;
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo h1 {
            color: #667eea;
            font-weight: bold;
        }
        .generated-code {
            background: #f8f9fa;
            border: 2px dashed #667eea;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 1.2em;
            text-align: center;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="logo">
            <h1>QuizYourFaith</h1>
            <p class="text-muted">Admin Activation Panel</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <?php if (!($_SESSION['admin_authenticated'] ?? false)): ?>
            <!-- Admin Login Form -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Admin Login</h5>
                    <form method="post">
                        <div class="mb-3">
                            <label for="password" class="form-label">Admin Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" name="admin_login" class="btn btn-primary w-100">Login</button>
                    </form>
                </div>
            </div>

            <div class="alert alert-warning">
                <strong>Security Notice:</strong> This page should be protected or deleted after use. 
                Change the default password in the source code.
            </div>

        <?php else: ?>
            <!-- Admin Panel -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5>Admin Panel</h5>
                <form method="post" class="d-inline">
                    <button type="submit" name="logout" class="btn btn-outline-danger btn-sm">Logout</button>
                </form>
            </div>

            <!-- Generate New Code -->
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="card-title">Generate New Activation Code</h6>
                    <form method="post">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="type" class="form-label">License Type</label>
                                <select class="form-select" id="type" name="type">
                                    <option value="standard">Standard</option>
                                    <option value="premium">Premium</option>
                                    <option value="trial">Trial</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="expires" class="form-label">Expires</label>
                                <select class="form-select" id="expires" name="expires">
                                    <option value="+1 month">1 Month</option>
                                    <option value="+3 months">3 Months</option>
                                    <option value="+6 months">6 Months</option>
                                    <option value="+1 year" selected>1 Year</option>
                                    <option value="+2 years">2 Years</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" name="generate" class="btn btn-success w-100 mt-3">Generate Code</button>
                    </form>
                    
                    <?php if ($generatedCode): ?>
                        <div class="generated-code">
                            <strong><?= $generatedCode ?></strong>
                        </div>
                        <div class="alert alert-info">
                            Save this code! Add it to the <code>$activationCodes</code> array in <code>config/activation.php</code>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Check Code Status -->
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="card-title">Check Code Status</h6>
                    <form method="post">
                        <div class="mb-3">
                            <label for="check_code" class="form-label">Activation Code</label>
                            <input type="text" class="form-control" id="check_code" name="check_code" 
                                   placeholder="Enter code to check" required>
                        </div>
                        <button type="submit" name="check_status" class="btn btn-info w-100">Check Status</button>
                    </form>
                </div>
            </div>

            <!-- Current Activation Status -->
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="card-title">System Status</h6>
                    <?php
                    $status = ActivationSystem::getStatus();
                    if ($status['activated']): 
                    ?>
                        <div class="alert alert-success">
                            <strong>System is ACTIVATED</strong><br>
                            Code: <?= $status['code'] ?><br>
                            Type: <?= $status['type'] ?><br>
                            Expires: <?= $status['expires'] ?><br>
                            Days remaining: <?= $status['days_remaining'] ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <strong>System is NOT ACTIVATED</strong><br>
                            Users will be redirected to activation page.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="alert alert-danger">
                <strong>Security Reminder:</strong> Delete this file after generating your activation codes, 
                or move it to a secure admin-only location.
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>