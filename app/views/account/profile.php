<?php include __DIR__.'/../partials/header.php'; ?>
<div class="container py-4">
  <h4>Profile</h4>
  <form method="post" enctype="multipart/form-data" action="/account/updateProfile">
    <div class="card-dash p-3 mb-3">
      <div class="text-center mb-3">
        <img src="<?=$user['avatar']??'/uploads/default.png'?>" class="rounded-circle" width="100" height="100" alt="avatar">
      </div>
      <div class="mb-3"><label class="form-label">Change Avatar</label><input type="file" name="avatar" class="form-control"></div>
      <div class="mb-3"><label class="form-label">Bio</label><textarea name="bio" class="form-control" rows="3"><?=$user['bio']??''?></textarea></div>
    </div>
    <div class="card-dash p-3 mb-3">
      <h6>Social Links</h6>
      <div class="mb-3"><label class="form-label">Facebook</label><input name="fb" class="form-control" value="<?=$user['fb']??''?>" placeholder="username"></div>
      <div class="mb-3"><label class="form-label">Twitter</label><input name="tw" class="form-control" value="<?=$user['tw']??''?>" placeholder="username"></div>
      <div class="mb-3"><label class="form-label">Instagram</label><input name="ig" class="form-control" value="<?=$user['ig']??''?>" placeholder="username"></div>
    </div>
    <button class="btn btn-royal">Update Profile</button>
  </form>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>
