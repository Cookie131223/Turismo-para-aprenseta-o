<?php
session_start();
require_once 'includes/Database.php';
require_once 'includes/Destino.php';

// --- INÍCIO DA MODIFICAÇÃO ---

// 1. Crie o seu destino de turismo pré-definido aqui
$destino_predefinido = new Destino(
    "Dubai", // Nome do destino
    "Emirados Árabes Unidos", // País ou região
    "Venha para Dubai", // Descrição
    5800.00, // Preço
    "https://imgs.search.brave.com/vEIikiuIXB-6oOWxHIQMEd7RC0hnt2D3Np8RXn8suXY/rs:fit:500:0:0:0/g:ce/aHR0cHM6Ly9tZWRp/YS5nZXR0eWltYWdl/cy5jb20vaWQvMTQ3/MTQ0MTM0OC9waG90/by9idXJqLWFsLWFy/YWItbHV4dXJ5LWJl/YWNoLXJlc29ydC1t/YWRpbmF0LWp1bWVp/cmFoLmpwZz9zPTYx/Mng2MTImdz0wJms9/MjAmYz1vRTlGWHlu/ZjdadEJySDlyTzMw/Z0lQLURHQlY1Yk10/WFdXQ3hSZVZIWG04/PQ" // URL da imagem
);

// --- FIM DA MODIFICAÇÃO ---


// O código para buscar os outros destinos do banco de dados continua o mesmo
$database = new Database();
$db = $database->getConnection();
$stmt = $db->query("SELECT * FROM destinos ORDER BY nome ASC");
$destinos_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$destinos = [];
foreach ($destinos_data as $data) {
    // Cria objetos Destino a partir dos dados do banco
    $destinos[] = new Destino($data['nome'], $data['pais'], $data['descricao'], $data['preco'], $data['imagem']);
}

// --- INÍCIO DA MODIFICAÇÃO ---

// 2. Adicione o destino pré-definido no início da lista de destinos
array_unshift($destinos, $destino_predefinido);

// --- FIM DA MODIFICAÇÃO ---

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Explorer Turismo</title>
    <link rel="stylesheet" href="Css/main.css">
    <style>.welcome-message { display: flex; align-items: center; color: white; margin-right: 1rem; }</style>
</head>
<body>
    <header>
        <nav style="display: flex; justify-content: space-between; align-items: center; padding: 1rem;">
           <div>
                <a href="index.php">Home</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="minhas_reservas.php">Minhas Reservas</a>
                <?php endif; ?>
                <?php // Link para o painel de admin
                if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <a href="admin_dashboard.php" style="color: #FFD700; font-weight: bold;">Painel Admin</a>
                <?php endif; ?>
            </div>
            <div>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="welcome-message">Olá, <?= htmlspecialchars($_SESSION['user_name']) ?>!</span>
                    <a href="logout.php">Sair</a>
                <?php else: ?>
                    <a href="login.html">Login</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main>
        <section class="hero">
            <h1>Descubra o mundo conosco</h1>
            <p>As melhores experiências de viagem ao seu alcance</p>
            <a href="registrar.html" class="btn">Cadastre-se</a>
        </section>

        <section class="destinos">
            <h2>Destinos em Destaque</h2>
            <div class="grid-destinos">
                <?php if (empty($destinos)): ?>
                    <p style="text-align: center; width: 100%;">Nenhum destino cadastrado no momento.</p>
                <?php else: ?>
                    <?php foreach ($destinos as $destino): ?>
                        <?= $destino->mostrarCard() ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 - Turismo</p>
        <p>Equipe de desenvolvimento:</p>
        <p> Guilherme Lima Zamberse da Silva - 318059 <br>
            Breno Martinho Denis - 318086 <br>
            Pedro Lucas Possidonio dos Santos - 317926 <br>
            Eduardo Francisco Bernardes da Silva - 317967 <br>
        </p>
    </footer>
</body>
</html>
