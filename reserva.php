<?php
session_start();
require_once 'includes/Database.php';
require_once 'includes/DestinoInfo.php';
require_once 'includes/layout.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.html?erro=nao_logado'); exit(); }

$db = (new Database())->getConnection();
$id = (int)($_POST['destino_id'] ?? $_GET['destino_id'] ?? 0);
$st = $db->prepare('SELECT * FROM destinos WHERE id = :id LIMIT 1');
$st->execute([':id' => $id]);
$d = $st->fetch();
if (!$d) { header('Location: index.php?erro=destino'); exit(); }

$hoje = new DateTimeImmutable('today', new DateTimeZone('America/Sao_Paulo'));
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ida = DateTimeImmutable::createFromFormat('!Y-m-d', (string)($_POST['data_ida'] ?? ''), new DateTimeZone('America/Sao_Paulo'));
    $volta = DateTimeImmutable::createFromFormat('!Y-m-d', (string)($_POST['data_volta'] ?? ''), new DateTimeZone('America/Sao_Paulo'));
    $viajantes = (int)($_POST['viajantes'] ?? 1);
    if (!$ida || !$volta) $erro = 'Escolha as datas de ida e de volta.';
    elseif ($ida < $hoje) $erro = 'A data de ida não pode estar no passado.';
    elseif ($volta <= $ida) $erro = 'A volta precisa ser depois da ida.';
    elseif ($ida->diff($volta)->days > 60) $erro = 'A viagem pode ter no máximo 60 dias.';
    elseif ($viajantes < 1 || $viajantes > 9) $erro = 'Escolha de 1 a 9 viajantes.';
    if (!$erro) {
        $valor = round((float)$d['preco'] * $viajantes, 2);
        $ins = $db->prepare("INSERT INTO reservas (cliente_id, destino_id, destino_nome, valor, status, data_ida, data_volta, viajantes) VALUES (:cliente, :destino, :nome, :valor, 'aguardando_pagamento', :ida, :volta, :viajantes)");
        $ins->execute([':cliente' => $_SESSION['user_id'], ':destino' => $d['id'], ':nome' => $d['nome'], ':valor' => $valor, ':ida' => $ida->format('Y-m-d'), ':volta' => $volta->format('Y-m-d'), ':viajantes' => $viajantes]);
        header('Location: pagamento.php?id=' . (int)$db->lastInsertId());
        exit();
    }
}

$info = DestinoInfo::de($d);
$idaPadrao = $_POST['data_ida'] ?? $hoje->modify('+30 days')->format('Y-m-d');
$voltaPadrao = $_POST['data_volta'] ?? $hoje->modify('+37 days')->format('Y-m-d');
$viajPadrao = (int)($_POST['viajantes'] ?? 1);
cabecalho('Reservar ' . $d['nome']);
?>
<main class="container section">
  <a class="voltar" href="destino.php?id=<?= (int)$d['id'] ?>">← Voltar para <?= h($d['nome']) ?></a>
  <div class="reserva-grade">
    <section class="panel">
      <p class="eyebrow">Monte sua viagem</p>
      <h1>Reservar <?= h($d['nome']) ?></h1>
      <?php if ($erro): ?><div class="notice erro"><?= h($erro) ?></div><?php endif; ?>
      <form method="post" action="reserva.php" class="reserva-form" id="reservaForm" data-preco="<?= h($d['preco']) ?>">
        <input type="hidden" name="destino_id" value="<?= (int)$d['id'] ?>">
        <div class="form-grid">
          <div class="form-group"><label for="data_ida">Ida</label><input type="date" id="data_ida" name="data_ida" required min="<?= $hoje->format('Y-m-d') ?>" value="<?= h($idaPadrao) ?>"></div>
          <div class="form-group"><label for="data_volta">Volta</label><input type="date" id="data_volta" name="data_volta" required min="<?= $hoje->modify('+1 day')->format('Y-m-d') ?>" value="<?= h($voltaPadrao) ?>"></div>
        </div>
        <div class="form-group"><label for="viajantes">Viajantes</label>
          <div class="contador"><button type="button" data-passo="-1" aria-label="Menos um viajante">−</button><input type="number" id="viajantes" name="viajantes" min="1" max="9" value="<?= $viajPadrao ?>" required><button type="button" data-passo="1" aria-label="Mais um viajante">+</button></div>
        </div>
        <div class="resumo-preco" aria-live="polite">
          <div><span><?= brl($d['preco']) ?> × <strong data-r="viajantes"><?= $viajPadrao ?></strong> viajante(s)</span><strong data-r="total"><?= brl($d['preco'] * $viajPadrao) ?></strong></div>
          <p class="muted" data-r="noites">7 noites</p>
        </div>
        <button class="btn" type="submit">Continuar para o pagamento</button>
      </form>
    </section>
    <aside class="panel">
      <p class="eyebrow">Sua rota</p>
      <h2>São Paulo → <?= h($d['nome']) ?></h2>
      <div class="globo-box" data-globo-rota="<?= jsonAttr(destinoParaJs($d, $info)) ?>"><img class="globo-fallback" src="<?= h($d['imagem']) ?>" alt=""></div>
      <div class="kpis kpis-rota">
        <div class="kpi"><strong><?= $info['km'] !== null ? number_format($info['km'], 0, ',', '.') . ' km' : '—' ?></strong><span class="muted">distância</span></div>
        <div class="kpi"><strong>~<?= DestinoInfo::vooTexto($info['voo_horas']) ?></strong><span class="muted">de voo</span></div>
      </div>
    </aside>
  </div>
</main>
<?php rodape(['assets/3d/rota3d.js'], ['js/reserva.js']); ?>
