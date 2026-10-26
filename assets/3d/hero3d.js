// Globo do topo da página inicial. Tocar num pino gira o globo até a cidade e abre um mini card do destino.
import { temWebGL } from './util3d.js';
import { criarGlobo } from './globo3d.js';

const card = document.querySelector('.hero-card');
if (card && temWebGL()) {
  let destinos = [];
  try { destinos = JSON.parse(card.dataset.destinos || '[]'); } catch { destinos = []; }
  card.classList.add('globo-3d');

  const popup = document.createElement('div');
  popup.className = 'globo-popup';
  popup.hidden = true;
  popup.setAttribute('role', 'dialog');
  popup.setAttribute('aria-live', 'polite');
  card.append(popup);

  const dica = document.createElement('span');
  dica.className = 'globo-dica';
  dica.textContent = 'Toque num pino para ver o destino';
  card.append(dica);

  const brl = v => 'R$ ' + Number(v).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  let globo;
  const fechar = () => { popup.hidden = true; globo && globo.soltar(); };
  const abrir = d => {
    dica.hidden = true;
    popup.replaceChildren();
    const img = document.createElement('img'); img.src = d.imagem; img.alt = d.nome; img.loading = 'lazy';
    const corpo = document.createElement('div'); corpo.className = 'globo-popup-corpo';
    const pais = document.createElement('p'); pais.className = 'eyebrow'; pais.textContent = d.pais;
    const nome = document.createElement('h3'); nome.textContent = d.nome;
    const info = document.createElement('p'); info.className = 'muted'; info.textContent = (d.km ? d.km.toLocaleString('pt-BR') + ' km de São Paulo · voo de ~' + d.voo : '');
    const preco = document.createElement('p'); preco.className = 'price'; preco.innerHTML = '<span class="price-label">a partir de</span>'; preco.append(brl(d.preco));
    const acoes = document.createElement('div'); acoes.className = 'globo-popup-acoes';
    const ver = document.createElement('a'); ver.className = 'btn secondary small'; ver.href = 'destino.php?id=' + d.id; ver.textContent = 'Ver destino';
    const reservar = document.createElement('a'); reservar.className = 'btn small'; reservar.href = 'reserva.php?destino_id=' + d.id; reservar.textContent = 'Reservar';
    acoes.append(ver, reservar);
    const x = document.createElement('button'); x.type = 'button'; x.className = 'globo-popup-fechar'; x.setAttribute('aria-label', 'Fechar'); x.textContent = '×'; x.onclick = fechar;
    corpo.append(pais, nome, info, preco, acoes);
    popup.append(x, img, corpo);
    popup.hidden = false;
  };

  globo = criarGlobo(card, { destinos, reservaInferior: 'auto', clicavel: true, onClicar: abrir });
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && !popup.hidden) fechar(); });
}
