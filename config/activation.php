<?php
/**
 * QuizYourFaith Activation System
 * Prevents unauthorized use without activation code
 */

class ActivationSystem {
    private static $activationFile = BASE_PATH . '/storage/.activated';
    private static $activationCodes = [
        // Domain-locked activation codes - no expiration, locked to domain
        'QYF-2024-PREMIUM-001' => ['domain' => 'example1.com', 'type' => 'premium'],
        'QYF-2024-PREMIUM-002' => ['domain' => 'example2.com', 'type' => 'premium'],
        'QYF-2024-PREMIUM-003' => ['domain' => 'example3.com', 'type' => 'premium'],
        'QYF-2024-PREMIUM-004' => ['domain' => 'example4.com', 'type' => 'premium'],
        'QYF-2024-PREMIUM-005' => ['domain' => 'example5.com', 'type' => 'premium'],
        'QYF-2024-STANDARD-006' => ['domain' => 'example6.com', 'type' => 'standard'],
        'QYF-2024-STANDARD-007' => ['domain' => 'example7.com', 'type' => 'standard'],
        'QYF-2024-STANDARD-008' => ['domain' => 'example8.com', 'type' => 'standard'],
        'QYF-2024-STANDARD-009' => ['domain' => 'example9.com', 'type' => 'standard'],
        'QYF-2024-STANDARD-010' => ['domain' => 'example10.com', 'type' => 'standard'],
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
        
        // Check domain lock (permanent activation for specific domain)
        if (isset($activationData['domain'])) {
            $currentDomain = $_SERVER['HTTP_HOST'] ?? 'localhost';
            if ($currentDomain !== $activationData['domain']) {
                // Domain mismatch - activation is invalid for this domain
                return false;
            }
            // Domain matches - activation is valid (no expiration for domain-locked codes)
            return true;
        }
        
        // Check if activation has expired (for legacy codes with expiration)
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
        
        // Check if code is domain-locked (no expiration, locked to specific domain)
        if (isset($codeData['domain'])) {
            $currentDomain = $_SERVER['HTTP_HOST'] ?? 'localhost';
            if ($currentDomain !== $codeData['domain']) {
                return ['success' => false, 'message' => 'This activation code is locked to domain: ' . $codeData['domain']];
            }
            // Domain matches, proceed with activation (no expiration check)
        } elseif (isset($codeData['expires'])) {
            // Legacy expiration-based codes (if any)
            if (strtotime($codeData['expires']) < time()) {
                return ['success' => false, 'message' => 'Activation code has expired'];
            }
        }
        
        // Create activation file
        $activationData = [
            'code' => $code,
            'activated_at' => date('Y-m-d H:i:s'),
            'type' => $codeData['type'],
            'domain' => $_SERVER['HTTP_HOST'] ?? 'localhost'
        ];
        
        // Only add expires if it exists (for legacy codes)
        if (isset($codeData['expires'])) {
            $activationData['expires'] = $codeData['expires'];
        }
        
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