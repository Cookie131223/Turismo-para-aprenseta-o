// Globo 3D do topo da página inicial: continentes em pontos, pinos nos destinos e rotas saindo do Brasil.
import * as THREE from 'three';
import { temWebGL, reduzMovimento, telaTouch, criarLaco, acompanharTamanho, texturaPonto } from './util3d.js';

// ===== PERSONALIZE AQUI =====
// Cores do site (as mesmas variáveis de css/main.css).
const CORES = { ciano: '#39d0ff', roxo: '#7c5cff', verde: '#38d996', oceano: '#0b1a2c', fundo: '#07111f' };
// Ponto de partida das rotas e destinos com pino (nome, latitude, longitude).
const ORIGEM = { nome: 'São Paulo', lat: -23.55, lon: -46.63, lado: 'esq' };
const DESTINOS = [
  { nome: 'Tóquio', lat: 35.68, lon: 139.69 },
  { nome: 'Nova York', lat: 40.71, lon: -74.01 },
  { nome: 'Roma', lat: 41.9, lon: 12.5, lado: 'dir' },
  { nome: 'Lisboa', lat: 38.72, lon: -9.14, lado: 'esq' },
  { nome: 'Buenos Aires', lat: -34.6, lon: -58.38 },
  { nome: 'Cancún', lat: 21.16, lon: -86.85 },
];
// =============================

// Continentes (Natural Earth 110m) em pontos: [latitude, pontos na linha, início, quantidade, início, quantidade...]
const TERRA = [[-58.0,95],[-56.0,101],[-54.0,106,31,2],[-52.0,111,32,2],[-50.0,116,34,2],[-48.0,120,35,3],[-46.0,125,36,3,120,2],[-44.0,129,38,3,125,1],[-42.0,134,39,4,121,1,131,1],[-40.0,138,41,4,136,1],[-38.0,142,42,6,126,3,140,1],[-36.0,146,43,7,130,4,143,1],[-34.0,149,45,7,82,2,122,2,130,7],[-32.0,153,46,8,84,5,126,5,133,8],[-30.0,156,47,9,85,6,128,16],[-28.0,159,48,10,86,8,130,17],[-26.0,162,49,10,88,8,132,18],[-24.0,164,50,11,89,9,102,2,134,17],[-22.0,167,51,13,90,10,104,2,137,16,160,1],[-20.0,169,52,14,91,10,105,2,140,14],[-18.0,171,52,15,91,12,106,3,144,11],[-16.0,173,51,17,92,14,108,2,146,6,154,2],[-14.0,175,50,19,93,14,111,1,151,3,156,1],[-12.0,176,50,20,95,13,153,2,157,1],[-10.0,177,50,21,95,13,147,1,149,1,161,1],[-8.0,178,50,22,95,13,143,3,157,3,161,1],[-6.0,179,49,23,96,13,142,1,158,5,164,1,166,1],[-4.0,180,49,22,96,14,141,2,147,1,156,6,166,1],[-2.0,180,50,18,95,16,140,2,145,3,150,1,156,1,158,2],[0.0,180,50,15,95,16,140,2,145,4],[2.0,180,51,14,95,18,139,3,146,3],[4.0,180,51,13,94,20,138,1,140,2,147,2],[6.0,179,51,10,84,6,92,22,129,1,139,1,147,1,151,1],[8.0,178,50,10,83,31,128,1,138,1,149,3],[10.0,177,46,2,51,2,54,4,81,32,126,1,140,1],[12.0,176,46,1,80,29,125,2,136,1,138,3],[14.0,175,43,4,79,32,124,2,135,6,146,2],[16.0,173,40,4,79,26,107,5,122,3,132,7,144,1],[18.0,171,37,7,48,1,51,1,78,26,105,7,120,5,130,6,143,1],[20.0,169,11,1,35,4,42,1,48,1,77,25,103,9,119,6,128,6,136,1],[22.0,167,9,1,34,4,44,1,46,1,76,25,102,9,116,9,126,10],[24.0,164,31,1,33,4,75,23,99,9,113,23],[26.0,162,30,1,32,5,44,1,74,22,97,7,107,28],[28.0,159,29,8,43,1,74,27,102,31],[30.0,156,29,10,42,1,74,57],[32.0,153,27,15,72,11,85,2,91,37],[34.0,149,25,17,72,7,89,35,129,2],[36.0,146,24,18,73,4,88,34,124,2,128,2],[38.0,142,23,18,68,3,76,1,79,1,81,37,120,2,126,1],[40.0,138,21,20,66,3,72,1,77,1,79,36,116,2],[42.0,134,21,20,64,4,70,3,74,3,79,1,83,32,119,1],[44.0,129,20,20,64,3,68,1,70,5,79,34,115,2],[46.0,125,19,21,41,1,62,11,74,1,76,34],[48.0,120,18,20,40,2,59,49],[50.0,116,17,20,39,1,58,45],[52.0,111,16,14,31,7,52,1,54,2,57,43],[54.0,106,15,14,30,6,50,1,52,1,56,37,99,1],[56.0,101,5,1,13,13,29,4,49,1,54,1,56,33,94,2],[58.0,95,6,1,12,11,27,4,46,1,51,1,54,31,89,1],[60.0,90,3,5,9,12,26,2,46,4,52,32,85,1],[62.0,85,3,17,24,1,31,1,44,3,48,36],[64.0,79,4,16,21,1,24,1,28,3,35,1,42,2,45,34],[66.0,73,0,2,3,16,21,3,26,3,32,2,39,5,45,28],[68.0,67,0,1,3,9,13,1,15,3,19,2,24,4,36,5,43,24],[70.0,62,3,3,8,1,11,4,16,3,22,5,34,2,43,16,60,1],[72.0,56,8,1,10,3,14,2,19,5,36,1,40,8,50,1],[74.0,50,8,1,17,5,37,3],[76.0,44,9,1,15,5,29,1,33,3],[78.0,37,8,1,10,6],[80.0,31,7,2,10,4,17,1,23,1],[82.0,25,6,5]];

const card = document.querySelector('.hero-card');
if (card && temWebGL()) iniciar(card);

function noGlobo(lat, lon, r = 1) {
  const phi = THREE.MathUtils.degToRad(90 - lat), theta = THREE.MathUtils.degToRad(lon + 180);
  return new THREE.Vector3(-r * Math.sin(phi) * Math.cos(theta), r * Math.cos(phi), r * Math.sin(phi) * Math.sin(theta));
}

function etiqueta(texto, cor, lado) {
  const c = document.createElement('canvas'); const g = c.getContext('2d');
  const fonte = '700 44px Inter, system-ui, sans-serif';
  g.font = fonte; const w = Math.ceil(g.measureText(texto).width) + 36;
  c.width = w; c.height = 72;
  g.font = fonte; g.fillStyle = 'rgba(7,17,31,.72)';
  g.beginPath(); g.roundRect(0, 6, w, 60, 30); g.fill();
  g.fillStyle = cor; g.textBaseline = 'middle'; g.fillText(texto, 18, 37);
  const t = new THREE.CanvasTexture(c); t.colorSpace = THREE.SRGBColorSpace;
  const s = new THREE.Sprite(new THREE.SpriteMaterial({ map: t, transparent: true, depthWrite: false, depthTest: false }));
  s.renderOrder = 5;
  s.scale.set(0.13 * (w / 72), 0.13, 1);
  // lado da etiqueta em relação ao pino (evita que nomes próximos se sobreponham)
  if (lado === 'dir') s.center.set(-0.08, 0.5); else if (lado === 'esq') s.center.set(1.08, 0.5); else s.center.set(0.5, -0.2);
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

function iniciar(card) {
  const reduzir = reduzMovimento();
  card.classList.add('globo-3d');
  const canvas = document.createElement('canvas');
  canvas.className = 'globo-canvas';
  canvas.setAttribute('aria-hidden', 'true');
  card.prepend(canvas);

  const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true, powerPreference: 'low-power' });
  renderer.setClearColor(0x000000, 0);
  renderer.outputColorSpace = THREE.SRGBColorSpace;
  const cena = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(32, 1, 0.1, 50);
  camera.position.set(0, 0, 4.2);

  cena.add(new THREE.AmbientLight('#ffffff', 0.7));
  const sol = new THREE.DirectionalLight(CORES.roxo, 2.2); sol.position.set(-3, 2.5, 3); cena.add(sol);
  const contraluz = new THREE.DirectionalLight(CORES.ciano, 1.4); contraluz.position.set(3, -1, -2); cena.add(contraluz);

  const inclinacao = new THREE.Group(); // recebe o parallax do mouse
  const globo = new THREE.Group();      // gira sozinho
  inclinacao.add(globo); cena.add(inclinacao);

  // esfera do oceano
  globo.add(new THREE.Mesh(new THREE.SphereGeometry(1, 64, 48), new THREE.MeshPhongMaterial({ color: CORES.oceano, emissive: '#06101d', shininess: 18, specular: '#1d3550' })));

  // brilho da atmosfera (borda iluminada)
  const atmosfera = new THREE.Mesh(new THREE.SphereGeometry(1.14, 64, 48), new THREE.ShaderMaterial({
    uniforms: { cor: { value: new THREE.Color(CORES.ciano) } },
    vertexShader: 'varying vec3 vN; void main(){ vN = normalize(normalMatrix * normal); gl_Position = projectionMatrix * modelViewMatrix * vec4(position,1.0); }',
    fragmentShader: 'uniform vec3 cor; varying vec3 vN; void main(){ float i = pow(0.68 - dot(vN, vec3(0.0,0.0,1.0)), 3.5); gl_FragColor = vec4(cor, 1.0) * i; }',
    side: THREE.BackSide, blending: THREE.AdditiveBlending, transparent: true, depthWrite: false,
  }));
  inclinacao.add(atmosfera);

  // continentes em pontos (degradê ciano → roxo do sul para o norte)
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

  // linhas de latitude bem discretas
  const matGrade = new THREE.LineBasicMaterial({ color: CORES.ciano, transparent: true, opacity: 0.07 });
  for (const lat of [-60, -30, 0, 30, 60]) {
    const pts = []; for (let lon = -180; lon <= 180; lon += 6) pts.push(noGlobo(lat, lon, 1.002));
    globo.add(new THREE.Line(new THREE.BufferGeometry().setFromPoints(pts), matGrade));
  }

  // pinos
  const pinos = [], etiquetas = [];
  const geoPino = new THREE.SphereGeometry(0.022, 16, 12), geoAnel = new THREE.RingGeometry(0.03, 0.04, 32);
  const marcar = (d, corPino, nomeVisivel) => {
    const base = noGlobo(d.lat, d.lon, 1.0);
    const pino = new THREE.Mesh(geoPino, new THREE.MeshBasicMaterial({ color: corPino }));
    pino.position.copy(noGlobo(d.lat, d.lon, 1.02)); globo.add(pino);
    const anel = new THREE.Mesh(geoAnel, new THREE.MeshBasicMaterial({ color: corPino, transparent: true, opacity: 0.8, side: THREE.DoubleSide, depthWrite: false }));
    anel.position.copy(noGlobo(d.lat, d.lon, 1.006)); anel.lookAt(base.clone().multiplyScalar(2)); globo.add(anel);
    if (nomeVisivel) { const e = etiqueta(d.nome, corPino, d.lado); e.position.copy(noGlobo(d.lat, d.lon, d.lado ? 1.05 : 1.06)); globo.add(e); etiquetas.push(e); }
    pinos.push({ anel, fase: Math.random() * Math.PI * 2 });
  };
  marcar(ORIGEM, CORES.verde, true);
  DESTINOS.forEach(d => marcar(d, '#ffffff', true));

  // rotas saindo do Brasil
  const origem = noGlobo(ORIGEM.lat, ORIGEM.lon, 1.0);
  const rotas = DESTINOS.map((d, i) => {
    const fim = noGlobo(d.lat, d.lon, 1.0);
    const dist = origem.distanceTo(fim);
    const meio = origem.clone().add(fim).multiplyScalar(0.5).normalize().multiplyScalar(1 + dist * 0.42);
    const curva = new THREE.QuadraticBezierCurve3(origem.clone().multiplyScalar(1.01), meio, fim.clone().multiplyScalar(1.01));
    const pontos = curva.getPoints(80);
    const geo = new THREE.BufferGeometry().setFromPoints(pontos);
    const linha = new THREE.Line(geo, new THREE.LineBasicMaterial({ color: i % 2 ? CORES.roxo : CORES.ciano, transparent: true, opacity: 0.75 }));
    globo.add(linha);
    return { curva, geo, total: pontos.length, atraso: i * 0.9 };
  });

  const aviao = aviaozinho(); globo.add(aviao);
  let rotaAviao = 0, progressoAviao = 0;

  // rotação inicial mostrando o Atlântico (Brasil, Europa e América do Norte)
  globo.rotation.y = -0.9;

  // parallax suave com o mouse
  const alvo = { x: 0, y: 0 };
  if (!reduzir && !telaTouch()) {
    window.addEventListener('pointermove', e => {
      alvo.x = (e.clientX / window.innerWidth) * 2 - 1;
      alvo.y = (e.clientY / window.innerHeight) * 2 - 1;
    }, { passive: true });
  }

  // Enquadra o globo na parte de cima do card, deixando o texto livre embaixo.
  const texto = card.querySelector(':scope > div');
  acompanharTamanho(card, renderer, camera, (w, h) => {
    const livre = Math.max(120, h - (texto ? texto.offsetHeight + 34 : 0));
    const diametroPx = Math.min(livre * 1.02, w * 0.84);
    const pxPorUnidade = diametroPx / 2.2;              // 2.3 = globo + brilho da atmosfera
    const alturaVisivel = h / pxPorUnidade;
    camera.position.z = alturaVisivel / (2 * Math.tan(THREE.MathUtils.degToRad(camera.fov / 2)));
    inclinacao.position.y = (h / 2 - livre / 2 - 6) / pxPorUnidade;
  });

  let tempo = 0;
  const quadro = (dt) => {
    tempo += dt;
    if (!reduzir) globo.rotation.y += dt * 0.09;
    inclinacao.rotation.x += ((alvo.y * 0.22) - inclinacao.rotation.x) * 0.05;
    inclinacao.rotation.y += ((alvo.x * 0.35) - inclinacao.rotation.y) * 0.05;

    // rotas "desenhando" em sequência
    for (const r of rotas) {
      const p = reduzir ? 1 : THREE.MathUtils.clamp((tempo - r.atraso) / 1.6, 0, 1);
      r.geo.setDrawRange(0, Math.max(2, Math.floor(r.total * p)));
    }
    // pulso dos pinos
    for (const p of pinos) {
      const s = 1 + 0.6 * (0.5 + 0.5 * Math.sin(tempo * 2.4 + p.fase));
      p.anel.scale.setScalar(reduzir ? 1.2 : s);
      p.anel.material.opacity = reduzir ? 0.6 : 0.9 - (s - 1) * 1.1;
    }
    // avião percorrendo as rotas, uma de cada vez
    if (!reduzir) {
      progressoAviao += dt / 4.5;
      if (progressoAviao >= 1) { progressoAviao = 0; rotaAviao = (rotaAviao + 1) % rotas.length; }
    } else progressoAviao = 0.55;
    const curva = rotas[rotaAviao].curva;
    aviao.position.copy(curva.getPointAt(progressoAviao));
    aviao.up.copy(aviao.position).normalize();
    aviao.lookAt(aviao.position.clone().add(curva.getTangentAt(progressoAviao)));
    aviao.visible = tempo > rotas[rotas.length - 1].atraso || reduzir;

    // etiquetas só aparecem quando o pino está virado para a câmera
    const v = new THREE.Vector3(), paraCamera = new THREE.Vector3();
    for (const e of etiquetas) {
      e.getWorldPosition(v); paraCamera.copy(camera.position).sub(v).normalize();
      const frente = v.sub(inclinacao.getWorldPosition(new THREE.Vector3())).normalize().dot(paraCamera);
      e.material.opacity = THREE.MathUtils.clamp((frente - 0.08) * 5, 0, 1);
    }
    renderer.render(cena, camera);
  };
  const laco = criarLaco(card, quadro);
  laco.renderizarUmaVez();
  card.classList.add('globo-pronto');
}
