// Gráfico de barras com versão 3D (blocos em CSS 3D que crescem ao aparecer na tela) e botão para alternar 2D/3D.
// Uso: <div class="barras3d" data-barras='{"titulo":"...","itens":[{"rotulo":"A","valor":10,"texto":"10"}]}' data-modo="3d|2d"></div>
(function () {
  var reduzir = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var CORES = ['#39d0ff', '#7c5cff', '#38d996', '#ffc24b', '#ff5f73', '#a7b5c7'];

  document.querySelectorAll('.barras3d[data-barras]').forEach(function (box) {
    var dados; try { dados = JSON.parse(box.dataset.barras); } catch (e) { return; }
    var itens = (dados.itens || []).filter(function (i) { return i && isFinite(i.valor); });
    var max = Math.max.apply(null, itens.map(function (i) { return i.valor; }).concat([0])) || 1;
    box.classList.add(box.dataset.modo === '2d' ? 'modo-2d' : 'modo-3d');

    var topo = document.createElement('div'); topo.className = 'barras-topo';
    var titulo = document.createElement('h3'); titulo.textContent = dados.titulo || '';
    var alternar = document.createElement('button'); alternar.type = 'button'; alternar.className = 'btn secondary small barras-alternar';
    var rotuloBotao = function () { alternar.textContent = box.classList.contains('modo-3d') ? 'Ver em 2D' : 'Ver em 3D'; };
    alternar.onclick = function () { box.classList.toggle('modo-3d'); box.classList.toggle('modo-2d'); rotuloBotao(); };
    rotuloBotao(); topo.append(titulo, alternar);

    var palco = document.createElement('div'); palco.className = 'barras-palco';
    var chao = document.createElement('div'); chao.className = 'barras-chao'; palco.append(chao);
    itens.forEach(function (it, n) {
      var col = document.createElement('div'); col.className = 'barra-col' + (it.valor > 0 ? '' : ' zero');
      col.style.setProperty('--h', (it.valor / max * 100).toFixed(1) + '%');
      col.style.setProperty('--cor', it.cor || CORES[n % CORES.length]);
      col.style.setProperty('--atraso', (n * 0.12) + 's');
      col.title = it.rotulo + ': ' + (it.texto || it.valor);
      var valor = document.createElement('span'); valor.className = 'barra-valor'; valor.textContent = it.texto || it.valor;
      var bloco = document.createElement('div'); bloco.className = 'barra-bloco';
      ['frente', 'lado', 'topo'].forEach(function (f) { var s = document.createElement('i'); s.className = 'face ' + f; bloco.append(s); });
      var rot = document.createElement('span'); rot.className = 'barra-rotulo'; rot.textContent = it.rotulo;
      col.append(valor, bloco, rot); palco.append(col);
    });
    if (!itens.length) { var vazio = document.createElement('p'); vazio.className = 'muted'; vazio.textContent = 'Sem dados ainda.'; palco.append(vazio); }
    box.replaceChildren(topo, palco);

    // cresce quando aparece na tela
    if (reduzir || !('IntersectionObserver' in window)) { box.classList.add('cresceu'); return; }
    new IntersectionObserver(function (ent, obs) { if (ent[0].isIntersecting) { box.classList.add('cresceu'); obs.disconnect(); } }, { threshold: 0.3 }).observe(box);
  });
})();
