<?php
// Defina a senha que você quer para o admin
$senhaPlana = '123';

// Gera o hash seguro usando o mesmo método do seu sistema
$hashSenha = password_hash($senhaPlana, PASSWORD_BCRYPT);

echo "Use este hash no seu comando SQL: <br><br>";
echo $hashSenha;
?>