<?php
session_start();
require_once 'includes/Database.php';
require_once 'includes/Destino.php';

$db=(new Database())->getConnection();
$rows=$db->query('SELECT * FROM destinos ORDER BY id ASC')->fetchAll();
$destinos=array_map(fn($d)=>new Destino((int)$d['id'],$d['nome'],$d['pais'],$d['descricao'],(float)$d['preco'],$d['imagem']),$rows);
?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="description" content="Tripora.com - sistema acadêmico de reservas de viagens"><title>Tripora.com</title><link rel="stylesheet" href="css/main.css"></head>
<body>
<header class="site-header"><div class="container nav"><a class="brand" href="index.php">Tripora<span>.com</span></a><nav class="nav-links"><a href="#destinos">Destinos</a><?php if(isset($_SESSION['user_id'])): ?><a href="minhas_reservas.php">Minhas viagens</a><?php endif; ?><?php if(($_SESSION['user_role']??'')==='admin'): ?><a href="admin_dashboard.php">Admin</a><?php endif; ?><?php if(isset($_SESSION['user_id'])): ?><span class="muted">Olá, <?= htmlspecialchars($_SESSION['user_name']) ?></span><a href="logout.php">Sair</a><?php else: ?><a href="login.html">Entrar</a><a class="btn small" href="registrar.html">Criar conta</a><?php endif; ?></nav></div></header>
<main>
<section class="container hero"><div><p class="eyebrow">Sua próxima história começa aqui</p><h1>Viaje mais.<br>Viva melhor.</h1><p>Escolha seu destino, faça sua reserva, acompanhe tudo em um painel e finalize o pagamento em um fluxo completo.</p><div class="hero-actions"><a class="btn" href="#destinos">Explorar destinos</a><?php if(isset($_SESSION['user_id'])): ?><a class="btn secondary" href="minhas_reservas.php">Gerenciar viagens</a><?php else: ?><a class="btn secondary" href="registrar.html">Começar agora</a><?php endif; ?></div></div><div class="hero-card"><div><p class="eyebrow">Experiências selecionadas</p><h2>Do Brasil para o mundo.</h2><p>Reservas simples, rápidas e organizadas.</p></div></div></section>
<section class="container section" id="destinos"><div class="section-head"><div><p class="eyebrow">Destinos</p><h2>Escolha sua próxima viagem</h2></div><p class="muted">Pacotes demonstrativos para apresentação acadêmica.</p></div><div class="grid"><?php foreach($destinos as $destino){ echo $destino->mostrarCard(); } ?></div></section>
<section class="container section"><div class="panel"><div class="kpis"><div class="kpi"><strong><?= count($destinos) ?></strong><span class="muted">destinos disponíveis</span></div><div class="kpi"><strong>3 etapas</strong><span class="muted">reservar, pagar e acompanhar</span></div><div class="kpi"><strong>100%</strong><span class="muted">fluxo demonstrável</span></div></div></div></section>
</main>
<footer class="footer"><div class="container"><strong>Tripora.com</strong><p>Projeto acadêmico desenvolvido por Guilherme Lima Zamberse da Silva, Breno Martinho Denis e Pedro Lucas Possidonio dos Santos.</p></div></footer>
</body></html>