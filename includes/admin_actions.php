<?php
session_start();
require_once __DIR__ . '/check_admin.php';
require_once __DIR__ . '/Database.php';

$db = (new Database())->getConnection();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'add_destino' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare('INSERT INTO destinos (nome, pais, descricao, preco, imagem) VALUES (:nome,:pais,:descricao,:preco,:imagem)');
    $stmt->execute([
        ':nome' => trim($_POST['nome'] ?? ''),
        ':pais' => trim($_POST['pais'] ?? ''),
        ':descricao' => trim($_POST['descricao'] ?? ''),
        ':preco' => (float)($_POST['preco'] ?? 0),
        ':imagem' => trim($_POST['imagem'] ?? '')
    ]);
}

if ($action === 'delete_destino' && isset($_GET['id'])) {
    $stmt = $db->prepare('DELETE FROM destinos WHERE id = :id');
    $stmt->execute([':id' => (int)$_GET['id']]);
}

if ($action === 'update_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $permitidos = ['aguardando_pagamento','confirmada','cancelada','concluida'];
    $status = $_POST['status'] ?? '';
    if (in_array($status, $permitidos, true)) {
        $stmt = $db->prepare('UPDATE reservas SET status = :status WHERE id = :id');
        $stmt->execute([':status' => $status, ':id' => (int)($_POST['reserva_id'] ?? 0)]);
    }
}

header('Location: ../admin_dashboard.php?ok=1');
exit();
