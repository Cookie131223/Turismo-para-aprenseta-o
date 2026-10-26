<?php
session_start();
require_once 'includes/Database.php';
require_once 'includes/DestinoInfo.php';
require_once 'includes/layout.php';
if(!isset($_SESSION['user_id'])){ header('Location: login.html?erro=nao_logado'); exit(); }
$db=(new Database())->getConnection();
$stmt=$db->prepare('SELECT r.*, d.nome AS dnome, d.pais, d.lat, d.lon, d.imagem FROM reservas r LEFT JOIN destinos d ON d.id = r.destino_id WHERE r.cliente_id=:id ORDER BY r.data_reserva DESC');
$stmt->execute([':id'=>$_SESSION['user_id']]); $reservas=$stmt->fetchAll();

// mapa das viagens: um pino por destino (verde = paga/concluída, amarelo = aguardando pagamento)
$porDestino = []; $paises = []; $km = 0; $ativas = 0;
foreach ($reservas as $r) {
    if ($r['status'] === 'cancelada') continue;
    $ativas++;
    $info = DestinoInfo::de(['nome' => $r['dnome'] ?? $r['destino_nome'], 'lat' => $r['lat'], 'lon' => $r['lon']]);
    if ($r['pais']) $paises[$r['pais']] = true;
    if ($info['km'] !== null) $km += 2 * $info['km'];
    if ($info['lat'] === null) continue;
    $pago = in_array($r['status'], ['confirmada', 'concluida'], true);
    $k = (int)$r['destino_id'];
    if (!isset($porDestino[$k]) || $pago) {
        $porDestino[$k] = ['id' => $k, 'nome' => $r['destino_nome'], 'lat' => $info['lat'], 'lon' => $info['lon'],
            'cor' => $pago ? '#38d996' : '#ffc24b', 'corRota' => $pago ? '#38d996' : '#ffc24b'];
    }
}
// favoritos
$st = $db->prepare('SELECT d.* FROM favoritos f JOIN destinos d ON d.id = f.destino_id WHERE f.cliente_id = :c ORDER BY f.created_at DESC');
$st->execute([':c' => $_SESSION['user_id']]); $favoritos = $st->fetchAll();

$rotulos = ['aguardando_pagamento' => 'Aguardando pagamento', 'confirmada' => 'Confirmada', 'concluida' => 'Concluída', 'cancelada' => 'Cancelada'];
cabecalho('Minhas viagens');
?>
<main class="container section">
<div class="section-head"><div><p class="eyebrow">Área do viajante</p><h1>Minhas viagens</h1><p class="muted">Olá, <?= h($_SESSION['user_name']) ?>. Acompanhe, pague ou cancele suas reservas.</p></div><a class="btn" href="index.php#destinos">Nova reserva</a></div>
<?php if(isset($_GET['sucesso'])): ?><div class="notice">Operação realizada com sucesso.</div><?php endif; ?>

<section class="panel mapa-viagens">
  <div class="mapa-viagens-grade">
    <div class="globo-box globo-viagens" data-globo-viagens="<?= jsonAttr(array_values($porDestino)) ?>">
      <?php if (!$porDestino): ?><p class="muted globo-vazio">Seu mapa aparece aqui depois da primeira reserva.</p><?php endif; ?>
    </div>
    <div>
      <p class="eyebrow">Meu mapa de viagens</p>
      <h2><?= count($paises) ?> <?= count($paises) === 1 ? 'país' : 'países' ?>, <?= number_format($km, 0, ',', '.') ?> km</h2>
      <div class="kpis kpis-viagens">
        <div class="kpi"><strong><?= $ativas ?></strong><span class="muted">viagens ativas</span></div>
        <div class="kpi"><strong><?= count($paises) ?></strong><span class="muted"><?= count($paises) === 1 ? 'país' : 'países' ?></span></div>
        <div class="kpi"><strong><?= number_format($km, 0, ',', '.') ?></strong><span class="muted">km (ida e volta)</span></div>
      </div>
      <p class="legenda"><span class="ponto verde"></span>Paga <span class="ponto amarelo"></span>Aguardando pagamento <span class="ponto origem"></span>São Paulo</p>
    </div>
  </div>
</section>

<div class="table-wrap"><table><thead><tr><th>Destino</th><th>Datas</th><th>Valor</th><th>Pagamento</th><th>Status</th><th>Ações</th></tr></thead><tbody>
<?php if(!$reservas): ?><tr><td colspan="6">Você ainda não possui reservas.</td></tr><?php endif; ?>
<?php foreach($reservas as $r):
  $total = (float)$r['valor'] - (float)($r['desconto'] ?? 0); $v = (int)($r['viajantes'] ?? 1); ?>
<tr>
  <td><strong><?= h($r['destino_nome']) ?></strong><br><small class="muted"><?= $v ?> viajante<?= $v > 1 ? 's' : '' ?> · reservado em <?= date('d/m/Y',strtotime($r['data_reserva'])) ?></small></td>
  <td><?= !empty($r['data_ida']) ? date('d/m',strtotime($r['data_ida'])).' → '.date('d/m/Y',strtotime($r['data_volta'])) : '—' ?></td>
  <td><?= brl($total) ?><?php if (!empty($r['cupom'])): ?><br><small class="muted">cupom <?= h($r['cupom']) ?></small><?php endif; ?></td>
  <td><?= $r['data_pagamento'] ? date('d/m/Y H:i',strtotime($r['data_pagamento'])) : 'Pendente' ?></td>
  <td><span class="badge <?= h($r['status']) ?>"><?= h($rotulos[$r['status']] ?? $r['status']) ?></span></td>
  <td><div class="card-actions">
    <?php if($r['status']==='aguardando_pagamento'): ?><a class="btn small" href="pagamento.php?id=<?= (int)$r['id'] ?>">Pagar</a><?php endif; ?>
    <?php if(in_array($r['status'],['confirmada','concluida'],true)): ?><a class="btn small" href="embarque.php?id=<?= (int)$r['id'] ?>">Cartão de embarque</a><a class="btn small secondary" href="destino.php?id=<?= (int)$r['destino_id'] ?>#avaliacoes">Avaliar</a><?php endif; ?>
    <?php if(in_array($r['status'],['aguardando_pagamento','confirmada'],true)): ?><form method="post" action="cancelar_reserva.php" onsubmit="return confirm('Cancelar esta reserva?')"><input type="hidden" name="reserva_id" value="<?= (int)$r['id'] ?>"><button class="btn small danger" type="submit">Cancelar</button></form><?php endif; ?>
  </div></td>
</tr><?php endforeach; ?>
</tbody></table></div>

<section class="section">
  <div class="section-head"><div><p class="eyebrow">Favoritos</p><h2>Destinos que você curtiu</h2></div></div>
  <?php if (!$favoritos): ?><p class="muted">Toque no ♥ de um destino para guardá-lo aqui.</p>
  <?php else: ?><div class="favoritos-grade">
    <?php foreach ($favoritos as $f): ?>
      <div class="favorito-item"><a href="destino.php?id=<?= (int)$f['id'] ?>"><img src="<?= h($f['imagem']) ?>" alt="" loading="lazy"><span><strong><?= h($f['nome']) ?></strong><small class="muted"><?= h($f['pais']) ?> · <?= brl($f['preco']) ?></small></span></a>
        <button type="button" class="fav-btn ativo" data-destino="<?= (int)$f['id'] ?>" aria-pressed="true" aria-label="Remover <?= h($f['nome']) ?> dos favoritos">♥</button></div>
    <?php endforeach; ?></div><?php endif; ?>
</section>
</main>
<?php rodape(['assets/3d/mapa-viagens.js'], ['js/favoritos.js']); ?>
