<?php
$uri=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
$map=[
    '#^/$#' => 'HomeController@index',
    '#^login$#' => 'AuthController@login',
    '#^register$#' => 'AuthController@register',
    '#^logout$#' => 'AuthController@logout',
    '#^dashboard$#' => 'UserController@dashboard',
    '#^quiz/(\d+)$#' => 'QuizController@play',
    '#^result/(\d+)$#' => 'QuizController@result',
    '#^admin$#' => 'AdminController@dashboard',
    '#^admin/quizzes$#' => 'AdminController@quizzes',
    '#^donate$#' => 'DonateController@handle',
];
foreach($map as $re=>$act){if(preg_match($re,$uri,$m)){list($c,$f)=explode('@',$act);$c="App\\Controllers\\$c";(new $c)->$f(...array_slice($m,1));exit;}}
http_response_code(404);echo "Page not found";
