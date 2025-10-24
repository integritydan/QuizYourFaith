<?php namespace App\Controllers;

use App\Models\Video;

class VideoController {
    
    function slider() {
        // Check if video feature is enabled
        if (!\App\Models\Feature::isEnabled('youtube_videos')) {
            $_SESSION['error'] = 'Video messages are currently disabled';
            redirect('/');
            return;
        }
        
        $videos = Video::getActiveVideos(10); // Get up to 10 active videos
        $categories = Video::getCategories();
        
        view('videos/slider', [
            'videos' => $videos,
            'categories' => $categories
        ]);
    }
    
    function watch($id) {
        // Check if video feature is enabled
        if (!\App\Models\Feature::isEnabled('youtube_videos')) {
            $_SESSION['error'] = 'Video messages are currently disabled';
            redirect('/');
            return;
        }
        
        $video = Video::find($id);
        
        if (!$video || !$video->is_active) {
            $_SESSION['error'] = "Video not found or not available";
            redirect('/videos');
            return;
        }
        
        // Record view
        $userId = $_SESSION['user_id'] ?? null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        Video::incrementViews($video->id);
        Video::recordView($video->id, $userId, $ipAddress, $userAgent);
        
        // Get user reaction if logged in
        $userReaction = null;
        if ($userId) {
            $userReaction = Video::getUserReaction($video->id, $userId);
        }
        
        // Get video stats
        $stats = Video::getVideoStats($video->id);
        
        view('videos/watch', [
            'video' => $video,
            'userReaction' => $userReaction,
            'stats' => $stats
        ]);
    }
    
    function apiReact() {
        header('Content-Type: application/json');
        
        // Check if video reactions are enabled
        if (!\App\Models\Feature::isEnabled('video_reactions')) {
            echo json_encode(['success' => false, 'message' => 'Video reactions are currently disabled']);
            return;
        }
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Please login to react']);
            return;
        }
        
        $videoId = $_POST['video_id'] ?? null;
        $reaction = $_POST['reaction'] ?? null;
        
        if (!$videoId || !in_array($reaction, ['like', 'dislike'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            return;
        }
        
        try {
            Video::addReaction($videoId, $_SESSION['user_id'], $reaction);
            $stats = Video::getVideoStats($videoId);
            
            echo json_encode([
                'success' => true,
                'message' => 'Reaction recorded',
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error recording reaction']);
        }
    }
    
    function apiRemoveReaction() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Please login']);
            return;
        }
        
        $videoId = $_POST['video_id'] ?? null;
        
        if (!$videoId) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            return;
        }
        
        try {
            Video::removeReaction($videoId, $_SESSION['user_id']);
            $stats = Video::getVideoStats($videoId);
            
            echo json_encode([
                'success' => true,
                'message' => 'Reaction removed',
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error removing reaction']);
        }
    }
}