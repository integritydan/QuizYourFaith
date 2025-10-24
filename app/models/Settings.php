<?php namespace App\Models;

use App\Middleware\Auth;

class Settings {
    
    private $encryptionKey;
    private $cipher = 'AES-256-CBC';
    
    public function __construct() {
        $this->encryptionKey = $this->getEncryptionKey();
    }
    
    /**
     * Get encryption key from environment or generate one
     */
    private function getEncryptionKey() {
        $key = $_ENV['SETTINGS_ENCRYPTION_KEY'] ?? $_ENV['JWT_SECRET'] ?? 'default-key-change-this';
        return hash('sha256', $key, true);
    }
    
    /**
     * Encrypt sensitive data
     */
    private function encrypt($data) {
        if (empty($data)) return '';
        
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipher));
        $encrypted = openssl_encrypt($data, $this->cipher, $this->encryptionKey, 0, $iv);
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt sensitive data
     */
    private function decrypt($encryptedData) {
        if (empty($encryptedData)) return '';
        
        $data = base64_decode($encryptedData);
        $ivLength = openssl_cipher_iv_length($this->cipher);
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        
        return openssl_decrypt($encrypted, $this->cipher, $this->encryptionKey, 0, $iv);
    }
    
    /**
     * Get setting by key
     */
    public function get($key, $default = null) {
        $st = db()->prepare("
            SELECT value, value_encrypted, is_encrypted, data_type 
            FROM settings_encrypted 
            WHERE key_name = ? 
            LIMIT 1
        ");
        $st->execute([$key]);
        $setting = $st->fetch();
        
        if (!$setting) {
            return $default;
        }
        
        $value = $setting->is_encrypted ? $this->decrypt($setting->value_encrypted) : $setting->value;
        
        // Cast to appropriate type
        switch ($setting->data_type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $value;
            case 'json':
                return json_decode($value, true) ?? $default;
            case 'array':
                return json_decode($value, true) ?? [];
            default:
                return $value;
        }
    }
    
    /**
     * Set setting value
     */
    public function set($key, $value, $userId = null) {
        // Get current value for history
        $oldValue = $this->get($key);
        
        // Determine if value should be encrypted
        $st = db()->prepare("SELECT is_encrypted, data_type FROM settings_encrypted WHERE key_name = ?");
        $st->execute([$key]);
        $setting = $st->fetch();
        
        if (!$setting) {
            return false;
        }
        
        $isEncrypted = $setting->is_encrypted;
        $dataType = $setting->data_type;
        
        // Prepare value based on data type
        $preparedValue = $this->prepareValue($value, $dataType);
        
        // Encrypt if needed
        if ($isEncrypted) {
            $encryptedValue = $this->encrypt($preparedValue);
            $st = db()->prepare("
                UPDATE settings_encrypted 
                SET value_encrypted = ?, value = NULL, updated_at = NOW() 
                WHERE key_name = ?
            ");
            $st->execute([$encryptedValue, $key]);
        } else {
            $st = db()->prepare("
                UPDATE settings_encrypted 
                SET value = ?, value_encrypted = NULL, updated_at = NOW() 
                WHERE key_name = ?
            ");
            $st->execute([$preparedValue, $key]);
        }
        
        // Log the change
        $this->logChange($key, $oldValue, $value, $userId);
        
        return $st->rowCount() > 0;
    }
    
    /**
     * Prepare value based on data type
     */
    private function prepareValue($value, $dataType) {
        switch ($dataType) {
            case 'boolean':
                return $value ? 'true' : 'false';
            case 'json':
            case 'array':
                return json_encode($value);
            case 'integer':
                return (string) $value;
            default:
                return (string) $value;
        }
    }
    
    /**
     * Log setting changes for audit trail
     */
    private function logChange($key, $oldValue, $newValue, $userId) {
        $st = db()->prepare("
            INSERT INTO settings_history (setting_id, user_id, old_value, new_value, ip_address, user_agent, created_at)
            SELECT id, ?, ?, ?, ?, ?, NOW()
            FROM settings_encrypted
            WHERE key_name = ?
        ");
        $st->execute([
            $userId,
            $this->prepareValue($oldValue, 'string'),
            $this->prepareValue($newValue, 'string'),
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            $key
        ]);
    }
    
    /**
     * Get all settings by category
     */
    public function getByCategory($categoryName) {
        $st = db()->prepare("
            SELECT 
                se.*,
                sc.name as category_name
            FROM settings_encrypted se
            JOIN setting_categories sc ON se.category_id = sc.id
            WHERE sc.name = ?
            ORDER BY se.display_order, se.key_name
        ");
        $st->execute([$categoryName]);
        $settings = $st->fetchAll();
        
        // Decrypt values and cast types
        foreach ($settings as &$setting) {
            if ($setting->is_encrypted) {
                $setting->value = $this->decrypt($setting->value_encrypted);
            }
            
            // Cast to appropriate type
            $setting->cast_value = $this->castValue($setting->value, $setting->data_type);
        }
        
        return $settings;
    }
    
    /**
     * Cast value to appropriate type
     */
    private function castValue($value, $dataType) {
        switch ($dataType) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $value;
            case 'json':
            case 'array':
                return json_decode($value, true) ?? [];
            default:
                return $value;
        }
    }
    
    /**
     * Get OAuth provider settings
     */
    public function getOAuthProviders() {
        $st = db()->prepare("
            SELECT * FROM oauth_providers 
            ORDER BY display_order, name
        ");
        $st->execute();
        $providers = $st->fetchAll();
        
        // Decrypt sensitive fields
        foreach ($providers as &$provider) {
            if ($provider->client_secret_encrypted) {
                $provider->client_secret = $this->decrypt($provider->client_secret_encrypted);
            }
        }
        
        return $providers;
    }
    
    /**
     * Update OAuth provider settings
     */
    public function updateOAuthProvider($providerId, $data, $userId = null) {
        $allowedFields = ['client_id', 'client_secret', 'redirect_uri', 'scopes', 'additional_params', 'is_enabled'];
        $setParts = [];
        $params = [];
        
        foreach ($data as $field => $value) {
            if (in_array($field, $allowedFields)) {
                if ($field === 'client_secret' && !empty($value)) {
                    // Encrypt client secret
                    $encrypted = $this->encrypt($value);
                    $setParts[] = "client_secret_encrypted = ?";
                    $params[] = $encrypted;
                    $setParts[] = "client_secret = NULL";
                } else {
                    $setParts[] = "$field = ?";
                    $params[] = $value;
                }
            }
        }
        
        if (empty($setParts)) {
            return false;
        }
        
        $params[] = $providerId;
        $sql = "UPDATE oauth_providers SET " . implode(', ', $setParts) . ", updated_at = NOW() WHERE id = ?";
        $st = db()->prepare($sql);
        
        return $st->execute($params);
    }
    
    /**
     * Get payment gateway settings
     */
    public function getPaymentGateways() {
        $st = db()->prepare("
            SELECT * FROM payment_gateways 
            ORDER BY display_order, name
        ");
        $st->execute();
        $gateways = $st->fetchAll();
        
        // Decrypt sensitive fields
        foreach ($gateways as &$gateway) {
            if ($gateway->secret_key_encrypted) {
                $gateway->secret_key = $this->decrypt($gateway->secret_key_encrypted);
            }
            if ($gateway->webhook_secret_encrypted) {
                $gateway->webhook_secret = $this->decrypt($gateway->webhook_secret_encrypted);
            }
        }
        
        return $gateways;
    }
    
    /**
     * Update payment gateway settings
     */
    public function updatePaymentGateway($gatewayId, $data, $userId = null) {
        $allowedFields = ['public_key', 'secret_key', 'merchant_id', 'webhook_secret', 'test_mode', 'is_enabled', 'additional_config'];
        $setParts = [];
        $params = [];
        
        foreach ($data as $field => $value) {
            if (in_array($field, $allowedFields)) {
                if (($field === 'secret_key' || $field === 'webhook_secret') && !empty($value)) {
                    // Encrypt sensitive fields
                    $encrypted = $this->encrypt($value);
                    $setParts[] = "{$field}_encrypted = ?";
                    $params[] = $encrypted;
                    $setParts[] = "$field = NULL";
                } else {
                    $setParts[] = "$field = ?";
                    $params[] = $value;
                }
            }
        }
        
        if (empty($setParts)) {
            return false;
        }
        
        $params[] = $gatewayId;
        $sql = "UPDATE payment_gateways SET " . implode(', ', $setParts) . ", updated_at = NOW() WHERE id = ?";
        $st = db()->prepare($sql);
        
        return $st->execute($params);
    }
    
    /**
     * Get email provider settings
     */
    public function getEmailProviders() {
        $st = db()->prepare("
            SELECT * FROM email_providers 
            ORDER BY display_order, name
        ");
        $st->execute();
        $providers = $st->fetchAll();
        
        // Decrypt sensitive fields
        foreach ($providers as &$provider) {
            if ($provider->password_encrypted) {
                $provider->password = $this->decrypt($provider->password_encrypted);
            }
            if ($provider->api_key_encrypted) {
                $provider->api_key = $this->decrypt($provider->api_key_encrypted);
            }
        }
        
        return $providers;
    }
    
    /**
     * Get security settings
     */
    public function getSecuritySettings() {
        $st = db()->prepare("
            SELECT * FROM security_settings 
            ORDER BY setting_key
        ");
        $st->execute();
        return $st->fetchAll();
    }
    
    /**
     * Update security setting
     */
    public function updateSecuritySetting($key, $value, $userId = null) {
        $st = db()->prepare("
            UPDATE security_settings 
            SET value = ?, updated_at = NOW() 
            WHERE setting_key = ?
        ");
        return $st->execute([$value, $key]);
    }
    
    /**
     * Get settings change history
     */
    public function getHistory($limit = 50, $offset = 0) {
        $st = db()->prepare("
            SELECT 
                sh.*,
                se.key_name,
                u.name as user_name,
                sc.name as category_name
            FROM settings_history sh
            JOIN settings_encrypted se ON sh.setting_id = se.id
            LEFT JOIN setting_categories sc ON se.category_id = sc.id
            LEFT JOIN users u ON sh.user_id = u.id
            ORDER BY sh.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $st->execute([$limit, $offset]);
        return $st->fetchAll();
    }
    
    /**
     * Validate setting value against rules
     */
    public function validateValue($key, $value) {
        $st = db()->prepare("
            SELECT validation_rules, data_type, options 
            FROM settings_encrypted 
            WHERE key_name = ?
        ");
        $st->execute([$key]);
        $setting = $st->fetch();
        
        if (!$setting || !$setting->validation_rules) {
            return ['valid' => true];
        }
        
        $rules = json_decode($setting->validation_rules, true);
        $errors = [];
        
        // Type validation
        switch ($setting->data_type) {
            case 'integer':
                if (!is_numeric($value)) {
                    $errors[] = 'Value must be a number';
                }
                break;
            case 'boolean':
                if (!in_array($value, ['true', 'false', true, false, 1, 0, '1', '0'])) {
                    $errors[] = 'Value must be true or false';
                }
                break;
        }
        
        // Custom validation rules
        if (isset($rules['min'])) {
            if (strlen($value) < $rules['min']) {
                $errors[] = "Value must be at least {$rules['min']} characters";
            }
        }
        
        if (isset($rules['max'])) {
            if (strlen($value) > $rules['max']) {
                $errors[] = "Value must not exceed {$rules['max']} characters";
            }
        }
        
        if (isset($rules['regex'])) {
            if (!preg_match($rules['regex'], $value)) {
                $errors[] = $rules['regex_message'] ?? 'Value format is invalid';
            }
        }
        
        if (isset($rules['options']) && is_array($rules['options'])) {
            if (!in_array($value, $rules['options'])) {
                $errors[] = 'Value must be one of: ' . implode(', ', $rules['options']);
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Get all categories with their settings
     */
    public function getAllSettings() {
        $categories = [];
        
        // Get all categories
        $st = db()->prepare("SELECT * FROM setting_categories ORDER BY display_order");
        $st->execute();
        $allCategories = $st->fetchAll();
        
        foreach ($allCategories as $category) {
            $settings = $this->getByCategory($category->name);
            $categories[] = [
                'category' => $category,
                'settings' => $settings
            ];
        }
        
        return $categories;
    }
    
    /**
     * Test OAuth provider connection
     */
    public function testOAuthConnection($providerId) {
        $provider = $this->getOAuthProviderById($providerId);
        if (!$provider || !$provider->is_enabled) {
            return ['success' => false, 'error' => 'Provider not found or disabled'];
        }
        
        // Basic validation test
        if (empty($provider->client_id) || empty($provider->client_secret)) {
            return ['success' => false, 'error' => 'Client ID or secret not configured'];
        }
        
        return ['success' => true, 'message' => 'OAuth provider configured correctly'];
    }
    
    /**
     * Test payment gateway connection
     */
    public function testPaymentGateway($gatewayId) {
        $gateway = $this->getPaymentGatewayById($gatewayId);
        if (!$gateway || !$gateway->is_enabled) {
            return ['success' => false, 'error' => 'Gateway not found or disabled'];
        }
        
        // Basic validation test
        if (empty($gateway->public_key) || empty($gateway->secret_key)) {
            return ['success' => false, 'error' => 'API keys not configured'];
        }
        
        return ['success' => true, 'message' => 'Payment gateway configured correctly'];
    }
    
    /**
     * Get OAuth provider by ID
     */
    private function getOAuthProviderById($id) {
        $st = db()->prepare("SELECT * FROM oauth_providers WHERE id = ?");
        $st->execute([$id]);
        return $st->fetch();
    }
    
    /**
     * Get payment gateway by ID
     */
    private function getPaymentGatewayById($id) {
        $st = db()->prepare("SELECT * FROM payment_gateways WHERE id = ?");
        $st->execute([$id]);
        return $st->fetch();
    }
}