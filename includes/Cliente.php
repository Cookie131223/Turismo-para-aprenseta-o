<?php
class Cliente {
    private string $senhaHash;
    public function __construct(public string $nome, public string $email, string $senha) {
        $this->senhaHash=password_hash($senha,PASSWORD_DEFAULT);
    }
    public function getSenhaHash(): string { return $this->senhaHash; }
}
