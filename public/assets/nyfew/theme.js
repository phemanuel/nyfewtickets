document.addEventListener('DOMContentLoaded',()=>{
 const menu=document.querySelector('[data-sidebar]'),scrim=document.querySelector('[data-scrim]');
 const close=()=>{menu?.classList.remove('open');scrim?.classList.remove('open');};
 document.querySelector('[data-menu-toggle]')?.addEventListener('click',()=>{menu?.classList.toggle('open');scrim?.classList.toggle('open');});
 scrim?.addEventListener('click',close);
 document.addEventListener('keydown',e=>{if(e.key==='Escape')close()});
 document.querySelectorAll('[data-password-toggle]').forEach(btn=>btn.addEventListener('click',()=>{const target=document.getElementById(btn.dataset.passwordToggle);if(!target)return;const show=target.type==='password';target.type=show?'text':'password';btn.textContent=show?'Hide':'Show';btn.setAttribute('aria-pressed',String(show));}));
});
