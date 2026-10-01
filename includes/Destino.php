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
        return "<article class='card destination-card' data-search='{$nome} {$pais}'><div class='card-image-wrap'><img src='{$imagem}' alt='{$nome}' loading='lazy'><span class='card-chip'>Pacote disponível</span></div><div class='card-body'><p class='eyebrow'>{$pais}</p><h3>{$nome}</h3><p class='muted card-description'>{$descricao}</p><div class='card-footer'><div><span class='price-label'>a partir de</span><p class='price'>R$ {$preco}</p></div><div class='card-actions'><a class='btn' href='reserva.php?destino_id={$id}'>Reservar</a></div></div></div></article>";
    }
}
