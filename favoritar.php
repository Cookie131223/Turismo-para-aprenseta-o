<?php
// Liga/desliga um destino nos favoritos do usuário. Responde em JSON.
session_start();
require_once 'includes/Database.php';
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['erro' => 'login']); exit(); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['erro' => 'metodo']); exit(); }

$destino = (int)($_POST['destino_id'] ?? 0);
$db = (new Database())->getConnection();
$st = $db->prepare('SELECT id FROM destinos WHERE id = :id');
$st->execute([':id' => $destino]);
if (!$st->fetch()) { http_response_code(404); echo json_encode(['erro' => 'destino']); exit(); }

$st = $db->prepare('SELECT 1 FROM favoritos WHERE cliente_id = :c AND destino_id = :d');
$st->execute([':c' => $_SESSION['user_id'], ':d' => $destino]);
if ($st->fetchColumn()) {
    $db->prepare('DELETE FROM favoritos WHERE cliente_id = :c AND destino_id = :d')->execute([':c' => $_SESSION['user_id'], ':d' => $destino]);
    echo json_encode(['favorito' => false]);
} else {
    $db->prepare('INSERT INTO favoritos (cliente_id, destino_id) VALUES (:c, :d)')->execute([':c' => $_SESSION['user_id'], ':d' => $destino]);
    echo json_encode(['favorito' => true]);
}
