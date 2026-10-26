// Globo 3D reutilizável: continentes em pontos, pinos, rotas saindo do Brasil e aviãozinho.
// Usado no topo da página inicial, na reserva, na página do destino e em "Minhas viagens".
import * as THREE from 'three';
import { reduzMovimento, telaTouch, criarLaco, acompanharTamanho, texturaPonto } from './util3d.js';

export const CORES = { ciano: '#39d0ff', roxo: '#7c5cff', verde: '#38d996', amarelo: '#ffc24b', oceano: '#0b1a2c', fundo: '#07111f' };
export const ORIGEM = { nome: 'São Paulo', lat: -23.55, lon: -46.63 };
// lado da etiqueta para cidades muito próximas não se sobreporem
const LADOS = { 'roma': 'dir', 'lisboa': 'esq', 'são paulo': 'esq', 'rio de janeiro': 'dir', 'buenos aires': 'baixo' };

// Continentes (Natural Earth 110m) em pontos: [latitude, pontos na linha, início, quantidade, início, quantidade...]
const TERRA = [[-58.0,95],[-56.0,101],[-54.0,106,31,2],[-52.0,111,32,2],[-50.0,116,34,2],[-48.0,120,35,3],[-46.0,125,36,3,120,2],[-44.0,129,38,3,125,1],[-42.0,134,39,4,121,1,131,1],[-40.0,138,41,4,136,1],[-38.0,142,42,6,126,3,140,1],[-36.0,146,43,7,130,4,143,1],[-34.0,149,45,7,82,2,122,2,130,7],[-32.0,153,46,8,84,5,126,5,133,8],[-30.0,156,47,9,85,6,128,16],[-28.0,159,48,10,86,8,130,17],[-26.0,162,49,10,88,8,132,18],[-24.0,164,50,11,89,9,102,2,134,17],[-22.0,167,51,13,90,10,104,2,137,16,160,1],[-20.0,169,52,14,91,10,105,2,140,14],[-18.0,171,52,15,91,12,106,3,144,11],[-16.0,173,51,17,92,14,108,2,146,6,154,2],[-14.0,175,50,19,93,14,111,1,151,3,156,1],[-12.0,176,50,20,95,13,153,2,157,1],[-10.0,177,50,21,95,13,147,1,149,1,161,1],[-8.0,178,50,22,95,13,143,3,157,3,161,1],[-6.0,179,49,23,96,13,142,1,158,5,164,1,166,1],[-4.0,180,49,22,96,14,141,2,147,1,156,6,166,1],[-2.0,180,50,18,95,16,140,2,145,3,150,1,156,1,158,2],[0.0,180,50,15,95,16,140,2,145,4],[2.0,180,51,14,95,18,139,3,146,3],[4.0,180,51,13,94,20,138,1,140,2,147,2],[6.0,179,51,10,84,6,92,22,129,1,139,1,147,1,151,1],[8.0,178,50,10,83,31,128,1,138,1,149,3],[10.0,177,46,2,51,2,54,4,81,32,126,1,140,1],[12.0,176,46,1,80,29,125,2,136,1,138,3],[14.0,175,43,4,79,32,124,2,135,6,146,2],[16.0,173,40,4,79,26,107,5,122,3,132,7,144,1],[18.0,171,37,7,48,1,51,1,78,26,105,7,120,5,130,6,143,1],[20.0,169,11,1,35,4,42,1,48,1,77,25,103,9,119,6,128,6,136,1],[22.0,167,9,1,34,4,44,1,46,1,76,25,102,9,116,9,126,10],[24.0,164,31,1,33,4,75,23,99,9,113,23],[26.0,162,30,1,32,5,44,1,74,22,97,7,107,28],[28.0,159,29,8,43,1,74,27,102,31],[30.0,156,29,10,42,1,74,57],[32.0,153,27,15,72,11,85,2,91,37],[34.0,149,25,17,72,7,89,35,129,2],[36.0,146,24,18,73,4,88,34,124,2,128,2],[38.0,142,23,18,68,3,76,1,79,1,81,37,120,2,126,1],[40.0,138,21,20,66,3,72,1,77,1,79,36,116,2],[42.0,134,21,20,64,4,70,3,74,3,79,1,83,32,119,1],[44.0,129,20,20,64,3,68,1,70,5,79,34,115,2],[46.0,125,19,21,41,1,62,11,74,1,76,34],[48.0,120,18,20,40,2,59,49],[50.0,116,17,20,39,1,58,45],[52.0,111,16,14,31,7,52,1,54,2,57,43],[54.0,106,15,14,30,6,50,1,52,1,56,37,99,1],[56.0,101,5,1,13,13,29,4,49,1,54,1,56,33,94,2],[58.0,95,6,1,12,11,27,4,46,1,51,1,54,31,89,1],[60.0,90,3,5,9,12,26,2,46,4,52,32,85,1],[62.0,85,3,17,24,1,31,1,44,3,48,36],[64.0,79,4,16,21,1,24,1,28,3,35,1,42,2,45,34],[66.0,73,0,2,3,16,21,3,26,3,32,2,39,5,45,28],[68.0,67,0,1,3,9,13,1,15,3,19,2,24,4,36,5,43,24],[70.0,62,3,3,8,1,11,4,16,3,22,5,34,2,43,16,60,1],[72.0,56,8,1,10,3,14,2,19,5,36,1,40,8,50,1],[74.0,50,8,1,17,5,37,3],[76.0,44,9,1,15,5,29,1,33,3],[78.0,37,8,1,10,6],[80.0,31,7,2,10,4,17,1,23,1],[82.0,25,6,5]];;;

export function noGlobo(lat, lon, r = 1) {
  const phi = THREE.MathUtils.degToRad(90 - lat), theta = THREE.MathUtils.degToRad(lon + 180);
  return new THREE.Vector3(-r * Math.sin(phi) * Math.cos(theta), r * Math.cos(phi), r * Math.sin(phi) * Math.sin(theta));
}

function etiqueta(texto, cor, lado) {
  const c = document.createElement('canvas'); const g = c.getContext('2d');
  const fonte = '700 44px Inter, system-ui, sans-serif';
  g.font = fonte; const w = Math.ceil(g.measureText(texto).width) + 36;
  c.width = w; c.height = 72;
  g.font = fonte; g.fillStyle = 'rgba(7,17,31,.72)';
  g.beginPath(); g.roundRect ? g.roundRect(0, 6, w, 60, 30) : g.rect(0, 6, w, 60); g.fill();
  g.fillStyle = cor; g.textBaseline = 'middle'; g.fillText(texto, 18, 37);
  const t = new THREE.CanvasTexture(c); t.colorSpace = THREE.SRGBColorSpace;
  const s = new THREE.Sprite(new THREE.SpriteMaterial({ map: t, transparent: true, depthWrite: false, depthTest: false }));
  s.renderOrder = 5;
  s.scale.set(0.13 * (w / 72), 0.13, 1);
  if (lado === 'dir') s.center.set(-0.08, 0.5);
  else if (lado === 'esq') s.center.set(1.08, 0.5);
  else if (lado === 'baixo') s.center.set(0.5, 1.25);
  else s.center.set(0.5, -0.2);
  return s;
}

function aviaozinho() {
  const g = new THREE.Group();
  const mat = new THREE.MeshStandardMaterial({ color: '#ffffff', emissive: CORES.ciano, emissiveIntensity: 0.35, roughness: 0.4 });
  const corpo = new THREE.Mesh(new THREE.CylinderGeometry(0.012, 0.007, 0.09, 8), mat); corpo.rotation.x = Math.PI / 2; g.add(corpo);
  const asa = new THREE.Mesh(new THREE.BoxGeometry(0.1, 0.004, 0.024), mat); asa.position.z = 0.006; g.add(asa);
  const cauda = new THREE.Mesh(new THREE.BoxGeometry(0.04, 0.004, 0.014), mat); cauda.position.z = -0.038; g.add(cauda);
  const leme = new THREE.Mesh(new THREE.BoxGeometry(0.003, 0.022, 0.014), mat); leme.position.set(0, 0.011, -0.038); g.add(leme);
  return g;
}

/**
 * Cria um globo dentro de `container` (que deve ter position: relative e altura definida).
 * opcoes:
 *   destinos: [{ id, nome, lat, lon, cor?, corRota?, categoria?, rota? }]   (rota false = sem linha saindo do Brasil)
 *   mostrarOrigem (true), corOrigem, etiquetas (true), aviao (true), giro (true)
 *   reservaInferior: px livres embaixo para texto ('auto' = mede o texto do card)
 *   clicavel: chama onClicar(destino) ao tocar num pino; onPassar(destino|null, x, y) ao passar o mouse
 *   arrastar: permite girar o globo com mouse/dedo
 *   maxRotas: quantas rotas aparecem ao mesmo tempo (em rodízio). Padrão: todas.
 *   focoInicial: id do destino que começa virado para a câmera
 * Retorna { focar(id), soltar(), filtrar(categoria|null) }.
 */
export function criarGlobo(container, opcoes = {}) {
  const o = Object.assign({ destinos: [], mostrarOrigem: true, corOrigem: CORES.verde, etiquetas: true, aviao: true, giro: true, reservaInferior: 0,
    clicavel: false, onClicar: null, onPassar: null, arrastar: false, maxRotas: Infinity, focoInicial: null, escala: 1 }, opcoes);
  const reduzir = reduzMovimento();
  const canvas = document.createElement('canvas');
  canvas.className = 'globo-canvas';
  canvas.setAttribute('aria-hidden', 'true');
  container.prepend(canvas);

  const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true, powerPreference: 'low-power' });
  renderer.setClearColor(0x000000, 0);
  renderer.outputColorSpace = THREE.SRGBColorSpace;
  const cena = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(32, 1, 0.1, 80);
  camera.position.set(0, 0, 4.2);

  cena.add(new THREE.AmbientLight('#ffffff', 0.7));
  const sol = new THREE.DirectionalLight(CORES.roxo, 2.2); sol.position.set(-3, 2.5, 3); cena.add(sol);
  const contraluz = new THREE.DirectionalLight(CORES.ciano, 1.4); contraluz.position.set(3, -1, -2); cena.add(contraluz);

  const inclinacao = new THREE.Group(); // parallax do mouse + foco
  const globo = new THREE.Group();      // gira sozinho / com o arraste
  inclinacao.add(globo); cena.add(inclinacao);

  globo.add(new THREE.Mesh(new THREE.SphereGeometry(1, 64, 48), new THREE.MeshPhongMaterial({ color: CORES.oceano, emissive: '#06101d', shininess: 18, specular: '#1d3550' })));
  inclinacao.add(new THREE.Mesh(new THREE.SphereGeometry(1.14, 64, 48), new THREE.ShaderMaterial({
    uniforms: { cor: { value: new THREE.Color(CORES.ciano) } },
    vertexShader: 'varying vec3 vN; void main(){ vN = normalize(normalMatrix * normal); gl_Position = projectionMatrix * modelViewMatrix * vec4(position,1.0); }',
    fragmentShader: 'uniform vec3 cor; varying vec3 vN; void main(){ float i = pow(0.68 - dot(vN, vec3(0.0,0.0,1.0)), 3.5); gl_FragColor = vec4(cor, 1.0) * i; }',
    side: THREE.BackSide, blending: THREE.AdditiveBlending, transparent: true, depthWrite: false,
  })));

  const pos = [], cor = [];
  const c1 = new THREE.Color(CORES.ciano), c2 = new THREE.Color(CORES.roxo), tmp = new THREE.Color();
  for (const [lat, n, ...runs] of TERRA) {
    for (let k = 0; k < runs.length; k += 2) {
      for (let i = runs[k]; i < runs[k] + runs[k + 1]; i++) {
        const v = noGlobo(lat, -180 + (i + 0.5) * 360 / n, 1.004);
        pos.push(v.x, v.y, v.z);
        tmp.copy(c1).lerp(c2, (lat + 60) / 145); cor.push(tmp.r, tmp.g, tmp.b);
      }
    }
  }
  const geoTerra = new THREE.BufferGeometry();
  geoTerra.setAttribute('position', new THREE.Float32BufferAttribute(pos, 3));
  geoTerra.setAttribute('color', new THREE.Float32BufferAttribute(cor, 3));
  globo.add(new THREE.Points(geoTerra, new THREE.PointsMaterial({ size: 0.034, map: texturaPonto(THREE), vertexColors: true, transparent: true, opacity: 0.95, depthWrite: false })));

  const matGrade = new THREE.LineBasicMaterial({ color: CORES.ciano, transparent: true, opacity: 0.07 });
  for (const lat of [-60, -30, 0, 30, 60]) {
    const pts = []; for (let lon = -180; lon <= 180; lon += 6) pts.push(noGlobo(lat, lon, 1.002));
    globo.add(new THREE.Line(new THREE.BufferGeometry().setFromPoints(pts), matGrade));
  }

  // ---------- pinos ----------
  const pinos = [], etiquetas = [], alvosClique = [];
  const geoPino = new THREE.SphereGeometry(0.022, 16, 12), geoAnel = new THREE.RingGeometry(0.03, 0.04, 32), geoAlvo = new THREE.SphereGeometry(0.07, 8, 6);
  const geoBrilho = new THREE.PlaneGeometry(0.16, 0.16), texBrilho = texturaPonto(THREE);
  const matAlvo = new THREE.MeshBasicMaterial({ visible: false });
  const marcar = (d, corPino, origem = false) => {
    const base = noGlobo(d.lat, d.lon, 1.0);
    const pino = new THREE.Mesh(geoPino, new THREE.MeshBasicMaterial({ color: corPino, transparent: true }));
    pino.position.copy(noGlobo(d.lat, d.lon, 1.02)); globo.add(pino);
    // halo luminoso
    const brilho = new THREE.Mesh(geoBrilho, new THREE.MeshBasicMaterial({ color: corPino, map: texBrilho, transparent: true, opacity: 0.55, blending: THREE.AdditiveBlending, depthWrite: false, side: THREE.DoubleSide }));
    brilho.position.copy(noGlobo(d.lat, d.lon, 1.012)); brilho.lookAt(base.clone().multiplyScalar(2)); globo.add(brilho);
    const anel = new THREE.Mesh(geoAnel, new THREE.MeshBasicMaterial({ color: corPino, transparent: true, opacity: 0.8, side: THREE.DoubleSide, depthWrite: false }));
    anel.position.copy(noGlobo(d.lat, d.lon, 1.006)); anel.lookAt(base.clone().multiplyScalar(2)); globo.add(anel);
    if (o.etiquetas || origem) {
      const lado = d.lado || LADOS[String(d.nome).toLowerCase()];
      const e = etiqueta(d.nome, corPino, lado); e.position.copy(noGlobo(d.lat, d.lon, lado ? 1.05 : 1.06)); globo.add(e); etiquetas.push(e);
    }
    const info = { d, pino, anel, brilho, fase: Math.random() * Math.PI * 2, destaque: 0, ativo: 1 };
    if (o.clicavel && !origem) { const alvo = new THREE.Mesh(geoAlvo, matAlvo); alvo.position.copy(pino.position); alvo.userData.info = info; globo.add(alvo); alvosClique.push(alvo); }
    pinos.push(info);
    return info;
  };
  if (o.mostrarOrigem) marcar(ORIGEM, o.corOrigem, true);
  const validos = o.destinos.filter(d => Number.isFinite(+d.lat) && Number.isFinite(+d.lon));
  validos.forEach(d => marcar(d, d.cor || '#ffffff'));

  // ---------- rotas saindo do Brasil ----------
  const origem = noGlobo(ORIGEM.lat, ORIGEM.lon, 1.0);
  const rotas = validos.filter(d => d.rota !== false).map((d, i) => {
    const fim = noGlobo(d.lat, d.lon, 1.0);
    const dist = origem.distanceTo(fim);
    const meio = origem.clone().add(fim).multiplyScalar(0.5).normalize().multiplyScalar(1 + Math.max(dist, 0.05) * 0.42);
    const curva = new THREE.QuadraticBezierCurve3(origem.clone().multiplyScalar(1.01), meio, fim.clone().multiplyScalar(1.01));
    const pontos = curva.getPoints(80);
    const geo = new THREE.BufferGeometry().setFromPoints(pontos);
    const linha = new THREE.Line(geo, new THREE.LineBasicMaterial({ color: d.corRota || d.cor || (i % 2 ? CORES.roxo : CORES.ciano), transparent: true, opacity: 0.8 }));
    globo.add(linha);
    return { d, curva, geo, linha, total: pontos.length, atraso: i * 0.9, inicio: null, fim: null };
  });
  const rodizio = Number.isFinite(o.maxRotas) && rotas.length > o.maxRotas;
  let filtro = null, proxima = 0, ultimaAtivacao = -10;
  if (rodizio) rotas.forEach(r => { r.linha.visible = false; });

  const aviao = aviaozinho(); aviao.visible = false; globo.add(aviao);
  let rotaAviao = null, progressoAviao = 0;

  // ---------- orientação, foco e filtro ----------
  globo.rotation.y = -0.9;
  let alvoGiro = null, focoX = 0;
  const anguloPara = (d) => {
    const p = noGlobo(d.lat, d.lon, 1);
    let a = Math.atan2(-p.x, p.z);
    const atual = globo.rotation.y;
    while (a - atual > Math.PI) a -= Math.PI * 2;
    while (a - atual < -Math.PI) a += Math.PI * 2;
    return { y: a, x: THREE.MathUtils.clamp(THREE.MathUtils.degToRad(d.lat) * 0.85, -0.7, 0.7) };
  };
  const api = {
    focar(id) {
      const d = validos.find(v => String(v.id) === String(id)) || (id === 'origem' ? ORIGEM : null);
      if (!d) return;
      const a = anguloPara(d); alvoGiro = a.y; focoX = a.x;
      pinos.forEach(p => { p.destaque = p.d === d ? 1 : 0; });
    },
    soltar() { alvoGiro = null; focoX = 0; pinos.forEach(p => { p.destaque = 0; }); },
    filtrar(categoria) {
      filtro = categoria || null;
      pinos.forEach(p => { p.ativo = !filtro || p.d === ORIGEM || p.d.categoria === filtro ? 1 : 0; });
      // rotas da outra categoria somem logo
      if (rodizio) rotas.forEach(r => { if (r.inicio !== null && filtro && r.d.categoria !== filtro) r.fim = Math.min(r.fim, tempo + 0.4); });
      ultimaAtivacao = -10;
    },
  };
  if (o.focoInicial != null) {
    const d = validos.find(v => String(v.id) === String(o.focoInicial));
    if (d) {
      // perto: mostra origem e destino juntos (ponto médio); longe (outro lado do mundo): centraliza o destino
      const km = noGlobo(d.lat, d.lon).distanceTo(noGlobo(ORIGEM.lat, ORIGEM.lon)) * 6371;
      const meio = km < 9000 ? { lat: (d.lat + ORIGEM.lat) / 2, lon: Math.abs(d.lon - ORIGEM.lon) > 180 ? (d.lon + ORIGEM.lon) / 2 + 180 : (d.lon + ORIGEM.lon) / 2 } : { lat: d.lat * 0.7, lon: d.lon - Math.sign(d.lon - ORIGEM.lon) * 25 };
      const a = anguloPara(meio); globo.rotation.y = a.y; alvoGiro = a.y; focoX = a.x * 0.6;
      pinos.forEach(p => { p.destaque = p.d === d ? 1 : 0; });
    }
  }

  // ---------- parallax suave com o mouse ----------
  const alvo = { x: 0, y: 0 };
  if (!reduzir && !telaTouch()) {
    window.addEventListener('pointermove', e => {
      alvo.x = (e.clientX / window.innerWidth) * 2 - 1;
      alvo.y = (e.clientY / window.innerHeight) * 2 - 1;
    }, { passive: true });
  }

  // ---------- mouse/dedo: passar, clicar e arrastar ----------
  let arrastando = false, arrastou = false, x0 = 0, y0 = 0, velGiro = 0, pausaGiroAte = 0;
  if (o.clicavel || o.arrastar) {
    canvas.style.pointerEvents = 'auto';
    if (o.arrastar) canvas.style.touchAction = 'pan-y'; // deslizar na vertical continua rolando a página
    const ray = new THREE.Raycaster(), ponteiro = new THREE.Vector2(), w = new THREE.Vector3(), centroG = new THREE.Vector3();
    const acertar = e => {
      if (!alvosClique.length) return null;
      const r = canvas.getBoundingClientRect();
      ponteiro.set(((e.clientX - r.left) / r.width) * 2 - 1, -((e.clientY - r.top) / r.height) * 2 + 1);
      ray.setFromCamera(ponteiro, camera);
      for (const hit of ray.intersectObjects(alvosClique, false)) {
        const info = hit.object.userData.info;
        if (!info.ativo) continue;
        hit.object.getWorldPosition(w); inclinacao.getWorldPosition(centroG);
        // ignora pinos do lado de trás do globo
        if (w.sub(centroG).normalize().dot(camera.position.clone().sub(centroG).normalize()) > 0.15) return info;
      }
      return null;
    };
    canvas.addEventListener('pointerdown', e => {
      if (!o.arrastar) return;
      arrastando = true; arrastou = false; x0 = e.clientX; y0 = e.clientY; velGiro = 0;
      canvas.setPointerCapture(e.pointerId);
    });
    canvas.addEventListener('pointermove', e => {
      if (arrastando) {
        const dx = e.clientX - x0, dy = e.clientY - y0;
        if (!arrastou && Math.hypot(dx, dy) > 6) { arrastou = true; alvoGiro = null; canvas.style.cursor = 'grabbing'; o.onPassar && o.onPassar(null); }
        if (arrastou) {
          const k = 2.6 / Math.max(200, canvas.clientWidth);
          globo.rotation.y += dx * k; velGiro = dx * k * 60;
          focoX = THREE.MathUtils.clamp(focoX + dy * k, -0.6, 0.6);
          x0 = e.clientX; y0 = e.clientY; pausaGiroAte = tempo + 3;
        }
        return;
      }
      const info = acertar(e);
      if (info) pausaGiroAte = tempo + 1.5; // para de girar enquanto o mouse está sobre um pino
      canvas.style.cursor = info ? 'pointer' : (o.arrastar ? 'grab' : 'default');
      if (o.onPassar) o.onPassar(info ? info.d : null, e.clientX, e.clientY);
    });
    const terminar = () => { if (arrastando) { arrastando = false; canvas.style.cursor = o.arrastar ? 'grab' : 'default'; } };
    canvas.addEventListener('pointerup', terminar);
    canvas.addEventListener('pointercancel', () => { terminar(); arrastou = false; });
    canvas.addEventListener('pointerleave', () => { if (!arrastando && o.onPassar) o.onPassar(null); });
    canvas.addEventListener('click', e => {
      if (arrastou) { arrastou = false; return; }
      const info = acertar(e);
      if (info && o.clicavel) { api.focar(info.d.id); o.onPassar && o.onPassar(null); o.onClicar && o.onClicar(info.d); }
    });
    if (o.arrastar) canvas.style.cursor = 'grab';
  }

  const texto = o.reservaInferior === 'auto' ? container.querySelector(':scope > div:not(.globo-popup):not(.globo-topo)') : null;
  const topo = container.querySelector(':scope > .globo-topo');
  acompanharTamanho(container, renderer, camera, (w, h) => {
    const embaixo = o.reservaInferior === 'auto' ? (texto ? texto.offsetHeight + 34 : 0) : (+o.reservaInferior || 0);
    const emcima = topo ? topo.offsetHeight + 10 : 0;
    const livre = Math.max(120, h - embaixo - emcima);
    const diametroPx = Math.min(livre * 1.02, w * 0.84) * o.escala;
    const pxPorUnidade = diametroPx / 2.2;
    camera.position.z = (h / pxPorUnidade) / (2 * Math.tan(THREE.MathUtils.degToRad(camera.fov / 2)));
    // centro do globo no meio do espaço livre
    inclinacao.position.y = (h / 2 - (emcima + livre / 2)) / pxPorUnidade;
  });

  let tempo = 0;
  const v = new THREE.Vector3(), paraCamera = new THREE.Vector3(), centro = new THREE.Vector3();
  const DURACAO_ROTA = 6.5, CRESCER = 1.6, SUMIR = 0.8;
  const quadro = (dt) => {
    tempo += dt;
    // giro: foco > arraste com inércia > rotação automática
    if (alvoGiro !== null) globo.rotation.y += (alvoGiro - globo.rotation.y) * Math.min(1, dt * 3.2);
    else if (arrastando) { /* controlado pelo dedo/mouse */ }
    else if (Math.abs(velGiro) > 0.002) { globo.rotation.y += velGiro * dt; velGiro *= Math.pow(0.04, dt); }
    else if (o.giro && !reduzir && tempo > pausaGiroAte) globo.rotation.y += dt * 0.09;
    const parX = reduzir ? 0 : alvo.y * 0.22, parY = reduzir ? 0 : alvo.x * 0.35;
    const peso = alvoGiro !== null || arrastando ? 0.3 : 1;
    inclinacao.rotation.x += ((focoX + parX * peso) - inclinacao.rotation.x) * 0.05;
    inclinacao.rotation.y += ((parY * peso) - inclinacao.rotation.y) * 0.05;

    // rotas
    if (!rodizio) {
      for (const r of rotas) {
        const p = reduzir ? 1 : THREE.MathUtils.clamp((tempo - r.atraso) / CRESCER, 0, 1);
        r.geo.setDrawRange(0, Math.max(2, Math.floor(r.total * p)));
        r.linha.material.opacity = 0.8 * (filtro && r.d.categoria !== filtro ? 0.12 : 1);
      }
    } else {
      const ativas = rotas.filter(r => r.inicio !== null);
      const pool = rotas.filter(r => r.inicio === null && (!filtro || r.d.categoria === filtro));
      if (ativas.length < o.maxRotas && pool.length && tempo - ultimaAtivacao > (reduzir ? 0 : 1.1)) {
        // próxima rota da fila (rodízio)
        let escolhida = null;
        for (let k = 0; k < rotas.length && !escolhida; k++) { const r = rotas[(proxima + k) % rotas.length]; if (pool.includes(r)) { escolhida = r; proxima = (rotas.indexOf(r) + 1) % rotas.length; } }
        if (escolhida) { escolhida.inicio = tempo; escolhida.fim = reduzir ? Infinity : tempo + DURACAO_ROTA; escolhida.linha.visible = true; ultimaAtivacao = tempo; }
      }
      for (const r of rotas) {
        if (r.inicio === null) continue;
        if (tempo >= r.fim) { r.inicio = null; r.linha.visible = false; if (rotaAviao === r) rotaAviao = null; continue; }
        const p = reduzir ? 1 : THREE.MathUtils.clamp((tempo - r.inicio) / CRESCER, 0, 1);
        r.geo.setDrawRange(0, Math.max(2, Math.floor(r.total * p)));
        r.linha.material.opacity = 0.85 * THREE.MathUtils.clamp((r.fim - tempo) / SUMIR, 0, 1);
      }
    }

    // pinos: pulso, destaque e filtro
    for (const p of pinos) {
      const s = 1 + 0.6 * (0.5 + 0.5 * Math.sin(tempo * 2.4 + p.fase));
      const a = p.ativo;
      p.anel.visible = a > 0;
      p.anel.scale.setScalar((reduzir ? 1.2 : s) * (1 + p.destaque * 0.8));
      p.anel.material.opacity = reduzir ? 0.6 : 0.9 - (s - 1) * 1.1;
      p.pino.scale.setScalar((1 + p.destaque * 0.6) * (a ? 1 : 0.6));
      p.pino.material.opacity = a ? 1 : 0.22;
      p.brilho.material.opacity = a ? 0.55 + p.destaque * 0.3 : 0.05;
    }

    // avião percorrendo uma rota visível
    if (o.aviao && rotas.length) {
      const visiveis = rodizio ? rotas.filter(r => r.inicio !== null && tempo - r.inicio > CRESCER) : rotas.filter(r => !filtro || r.d.categoria === filtro);
      if (!rotaAviao || !visiveis.includes(rotaAviao)) { rotaAviao = visiveis[Math.floor(Math.random() * visiveis.length)] || null; progressoAviao = 0; }
      if (rotaAviao) {
        if (!reduzir) { progressoAviao += dt / 4.5; if (progressoAviao >= 1) { progressoAviao = 0; rotaAviao = null; } }
        else progressoAviao = 0.55;
      }
      if (rotaAviao) {
        const curva = rotaAviao.curva;
        aviao.position.copy(curva.getPointAt(progressoAviao));
        aviao.up.copy(aviao.position).normalize();
        aviao.lookAt(aviao.position.clone().add(curva.getTangentAt(progressoAviao)));
        aviao.visible = reduzir || tempo > (rodizio ? CRESCER : Math.min(rotas[rotas.length - 1].atraso, 2.5));
      } else aviao.visible = false;
    }
    inclinacao.getWorldPosition(centro);
    for (const e of etiquetas) {
      e.getWorldPosition(v); paraCamera.copy(camera.position).sub(v).normalize();
      e.material.opacity = THREE.MathUtils.clamp((v.sub(centro).normalize().dot(paraCamera) - 0.08) * 5, 0, 1);
    }
    renderer.render(cena, camera);
  };
  const laco = criarLaco(container, quadro);
  laco.renderizarUmaVez();
  container.classList.add('globo-pronto');
  return api;
}
