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

    // $extra (opcional): favorito (bool), media (float), qtd (int), lat/lon (clima ao vivo)
    public function mostrarCard(array $extra = []): string {
        $id=$this->id;
        $nome=htmlspecialchars($this->nome);
        $pais=htmlspecialchars($this->pais);
        $descricao=htmlspecialchars($this->descricao);
        $imagem=htmlspecialchars($this->imagem);
        $preco=number_format($this->preco,2,',','.');
        $fav = !empty($extra['favorito']);
        $favAttr = $fav ? " ativo' aria-pressed='true" : "' aria-pressed='false";
        $clima = (isset($extra['lat'], $extra['lon']) && $extra['lat'] !== null)
            ? "<span class='card-clima' data-clima-lat='".htmlspecialchars((string)$extra['lat'])."' data-clima-lon='".htmlspecialchars((string)$extra['lon'])."' data-clima-curto='1' data-clima-ocultar='1' hidden></span>" : '';
        $qtd = (int)($extra['qtd'] ?? 0);
        if ($qtd > 0) {
            $media = (float)$extra['media'];
            $cheias = (int)round($media);
            $estrelas = "<p class='card-estrelas' title='".number_format($media,1,',','')." de 5'>".str_repeat('★',$cheias).str_repeat('☆',5-$cheias)." <span>".number_format($media,1,',','')." ($qtd)</span></p>";
        } else {
            $estrelas = "<p class='card-estrelas vazio'>☆☆☆☆☆ <span>sem avaliações</span></p>";
        }
        return "<article class='card destination-card card-tilt' data-search='{$nome} {$pais}' data-id='{$id}'>"
            . "<div class='card-image-wrap'><a href='destino.php?id={$id}' tabindex='-1' aria-hidden='true'><img src='{$imagem}' alt='{$nome}' loading='lazy'></a><span class='card-chip'>Pacote disponível</span>{$clima}"
            . "<button type='button' class='fav-btn{$favAttr}' data-destino='{$id}' aria-label='Favoritar {$nome}'>♥</button></div>"
            . "<div class='card-body'><p class='eyebrow'>{$pais}</p><h3><a href='destino.php?id={$id}'>{$nome}</a></h3>{$estrelas}<p class='muted card-description'>{$descricao}</p>"
            . "<div class='card-footer'><div><span class='price-label'>a partir de</span><p class='price'>R$ {$preco}</p></div>"
            . "<div class='card-actions'><a class='btn secondary small' href='destino.php?id={$id}'>Detalhes</a><a class='btn' href='reserva.php?destino_id={$id}'>Reservar</a></div></div>"
            . "<label class='comparar-check'><input type='checkbox' data-comparar='{$id}' data-nome='{$nome}'> Comparar</label>"
            . "</div></article>";
    }
}
