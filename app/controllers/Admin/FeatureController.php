<?php namespace App\Controllers\Admin;

use App\Models\Feature;

class FeatureController {
    
    function index() {
        $categories = Feature::getCategories();
        $features = Feature::all();
        $auditLog = Feature::getAuditLog(null, 20);
        
        view('admin/features/index', [
            'categories' => $categories,
            'features' => $features,
            'auditLog' => $auditLog
        ]);
    }
    
    function category($categoryName) {
        $category = Feature::findCategoryByName($categoryName);
        if (!$category) {
            $_SESSION['error'] = "Category not found";
            redirect('/admin/features');
            return;
        }
        
        $features = Feature::all($categoryName);
        $auditLog = Feature::getAuditLog(null, 20);
        
        view('admin/features/category', [
            'category' => $category,
            'features' => $features,
            'auditLog' => $auditLog
        ]);
    }
    
    function toggle($featureName) {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        $feature = Feature::findByName($featureName);
        if (!$feature) {
            echo json_encode(['success' => false, 'message' => 'Feature not found']);
            return;
        }
        
        // Check permissions
        if ($feature->requires_permission && !User::isAdmin($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
            return;
        }
        
        // Check dependencies
        if (!$feature->is_enabled && !Feature::checkDependencies($featureName)) {
            echo json_encode(['success' => false, 'message' => 'Required dependencies are not enabled']);
            return;
        }
        
        $success = Feature::toggle($featureName, $_SESSION['user_id']);
        
        if ($success) {
            $updatedFeature = Feature::findByName($featureName);
            echo json_encode([
                'success' => true,
                'message' => 'Feature ' . ($updatedFeature->is_enabled ? 'enabled' : 'disabled'),
                'is_enabled' => $updatedFeature->is_enabled,
                'enabled_at' => $updatedFeature->enabled_at,
                'disabled_at' => $updatedFeature->disabled_at
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to toggle feature']);
        }
    }
    
    function enableCategory($categoryName) {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id']) || !User::isAdmin($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        $category = Feature::findCategoryByName($categoryName);
        if (!$category) {
            echo json_encode(['success' => false, 'message' => 'Category not found']);
            return;
        }
        
        $success = Feature::enableCategory($categoryName, $_SESSION['user_id']);
        
        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => "All features in category '{$categoryName}' enabled"
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to enable category']);
        }
    }
    
    function disableCategory($categoryName) {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id']) || !User::isAdmin($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        $category = Feature::findCategoryByName($categoryName);
        if (!$category) {
            echo json_encode(['success' => false, 'message' => 'Category not found']);
            return;
        }
        
        $success = Feature::disableCategory($categoryName, $_SESSION['user_id']);
        
        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => "All features in category '{$categoryName}' disabled"
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to disable category']);
        }
    }
    
    function create() {
        if (!User::isAdmin($_SESSION['user_id'] ?? 0)) {
            $_SESSION['error'] = 'Insufficient permissions';
            redirect('/admin/features');
            return;
        }
        
        $categories = Feature::getCategories(false);
        
        view('admin/features/create', [
            'categories' => $categories
        ]);
    }
    
    function store() {
        if (!User::isAdmin($_SESSION['user_id'] ?? 0)) {
            $_SESSION['error'] = 'Insufficient permissions';
            redirect('/admin/features');
            return;
        }
        
        $required = ['name', 'display_name', 'category'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $_SESSION['error'] = "Please fill in all required fields";
                redirect('/admin/features/create');
                return;
            }
        }
        
        // Check if feature name already exists
        if (Feature::findByName($_POST['name'])) {
            $_SESSION['error'] = "Feature with this name already exists";
            redirect('/admin/features/create');
            return;
        }
        
        $data = [
            'name' => $_POST['name'],
            'display_name' => $_POST['display_name'],
            'description' => $_POST['description'] ?? '',
            'is_enabled' => isset($_POST['is_enabled']) ? 1 : 0,
            'category' => $_POST['category'],
            'requires_permission' => $_POST['requires_permission'] ?: null,
            'config_data' => $_POST['config_data'] ? json_encode(json_decode($_POST['config_data'])) : null,
            'created_by' => $_SESSION['user_id']
        ];
        
        try {
            $featureId = Feature::create($data);
            $_SESSION['success'] = "Feature created successfully!";
            redirect('/admin/features');
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error creating feature: " . $e->getMessage();
            redirect('/admin/features/create');
        }
    }
    
    function edit($id) {
        if (!User::isAdmin($_SESSION['user_id'] ?? 0)) {
            $_SESSION['error'] = 'Insufficient permissions';
            redirect('/admin/features');
            return;
        }
        
        $feature = Feature::findById($id);
        if (!$feature) {
            $_SESSION['error'] = "Feature not found";
            redirect('/admin/features');
            return;
        }
        
        $categories = Feature::getCategories(false);
        $auditLog = Feature::getAuditLog($id, 10);
        
        view('admin/features/edit', [
            'feature' => $feature,
            'categories' => $categories,
            'auditLog' => $auditLog
        ]);
    }
    
    function update($id) {
        if (!User::isAdmin($_SESSION['user_id'] ?? 0)) {
            $_SESSION['error'] = 'Insufficient permissions';
            redirect('/admin/features');
            return;
        }
        
        $feature = Feature::findById($id);
        if (!$feature) {
            $_SESSION['error'] = "Feature not found";
            redirect('/admin/features');
            return;
        }
        
        $data = [
            'display_name' => $_POST['display_name'] ?? $feature->display_name,
            'description' => $_POST['description'] ?? $feature->description,
            'category' => $_POST['category'] ?? $feature->category,
            'requires_permission' => $_POST['requires_permission'] ?: null,
            'config_data' => $_POST['config_data'] ? json_encode(json_decode($_POST['config_data'])) : $feature->config_data,
            'updated_by' => $_SESSION['user_id']
        ];
        
        try {
            Feature::update($id, $data);
            $_SESSION['success'] = "Feature updated successfully!";
            redirect('/admin/features');
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error updating feature: " . $e->getMessage();
            redirect('/admin/features/edit/' . $id);
        }
    }
    
    function delete($id) {
        if (!User::isAdmin($_SESSION['user_id'] ?? 0)) {
            $_SESSION['error'] = 'Insufficient permissions';
            redirect('/admin/features');
            return;
        }
        
        $feature = Feature::findById($id);
        if (!$feature) {
            $_SESSION['error'] = "Feature not found";
            redirect('/admin/features');
            return;
        }
        
        // Don't allow deletion of core features
        if ($feature->category === 'core') {
            $_SESSION['error'] = "Cannot delete core features";
            redirect('/admin/features');
            return;
        }
        
        try {
            Feature::delete($id);
            $_SESSION['success'] = "Feature deleted successfully!";
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error deleting feature: " . $e->getMessage();
        }
        
        redirect('/admin/features');
    }
    
    function audit($id) {
        $feature = Feature::findById($id);
        if (!$feature) {
            $_SESSION['error'] = "Feature not found";
            redirect('/admin/features');
            return;
        }
        
        $auditLog = Feature::getAuditLog($id, 100);
        
        view('admin/features/audit', [
            'feature' => $feature,
            'auditLog' => $auditLog
        ]);
    }
}