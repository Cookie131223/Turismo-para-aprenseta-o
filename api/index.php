<?php
// Roteador da Vercel: deixa o servidor embutido do PHP servir as páginas e arquivos do site,
// como o "php -S" do Dockerfile, mas esconde arquivos que não devem ser baixados.
$caminho = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$bloqueado = preg_match('#(^|/)\.#', $caminho)                                   // .git, .env...
    || preg_match('#^/api/#', $caminho)
    || preg_match('#\.(sql|md|ini|example|lock|json)$#i', $caminho) && !preg_match('#^/assets/#', $caminho)
    || preg_match('#^/(Dockerfile|vercel\.json)$#i', $caminho);
if ($bloqueado) {
    http_response_code(404);
    echo 'Página não encontrada';
    return true;
}
return false;
