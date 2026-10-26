<?php
session_start();
require_once __DIR__ . '/check_admin.php';
require_once __DIR__ . '/Database.php';

$db = (new Database())->getConnection();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'add_destino' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $lat = is_numeric($_POST['lat'] ?? null) ? max(-90, min(90, (float)$_POST['lat'])) : null;
    $lon = is_numeric($_POST['lon'] ?? null) ? max(-180, min(180, (float)$_POST['lon'])) : null;
    if ($lat === null || $lon === null) { $lat = null; $lon = null; }
    $stmt = $db->prepare('INSERT INTO destinos (nome, pais, descricao, preco, imagem, lat, lon) VALUES (:nome,:pais,:descricao,:preco,:imagem,:lat,:lon)');
    $stmt->execute([
        ':nome' => trim($_POST['nome'] ?? ''),
        ':pais' => trim($_POST['pais'] ?? ''),
        ':descricao' => trim($_POST['descricao'] ?? ''),
        ':preco' => (float)($_POST['preco'] ?? 0),
        ':imagem' => trim($_POST['imagem'] ?? ''),
        ':lat' => $lat,
        ':lon' => $lon
    ]);
}

if ($action === 'delete_destino' && isset($_GET['id'])) {
    $idDestino = (int)$_GET['id'];
    try {
        $stmt = $db->prepare('DELETE FROM destinos WHERE id = :id');
        $stmt->execute([':id' => $idDestino]);
        $db->prepare('DELETE FROM favoritos WHERE destino_id = :id')->execute([':id' => $idDestino]);
        $db->prepare('DELETE FROM avaliacoes WHERE destino_id = :id')->execute([':id' => $idDestino]);
    } catch (PDOException $e) {
        // destino com reservas não pode ser excluído (as reservas dependem dele)
        header('Location: ../admin_dashboard.php?erro=destino_com_reservas'); exit();
    }
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
