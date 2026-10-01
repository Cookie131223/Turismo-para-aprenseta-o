<?php
class Database {
    private PDO $connection;

    public function __construct() {
        $url = getenv('MYSQL_PRIVATE_URL') ?: getenv('DATABASE_URL') ?: getenv('MYSQL_URL');

        if ($url) {
            $parts = parse_url($url);
            $host = $parts['host'] ?? '127.0.0.1';
            $port = $parts['port'] ?? 3306;
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

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
        $this->connection = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
    }

    public function getConnection(): PDO {
        return $this->connection;
    }
}
