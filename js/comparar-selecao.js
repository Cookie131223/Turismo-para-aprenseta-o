// Seleção de até 3 destinos para comparar (caixinhas "Comparar" nos cards) com barra flutuante.
(function () {
  var caixas = document.querySelectorAll('[data-comparar]');
  if (!caixas.length) return;
  var CHAVE = 'tripora_comparar', MAX = 3;
  var ler = function () { try { return JSON.parse(sessionStorage.getItem(CHAVE) || '[]'); } catch (e) { return []; } };
  var salvar = function (l) { try { sessionStorage.setItem(CHAVE, JSON.stringify(l)); } catch (e) {} };
  var sel = ler().filter(function (s) { return document.querySelector('[data-comparar="' + s.id + '"]'); });

  var barra = document.createElement('div');
  barra.className = 'comparar-barra'; barra.hidden = true; barra.setAttribute('role', 'region'); barra.setAttribute('aria-label', 'Destinos para comparar');
  document.body.append(barra);

  var render = function () {
    caixas.forEach(function (c) {
      c.checked = sel.some(function (s) { return s.id === c.dataset.comparar; });
      c.disabled = !c.checked && sel.length >= MAX;
      c.closest('.comparar-check').classList.toggle('marcado', c.checked);
    });
    barra.replaceChildren();
    if (!sel.length) { barra.hidden = true; return; }
    var lista = document.createElement('div'); lista.className = 'comparar-chips';
    sel.forEach(function (s) {
      var chip = document.createElement('button'); chip.type = 'button'; chip.className = 'chip-x'; chip.textContent = s.nome + ' ×';
      chip.setAttribute('aria-label', 'Tirar ' + s.nome + ' da comparação');
      chip.onclick = function () { sel = sel.filter(function (x) { return x.id !== s.id; }); salvar(sel); render(); };
      lista.append(chip);
    });
    var ir = document.createElement('a'); ir.className = 'btn small';
    ir.href = 'comparar.php?ids=' + sel.map(function (s) { return s.id; }).join(',');
    ir.textContent = sel.length < 2 ? 'Escolha mais 1' : 'Comparar (' + sel.length + ')';
    if (sel.length < 2) { ir.setAttribute('aria-disabled', 'true'); ir.classList.add('desativado'); ir.onclick = function (e) { e.preventDefault(); }; }
    barra.append(lista, ir); barra.hidden = false;
  };
  caixas.forEach(function (c) {
    c.addEventListener('change', function () {
      if (c.checked && sel.length < MAX) sel.push({ id: c.dataset.comparar, nome: c.dataset.nome });
      else sel = sel.filter(function (s) { return s.id !== c.dataset.comparar; });
      salvar(sel); render();
    });
  });
  render();
})();
