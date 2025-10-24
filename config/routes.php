<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Allow access to activation page without checking activation status
if ($uri === '/activate.php' || $uri === '/activate') {
    require BASE_PATH . '/activate.php';
    exit;
}

$map = [
    // Home and basic routes
    '#^/$#' => 'HomeController@index',
    
    // Authentication routes
    '#^login$#' => 'AuthController@login',
    '#^register$#' => 'AuthController@register',
    '#^logout$#' => 'AuthController@logout',
    '#^forgot-password$#' => 'AuthController@forgotPassword',
    '#^reset-password$#' => 'AuthController@resetPassword',
    
    // User dashboard and profile
    '#^dashboard$#' => 'UserController@dashboard',
    '#^user/profile$#' => 'UserController@profile',
    '#^user/edit-profile$#' => 'UserController@editProfile',
    '#^user/change-password$#' => 'UserController@changePassword',
    '#^user/settings$#' => 'UserController@settings',
    '#^user/match-history$#' => 'UserController@matchHistory',
    '#^user/leaderboard$#' => 'UserController@leaderboard',
    '#^user/achievements$#' => 'UserController@achievements',
    '#^user/statistics$#' => 'UserController@statistics',
    
    // Quiz routes
    '#^quizzes$#' => 'QuizController@index',
    '#^quiz/(\d+)$#' => 'QuizController@play',
    '#^result/(\d+)$#' => 'QuizController@result',
    
    // Multiplayer routes
    '#^multiplayer/lobby$#' => 'MultiplayerController@lobby',
    '#^multiplayer/create$#' => 'MultiplayerController@createMatch',
    '#^multiplayer/join/(\d+)$#' => 'MultiplayerController@joinMatch',
    '#^multiplayer/leave/(\d+)$#' => 'MultiplayerController@leaveMatch',
    '#^multiplayer/match/(\d+)$#' => 'MultiplayerController@matchRoom',
    '#^multiplayer/start/(\d+)$#' => 'MultiplayerController@startMatch',
    '#^multiplayer/submit-answer/(\d+)$#' => 'MultiplayerController@submitAnswer',
    '#^multiplayer/send-message/(\d+)$#' => 'MultiplayerController@sendMessage',
    '#^multiplayer/updates/(\d+)$#' => 'MultiplayerController@getUpdates',
    
    // Friends routes
    '#^friends$#' => 'FriendsController@index',
    '#^friends/search$#' => 'FriendsController@search',
    '#^friends/send-request$#' => 'FriendsController@sendRequest',
    '#^friends/accept/(\d+)$#' => 'FriendsController@acceptRequest',
    '#^friends/decline/(\d+)$#' => 'FriendsController@declineRequest',
    '#^friends/remove/(\d+)$#' => 'FriendsController@removeFriend',
    '#^friends/block/(\d+)$#' => 'FriendsController@blockUser',
    '#^friends/unblock/(\d+)$#' => 'FriendsController@unblockUser',
    '#^friends/profile/(\d+)$#' => 'FriendsController@friendProfile',
    '#^friends/invite-to-match/(\d+)$#' => 'FriendsController@inviteToMatch',
    
    // Admin routes (general admin)
    '#^admin$#' => 'AdminController@dashboard',
    '#^admin/quizzes$#' => 'AdminController@quizzes',
    
    // Super Admin routes
    '#^admin/super$#' => 'Admin\SuperAdminController@dashboard',
    '#^admin/super/users$#' => 'Admin\SuperAdminController@users',
    '#^admin/super/users/(\d+)$#' => 'Admin\SuperAdminController@editUser',
    '#^admin/super/users/(\d+)/ban$#' => 'Admin\SuperAdminController@banUser',
    '#^admin/super/users/(\d+)/unban$#' => 'Admin\SuperAdminController@unbanUser',
    '#^admin/super/settings$#' => 'Admin\SuperAdminController@settings',
    '#^admin/super/multiplayer-servers$#' => 'Admin\SuperAdminController@multiplayerServers',
    '#^admin/super/match-reports$#' => 'Admin\SuperAdminController@matchReports',
    '#^admin/super/match-reports/(\d+)$#' => 'Admin\SuperAdminController@handleReport',
    '#^admin/super/tournaments$#' => 'Admin\SuperAdminController@tournaments',
    '#^admin/super/tournaments/create$#' => 'Admin\SuperAdminController@createTournament',
    '#^admin/super/system-logs$#' => 'Admin\SuperAdminController@systemLogs',
    '#^admin/super/analytics$#' => 'Admin\SuperAdminController@analytics',
    
    // Settings Management routes
    '#^admin/settings$#' => 'Admin\SettingsController@index',
    '#^admin/settings/update-general$#' => 'Admin\SettingsController@updateGeneral',
    '#^admin/settings/update-oauth$#' => 'Admin\SettingsController@updateOAuth',
    '#^admin/settings/test-oauth$#' => 'Admin\SettingsController@testOAuth',
    '#^admin/settings/update-payment-gateway$#' => 'Admin\SettingsController@updatePaymentGateway',
    '#^admin/settings/test-payment-gateway$#' => 'Admin\SettingsController@testPaymentGateway',
    '#^admin/settings/update-email-provider$#' => 'Admin\SettingsController@updateEmailProvider',
    '#^admin/settings/test-email$#' => 'Admin\SettingsController@testEmail',
    '#^admin/settings/update-security$#' => 'Admin\SettingsController@updateSecurity',
    '#^admin/settings/update-api$#' => 'Admin\SettingsController@updateAPI',
    '#^admin/settings/generate-api-key$#' => 'Admin\SettingsController@generateAPIKey',
    '#^admin/settings/revoke-api-key/(\d+)$#' => 'Admin\SettingsController@revokeAPIKey',
    '#^admin/settings/history$#' => 'Admin\SettingsController@history',
    '#^admin/settings/export$#' => 'Admin\SettingsController@export',
    '#^admin/settings/import$#' => 'Admin\SettingsController@import',
    
    // System Update Routes
    '#^admin/update$#' => 'Admin\UpdateController@index',
    '#^admin/update/upload$#' => 'Admin\UpdateController@uploadUpdate',
    
    // OAuth Routes
    '#^auth/oauth/google$#' => 'Auth\OAuthController@googleLogin',
    '#^auth/oauth/google/callback$#' => 'Auth\OAuthController@googleCallback',
    '#^auth/oauth/facebook$#' => 'Auth\OAuthController@facebookLogin',
    '#^auth/oauth/facebook/callback$#' => 'Auth\OAuthController@facebookCallback',
    '#^auth/oauth/unlink/(\w+)$#' => 'Auth\OAuthController@unlinkProvider',
    
    // Game Admin routes (moderators)
    '#^admin/game$#' => 'Admin\GameAdminController@dashboard',
    '#^admin/game/active-matches$#' => 'Admin\GameAdminController@activeMatches',
    '#^admin/game/match/(\d+)$#' => 'Admin\GameAdminController@matchDetails',
    '#^admin/game/match/(\d+)/kick/(\d+)$#' => 'Admin\GameAdminController@kickPlayer',
    '#^admin/game/match/(\d+)/end$#' => 'Admin\GameAdminController@endMatch',
    '#^admin/game/match-reports$#' => 'Admin\GameAdminController@matchReports',
    '#^admin/game/match-reports/(\d+)$#' => 'Admin\GameAdminController@handleReport',
    '#^admin/game/tournaments$#' => 'Admin\GameAdminController@tournaments',
    '#^admin/game/tournament/(\d+)$#' => 'Admin\GameAdminController@tournamentDetails',
    '#^admin/game/chat-moderation$#' => 'Admin\GameAdminController@chatModeration',
    '#^admin/game/moderate-message/(\d+)$#' => 'Admin\GameAdminController@moderateMessage',
    '#^admin/game/send-announcement$#' => 'Admin\GameAdminController@sendAnnouncement',
    
    // Donation routes
    '#^donate$#' => 'DonateController@handle',
    
    // API routes (for AJAX requests)
    '#^api/user/online-status$#' => 'ApiController@updateOnlineStatus',
    '#^api/user/stats$#' => 'ApiController@getUserStats',
    '#^api/match/(\d+)/players$#' => 'ApiController@getMatchPlayers',
    '#^api/friends/online$#' => 'ApiController@getOnlineFriends',
];

foreach ($map as $re => $act) {
    if (preg_match($re, $uri, $m)) {
        list($c, $f) = explode('@', $act);
        $c = "App\\Controllers\\$c";
        
        // Check if controller exists
        if (!class_exists($c)) {
            http_response_code(404);
            echo "Controller not found: $c";
            exit;
        }
        
        // Create controller instance and call method
        $controller = new $c();
        
        // Check if method exists
        if (!method_exists($controller, $f)) {
            http_response_code(404);
            echo "Method not found: $f in $c";
            exit;
        }
        
        // Call method with parameters
        call_user_func_array([$controller, $f], array_slice($m, 1));
        exit;
    }
}

http_response_code(404);
echo "Page not found";
