<?php
session_start(); // Inicia a sessão
require_once 'includes/Database.php'; // Inclui a classe de banco de dados

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html?erro=nao_logado"); // Redireciona se não estiver logado
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['destino'])) { // Verifica se a requisição é GET e se o destino foi passado
    $destino_nome = htmlspecialchars($_GET['destino']); // Obtém e sanitiza o nome do destino
    $cliente_id = $_SESSION['user_id']; // Obtém o ID do cliente logado

    $database = new Database(); // Instancia a classe de banco de dados
    $db = $database->getConnection(); // Obtém a conexão com o banco de dados

    // Inserir a reserva no banco de dados
    $query = "INSERT INTO reservas (cliente_id, destino_nome) VALUES (:cliente_id, :destino_nome)";
    $stmt = $db->prepare($query); // Prepara a query
    $stmt->bindParam(':cliente_id', $cliente_id); // Associa o ID do cliente
    $stmt->bindParam(':destino_nome', $destino_nome); // Associa o nome do destino

    if ($stmt->execute()) { // Executa a query
        header("Location: minhas_reservas.php?sucesso=reserva_realizada"); // Redireciona para a página de minhas reservas com mensagem de sucesso
        exit();
    } else {
        header("Location: index.php?erro=falha_reserva"); // Redireciona para a página inicial com mensagem de erro
        exit();
    }
} else {
    header("Location: index.php"); // Redireciona se a requisição não for válida
    exit();
}
?>