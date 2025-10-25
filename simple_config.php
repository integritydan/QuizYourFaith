<?php
/**
 * Simple Configuration for Webuzo Deployment
 * Just update these basic settings and upload!
 */

// Database Configuration - Update these with your Webuzo database details
define('DB_HOST', 'localhost');        // Usually localhost for Webuzo
define('DB_NAME', 'quizyourfaith');    // Your database name
define('DB_USER', 'root');             // Your database username
define('DB_PASS', '');                 // Your database password
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_URL', 'https://yourdomain.com');  // Your domain URL
define('SITE_NAME', 'Quiz Your Faith');
define('SITE_EMAIL', 'admin@yourdomain.com');

// Security Keys - Generate random strings for these
define('SECRET_KEY', 'your-secret-key-here');
define('JWT_SECRET', 'your-jwt-secret-here');

// Feature Toggles - Set to true/false as needed
define('ENABLE_VIDEOS', true);
define('ENABLE_MULTIPLAYER', true);
define('ENABLE_DONATIONS', false);  // Set to false for simple setup
define('ENABLE_ACTIVATION', false); // Set to false for simple setup

// Basic Settings
define('DEFAULT_QUIZ_LIMIT', 10);
define('LEADERBOARD_LIMIT', 50);
define('SESSION_TIMEOUT', 3600); // 1 hour

// Error Reporting - Set to false for production
define('DEBUG_MODE', false);
define('SHOW_ERRORS', false);

// Paths - These should work automatically
define('BASE_PATH', dirname(__FILE__));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', BASE_PATH . '/storage');

/**
 * Simple Database Connection
 * No complex ORM - just basic PDO
 */
function getDB() {
    static $db = null;
    if ($db === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $db = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    return $db;
}

/**
 * Simple Session Start
 */
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start([
            'cookie_secure' => true,
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax'
        ]);
    }
}

/**
 * Simple Security Functions
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function generateToken() {
    return bin2hex(random_bytes(32));
}

function verifyToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Initialize session
startSession();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generateToken();
}