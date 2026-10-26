<?php
session_start();
require_once 'includes/Database.php';
require_once 'includes/DestinoInfo.php';
require_once 'includes/layout.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.html?erro=nao_logado'); exit(); }

$id = (int)($_GET['id'] ?? 0);
$db = (new Database())->getConnection();
$stmt = $db->prepare('SELECT r.*, d.imagem, d.pais FROM reservas r LEFT JOIN destinos d ON d.id = r.destino_id WHERE r.id = :id AND r.cliente_id = :cliente_id LIMIT 1');
$stmt->execute([':id' => $id, ':cliente_id' => $_SESSION['user_id']]);
$reserva = $stmt->fetch();
if (!$reserva) { header('Location: minhas_reservas.php'); exit(); }

$desconto = (float)($reserva['desconto'] ?? 0);
$total = max(0, (float)$reserva['valor'] - $desconto);
$viajantes = (int)($reserva['viajantes'] ?? 1);
$msgCupom = ['ok' => 'Cupom aplicado!', 'invalido' => 'Cupom inválido ou expirado.', 'removido' => 'Cupom removido.'][$_GET['cupom'] ?? ''] ?? '';
cabecalho('Pagamento', false);
?>
<main class="container section"><div class="panel pagamento" style="max-width:720px">
  <span class="eyebrow">Checkout demonstrativo</span><h1>Finalizar pagamento</h1>
  <p class="muted">Fluxo acadêmico simulado — nenhuma cobrança real será realizada.</p>

  <div class="pag-resumo">
    <?php if (!empty($reserva['imagem'])): ?><img src="<?= h($reserva['imagem']) ?>" alt=""><?php endif; ?>
    <div>
      <h3><?= h($reserva['destino_nome']) ?></h3>
      <p class="muted"><?php if (!empty($reserva['data_ida'])): ?><?= date('d/m/Y', strtotime($reserva['data_ida'])) ?> a <?= date('d/m/Y', strtotime($reserva['data_volta'])) ?> · <?php endif; ?><?= $viajantes ?> viajante<?= $viajantes > 1 ? 's' : '' ?></p>
    </div>
  </div>
  <ul class="pag-linhas">
    <li><span>Subtotal</span><span><?= brl($reserva['valor']) ?></span></li>
    <?php if ($desconto > 0): ?><li class="desconto"><span>Cupom <?= h($reserva['cupom']) ?></span><span>− <?= brl($desconto) ?></span></li><?php endif; ?>
    <li class="total"><span>Total</span><strong><?= brl($total) ?></strong></li>
  </ul>

  <?php if ($reserva['status'] === 'aguardando_pagamento'): ?>
    <form method="post" action="aplicar_cupom.php" class="cupom-form">
      <input type="hidden" name="reserva_id" value="<?= (int)$reserva['id'] ?>">
      <label for="cupom">Cupom de desconto</label>
      <div class="cupom-linha">
        <input id="cupom" name="cupom" placeholder="Ex.: TRIPORA10" maxlength="40" autocomplete="off" value="<?= h($reserva['cupom'] ?? '') ?>">
        <button class="btn secondary" type="submit">Aplicar</button>
        <?php if (!empty($reserva['cupom'])): ?><button class="btn secondary" type="submit" name="remover" value="1">Remover</button><?php endif; ?>
      </div>
      <?php if ($msgCupom): ?><p class="cupom-msg <?= ($_GET['cupom'] ?? '') === 'invalido' ? 'erro' : 'ok' ?>"><?= h($msgCupom) ?></p><?php endif; ?>
      <p class="muted cupom-dica">Para a apresentação: TRIPORA10, BEMVINDO15 ou APRESENTACAO20.</p>
    </form>
    <form method="post" action="processar_pagamento.php">
      <input type="hidden" name="reserva_id" value="<?= (int)$reserva['id'] ?>">
      <div class="form-group"><label for="forma">Forma de pagamento</label><select id="forma" name="forma_pagamento" required><option value="pix">PIX</option><option value="cartao">Cartão de crédito</option><option value="boleto">Boleto</option></select></div><br>
      <button class="btn" type="submit">Pagar <?= brl($total) ?> e confirmar</button>
    </form>
  <?php else: ?>
    <div class="notice">Esta reserva já está com status <strong><?= h(str_replace('_', ' ', $reserva['status'])) ?></strong>.</div>
    <?php if (in_array($reserva['status'], ['confirmada', 'concluida'], true)): ?><p><a class="btn" href="embarque.php?id=<?= (int)$reserva['id'] ?>">Ver cartão de embarque</a></p><?php endif; ?>
  <?php endif; ?>
</div></main>
<?php rodape(); ?>
