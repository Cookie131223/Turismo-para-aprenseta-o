// Cartão de embarque: inclina com o mouse, vira ao tocar e pode ser baixado como imagem.
(function () {
  var cena = document.getElementById('bilhete');
  if (!cena) return;
  var dados = JSON.parse(cena.dataset.bilhete || '{}');
  var cartao = cena.querySelector('.bilhete');
  var reduzir = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var touch = window.matchMedia('(hover: none), (pointer: coarse)').matches;
  var virado = false, rx = 0, ry = 0;

  var aplicar = function () { cartao.style.transform = 'rotateX(' + rx + 'deg) rotateY(' + (ry + (virado ? 180 : 0)) + 'deg)'; };
  var virar = function () { virado = !virado; cena.classList.remove('chegando'); aplicar(); };
  cartao.addEventListener('click', virar);
  cartao.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); virar(); } });
  if (!touch && !reduzir) {
    cena.addEventListener('pointermove', function (e) {
      cena.classList.remove('chegando');
      var r = cena.getBoundingClientRect();
      ry = ((e.clientX - r.left) / r.width - 0.5) * 16; rx = (0.5 - (e.clientY - r.top) / r.height) * 12;
      cartao.classList.add('movendo'); aplicar();
    });
    cena.addEventListener('pointerleave', function () { rx = 0; ry = 0; cartao.classList.remove('movendo'); aplicar(); });
  }

  // "código de barras" desenhado a partir do localizador
  var barras = function (ctx, x, y, w, h, cor) {
    var bits = '';
    (dados.codigo + dados.voo + dados.assento).split('').forEach(function (c) { bits += c.charCodeAt(0).toString(2).padStart(8, '0'); });
    var n = bits.length, larg = w / (n * 1.15);
    ctx.fillStyle = cor;
    for (var i = 0, px = x; i < n; i++) { var grossa = bits[i] === '1'; ctx.fillRect(px, y, grossa ? larg * 1.6 : larg * 0.7, h); px += larg * 1.15; }
  };
  var cv = cena.querySelector('.bilhete-barras');
  if (cv) { var c2 = cv.getContext('2d'); barras(c2, 10, 10, 540, 90, '#f5f7fb'); c2.fillStyle = '#a7b5c7'; c2.font = '600 16px Inter, system-ui, sans-serif'; c2.textAlign = 'center'; c2.fillText(dados.codigo + ' · ' + dados.voo, 280, 116); }

  // imagem para baixar
  document.getElementById('baixarBilhete').addEventListener('click', function () {
    var W = 1400, H = 600, c = document.createElement('canvas'); c.width = W; c.height = H;
    var g = c.getContext('2d');
    var grad = g.createLinearGradient(0, 0, W, H); grad.addColorStop(0, '#13263c'); grad.addColorStop(1, '#0b1728');
    var raio = function (x, y, w, h, r) { g.beginPath(); g.moveTo(x + r, y); g.arcTo(x + w, y, x + w, y + h, r); g.arcTo(x + w, y + h, x, y + h, r); g.arcTo(x, y + h, x, y, r); g.arcTo(x, y, x + w, y, r); g.closePath(); };
    g.fillStyle = '#07111f'; g.fillRect(0, 0, W, H);
    raio(30, 30, W - 60, H - 60, 36); g.fillStyle = grad; g.fill();
    g.strokeStyle = 'rgba(255,255,255,.12)'; g.lineWidth = 2; g.stroke();
    var fx = function (txt, x, y, tam, cor, peso, alinh) { g.font = (peso || 700) + ' ' + tam + 'px Inter, system-ui, sans-serif'; g.fillStyle = cor; g.textAlign = alinh || 'left'; g.fillText(txt, x, y); };
    fx('Tripora', 80, 110, 40, '#f5f7fb', 900); fx('.com', 80 + g.measureText('Tripora').width, 110, 40, '#39d0ff', 900);
    fx('CARTÃO DE EMBARQUE', W - 80, 104, 22, '#39d0ff', 800, 'right');
    fx(dados.origem.iata, 80, 250, 110, '#f5f7fb', 900); fx(dados.origem.cidade, 84, 290, 26, '#a7b5c7', 600);
    fx(dados.destino.iata, 1000, 250, 110, '#f5f7fb', 900, 'right'); fx(dados.destino.cidade, 996, 290, 26, '#a7b5c7', 600, 'right');
    g.strokeStyle = 'rgba(57,208,255,.6)'; g.setLineDash([10, 10]); g.beginPath(); g.moveTo(400, 210); g.lineTo(680, 210); g.stroke(); g.setLineDash([]);
    fx('✈', 540, 225, 54, '#39d0ff', 400, 'center');
    var campos = [['PASSAGEIRO', dados.passageiro], ['DATA', dados.ida], ['EMBARQUE', dados.horario], ['PORTÃO', dados.portao], ['ASSENTO', dados.assento], ['VOO', dados.voo]];
    campos.forEach(function (cpo, i) { var x = 80 + (i % 3) * 320, y = 380 + Math.floor(i / 3) * 100; fx(cpo[0], x, y, 18, '#a7b5c7', 700); fx(String(cpo[1]).slice(0, 22), x, y + 40, 30, '#f5f7fb', 800); });
    g.strokeStyle = 'rgba(255,255,255,.18)'; g.setLineDash([8, 10]); g.beginPath(); g.moveTo(1060, 60); g.lineTo(1060, H - 60); g.stroke(); g.setLineDash([]);
    fx('LOCALIZADOR', 1110, 170, 18, '#a7b5c7', 700); fx(dados.codigo, 1110, 230, 54, '#39d0ff', 900);
    fx('VIAJANTES: ' + dados.viajantes, 1110, 290, 22, '#f5f7fb', 700); fx('VOLTA: ' + dados.volta, 1110, 330, 22, '#f5f7fb', 700);
    g.save(); g.translate(1110, 360); barras(g, 0, 0, 230, 140, '#f5f7fb'); g.restore();
    fx('Demonstrativo · projeto acadêmico', 1110, 535, 15, '#a7b5c7', 600);
    var a = document.createElement('a'); a.download = 'cartao-embarque-' + dados.codigo + '.png'; a.href = c.toDataURL('image/png'); a.click();
  });
})();
