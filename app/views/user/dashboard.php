<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Welcome back, <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>!</h1>
        <div>
            <span class="badge badge-success">
                <i class="fas fa-circle"></i> Online
            </span>
            <a href="/multiplayer/lobby" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-gamepad fa-sm text-white-50"></i> Play Multiplayer
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Matches Won
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats->matches_won ?? 0 ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-trophy fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Score
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats->total_score ?? 0) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Friends
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats->friends_count ?? 0 ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-friends fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Achievements
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats->achievements_count ?? 0 ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-medal fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Active Matches -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Your Active Matches</h6>
                    <a href="/multiplayer/lobby" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Join More
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($active_matches)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-gamepad fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No active matches</h5>
                            <p class="text-muted">Join a multiplayer match to get started!</p>
                            <a href="/multiplayer/lobby" class="btn btn-primary">Browse Matches</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Match</th>
                                        <th>Quiz</th>
                                        <th>Your Score</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($active_matches as $match): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($match->title) ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <?= $match->current_players ?>/<?= $match->max_players ?> players
                                            </small>
                                        </td>
                                        <td><?= htmlspecialchars($match->quiz_title) ?></td>
                                        <td>
                                            <span class="badge badge-primary"><?= $match->score ?> pts</span>
                                            <br>
                                            <small class="text-muted">
                                                <?= $match->correct_answers ?>/<?= $match->total_answers ?> correct
                                            </small>
                                        </td>
                                        <td>
                                            <?php if ($match->status === 'waiting'): ?>
                                                <span class="badge badge-warning">Waiting</span>
                                            <?php elseif ($match->status === 'active'): ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="/multiplayer/match/<?= $match->id ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-play"></i> Play
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Matches -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Match History</h6>
                    <a href="/user/match-history" class="btn btn-outline-primary btn-sm">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_matches)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No recent matches</h5>
                            <p class="text-muted">Your match history will appear here</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Match</th>
                                        <th>Result</th>
                                        <th>Score</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_matches as $match): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($match->title ?? 'Quick Match') ?></td>
                                        <td>
                                            <?php if ($match->result === 'win'): ?>
                                                <span class="badge badge-success">Win</span>
                                            <?php elseif ($match->result === 'lose'): ?>
                                                <span class="badge badge-danger">Loss</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary"><?= ucfirst($match->result) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $match->score ?> points</td>
                                        <td><?= date('M j, Y', strtotime($match->finished_at)) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Online Friends -->
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
                                <span class="online-indicator online"></span>
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
                    <div class="text-center mt-3">
                        <a href="/friends" class="btn btn-outline-primary btn-sm">Manage Friends</a>
                    </div>
                </div>
            </div>

            <!-- Available Tournaments -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Available Tournaments</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($available_tournaments)): ?>
                        <p class="text-muted">No tournaments available</p>
                    <?php else: ?>
                        <?php foreach ($available_tournaments as $tournament): ?>
                        <div class="mb-3 p-2 border rounded">
                            <h6 class="mb-1"><?= htmlspecialchars($tournament->name) ?></h6>
                            <small class="text-muted">
                                <?= $tournament->participant_count ?>/<?= $tournament->max_participants ?> participants
                            </small>
                            <br>
                            <small class="text-muted">
                                Starts: <?= date('M j, H:i', strtotime($tournament->start_time)) ?>
                            </small>
                            <div class="mt-2">
                                <a href="/tournaments/<?= $tournament->id ?>" class="btn btn-sm btn-primary">
                                    Join Tournament
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <div class="text-center mt-3">
                        <a href="/tournaments" class="btn btn-outline-primary btn-sm">View All</a>
                    </div>
                </div>
            </div>

            <!-- Recent Achievements -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Achievements</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($achievements)): ?>
                        <p class="text-muted">No achievements yet</p>
                    <?php else: ?>
                        <?php foreach (array_slice($achievements, 0, 3) as $achievement): ?>
                        <div class="mb-2 p-2 border rounded">
                            <div class="d-flex align-items-center">
                                <div class="mr-2">
                                    <i class="fas fa-medal text-warning"></i>
                                </div>
                                <div>
                                    <strong><?= htmlspecialchars($achievement->name) ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        Earned <?= date('M j', strtotime($achievement->earned_at)) ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <div class="text-center mt-3">
                        <a href="/user/achievements" class="btn btn-outline-primary btn-sm">View All</a>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/multiplayer/create" class="btn btn-success">
                            <i class="fas fa-plus"></i> Create Match
                        </a>
                        <a href="/multiplayer/lobby" class="btn btn-primary">
                            <i class="fas fa-gamepad"></i> Join Match
                        </a>
                        <a href="/friends" class="btn btn-info">
                            <i class="fas fa-user-friends"></i> Find Friends
                        </a>
                        <a href="/user/leaderboard" class="btn btn-warning">
                            <i class="fas fa-trophy"></i> Leaderboard
                        </a>
                    </div>
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
                            <?php foreach ($active_matches as $match): ?>
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
        // Could refresh the online friends section here
    });

    socket.on('match_update', function(data) {
        // Update match information
        console.log('Match update:', data);
        // Could refresh the active matches section here
    });
}

function updateOnlineStatus(status) {
    if (socket && socket.connected) {
        socket.emit('update_status', { status: status });
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
    
    // Auto-refresh every 30 seconds
    setInterval(function() {
        location.reload();
    }, 30000);
});

// Update status when leaving page
window.addEventListener('beforeunload', function() {
    updateOnlineStatus('offline');
});
</script>

<style>
.online-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
}

.online-indicator.online {
    background-color: #28a745;
}

.online-indicator.playing {
    background-color: #ffc107;
}

.online-indicator.offline {
    background-color: #6c757d;
}

.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}

.badge {
    font-size: 0.75em;
}
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
