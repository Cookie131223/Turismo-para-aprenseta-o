<?php
session_start();
require_once 'includes/check_admin.php';
require_once 'includes/Database.php';
require_once 'includes/layout.php';

$db=(new Database())->getConnection();
$destinos=$db->query('SELECT * FROM destinos ORDER BY nome')->fetchAll();
$reservas=$db->query('SELECT r.*, c.nome AS cliente_nome FROM reservas r JOIN clientes c ON c.id=r.cliente_id ORDER BY r.data_reserva DESC')->fetchAll();
$totalReservas=count($reservas);
$totalConfirmadas=count(array_filter($reservas,fn($r)=>$r['status']==='confirmada'));
$pagas=fn($r)=>in_array($r['status'],['confirmada','concluida'],true);
$faturamento=array_sum(array_map(fn($r)=>$pagas($r)?(float)$r['valor']-(float)($r['desconto']??0):0,$reservas));

// dados dos gráficos
$porDestino=[];
foreach($reservas as $r){ if($r['status']!=='cancelada') $porDestino[$r['destino_nome']]=($porDestino[$r['destino_nome']]??0)+1; }
arsort($porDestino); $porDestino=array_slice($porDestino,0,8,true);
$meses=[]; $nomesMes=['jan','fev','mar','abr','mai','jun','jul','ago','set','out','nov','dez'];
for($i=5;$i>=0;$i--){ $t=strtotime(date('Y-m-01')." -$i months"); $meses[date('Y-m',$t)]=['rotulo'=>$nomesMes[(int)date('n',$t)-1].'/'.date('y',$t),'valor'=>0]; }
foreach($reservas as $r){ if(!$pagas($r)) continue; $k=date('Y-m',strtotime($r['data_pagamento']?:$r['data_reserva'])); if(isset($meses[$k])) $meses[$k]['valor']+=(float)$r['valor']-(float)($r['desconto']??0); }
$porStatus=['aguardando_pagamento'=>0,'confirmada'=>0,'concluida'=>0,'cancelada'=>0];
foreach($reservas as $r){ if(isset($porStatus[$r['status']])) $porStatus[$r['status']]++; }
$gReservas=['titulo'=>'Reservas por destino','itens'=>array_map(fn($n,$q)=>['rotulo'=>$n,'valor'=>$q,'texto'=>(string)$q],array_keys($porDestino),array_values($porDestino))];
$gReceita=['titulo'=>'Receita dos últimos 6 meses','itens'=>array_map(fn($m)=>['rotulo'=>$m['rotulo'],'valor'=>round($m['valor'],2),'texto'=>'R$ '.number_format($m['valor'],0,',','.')],array_values($meses))];
$nomesStatus=['aguardando_pagamento'=>'Aguardando','confirmada'=>'Confirmada','concluida'=>'Concluída','cancelada'=>'Cancelada'];
$coresStatus=['aguardando_pagamento'=>'#ffc24b','confirmada'=>'#38d996','concluida'=>'#39d0ff','cancelada'=>'#ff5f73'];
$gStatus=['titulo'=>'Reservas por status','itens'=>array_map(fn($k)=>['rotulo'=>$nomesStatus[$k],'valor'=>$porStatus[$k],'texto'=>(string)$porStatus[$k],'cor'=>$coresStatus[$k]],array_keys($porStatus))];
?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Painel administrativo | Tripora.com</title><link rel="stylesheet" href="css/main.css"><link rel="stylesheet" href="css/recursos.css"></head><body>
<header class="site-header"><div class="container nav"><a class="brand" href="index.php">Tripora<span>.com</span></a><button class="menu-toggle" type="button" aria-label="Abrir menu" aria-expanded="false"><span></span><span></span><span></span></button><nav class="nav-links"><a href="index.php">Ver site</a><a href="logout.php">Sair</a></nav></div></header>
<main class="container section"><p class="eyebrow">Gestão</p><h1>Painel administrativo</h1><?php if(($_GET['erro']??'')==='destino_com_reservas'): ?><div class="notice erro">Esse destino tem reservas e não pode ser excluído.</div><?php endif; ?><div class="kpis"><div class="kpi"><strong><?= count($destinos) ?></strong><span class="muted">destinos</span></div><div class="kpi"><strong><?= $totalReservas ?></strong><span class="muted">reservas</span></div><div class="kpi"><strong>R$ <?= number_format($faturamento,2,',','.') ?></strong><span class="muted">receita confirmada (com descontos)</span></div></div>
<section class="graficos-admin"><div class="panel graf-largo"><div class="barras3d" data-modo="2d" data-barras="<?= jsonAttr($gReceita) ?>"></div></div><div class="panel"><div class="barras3d" data-modo="2d" data-barras="<?= jsonAttr($gReservas) ?>"></div></div><div class="panel"><div class="barras3d" data-modo="2d" data-barras="<?= jsonAttr($gStatus) ?>"></div></div></section>
<section class="panel"><h2>Novo destino</h2><form class="form-grid" action="includes/admin_actions.php" method="post"><input type="hidden" name="action" value="add_destino"><div class="form-group"><label>Nome</label><input name="nome" required></div><div class="form-group"><label>País</label><input name="pais" required></div><div class="form-group full"><label>Descrição</label><textarea name="descricao" required></textarea></div><div class="form-group"><label>Preço</label><input type="number" min="0" step="0.01" name="preco" required></div><div class="form-group"><label>URL da imagem</label><input type="url" name="imagem" required></div><div class="form-group"><label>Latitude (opcional, para o globo)</label><input type="number" step="0.0001" min="-90" max="90" name="lat" placeholder="-22.9068"></div><div class="form-group"><label>Longitude (opcional)</label><input type="number" step="0.0001" min="-180" max="180" name="lon" placeholder="-43.1729"></div><div class="form-group full"><button class="btn" type="submit">Adicionar destino</button></div></form></section>
<section class="panel"><h2>Destinos cadastrados</h2><div class="table-wrap"><table><thead><tr><th>Destino</th><th>País</th><th>Preço</th><th>Ação</th></tr></thead><tbody><?php foreach($destinos as $d): ?><tr><td><?= htmlspecialchars($d['nome']) ?></td><td><?= htmlspecialchars($d['pais']) ?></td><td>R$ <?= number_format($d['preco'],2,',','.') ?></td><td><a class="btn small danger" href="includes/admin_actions.php?action=delete_destino&id=<?= $d['id'] ?>" onclick="return confirm('Excluir este destino?')">Excluir</a></td></tr><?php endforeach; ?></tbody></table></div></section>
<section class="panel"><h2>Gerenciar reservas</h2><div class="table-wrap"><table><thead><tr><th>Cliente</th><th>Destino</th><th>Valor</th><th>Status</th><th>Pagamento</th><th>Atualizar</th></tr></thead><tbody><?php foreach($reservas as $r): ?><tr><td><?= htmlspecialchars($r['cliente_nome']) ?></td><td><?= htmlspecialchars($r['destino_nome']) ?></td><td>R$ <?= number_format($r['valor'],2,',','.') ?></td><td><span class="badge <?= htmlspecialchars($r['status']) ?>"><?= htmlspecialchars(str_replace('_',' ',ucfirst($r['status']))) ?></span></td><td><?= htmlspecialchars($r['forma_pagamento']?:'—') ?></td><td><form action="includes/admin_actions.php" method="post" class="card-actions"><input type="hidden" name="action" value="update_status"><input type="hidden" name="reserva_id" value="<?= $r['id'] ?>"><select name="status"><option value="aguardando_pagamento">Aguardando pagamento</option><option value="confirmada">Confirmada</option><option value="concluida">Concluída</option><option value="cancelada">Cancelada</option></select><button class="btn small" type="submit">Salvar</button></form></td></tr><?php endforeach; ?></tbody></table></div></section>
</main><script src="js/main.js"></script><script src="js/barras3d.js"></script></body></html>