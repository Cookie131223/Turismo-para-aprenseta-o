const toggle=document.querySelector('.menu-toggle');
const nav=document.querySelector('.nav-links');
if(toggle&&nav){
  const closeMenu=()=>{nav.classList.remove('open');toggle.classList.remove('open');toggle.setAttribute('aria-expanded','false');};
  toggle.addEventListener('click',()=>{
    const isOpen=nav.classList.toggle('open');
    toggle.classList.toggle('open',isOpen);
    toggle.setAttribute('aria-expanded',String(isOpen));
  });
  nav.querySelectorAll('a').forEach(link=>link.addEventListener('click',closeMenu));
  window.addEventListener('resize',()=>{if(window.innerWidth>720) closeMenu();});
}
