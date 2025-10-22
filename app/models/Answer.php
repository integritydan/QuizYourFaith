<?php namespace App\Models;
class Answer{
    static function saveBatch($uid,$qid,$post){
        foreach($post['answers']??[] as $q=>$a){
            $correct=db()->prepare("SELECT correct FROM questions WHERE id=?")->execute([$q])->fetchColumn();
            $stmt=db()->prepare("INSERT INTO answers (user_id,quiz_id,question_id,chosen,is_correct,answered_at) VALUES (?,?,?,?,?,NOW())");
            $stmt->execute([$uid,$qid,$q,$a,$a===$correct?1:0]);
        }
    }
}
