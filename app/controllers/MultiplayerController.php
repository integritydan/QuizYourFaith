<?php namespace App\Controllers;
use App\Models\User;
use App\Middleware\Auth;

class MultiplayerController {
    
    function __construct() {
        Auth::userMiddleware();
    }
    
    // Multiplayer lobby - list available matches
    function lobby() {
        $userId = $_SESSION['user_id'];
        
        // Get available matches
        $availableMatches = db()->prepare("
            SELECT 
                m.*, 
                q.title as quiz_title,
                q.difficulty,
                COUNT(mp.user_id) as current_players,
                u.name as creator_name
            FROM matches m
            JOIN quizzes q ON m.quiz_id = q.id
            JOIN users u ON m.created_by = u.id
            LEFT JOIN match_players mp ON m.id = mp.match_id
            WHERE m.status = 'waiting' 
            AND m.current_players < m.max_players
            AND m.id NOT IN (SELECT match_id FROM match_players WHERE user_id = ?)
            ORDER BY m.created_at DESC
        ");
        $availableMatches->execute([$userId]);
        
        // Get user's active matches
        $userMatches = User::getActiveMatches($userId);
        
        // Get friends who are online
        $friends = User::getFriends($userId, 'accepted');
        $onlineFriends = array_filter($friends, function($friend) {
            return $friend->online_status === 'online';
        });
        
        view('multiplayer/lobby', [
            'available_matches' => $availableMatches->fetchAll(),
            'user_matches' => $userMatches,
            'online_friends' => $onlineFriends
        ]);
    }
    
    // Create new match
    function createMatch() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::validateCSRF();
            
            $userId = $_SESSION['user_id'];
            
            // Validate input
            $quizId = $_POST['quiz_id'] ?? 0;
            $maxPlayers = min(max((int)($_POST['max_players'] ?? 4), 2), 8);
            $matchType = $_POST['match_type'] ?? 'quick';
            $title = $_POST['title'] ?? 'Quick Match';
            
            if ($quizId <= 0) {
                $_SESSION['error'] = 'Please select a valid quiz';
                redirect('/multiplayer/create');
                exit;
            }
            
            // Create match
            $st = db()->prepare("
                INSERT INTO matches 
                (mode, match_type, created_by, title, max_players, quiz_id, settings, created_at)
                VALUES ('multiplayer', ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $settings = json_encode([
                'time_per_question' => (int)($_POST['time_per_question'] ?? 30),
                'allow_chat' => isset($_POST['allow_chat']),
                'show_scores' => isset($_POST['show_scores']),
                'random_questions' => isset($_POST['random_questions'])
            ]);
            
            $st->execute([$matchType, $userId, $title, $maxPlayers, $quizId, $settings]);
            $matchId = db()->lastInsertId();
            
            // Add creator as first player
            $st = db()->prepare("INSERT INTO match_players (match_id, user_id, joined_at) VALUES (?, ?, NOW())");
            $st->execute([$matchId, $userId]);
            
            // Update match player count
            $st = db()->prepare("UPDATE matches SET current_players = 1 WHERE id = ?");
            $st->execute([$matchId]);
            
            $_SESSION['success'] = 'Match created successfully';
            Auth::logAction('match_created', ['match_id' => $matchId, 'quiz_id' => $quizId]);
            
            redirect("/multiplayer/match/{$matchId}");
            exit;
        }
        
        // Get available quizzes
        $quizzes = db()->query("SELECT * FROM quizzes ORDER BY title")->fetchAll();
        
        $csrf_token = Auth::generateCSRF();
        view('multiplayer/create_match', [
            'quizzes' => $quizzes,
            'csrf_token' => $csrf_token
        ]);
    }
    
    // Join match
    function joinMatch($matchId) {
        $userId = $_SESSION['user_id'];
        
        // Check if match exists and is joinable
        $match = db()->prepare("
            SELECT m.*, q.title as quiz_title 
            FROM matches m
            JOIN quizzes q ON m.quiz_id = q.id
            WHERE m.id = ? AND m.status = 'waiting'
        ");
        $match->execute([$matchId]);
        $match = $match->fetch();
        
        if (!$match) {
            $_SESSION['error'] = 'Match not found or already started';
            redirect('/multiplayer/lobby');
            exit;
        }
        
        // Check if already joined
        $existing = db()->prepare("SELECT id FROM match_players WHERE match_id = ? AND user_id = ?");
        $existing->execute([$matchId, $userId]);
        
        if ($existing->fetch()) {
            $_SESSION['info'] = 'You are already in this match';
            redirect("/multiplayer/match/{$matchId}");
            exit;
        }
        
        // Check if match is full
        if ($match->current_players >= $match->max_players) {
            $_SESSION['error'] = 'Match is full';
            redirect('/multiplayer/lobby');
            exit;
        }
        
        // Add player to match
        $st = db()->prepare("
            INSERT INTO match_players (match_id, user_id, joined_at) 
            VALUES (?, ?, NOW())
        ");
        $st->execute([$matchId, $userId]);
        
        // Update match player count
        $st = db()->prepare("UPDATE matches SET current_players = current_players + 1 WHERE id = ?");
        $st->execute([$matchId]);
        
        // Add system message
        $userName = User::findById($userId)->name;
        $st = db()->prepare("
            INSERT INTO chat_messages (match_id, user_id, message, message_type, created_at)
            VALUES (?, ?, ?, 'system', NOW())
        ");
        $st->execute([$matchId, $userId, "{$userName} joined the match"]);
        
        $_SESSION['success'] = 'Joined match successfully';
        Auth::logAction('match_joined', ['match_id' => $matchId]);
        
        redirect("/multiplayer/match/{$matchId}");
    }
    
    // Leave match
    function leaveMatch($matchId) {
        $userId = $_SESSION['user_id'];
        
        // Check if user is in match
        $st = db()->prepare("SELECT id FROM match_players WHERE match_id = ? AND user_id = ?");
        $st->execute([$matchId, $userId]);
        
        if (!$st->fetch()) {
            $_SESSION['error'] = 'You are not in this match';
            redirect('/multiplayer/lobby');
            exit;
        }
        
        // Remove from match
        $st = db()->prepare("DELETE FROM match_players WHERE match_id = ? AND user_id = ?");
        $st->execute([$matchId, $userId]);
        
        // Update match player count
        $st = db()->prepare("UPDATE matches SET current_players = current_players - 1 WHERE id = ?");
        $st->execute([$matchId]);
        
        // Add system message
        $userName = User::findById($userId)->name;
        $st = db()->prepare("
            INSERT INTO chat_messages (match_id, user_id, message, message_type, created_at)
            VALUES (?, ?, ?, 'system', NOW())
        ");
        $st->execute([$matchId, $userId, "{$userName} left the match"]);
        
        $_SESSION['success'] = 'Left match successfully';
        Auth::logAction('match_left', ['match_id' => $matchId]);
        
        redirect('/multiplayer/lobby');
    }
    
    // Match room
    function matchRoom($matchId) {
        $userId = $_SESSION['user_id'];
        
        // Get match details
        $match = db()->prepare("
            SELECT 
                m.*, 
                q.title as quiz_title,
                q.duration as quiz_duration,
                u.name as creator_name
            FROM matches m
            JOIN quizzes q ON m.quiz_id = q.id
            JOIN users u ON m.created_by = u.id
            WHERE m.id = ?
        ");
        $match->execute([$matchId]);
        $match = $match->fetch();
        
        if (!$match) {
            $_SESSION['error'] = 'Match not found';
            redirect('/multiplayer/lobby');
            exit;
        }
        
        // Check if user is in match
        $player = db()->prepare("
            SELECT * FROM match_players 
            WHERE match_id = ? AND user_id = ?
        ");
        $player->execute([$matchId, $userId]);
        $player = $player->fetch();
        
        if (!$player) {
            $_SESSION['error'] = 'You are not in this match';
            redirect('/multiplayer/lobby');
            exit;
        }
        
        // Get all players
        $players = db()->prepare("
            SELECT 
                mp.*,
                u.name as player_name,
                u.avatar,
                u.online_status
            FROM match_players mp
            JOIN users u ON mp.user_id = u.id
            WHERE mp.match_id = ?
            ORDER BY mp.score DESC, mp.joined_at ASC
        ");
        $players->execute([$matchId]);
        
        // Get recent chat messages
        $chatMessages = db()->prepare("
            SELECT 
                cm.*,
                u.name as user_name,
                u.avatar
            FROM chat_messages cm
            JOIN users u ON cm.user_id = u.id
            WHERE cm.match_id = ?
            ORDER BY cm.created_at DESC
            LIMIT 50
        ");
        $chatMessages->execute([$matchId]);
        
        // Get questions for the quiz
        $questions = db()->prepare("
            SELECT * FROM questions 
            WHERE quiz_id = ? 
            ORDER BY RAND()
        ");
        $questions->execute([$match->quiz_id]);
        
        view('multiplayer/match_room', [
            'match' => $match,
            'player' => $player,
            'players' => $players->fetchAll(),
            'chat_messages' => array_reverse($chatMessages->fetchAll()),
            'questions' => $questions->fetchAll(),
            'settings' => json_decode($match->settings, true)
        ]);
    }
    
    // Start match (for creator)
    function startMatch($matchId) {
        $userId = $_SESSION['user_id'];
        
        // Check if user is the creator
        $match = db()->prepare("SELECT * FROM matches WHERE id = ? AND created_by = ?");
        $match->execute([$matchId, $userId]);
        $match = $match->fetch();
        
        if (!$match) {
            $_SESSION['error'] = 'Only the match creator can start the match';
            redirect("/multiplayer/match/{$matchId}");
            exit;
        }
        
        if ($match->status !== 'waiting') {
            $_SESSION['error'] = 'Match has already started';
            redirect("/multiplayer/match/{$matchId}");
            exit;
        }
        
        // Start the match
        $st = db()->prepare("
            UPDATE matches 
            SET status = 'active', start_time = NOW() 
            WHERE id = ?
        ");
        $st->execute([$matchId]);
        
        // Add system message
        $st = db()->prepare("
            INSERT INTO chat_messages (match_id, user_id, message, message_type, created_at)
            VALUES (?, ?, ?, 'system', NOW())
        ");
        $st->execute([$matchId, $userId, "Match started! Good luck!"]);
        
        $_SESSION['success'] = 'Match started successfully';
        Auth::logAction('match_started', ['match_id' => $matchId]);
        
        redirect("/multiplayer/match/{$matchId}");
    }
    
    // Submit answer
    function submitAnswer($matchId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect("/multiplayer/match/{$matchId}");
            exit;
        }
        
        Auth::validateCSRF();
        
        $userId = $_SESSION['user_id'];
        $questionId = $_POST['question_id'] ?? 0;
        $chosenAnswer = $_POST['answer'] ?? '';
        
        // Validate input
        if ($questionId <= 0 || !in_array($chosenAnswer, ['a', 'b', 'c', 'd'])) {
            echo json_encode(['success' => false, 'error' => 'Invalid input']);
            exit;
        }
        
        // Check if match is active and user is participating
        $match = db()->prepare("
            SELECT m.*, mp.id as player_id 
            FROM matches m
            JOIN match_players mp ON m.id = mp.match_id
            WHERE m.id = ? AND mp.user_id = ? AND m.status = 'active'
        ");
        $match->execute([$matchId, $userId]);
        $match = $match->fetch();
        
        if (!$match) {
            echo json_encode(['success' => false, 'error' => 'Match not found or not active']);
            exit;
        }
        
        // Check if question exists and belongs to the quiz
        $question = db()->prepare("
            SELECT * FROM questions 
            WHERE id = ? AND quiz_id = ?
        ");
        $question->execute([$questionId, $match->quiz_id]);
        $question = $question->fetch();
        
        if (!$question) {
            echo json_encode(['success' => false, 'error' => 'Invalid question']);
            exit;
        }
        
        // Check if already answered
        $existing = db()->prepare("
            SELECT id FROM answers 
            WHERE user_id = ? AND match_id = ? AND question_id = ?
        ");
        $existing->execute([$userId, $matchId, $questionId]);
        
        if ($existing->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Question already answered']);
            exit;
        }
        
        // Check if answer is correct
        $isCorrect = $chosenAnswer === $question->correct;
        
        // Calculate score (base score + time bonus)
        $baseScore = $isCorrect ? 100 : 0;
        $timeBonus = 0; // Would calculate based on time taken
        
        $score = $baseScore + $timeBonus;
        
        // Save answer
        $st = db()->prepare("
            INSERT INTO answers 
            (user_id, quiz_id, question_id, match_id, chosen, is_correct, score, answered_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $st->execute([$userId, $match->quiz_id, $questionId, $matchId, $chosenAnswer, $isCorrect, $score]);
        
        // Update player score and stats
        $st = db()->prepare("
            UPDATE match_players 
            SET 
                score = score + ?,
                correct_answers = correct_answers + ?,
                total_answers = total_answers + 1
            WHERE id = ?
        ");
        $st->execute([$score, $isCorrect ? 1 : 0, $match->player_id]);
        
        // Check if match is complete (all questions answered by all players)
        $this->checkMatchCompletion($matchId);
        
        echo json_encode([
            'success' => true,
            'correct' => $isCorrect,
            'score' => $score,
            'explanation' => $question->explanation
        ]);
    }
    
    // Send chat message
    function sendMessage($matchId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect("/multiplayer/match/{$matchId}");
            exit;
        }
        
        Auth::validateCSRF();
        
        $userId = $_SESSION['user_id'];
        $message = $_POST['message'] ?? '';
        
        if (empty(trim($message))) {
            echo json_encode(['success' => false, 'error' => 'Message cannot be empty']);
            exit;
        }
        
        // Check if user is in match
        $player = db()->prepare("
            SELECT id FROM match_players 
            WHERE match_id = ? AND user_id = ?
        ");
        $player->execute([$matchId, $userId]);
        
        if (!$player->fetch()) {
            echo json_encode(['success' => false, 'error' => 'You are not in this match']);
            exit;
        }
        
        // Check if chat is enabled for this match
        $match = db()->prepare("SELECT settings FROM matches WHERE id = ?");
        $match->execute([$matchId]);
        $match = $match->fetch();
        
        if ($match) {
            $settings = json_decode($match->settings, true);
            if (!($settings['allow_chat'] ?? true)) {
                echo json_encode(['success' => false, 'error' => 'Chat is disabled for this match']);
                exit;
            }
        }
        
        // Save message
        $st = db()->prepare("
            INSERT INTO chat_messages 
            (match_id, user_id, message, message_type, created_at)
            VALUES (?, ?, ?, 'text', NOW())
        ");
        $st->execute([$matchId, $userId, $message]);
        
        echo json_encode(['success' => true]);
    }
    
    // Get match updates (for AJAX polling)
    function getUpdates($matchId) {
        $userId = $_SESSION['user_id'];
        $lastUpdate = $_GET['last_update'] ?? 0;
        
        // Check if user is in match
        $player = db()->prepare("
            SELECT id FROM match_players 
            WHERE match_id = ? AND user_id = ?
        ");
        $player->execute([$matchId, $userId]);
        
        if (!$player->fetch()) {
            echo json_encode(['error' => 'Access denied']);
            exit;
        }
        
        // Get new messages
        $messages = db()->prepare("
            SELECT 
                cm.*,
                u.name as user_name,
                u.avatar
            FROM chat_messages cm
            JOIN users u ON cm.user_id = u.id
            WHERE cm.match_id = ? AND cm.id > ?
            ORDER BY cm.created_at ASC
        ");
        $messages->execute([$matchId, $lastUpdate]);
        
        // Get updated player scores
        $players = db()->prepare("
            SELECT 
                mp.*,
                u.name as player_name,
                u.avatar
            FROM match_players mp
            JOIN users u ON mp.user_id = u.id
            WHERE mp.match_id = ?
            ORDER BY mp.score DESC
        ");
        $players->execute([$matchId]);
        
        // Get match status
        $match = db()->prepare("SELECT status, start_time FROM matches WHERE id = ?");
        $match->execute([$matchId]);
        $match = $match->fetch();
        
        echo json_encode([
            'messages' => $messages->fetchAll(),
            'players' => $players->fetchAll(),
            'match_status' => $match->status,
            'last_update' => time()
        ]);
    }
    
    // Private helper methods
    private function checkMatchCompletion($matchId) {
        // Get total questions for the quiz
        $totalQuestions = db()->prepare("
            SELECT COUNT(*) 
            FROM questions q
            JOIN matches m ON q.quiz_id = m.quiz_id
            WHERE m.id = ?
        ");
        $totalQuestions->execute([$matchId]);
        $totalQuestions = $totalQuestions->fetchColumn();
        
        // Get number of players
        $playerCount = db()->prepare("
            SELECT COUNT(*) FROM match_players WHERE match_id = ?
        ");
        $playerCount->execute([$matchId]);
        $playerCount = $playerCount->fetchColumn();
        
        // Get total answers submitted
        $totalAnswers = db()->prepare("
            SELECT COUNT(DISTINCT CONCAT(user_id, '-', question_id))
            FROM answers
            WHERE match_id = ?
        ");
        $totalAnswers->execute([$matchId]);
        $totalAnswers = $totalAnswers->fetchColumn();
        
        // Check if all players answered all questions
        if ($totalAnswers >= ($totalQuestions * $playerCount)) {
            $this->finalizeMatch($matchId);
        }
    }
    
    private function finalizeMatch($matchId) {
        // Get final scores
        $players = db()->prepare("
            SELECT 
                mp.*,
                u.name as player_name
            FROM match_players mp
            JOIN users u ON mp.user_id = u.id
            WHERE mp.match_id = ?
            ORDER BY mp.score DESC
        ");
        $players->execute([$matchId]);
        $players = $players->fetchAll();
        
        // Determine winners
        $maxScore = $players[0]->score ?? 0;
        $winners = array_filter($players, function($player) use ($maxScore) {
            return $player->score === $maxScore;
        });
        
        // Update match status and player results
        $st = db()->prepare("UPDATE matches SET status = 'completed', end_time = NOW() WHERE id = ?");
        $st->execute([$matchId]);
        
        foreach ($players as $player) {
            $result = $player->score === $maxScore ? 'win' : 'lose';
            $st = db()->prepare("
                UPDATE match_players 
                SET result = ?, finished_at = NOW() 
                WHERE id = ?
            ");
            $st->execute([$result, $player->id]);
        }
        
        // Award achievements
        foreach ($winners as $winner) {
            $this->awardAchievement($winner->user_id, 'matches_won', 1);
        }
        
        // Add system message
        $winnerNames = implode(', ', array_column($winners, 'player_name'));
        $st = db()->prepare("
            INSERT INTO chat_messages (match_id, user_id, message, message_type, created_at)
            VALUES (?, ?, ?, 'system', NOW())
        ");
        $st->execute([$matchId, 0, "Match completed! Winners: {$winnerNames}"]);
        
        Auth::logAction('match_completed', ['match_id' => $matchId, 'winners' => array_column($winners, 'user_id')]);
    }
    
    private function awardAchievement($userId, $metric, $threshold) {
        // Check if user already has this achievement
        $existing = db()->prepare("
            SELECT uma.achievement_id 
            FROM user_multiplayer_achievements uma
            JOIN multiplayer_achievements ma ON uma.achievement_id = ma.id
            WHERE uma.user_id = ? AND ma.metric = ? AND ma.threshold <= ?
        ");
        $existing->execute([$userId, $metric, $threshold]);
        
        if ($existing->fetch()) {
            return; // Already has achievement
        }
        
        // Get achievement
        $achievement = db()->prepare("
            SELECT id FROM multiplayer_achievements 
            WHERE metric = ? AND threshold <= ?
            ORDER BY threshold DESC 
            LIMIT 1
        ");
        $achievement->execute([$metric, $threshold]);
        $achievement = $achievement->fetch();
        
        if ($achievement) {
            // Award achievement
            $st = db()->prepare("
                INSERT INTO user_multiplayer_achievements (user_id, achievement_id, earned_at)
                VALUES (?, ?, NOW())
            ");
            $st->execute([$userId, $achievement->id]);
            
            Auth::logAction('achievement_earned', [
                'user_id' => $userId,
                'achievement_id' => $achievement->id
            ]);
        }
    }
}