<?php
session_start();
require_once 'includes/Database.php';
require_once 'includes/DestinoInfo.php';
require_once 'includes/layout.php';

$db = (new Database())->getConnection();
$id = (int)($_GET['id'] ?? 0);
$st = $db->prepare('SELECT * FROM destinos WHERE id = :id LIMIT 1');
$st->execute([':id' => $id]);
$d = $st->fetch();
if (!$d) { header('Location: index.php?erro=destino'); exit(); }
$info = DestinoInfo::de($d);
$userId = $_SESSION['user_id'] ?? null;

// avaliações
$st = $db->prepare('SELECT a.nota, a.comentario, a.created_at, c.nome FROM avaliacoes a JOIN clientes c ON c.id = a.cliente_id WHERE a.destino_id = :id ORDER BY a.created_at DESC');
$st->execute([':id' => $id]);
$avaliacoes = $st->fetchAll();
$media = $avaliacoes ? array_sum(array_column($avaliacoes, 'nota')) / count($avaliacoes) : 0;

$favorito = false; $podeAvaliar = false; $minhaNota = null;
if ($userId) {
    $st = $db->prepare('SELECT 1 FROM favoritos WHERE cliente_id = :c AND destino_id = :d');
    $st->execute([':c' => $userId, ':d' => $id]); $favorito = (bool)$st->fetchColumn();
    $st = $db->prepare("SELECT COUNT(*) FROM reservas WHERE cliente_id = :c AND destino_id = :d AND status IN ('confirmada','concluida')");
    $st->execute([':c' => $userId, ':d' => $id]); $podeAvaliar = (int)$st->fetchColumn() > 0;
    $st = $db->prepare('SELECT nota, comentario FROM avaliacoes WHERE cliente_id = :c AND destino_id = :d');
    $st->execute([':c' => $userId, ':d' => $id]); $minhaNota = $st->fetch() ?: null;
}
$imgGrande = preg_match('~images\.unsplash\.com~', $d['imagem']) ? preg_replace('~([?&])w=\d+~', '$1w=2400', $d['imagem']) : $d['imagem'];
$jsDestino = destinoParaJs($d, $info);

function estrelas(float $n): string {
    $cheias = (int)round($n); return str_repeat('★', $cheias) . str_repeat('☆', 5 - $cheias);
}

cabecalho($d['nome']);
?>
<main class="container section destino-pagina">
  <a class="voltar" href="index.php#destinos">← Todos os destinos</a>
  <?php if (isset($_GET['avaliacao'])): ?><div class="notice"><?= $_GET['avaliacao'] === 'ok' ? 'Obrigado! Sua avaliação foi publicada.' : 'Não foi possível salvar a avaliação.' ?></div><?php endif; ?>
  <section class="destino-topo">
    <div class="panorama" data-img="<?= h($imgGrande) ?>" aria-label="Vista panorâmica de <?= h($d['nome']) ?>">
      <img src="<?= h($d['imagem']) ?>" alt="<?= h($d['nome']) ?>">
      <span class="panorama-dica">Arraste para olhar em volta</span>
    </div>
    <div class="destino-info">
      <p class="eyebrow"><?= h($d['pais']) ?></p>
      <h1><?= h($d['nome']) ?></h1>
      <p class="estrelas" title="<?= $avaliacoes ? number_format($media, 1, ',', '') . ' de 5' : 'Sem avaliações ainda' ?>"><?= estrelas($media) ?> <span class="muted"><?= $avaliacoes ? number_format($media, 1, ',', '') . ' · ' . count($avaliacoes) . ' avaliação' . (count($avaliacoes) > 1 ? 'ões' : '') : 'Sem avaliações ainda' ?></span></p>
      <p class="muted"><?= h($d['descricao']) ?></p>
      <div class="chips-ao-vivo">
        <?php if ($info['lat'] !== null): ?><span class="chip" data-clima-lat="<?= h($info['lat']) ?>" data-clima-lon="<?= h($info['lon']) ?>">Clima: carregando…</span><?php endif; ?>
        <?php if ($info['fuso']): ?><span class="chip">Hora local: <strong data-fuso="<?= h($info['fuso']) ?>">--:--</strong></span><?php endif; ?>
        <?php if ($info['voo_horas'] !== null): ?><span class="chip">✈ <?= number_format($info['km'], 0, ',', '.') ?> km · ~<?= DestinoInfo::vooTexto($info['voo_horas']) ?> de voo</span><?php endif; ?>
      </div>
      <div class="destino-preco">
        <div><span class="price-label">a partir de, por pessoa</span><p class="price"><?= brl($d['preco']) ?></p>
          <?php if ($info['moeda'] && $info['moeda'] !== 'BRL'): ?><p class="muted conv-linha" data-moeda="<?= h($info['moeda']) ?>" data-brl="<?= h($d['preco']) ?>">Convertendo…</p><?php endif; ?></div>
        <div class="destino-acoes">
          <button type="button" class="fav-btn<?= $favorito ? ' ativo' : '' ?>" data-destino="<?= $id ?>" aria-pressed="<?= $favorito ? 'true' : 'false' ?>" aria-label="Favoritar <?= h($d['nome']) ?>">♥</button>
          <a class="btn" href="reserva.php?destino_id=<?= $id ?>">Reservar</a>
        </div>
      </div>
    </div>
  </section>

  <section class="destino-grade">
    <div class="panel">
      <p class="eyebrow">Sua rota</p><h2>De São Paulo a <?= h($d['nome']) ?></h2>
      <div class="globo-box" data-globo-rota="<?= jsonAttr($jsDestino) ?>"><img class="globo-fallback" src="<?= h($d['imagem']) ?>" alt=""></div>
      <div class="kpis kpis-rota">
        <div class="kpi"><strong><?= $info['km'] !== null ? number_format($info['km'], 0, ',', '.') . ' km' : '—' ?></strong><span class="muted">distância</span></div>
        <div class="kpi"><strong>~<?= DestinoInfo::vooTexto($info['voo_horas']) ?></strong><span class="muted">tempo de voo</span></div>
        <div class="kpi"><strong><?= h($info['epoca'] ?? '—') ?></strong><span class="muted">melhor época</span></div>
      </div>
    </div>
    <div class="panel">
      <p class="eyebrow">Quanto custa lá</p><h2>Conversor de moeda</h2>
      <?php if ($info['moeda'] && $info['moeda'] !== 'BRL'): ?>
      <div class="conversor" data-moeda="<?= h($info['moeda']) ?>">
        <label for="conv-brl">Valor em reais</label>
        <div class="conversor-linha"><span>R$</span><input id="conv-brl" type="number" min="0" step="10" value="<?= h((int)$d['preco']) ?>"></div>
        <p class="conversor-saida" aria-live="polite">—</p>
        <p class="muted conversor-fonte">Cotação do dia (open.er-api.com). Valores aproximados.</p>
      </div>
      <?php else: ?><p class="muted">Aqui a moeda é o real. Sem conversão necessária!</p><?php endif; ?>
      <ul class="lista-info">
        <li><span>Idioma</span><strong><?= h($info['idioma'] ?? '—') ?></strong></li>
        <li><span>Moeda</span><strong><?= h($info['moeda'] ?? '—') ?></strong></li>
        <li><span>Fuso</span><strong><?= h($info['fuso'] ? str_replace('_', ' ', $info['fuso']) : '—') ?></strong></li>
      </ul>
    </div>
  </section>

  <section class="panel" id="avaliacoes">
    <div class="section-head"><div><p class="eyebrow">Avaliações</p><h2>O que os viajantes dizem</h2></div>
      <p class="estrelas grande"><?= estrelas($media) ?> <span class="muted"><?= $avaliacoes ? number_format($media, 1, ',', '') : '—' ?></span></p></div>
    <?php if ($podeAvaliar): ?>
      <form class="avaliar-form" method="post" action="avaliar.php">
        <input type="hidden" name="destino_id" value="<?= $id ?>">
        <fieldset class="nota-escolha"><legend><?= $minhaNota ? 'Atualize sua nota' : 'Sua nota' ?></legend>
          <?php for ($n = 5; $n >= 1; $n--): ?><input type="radio" id="nota<?= $n ?>" name="nota" value="<?= $n ?>" <?= ($minhaNota['nota'] ?? 5) == $n ? 'checked' : '' ?> required><label for="nota<?= $n ?>" title="<?= $n ?> estrela<?= $n > 1 ? 's' : '' ?>">★</label><?php endfor; ?>
        </fieldset>
        <div class="form-group"><label for="comentario">Comentário (opcional)</label><textarea id="comentario" name="comentario" maxlength="600" rows="3"><?= h($minhaNota['comentario'] ?? '') ?></textarea></div>
        <button class="btn" type="submit">Publicar avaliação</button>
      </form>
    <?php elseif ($userId): ?><p class="muted">Você poderá avaliar <?= h($d['nome']) ?> depois de ter uma reserva confirmada para este destino.</p>
    <?php else: ?><p class="muted"><a href="login.html">Entre</a> para avaliar depois da sua viagem.</p><?php endif; ?>
    <div class="avaliacoes-lista">
      <?php if (!$avaliacoes): ?><p class="muted">Ainda não há avaliações. Seja o primeiro!</p><?php endif; ?>
      <?php foreach ($avaliacoes as $a): ?>
        <article class="avaliacao"><div class="avaliacao-topo"><strong><?= h(explode(' ', trim($a['nome']))[0]) ?></strong><span class="estrelas"><?= estrelas((float)$a['nota']) ?></span></div>
          <?php if (trim((string)$a['comentario']) !== ''): ?><p><?= nl2br(h($a['comentario'])) ?></p><?php endif; ?>
          <small class="muted"><?= date('d/m/Y', strtotime($a['created_at'])) ?></small></article>
      <?php endforeach; ?>
    </div>
  </section>
</main>
<?php rodape(['assets/3d/particulas.js', 'assets/3d/panorama.js', 'assets/3d/rota3d.js', 'assets/3d/transicao.js'], ['js/ao-vivo.js', 'js/favoritos.js']); ?>
