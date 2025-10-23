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
