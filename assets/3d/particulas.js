// Fundo sutil de partículas atrás do conteúdo (THREE.Points). Fica fixo, não recebe cliques e pausa com a aba oculta.
import * as THREE from 'three';
import { temWebGL, reduzMovimento, telaTouch, criarLaco, acompanharTamanho, texturaPonto } from './util3d.js';

const QUANTIDADE = 1500;

// Céu que muda com o horário de quem está vendo: dia, pôr do sol e noite.
const hora = new Date().getHours();
const PERIODO = hora >= 6 && hora < 17 ? 'dia' : hora >= 17 && hora < 19 ? 'por-do-sol' : hora >= 5 && hora < 6 ? 'amanhecer' : 'noite';
const PALETAS = {
  dia: { cores: ['#39d0ff', '#8be3ff', '#bfe9ff'], opacidade: 0.3 },
  amanhecer: { cores: ['#ff9ec7', '#ffc24b', '#7c5cff'], opacidade: 0.32 },
  'por-do-sol': { cores: ['#ff8a5c', '#ff5f9e', '#7c5cff', '#ffc24b'], opacidade: 0.34 },
  noite: { cores: ['#ffffff', '#a7b5c7', '#7c5cff', '#39d0ff'], opacidade: 0.4 },
};
const CORES = PALETAS[PERIODO].cores;
document.documentElement.classList.add('ceu-' + PERIODO);

if (temWebGL()) iniciar();

function iniciar() {
  const reduzir = reduzMovimento();
  const canvas = document.createElement('canvas');
  canvas.className = 'particulas-fundo';
  canvas.setAttribute('aria-hidden', 'true');
  document.body.prepend(canvas);

  const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: false, powerPreference: 'low-power' });
  renderer.setClearColor(0x000000, 0);
  const cena = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(60, 1, 0.1, 100);
  camera.position.z = 12;

  const pos = new Float32Array(QUANTIDADE * 3), cor = new Float32Array(QUANTIDADE * 3), fase = new Float32Array(QUANTIDADE);
  const c = new THREE.Color();
  for (let i = 0; i < QUANTIDADE; i++) {
    pos[i * 3] = (Math.random() - 0.5) * 34;
    pos[i * 3 + 1] = (Math.random() - 0.5) * 22;
    pos[i * 3 + 2] = (Math.random() - 0.5) * 18;
    c.set(CORES[i % CORES.length]); cor.set([c.r, c.g, c.b], i * 3);
    fase[i] = Math.random() * Math.PI * 2;
  }
  const geo = new THREE.BufferGeometry();
  geo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
  geo.setAttribute('color', new THREE.BufferAttribute(cor, 3));
  geo.setAttribute('fase', new THREE.BufferAttribute(fase, 1));

  // flutuação feita na GPU (shader), para não pesar o processador
  const material = new THREE.ShaderMaterial({
    uniforms: { tempo: { value: 0 }, mapa: { value: texturaPonto(THREE) }, opacidade: { value: PALETAS[PERIODO].opacidade }, tamanho: { value: 70 * Math.min(window.devicePixelRatio || 1, 2) } },
    vertexShader: `
      attribute vec3 color; attribute float fase; uniform float tempo; uniform float tamanho; varying vec3 vCor;
      void main(){
        vec3 p = position;
        p.x += sin(tempo * 0.12 + fase) * 0.6;
        p.y += cos(tempo * 0.1 + fase * 1.3) * 0.5;
        vec4 mv = modelViewMatrix * vec4(p, 1.0);
        gl_PointSize = min(tamanho / -mv.z, 3.2 * tamanho / 70.0);
        gl_Position = projectionMatrix * mv;
        vCor = color;
      }`,
    fragmentShader: `
      uniform sampler2D mapa; uniform float opacidade; varying vec3 vCor;
      void main(){ vec4 t = texture2D(mapa, gl_PointCoord); gl_FragColor = vec4(vCor, t.a * opacidade); }`,
    transparent: true, depthWrite: false, blending: THREE.AdditiveBlending,
  });
  const pontos = new THREE.Points(geo, material);
  cena.add(pontos);

  const alvo = { x: 0, y: 0 };
  if (!reduzir && !telaTouch()) {
    window.addEventListener('pointermove', e => {
      alvo.x = (e.clientX / window.innerWidth - 0.5);
      alvo.y = (e.clientY / window.innerHeight - 0.5);
    }, { passive: true });
  }

  acompanharTamanho(canvas, renderer, camera);

  const quadro = (dt, t) => {
    if (!reduzir) {
      material.uniforms.tempo.value = t;
      pontos.rotation.y += dt * 0.012;
    }
    camera.position.x += (alvo.x * 1.6 - camera.position.x) * 0.03;
    camera.position.y += (-alvo.y * 1.0 - camera.position.y) * 0.03;
    camera.lookAt(0, 0, 0);
    renderer.render(cena, camera);
  };
  const laco = criarLaco(canvas, quadro);
  laco.renderizarUmaVez();
}
