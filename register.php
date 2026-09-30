<?php
session_start();
require_once 'includes/Database.php';
require_once 'includes/Cliente.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: registrar.html'); exit(); }

$nome=trim($_POST['nome']??'');
$email=trim($_POST['email']??'');
$senha=$_POST['senha']??'';

if($nome==='' || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($senha)<6){
    header('Location: registrar.html?erro=dados_invalidos'); exit();
}

$db=(new Database())->getConnection();
$stmt=$db->prepare('SELECT id FROM clientes WHERE email=:email LIMIT 1');
$stmt->execute([':email'=>$email]);
if($stmt->fetch()){ header('Location: registrar.html?erro=email_ja_cadastrado'); exit(); }

$cliente=new Cliente($nome,$email,$senha);
$stmt=$db->prepare('INSERT INTO clientes (nome,email,senha,role) VALUES (:nome,:email,:senha,\'user\')');
$stmt->execute([':nome'=>$nome,':email'=>$email,':senha'=>$cliente->getSenhaHash()]);
header('Location: login.html?cadastro=sucesso');
exit();
