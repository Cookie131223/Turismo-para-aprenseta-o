<?php
session_start(); // Inicia a sessão para redirecionar após o registro
require_once 'includes/Database.php'; // Inclui a classe de banco de dados
require_once 'includes/Cliente.php'; // Inclui a classe Cliente

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    // Validação básica (você pode adicionar validações mais robustas)
    if (empty($nome) || empty($email) || empty($senha)) {
        header("Location: registrar.html?erro=campos_vazios");
        exit();
    }

    $database = new Database();
    $db = $database->getConnection();

    // Verificar se o e-mail já está em uso
    $query_check_email = "SELECT id FROM clientes WHERE email = :email LIMIT 0,1";
    $stmt_check_email = $db->prepare($query_check_email);
    $stmt_check_email->bindParam(':email', $email);
    $stmt_check_email->execute();

    if ($stmt_check_email->rowCount() > 0) {
        header("Location: registrar.html?erro=email_ja_cadastrado");
        exit();
    }

    // Criar uma instância da classe Cliente
    // Nota: CPF e Telefone não estão no formulário de registro atual, então usei valores padrão.
    // Você precisaria adicioná-los ao formulário se quisesse coletá-los.
    $novoCliente = new Cliente($nome, $email, $senha, '00000000000', '00000000000');

    // Inserir o novo cliente no banco de dados
    $query_insert = "INSERT INTO clientes (nome, email, senha) VALUES (:nome, :email, :senha)";
    $stmt_insert = $db->prepare($query_insert);

    // Bind dos parâmetros
    $stmt_insert->bindParam(':nome', $nome);
    $stmt_insert->bindParam(':email', $email);
    
// A senha é hashada dentro da classe Cliente
// Usando o método getSenhaHash() da classe Cliente
$hashedSenha = $novoCliente->getSenhaHash();
$stmt_insert->bindParam(':senha', $hashedSenha);


    if ($stmt_insert->execute()) {
        // Registro bem-sucedido, redirecionar para a página de login
        header("Location: login.html?cadastro=sucesso");
        exit();
    } else {
        header("Location: registrar.html?erro=falha_cadastro");
        exit();
    }
} else {
    // Redireciona se não for um POST
    header("Location: registrar.html");
    exit();
}
?>