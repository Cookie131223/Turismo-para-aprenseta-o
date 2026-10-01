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

const search=document.querySelector('#destinationSearch');
const cards=[...document.querySelectorAll('.destination-card')];
const count=document.querySelector('#resultCount');
const empty=document.querySelector('#emptySearch');
if(search&&cards.length){
  const normalize=value=>value.normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase();
  search.addEventListener('input',()=>{
    const term=normalize(search.value.trim());
    let visible=0;
    cards.forEach(card=>{
      const match=normalize(card.dataset.search||'').includes(term);
      card.hidden=!match;
      if(match) visible++;
    });
    if(count) count.textContent=visible+' '+(visible===1?'opção':'opções');
    if(empty) empty.hidden=visible!==0;
  });
}
