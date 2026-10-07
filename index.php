<?php
session_start();
require_once 'includes/Database.php';
require_once 'includes/Destino.php';

$db=(new Database())->getConnection();

$destinosPadrao = [
    ['Tóquio','Japão','Tecnologia, tradição, gastronomia e bairros vibrantes em uma das cidades mais fascinantes do mundo.',8200.00,'https://images.unsplash.com/photo-1540959733332-eab4deabeeaf?auto=format&fit=crop&w=900&q=80'],
    ['Nova York','Estados Unidos','Arranha-céus, Broadway, Central Park e a energia única da cidade que nunca dorme.',7600.00,'https://images.unsplash.com/photo-1485871981521-5b1fd3805eee?auto=format&fit=crop&w=900&q=80'],
    ['Roma','Itália','História, arquitetura, gastronomia italiana e alguns dos monumentos mais famosos do mundo.',6400.00,'https://images.unsplash.com/photo-1552832230-c0197dd311b5?auto=format&fit=crop&w=900&q=80'],
    ['Lisboa','Portugal','Mirantes, ruas históricas, gastronomia e o charme de uma das capitais mais acolhedoras da Europa.',5200.00,'https://images.unsplash.com/photo-1525207934214-58e69a8f8a93?auto=format&fit=crop&w=900&q=80'],
    ['Buenos Aires','Argentina','Cultura, tango, gastronomia e arquitetura clássica em uma viagem cheia de personalidade.',2900.00,'https://images.unsplash.com/photo-1589909202802-8f4aadce1849?auto=format&fit=crop&w=900&q=80'],
    ['Cancún','México','Mar azul-turquesa, resorts, praias paradisíacas e experiências inesquecíveis no Caribe.',6100.00,'https://images.unsplash.com/photo-1552074284-5e88ef1aef18?auto=format&fit=crop&w=900&q=80']
];

$checkDestino = $db->prepare('SELECT id FROM destinos WHERE nome = :nome LIMIT 1');
$insertDestino = $db->prepare('INSERT INTO destinos (nome,pais,descricao,preco,imagem) VALUES (:nome,:pais,:descricao,:preco,:imagem)');
foreach ($destinosPadrao as $d) {
    $checkDestino->execute([':nome'=>$d[0]]);
    if (!$checkDestino->fetch()) {
        $insertDestino->execute([':nome'=>$d[0],':pais'=>$d[1],':descricao'=>$d[2],':preco'=>$d[3],':imagem'=>$d[4]]);
    }
}

$rows=$db->query('SELECT * FROM destinos ORDER BY id ASC')->fetchAll();
$destinos=array_map(fn($d)=>new Destino((int)$d['id'],$d['nome'],$d['pais'],$d['descricao'],(float)$d['preco'],$d['imagem']),$rows);
?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="description" content="Tripora.com - reserve, acompanhe e gerencie suas viagens em um só lugar"><meta name="theme-color" content="#07111f"><title>Tripora.com</title><link rel="stylesheet" href="css/main.css"></head>
<body>
<header class="site-header"><div class="container nav"><a class="brand" href="index.php">Tripora<span>.com</span></a><button class="menu-toggle" type="button" aria-label="Abrir menu" aria-expanded="false"><span></span><span></span><span></span></button><nav class="nav-links"><a href="#destinos">Destinos</a><?php if(isset($_SESSION['user_id'])): ?><a href="minhas_reservas.php">Minhas viagens</a><?php endif; ?><?php if(($_SESSION['user_role']??'')==='admin'): ?><a href="admin_dashboard.php">Admin</a><?php endif; ?><?php if(isset($_SESSION['user_id'])): ?><span class="muted">Olá, <?= htmlspecialchars($_SESSION['user_name']) ?></span><a href="logout.php">Sair</a><?php else: ?><a href="login.html">Entrar</a><a class="btn small" href="registrar.html">Criar conta</a><?php endif; ?></nav></div></header>
<main>
<section class="container hero"><div><p class="eyebrow">Sua próxima história começa aqui</p><h1>Viaje mais.<br>Viva melhor.</h1><p>Escolha seu destino, faça sua reserva, acompanhe tudo em um painel e finalize o pagamento em um fluxo completo.</p><div class="hero-actions"><a class="btn" href="#destinos">Explorar destinos</a><?php if(isset($_SESSION['user_id'])): ?><a class="btn secondary" href="minhas_reservas.php">Gerenciar viagens</a><?php else: ?><a class="btn secondary" href="registrar.html">Começar agora</a><?php endif; ?></div></div><div class="hero-card"><div><p class="eyebrow">Experiências selecionadas</p><h2>Do Brasil para o mundo.</h2><p>Reservas simples, rápidas e organizadas.</p></div></div></section>
<section class="container section" id="destinos">
<div class="section-head"><div><p class="eyebrow">Destinos</p><h2>Escolha sua próxima viagem</h2></div><p class="muted">Encontre seu destino e reserve em poucos passos.</p></div>
<div class="search-box"><span class="search-icon">⌕</span><input id="destinationSearch" type="search" placeholder="Buscar por destino ou país..." aria-label="Buscar destinos"><span id="resultCount" class="search-count"><?= count($destinos) ?> opções</span></div>
<div class="carousel-shell">
  <button class="carousel-btn prev" type="button" aria-label="Destino anterior">‹</button>
  <div class="destinations-carousel" id="destinationGrid"><?php foreach($destinos as $destino){ echo $destino->mostrarCard(); } ?></div>
  <button class="carousel-btn next" type="button" aria-label="Próximo destino">›</button>
</div>
<p id="emptySearch" class="empty-search" hidden>Nenhum destino encontrado. Tente outro nome ou país.</p>
</section>
<section class="container section benefits-section"><div class="section-head"><div><p class="eyebrow">Por que Tripora?</p><h2>Uma experiência simples do início ao fim</h2></div></div><div class="benefits-grid">
<article class="benefit"><span class="benefit-icon">01</span><h3>Escolha</h3><p class="muted">Compare destinos em uma interface clara e responsiva.</p></article>
<article class="benefit"><span class="benefit-icon">02</span><h3>Reserve</h3><p class="muted">Crie sua reserva em poucos cliques e acompanhe o status.</p></article>
<article class="benefit"><span class="benefit-icon">03</span><h3>Gerencie</h3><p class="muted">Pagamento simulado, cancelamento e histórico em um só lugar.</p></article>
</div></section>
<section class="container section"><div class="panel"><div class="kpis"><div class="kpi"><strong><?= count($destinos) ?></strong><span class="muted">destinos disponíveis</span></div><div class="kpi"><strong>3 etapas</strong><span class="muted">reservar, pagar e acompanhar</span></div><div class="kpi"><strong>100%</strong><span class="muted">fluxo demonstrável</span></div></div></div></section>
</main>
<footer class="footer"><div class="container"><strong>Tripora.com</strong><p>Projeto acadêmico desenvolvido por Guilherme Lima Zamberse da Silva, Breno Martinho Denis, Pedro Lucas Possidonio dos Santos e Pedro dos Reis Escudeiro.</p></div></footer>
<script src="js/main.js"></script></body></html>