<?php
class Destino {
    public int $id;
    public string $nome;
    public string $pais;
    public string $descricao;
    public float $preco;
    public string $imagem;

    public function __construct(int $id, string $nome, string $pais, string $descricao, float $preco, string $imagem) {
        $this->id=$id; $this->nome=$nome; $this->pais=$pais; $this->descricao=$descricao; $this->preco=$preco; $this->imagem=$imagem;
    }

    public function mostrarCard(): string {
        $id=$this->id;
        $nome=htmlspecialchars($this->nome);
        $pais=htmlspecialchars($this->pais);
        $descricao=htmlspecialchars($this->descricao);
        $imagem=htmlspecialchars($this->imagem);
        $preco=number_format($this->preco,2,',','.');
        return "<article class='card'><img src='{$imagem}' alt='{$nome}' loading='lazy'><div class='card-body'><p class='eyebrow'>{$pais}</p><h3>{$nome}</h3><p class='muted'>{$descricao}</p><p class='price'>R$ {$preco}</p><div class='card-actions'><a class='btn' href='reserva.php?destino_id={$id}'>Reservar viagem</a></div></div></article>";
    }
}
