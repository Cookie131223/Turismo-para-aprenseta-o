<?php
// Atualiza o banco automaticamente (novas tabelas e colunas), sem precisar rodar SQL na mão.
// Roda uma vez por versão: a versão aplicada fica guardada na tabela tripora_meta.
class Migracoes {
    const VERSAO = 2;
    private static bool $verificado = false;

    public static function garantir(PDO $db): void {
        if (self::$verificado) return;
        self::$verificado = true;
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
        $sqlite = $driver === 'sqlite';
        $pgsql = $driver === 'pgsql';
        $db->exec('CREATE TABLE IF NOT EXISTS tripora_meta (chave VARCHAR(40) PRIMARY KEY, valor VARCHAR(255) NOT NULL)');
        $st = $db->query("SELECT valor FROM tripora_meta WHERE chave='schema'");
        $atual = (int)($st ? $st->fetchColumn() : 0);
        if ($atual >= self::VERSAO) return;

        $auto = $sqlite ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : ($pgsql ? 'SERIAL PRIMARY KEY' : 'INT AUTO_INCREMENT PRIMARY KEY');
        $ignore = $sqlite ? 'INSERT OR IGNORE' : ($pgsql ? 'INSERT' : 'INSERT IGNORE');
        $semDuplicar = $pgsql ? ' ON CONFLICT DO NOTHING' : '';

        // reservas: datas, viajantes, cupom e código do cartão de embarque
        self::coluna($db, $sqlite, 'reservas', 'data_ida', 'DATE NULL');
        self::coluna($db, $sqlite, 'reservas', 'data_volta', 'DATE NULL');
        self::coluna($db, $sqlite, 'reservas', 'viajantes', 'INT NOT NULL DEFAULT 1');
        self::coluna($db, $sqlite, 'reservas', 'cupom', 'VARCHAR(40) NULL');
        self::coluna($db, $sqlite, 'reservas', 'desconto', 'DECIMAL(10,2) NOT NULL DEFAULT 0');
        self::coluna($db, $sqlite, 'reservas', 'codigo', 'VARCHAR(12) NULL');
        // destinos: coordenadas opcionais (para destinos novos cadastrados pelo admin)
        self::coluna($db, $sqlite, 'destinos', 'lat', 'DECIMAL(9,6) NULL');
        self::coluna($db, $sqlite, 'destinos', 'lon', 'DECIMAL(9,6) NULL');

        $db->exec('CREATE TABLE IF NOT EXISTS favoritos (
            cliente_id INT NOT NULL,
            destino_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (cliente_id, destino_id))');
        $db->exec("CREATE TABLE IF NOT EXISTS avaliacoes (
            id $auto,
            cliente_id INT NOT NULL,
            destino_id INT NOT NULL,
            nota INT NOT NULL,
            comentario TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE (cliente_id, destino_id))");
        $db->exec('CREATE TABLE IF NOT EXISTS cupons (
            codigo VARCHAR(40) PRIMARY KEY,
            percentual INT NOT NULL,
            descricao VARCHAR(160) NOT NULL,
            ativo INT NOT NULL DEFAULT 1)');
        $db->exec("$ignore INTO cupons (codigo, percentual, descricao, ativo) VALUES
            ('TRIPORA10', 10, '10% de desconto em qualquer destino', 1),
            ('BEMVINDO15', 15, '15% de desconto de boas-vindas', 1),
            ('APRESENTACAO20', 20, '20% de desconto para a apresentação', 1)" . $semDuplicar);

        if ($atual === 0) $db->exec("INSERT INTO tripora_meta (chave, valor) VALUES ('schema', '" . self::VERSAO . "')");
        else $db->exec("UPDATE tripora_meta SET valor='" . self::VERSAO . "' WHERE chave='schema'");
    }

    private static function coluna(PDO $db, bool $sqlite, string $tabela, string $coluna, string $definicao): void {
        if ($sqlite) {
            $existe = false;
            foreach ($db->query("PRAGMA table_info($tabela)")->fetchAll(PDO::FETCH_ASSOC) as $c) if ($c['name'] === $coluna) $existe = true;
        } else {
            $atual = $db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql' ? 'current_schema()' : 'DATABASE()';
            $st = $db->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = $atual AND table_name = :t AND column_name = :c");
            $st->execute([':t' => $tabela, ':c' => $coluna]);
            $existe = (int)$st->fetchColumn() > 0;
        }
        if (!$existe) $db->exec("ALTER TABLE $tabela ADD COLUMN $coluna $definicao");
    }
}
