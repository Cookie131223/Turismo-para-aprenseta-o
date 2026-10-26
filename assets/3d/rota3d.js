// Mini globo com a rota de São Paulo até o destino (página do destino e reserva).
import { temWebGL } from './util3d.js';
import { criarGlobo } from './globo3d.js';

document.querySelectorAll('[data-globo-rota]').forEach(box => {
  if (!temWebGL()) return;
  let d; try { d = JSON.parse(box.dataset.globoRota); } catch { return; }
  if (!Number.isFinite(+d.lat)) return;
  box.classList.add('globo-3d');
  criarGlobo(box, { destinos: [d], focoInicial: d.id, giro: false, escala: 0.92 });
});
