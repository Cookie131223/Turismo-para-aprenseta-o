// Inclinação 3D dos cards com o mouse (CSS transform + perspective, sem three.js).
// Vale para todo elemento com a classe .card-tilt. Desligado em telas touch e com movimento reduzido.
const MAX_GRAUS = 8;

const semHover = window.matchMedia('(hover: none), (pointer: coarse)').matches;
const reduzir = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

if (!semHover && !reduzir) {
  document.querySelectorAll('.card-tilt').forEach(card => {
    const brilho = document.createElement('span');
    brilho.className = 'tilt-brilho';
    brilho.setAttribute('aria-hidden', 'true');
    card.append(brilho);

    let quadro = 0;
    card.addEventListener('pointerenter', () => { card.classList.add('tilt-ativo'); });
    card.addEventListener('pointermove', e => {
      cancelAnimationFrame(quadro);
      quadro = requestAnimationFrame(() => {
        const r = card.getBoundingClientRect();
        const px = (e.clientX - r.left) / r.width, py = (e.clientY - r.top) / r.height;
        const rotY = (px - 0.5) * 2 * MAX_GRAUS, rotX = (0.5 - py) * 2 * MAX_GRAUS;
        card.style.transform = `perspective(900px) rotateX(${rotX.toFixed(2)}deg) rotateY(${rotY.toFixed(2)}deg) translateY(-6px)`;
        card.style.setProperty('--brilho-x', (px * 100).toFixed(1) + '%');
        card.style.setProperty('--brilho-y', (py * 100).toFixed(1) + '%');
      });
    });
    card.addEventListener('pointerleave', () => {
      cancelAnimationFrame(quadro);
      card.classList.remove('tilt-ativo');
      card.style.transform = '';
    });
  });
}
