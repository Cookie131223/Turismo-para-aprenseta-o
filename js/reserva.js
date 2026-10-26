// Calendário de ida/volta, número de viajantes e total calculado na hora.
(function () {
  var form = document.getElementById('reservaForm');
  if (!form) return;
  var preco = parseFloat(form.dataset.preco) || 0;
  var ida = form.querySelector('#data_ida'), volta = form.querySelector('#data_volta'), viaj = form.querySelector('#viajantes');
  var brl = function (v) { return v.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }); };
  var dia = function (s) { var p = s.split('-'); return p.length === 3 ? new Date(+p[0], +p[1] - 1, +p[2]) : null; };
  var iso = function (d) { return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0'); };

  var atualizar = function () {
    var n = Math.min(9, Math.max(1, parseInt(viaj.value, 10) || 1));
    form.querySelector('[data-r="viajantes"]').textContent = n;
    form.querySelector('[data-r="total"]').textContent = brl(preco * n);
    var a = dia(ida.value), b = dia(volta.value), noites = form.querySelector('[data-r="noites"]');
    if (a && b && b > a) {
      var qtd = Math.round((b - a) / 86400000);
      noites.textContent = qtd + (qtd === 1 ? ' noite' : ' noites') + ' · ' + a.toLocaleDateString('pt-BR') + ' a ' + b.toLocaleDateString('pt-BR');
      noites.classList.remove('erro');
    } else { noites.textContent = 'A volta precisa ser depois da ida.'; noites.classList.add('erro'); }
  };
  ida.addEventListener('change', function () {
    var a = dia(ida.value); if (!a) return;
    var min = new Date(a); min.setDate(min.getDate() + 1); volta.min = iso(min);
    var b = dia(volta.value); if (!b || b <= a) { var sug = new Date(a); sug.setDate(sug.getDate() + 7); volta.value = iso(sug); }
    atualizar();
  });
  volta.addEventListener('change', atualizar);
  viaj.addEventListener('input', atualizar);
  form.querySelectorAll('[data-passo]').forEach(function (b) {
    b.addEventListener('click', function () { viaj.value = Math.min(9, Math.max(1, (parseInt(viaj.value, 10) || 1) + parseInt(b.dataset.passo, 10))); atualizar(); });
  });
  atualizar();
})();
