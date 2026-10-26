<?php
session_start();
require_once 'includes/Database.php';
require_once 'includes/DestinoInfo.php';
require_once 'includes/layout.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.html?erro=nao_logado'); exit(); }

$id = (int)($_GET['id'] ?? 0);
$db = (new Database())->getConnection();
$st = $db->prepare("SELECT r.*, d.nome AS dnome, d.lat, d.lon FROM reservas r LEFT JOIN destinos d ON d.id = r.destino_id WHERE r.id = :id AND r.cliente_id = :c AND r.status IN ('confirmada','concluida') LIMIT 1");
$st->execute([':id' => $id, ':c' => $_SESSION['user_id']]);
$r = $st->fetch();
if (!$r) { header('Location: minhas_reservas.php'); exit(); }

// reservas antigas (de antes deste recurso) recebem um localizador agora
if (empty($r['codigo'])) {
    $alfabeto = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; $codigo = '';
    for ($i = 0; $i < 6; $i++) $codigo .= $alfabeto[random_int(0, strlen($alfabeto) - 1)];
    $db->prepare('UPDATE reservas SET codigo = :c WHERE id = :id')->execute([':c' => $codigo, ':id' => $id]);
    $r['codigo'] = $codigo;
}
$info = DestinoInfo::de(['nome' => $r['dnome'] ?? $r['destino_nome'], 'lat' => $r['lat'] ?? null, 'lon' => $r['lon'] ?? null]);
$iataDestino = $info['iata'] ?? strtoupper(substr(DestinoInfo::chave($r['destino_nome']), 0, 3));
$semente = crc32($r['codigo']);
$dados = [
    'codigo' => $r['codigo'],
    'passageiro' => $_SESSION['user_name'],
    'viajantes' => (int)($r['viajantes'] ?? 1),
    'origem' => ['iata' => 'GRU', 'cidade' => 'São Paulo'],
    'destino' => ['iata' => $iataDestino, 'cidade' => $r['destino_nome']],
    'ida' => !empty($r['data_ida']) ? date('d/m/Y', strtotime($r['data_ida'])) : date('d/m/Y', strtotime($r['data_reserva'] . ' +30 days')),
    'volta' => !empty($r['data_volta']) ? date('d/m/Y', strtotime($r['data_volta'])) : '—',
    'horario' => sprintf('%02d:%02d', 6 + $semente % 17, ($semente >> 5) % 12 * 5),
    'portao' => chr(65 + ($semente >> 9) % 6) . (1 + ($semente >> 12) % 24),
    'assento' => (1 + ($semente >> 16) % 38) . chr(65 + ($semente >> 22) % 6),
    'voo' => 'TP' . (1000 + $semente % 9000),
    'duracao' => DestinoInfo::vooTexto($info['voo_horas']),
];
cabecalho('Cartão de embarque', false);
?>
<main class="container section embarque-pagina">
  <?php if (isset($_GET['novo'])): ?><div class="notice sucesso">✈ Pagamento confirmado! Seu cartão de embarque está pronto.</div><?php endif; ?>
  <p class="eyebrow">Cartão de embarque</p>
  <h1>Boa viagem, <?= h(explode(' ', trim($_SESSION['user_name']))[0]) ?>!</h1>
  <p class="muted">Passe o mouse para inclinar e toque no cartão para ver o verso.</p>

  <div class="bilhete-cena<?= isset($_GET['novo']) ? ' chegando' : '' ?>" id="bilhete" data-bilhete="<?= jsonAttr($dados) ?>">
    <div class="bilhete" role="button" tabindex="0" aria-label="Cartão de embarque — toque para virar">
      <div class="bilhete-face frente">
        <div class="bilhete-topo"><span class="brand">Tripora<span>.com</span></span><span class="bilhete-tag">Cartão de embarque</span></div>
        <div class="bilhete-rota">
          <div><strong><?= h($dados['origem']['iata']) ?></strong><small><?= h($dados['origem']['cidade']) ?></small></div>
          <div class="bilhete-aviao" aria-hidden="true"><span></span>✈<span></span></div>
          <div class="dir"><strong><?= h($dados['destino']['iata']) ?></strong><small><?= h($dados['destino']['cidade']) ?></small></div>
        </div>
        <div class="bilhete-campos">
          <div><small>Passageiro</small><b><?= h($dados['passageiro']) ?></b></div>
          <div><small>Data</small><b><?= h($dados['ida']) ?></b></div>
          <div><small>Embarque</small><b><?= h($dados['horario']) ?></b></div>
          <div><small>Portão</small><b><?= h($dados['portao']) ?></b></div>
          <div><small>Assento</small><b><?= h($dados['assento']) ?></b></div>
          <div><small>Voo</small><b><?= h($dados['voo']) ?></b></div>
        </div>
        <div class="bilhete-rodape"><div><small>Localizador</small><b class="localizador"><?= h($dados['codigo']) ?></b></div><div class="dir"><small>Viajantes</small><b><?= $dados['viajantes'] ?></b></div></div>
      </div>
      <div class="bilhete-face verso">
        <div class="bilhete-topo"><span class="brand">Tripora<span>.com</span></span><span class="bilhete-tag">Volta e embarque</span></div>
        <canvas class="bilhete-barras" width="560" height="120" aria-hidden="true"></canvas>
        <div class="bilhete-campos">
          <div><small>Ida</small><b><?= h($dados['ida']) ?></b></div>
          <div><small>Volta</small><b><?= h($dados['volta']) ?></b></div>
          <div><small>Duração do voo</small><b>~<?= h($dados['duracao']) ?></b></div>
          <div><small>Classe</small><b>Econômica</b></div>
        </div>
        <p class="muted bilhete-aviso">Documento demonstrativo de um projeto acadêmico. Não vale como passagem.</p>
      </div>
    </div>
  </div>
  <div class="hero-actions bilhete-acoes">
    <button class="btn" type="button" id="baixarBilhete">Baixar imagem</button>
    <a class="btn secondary" href="minhas_reservas.php">Minhas viagens</a>
  </div>
</main>
<?php rodape([], ['js/embarque.js']); ?>
