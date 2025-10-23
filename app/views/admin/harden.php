<?php include __DIR__.'/../partials/header.php'; ?>
<div class="container py-4">
  <h4>One-Click Hardening</h4>
  <p>Run this <b>after installation</b> to lock the app for production.</p>
  <button id="hardenBtn" class="btn btn-success btn-lg">🔒 Harden Now</button>
  <div id="result" class="mt-3"></div>
</div>
<script>
document.getElementById('hardenBtn').onclick=async()=>{
  const res=await fetch('/admin/harden/run',{method:'POST'});
  const j=await res.json();
  let html='';
  for(const[k,v] of Object.entries(j)){
    html+=`<div class="alert alert-${v==='ok'?'success':'danger'}">${k}: ${v}</div>`;
  }
  document.getElementById('result').innerHTML=html;
};
</script>
<?php include __DIR__.'/../partials/footer.php'; ?>
