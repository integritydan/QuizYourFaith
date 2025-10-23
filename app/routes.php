# ---- auto-added updater routes ----
$router->group('/admin',function($r){
    $r->get('/update',[\App\Controllers\Admin\UpdateController::class,'index']);
    $r->post('/update/upload',[\App\Controllers\Admin\UpdateController::class,'upload']);
});
