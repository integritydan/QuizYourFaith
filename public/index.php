<?php
session_start();
require __DIR__.'/../config/constants.php';

// Check activation before allowing access
require __DIR__.'/../config/activation.php';
if (!ActivationSystem::isActivated()) {
    // Allow access to activation page only
    if (basename($_SERVER['PHP_SELF']) !== 'activate.php' && $_SERVER['REQUEST_URI'] !== '/activate.php') {
        header('Location: /activate.php');
        exit;
    }
}

require __DIR__.'/../config/config.php';
require __DIR__.'/../config/routes.php';
