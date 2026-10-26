<?php
// Aplica (ou remove) um cupom numa reserva que ainda está aguardando pagamento.
session_start();
require_once 'includes/Database.php';
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: login.html'); exit(); }

$id = (int)($_POST['reserva_id'] ?? 0);
$db = (new Database())->getConnection();
$st = $db->prepare("SELECT * FROM reservas WHERE id = :id AND cliente_id = :c AND status = 'aguardando_pagamento'");
$st->execute([':id' => $id, ':c' => $_SESSION['user_id']]);
$reserva = $st->fetch();
if (!$reserva) { header('Location: minhas_reservas.php'); exit(); }

if (!empty($_POST['remover'])) {
    $db->prepare('UPDATE reservas SET cupom = NULL, desconto = 0 WHERE id = :id')->execute([':id' => $id]);
    header("Location: pagamento.php?id=$id&cupom=removido"); exit();
}

$codigo = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string)($_POST['cupom'] ?? '')));
$st = $db->prepare('SELECT codigo, percentual FROM cupons WHERE UPPER(codigo) = :c AND ativo = 1');
$st->execute([':c' => $codigo]);
$cupom = $codigo !== '' ? $st->fetch() : false;
if (!$cupom) { header("Location: pagamento.php?id=$id&cupom=invalido"); exit(); }

$desconto = round((float)$reserva['valor'] * (int)$cupom['percentual'] / 100, 2);
$db->prepare('UPDATE reservas SET cupom = :c, desconto = :d WHERE id = :id')->execute([':c' => $cupom['codigo'], ':d' => $desconto, ':id' => $id]);
header("Location: pagamento.php?id=$id&cupom=ok");
exit();
