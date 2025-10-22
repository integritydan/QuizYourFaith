<?php namespace App\Controllers;
use App\Models\{Quiz,Question,Answer};
class QuizController{
    function index(){view('quiz/index',['quizzes'=>Quiz::all()]);}
    function play($id){
        $quiz=Quiz::find($id);
        $qs=Question::byQuiz($id);
        view('quiz/play',['quiz'=>$quiz,'questions'=>$qs]);
    }
    function result($id){
        // mark answers & show result
        Answer::saveBatch($_SESSION['user_id'],$id,$_POST);
        view('quiz/result',['quiz'=>Quiz::find($id)]);
    }
}
