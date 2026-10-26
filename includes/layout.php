<?php
// Partes comuns das páginas novas (cabeçalho, rodapé e helpers).
function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function brl($v): string { return 'R$ ' . number_format((float)$v, 2, ',', '.'); }

const TRIPORA_IMPORTMAP = '<script type="importmap">{"imports":{"three":"https://cdn.jsdelivr.net/npm/three@0.170.0/build/three.module.js","three/addons/":"https://cdn.jsdelivr.net/npm/three@0.170.0/examples/jsm/"}}</script>';

function cabecalho(string $titulo, bool $com3d = true, string $extraHead = ''): void {
    $logado = isset($_SESSION['user_id']);
    $admin = ($_SESSION['user_role'] ?? '') === 'admin';
    echo '<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#07111f">';
    echo '<title>' . h($titulo) . ' | Tripora.com</title><link rel="stylesheet" href="css/main.css">';
    if ($com3d) echo '<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin><link rel="stylesheet" href="assets/3d/efeitos3d.css">' . TRIPORA_IMPORTMAP;
    echo '<link rel="stylesheet" href="css/recursos.css">' . $extraHead . '</head><body>';
    echo '<header class="site-header"><div class="container nav"><a class="brand" href="index.php">Tripora<span>.com</span></a>';
    echo '<button class="menu-toggle" type="button" aria-label="Abrir menu" aria-expanded="false"><span></span><span></span><span></span></button><nav class="nav-links">';
    echo '<a href="index.php#destinos">Destinos</a>';
    if ($logado) echo '<a href="minhas_reservas.php">Minhas viagens</a>';
    if ($admin) echo '<a href="admin_dashboard.php">Admin</a>';
    if ($logado) echo '<span class="muted">Olá, ' . h($_SESSION['user_name']) . '</span><a href="logout.php">Sair</a>';
    else echo '<a href="login.html">Entrar</a><a class="btn small" href="registrar.html">Criar conta</a>';
    echo '</nav></div></header>';
}

function rodape(array $modulos = [], array $scripts = []): void {
    echo '<footer class="footer"><div class="container"><strong>Tripora.com</strong><p>Projeto acadêmico desenvolvido por Guilherme Lima Zamberse da Silva, Breno Martinho Denis, Pedro Lucas Possidonio dos Santos e Pedro dos Reis Escudeiro.</p></div></footer>';
    echo '<script src="js/main.js"></script>';
    foreach ($scripts as $s) echo '<script src="' . h($s) . '"></script>';
    foreach ($modulos as $m) echo '<script type="module" src="' . h($m) . '"></script>';
    echo '</body></html>';
}

// Dados de um destino prontos para o JavaScript (globo, clima, moeda).
function destinoParaJs(array $d, ?array $info = null): array {
    $info = $info ?? DestinoInfo::de($d);
    return ['id' => (int)$d['id'], 'nome' => $d['nome'], 'pais' => $d['pais'], 'preco' => (float)$d['preco'], 'imagem' => $d['imagem'],
            'lat' => $info['lat'], 'lon' => $info['lon'], 'moeda' => $info['moeda'], 'fuso' => $info['fuso'], 'km' => $info['km'], 'voo' => DestinoInfo::vooTexto($info['voo_horas'])];
}

function jsonAttr($dados): string { return h(json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); }
