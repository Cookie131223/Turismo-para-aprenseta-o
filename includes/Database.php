<?php
class Database {
    private PDO $connection;
    // Uma conexão por requisição: as páginas e as sessões de login usam a mesma.
    private static ?PDO $compartilhada = null;

    public function __construct() {
        $this->connection = self::conexao();
    }

    public static function conexao(): PDO {
        if (self::$compartilhada) return self::$compartilhada;

        $url = getenv('MYSQL_PRIVATE_URL') ?: getenv('DATABASE_URL') ?: getenv('MYSQL_URL');
        $driver = 'mysql';

        if ($url) {
            $parts = parse_url($url);
            $scheme = strtolower($parts['scheme'] ?? 'mysql');
            // postgres:// ou postgresql:// (Supabase, Neon...) usa o driver do Postgres
            if (in_array($scheme, ['postgres', 'postgresql', 'pgsql'], true)) $driver = 'pgsql';
            $host = $parts['host'] ?? '127.0.0.1';
            $port = $parts['port'] ?? ($driver === 'pgsql' ? 5432 : 3306);
            $user = isset($parts['user']) ? urldecode($parts['user']) : 'root';
            $pass = isset($parts['pass']) ? urldecode($parts['pass']) : '';
            $name = isset($parts['path']) ? ltrim($parts['path'], '/') : 'railway';
        } else {
            // Railway variables take precedence over generic DB_* variables.
            $host = getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: getenv('DB_HOST') ?: '127.0.0.1';
            $port = getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: getenv('DB_PORT') ?: '3306';
            $name = getenv('MYSQL_DATABASE') ?: getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'railway';
            $user = getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: getenv('DB_USER') ?: 'root';
            $pass = getenv('MYSQL_ROOT_PASSWORD') ?: getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: getenv('DB_PASS') ?: '';
        }

        if ($driver === 'pgsql') {
            $schema = getenv('DB_SCHEMA') ?: '';
            parse_str($parts['query'] ?? '', $opcoes);
            $ssl = preg_replace('/[^a-z-]/', '', $opcoes['sslmode'] ?? 'prefer');
            $dsn = "pgsql:host={$host};port={$port};dbname={$name};sslmode={$ssl};connect_timeout=10";
        } else {
            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
        }

        $db = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);

        if ($driver === 'pgsql') {
            if ($schema !== '' && preg_match('/^[a-z_][a-z0-9_]*$/', $schema)) $db->exec("SET search_path TO {$schema}");
            $db->exec("SET TIME ZONE 'America/Sao_Paulo'");
        }

        // Cria automaticamente as tabelas/colunas dos recursos novos (favoritos, avaliações, cupons...).
        require_once __DIR__ . '/Migracoes.php';
        try { Migracoes::garantir($db); }
        catch (Throwable $e) { error_log('Tripora: falha ao atualizar o banco: ' . $e->getMessage()); }

        return self::$compartilhada = $db;
    }

    public function getConnection(): PDO {
        return $this->connection;
    }
}
