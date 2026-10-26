<?php
// Salva (ou atualiza) a avaliação do usuário para um destino em que ele já tem reserva confirmada.
session_start();
require_once 'includes/Database.php';
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: login.html?erro=nao_logado'); exit(); }

$destino = (int)($_POST['destino_id'] ?? 0);
$nota = (int)($_POST['nota'] ?? 0);
$comentario = trim((string)($_POST['comentario'] ?? ''));
if (function_exists('mb_substr')) $comentario = mb_substr($comentario, 0, 600); else $comentario = substr($comentario, 0, 600);
$volta = 'destino.php?id=' . $destino;
if ($nota < 1 || $nota > 5) { header("Location: $volta&avaliacao=erro#avaliacoes"); exit(); }

$db = (new Database())->getConnection();
$st = $db->prepare("SELECT COUNT(*) FROM reservas WHERE cliente_id = :c AND destino_id = :d AND status IN ('confirmada','concluida')");
$st->execute([':c' => $_SESSION['user_id'], ':d' => $destino]);
if ((int)$st->fetchColumn() === 0) { header("Location: $volta&avaliacao=erro#avaliacoes"); exit(); }

$st = $db->prepare('SELECT id FROM avaliacoes WHERE cliente_id = :c AND destino_id = :d');
$st->execute([':c' => $_SESSION['user_id'], ':d' => $destino]);
if ($idAval = $st->fetchColumn()) {
    $db->prepare('UPDATE avaliacoes SET nota = :n, comentario = :t WHERE id = :id')->execute([':n' => $nota, ':t' => $comentario, ':id' => $idAval]);
} else {
    $db->prepare('INSERT INTO avaliacoes (cliente_id, destino_id, nota, comentario) VALUES (:c, :d, :n, :t)')->execute([':c' => $_SESSION['user_id'], ':d' => $destino, ':n' => $nota, ':t' => $comentario]);
}
header("Location: $volta&avaliacao=ok#avaliacoes");
exit();
