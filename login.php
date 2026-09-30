<?php
session_start();
require_once 'includes/Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: login.html'); exit(); }

$email=trim($_POST['email']??'');
$senha=$_POST['senha']??'';
if(!filter_var($email,FILTER_VALIDATE_EMAIL) || $senha===''){ header('Location: login.html?erro=dados_invalidos'); exit(); }

$db=(new Database())->getConnection();
$stmt=$db->prepare('SELECT id,nome,email,senha,role FROM clientes WHERE email=:email LIMIT 1');
$stmt->execute([':email'=>$email]);
$user=$stmt->fetch();

if($user && password_verify($senha,$user['senha'])){
    session_regenerate_id(true);
    $_SESSION['user_id']=$user['id'];
    $_SESSION['user_name']=$user['nome'];
    $_SESSION['user_role']=$user['role'];
    header('Location: '.($user['role']==='admin'?'admin_dashboard.php':'index.php'));
    exit();
}
header('Location: login.html?erro=credenciais_invalidas');
exit();
