<!doctype html>
<html lang="en"><head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?=$title??'QuizYourFaith'?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root{--primary:#ff9f1c;--dark:#001219;--light:#fdfffc;}
    body{background:var(--dark);color:var(--light);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto;}
    .btn-primary{background:var(--primary);border-color:var(--primary);color:var(--dark);font-weight:700;}
    .card{background:#0b2a3a;border:none;border-radius:1rem;}
    .timer{font-size:2.5rem;font-weight:700;letter-spacing:2px;}
    .points{font-size:3rem;font-weight:800;color:var(--primary);}
    .question{font-size:1.4rem;line-height:1.3;}
    .answer-block{border-radius:.75rem;font-weight:600;font-size:1.1rem;padding:1rem;text-align:center;cursor:pointer;transition:.2s;}
    .answer-block:hover{transform:scale(1.03);opacity:.9;}
  </style>
</head><body>
<nav class="navbar navbar-dark bg-dark px-3">
  <span class="navbar-brand mb-0 h1">QuizYourFaith</span>
  <div>
    <span class="badge bg-primary"><?=$user['name']??'Guest'?></span>
    <span class="badge bg-light text-dark"><?=$user['points']??0?> pts</span>
  </div>
</nav>
