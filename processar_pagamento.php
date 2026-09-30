<?php
session_start();
require_once 'includes/Database.php';
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: login.html'); exit(); }

$id = (int)($_POST['reserva_id'] ?? 0);
$forma = $_POST['forma_pagamento'] ?? '';
$formas = ['pix','cartao','boleto'];
if (!in_array($forma,$formas,true)) { header('Location: minhas_reservas.php?erro=pagamento'); exit(); }

$db=(new Database())->getConnection();
$stmt=$db->prepare("UPDATE reservas SET status='confirmada', forma_pagamento=:forma, data_pagamento=NOW() WHERE id=:id AND cliente_id=:cliente_id AND status='aguardando_pagamento'");
$stmt->execute([':forma'=>$forma,':id'=>$id,':cliente_id'=>$_SESSION['user_id']]);
header('Location: minhas_reservas.php?sucesso=pagamento');
exit();
