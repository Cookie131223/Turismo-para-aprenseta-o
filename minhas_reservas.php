<?php
session_start(); // Inicia a sessão
require_once 'includes/Database.php'; // Inclui a classe de banco de dados

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html?erro=nao_logado"); // Redireciona se não estiver logado
    exit();
}

$cliente_id = $_SESSION['user_id']; // Obtém o ID do cliente logado
$cliente_name = htmlspecialchars($_SESSION['user_name']); // Obtém e sanitiza o nome do cliente

$database = new Database(); // Instancia a classe de banco de dados
$db = $database->getConnection(); // Obtém a conexão com o banco de dados

$reservas = []; // Array para armazenar as reservas
$query = "SELECT destino_nome, data_reserva, status FROM reservas WHERE cliente_id = :cliente_id ORDER BY data_reserva DESC"; // Query para buscar as reservas do cliente
$stmt = $db->prepare($query); // Prepara a query
$stmt->bindParam(':cliente_id', $cliente_id); // Associa o ID do cliente
$stmt->execute(); // Executa a query

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { // Percorre os resultados
    $reservas[] = $row; // Adiciona cada reserva ao array
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Reservas - Explorer Turismo</title>
    <link rel="stylesheet" href="Css/main.css">
    <style>
        /* Estilos para o avatar do usuário */
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
            vertical-align: middle;
        }
        /* Estilos para a mensagem de boas-vindas */
        .welcome-message {
            display: flex;
            align-items: center;
            color: white;
            margin-right: 1rem;
        }
        /* Contêiner principal das reservas */
        .reservas-container {
            padding: 2rem;
            max-width: 800px;
            margin: 2rem auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        /* Item individual da reserva */
        .reserva-item {
            border-bottom: 1px solid #eee;
            padding: 1rem 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        /* Remove a borda inferior do último item */
        .reserva-item:last-child {
            border-bottom: none;
        }
        /* Estilos para o título do destino na reserva */
        .reserva-item h3 {
            margin: 0;
            color: #0066cc;
        }
        /* Estilos para parágrafos dentro do item de reserva */
        .reserva-item p {
            margin: 0;
            color: #555;
            font-size: 0.9rem;
        }
        /* Mensagem quando não há reservas */
        .no-reservations {
            text-align: center;
            color: #777;
        }
    </style>
</head>
<body>
    <header>
        <nav style="display: flex; justify-content: space-between; align-items: center; padding: 1rem;">
            <div>
                <a href="index.php">Home</a>
                <a href="#">Destinos</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="minhas_reservas.php">Minhas Reservas</a>
                <?php endif; ?>
            </div>
            <div>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="welcome-message">
                        <img src="assets/avatar_padrao.png" alt="Avatar do Usuário" class="user-avatar">
                        Olá, <?= $cliente_name ?>!
                    </span>
                    <a href="logout.php">Sair</a>
                <?php else: ?>
                    <a href="login.html">Login</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main>
        <section class="reservas-container">
            <h2>Minhas Reservas</h2>
            <?php if (count($reservas) > 0): ?>
                <?php foreach ($reservas as $reserva): ?>
                    <div class="reserva-item">
                        <div>
                            <h3><?= htmlspecialchars($reserva['destino_nome']) ?></h3>
                            <p>Data da Reserva: <?= date('d/m/Y H:i', strtotime($reserva['data_reserva'])) ?></p>
                        </div>
                        <div>
                            <p>Status: <strong><?= htmlspecialchars(ucfirst($reserva['status'])) ?></strong></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-reservations">Você ainda não tem nenhuma reserva.</p>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 - Turismo</p>
    </footer>
</body>
</html>