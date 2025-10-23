<?php include __DIR__.'/partials/header.php'; ?>
<h2>One-Click Update</h2>
<form id="upForm" enctype="multipart/form-data">
  <div class="mb-3"><label class="form-label">Choose .zip package</label>
  <input type="file" name="update_zip" class="form-control" required></div>
  <button class="btn btn-warning">Upload & Update</button>
</form>
<div id="msg"></div>
<script>
document.getElementById('upForm').onsubmit=async e=>{
  e.preventDefault();
  const fd=new FormData(e.target);
  const res=await fetch('/admin/update/upload',{method:'POST',body:fd});
  const j=await res.json();
  document.getElementById('msg').innerHTML=`<div class="alert alert-${j.status}">${j.msg}</div>`;
};
</script>
<?php include __DIR__.'/partials/footer.php'; ?>
