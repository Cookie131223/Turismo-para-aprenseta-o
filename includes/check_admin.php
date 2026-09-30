<?php
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? 'user') !== 'admin') {
    header('Location: login.html?erro=acesso_negado');
    exit();
}
