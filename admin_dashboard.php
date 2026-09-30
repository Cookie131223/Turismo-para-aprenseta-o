<?php
session_start();
require_once 'includes/check_admin.php'; // Security check
require_once 'includes/Database.php';

$database = new Database();
$db = $database->getConnection();

// Fetch all destinations to manage them
$stmt_destinos = $db->query("SELECT * FROM destinos ORDER BY nome ASC");
$destinos = $stmt_destinos->fetchAll(PDO::FETCH_ASSOC);

// Fetch all reservations to monitor them
$stmt_reservas = $db->query("SELECT r.id, c.nome as cliente_nome, r.destino_nome, r.data_reserva, r.status FROM reservas r JOIN clientes c ON r.cliente_id = c.id ORDER BY r.data_reserva DESC");
$reservas = $stmt_reservas->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="Css/main.css">
    <link rel="stylesheet" href="Css/admin.css">
</head>
<body>
    <header>
        <nav style="display: flex; justify-content: space-between; align-items: center; padding: 1rem;">
            <a href="index.php">Ver Site</a>
            <a href="logout.php">Sair</a>
        </nav>
    </header>

    <main class="admin-container">
        <h1>Painel do Administrador</h1>

        <section class="admin-section">
            <h2>Gerenciar Destinos</h2>

            <form action="includes/admin_actions.php" method="POST" class="admin-form">
                <h3>Adicionar Novo Destino</h3>
                <input type="hidden" name="action" value="add_destino">
                <div class="form-group"><label>Nome</label><input type="text" name="nome" required></div>
                <div class="form-group"><label>País</label><input type="text" name="pais" required></div>
                <div class="form-group"><label>Descrição</label><textarea name="descricao" required></textarea></div>
                <div class="form-group"><label>Preço (ex: 4500.00)</label><input type="text" name="preco" required pattern="[0-9]+(\.[0-9]{2})?"></div>
                <div class="form-group"><label>URL da Imagem</label><input type="text" name="imagem" required></div>
                <button type="submit" class="btn">Adicionar Destino</button>
            </form>

            <div class="admin-table">
                <h3>Destinos Atuais</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Preço</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($destinos as $destino): ?>
                        <tr>
                            <td><?= htmlspecialchars($destino['nome']) ?></td>
                            <td>R$ <?= number_format($destino['preco'], 2, ',', '.') ?></td>
                            <td class="action-links">
                                <a href="includes/admin_actions.php?action=delete_destino&id=<?= $destino['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir?');">Excluir</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <hr>

        <section class="admin-section">
            <h2>Acompanhar Pedidos (Reservas)</h2>
            <div class="admin-table">
                <table>
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Destino</th>
                            <th>Data da Reserva</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservas as $reserva): ?>
                        <tr>
                            <td><?= htmlspecialchars($reserva['cliente_nome']) ?></td>
                            <td><?= htmlspecialchars($reserva['destino_nome']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($reserva['data_reserva'])) ?></td>
                            <td><?= htmlspecialchars(ucfirst($reserva['status'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>