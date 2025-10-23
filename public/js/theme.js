document.addEventListener('DOMContentLoaded',()=>{
  const toggle=document.getElementById('themeToggle');
  if(!toggle)return;
  toggle.addEventListener('click',()=>{
    const html=document.documentElement;
    const newTheme=html.getAttribute('data-bs-theme')==='dark'?'light':'dark';
    html.setAttribute('data-bs-theme',newTheme);
    document.cookie=`theme=${newTheme}; path=/; max-age=2592000`; // 30 days
  });
});
