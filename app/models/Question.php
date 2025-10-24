<?php namespace App\Models;
class Question{
    static function byQuiz($id){
        return db()->prepare("SELECT * FROM questions WHERE quiz_id=? ORDER BY id")->execute([$id])->fetchAll();
    }
    
    static function find($id){
        return db()->prepare("SELECT * FROM questions WHERE id=? LIMIT 1")->execute([$id])->fetch();
    }
    
    static function getCorrectAnswer($question_id){
        return db()->prepare("SELECT correct FROM questions WHERE id=?")->execute([$question_id])->fetchColumn();
    }
}
