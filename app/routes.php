# ---- auto-added updater routes ----
$router->group('/admin',function($r){
    $r->get('/update',[\App\Controllers\Admin\UpdateController::class,'index']);
    $r->post('/update/upload',[\App\Controllers\Admin\UpdateController::class,'upload']);
});
# ---- user account hub ----
$router->get('/account',        [\App\Controllers\AccountController::class,'index']);
$router->get('/account/settings',[\App\Controllers\AccountController::class,'settings']);
$router->get('/account/profile', [\App\Controllers\AccountController::class,'profile']);
$router->get('/account/donations',[\App\Controllers\AccountController::class,'donations']);
$router->group('/admin',function($r){
    $r->get('/harden',[\App\Controllers\Admin\HardenController::class,'index']);
    $r->post('/harden/run',[\App\Controllers\Admin\HardenController::class,'run']);
});
$router->group('/admin',function($r){
    $r->get('/payment-keys',[\App\Controllers\Admin\PaymentKeyController::class,'index']);
    $r->post('/payment-keys/save',[\App\Controllers\Admin\PaymentKeyController::class,'save']);
});

// Video management routes
$router->group('/admin',function($r){
    $r->get('/videos',[\App\Controllers\Admin\VideoController::class,'index']);
    $r->get('/videos/create',[\App\Controllers\Admin\VideoController::class,'create']);
    $r->post('/videos/store',[\App\Controllers\Admin\VideoController::class,'store']);
    $r->get('/videos/edit/{id}',[\App\Controllers\Admin\VideoController::class,'edit']);
    $r->post('/videos/update/{id}',[\App\Controllers\Admin\VideoController::class,'update']);
    $r->post('/videos/delete/{id}',[\App\Controllers\Admin\VideoController::class,'delete']);
    $r->post('/videos/toggle-status/{id}',[\App\Controllers\Admin\VideoController::class,'toggleStatus']);
    $r->get('/videos/categories',[\App\Controllers\Admin\VideoController::class,'categories']);
    $r->post('/videos/categories/store',[\App\Controllers\Admin\VideoController::class,'storeCategory']);
});

// Feature management routes
$router->group('/admin',function($r){
    $r->get('/features',[\App\Controllers\Admin\FeatureController::class,'index']);
    $r->get('/features/category/{name}',[\App\Controllers\Admin\FeatureController::class,'category']);
    $r->get('/features/create',[\App\Controllers\Admin\FeatureController::class,'create']);
    $r->post('/features/store',[\App\Controllers\Admin\FeatureController::class,'store']);
    $r->get('/features/edit/{id}',[\App\Controllers\Admin\FeatureController::class,'edit']);
    $r->post('/features/update/{id}',[\App\Controllers\Admin\FeatureController::class,'update']);
    $r->post('/features/delete/{name}',[\App\Controllers\Admin\FeatureController::class,'delete']);
    $r->post('/features/toggle/{name}',[\App\Controllers\Admin\FeatureController::class,'toggle']);
    $r->post('/features/enable-category/{name}',[\App\Controllers\Admin\FeatureController::class,'enableCategory']);
    $r->post('/features/disable-category/{name}',[\App\Controllers\Admin\FeatureController::class,'disableCategory']);
    $r->get('/features/audit/{id}',[\App\Controllers\Admin\FeatureController::class,'audit']);
});

// Frontend video routes
$router->get('/videos',[\App\Controllers\VideoController::class,'slider']);
$router->get('/videos/watch/{id}',[\App\Controllers\VideoController::class,'watch']);
$router->post('/videos/react',[\App\Controllers\VideoController::class,'apiReact']);
$router->post('/videos/remove-reaction',[\App\Controllers\VideoController::class,'apiRemoveReaction']);
