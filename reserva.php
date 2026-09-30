<?php
session_start();
require_once 'includes/Database.php';
if(!isset($_SESSION['user_id'])){ header('Location: login.html?erro=nao_logado'); exit(); }
$id=(int)($_GET['destino_id']??0);
$db=(new Database())->getConnection();
$stmt=$db->prepare('SELECT * FROM destinos WHERE id=:id LIMIT 1'); $stmt->execute([':id'=>$id]); $d=$stmt->fetch();
if(!$d){ header('Location: index.php?erro=destino'); exit(); }
$insert=$db->prepare('INSERT INTO reservas (cliente_id,destino_id,destino_nome,valor,status) VALUES (:cliente,:destino,:nome,:valor,\'aguardando_pagamento\')');
$insert->execute([':cliente'=>$_SESSION['user_id'],':destino'=>$d['id'],':nome'=>$d['nome'],':valor'=>$d['preco']]);
$reservaId=(int)$db->lastInsertId();
header('Location: pagamento.php?id='.$reservaId);
exit();
