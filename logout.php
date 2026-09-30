<?php
session_start(); // Inicia a sessão

// Destruir todas as variáveis de sessão
$_SESSION = array();

// Se for preciso destruir a sessão, também deleta o cookie de sessão.
// Nota: Isso irá destruir a sessão, e não apenas os dados da sessão!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destrói a sessão
session_destroy();

// Redireciona para a página inicial ou de login
header("Location: index.php");
exit();
?>