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

const carousel=document.querySelector('.destinations-carousel');
const prevBtn=document.querySelector('.carousel-btn.prev');
const nextBtn=document.querySelector('.carousel-btn.next');
if(carousel&&prevBtn&&nextBtn){
  const step=()=>Math.max(280,Math.min(360,carousel.clientWidth*.82));
  const updateButtons=()=>{
    prevBtn.disabled=carousel.scrollLeft<=4;
    nextBtn.disabled=carousel.scrollLeft+carousel.clientWidth>=carousel.scrollWidth-4;
  };
  prevBtn.addEventListener('click',()=>carousel.scrollBy({left:-step(),behavior:'smooth'}));
  nextBtn.addEventListener('click',()=>carousel.scrollBy({left:step(),behavior:'smooth'}));
  carousel.addEventListener('scroll',updateButtons,{passive:true});
  window.addEventListener('resize',updateButtons);
  setTimeout(updateButtons,0);
}
