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

// Bible Quiz Admin routes
$router->group('/admin',function($r){
    $r->get('/bible-quiz',[\App\Controllers\Admin\BibleQuizController::class,'dashboard']);
    $r->get('/bible-quiz/books',[\App\Controllers\Admin\BibleQuizController::class,'books']);
    $r->get('/bible-quiz/book/{id}',[\App\Controllers\Admin\BibleQuizController::class,'book']);
    $r->get('/bible-quiz/quizzes',[\App\Controllers\Admin\BibleQuizController::class,'quizzes']);
    $r->get('/bible-quiz/create-quiz',[\App\Controllers\Admin\BibleQuizController::class,'createQuiz']);
    $r->post('/bible-quiz/store-quiz',[\App\Controllers\Admin\BibleQuizController::class,'storeQuiz']);
    $r->get('/bible-quiz/edit-quiz/{id}',[\App\Controllers\Admin\BibleQuizController::class,'editQuiz']);
    $r->post('/bible-quiz/update-quiz/{id}',[\App\Controllers\Admin\BibleQuizController::class,'updateQuiz']);
    $r->post('/bible-quiz/delete-quiz/{id}',[\App\Controllers\Admin\BibleQuizController::class,'deleteQuiz']);
    $r->get('/bible-quiz/questions/{quizId}',[\App\Controllers\Admin\BibleQuizController::class,'questions']);
    $r->get('/bible-quiz/create-question/{quizId}',[\App\Controllers\Admin\BibleQuizController::class,'createQuestion']);
    $r->post('/bible-quiz/store-question/{quizId}',[\App\Controllers\Admin\BibleQuizController::class,'storeQuestion']);
    $r->get('/bible-quiz/edit-question/{id}',[\App\Controllers\Admin\BibleQuizController::class,'editQuestion']);
    $r->post('/bible-quiz/update-question/{id}',[\App\Controllers\Admin\BibleQuizController::class,'updateQuestion']);
    $r->post('/bible-quiz/delete-question/{id}',[\App\Controllers\Admin\BibleQuizController::class,'deleteQuestion']);
    $r->get('/bible-quiz/statistics',[\App\Controllers\Admin\BibleQuizController::class,'statistics']);
    $r->get('/bible-quiz/bulk-import',[\App\Controllers\Admin\BibleQuizController::class,'bulkImport']);
    $r->post('/bible-quiz/process-bulk-import',[\App\Controllers\Admin\BibleQuizController::class,'processBulkImport']);
});

// Bible Quiz routes
$router->group('/bible-quiz',function($r){
    $r->get('/',[\App\Controllers\BibleQuizController::class,'index']);
    $r->get('/testament/{testament}',[\App\Controllers\BibleQuizController::class,'testament']);
    $r->get('/category/{name}',[\App\Controllers\BibleQuizController::class,'category']);
    $r->get('/book/{id}',[\App\Controllers\BibleQuizController::class,'book']);
    $r->get('/play/{id}',[\App\Controllers\BibleQuizController::class,'play']);
    $r->post('/submit/{id}',[\App\Controllers\BibleQuizController::class,'submit']);
    $r->get('/result/{id}',[\App\Controllers\BibleQuizController::class,'result']);
    $r->get('/leaderboard',[\App\Controllers\BibleQuizController::class,'leaderboard']);
    $r->get('/leaderboard/{bookId}',[\App\Controllers\BibleQuizController::class,'leaderboard']);
    $r->get('/my-progress',[\App\Controllers\BibleQuizController::class,'myProgress']);
    $r->get('/achievements',[\App\Controllers\BibleQuizController::class,'achievements']);
    $r->get('/search',[\App\Controllers\BibleQuizController::class,'search']);
});

// Frontend video routes
$router->get('/videos',[\App\Controllers\VideoController::class,'slider']);
$router->get('/videos/watch/{id}',[\App\Controllers\VideoController::class,'watch']);
$router->post('/videos/react',[\App\Controllers\VideoController::class,'apiReact']);
$router->post('/videos/remove-reaction',[\App\Controllers\VideoController::class,'apiRemoveReaction']);
