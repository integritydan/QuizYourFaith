const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const jwt = require('jsonwebtoken');
const mysql = require('mysql2/promise');
const rateLimit = require('express-rate-limit');

// Configuration
const config = {
    port: process.env.WS_PORT || 3001,
    jwtSecret: process.env.JWT_SECRET || 'your-secret-key',
    db: {
        host: process.env.DB_HOST || 'localhost',
        user: process.env.DB_USER || 'root',
        password: process.env.DB_PASSWORD || '',
        database: process.env.DB_NAME || 'qyf_db'
    }
};

// Database connection pool
const pool = mysql.createPool(config.db);

// Express app setup
const app = express();
const server = http.createServer(app);
const io = socketIo(server, {
    cors: {
        origin: process.env.FRONTEND_URL || "http://localhost",
        methods: ["GET", "POST"]
    }
});

// Rate limiting
const limiter = rateLimit({
    windowMs: 15 * 60 * 1000, // 15 minutes
    max: 100, // limit each IP to 100 requests per windowMs
    message: 'Too many requests from this IP'
});

app.use(limiter);
app.use(express.json());

// Authentication middleware
async function authenticateSocket(socket, next) {
    try {
        const token = socket.handshake.auth.token;
        if (!token) {
            return next(new Error('Authentication error'));
        }
        
        const decoded = jwt.verify(token, config.jwtSecret);
        const user = await getUserById(decoded.userId);
        
        if (!user) {
            return next(new Error('User not found'));
        }
        
        socket.userId = user.id;
        socket.userName = user.name;
        socket.userRole = user.role;
        next();
    } catch (err) {
        next(new Error('Authentication error'));
    }
}

// Get user by ID
async function getUserById(userId) {
    const [rows] = await pool.execute('SELECT * FROM users WHERE id = ?', [userId]);
    return rows[0];
}

// Socket authentication
io.use(authenticateSocket);

// Connected clients
const connectedUsers = new Map();
const activeMatches = new Map();

// Socket connection handler
io.on('connection', (socket) => {
    console.log(`User ${socket.userName} (${socket.userId}) connected`);
    
    // Add to connected users
    connectedUsers.set(socket.userId, {
        socketId: socket.id,
        userId: socket.userId,
        userName: socket.userName,
        userRole: socket.userRole,
        onlineStatus: 'online'
    });
    
    // Update user online status
    updateUserOnlineStatus(socket.userId, 'online');
    
    // Join user to their personal room
    socket.join(`user_${socket.userId}`);
    
    // Handle joining a match room
    socket.on('join_match', async (data) => {
        try {
            const { matchId } = data;
            
            // Verify user is in the match
            const [playerRows] = await pool.execute(
                'SELECT * FROM match_players WHERE match_id = ? AND user_id = ?',
                [matchId, socket.userId]
            );
            
            if (playerRows.length === 0) {
                socket.emit('error', { message: 'You are not in this match' });
                return;
            }
            
            // Join match room
            socket.join(`match_${matchId}`);
            
            // Update user's current match
            connectedUsers.set(socket.userId, {
                ...connectedUsers.get(socket.userId),
                currentMatchId: matchId
            });
            
            // Notify others in the match
            socket.to(`match_${matchId}`).emit('user_joined', {
                userId: socket.userId,
                userName: socket.userName,
                message: `${socket.userName} joined the match`
            });
            
            // Send current match state
            const matchState = await getMatchState(matchId);
            socket.emit('match_state', matchState);
            
            console.log(`User ${socket.userName} joined match ${matchId}`);
            
        } catch (error) {
            console.error('Error joining match:', error);
            socket.emit('error', { message: 'Failed to join match' });
        }
    });
    
    // Handle leaving a match room
    socket.on('leave_match', async (data) => {
        try {
            const { matchId } = data;
            
            // Leave match room
            socket.leave(`match_${matchId}`);
            
            // Update user's current match
            const userData = connectedUsers.get(socket.userId);
            if (userData) {
                delete userData.currentMatchId;
                connectedUsers.set(socket.userId, userData);
            }
            
            // Notify others in the match
            socket.to(`match_${matchId}`).emit('user_left', {
                userId: socket.userId,
                userName: socket.userName,
                message: `${socket.userName} left the match`
            });
            
            console.log(`User ${socket.userName} left match ${matchId}`);
            
        } catch (error) {
            console.error('Error leaving match:', error);
            socket.emit('error', { message: 'Failed to leave match' });
        }
    });
    
    // Handle chat messages
    socket.on('send_message', async (data) => {
        try {
            const { matchId, message } = data;
            
            // Rate limiting
            if (!socket.lastMessageTime || Date.now() - socket.lastMessageTime < 1000) {
                if (socket.messageCount && socket.messageCount > 5) {
                    socket.emit('error', { message: 'Message rate limit exceeded' });
                    return;
                }
                socket.messageCount = (socket.messageCount || 0) + 1;
            } else {
                socket.messageCount = 1;
            }
            socket.lastMessageTime = Date.now();
            
            // Verify user is in the match
            const [playerRows] = await pool.execute(
                'SELECT * FROM match_players WHERE match_id = ? AND user_id = ?',
                [matchId, socket.userId]
            );
            
            if (playerRows.length === 0) {
                socket.emit('error', { message: 'You are not in this match' });
                return;
            }
            
            // Check if chat is enabled for this match
            const [matchRows] = await pool.execute(
                'SELECT settings FROM matches WHERE id = ?',
                [matchId]
            );
            
            if (matchRows.length > 0) {
                const settings = JSON.parse(matchRows[0].settings || '{}');
                if (!settings.allow_chat) {
                    socket.emit('error', { message: 'Chat is disabled for this match' });
                    return;
                }
            }
            
            // Moderate message (simple profanity filter)
            const moderatedMessage = moderateMessage(message);
            
            // Save message to database
            await pool.execute(
                'INSERT INTO chat_messages (match_id, user_id, message, message_type, created_at) VALUES (?, ?, ?, ?, NOW())',
                [matchId, socket.userId, moderatedMessage, 'text']
            );
            
            // Broadcast message to all users in the match
            io.to(`match_${matchId}`).emit('new_message', {
                userId: socket.userId,
                userName: socket.userName,
                message: moderatedMessage,
                timestamp: new Date().toISOString()
            });
            
        } catch (error) {
            console.error('Error sending message:', error);
            socket.emit('error', { message: 'Failed to send message' });
        }
    });
    
    // Handle answer submission
    socket.on('submit_answer', async (data) => {
        try {
            const { matchId, questionId, answer } = data;
            
            // Verify user is in the match
            const [playerRows] = await pool.execute(
                'SELECT * FROM match_players WHERE match_id = ? AND user_id = ?',
                [matchId, socket.userId]
            );
            
            if (playerRows.length === 0) {
                socket.emit('error', { message: 'You are not in this match' });
                return;
            }
            
            // Check if question exists and belongs to the quiz
            const [questionRows] = await pool.execute(
                'SELECT * FROM questions q JOIN matches m ON q.quiz_id = m.quiz_id WHERE q.id = ? AND m.id = ?',
                [questionId, matchId]
            );
            
            if (questionRows.length === 0) {
                socket.emit('error', { message: 'Invalid question' });
                return;
            }
            
            const question = questionRows[0];
            
            // Check if already answered
            const [existingRows] = await pool.execute(
                'SELECT id FROM answers WHERE user_id = ? AND match_id = ? AND question_id = ?',
                [socket.userId, matchId, questionId]
            );
            
            if (existingRows.length > 0) {
                socket.emit('error', { message: 'Question already answered' });
                return;
            }
            
            // Check if answer is correct
            const isCorrect = answer === question.correct;
            const score = isCorrect ? 100 : 0; // Base score, could add time bonus
            
            // Save answer
            await pool.execute(
                'INSERT INTO answers (user_id, quiz_id, question_id, match_id, chosen, is_correct, score, answered_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())',
                [socket.userId, question.quiz_id, questionId, matchId, answer, isCorrect, score]
            );
            
            // Update player score
            await pool.execute(
                'UPDATE match_players SET score = score + ?, correct_answers = correct_answers + ?, total_answers = total_answers + 1 WHERE user_id = ? AND match_id = ?',
                [score, isCorrect ? 1 : 0, socket.userId, matchId]
            );
            
            // Notify user of result
            socket.emit('answer_result', {
                correct: isCorrect,
                score: score,
                explanation: question.explanation
            });
            
            // Update all players with new scores
            const players = await getMatchPlayers(matchId);
            io.to(`match_${matchId}`).emit('players_update', players);
            
            // Check if match is complete
            await checkMatchCompletion(matchId);
            
        } catch (error) {
            console.error('Error submitting answer:', error);
            socket.emit('error', { message: 'Failed to submit answer' });
        }
    });
    
    // Handle online status updates
    socket.on('update_status', async (data) => {
        try {
            const { status } = data;
            
            if (!['online', 'away', 'busy', 'offline'].includes(status)) {
                socket.emit('error', { message: 'Invalid status' });
                return;
            }
            
            // Update user status
            connectedUsers.set(socket.userId, {
                ...connectedUsers.get(socket.userId),
                onlineStatus: status
            });
            
            await updateUserOnlineStatus(socket.userId, status);
            
            // Notify friends of status change
            const friends = await getUserFriends(socket.userId);
            friends.forEach(friend => {
                if (connectedUsers.has(friend.id)) {
                    const friendSocketId = connectedUsers.get(friend.id).socketId;
                    io.to(friendSocketId).emit('friend_status_update', {
                        userId: socket.userId,
                        userName: socket.userName,
                        status: status
                    });
                }
            });
            
        } catch (error) {
            console.error('Error updating status:', error);
            socket.emit('error', { message: 'Failed to update status' });
        }
    });
    
    // Handle friend invitations
    socket.on('invite_friend', async (data) => {
        try {
            const { friendId, matchId } = data;
            
            // Verify they are friends
            const [friendRows] = await pool.execute(
                'SELECT * FROM friends WHERE ((user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)) AND status = ?',
                [socket.userId, friendId, friendId, socket.userId, 'accepted']
            );
            
            if (friendRows.length === 0) {
                socket.emit('error', { message: 'You can only invite friends' });
                return;
            }
            
            // Check if friend is online
            if (!connectedUsers.has(friendId)) {
                socket.emit('error', { message: 'Friend is offline' });
                return;
            }
            
            // Send invitation
            const friendSocketId = connectedUsers.get(friendId).socketId;
            io.to(friendSocketId).emit('match_invitation', {
                fromUserId: socket.userId,
                fromUserName: socket.userName,
                matchId: matchId,
                message: `${socket.userName} invited you to join a match`
            });
            
        } catch (error) {
            console.error('Error inviting friend:', error);
            socket.emit('error', { message: 'Failed to send invitation' });
        }
    });
    
    // Handle disconnection
    socket.on('disconnect', async () => {
        console.log(`User ${socket.userName} (${socket.userId}) disconnected`);
        
        // Get user's current match before removing from connected users
        const userData = connectedUsers.get(socket.userId);
        const currentMatchId = userData ? userData.currentMatchId : null;
        
        // Clear chat messages if user was in a match
        if (currentMatchId) {
            try {
                // Clear user's chat messages from the match
                await pool.execute(
                    'DELETE FROM chat_messages WHERE user_id = ? AND match_id = ?',
                    [socket.userId, currentMatchId]
                );
                
                // Notify other players in the match about chat clearing
                socket.to(`match_${currentMatchId}`).emit('chat_cleared', {
                    userId: socket.userId,
                    userName: socket.userName,
                    message: `${socket.userName} left and their chat history was cleared`
                });
                
                // Check if match is now empty and clear all chat if so
                const [playerRows] = await pool.execute(
                    'SELECT COUNT(*) FROM match_players WHERE match_id = ?',
                    [currentMatchId]
                );
                
                if (playerRows[0]['COUNT(*)'] == 0) {
                    // Clear all chat messages from the match
                    await pool.execute(
                        'DELETE FROM chat_messages WHERE match_id = ?',
                        [currentMatchId]
                    );
                }
                
            } catch (error) {
                console.error('Error clearing chat on disconnect:', error);
            }
            
            // Leave match room
            socket.leave(`match_${currentMatchId}`);
        }
        
        // Update online status
        await updateUserOnlineStatus(socket.userId, 'offline');
        
        // Remove from connected users
        connectedUsers.delete(socket.userId);
        
        // Notify friends of offline status
        try {
            const friends = await getUserFriends(socket.userId);
            friends.forEach(friend => {
                if (connectedUsers.has(friend.id)) {
                    const friendSocketId = connectedUsers.get(friend.id).socketId;
                    io.to(friendSocketId).emit('friend_status_update', {
                        userId: socket.userId,
                        userName: socket.userName,
                        status: 'offline'
                    });
                }
            });
        } catch (error) {
            console.error('Error notifying friends of disconnect:', error);
        }
    });
});

// Helper functions
async function getMatchState(matchId) {
    const [matchRows] = await pool.execute(
        'SELECT * FROM matches WHERE id = ?',
        [matchId]
    );
    
    if (matchRows.length === 0) {
        return null;
    }
    
    const players = await getMatchPlayers(matchId);
    const messages = await getMatchMessages(matchId);
    
    return {
        match: matchRows[0],
        players: players,
        messages: messages
    };
}

async function getMatchPlayers(matchId) {
    const [rows] = await pool.execute(
        `SELECT 
            mp.*,
            u.name as player_name,
            u.avatar,
            u.online_status
        FROM match_players mp
        JOIN users u ON mp.user_id = u.id
        WHERE mp.match_id = ?
        ORDER BY mp.score DESC, mp.joined_at ASC`,
        [matchId]
    );
    return rows;
}

async function getMatchMessages(matchId, limit = 50) {
    const [rows] = await pool.execute(
        `SELECT 
            cm.*,
            u.name as user_name,
            u.avatar
        FROM chat_messages cm
        JOIN users u ON cm.user_id = u.id
        WHERE cm.match_id = ?
        ORDER BY cm.created_at DESC
        LIMIT ?`,
        [matchId, limit]
    );
    return rows.reverse();
}

async function getUserFriends(userId) {
    const [rows] = await pool.execute(
        `SELECT DISTINCT
            u.id,
            u.name,
            u.online_status
        FROM friends f
        JOIN users u ON (f.friend_id = u.id OR f.user_id = u.id)
        WHERE (f.user_id = ? OR f.friend_id = ?) 
        AND u.id != ?
        AND f.status = ?`,
        [userId, userId, userId, 'accepted']
    );
    return rows;
}

async function updateUserOnlineStatus(userId, status) {
    await pool.execute(
        'UPDATE users SET online_status = ?, last_seen_at = NOW() WHERE id = ?',
        [status, userId]
    );
}

async function checkMatchCompletion(matchId) {
    // Get total questions for the quiz
    const [totalQuestionsRows] = await pool.execute(
        `SELECT COUNT(*) as total 
        FROM questions q
        JOIN matches m ON q.quiz_id = m.quiz_id
        WHERE m.id = ?`,
        [matchId]
    );
    
    const totalQuestions = totalQuestionsRows[0].total;
    
    // Get number of players
    const [playerCountRows] = await pool.execute(
        'SELECT COUNT(*) as count FROM match_players WHERE match_id = ?',
        [matchId]
    );
    
    const playerCount = playerCountRows[0].count;
    
    // Get total answers submitted
    const [totalAnswersRows] = await pool.execute(
        `SELECT COUNT(DISTINCT CONCAT(user_id, '-', question_id)) as total
        FROM answers
        WHERE match_id = ?`,
        [matchId]
    );
    
    const totalAnswers = totalAnswersRows[0].total;
    
    // Check if all players answered all questions
    if (totalAnswers >= (totalQuestions * playerCount)) {
        await finalizeMatch(matchId);
    }
}

async function finalizeMatch(matchId) {
    // Get final scores
    const [players] = await pool.execute(
        `SELECT 
            mp.*,
            u.name as player_name
        FROM match_players mp
        JOIN users u ON mp.user_id = u.id
        WHERE mp.match_id = ?
        ORDER BY mp.score DESC`,
        [matchId]
    );
    
    if (players.length === 0) return;
    
    // Determine winner(s)
    const maxScore = players[0].score;
    const winners = players.filter(p => p.score === maxScore);
    
    // Update match status and player results
    await pool.execute(
        'UPDATE matches SET status = ?, end_time = NOW() WHERE id = ?',
        ['completed', matchId]
    );
    
    for (const player of players) {
        const result = player.score === maxScore ? 'win' : 'lose';
        await pool.execute(
            'UPDATE match_players SET result = ?, finished_at = NOW() WHERE id = ?',
            [result, player.id]
        );
    }
    
    // Notify all players
    const winnerNames = winners.map(w => w.player_name).join(', ');
    io.to(`match_${matchId}`).emit('match_completed', {
        winners: winners.map(w => ({ userId: w.user_id, userName: w.player_name })),
        message: `Match completed! Winners: ${winnerNames}`
    });
    
    console.log(`Match ${matchId} completed. Winners: ${winnerNames}`);
}

function moderateMessage(message) {
    // Simple profanity filter - replace with more sophisticated solution
    const profanities = ['badword1', 'badword2', 'badword3'];
    let moderated = message;
    
    profanities.forEach(word => {
        const regex = new RegExp(word, 'gi');
        moderated = moderated.replace(regex, '*'.repeat(word.length));
    });
    
    return moderated;
}

// Start server
server.listen(config.port, () => {
    console.log(`WebSocket server running on port ${config.port}`);
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('SIGTERM received, shutting down gracefully');
    server.close(() => {
        console.log('WebSocket server closed');
        pool.end();
        process.exit(0);
    });
});