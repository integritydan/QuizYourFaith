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
  <div class="ms-auto d-flex align-items-center gap-3">
    <button id="themeToggle" class="btn btn-sm btn-outline-light">🌓</button>
    <span class="badge bg-primary"><?=$user['name']??'Guest'?></span>
    <span class="badge bg-light text-dark"><?=$user['points']??0?> pts</span>
  </div>
</nav>
<script src="/js/theme.js"></script>
