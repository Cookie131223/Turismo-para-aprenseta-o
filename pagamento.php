<?php
session_start();
require_once 'includes/Database.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.html?erro=nao_logado'); exit(); }

$id = (int)($_GET['id'] ?? 0);
$db = (new Database())->getConnection();
$stmt = $db->prepare('SELECT * FROM reservas WHERE id=:id AND cliente_id=:cliente_id LIMIT 1');
$stmt->execute([':id'=>$id, ':cliente_id'=>$_SESSION['user_id']]);
$reserva = $stmt->fetch();
if (!$reserva) { header('Location: minhas_reservas.php'); exit(); }
?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Pagamento | Explorer</title><link rel="stylesheet" href="css/main.css"></head>
<body><header class="site-header"><div class="container nav"><a class="brand" href="index.php">Explorer<span>.</span></a><div class="nav-links"><a href="minhas_reservas.php">Minhas viagens</a></div></div></header>
<main class="container section"><div class="panel" style="max-width:680px"><span class="eyebrow">Checkout demonstrativo</span><h1>Finalizar pagamento</h1><p class="muted">Fluxo acadêmico simulado — nenhuma cobrança real será realizada.</p>
<div class="panel"><h3><?= htmlspecialchars($reserva['destino_nome']) ?></h3><p class="price">R$ <?= number_format($reserva['valor'],2,',','.') ?></p></div>
<?php if ($reserva['status']==='aguardando_pagamento'): ?>
<form method="post" action="processar_pagamento.php"><input type="hidden" name="reserva_id" value="<?= $reserva['id'] ?>"><div class="form-group"><label>Forma de pagamento</label><select name="forma_pagamento" required><option value="pix">PIX</option><option value="cartao">Cartão de crédito</option><option value="boleto">Boleto</option></select></div><br><button class="btn" type="submit">Simular pagamento e confirmar</button></form>
<?php else: ?><div class="notice">Esta reserva já está com status <strong><?= htmlspecialchars($reserva['status']) ?></strong>.</div><?php endif; ?>
</div></main></body></html>
