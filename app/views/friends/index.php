<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Add Friends</h6>
                </div>
                <div class="card-body">
                    <form id="searchFriendsForm">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="friendSearch" placeholder="Search by name or email..." minlength="2">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <div id="searchResults" class="mt-3"></div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Friend Requests</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($received_requests)): ?>
                        <p class="text-muted">No pending requests</p>
                    <?php else: ?>
                        <?php foreach ($received_requests as $request): ?>
                        <div class="d-flex align-items-center mb-2 p-2 border rounded">
                            <div class="flex-grow-1">
                                <strong><?= htmlspecialchars($request->name) ?></strong>
                                <br>
                                <small class="text-muted">
                                    Sent <?= time_ago($request->request_sent) ?>
                                </small>
                            </div>
                            <div>
                                <form method="POST" action="/friends/accept/<?= $request->id ?>" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                    <button type="submit" class="btn btn-sm btn-success" title="Accept">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                <form method="POST" action="/friends/decline/<?= $request->id ?>" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Decline">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Friends</h1>
                <div>
                    <span class="badge badge-primary"><?= count($friends) ?> Friends</span>
                    <span class="badge badge-warning"><?= count($received_requests) ?> Pending</span>
                </div>
            </div>

            <!-- Friends List -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Your Friends</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($friends)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-user-friends fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No friends yet</h5>
                            <p class="text-muted">Search for users and send friend requests to build your friend list!</p>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($friends as $friend): ?>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="mr-3">
                                                <?php if ($friend->online_status === 'online'): ?>
                                                    <span class="badge badge-success">●</span>
                                                <?php elseif ($friend->online_status === 'playing'): ?>
                                                    <span class="badge badge-warning">●</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">●</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0"><?= htmlspecialchars($friend->name) ?></h6>
                                                <small class="text-muted">
                                                    <?= ucfirst($friend->online_status) ?>
                                                </small>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <small class="text-muted">
                                                    Friends since <?= date('M Y', strtotime($friend->friends_since)) ?>
                                                </small>
                                            </div>
                                            <div class="btn-group">
                                                <a href="/friends/profile/<?= $friend->id ?>" class="btn btn-sm btn-info" title="View Profile">
                                                    <i class="fas fa-user"></i>
                                                </a>
                                                <button class="btn btn-sm btn-primary invite-to-match" 
                                                        data-friend-id="<?= $friend->id ?>" 
                                                        data-friend-name="<?= htmlspecialchars($friend->name) ?>"
                                                        title="Invite to Match">
                                                    <i class="fas fa-gamepad"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger remove-friend" 
                                                        data-friend-id="<?= $friend->id ?>"
                                                        data-friend-name="<?= htmlspecialchars($friend->name) ?>"
                                                        title="Remove Friend">
                                                    <i class="fas fa-user-minus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sent Requests -->
            <?php if (!empty($sent_requests)): ?>
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Sent Friend Requests</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($sent_requests as $request): ?>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($request->name) ?></h6>
                                            <small class="text-muted">
                                                Sent <?= time_ago($request->request_sent) ?>
                                            </small>
                                        </div>
                                        <span class="badge badge-warning">Pending</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Invite to Match Modal -->
<div class="modal fade" id="inviteToMatchModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Invite Friend to Match</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Invite <strong id="inviteFriendName"></strong> to join your match:</p>
                <form id="inviteToMatchForm">
                    <input type="hidden" id="inviteFriendId" name="friend_id">
                    <div class="form-group">
                        <label>Select Match:</label>
                        <select class="form-control" id="inviteMatchId" name="match_id" required>
                            <option value="">Choose a match...</option>
                            <!-- This would be populated with user's available matches -->
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
                <button type="button" class="btn btn-primary" onclick="sendMatchInvitation()">Send Invitation</button>
            </div>
        </div>
    </div>
</div>

<script>
// Search friends functionality
document.getElementById('searchFriendsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    searchUsers();
});

document.getElementById('friendSearch').addEventListener('input', function() {
    if (this.value.length >= 2) {
        searchUsers();
    } else {
        document.getElementById('searchResults').innerHTML = '';
    }
});

function searchUsers() {
    const query = document.getElementById('friendSearch').value;
    
    if (query.length < 2) return;
    
    fetch('/friends/search?q=' + encodeURIComponent(query))
        .then(response => response.json())
        .then(data => {
            displaySearchResults(data.users);
        })
        .catch(error => {
            console.error('Error searching users:', error);
        });
}

function displaySearchResults(users) {
    const resultsDiv = document.getElementById('searchResults');
    
    if (users.length === 0) {
        resultsDiv.innerHTML = '<p class="text-muted">No users found</p>';
        return;
    }
    
    let html = '';
    users.forEach(user => {
        html += `
            <div class="d-flex align-items-center mb-2 p-2 border rounded">
                <div class="flex-grow-1">
                    <strong>${escapeHtml(user.name)}</strong>
                    <br>
                    <small class="text-muted">${escapeHtml(user.email)}</small>
                </div>
                <div>`;
        
        if (user.friendship_status === 'none') {
            html += `
                <form method="POST" action="/friends/send-request" class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="friend_id" value="${user.id}">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-user-plus"></i> Add
                    </button>
                </form>`;
        } else if (user.friendship_status === 'pending') {
            if (user.request_direction === 'received') {
                html += `<span class="badge badge-warning">Request Received</span>`;
            } else {
                html += `<span class="badge badge-info">Request Sent</span>`;
            }
        } else if (user.friendship_status === 'accepted') {
            html += `<span class="badge badge-success">Friends</span>`;
        } else if (user.friendship_status === 'blocked') {
            html += `<span class="badge badge-danger">Blocked</span>`;
        }
        
        html += `</div></div>`;
    });
    
    resultsDiv.innerHTML = html;
}

// Invite to match functionality
document.querySelectorAll('.invite-to-match').forEach(button => {
    button.addEventListener('click', function() {
        const friendId = this.getAttribute('data-friend-id');
        const friendName = this.getAttribute('data-friend-name');
        
        document.getElementById('inviteFriendId').value = friendId;
        document.getElementById('inviteFriendName').textContent = friendName;
        
        // Load available matches
        loadAvailableMatches();
        
        $('#inviteToMatchModal').modal('show');
    });
});

function loadAvailableMatches() {
    // This would fetch user's available matches from the server
    // For now, we'll use a placeholder
    const select = document.getElementById('inviteMatchId');
    select.innerHTML = '<option value="">Loading matches...</option>';
    
    // Simulate loading matches
    setTimeout(() => {
        select.innerHTML = `
            <option value="">Choose a match...</option>
            <option value="1">Quick Match - General Bible Quiz</option>
            <option value="2">Bible Challenge - Life of Jesus</option>
        `;
    }, 500);
}

function sendMatchInvitation() {
    const form = document.getElementById('inviteToMatchForm');
    const formData = new FormData(form);
    
    fetch('/friends/invite-to-match/' + formData.get('friend_id'), {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#inviteToMatchModal').modal('hide');
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

// Remove friend functionality
document.querySelectorAll('.remove-friend').forEach(button => {
    button.addEventListener('click', function() {
        const friendId = this.getAttribute('data-friend-id');
        const friendName = this.getAttribute('data-friend-name');
        
        if (confirm(`Are you sure you want to remove ${friendName} from your friends?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/friends/remove/' + friendId;
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = '<?= $_SESSION['csrf_token'] ?? '' ?>';
            form.appendChild(csrfInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    });
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<style>
.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}

.online-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
}

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>