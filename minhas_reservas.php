<?php
session_start();
require_once 'includes/Database.php';
if(!isset($_SESSION['user_id'])){ header('Location: login.html?erro=nao_logado'); exit(); }
$db=(new Database())->getConnection();
$stmt=$db->prepare('SELECT * FROM reservas WHERE cliente_id=:id ORDER BY data_reserva DESC');
$stmt->execute([':id'=>$_SESSION['user_id']]); $reservas=$stmt->fetchAll();
?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Minhas viagens | Tripora.com</title><link rel="stylesheet" href="css/main.css"></head><body>
<header class="site-header"><div class="container nav"><a class="brand" href="index.php">Tripora<span>.com</span></a><button class="menu-toggle" type="button" aria-label="Abrir menu" aria-expanded="false"><span></span><span></span><span></span></button><nav class="nav-links"><a href="index.php#destinos">Destinos</a><a href="logout.php">Sair</a></nav></div></header>
<main class="container section"><div class="section-head"><div><p class="eyebrow">Área do viajante</p><h1>Minhas viagens</h1><p class="muted">Olá, <?= htmlspecialchars($_SESSION['user_name']) ?>. Acompanhe, pague ou cancele suas reservas.</p></div><a class="btn" href="index.php#destinos">Nova reserva</a></div>
<?php if(isset($_GET['sucesso'])): ?><div class="notice">Operação realizada com sucesso.</div><?php endif; ?>
<div class="table-wrap"><table><thead><tr><th>Destino</th><th>Valor</th><th>Reserva</th><th>Pagamento</th><th>Status</th><th>Ações</th></tr></thead><tbody>
<?php if(!$reservas): ?><tr><td colspan="6">Você ainda não possui reservas.</td></tr><?php endif; ?>
<?php foreach($reservas as $r): ?><tr><td><strong><?= htmlspecialchars($r['destino_nome']) ?></strong></td><td>R$ <?= number_format($r['valor'],2,',','.') ?></td><td><?= date('d/m/Y H:i',strtotime($r['data_reserva'])) ?></td><td><?= $r['data_pagamento'] ? date('d/m/Y H:i',strtotime($r['data_pagamento'])) : 'Pendente' ?></td><td><span class="badge <?= htmlspecialchars($r['status']) ?>"><?= htmlspecialchars(str_replace('_',' ',ucfirst($r['status']))) ?></span></td><td><div class="card-actions"><?php if($r['status']==='aguardando_pagamento'): ?><a class="btn small" href="pagamento.php?id=<?= $r['id'] ?>">Pagar</a><?php endif; ?><?php if(in_array($r['status'],['aguardando_pagamento','confirmada'],true)): ?><form method="post" action="cancelar_reserva.php" onsubmit="return confirm('Cancelar esta reserva?')"><input type="hidden" name="reserva_id" value="<?= $r['id'] ?>"><button class="btn small danger" type="submit">Cancelar</button></form><?php endif; ?></div></td></tr><?php endforeach; ?>
</tbody></table></div></main><script src="js/main.js"></script></body></html>