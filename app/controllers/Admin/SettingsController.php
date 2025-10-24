<?php namespace App\Controllers\Admin;
use App\Models\Settings;
use App\Models\User;
use App\Middleware\Auth;

class SettingsController {
    
    private $settings;
    
    function __construct() {
        Auth::superAdminMiddleware();
        $this->settings = new Settings();
    }
    
    /**
     * Main settings dashboard
     */
    function index() {
        $categories = $this->settings->getAllSettings();
        $oauthProviders = $this->settings->getOAuthProviders();
        $paymentGateways = $this->settings->getPaymentGateways();
        $emailProviders = $this->settings->getEmailProviders();
        $securitySettings = $this->settings->getSecuritySettings();
        
        view('admin/settings/dashboard', [
            'categories' => $categories,
            'oauth_providers' => $oauthProviders,
            'payment_gateways' => $paymentGateways,
            'email_providers' => $emailProviders,
            'security_settings' => $securitySettings,
            'active_tab' => $_GET['tab'] ?? 'general'
        ]);
    }
    
    /**
     * Update general settings
     */
    function updateGeneral() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/settings');
            exit;
        }
        
        Auth::validateCSRF();
        
        $userId = $_SESSION['user_id'];
        $updated = [];
        $errors = [];
        
        // General settings
        $generalSettings = [
            'site_name' => $_POST['site_name'] ?? '',
            'site_description' => $_POST['site_description'] ?? '',
            'site_logo' => $_POST['site_logo'] ?? '',
            'maintenance_mode' => isset($_POST['maintenance_mode']) ? 'true' : 'false',
            'timezone' => $_POST['timezone'] ?? 'UTC',
            'registration_enabled' => isset($_POST['registration_enabled']) ? 'true' : 'false',
            'email_verification_required' => isset($_POST['email_verification_required']) ? 'true' : 'false'
        ];
        
        foreach ($generalSettings as $key => $value) {
            $validation = $this->settings->validateValue($key, $value);
            if ($validation['valid']) {
                if ($this->settings->set($key, $value, $userId)) {
                    $updated[] = $key;
                }
            } else {
                $errors[$key] = $validation['errors'];
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['error'] = 'Some settings could not be updated';
        } else {
            $_SESSION['success'] = 'General settings updated successfully';
        }
        
        Auth::logAction('settings_updated', ['category' => 'general', 'updated' => $updated]);
        
        redirect('/admin/settings?tab=general');
    }
    
    /**
     * Update OAuth settings
     */
    function updateOAuth() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/settings');
            exit;
        }
        
        Auth::validateCSRF();
        
        $userId = $_SESSION['user_id'];
        $providerId = $_POST['provider_id'] ?? 0;
        
        if ($providerId <= 0) {
            $_SESSION['error'] = 'Invalid provider';
            redirect('/admin/settings?tab=authentication');
            exit;
        }
        
        $data = [
            'client_id' => $_POST['client_id'] ?? '',
            'client_secret' => $_POST['client_secret'] ?? '',
            'redirect_uri' => $_POST['redirect_uri'] ?? '',
            'scopes' => $_POST['scopes'] ?? '',
            'additional_params' => json_encode($_POST['additional_params'] ?? []),
            'is_enabled' => isset($_POST['is_enabled']) ? 1 : 0
        ];
        
        // Only update client_secret if provided
        if (empty($data['client_secret'])) {
            unset($data['client_secret']);
        }
        
        if ($this->settings->updateOAuthProvider($providerId, $data, $userId)) {
            // Update the main setting
            $this->settings->set($data['name'] . '_oauth_enabled', $data['is_enabled'] ? 'true' : 'false', $userId);
            
            $_SESSION['success'] = 'OAuth settings updated successfully';
            Auth::logAction('oauth_settings_updated', ['provider_id' => $providerId]);
        } else {
            $_SESSION['error'] = 'Failed to update OAuth settings';
        }
        
        redirect('/admin/settings?tab=authentication');
    }
    
    /**
     * Test OAuth connection
     */
    function testOAuth() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/settings');
            exit;
        }
        
        Auth::validateCSRF();
        
        $providerId = $_POST['provider_id'] ?? 0;
        
        if ($providerId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid provider']);
            exit;
        }
        
        $result = $this->settings->testOAuthConnection($providerId);
        
        header('Content-Type: application/json');
        echo json_encode($result);
    }
    
    /**
     * Update payment gateway settings
     */
    function updatePaymentGateway() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/settings');
            exit;
        }
        
        Auth::validateCSRF();
        
        $userId = $_SESSION['user_id'];
        $gatewayId = $_POST['gateway_id'] ?? 0;
        
        if ($gatewayId <= 0) {
            $_SESSION['error'] = 'Invalid gateway';
            redirect('/admin/settings?tab=payment');
            exit;
        }
        
        $data = [
            'public_key' => $_POST['public_key'] ?? '',
            'secret_key' => $_POST['secret_key'] ?? '',
            'merchant_id' => $_POST['merchant_id'] ?? '',
            'webhook_secret' => $_POST['webhook_secret'] ?? '',
            'test_mode' => isset($_POST['test_mode']) ? 1 : 0,
            'is_enabled' => isset($_POST['is_enabled']) ? 1 : 0,
            'additional_config' => json_encode($_POST['additional_config'] ?? [])
        ];
        
        // Only update secret keys if provided
        if (empty($data['secret_key'])) {
            unset($data['secret_key']);
        }
        if (empty($data['webhook_secret'])) {
            unset($data['webhook_secret']);
        }
        
        if ($this->settings->updatePaymentGateway($gatewayId, $data, $userId)) {
            $_SESSION['success'] = 'Payment gateway settings updated successfully';
            Auth::logAction('payment_gateway_updated', ['gateway_id' => $gatewayId]);
        } else {
            $_SESSION['error'] = 'Failed to update payment gateway settings';
        }
        
        redirect('/admin/settings?tab=payment');
    }
    
    /**
     * Test payment gateway connection
     */
    function testPaymentGateway() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/settings');
            exit;
        }
        
        Auth::validateCSRF();
        
        $gatewayId = $_POST['gateway_id'] ?? 0;
        
        if ($gatewayId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid gateway']);
            exit;
        }
        
        $result = $this->settings->testPaymentGateway($gatewayId);
        
        header('Content-Type: application/json');
        echo json_encode($result);
    }
    
    /**
     * Update email provider settings
     */
    function updateEmailProvider() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/settings');
            exit;
        }
        
        Auth::validateCSRF();
        
        $userId = $_SESSION['user_id'];
        $providerId = $_POST['provider_id'] ?? 0;
        
        if ($providerId <= 0) {
            $_SESSION['error'] = 'Invalid provider';
            redirect('/admin/settings?tab=email');
            exit;
        }
        
        $data = [
            'host' => $_POST['host'] ?? '',
            'port' => $_POST['port'] ?? 587,
            'username' => $_POST['username'] ?? '',
            'password' => $_POST['password'] ?? '',
            'encryption' => $_POST['encryption'] ?? 'tls',
            'api_key' => $_POST['api_key'] ?? '',
            'from_address' => $_POST['from_address'] ?? '',
            'from_name' => $_POST['from_name'] ?? '',
            'test_mode' => isset($_POST['test_mode']) ? 1 : 0,
            'is_enabled' => isset($_POST['is_enabled']) ? 1 : 0,
            'additional_config' => json_encode($_POST['additional_config'] ?? [])
        ];
        
        // Only update password/API key if provided
        if (empty($data['password'])) {
            unset($data['password']);
        }
        if (empty($data['api_key'])) {
            unset($data['api_key']);
        }
        
        $st = db()->prepare("
            UPDATE email_providers 
            SET host = ?, port = ?, username = ?, encryption = ?, from_address = ?, from_name = ?, 
                test_mode = ?, is_enabled = ?, additional_config = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        // Handle encrypted fields
        if (isset($data['password']) && !empty($data['password'])) {
            $encryptedPassword = $this->settings->encrypt($data['password']);
            $st = db()->prepare("
                UPDATE email_providers 
                SET host = ?, port = ?, username = ?, password_encrypted = ?, password = NULL, 
                    encryption = ?, from_address = ?, from_name = ?, test_mode = ?, is_enabled = ?, 
                    additional_config = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $params = [
                $data['host'], $data['port'], $data['username'], $encryptedPassword,
                $data['encryption'], $data['from_address'], $data['from_name'],
                $data['test_mode'], $data['is_enabled'], $data['additional_config'], $providerId
            ];
        } elseif (isset($data['api_key']) && !empty($data['api_key'])) {
            $encryptedApiKey = $this->settings->encrypt($data['api_key']);
            $st = db()->prepare("
                UPDATE email_providers 
                SET host = ?, port = ?, username = ?, api_key_encrypted = ?, api_key = NULL, 
                    encryption = ?, from_address = ?, from_name = ?, test_mode = ?, is_enabled = ?, 
                    additional_config = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $params = [
                $data['host'], $data['port'], $data['username'], $encryptedApiKey,
                $data['encryption'], $data['from_address'], $data['from_name'],
                $data['test_mode'], $data['is_enabled'], $data['additional_config'], $providerId
            ];
        } else {
            $params = [
                $data['host'], $data['port'], $data['username'], $data['encryption'],
                $data['from_address'], $data['from_name'], $data['test_mode'],
                $data['is_enabled'], $data['additional_config'], $providerId
            ];
        }
        
        if ($st->execute($params)) {
            $_SESSION['success'] = 'Email provider settings updated successfully';
            Auth::logAction('email_provider_updated', ['provider_id' => $providerId]);
        } else {
            $_SESSION['error'] = 'Failed to update email provider settings';
        }
        
        redirect('/admin/settings?tab=email');
    }
    
    /**
     * Test email configuration
     */
    function testEmail() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/settings');
            exit;
        }
        
        Auth::validateCSRF();
        
        $providerId = $_POST['provider_id'] ?? 0;
        $testEmail = $_POST['test_email'] ?? '';
        
        if ($providerId <= 0 || empty($testEmail)) {
            echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
            exit;
        }
        
        // Get provider settings
        $providers = $this->settings->getEmailProviders();
        $provider = null;
        foreach ($providers as $p) {
            if ($p->id == $providerId) {
                $provider = $p;
                break;
            }
        }
        
        if (!$provider || !$provider->is_enabled) {
            echo json_encode(['success' => false, 'error' => 'Provider not found or disabled']);
            exit;
        }
        
        // Test email sending (simplified implementation)
        $result = $this->sendTestEmail($provider, $testEmail);
        
        header('Content-Type: application/json');
        echo json_encode($result);
    }
    
    /**
     * Send test email
     */
    private function sendTestEmail($provider, $testEmail) {
        try {
            // This is a simplified implementation
            // In production, you would use PHPMailer or similar library
            
            $subject = "Test Email from QuizYourFaith";
            $message = "This is a test email to verify your email configuration.";
            
            // Basic mail function (replace with proper email library)
            $headers = "From: {$provider->from_name} <{$provider->from_address}>\r\n";
            $headers .= "Reply-To: {$provider->from_address}\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();
            
            $sent = mail($testEmail, $subject, $message, $headers);
            
            if ($sent) {
                return ['success' => true, 'message' => 'Test email sent successfully'];
            } else {
                return ['success' => false, 'error' => 'Failed to send test email'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Email sending error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Update security settings
     */
    function updateSecurity() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/settings');
            exit;
        }
        
        Auth::validateCSRF();
        
        $userId = $_SESSION['user_id'];
        $updated = [];
        $errors = [];
        
        $securitySettings = [
            'max_login_attempts',
            'lockout_duration_minutes',
            'password_min_length',
            'password_require_uppercase',
            'password_require_lowercase',
            'password_require_numbers',
            'password_require_special',
            'session_timeout_minutes',
            'enable_2fa',
            'api_rate_limit',
            'enable_captcha',
            'force_ssl'
        ];
        
        foreach ($securitySettings as $key) {
            $value = $_POST[$key] ?? '';
            
            if ($key === 'password_require_uppercase' || 
                $key === 'password_require_lowercase' || 
                $key === 'password_require_numbers' || 
                $key === 'password_require_special' || 
                $key === 'enable_2fa' || 
                $key === 'enable_captcha' || 
                $key === 'force_ssl') {
                $value = isset($_POST[$key]) ? 'true' : 'false';
            }
            
            if ($this->settings->updateSecuritySetting($key, $value, $userId)) {
                $updated[] = $key;
            } else {
                $errors[$key] = ['Failed to update setting'];
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['error'] = 'Some security settings could not be updated';
        } else {
            $_SESSION['success'] = 'Security settings updated successfully';
        }
        
        Auth::logAction('security_settings_updated', ['updated' => $updated]);
        
        redirect('/admin/settings?tab=security');
    }
    
    /**
     * Update API settings
     */
    function updateAPI() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/settings');
            exit;
        }
        
        Auth::validateCSRF();
        
        $userId = $_SESSION['user_id'];
        
        $apiSettings = [
            'enable_api' => isset($_POST['enable_api']) ? 'true' : 'false',
            'api_rate_limit' => $_POST['api_rate_limit'] ?? '100'
        ];
        
        foreach ($apiSettings as $key => $value) {
            $this->settings->set($key, $value, $userId);
        }
        
        $_SESSION['success'] = 'API settings updated successfully';
        Auth::logAction('api_settings_updated');
        
        redirect('/admin/settings?tab=api');
    }
    
    /**
     * Generate new API key
     */
    function generateAPIKey() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/settings');
            exit;
        }
        
        Auth::validateCSRF();
        
        $userId = $_SESSION['user_id'];
        
        $name = $_POST['name'] ?? '';
        $service = $_POST['service'] ?? '';
        $description = $_POST['description'] ?? '';
        $permissions = $_POST['permissions'] ?? [];
        $expiresIn = $_POST['expires_in'] ?? 365; // days
        
        if (empty($name) || empty($service)) {
            $_SESSION['error'] = 'Name and service are required';
            redirect('/admin/settings?tab=api');
            exit;
        }
        
        // Generate secure API key
        $apiKey = bin2hex(random_bytes(32));
        $hashedKey = hash('sha256', $apiKey);
        
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expiresIn} days"));
        
        $st = db()->prepare("
            INSERT INTO api_keys (name, key_value_encrypted, service_name, description, permissions, expires_at, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $encryptedKey = $this->settings->encrypt($hashedKey);
        $permissionsJson = json_encode($permissions);
        
        if ($st->execute([$name, $encryptedKey, $service, $description, $permissionsJson, $expiresAt, $userId])) {
            $_SESSION['success'] = 'API key generated successfully. Copy this key now as it won\'t be shown again: ' . $apiKey;
            Auth::logAction('api_key_generated', ['service' => $service, 'name' => $name]);
        } else {
            $_SESSION['error'] = 'Failed to generate API key';
        }
        
        redirect('/admin/settings?tab=api');
    }
    
    /**
     * Revoke API key
     */
    function revokeAPIKey($keyId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/settings');
            exit;
        }
        
        Auth::validateCSRF();
        
        $st = db()->prepare("UPDATE api_keys SET is_active = 0 WHERE id = ?");
        
        if ($st->execute([$keyId])) {
            $_SESSION['success'] = 'API key revoked successfully';
            Auth::logAction('api_key_revoked', ['key_id' => $keyId]);
        } else {
            $_SESSION['error'] = 'Failed to revoke API key';
        }
        
        redirect('/admin/settings?tab=api');
    }
    
    /**
     * View settings history
     */
    function history() {
        $page = $_GET['page'] ?? 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;
        
        $history = $this->settings->getHistory($limit, $offset);
        $totalChanges = db()->query("SELECT COUNT(*) FROM settings_history")->fetchColumn();
        
        view('admin/settings/history', [
            'history' => $history,
            'total_pages' => ceil($totalChanges / $limit),
            'current_page' => $page
        ]);
    }
    
    /**
     * Export settings
     */
    function export() {
        $categories = $this->settings->getAllSettings();
        
        $exportData = [
            'exported_at' => date('Y-m-d H:i:s'),
            'exported_by' => $_SESSION['username'] ?? 'Unknown',
            'settings' => []
        ];
        
        foreach ($categories as $category) {
            $categoryData = [
                'category' => $category['category']->name,
                'settings' => []
            ];
            
            foreach ($category['settings'] as $setting) {
                // Skip sensitive settings
                if ($setting->is_sensitive) {
                    continue;
                }
                
                $categoryData['settings'][] = [
                    'key' => $setting->key_name,
                    'value' => $setting->cast_value,
                    'type' => $setting->data_type,
                    'description' => $setting->description
                ];
            }
            
            if (!empty($categoryData['settings'])) {
                $exportData['settings'][] = $categoryData;
            }
        }
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="settings_export_' . date('Y-m-d_H-i-s') . '.json"');
        
        echo json_encode($exportData, JSON_PRETTY_PRINT);
    }
    
    /**
     * Import settings
     */
    function import() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/settings');
            exit;
        }
        
        Auth::validateCSRF();
        
        if (!isset($_FILES['settings_file']) || $_FILES['settings_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Please select a valid settings file';
            redirect('/admin/settings');
            exit;
        }
        
        $fileContent = file_get_contents($_FILES['settings_file']['tmp_name']);
        $importData = json_decode($fileContent, true);
        
        if (!$importData || !isset($importData['settings'])) {
            $_SESSION['error'] = 'Invalid settings file format';
            redirect('/admin/settings');
            exit;
        }
        
        $imported = 0;
        $errors = [];
        
        foreach ($importData['settings'] as $categoryData) {
            foreach ($categoryData['settings'] as $setting) {
                $key = $setting['key'] ?? '';
                $value = $setting['value'] ?? '';
                
                if (empty($key)) continue;
                
                $validation = $this->settings->validateValue($key, $value);
                if ($validation['valid']) {
                    if ($this->settings->set($key, $value, $_SESSION['user_id'])) {
                        $imported++;
                    }
                } else {
                    $errors[$key] = $validation['errors'];
                }
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['error'] = "Imported $imported settings with some errors";
        } else {
            $_SESSION['success'] = "Successfully imported $imported settings";
        }
        
        Auth::logAction('settings_imported', ['imported_count' => $imported]);
        
        redirect('/admin/settings');
    }
    
    /**
     * Get OAuth redirect URI
     */
    private function getOAuthRedirectUri($provider) {
        $baseUrl = $this->settings->get('site_url', 'http://localhost');
        return $baseUrl . '/auth/oauth/' . $provider . '/callback';
    }
    
    /**
     * Get default OAuth scopes
     */
    private function getDefaultScopes($provider) {
        $scopes = [
            'google' => 'openid email profile',
            'facebook' => 'email public_profile',
            'twitter' => 'account.read users.read',
            'github' => 'user:email',
            'microsoft' => 'openid email profile'
        ];
        
        return $scopes[$provider] ?? 'email profile';
    }
    
    /**
     * Get webhook URL
     */
    private function getWebhookUrl() {
        $baseUrl = $this->settings->get('site_url', 'http://localhost');
        return $baseUrl . '/webhooks/payment';
    }
}
