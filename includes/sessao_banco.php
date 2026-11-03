<?php
// Guarda as sessões de login no banco em vez de arquivos.
// Na Vercel cada acesso pode cair numa máquina diferente, então arquivos de sessão se perdem.
// Carregado automaticamente antes de cada página pelo api/php.ini (só na Vercel).
require_once __DIR__ . '/Database.php';

if (!class_exists('SessaoBanco', false)) {
class SessaoBanco implements SessionHandlerInterface {
    private ?PDO $db = null;

    private function db(): PDO {
        return $this->db ??= Database::conexao();
    }

    public function open(string $path, string $name): bool { return true; }
    public function close(): bool { return true; }

    public function read(string $id): string|false {
        $st = $this->db()->prepare('SELECT dados FROM sessoes WHERE id = :id');
        $st->execute([':id' => $id]);
        $dados = $st->fetchColumn();
        return $dados === false ? '' : (string)$dados;
    }

    public function write(string $id, string $data): bool {
        $agora = time();
        if ($this->db()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            $sql = 'INSERT INTO sessoes (id, dados, atualizado) VALUES (:id, :d, :t)
                    ON CONFLICT (id) DO UPDATE SET dados = EXCLUDED.dados, atualizado = EXCLUDED.atualizado';
        } else {
            $sql = 'INSERT INTO sessoes (id, dados, atualizado) VALUES (:id, :d, :t)
                    ON DUPLICATE KEY UPDATE dados = VALUES(dados), atualizado = VALUES(atualizado)';
        }
        $this->db()->prepare($sql)->execute([':id' => $id, ':d' => $data, ':t' => $agora]);
        return true;
    }

    public function destroy(string $id): bool {
        $this->db()->prepare('DELETE FROM sessoes WHERE id = :id')->execute([':id' => $id]);
        return true;
    }

    public function gc(int $max_lifetime): int|false {
        $st = $this->db()->prepare('DELETE FROM sessoes WHERE atualizado < :t');
        $st->execute([':t' => time() - $max_lifetime]);
        return $st->rowCount();
    }
}
}

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', (string)(7 * 24 * 3600));
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_samesite', 'Lax');
    session_set_save_handler(new SessaoBanco(), true);
}
