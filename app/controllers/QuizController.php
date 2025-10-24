<?php namespace App\Controllers;
use App\Models\{Quiz,Question,Answer};
class QuizController{
    function index(){view('quiz/index',['quizzes'=>Quiz::all()]);}
    
    function play($id){
        $quiz=Quiz::find($id);
        if(!$quiz){http_response_code(404);exit('Quiz not found');}
        
        $questions=Question::byQuiz($id);
        if(empty($questions)){http_response_code(404);exit('No questions found for this quiz');}
        
        // Initialize quiz session
        $_SESSION['quiz_'.$id] = [
            'start_time' => time(),
            'current_question' => 0,
            'total_questions' => count($questions),
            'timer' => $quiz->time_limit ?? 900 // 15 minutes default
        ];
        
        view('quiz/play',[
            'quiz'=>$quiz,
            'questions'=>$questions,
            'current_question'=>0,
            'timer'=>$quiz->time_limit ?? 900
        ]);
    }
    
    function result($id){
        if(!isset($_SESSION['quiz_'.$id])){
            header('Location: /quizzes');
            exit;
        }
        
        // Calculate score
        $correct_answers = 0;
        $total_questions = $_SESSION['quiz_'.$id]['total_questions'];
        
        if(isset($_POST['answers']) && is_array($_POST['answers'])){
            foreach($_POST['answers'] as $question_id => $chosen_answer){
                $correct = db()->prepare("SELECT correct FROM questions WHERE id=?")->execute([$question_id])->fetchColumn();
                if($chosen_answer == $correct){
                    $correct_answers++;
                }
            }
        }
        
        $score = ($total_questions > 0) ? round(($correct_answers / $total_questions) * 100) : 0;
        
        // Save answers to database
        Answer::saveBatch($_SESSION['user_id'], $id, $_POST);
        
        // Clear quiz session
        unset($_SESSION['quiz_'.$id]);
        
        view('quiz/result',[
            'quiz'=>Quiz::find($id),
            'score'=>$score,
            'correct_answers'=>$correct_answers,
            'total_questions'=>$total_questions
        ]);
    }
}
