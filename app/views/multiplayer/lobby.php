<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Online Friends</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($online_friends)): ?>
                        <p class="text-muted">No friends online</p>
                    <?php else: ?>
                        <?php foreach ($online_friends as $friend): ?>
                        <div class="d-flex align-items-center mb-2 p-2 border rounded">
                            <div class="mr-2">
                                <span class="badge badge-success">●</span>
                            </div>
                            <div class="flex-grow-1">
                                <strong><?= htmlspecialchars($friend->name) ?></strong>
                                <br>
                                <small class="text-muted"><?= ucfirst($friend->online_status) ?></small>
                            </div>
                            <div>
                                <button class="btn btn-sm btn-primary invite-friend" data-friend-id="<?= $friend->id ?>">
                                    <i class="fas fa-paper-plane"></i> Invite
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Your Active Matches</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($user_matches)): ?>
                        <p class="text-muted">No active matches</p>
                    <?php else: ?>
                        <?php foreach ($user_matches as $match): ?>
                        <div class="mb-2 p-2 border rounded">
                            <h6><?= htmlspecialchars($match->title) ?></h6>
                            <small class="text-muted">
                                Quiz: <?= htmlspecialchars($match->quiz_title) ?><br>
                                Score: <?= $match->score ?> points
                            </small>
                            <br>
                            <a href="/multiplayer/match/<?= $match->id ?>" class="btn btn-sm btn-primary mt-1">
                                Join Match
                            </a>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Multiplayer Lobby</h1>
                <div>
                    <a href="/multiplayer/create" class="btn btn-success">
                        <i class="fas fa-plus"></i> Create Match
                    </a>
                    <button class="btn btn-primary" onclick="refreshLobby()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>

            <!-- Available Matches -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Available Matches</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($available_matches)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-gamepad fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No matches available</h5>
                            <p class="text-muted">Why not create one?</p>
                            <a href="/multiplayer/create" class="btn btn-primary">Create Match</a>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($available_matches as $match): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($match->title) ?></h5>
                                        <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($match->quiz_title) ?></h6>
                                        
                                        <div class="mb-2">
                                            <span class="badge badge-info"><?= ucfirst($match->difficulty) ?></span>
                                            <span class="badge badge-secondary"><?= ucfirst($match->match_type) ?></span>
                                        </div>

                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="fas fa-users"></i> <?= $match->current_players ?>/<?= $match->max_players ?> players
                                            </small>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-user"></i> Created by <?= htmlspecialchars($match->creator_name) ?>
                                            </small>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center">
                                            <a href="/multiplayer/join/<?= $match->id ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-sign-in-alt"></i> Join Match
                                            </a>
                                            <small class="text-muted">
                                                <i class="fas fa-clock"></i> <?= time_ago($match->created_at) ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Match -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Match</h6>
                </div>
                <div class="card-body text-center">
                    <p class="text-muted">Join a random match instantly!</p>
                    <button class="btn btn-lg btn-success" onclick="joinQuickMatch()">
                        <i class="fas fa-bolt"></i> Find Match
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Invite Friend Modal -->
<div class="modal fade" id="inviteFriendModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Invite Friend to Match</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="inviteFriendForm">
                    <input type="hidden" id="inviteFriendId" name="friend_id">
                    <div class="form-group">
                        <label>Select Match:</label>
                        <select class="form-control" id="inviteMatchId" name="match_id" required>
                            <option value="">Choose a match...</option>
                            <?php foreach ($available_matches as $match): ?>
                            <option value="<?= $match->id ?>">
                                <?= htmlspecialchars($match->title) ?> - <?= htmlspecialchars($match->quiz_title) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Message (optional):</label>
                        <textarea class="form-control" name="message" rows="2" placeholder="Hey, want to join my match?"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="sendInvitation()">Send Invitation</button>
            </div>
        </div>
    </div>
</div>

<script>
// WebSocket connection for real-time updates
let socket;
let wsToken = '';

// Initialize WebSocket connection
function initWebSocket() {
    // Get JWT token first
    fetch('/api/token', {
        method: 'POST',
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.token) {
            wsToken = data.token;
            connectWebSocket();
        }
    })
    .catch(error => console.error('Error getting token:', error));
}

function connectWebSocket() {
    socket = io('ws://localhost:3001', {
        auth: {
            token: wsToken
        }
    });

    socket.on('connect', function() {
        console.log('Connected to WebSocket server');
        updateOnlineStatus('online');
    });

    socket.on('disconnect', function() {
        console.log('Disconnected from WebSocket server');
    });

    socket.on('friend_status_update', function(data) {
        // Update friend status in the UI
        console.log('Friend status update:', data);
        refreshLobby();
    });

    socket.on('new_match', function(data) {
        // New match created
        console.log('New match available:', data);
        refreshLobby();
    });
}

function updateOnlineStatus(status) {
    if (socket && socket.connected) {
        socket.emit('update_status', { status: status });
    }
}

function refreshLobby() {
    location.reload();
}

function joinQuickMatch() {
    // Find a match with available slots
    const availableMatches = document.querySelectorAll('[href^="/multiplayer/join/"]');
    if (availableMatches.length > 0) {
        availableMatches[0].click();
    } else {
        alert('No matches available. Please create a new match.');
    }
}

// Invite friend functionality
document.querySelectorAll('.invite-friend').forEach(button => {
    button.addEventListener('click', function() {
        const friendId = this.getAttribute('data-friend-id');
        document.getElementById('inviteFriendId').value = friendId;
        $('#inviteFriendModal').modal('show');
    });
});

function sendInvitation() {
    const form = document.getElementById('inviteFriendForm');
    const formData = new FormData(form);
    
    fetch('/friends/invite-to-match/' + formData.get('friend_id'), {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#inviteFriendModal').modal('hide');
            alert('Invitation sent successfully!');
        } else {
            alert('Error: ' + (data.error || 'Failed to send invitation'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error sending invitation');
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initWebSocket();
    
    // Update online status
    updateOnlineStatus('online');
    
    // Refresh lobby every 30 seconds
    setInterval(refreshLobby, 30000);
});

// Update status when leaving page
window.addEventListener('beforeunload', function() {
    updateOnlineStatus('offline');
});
</script>

<style>
.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}

.badge-success {
    background-color: #28a745;
}

.online-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
}
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>