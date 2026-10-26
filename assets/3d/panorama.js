// Vista panorâmica: a foto do destino vira uma parede curva ao redor da câmera.
// Arraste (ou mova o celular) para olhar em volta. Sem WebGL, fica só a foto normal.
import * as THREE from 'three';
import { temWebGL, reduzMovimento, criarLaco, acompanharTamanho } from './util3d.js';

document.querySelectorAll('.panorama[data-img]').forEach(el => { if (temWebGL()) iniciar(el); });

function iniciar(el) {
  const reduzir = reduzMovimento();
  const ARCO = THREE.MathUtils.degToRad(150);   // quanto da "volta" a foto ocupa
  const RAIO = 10;

  new THREE.TextureLoader().setCrossOrigin('anonymous').load(el.dataset.img, textura => {
    textura.colorSpace = THREE.SRGBColorSpace;
    textura.anisotropy = 4;
    const img = textura.image, proporcao = img.width / img.height;
    const altura = (RAIO * ARCO) / proporcao;

    const canvas = document.createElement('canvas');
    canvas.className = 'panorama-canvas';
    el.append(canvas);
    const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, powerPreference: 'low-power' });
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    const cena = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(58, 1, 0.1, 50);
    // a textura é espelhada para ser vista por dentro do cilindro
    textura.wrapS = THREE.RepeatWrapping; textura.repeat.x = -1;
    const geo = new THREE.CylinderGeometry(RAIO, RAIO, altura, 96, 1, true, Math.PI - ARCO / 2, ARCO);
    cena.add(new THREE.Mesh(geo, new THREE.MeshBasicMaterial({ map: textura, side: THREE.BackSide })));

    let yaw = 0, pitch = 0, alvoYaw = 0, alvoPitch = 0, arrastando = false, ultimoToque = 0, giroDispositivo = null;
    let limiteYaw = 0.5, limitePitch = 0.1;
    acompanharTamanho(el, renderer, camera, (w, h) => {
      const fovV = THREE.MathUtils.degToRad(camera.fov);
      const fovH = 2 * Math.atan(Math.tan(fovV / 2) * camera.aspect);
      limiteYaw = Math.max(0, ARCO / 2 - fovH / 2 - 0.02);
      limitePitch = Math.max(0, Math.atan((altura / 2) / RAIO) - fovV / 2 - 0.01);
      // se a foto não cobre a altura da tela, aproxima (diminui o campo de visão)
      const fovMax = 2 * Math.atan((altura / 2) / RAIO) * 0.98;
      if (fovV > fovMax) { camera.fov = THREE.MathUtils.radToDeg(fovMax); camera.updateProjectionMatrix(); }
    });

    let x0 = 0, y0 = 0;
    canvas.addEventListener('pointerdown', e => { arrastando = true; x0 = e.clientX; y0 = e.clientY; canvas.setPointerCapture(e.pointerId); el.classList.add('usado'); });
    canvas.addEventListener('pointermove', e => {
      if (!arrastando) return;
      const k = THREE.MathUtils.degToRad(camera.fov) / el.clientHeight;
      alvoYaw += (e.clientX - x0) * k; alvoPitch += (e.clientY - y0) * k;
      x0 = e.clientX; y0 = e.clientY; ultimoToque = performance.now();
    });
    const soltar = () => { arrastando = false; ultimoToque = performance.now(); };
    canvas.addEventListener('pointerup', soltar);
    canvas.addEventListener('pointercancel', soltar);

    // botão para usar o movimento do celular (no iPhone precisa de permissão)
    if ('DeviceOrientationEvent' in window && window.matchMedia('(pointer: coarse)').matches) {
      const b = document.createElement('button');
      b.type = 'button'; b.className = 'panorama-giro'; b.textContent = '📱 Mover com o celular';
      b.onclick = async () => {
        try { if (typeof DeviceOrientationEvent.requestPermission === 'function' && (await DeviceOrientationEvent.requestPermission()) !== 'granted') return; } catch { return; }
        let base = null;
        window.addEventListener('deviceorientation', ev => {
          if (ev.alpha == null) return;
          if (!base) base = { a: ev.alpha, b: ev.beta };
          giroDispositivo = { yaw: THREE.MathUtils.degToRad(ev.alpha - base.a), pitch: THREE.MathUtils.degToRad(ev.beta - base.b) * 0.6 };
        });
        b.remove(); el.classList.add('usado');
      };
      el.append(b);
    }

    criarLaco(el, (dt, t) => {
      if (giroDispositivo) { alvoYaw = giroDispositivo.yaw; alvoPitch = giroDispositivo.pitch; }
      else if (!arrastando && !reduzir && performance.now() - ultimoToque > 2500) alvoYaw = Math.sin(t * 0.18) * limiteYaw * 0.85; // passeio automático
      alvoYaw = THREE.MathUtils.clamp(alvoYaw, -limiteYaw, limiteYaw);
      alvoPitch = THREE.MathUtils.clamp(alvoPitch, -limitePitch, limitePitch);
      yaw += (alvoYaw - yaw) * 0.08; pitch += (alvoPitch - pitch) * 0.08;
      camera.rotation.set(pitch, yaw, 0, 'YXZ');
      renderer.render(cena, camera);
    });
    el.classList.add('panorama-pronta');
  }, undefined, () => { el.classList.add('panorama-sem-3d'); });
}
