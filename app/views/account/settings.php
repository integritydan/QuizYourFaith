<?php include __DIR__.'/../partials/header.php'; ?>
<div class="container py-4">
  <h4>Settings</h4>
  <form method="post" action="/account/update">
    <div class="card-dash p-3 mb-3">
      <h6>Account</h6>
      <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" value="<?=$user['name']??''?>"></div>
      <div class="mb-3"><label class="form-label">Email</label><input name="email" type="email" class="form-control" value="<?=$user['email']??''?>"></div>
      <div class="mb-3"><label class="form-label">New Password</label><input name="password" type="password" class="form-control" placeholder="Leave blank to keep current"></div>
    </div>
    <div class="card-dash p-3 mb-3">
      <h6>Theme</h6>
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="themeSwitch" <?=($_COOKIE['theme']??'dark')==='light'?'checked':''?>>
        <label class="form-check-label" for="themeSwitch">Light mode</label>
      </div>
    </div>
    <button class="btn btn-royal">Save Changes</button>
  </form>
  <hr>
  <form method="post" action="/account/delete" onsubmit="return confirm('Delete account forever?');">
    <button class="btn btn-outline-danger btn-sm">Delete Account</button>
  </form>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>
