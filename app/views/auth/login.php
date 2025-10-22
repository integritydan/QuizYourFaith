<?php include APP_PATH.'/views/layouts/header.php'; ?>
<h4>Login</h4>
<?php if(isset($_SESSION['error'])): ?><div class="alert alert-danger"><?=$_SESSION['error']?></div><?php unset($_SESSION['error']); endif; ?>
<form method="post">
  <?=csrf()?>
  <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
  <div class="mb-3"><label>Password</label><input type="password" name="password" class="form-control" required></div>
  <button class="btn btn-primary">Login</button> &nbsp; <a href="/register">No account?</a>
</form>
<?php include APP_PATH.'/views/layouts/footer.php'; ?>
