<?php namespace App\Models;
class Quiz{
    static function all(){
        return db()->query("SELECT * FROM quizzes ORDER BY title")->fetchAll();
    }
    
    static function find($id){
        return db()->prepare("SELECT * FROM quizzes WHERE id=? LIMIT 1")->execute([$id])->fetch();
    }
    
    static function getUserScore($user_id, $quiz_id){
        $stmt = db()->prepare("
            SELECT COUNT(*) as correct,
                   (SELECT COUNT(*) FROM questions WHERE quiz_id=?) as total
            FROM answers
            WHERE user_id=? AND quiz_id=? AND is_correct=1
        ");
        $stmt->execute([$quiz_id, $user_id, $quiz_id]);
        return $stmt->fetch();
    }
}
