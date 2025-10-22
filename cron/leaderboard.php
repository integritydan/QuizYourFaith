#!/usr/bin/env php
<?php
require __DIR__.'/../config/constants.php';
$pdo=db();
// simple total-score materialiser
$pdo->exec("TRUNCATE leaderboard");
$pdo->exec("
INSERT INTO leaderboard (user_id,total_score,quizzes_taken,accuracy,streak,last_quiz_at,rank)
SELECT a.user_id,
       SUM(a.is_correct) AS total_score,
       COUNT(DISTINCT a.quiz_id) AS quizzes_taken,
       ROUND(AVG(a.is_correct)*100,2) AS accuracy,
       0 AS streak,
       MAX(a.answered_at) AS last_quiz_at,
       ROW_NUMBER() OVER (ORDER BY SUM(a.is_correct) DESC) AS rank
FROM answers a
GROUP BY a.user_id
");
