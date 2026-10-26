// Globo de "Minhas viagens": um pino por destino reservado (verde = pago, amarelo = aguardando).
import { temWebGL } from './util3d.js';
import { criarGlobo } from './globo3d.js';

const box = document.querySelector('[data-globo-viagens]');
if (box && temWebGL()) {
  let destinos = [];
  try { destinos = JSON.parse(box.dataset.globoViagens || '[]'); } catch { destinos = []; }
  box.classList.add('globo-3d');
  criarGlobo(box, { destinos, escala: 1.05, aviao: destinos.length > 0 });
}
