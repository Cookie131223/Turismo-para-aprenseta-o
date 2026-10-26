// Botão de favoritar (coração). Salva na conta; quem não está logado vai para o login.
document.addEventListener('click', function (e) {
  var btn = e.target.closest('.fav-btn');
  if (!btn) return;
  e.preventDefault(); e.stopPropagation();
  if (btn.dataset.ocupado) return;
  btn.dataset.ocupado = '1';
  var corpo = new URLSearchParams({ destino_id: btn.dataset.destino });
  fetch('favoritar.php', { method: 'POST', body: corpo, headers: { 'X-Requested-With': 'fetch' }, credentials: 'same-origin' })
    .then(function (r) {
      if (r.status === 401) { window.location.href = 'login.html?erro=nao_logado'; return null; }
      if (!r.ok) throw new Error(r.status);
      return r.json();
    })
    .then(function (d) {
      if (!d) return;
      document.querySelectorAll('.fav-btn[data-destino="' + btn.dataset.destino + '"]').forEach(function (b) {
        b.classList.toggle('ativo', d.favorito);
        b.setAttribute('aria-pressed', d.favorito ? 'true' : 'false');
        b.classList.remove('pulsar'); void b.offsetWidth; b.classList.add('pulsar');
      });
    })
    .catch(function () { btn.classList.add('erro'); setTimeout(function () { btn.classList.remove('erro'); }, 800); })
    .finally(function () { delete btn.dataset.ocupado; });
});
