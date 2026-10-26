// Informações ao vivo: clima atual (Open-Meteo), hora local do destino e conversão de moeda (open.er-api.com).
// Tudo roda no navegador, sem chave de API. Se algum serviço falhar, o site continua normal.
(function () {
  var guardar = function (k, v) { try { localStorage.setItem(k, JSON.stringify({ t: Date.now(), v: v })); } catch (e) {} };
  var ler = function (k, maxMin) { try { var o = JSON.parse(localStorage.getItem(k) || 'null'); return o && Date.now() - o.t < maxMin * 60000 ? o.v : null; } catch (e) { return null; } };

  // ---------- clima ----------
  var CODIGOS = {
    0: ['☀️', 'Céu limpo'], 1: ['🌤️', 'Poucas nuvens'], 2: ['⛅', 'Parcialmente nublado'], 3: ['☁️', 'Nublado'],
    45: ['🌫️', 'Neblina'], 48: ['🌫️', 'Neblina'], 51: ['🌦️', 'Garoa'], 53: ['🌦️', 'Garoa'], 55: ['🌦️', 'Garoa'],
    61: ['🌧️', 'Chuva fraca'], 63: ['🌧️', 'Chuva'], 65: ['🌧️', 'Chuva forte'], 66: ['🌧️', 'Chuva gelada'], 67: ['🌧️', 'Chuva gelada'],
    71: ['🌨️', 'Neve fraca'], 73: ['🌨️', 'Neve'], 75: ['❄️', 'Neve forte'], 77: ['🌨️', 'Neve'], 80: ['🌦️', 'Pancadas'], 81: ['🌧️', 'Pancadas'],
    82: ['⛈️', 'Pancadas fortes'], 85: ['🌨️', 'Neve'], 86: ['🌨️', 'Neve'], 95: ['⛈️', 'Trovoadas'], 96: ['⛈️', 'Trovoadas'], 99: ['⛈️', 'Trovoadas']
  };
  var climaEls = Array.prototype.slice.call(document.querySelectorAll('[data-clima-lat]'));
  if (climaEls.length) {
    var unicos = {};
    climaEls.forEach(function (el) { unicos[el.dataset.climaLat + ',' + el.dataset.climaLon] = true; });
    var chaves = Object.keys(unicos);
    var mostrar = function (mapa) {
      climaEls.forEach(function (el) {
        var c = mapa[el.dataset.climaLat + ',' + el.dataset.climaLon];
        if (!c) { el.hidden = el.dataset.climaOcultar === '1'; if (!el.hidden) el.textContent = 'Clima indisponível'; return; }
        var info = CODIGOS[c.code] || ['🌡️', ''];
        el.textContent = info[0] + ' ' + Math.round(c.temp) + '°C' + (el.dataset.climaCurto ? '' : ' agora · ' + info[1]);
        el.title = info[1] + ' agora';
        el.hidden = false;
      });
    };
    var cache = ler('tripora_clima_' + chaves.join('|'), 20);
    if (cache) mostrar(cache);
    else {
      var lats = chaves.map(function (k) { return k.split(',')[0]; }).join(',');
      var lons = chaves.map(function (k) { return k.split(',')[1]; }).join(',');
      fetch('https://api.open-meteo.com/v1/forecast?latitude=' + lats + '&longitude=' + lons + '&current=temperature_2m,weather_code&timezone=auto')
        .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
        .then(function (d) {
          var lista = Array.isArray(d) ? d : [d], mapa = {};
          lista.forEach(function (item, i) { if (item && item.current) mapa[chaves[i]] = { temp: item.current.temperature_2m, code: item.current.weather_code }; });
          guardar('tripora_clima_' + chaves.join('|'), mapa); mostrar(mapa);
        })
        .catch(function () { mostrar({}); });
    }
  }

  // ---------- hora local ----------
  var horas = document.querySelectorAll('[data-fuso]');
  var tick = function () {
    horas.forEach(function (el) {
      try { el.textContent = new Intl.DateTimeFormat('pt-BR', { timeZone: el.dataset.fuso, hour: '2-digit', minute: '2-digit' }).format(new Date()); } catch (e) { el.textContent = '—'; }
    });
  };
  if (horas.length) { tick(); setInterval(tick, 30000); }

  // ---------- moeda ----------
  var moedaEls = document.querySelectorAll('[data-moeda]');
  if (!moedaEls.length) return;
  var formatar = function (valor, moeda) {
    try { return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: moeda, maximumFractionDigits: valor >= 1000 ? 0 : 2 }).format(valor); }
    catch (e) { return moeda + ' ' + Math.round(valor).toLocaleString('pt-BR'); }
  };
  var aplicar = function (taxas) {
    document.querySelectorAll('.conv-linha[data-moeda]').forEach(function (el) {
      var t = taxas[el.dataset.moeda]; if (!t) { el.hidden = true; return; }
      el.textContent = '≈ ' + formatar(parseFloat(el.dataset.brl) * t, el.dataset.moeda) + ' (cotação de hoje)';
    });
    document.querySelectorAll('.conversor[data-moeda]').forEach(function (box) {
      var t = taxas[box.dataset.moeda], input = box.querySelector('input'), saida = box.querySelector('.conversor-saida');
      if (!t || !input || !saida) { if (saida) saida.textContent = 'Cotação indisponível agora.'; return; }
      var atualizar = function () {
        var v = parseFloat(input.value) || 0;
        saida.textContent = formatar(v, 'BRL') + ' ≈ ' + formatar(v * t, box.dataset.moeda);
      };
      input.addEventListener('input', atualizar); atualizar();
      var fonte = box.querySelector('.conversor-fonte');
      if (fonte) fonte.textContent = '1 real = ' + t.toLocaleString('pt-BR', { maximumFractionDigits: 4 }) + ' ' + box.dataset.moeda + '. Cotação do dia (open.er-api.com), valores aproximados.';
    });
    document.querySelectorAll('[data-moeda-celula]').forEach(function (el) {
      var t = taxas[el.dataset.moedaCelula];
      el.textContent = t ? '1 R$ = ' + t.toLocaleString('pt-BR', { maximumFractionDigits: 3 }) + ' ' + el.dataset.moedaCelula : el.dataset.moedaCelula;
    });
  };
  var taxas = ler('tripora_cambio', 360);
  if (taxas) aplicar(taxas);
  else fetch('https://open.er-api.com/v6/latest/BRL')
    .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
    .then(function (d) { if (d && d.rates) { guardar('tripora_cambio', d.rates); aplicar(d.rates); } else throw new Error('sem taxas'); })
    .catch(function () { aplicar({}); });
})();
