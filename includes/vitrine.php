<?php
// MODO VITRINE (link do portfólio: /?vitrine)
// Quem abre com ?vitrine entra direto como "Visitante" (conta de exemplo, sem senha) e pode ver tudo:
// destinos, globo, reserva, pagamento, minhas reservas, cartão de embarque e comparação.
// Reservar, pagar, cancelar, favoritar, avaliar, usar cupom, entrar/criar conta e sair ficam travados com um aviso.
// Carregado pelo includes/sessao_banco.php (Vercel). Sem ?vitrine (e sem o cookie da vitrine) nada muda.
// Sair da vitrine neste navegador: /?sair_vitrine

if (defined('TRIPORA_VITRINE_OK') || PHP_SAPI === 'cli') return;
define('TRIPORA_VITRINE_OK', true);

const VITRINE_EMAIL = 'visitante@vitrine.tripora';
const VITRINE_FAIXA = 'MODO VITRINE · SÓ PARA VER';
const VITRINE_AVISO = 'Modo vitrine: aqui é só para ver. Reservas, pagamentos e alterações estão desativados.';

(function () {
    $cookie = ['expires' => 0, 'path' => '/', 'secure' => true, 'httponly' => true, 'samesite' => 'Lax'];
    $caminho = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $arquivo = basename($caminho) ?: 'index.php';

    if (isset($_GET['sair_vitrine'])) {
        setcookie('tripora_vitrine', '', ['expires' => time() - 3600] + $cookie);
        if (isset($_COOKIE[session_name()])) {
            session_start();
            if (!empty($_SESSION['vitrine'])) { $_SESSION = []; session_destroy(); }
        }
        header('Location: /index.php');
        exit();
    }

    // só páginas (arquivos de css, js e imagens passam direto)
    if ($caminho !== '/' && !preg_match('/\.(php|html)$/i', $arquivo)) return;

    $pediu = isset($_GET['vitrine']);
    if (!$pediu && ($_COOKIE['tripora_vitrine'] ?? '') !== '1') return;

    // As páginas chamam session_start() de novo; com a sessão já aberta isso só gera um aviso, que escondemos.
    error_reporting(error_reporting() & ~E_NOTICE);
    if ($pediu) setcookie('tripora_vitrine', '1', $cookie);
    if (session_status() === PHP_SESSION_NONE) session_start();

    if (empty($_SESSION['vitrine'])) {
        try {
            $st = Database::conexao()->prepare('SELECT id, nome FROM clientes WHERE email = :e LIMIT 1');
            $st->execute([':e' => VITRINE_EMAIL]);
            $u = $st->fetch();
        } catch (Throwable $e) { $u = null; }
        if (!$u) return; // sem a conta de exemplo, o site segue normal
        session_regenerate_id(true);
        $_SESSION = ['user_id' => (int)$u['id'], 'user_name' => $u['nome'], 'user_role' => 'user', 'vitrine' => 1];
    }

    // Travas: qualquer envio de formulário e as páginas que mudam dados
    $travadas = ['login.php', 'register.php', 'logout.php', 'login.html', 'registrar.html', 'cancelar_reserva.php', 'favoritar.php',
        'avaliar.php', 'aplicar_cupom.php', 'processar_pagamento.php', 'admin_actions.php', 'admin_dashboard.php'];
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET' || in_array($arquivo, $travadas, true)) {
        if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== '') {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['erro' => 'vitrine', 'mensagem' => VITRINE_AVISO], JSON_UNESCAPED_UNICODE);
            exit();
        }
        $_SESSION['aviso_vitrine'] = 1;
        // volta para a página de onde veio (só caminhos deste site)
        $volta = '/index.php';
        $ref = parse_url($_SERVER['HTTP_REFERER'] ?? '');
        if (!empty($ref['path']) && ($ref['host'] ?? '') === ($_SERVER['HTTP_HOST'] ?? '') && !in_array(basename($ref['path']), $travadas, true)) $volta = $ref['path'] . (isset($ref['query']) ? '?' . $ref['query'] : '');
        header('Location: ' . $volta, true, 303);
        exit();
    }

    $GLOBALS['tripora_aviso_vitrine'] = !empty($_SESSION['aviso_vitrine']);
    unset($_SESSION['aviso_vitrine']);

    // Faixa "modo vitrine" no fim de cada página HTML
    ob_start(function (string $html): string {
        if (stripos($html, '</body>') === false) return $html;
        $faixa = '<style>
.vitrine-faixa{position:fixed;z-index:999;left:50%;bottom:calc(14px + env(safe-area-inset-bottom));transform:translateX(-50%);display:flex;align-items:center;gap:10px;width:max-content;max-width:calc(100vw - 32px);
  padding:9px 16px;border-radius:999px;background:rgba(7,17,31,.94);border:1px solid rgba(57,208,255,.55);color:#39d0ff;font:800 12px Inter,system-ui,sans-serif;letter-spacing:.2em;box-shadow:0 8px 30px rgba(0,0,0,.45);pointer-events:none}
.vitrine-faixa i{width:8px;height:8px;border-radius:50%;background:#39d0ff;box-shadow:0 0 10px #39d0ff;animation:vitPisca 1.6s ease-in-out infinite}
.vitrine-faixa.pulso{animation:vitPulso .6s ease 2}
.vitrine-aviso{position:fixed;z-index:999;left:50%;bottom:calc(66px + env(safe-area-inset-bottom));transform:translate(-50%,8px);opacity:0;transition:opacity .2s,transform .2s;width:max-content;max-width:calc(100vw - 32px);
  padding:12px 16px;border-radius:14px;background:#0f1d2e;color:#f5f7fb;border:1px solid rgba(255,255,255,.12);border-left:4px solid #39d0ff;font:600 14px Inter,system-ui,sans-serif;box-shadow:0 12px 34px rgba(0,0,0,.5);pointer-events:none}
.vitrine-aviso.ver{opacity:1;transform:translate(-50%,0)}
body{padding-bottom:64px}
@keyframes vitPisca{50%{opacity:.3}}@keyframes vitPulso{50%{box-shadow:0 0 0 6px rgba(57,208,255,.3)}}
@media (max-width:420px){.vitrine-faixa{font-size:10px;letter-spacing:.14em}}
@media (prefers-reduced-motion:reduce){.vitrine-faixa i,.vitrine-faixa.pulso{animation:none}.vitrine-aviso{transition:none}}
</style>
<div class="vitrine-faixa" role="note"><i></i><span>' . VITRINE_FAIXA . '</span></div><div class="vitrine-aviso" role="status" aria-live="polite"></div>
<script>(function(){var f=document.querySelector(".vitrine-faixa"),a=document.querySelector(".vitrine-aviso"),t;
function avisar(){a.textContent=' . json_encode(VITRINE_AVISO, JSON_UNESCAPED_UNICODE) . ';a.classList.add("ver");f.classList.remove("pulso");void f.offsetWidth;f.classList.add("pulso");clearTimeout(t);t=setTimeout(function(){a.classList.remove("ver")},3400)}
document.addEventListener("submit",function(e){e.preventDefault();e.stopImmediatePropagation();avisar()},true);
document.addEventListener("click",function(e){var b=e.target.closest(".fav-btn,a[href$=\"logout.php\"],a[href*=\"login.html\"],a[href*=\"registrar.html\"],a[href*=\"cancelar_reserva.php\"]");if(!b)return;e.preventDefault();e.stopImmediatePropagation();avisar()},true);
' . (!empty($GLOBALS['tripora_aviso_vitrine']) ? 'avisar();' : '') . '})();</script>';
        return substr_replace($html, $faixa, strripos($html, '</body>'), 0);
    });
})();
