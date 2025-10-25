<?php
/**
 * Manual Database Installation for Webuzo
 * Use this when the Webuzo installer fails
 */

// Check if already installed
if (file_exists('installed.lock')) {
    die("Installation already completed. Delete 'installed.lock' to reinstall.");
}

// Basic configuration check
if (!file_exists('simple_config.php')) {
    die("Error: simple_config.php not found. Please upload all files first.");
}

require_once 'simple_config.php';

// Installation page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual Database Installation - Quiz Your Faith</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            color: #e2e8f0;
            padding-top: 50px;
        }
        .install-container {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 2rem;
            margin: 2rem auto;
            max-width: 800px;
        }
        .step-card {
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid #475569;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 8px;
        }
        .progress-bar {
            background: linear-gradient(90deg, #2563eb, #f59e0b);
        }
        .success-card {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
        }
        .error-card {
            background: linear-gradient(135deg, #dc2626, #ef4444);
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="install-container">
            <div class="text-center mb-4">
                <i class="fas fa-database fa-3x text-primary mb-3"></i>
                <h2 class="text-white">Manual Database Installation</h2>
                <p class="text-muted">Use this when Webuzo installer fails</p>
            </div>

            <?php
            $step = isset($_GET['step']) ? intval($_GET['step']) : 1;
            $errors = [];
            $success = false;

            if ($step == 2) {
                // Step 2: Test database connection
                try {
                    $db = getDB();
                    echo '<div class="alert alert-success">';
                    echo '<i class="fas fa-check-circle me-2"></i>Database connection successful!';
                    echo '</div>';
                    echo '<div class="text-center mt-4">';
                    echo '<a href="?step=3" class="btn btn-primary btn-lg">';
                    echo '<i class="fas fa-arrow-right me-2"></i>Continue to Step 3';
                    echo '</a>';
                    echo '</div>';
                } catch (Exception $e) {
                    echo '<div class="alert alert-danger">';
                    echo '<i class="fas fa-exclamation-circle me-2"></i>Database connection failed: ' . htmlspecialchars($e->getMessage());
                    echo '</div>';
                    echo '<div class="step-card">';
                    echo '<h5>Fix Database Connection:</h5>';
                    echo '<ol>';
                    echo '<li>Check your database credentials in <code>simple_config.php</code></li>';
                    echo '<li>Verify database exists in Webuzo panel</li>';
                    echo '<li>Ensure MySQL is running</li>';
                    echo '<li>Check database user permissions</li>';
                    echo '</ol>';
                    echo '</div>';
                }
            } elseif ($step == 3) {
                // Step 3: Install tables
                try {
                    $db = getDB();
                    
                    // Read and execute the SQL file
                    $sql_file = 'simple_install.sql';
                    if (!file_exists($sql_file)) {
                        throw new Exception("SQL file not found: $sql_file");
                    }
                    
                    $sql_content = file_get_contents($sql_file);
                    $statements = array_filter(array_map('trim', explode(';', $sql_content)));
                    
                    $success_count = 0;
                    $error_count = 0;
                    
                    echo '<div class="mb-4">';
                    echo '<h5>Installing Database Tables...</h5>';
                    echo '<div class="progress mb-3">';
                    echo '<div class="progress-bar" role="progressbar" style="width: 0%"></div>';
                    echo '</div>';
                    echo '</div>';
                    
                    $total = count($statements);
                    $current = 0;
                    
                    foreach ($statements as $sql) {
                        if (empty($sql)) continue;
                        
                        $current++;
                        $progress = ($current / $total) * 100;
                        
                        try {
                            $db->exec($sql);
                            $success_count++;
                            echo '<div class="alert alert-success alert-sm">';
                            echo '<i class="fas fa-check me-2"></i>✓ ' . substr($sql, 0, 50) . '...';
                            echo '</div>';
                        } catch (Exception $e) {
                            $error_count++;
                            echo '<div class="alert alert-warning alert-sm">';
                            echo '<i class="fas fa-exclamation-triangle me-2"></i>⚠ ' . substr($sql, 0, 50) . '...';
                            echo '<br><small>' . htmlspecialchars($e->getMessage()) . '</small>';
                            echo '</div>';
                        }
                        
                        // Update progress bar
                        echo '<script>document.querySelector(".progress-bar").style.width = "' . $progress . '%";</script>';
                        flush();
                    }
                    
                    if ($success_count > 0) {
                        echo '<div class="success-card mt-4">';
                        echo '<i class="fas fa-check-circle fa-2x mb-3"></i>';
                        echo '<h4>Installation Complete!</h4>';
                        echo '<p>Successfully installed ' . $success_count . ' database objects.</p>';
                        echo '<a href="/" class="btn btn-light btn-lg mt-3">';
                        echo '<i class="fas fa-home me-2"></i>Go to Homepage';
                        echo '</a>';
                        echo '</div>';
                        
                        // Create installation lock file
                        file_put_contents('installed.lock', date('Y-m-d H:i:s'));
                    } else {
                        echo '<div class="error-card mt-4">';
                        echo '<i class="fas fa-times-circle fa-2x mb-3"></i>';
                        echo '<h4>Installation Failed</h4>';
                        echo '<p>No database objects were installed successfully.</p>';
                        echo '<a href="?step=1" class="btn btn-light btn-lg mt-3">';
                        echo '<i class="fas fa-redo me-2"></i>Try Again';
                        echo '</a>';
                        echo '</div>';
                    }
                    
                } catch (Exception $e) {
                    echo '<div class="error-card mt-4">';
                    echo '<i class="fas fa-times-circle fa-2x mb-3"></i>';
                    echo '<h4>Installation Error</h4>';
                    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
                    echo '<a href="?step=1" class="btn btn-light btn-lg mt-3">';
                    echo '<i class="fas fa-redo me-2"></i>Try Again';
                    echo '</a>';
                    echo '</div>';
                }
            } else {
                // Step 1: Show instructions
                ?>
                <div class="step-card">
                    <h5><i class="fas fa-info-circle me-2"></i>Step 1: Prerequisites</h5>
                    <p>Before proceeding, ensure you have:</p>
                    <ul>
                        <li>Uploaded all files to your Webuzo public_html directory</li>
                        <li>Created a database in Webuzo panel</li>
                        <li>Updated database credentials in <code>simple_config.php</code></li>
                    </ul>
                </div>
                
                <div class="step-card">
                    <h5><i class="fas fa-cog me-2"></i>Step 2: Configuration Check</h5>
                    <p>Current configuration:</p>
                    <ul>
                        <li>Database Host: <code><?php echo DB_HOST; ?></code></li>
                        <li>Database Name: <code><?php echo DB_NAME; ?></code></li>
                        <li>Database User: <code><?php echo DB_USER; ?></code></li>
                    </ul>
                </div>
                
                <div class="text-center mt-4">
                    <a href="?step=2" class="btn btn-primary btn-lg">
                        <i class="fas fa-play me-2"></i>Start Installation
                    </a>
                </div>
                <?php
            }
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>