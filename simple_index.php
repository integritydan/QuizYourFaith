<?php
/**
 * Simple Index for Webuzo Deployment
 * Just upload and visit your domain!
 */

// Include simple configuration
require_once 'simple_config.php';

// Simple routing
$page = $_GET['page'] ?? 'home';

// Basic security
$page = preg_replace('/[^a-zA-Z0-9-_]/', '', $page);

// Simple page routing
switch ($page) {
    case 'home':
        include 'simple_home.php';
        break;
    case 'quiz':
        include 'simple_quiz.php';
        break;
    case 'login':
        include 'simple_login.php';
        break;
    case 'register':
        include 'simple_register.php';
        break;
    default:
        include 'simple_home.php';
        break;
}
?>