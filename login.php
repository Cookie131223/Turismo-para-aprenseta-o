<?php
session_start();
require_once 'includes/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        header("Location: login.html?erro=campos_vazios");
        exit();
    }

    $database = new Database();
    $db = $database->getConnection();

    // Busca o usuário e sua função ('role')
    $query = "SELECT id, nome, email, senha, role FROM clientes WHERE email = :email LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifica a senha usando o hash do banco de dados
        if (password_verify($senha, $row['senha'])) {
            // Armazena os dados do usuário na sessão
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['nome'];
            $_SESSION['user_role'] = $row['role']; // Armazena a função (admin/user)

            // Redireciona o admin para o painel, e o usuário comum para a home
            if ($row['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();
        }
    }
    
    // Se o email ou senha estiverem incorretos, retorna um erro genérico
    header("Location: login.html?erro=credenciais_invalidas");
    exit();
}
?>