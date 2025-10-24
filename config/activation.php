<?php
/**
 * QuizYourFaith Activation System
 * Prevents unauthorized use without activation code
 */

class ActivationSystem {
    private static $activationFile = BASE_PATH . '/storage/.activated';
    private static $activationCodes = [
        // You can add multiple valid activation codes here
        'QYF-2024-PREMIUM-001' => ['expires' => '2025-12-31', 'type' => 'premium'],
        'QYF-2024-STANDARD-002' => ['expires' => '2025-06-30', 'type' => 'standard'],
    ];
    
    /**
     * Check if the application is activated
     */
    public static function isActivated() {
        if (!file_exists(self::$activationFile)) {
            return false;
        }
        
        $activationData = json_decode(file_get_contents(self::$activationFile), true);
        if (!$activationData) {
            return false;
        }
        
        // Check if activation has expired
        if (isset($activationData['expires']) && strtotime($activationData['expires']) < time()) {
            self::deactivate();
            return false;
        }
        
        return true;
    }
    
    /**
     * Activate the application with a code
     */
    public static function activate($code) {
        if (!isset(self::$activationCodes[$code])) {
            return ['success' => false, 'message' => 'Invalid activation code'];
        }
        
        $codeData = self::$activationCodes[$code];
        
        // Check if code has expired
        if (strtotime($codeData['expires']) < time()) {
            return ['success' => false, 'message' => 'Activation code has expired'];
        }
        
        // Create activation file
        $activationData = [
            'code' => $code,
            'activated_at' => date('Y-m-d H:i:s'),
            'expires' => $codeData['expires'],
            'type' => $codeData['type'],
            'domain' => $_SERVER['HTTP_HOST'] ?? 'localhost'
        ];
        
        // Ensure storage directory exists
        $storageDir = dirname(self::$activationFile);
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }
        
        file_put_contents(self::$activationFile, json_encode($activationData, JSON_PRETTY_PRINT));
        
        return ['success' => true, 'message' => 'Application activated successfully'];
    }
    
    /**
     * Deactivate the application
     */
    public static function deactivate() {
        if (file_exists(self::$activationFile)) {
            unlink(self::$activationFile);
        }
    }
    
    /**
     * Get activation status and details
     */
    public static function getStatus() {
        if (!self::isActivated()) {
            return ['activated' => false];
        }
        
        $activationData = json_decode(file_get_contents(self::$activationFile), true);
        return [
            'activated' => true,
            'code' => $activationData['code'],
            'activated_at' => $activationData['activated_at'],
            'expires' => $activationData['expires'],
            'type' => $activationData['type'],
            'days_remaining' => ceil((strtotime($activationData['expires']) - time()) / 86400)
        ];
    }
    
    /**
     * Generate a new activation code (for admin use)
     */
    public static function generateCode($type = 'standard', $expires = '+1 year') {
        $code = 'QYF-' . date('Y') . '-' . strtoupper($type) . '-' . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        self::$activationCodes[$code] = [
            'expires' => date('Y-m-d', strtotime($expires)),
            'type' => $type
        ];
        return $code;
    }
}

/**
 * Activation check - Include this at the top of your main entry files
 */
function checkActivation() {
    if (!ActivationSystem::isActivated()) {
        // Redirect to activation page or show activation form
        if (basename($_SERVER['PHP_SELF']) !== 'activate.php') {
            header('Location: /activate.php');
            exit;
        }
    }
}

// Uncomment the line below to enable activation checking on every page load
// checkActivation();