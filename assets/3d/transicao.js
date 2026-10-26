// Transição de avião: ao clicar em "Reservar", um aviãozinho cruza a tela antes de abrir a página.
const reduzir = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

if (!reduzir) {
  document.addEventListener('click', e => {
    const link = e.target.closest('a[href*="reserva.php"]');
    if (!link || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || link.target === '_blank') return;
    e.preventDefault();
    const destino = link.href;
    const camada = document.createElement('div');
    camada.className = 'voo-transicao';
    camada.setAttribute('aria-hidden', 'true');
    camada.innerHTML = '<div class="voo-rastro"></div><svg class="voo-aviao" viewBox="0 0 64 64" width="88" height="88"><path fill="#fff" d="M60 30.5c0-2-1.6-3.5-3.6-3.5H40.6L26.4 6.5c-.4-.6-1-1-1.8-1h-4.3c-1 0-1.7 1-1.4 2l6 19.5H12.5l-4.6-6c-.4-.5-1-.8-1.6-.8H3.2c-.9 0-1.6.9-1.3 1.8L5 32 1.9 42c-.3.9.4 1.8 1.3 1.8h3.1c.6 0 1.2-.3 1.6-.8l4.6-6h12.4l-6 19.5c-.3 1 .4 2 1.4 2h4.3c.7 0 1.4-.4 1.8-1L40.6 37h15.8c2 0 3.6-1.6 3.6-3.5v-3z"/></svg>';
    document.body.append(camada);
    let foi = false;
    const ir = () => { if (!foi) { foi = true; window.location.href = destino; } };
    camada.querySelector('.voo-aviao').addEventListener('animationend', ir);
    setTimeout(ir, 900); // garantia, caso a animação não dispare
  });
  // ao voltar pelo botão "voltar" do navegador, remove a camada que ficou na página
  window.addEventListener('pageshow', () => document.querySelectorAll('.voo-transicao').forEach(c => c.remove()));
}
