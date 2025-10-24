<!doctype html>
<html lang="en" data-bs-theme="<?= $_COOKIE['theme'] ?? 'dark' ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?=$title??'QuizYourFaith'?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/css/themes.css">
  <style>
    :root{--royal:#0033a0;--gold:#ffb81c;}
    [data-bs-theme="dark"]{background:#000;color:#fff;}
    [data-bs-theme="light"]{background:#f5f7fb;color:#111;}
    .card-dash{background: rgba(255,255,255,.08);border-radius:1rem;backdrop-filter:blur(6px);}
    [data-bs-theme="light"] .card-dash{background:rgba(0,51,160,.05);}
    .btn-royal{background:var(--royal);color:#fff;}
    .icon-bible{font-size:2rem;filter:drop-shadow(0 0 3px var(--gold));}
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3">
  <a class="navbar-brand fw-bold" href="/">QuizYourFaith</a>
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarNav">
    <ul class="navbar-nav me-auto">
      <li class="nav-item">
        <a class="nav-link" href="/">Home</a>
      </li>
      <?php if (\App\Models\Feature::isEnabled('youtube_videos')): ?>
      <li class="nav-item">
        <a class="nav-link" href="/videos">📺 Messages</a>
      </li>
      <?php endif; ?>
      <?php if (\App\Models\Feature::isEnabled('quiz_system')): ?>
      <li class="nav-item">
        <a class="nav-link" href="/bible-quiz">
            <i class="fas fa-book-open"></i> Bible Quiz
        </a>
      </li>
      <?php endif; ?>
      <?php if (\App\Models\Feature::isEnabled('multiplayer')): ?>
      <li class="nav-item">
        <a class="nav-link" href="/multiplayer">Multiplayer</a>
      </li>
      <?php endif; ?>
    </ul>
    <div class="d-flex align-items-center gap-3">
      <button id="themeToggle" class="btn btn-sm btn-outline-light">🌓</button>
      <a href="/account" class="badge bg-primary text-decoration-none"><?=$user["name"]??"Guest"?></a>
      <span class="badge bg-light text-dark"><?=$user['points']??0?> pts</span>
    </div>
  </div>
</nav>
<script src="/js/theme.js"></script>
