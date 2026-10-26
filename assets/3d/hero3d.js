// Globo do topo da página inicial.
// - pinos em todos os destinos de destinos.js (Brasil e Internacional em cores diferentes) + legenda
// - filtros "Brasil" / "Internacional", card ao passar o mouse, clique gira até o destino e mostra "Ver destino"
// - até 6 rotas saindo de São Paulo por vez, em rodízio; dá para girar o globo com o mouse ou o dedo
import { temWebGL } from './util3d.js';
import { criarGlobo } from './globo3d.js';
import { DESTINOS, CORES_CATEGORIA } from './destinos.js';

const chave = s => String(s).normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();
const brl = v => 'R$ ' + Number(v).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const el = (tag, cls, txt) => { const e = document.createElement(tag); if (cls) e.className = cls; if (txt != null) e.textContent = txt; return e; };

function montarLista(card) {
  // destinos cadastrados no site (vêm do PHP): id, preço, foto
  let doSite = [];
  try { doSite = JSON.parse(card.dataset.destinos || '[]'); } catch { doSite = []; }
  const porNome = new Map(doSite.map(d => [chave(d.nome), d]));
  const lista = DESTINOS.map((d, i) => {
    const s = porNome.get(chave(d.nome));
    if (s) porNome.delete(chave(d.nome));
    return { ...d, id: 'g' + i, link: s ? 'destino.php?id=' + s.id : d.link, reservar: s ? 'reserva.php?destino_id=' + s.id : null,
      preco: s ? s.preco : null, imagem: s ? s.imagem : null, km: s ? s.km : null, voo: s ? s.voo : null, cor: CORES_CATEGORIA[d.categoria] };
  });
  // destinos que o admin cadastrou e não estão na lista fixa também ganham pino
  let extra = 0;
  for (const s of porNome.values()) {
    if (!Number.isFinite(+s.lat)) continue;
    const categoria = chave(s.pais).startsWith('brasil') ? 'Brasil' : 'Internacional';
    lista.push({ nome: s.nome, pais: s.pais, categoria, lat: +s.lat, lon: +s.lon, id: 'x' + (extra++), link: 'destino.php?id=' + s.id,
      reservar: 'reserva.php?destino_id=' + s.id, preco: s.preco, imagem: s.imagem, km: s.km, voo: s.voo, cor: CORES_CATEGORIA[categoria] });
  }
  return lista;
}

function iniciar(card) {
  const destinos = montarLista(card);
  card.classList.add('globo-3d', 'globo-hero');

  // filtros + legenda (acima do globo)
  const topo = el('div', 'globo-topo');
  const filtros = el('div', 'globo-filtros'); filtros.setAttribute('role', 'group'); filtros.setAttribute('aria-label', 'Filtrar destinos do globo');
  const legenda = el('div', 'globo-legenda');
  const botoes = {};
  for (const cat of ['Brasil', 'Internacional']) {
    const qtd = destinos.filter(d => d.categoria === cat).length;
    const b = el('button', 'globo-filtro'); b.type = 'button'; b.setAttribute('aria-pressed', 'false'); b.style.setProperty('--cor', CORES_CATEGORIA[cat]);
    b.append(el('i'), cat + ' ', el('small', null, String(qtd)));
    filtros.append(b); botoes[cat] = b;
    const item = el('span'); item.style.setProperty('--cor', CORES_CATEGORIA[cat]); item.append(el('i'), cat); legenda.append(item);
  }
  const itemOrigem = el('span', 'origem'); itemOrigem.style.setProperty('--cor', CORES_CATEGORIA.origem); itemOrigem.append(el('i'), 'São Paulo (partida)'); legenda.append(itemOrigem);
  topo.append(filtros, legenda);

  const dica = el('div', 'globo-tooltip'); dica.hidden = true; dica.setAttribute('aria-hidden', 'true');
  const popup = el('div', 'globo-popup'); popup.hidden = true; popup.setAttribute('role', 'dialog'); popup.setAttribute('aria-live', 'polite');
  card.append(popup, topo, dica);

  let globo;
  const fechar = () => { popup.hidden = true; globo && globo.soltar(); };

  const passar = (d, x, y) => {
    if (!d) { dica.hidden = true; return; }
    const r = card.getBoundingClientRect();
    dica.replaceChildren(el('strong', null, d.nome), el('span', null, d.pais));
    dica.style.setProperty('--cor', d.cor);
    dica.style.left = Math.min(Math.max(x - r.left, 70), r.width - 70) + 'px';
    dica.style.top = (y - r.top) + 'px';
    dica.hidden = false;
  };

  const abrir = d => {
    popup.replaceChildren();
    if (d.imagem) { const img = el('img'); img.src = d.imagem; img.alt = d.nome; img.loading = 'lazy'; popup.append(img); }
    const corpo = el('div', 'globo-popup-corpo');
    const cat = el('p', 'globo-popup-cat', d.categoria); cat.style.setProperty('--cor', d.cor);
    corpo.append(cat, el('h3', null, d.nome), el('p', 'eyebrow', d.pais));
    if (d.km) corpo.append(el('p', 'muted', d.km.toLocaleString('pt-BR') + ' km de São Paulo · voo de ~' + d.voo));
    if (d.preco != null) { const preco = el('p', 'price'); preco.append(el('span', 'price-label', 'a partir de'), brl(d.preco)); corpo.append(preco); }
    const acoes = el('div', 'globo-popup-acoes');
    if (d.link && d.link !== '#') {
      const ver = el('a', 'btn small' + (d.reservar ? ' secondary' : ''), 'Ver destino'); ver.href = d.link; acoes.append(ver);
      if (d.reservar) { const res = el('a', 'btn small', 'Reservar'); res.href = d.reservar; acoes.append(res); }
    } else {
      const breve = el('span', 'btn small secondary desativado', 'Pacote em breve'); breve.setAttribute('aria-disabled', 'true'); acoes.append(breve);
    }
    const x = el('button', 'globo-popup-fechar', '×'); x.type = 'button'; x.setAttribute('aria-label', 'Fechar'); x.onclick = fechar;
    corpo.append(acoes);
    popup.append(x, corpo);
    popup.classList.toggle('sem-foto', !d.imagem);
    popup.hidden = false;
  };

  globo = criarGlobo(card, {
    destinos, reservaInferior: 'auto', clicavel: true, arrastar: true, etiquetas: false, maxRotas: 6,
    corOrigem: CORES_CATEGORIA.origem, onPassar: passar, onClicar: abrir,
  });

  let ativo = null;
  for (const [cat, b] of Object.entries(botoes)) {
    b.addEventListener('click', () => {
      ativo = ativo === cat ? null : cat;
      for (const [c, bb] of Object.entries(botoes)) bb.setAttribute('aria-pressed', String(c === ativo));
      globo.filtrar(ativo);
      if (ativo && !popup.hidden) fechar();
    });
  }
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && !popup.hidden) fechar(); });
}

const card = document.querySelector('.hero-card');
if (card && temWebGL()) iniciar(card);
