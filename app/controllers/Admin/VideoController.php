<?php namespace App\Controllers\Admin;

use App\Models\Video;

class VideoController {
    
    function index() {
        $videos = Video::all();
        $categories = Video::getCategories();
        
        view('admin/videos/index', [
            'videos' => $videos,
            'categories' => $categories
        ]);
    }
    
    function create() {
        $categories = Video::getCategories();
        
        view('admin/videos/create', [
            'categories' => $categories
        ]);
    }
    
    function store() {
        // Validate required fields
        $required = ['title', 'youtube_url', 'category_id'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $_SESSION['error'] = "Please fill in all required fields";
                redirect('/admin/videos/create');
                return;
            }
        }
        
        // Extract YouTube video ID
        $youtubeUrl = $_POST['youtube_url'];
        $videoId = Video::extractYouTubeVideoId($youtubeUrl);
        
        if (!$videoId) {
            $_SESSION['error'] = "Invalid YouTube URL. Please provide a valid YouTube video link.";
            redirect('/admin/videos/create');
            return;
        }
        
        // Check if video already exists
        $existing = Video::findByVideoId($videoId);
        if ($existing) {
            $_SESSION['error'] = "This video has already been added to the library.";
            redirect('/admin/videos/create');
            return;
        }
        
        // Prepare data
        $data = [
            'title' => $_POST['title'],
            'description' => $_POST['description'] ?? '',
            'youtube_url' => $youtubeUrl,
            'youtube_video_id' => $videoId,
            'thumbnail_url' => Video::getYouTubeThumbnail($videoId),
            'duration' => $_POST['duration'] ?? '',
            'category_id' => $_POST['category_id'],
            'tags' => $_POST['tags'] ?? '',
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'display_order' => $_POST['display_order'] ?? 0,
            'created_by' => $_SESSION['user_id']
        ];
        
        try {
            $videoId = Video::create($data);
            $_SESSION['success'] = "Video added successfully!";
            redirect('/admin/videos');
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error adding video: " . $e->getMessage();
            redirect('/admin/videos/create');
        }
    }
    
    function edit($id) {
        $video = Video::find($id);
        if (!$video) {
            $_SESSION['error'] = "Video not found";
            redirect('/admin/videos');
            return;
        }
        
        $categories = Video::getCategories();
        
        view('admin/videos/edit', [
            'video' => $video,
            'categories' => $categories
        ]);
    }
    
    function update($id) {
        $video = Video::find($id);
        if (!$video) {
            $_SESSION['error'] = "Video not found";
            redirect('/admin/videos');
            return;
        }
        
        // Validate required fields
        $required = ['title', 'youtube_url', 'category_id'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $_SESSION['error'] = "Please fill in all required fields";
                redirect('/admin/videos/edit/' . $id);
                return;
            }
        }
        
        // Extract YouTube video ID
        $youtubeUrl = $_POST['youtube_url'];
        $videoId = Video::extractYouTubeVideoId($youtubeUrl);
        
        if (!$videoId) {
            $_SESSION['error'] = "Invalid YouTube URL. Please provide a valid YouTube video link.";
            redirect('/admin/videos/edit/' . $id);
            return;
        }
        
        // Prepare data
        $data = [
            'title' => $_POST['title'],
            'description' => $_POST['description'] ?? '',
            'youtube_url' => $youtubeUrl,
            'youtube_video_id' => $videoId,
            'thumbnail_url' => Video::getYouTubeThumbnail($videoId),
            'duration' => $_POST['duration'] ?? '',
            'category_id' => $_POST['category_id'],
            'tags' => $_POST['tags'] ?? '',
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'display_order' => $_POST['display_order'] ?? 0
        ];
        
        try {
            Video::update($id, $data);
            $_SESSION['success'] = "Video updated successfully!";
            redirect('/admin/videos');
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error updating video: " . $e->getMessage();
            redirect('/admin/videos/edit/' . $id);
        }
    }
    
    function delete($id) {
        $video = Video::find($id);
        if (!$video) {
            $_SESSION['error'] = "Video not found";
            redirect('/admin/videos');
            return;
        }
        
        try {
            Video::delete($id);
            $_SESSION['success'] = "Video deleted successfully!";
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error deleting video: " . $e->getMessage();
        }
        
        redirect('/admin/videos');
    }
    
    function toggleStatus($id) {
        $video = Video::find($id);
        if (!$video) {
            echo json_encode(['success' => false, 'message' => 'Video not found']);
            return;
        }
        
        $newStatus = $video->is_active ? 0 : 1;
        Video::update($id, ['is_active' => $newStatus]);
        
        echo json_encode([
            'success' => true, 
            'new_status' => $newStatus,
            'message' => 'Video status updated'
        ]);
    }
    
    function categories() {
        $categories = Video::getCategories(false);
        
        view('admin/videos/categories', [
            'categories' => $categories
        ]);
    }
    
    function storeCategory() {
        $required = ['name', 'slug'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $_SESSION['error'] = "Please fill in all required fields";
                redirect('/admin/videos/categories');
                return;
            }
        }
        
        $data = [
            'name' => $_POST['name'],
            'slug' => $_POST['slug'],
            'description' => $_POST['description'] ?? '',
            'color' => $_POST['color'] ?? '#007bff',
            'icon' => $_POST['icon'] ?? 'video',
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        try {
            Video::createCategory($data);
            $_SESSION['success'] = "Category created successfully!";
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error creating category: " . $e->getMessage();
        }
        
        redirect('/admin/videos/categories');
    }
}