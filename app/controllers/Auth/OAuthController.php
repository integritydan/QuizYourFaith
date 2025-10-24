<?php namespace App\Controllers\Auth;
use App\Models\User;
use App\Models\Settings;
use App\Middleware\Auth;

class OAuthController {
    
    private $settings;
    
    function __construct() {
        $this->settings = new Settings();
    }
    
    /**
     * Initiate Google OAuth flow
     */
    function googleLogin() {
        $provider = $this->getOAuthProvider('google');
        if (!$provider || !$provider->is_enabled) {
            $_SESSION['error'] = 'Google login is not available';
            redirect('/login');
            exit;
        }
        
        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;
        
        $params = [
            'client_id' => $provider->client_id,
            'redirect_uri' => $this->getRedirectUri('google'),
            'scope' => 'openid email profile',
            'response_type' => 'code',
            'state' => $state,
            'access_type' => 'offline',
            'prompt' => 'consent'
        ];
        
        $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
        redirect($authUrl);
    }
    
    /**
     * Handle Google OAuth callback
     */
    function googleCallback() {
        // Verify state
        if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
            $_SESSION['error'] = 'Invalid state parameter';
            redirect('/login');
            exit;
        }
        
        // Check for errors
        if (isset($_GET['error'])) {
            $_SESSION['error'] = 'Google login failed: ' . htmlspecialchars($_GET['error']);
            redirect('/login');
            exit;
        }
        
        // Get authorization code
        if (!isset($_GET['code'])) {
            $_SESSION['error'] = 'No authorization code received';
            redirect('/login');
            exit;
        }
        
        $provider = $this->getOAuthProvider('google');
        if (!$provider || !$provider->is_enabled) {
            $_SESSION['error'] = 'Google login is not available';
            redirect('/login');
            exit;
        }
        
        // Exchange code for access token
        $tokenResponse = $this->exchangeCodeForToken($provider, $_GET['code'], 'google');
        if (!$tokenResponse || !isset($tokenResponse['access_token'])) {
            $_SESSION['error'] = 'Failed to get access token from Google';
            redirect('/login');
            exit;
        }
        
        // Get user info from Google
        $userInfo = $this->getGoogleUserInfo($tokenResponse['access_token']);
        if (!$userInfo || !isset($userInfo['email'])) {
            $_SESSION['error'] = 'Failed to get user information from Google';
            redirect('/login');
            exit;
        }
        
        // Process user login/registration
        $this->processOAuthUser($userInfo, 'google');
    }
    
    /**
     * Initiate Facebook OAuth flow
     */
    function facebookLogin() {
        $provider = $this->getOAuthProvider('facebook');
        if (!$provider || !$provider->is_enabled) {
            $_SESSION['error'] = 'Facebook login is not available';
            redirect('/login');
            exit;
        }
        
        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;
        
        $params = [
            'client_id' => $provider->client_id,
            'redirect_uri' => $this->getRedirectUri('facebook'),
            'scope' => 'email public_profile',
            'response_type' => 'code',
            'state' => $state
        ];
        
        $authUrl = 'https://www.facebook.com/v12.0/dialog/oauth?' . http_build_query($params);
        redirect($authUrl);
    }
    
    /**
     * Handle Facebook OAuth callback
     */
    function facebookCallback() {
        // Verify state
        if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
            $_SESSION['error'] = 'Invalid state parameter';
            redirect('/login');
            exit;
        }
        
        // Check for errors
        if (isset($_GET['error'])) {
            $_SESSION['error'] = 'Facebook login failed: ' . htmlspecialchars($_GET['error']);
            redirect('/login');
            exit;
        }
        
        // Get authorization code
        if (!isset($_GET['code'])) {
            $_SESSION['error'] = 'No authorization code received';
            redirect('/login');
            exit;
        }
        
        $provider = $this->getOAuthProvider('facebook');
        if (!$provider || !$provider->is_enabled) {
            $_SESSION['error'] = 'Facebook login is not available';
            redirect('/login');
            exit;
        }
        
        // Exchange code for access token
        $tokenResponse = $this->exchangeCodeForToken($provider, $_GET['code'], 'facebook');
        if (!$tokenResponse || !isset($tokenResponse['access_token'])) {
            $_SESSION['error'] = 'Failed to get access token from Facebook';
            redirect('/login');
            exit;
        }
        
        // Get user info from Facebook
        $userInfo = $this->getFacebookUserInfo($tokenResponse['access_token']);
        if (!$userInfo || !isset($userInfo['email'])) {
            $_SESSION['error'] = 'Failed to get user information from Facebook';
            redirect('/login');
            exit;
        }
        
        // Process user login/registration
        $this->processOAuthUser($userInfo, 'facebook');
    }
    
    /**
     * Exchange authorization code for access token
     */
    private function exchangeCodeForToken($provider, $code, $providerName) {
        $params = [
            'client_id' => $provider->client_id,
            'client_secret' => $provider->client_secret,
            'code' => $code,
            'redirect_uri' => $this->getRedirectUri($providerName),
            'grant_type' => 'authorization_code'
        ];
        
        $ch = curl_init();
        
        if ($providerName === 'google') {
            curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        } elseif ($providerName === 'facebook') {
            curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/v12.0/oauth/access_token');
        }
        
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            return false;
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Get user info from Google
     */
    private function getGoogleUserInfo($accessToken) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/oauth2/v2/userinfo');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            return false;
        }
        
        $userData = json_decode($response, true);
        
        return [
            'id' => $userData['id'] ?? '',
            'email' => $userData['email'] ?? '',
            'name' => $userData['name'] ?? '',
            'picture' => $userData['picture'] ?? '',
            'provider' => 'google'
        ];
    }
    
    /**
     * Get user info from Facebook
     */
    private function getFacebookUserInfo($accessToken) {
        $fields = 'id,email,name,picture';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/v12.0/me?fields=' . $fields . '&access_token=' . $accessToken);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            return false;
        }
        
        $userData = json_decode($response, true);
        
        return [
            'id' => $userData['id'] ?? '',
            'email' => $userData['email'] ?? '',
            'name' => $userData['name'] ?? '',
            'picture' => $userData['picture']['data']['url'] ?? '',
            'provider' => 'facebook'
        ];
    }
    
    /**
     * Process OAuth user login/registration
     */
    private function processOAuthUser($userInfo, $provider) {
        $email = $userInfo['email'] ?? '';
        $oauthId = $userInfo['id'] ?? '';
        $name = $userInfo['name'] ?? '';
        $avatar = $userInfo['picture'] ?? '';
        
        if (empty($email) || empty($oauthId)) {
            $_SESSION['error'] = 'Invalid user information from ' . ucfirst($provider);
            redirect('/login');
            exit;
        }
        
        // Check if user exists with this email
        $existingUser = User::findByEmail($email);
        
        if ($existingUser) {
            // Check if user has OAuth ID for this provider
            $st = db()->prepare("SELECT id FROM user_oauth WHERE user_id = ? AND provider = ? AND provider_user_id = ?");
            $st->execute([$existingUser->id, $provider, $oauthId]);
            
            if (!$st->fetch()) {
                // Link OAuth to existing user
                $st = db()->prepare("INSERT INTO user_oauth (user_id, provider, provider_user_id, created_at) VALUES (?, ?, ?, NOW())");
                $st->execute([$existingUser->id, $provider, $oauthId]);
            }
            
            // Update user info if needed
            $updateData = [];
            if (empty($existingUser->avatar) && !empty($avatar)) {
                $updateData['avatar'] = $avatar;
            }
            if (empty($existingUser->name) && !empty($name)) {
                $updateData['name'] = $name;
            }
            
            if (!empty($updateData)) {
                User::updateProfile($existingUser->id, $updateData);
            }
            
            // Login user
            $this->loginUser($existingUser);
        } else {
            // Create new user
            if (!$this->settings->get('registration_enabled', true)) {
                $_SESSION['error'] = 'Registration is currently disabled';
                redirect('/login');
                exit;
            }
            
            // Generate username from email
            $username = explode('@', $email)[0];
            $username = preg_replace('/[^a-zA-Z0-9]/', '', $username);
            
            // Ensure unique username
            $baseUsername = $username;
            $counter = 1;
            while (User::findByUsername($username)) {
                $username = $baseUsername . $counter++;
            }
            
            // Create user
            $userId = User::create([
                'name' => $name ?: $username,
                'email' => $email,
                'password' => password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT),
                'avatar' => $avatar,
                'email_verified_at' => date('Y-m-d H:i:s') // Auto-verify OAuth users
            ]);
            
            // Link OAuth
            $st = db()->prepare("INSERT INTO user_oauth (user_id, provider, provider_user_id, created_at) VALUES (?, ?, ?, NOW())");
            $st->execute([$userId, $provider, $oauthId]);
            
            // Login user
            $newUser = User::findById($userId);
            $this->loginUser($newUser);
        }
    }
    
    /**
     * Login user and set session
     */
    private function loginUser($user) {
        // Check if user is banned
        if (User::isBanned($user->id)) {
            $_SESSION['error'] = 'Your account has been banned';
            redirect('/login');
            exit;
        }
        
        // Set session
        $_SESSION['user_id'] = $user->id;
        $_SESSION['role'] = $user->role;
        $_SESSION['username'] = $user->name;
        $_SESSION['login_time'] = time();
        
        // Update online status
        User::updateOnlineStatus($user->id, 'online');
        
        // Log the login
        Auth::logAction('oauth_login', ['provider' => 'google', 'email' => $user->email]);
        
        // Redirect based on role
        redirect('/dashboard');
    }
    
    /**
     * Get OAuth provider by name
     */
    private function getOAuthProvider($name) {
        $st = db()->prepare("SELECT * FROM oauth_providers WHERE name = ? AND is_enabled = 1");
        $st->execute([$name]);
        return $st->fetch();
    }
    
    /**
     * Get OAuth redirect URI
     */
    private function getRedirectUri($provider) {
        $baseUrl = $this->settings->get('site_url', 'http://localhost');
        return $baseUrl . '/auth/oauth/' . $provider . '/callback';
    }
    
    /**
     * Unlink OAuth provider from user account
     */
    function unlinkProvider($provider) {
        Auth::userMiddleware();
        
        $userId = $_SESSION['user_id'];
        
        $st = db()->prepare("DELETE FROM user_oauth WHERE user_id = ? AND provider = ?");
        $st->execute([$userId, $provider]);
        
        if ($st->rowCount() > 0) {
            $_SESSION['success'] = ucfirst($provider) . ' account unlinked successfully';
            Auth::logAction('oauth_unlinked', ['provider' => $provider]);
        } else {
            $_SESSION['error'] = 'Failed to unlink ' . $provider . ' account';
        }
        
        redirect('/user/settings');
    }
    
    /**
     * Get user's linked OAuth providers
     */
    function getLinkedProviders($userId) {
        $st = db()->prepare("
            SELECT provider, created_at 
            FROM user_oauth 
            WHERE user_id = ? 
            ORDER BY created_at DESC
        ");
        $st->execute([$userId]);
        return $st->fetchAll();
    }
}