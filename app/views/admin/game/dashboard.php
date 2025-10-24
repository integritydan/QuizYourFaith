<?php include __DIR__ . '/../../layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-none d-md-block bg-light sidebar">
            <div class="sidebar-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="/admin/game">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/game/active-matches">
                            <i class="fas fa-gamepad"></i> Active Matches
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/game/match-reports">
                            <i class="fas fa-exclamation-triangle"></i> Match Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/game/tournaments">
                            <i class="fas fa-trophy"></i> Tournaments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/game/chat-moderation">
                            <i class="fas fa-comments"></i> Chat Moderation
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/game/send-announcement">
                            <i class="fas fa-bullhorn"></i> Send Announcement
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Game Admin Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group mr-2">
                        <a href="/admin/game/active-matches" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i> Monitor Matches
                        </a>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Active Matches
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['active_matches'] ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-gamepad fa-2x text-gray-300"></i>
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
                                        Reported Matches
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['reported_matches'] ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
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
                                        Online Players
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['online_players'] ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-check fa-2x text-gray-300"></i>
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
                                        Today's Tournaments
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['tournaments_today'] ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Quick Moderation Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-3 mb-2">
                                    <a href="/admin/game/active-matches" class="btn btn-primary btn-block">
                                        <i class="fas fa-eye"></i> Monitor Active Matches
                                    </a>
                                </div>
                                <div class="col-sm-3 mb-2">
                                    <a href="/admin/game/match-reports" class="btn btn-warning btn-block">
                                        <i class="fas fa-exclamation-triangle"></i> Review Reports
                                    </a>
                                </div>
                                <div class="col-sm-3 mb-2">
                                    <a href="/admin/game/chat-moderation" class="btn btn-info btn-block">
                                        <i class="fas fa-comments"></i> Moderate Chat
                                    </a>
                                </div>
                                <div class="col-sm-3 mb-2">
                                    <a href="/admin/game/send-announcement" class="btn btn-success btn-block">
                                        <i class="fas fa-bullhorn"></i> Send Announcement
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Reports -->
            <div class="row mb-4">
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Recent Match Reports</h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($stats['recent_reports'])): ?>
                                <p class="text-muted">No recent reports</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Time</th>
                                                <th>Reporter</th>
                                                <th>Reported User</th>
                                                <th>Type</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($stats['recent_reports'] as $report): ?>
                                            <tr>
                                                <td><?= date('Y-m-d H:i', strtotime($report->created_at)) ?></td>
                                                <td><?= htmlspecialchars($report->reporter_name) ?></td>
                                                <td><?= htmlspecialchars($report->reported_name) ?></td>
                                                <td>
                                                    <span class="badge badge-warning"><?= htmlspecialchars($report->report_type) ?></span>
                                                </td>
                                                <td>
                                                    <a href="/admin/game/match-reports" class="btn btn-sm btn-primary">Review</a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Active Tournaments</h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($stats['active_tournaments'])): ?>
                                <p class="text-muted">No active tournaments</p>
                            <?php else: ?>
                                <?php foreach ($stats['active_tournaments'] as $tournament): ?>
                                <div class="mb-3 p-2 border rounded">
                                    <h6 class="mb-1"><?= htmlspecialchars($tournament->name) ?></h6>
                                    <small class="text-muted">
                                        Participants: <?= $tournament->participant_count ?>/<?= $tournament->max_participants ?>
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        Started: <?= date('Y-m-d H:i', strtotime($tournament->start_time)) ?>
                                    </small>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Match Monitoring -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Currently Active Matches</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Match ID</th>
                                            <th>Title</th>
                                            <th>Quiz</th>
                                            <th>Players</th>
                                            <th>Creator</th>
                                            <th>Started</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Get active matches for display
                                        $activeMatches = db()->query("
                                            SELECT 
                                                m.*, 
                                                q.title as quiz_title,
                                                COUNT(mp.user_id) as current_players,
                                                u.name as creator_name
                                            FROM matches m
                                            JOIN quizzes q ON m.quiz_id = q.id
                                            JOIN users u ON m.created_by = u.id
                                            LEFT JOIN match_players mp ON m.id = mp.match_id
                                            WHERE m.status = 'active'
                                            GROUP BY m.id
                                            ORDER BY m.start_time DESC
                                            LIMIT 10
                                        ")->fetchAll();
                                        ?>
                                        <?php foreach ($activeMatches as $match): ?>
                                        <tr>
                                            <td><?= $match->id ?></td>
                                            <td><?= htmlspecialchars($match->title) ?></td>
                                            <td><?= htmlspecialchars($match->quiz_title) ?></td>
                                            <td><?= $match->current_players ?>/<?= $match->max_players ?></td>
                                            <td><?= htmlspecialchars($match->creator_name) ?></td>
                                            <td><?= date('H:i', strtotime($match->start_time)) ?></td>
                                            <td>
                                                <a href="/admin/game/match/<?= $match->id ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <button class="btn btn-sm btn-danger" onclick="endMatch(<?= $match->id ?>)">
                                                    <i class="fas fa-stop"></i> End
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function endMatch(matchId) {
    if (confirm('Are you sure you want to end this match?')) {
        // Create a form to submit the request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/game/match/${matchId}/end`;
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = '<?= $_SESSION['csrf_token'] ?? '' ?>';
        form.appendChild(csrfInput);
        
        const reasonInput = document.createElement('input');
        reasonInput.type = 'hidden';
        reasonInput.name = 'reason';
        reasonInput.value = 'Match ended by moderator';
        form.appendChild(reasonInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<style>
.sidebar {
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    z-index: 100;
    padding: 48px 0 0;
    box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
}

.sidebar-sticky {
    position: relative;
    top: 0;
    height: calc(100vh - 48px);
    padding-top: .5rem;
    overflow-x: hidden;
    overflow-y: auto;
}

.sidebar .nav-link {
    font-weight: 500;
    color: #333;
}

.sidebar .nav-link.active {
    color: #007bff;
}

.sidebar .nav-link:hover {
    color: #007bff;
}
</style>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>