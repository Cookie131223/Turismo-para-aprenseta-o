// Mostra um modelo 3D (.glb) girando devagar, que o visitante pode girar com o mouse/dedo.
// Uso no HTML: <div class="modelo-3d" data-modelo-3d="/assets/3d/modelos/modelo.glb"></div>
import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { temWebGL, reduzMovimento, criarLaco, acompanharTamanho } from './util3d.js';

const MODELO_PADRAO = '/assets/3d/modelos/modelo.glb';

document.querySelectorAll('[data-modelo-3d]').forEach(el => {
  if (!temWebGL()) { el.classList.add('modelo-sem-3d'); return; }
  iniciar(el, el.dataset.modelo3d || MODELO_PADRAO);
});

function iniciar(el, arquivo) {
  const reduzir = reduzMovimento();
  const canvas = document.createElement('canvas');
  const aviso = document.createElement('span');
  aviso.className = 'modelo-carregando'; aviso.textContent = 'Carregando 3D…';
  el.append(canvas, aviso);

  const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true, powerPreference: 'low-power' });
  renderer.setClearColor(0x000000, 0);
  renderer.outputColorSpace = THREE.SRGBColorSpace;
  const cena = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(35, 1, 0.01, 100);

  cena.add(new THREE.AmbientLight('#ffffff', 1.1));
  const luz = new THREE.DirectionalLight('#ffffff', 2.4); luz.position.set(2, 3, 2.5); cena.add(luz);
  const luz2 = new THREE.DirectionalLight('#39d0ff', 1.2); luz2.position.set(-3, -1, -2); cena.add(luz2);

  const controles = new OrbitControls(camera, canvas);
  controles.enablePan = false;
  controles.enableDamping = true;
  controles.autoRotate = !reduzir;
  controles.autoRotateSpeed = 1.6;

  const suporte = new THREE.Group(); cena.add(suporte);
  acompanharTamanho(el, renderer, camera);

  new GLTFLoader().load(arquivo, gltf => {
    const modelo = gltf.scene;
    // centraliza e ajusta a escala pela caixa que envolve o modelo
    const caixa = new THREE.Box3().setFromObject(modelo);
    const tamanho = caixa.getSize(new THREE.Vector3()), centro = caixa.getCenter(new THREE.Vector3());
    modelo.position.sub(centro);
    const escala = 1.6 / Math.max(tamanho.x, tamanho.y, tamanho.z || 1);
    suporte.scale.setScalar(escala);
    suporte.add(modelo);
    const dist = 0.66 / Math.sin(THREE.MathUtils.degToRad(camera.fov / 2));
    camera.position.set(0.5, 0.32, 0.8).normalize().multiplyScalar(dist);
    controles.minDistance = dist * 0.6;
    controles.maxDistance = dist * 1.4;
    controles.target.set(0, 0, 0);
    controles.update();
    aviso.remove();
    el.classList.add('modelo-pronto');
  }, p => {
    if (p.total) aviso.textContent = 'Carregando 3D… ' + Math.round(p.loaded / p.total * 100) + '%';
  }, () => {
    aviso.textContent = '';
    el.classList.add('modelo-sem-3d');
    canvas.remove();
  });

  criarLaco(el, () => { controles.update(); renderer.render(cena, camera); });
}
