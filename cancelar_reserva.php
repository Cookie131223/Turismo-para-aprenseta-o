<?php
session_start();
require_once 'includes/Database.php';
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: login.html'); exit(); }
$id=(int)($_POST['reserva_id'] ?? 0);
$db=(new Database())->getConnection();
$stmt=$db->prepare("UPDATE reservas SET status='cancelada' WHERE id=:id AND cliente_id=:cliente_id AND status IN ('aguardando_pagamento','confirmada')");
$stmt->execute([':id'=>$id,':cliente_id'=>$_SESSION['user_id']]);
header('Location: minhas_reservas.php?sucesso=cancelada');
exit();
