<?php
session_start();
require_once 'includes/Database.php';
require_once 'includes/DestinoInfo.php';
require_once 'includes/layout.php';

$db = (new Database())->getConnection();
$todos = $db->query('SELECT * FROM destinos ORDER BY nome')->fetchAll();
$ids = array_values(array_unique(array_filter(array_map('intval', explode(',', (string)($_GET['ids'] ?? ''))))));
$ids = array_slice($ids, 0, 3);
$escolhidos = array_values(array_filter($todos, fn($d) => in_array((int)$d['id'], $ids, true)));
usort($escolhidos, fn($a, $b) => array_search((int)$a['id'], $ids) <=> array_search((int)$b['id'], $ids));

$notas = [];
foreach ($db->query('SELECT destino_id, AVG(nota) AS media, COUNT(*) AS qtd FROM avaliacoes GROUP BY destino_id')->fetchAll() as $n) $notas[(int)$n['destino_id']] = $n;

$linhas = [];
foreach ($escolhidos as $d) {
    $i = DestinoInfo::de($d);
    $linhas[] = ['d' => $d, 'i' => $i, 'nota' => isset($notas[(int)$d['id']]) ? round((float)$notas[(int)$d['id']]['media'], 1) : null];
}
$barras = fn(string $titulo, string $campo, callable $texto) => ['titulo' => $titulo, 'itens' => array_map(fn($l) => ['rotulo' => $l['d']['nome'], 'valor' => (float)($campo === 'preco' ? $l['d']['preco'] : ($campo === 'nota' ? ($l['nota'] ?? 0) : ($l['i'][$campo] ?? 0))), 'texto' => $texto($l)], $linhas)];
cabecalho('Comparar destinos');
?>
<main class="container section">
  <p class="eyebrow">Comparar destinos</p>
  <h1>Qual vai ser a próxima?</h1>
  <form class="comparar-escolha" method="get" action="comparar.php" id="compararForm">
    <?php for ($k = 0; $k < 3; $k++): ?>
      <label class="sr-only" for="sel<?= $k ?>">Destino <?= $k + 1 ?></label>
      <select id="sel<?= $k ?>" data-slot>
        <option value=""><?= $k < 2 ? 'Escolha um destino' : '(opcional) 3º destino' ?></option>
        <?php foreach ($todos as $d): ?><option value="<?= (int)$d['id'] ?>" <?= ($ids[$k] ?? 0) === (int)$d['id'] ? 'selected' : '' ?>><?= h($d['nome']) ?></option><?php endforeach; ?>
      </select>
    <?php endfor; ?>
    <input type="hidden" name="ids" value="<?= h(implode(',', $ids)) ?>">
    <button class="btn" type="submit">Comparar</button>
  </form>

  <?php if (count($linhas) < 2): ?>
    <div class="panel"><p class="muted">Escolha pelo menos dois destinos acima, ou marque "Comparar" nos cards da página inicial.</p></div>
  <?php else: ?>
  <div class="comparar-cards" style="--colunas: <?= count($linhas) ?>">
    <?php foreach ($linhas as $l): $d = $l['d']; $i = $l['i']; ?>
      <article class="card comparar-card card-tilt">
        <div class="card-image-wrap"><img src="<?= h($d['imagem']) ?>" alt="<?= h($d['nome']) ?>" loading="lazy"></div>
        <div class="card-body">
          <p class="eyebrow"><?= h($d['pais']) ?></p><h3><?= h($d['nome']) ?></h3>
          <ul class="lista-info">
            <li><span>Preço por pessoa</span><strong><?= brl($d['preco']) ?></strong></li>
            <li><span>Clima agora</span><strong><?php if ($i['lat'] !== null): ?><span data-clima-lat="<?= h($i['lat']) ?>" data-clima-lon="<?= h($i['lon']) ?>" data-clima-curto="1">…</span><?php else: ?>—<?php endif; ?></strong></li>
            <li><span>Hora local</span><strong><?php if ($i['fuso']): ?><span data-fuso="<?= h($i['fuso']) ?>">--:--</span><?php else: ?>—<?php endif; ?></strong></li>
            <li><span>Moeda</span><strong><?php if ($i['moeda'] && $i['moeda'] !== 'BRL'): ?><span data-moeda-celula="<?= h($i['moeda']) ?>"><?= h($i['moeda']) ?></span><?php else: ?><?= h($i['moeda'] ?? '—') ?><?php endif; ?></strong></li>
            <li><span>Melhor época</span><strong><?= h($i['epoca'] ?? '—') ?></strong></li>
            <li><span>Idioma</span><strong><?= h($i['idioma'] ?? '—') ?></strong></li>
            <li><span>Voo desde SP</span><strong><?= $i['voo_horas'] !== null ? '~' . DestinoInfo::vooTexto($i['voo_horas']) : '—' ?></strong></li>
            <li><span>Avaliação</span><strong><?= $l['nota'] !== null ? '★ ' . number_format($l['nota'], 1, ',', '') : 'sem notas' ?></strong></li>
          </ul>
          <div class="card-actions"><a class="btn secondary small" href="destino.php?id=<?= (int)$d['id'] ?>">Ver destino</a><a class="btn small" href="reserva.php?destino_id=<?= (int)$d['id'] ?>">Reservar</a></div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>

  <section class="graficos-comparar">
    <div class="panel"><div class="barras3d" data-barras="<?= jsonAttr($barras('Preço por pessoa', 'preco', fn($l) => brl($l['d']['preco']))) ?>"></div></div>
    <div class="panel"><div class="barras3d" data-barras="<?= jsonAttr($barras('Tempo de voo (horas)', 'voo_horas', fn($l) => $l['i']['voo_horas'] !== null ? '~' . DestinoInfo::vooTexto($l['i']['voo_horas']) : '—')) ?>"></div></div>
    <div class="panel"><div class="barras3d" data-barras="<?= jsonAttr($barras('Distância de São Paulo (km)', 'km', fn($l) => $l['i']['km'] !== null ? number_format($l['i']['km'], 0, ',', '.') . ' km' : '—')) ?>"></div></div>
  </section>
  <?php endif; ?>
</main>
<script>
// junta os 3 selects no parâmetro ?ids=1,2,3
document.getElementById('compararForm').addEventListener('submit', function () {
  var ids = Array.prototype.map.call(this.querySelectorAll('[data-slot]'), function (s) { return s.value; }).filter(Boolean);
  this.querySelector('[name=ids]').value = ids.filter(function (v, i) { return ids.indexOf(v) === i; }).join(',');
});
</script>
<?php rodape(['assets/3d/cards-tilt.js', 'assets/3d/transicao.js'], ['js/ao-vivo.js', 'js/barras3d.js']); ?>
