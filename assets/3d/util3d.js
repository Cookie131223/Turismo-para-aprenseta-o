// Funções compartilhadas pelos efeitos 3D (WebGL, movimento reduzido, pausa fora da tela, redimensionamento).

export function temWebGL() {
  try {
    const c = document.createElement('canvas');
    return !!(window.WebGLRenderingContext && (c.getContext('webgl2') || c.getContext('webgl')));
  } catch {
    return false;
  }
}

const mqReduzir = window.matchMedia('(prefers-reduced-motion: reduce)');
export const reduzMovimento = () => mqReduzir.matches;
export const telaTouch = () => window.matchMedia('(hover: none), (pointer: coarse)').matches;

// Laço de animação que só roda quando o elemento está visível na tela e a aba está ativa.
export function criarLaco(elemento, quadro) {
  let visivel = true, rodando = false, id = 0, ultimo = performance.now();
  const passo = agora => {
    if (!rodando) return;
    const dt = Math.min((agora - ultimo) / 1000, 0.05);
    ultimo = agora;
    quadro(dt, agora / 1000);
    id = requestAnimationFrame(passo);
  };
  const atualizar = () => {
    const deveRodar = visivel && document.visibilityState === 'visible';
    if (deveRodar && !rodando) { rodando = true; ultimo = performance.now(); id = requestAnimationFrame(passo); }
    else if (!deveRodar && rodando) { rodando = false; cancelAnimationFrame(id); }
  };
  if ('IntersectionObserver' in window) {
    new IntersectionObserver(([e]) => { visivel = e.isIntersecting; atualizar(); }, { rootMargin: '80px' }).observe(elemento);
  }
  document.addEventListener('visibilitychange', atualizar);
  atualizar();
  return { renderizarUmaVez: () => quadro(0, performance.now() / 1000) };
}

// Mantém renderer e câmera do tamanho do container.
export function acompanharTamanho(container, renderer, camera, aoMudar) {
  const ajustar = () => {
    const w = Math.max(1, container.clientWidth), h = Math.max(1, container.clientHeight);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
    renderer.setSize(w, h, false);
    camera.aspect = w / h;
    camera.updateProjectionMatrix();
    if (aoMudar) aoMudar(w, h);
  };
  ajustar();
  if ('ResizeObserver' in window) new ResizeObserver(ajustar).observe(container);
  else window.addEventListener('resize', ajustar);
  return ajustar;
}

// Textura redonda e suave para pontos (evita os quadradinhos padrão).
export function texturaPonto(THREE) {
  const c = document.createElement('canvas'); c.width = c.height = 64;
  const g = c.getContext('2d');
  const grad = g.createRadialGradient(32, 32, 0, 32, 32, 32);
  grad.addColorStop(0, 'rgba(255,255,255,1)');
  grad.addColorStop(0.45, 'rgba(255,255,255,.85)');
  grad.addColorStop(1, 'rgba(255,255,255,0)');
  g.fillStyle = grad; g.fillRect(0, 0, 64, 64);
  const t = new THREE.CanvasTexture(c);
  t.colorSpace = THREE.SRGBColorSpace;
  return t;
}
