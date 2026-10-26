# Efeitos 3D do Tripora

three.js puro (versão 0.170.0), carregado pelo CDN jsDelivr via importmap. Não precisa de npm nem de build.

| Arquivo | O que faz | Onde é usado |
|---|---|---|
| `hero3d.js` | Globo com os continentes em pontos, pinos nos destinos, rotas saindo de São Paulo e um aviãozinho | `index.php` (card do topo) |
| `cards-tilt.js` | Inclinação 3D (máx. 8°) com brilho que segue o cursor, em todo elemento `.card-tilt` | `index.php` (cards de destino e "Por que Tripora?") |
| `particulas.js` | Fundo com 1500 partículas que flutuam e reagem de leve ao mouse | `index.php`, `login.html`, `registrar.html` |
| `modelo.js` | Carrega um `.glb`, centraliza, ajusta a escala e deixa girar com o mouse/dedo | `login.html`, `registrar.html` |
| `util3d.js` | Funções comuns: checagem de WebGL, movimento reduzido, pausa fora da tela, redimensionamento | todos |
| `efeitos3d.css` | Estilos dos efeitos | páginas acima |
| `modelos/modelo.glb` | Avião low-poly nas cores do site | `login.html`, `registrar.html` |

## Como personalizar
- **Destinos e cores do globo:** topo de `hero3d.js`, nas constantes `CORES`, `ORIGEM` e `DESTINOS` (nome, latitude, longitude). Use `lado: 'esq'` ou `lado: 'dir'` para afastar etiquetas de cidades próximas.
- **Quantidade e cores das partículas:** topo de `particulas.js` (`QUANTIDADE`, `CORES`).
- **Trocar o modelo 3D:** substitua `modelos/modelo.glb`, ou aponte outro arquivo no atributo `data-modelo-3d="..."` da `<div class="modelo-3d">`. Há modelos gratuitos em Sketchfab e Poly Pizza.
- **Força da inclinação dos cards:** `MAX_GRAUS` em `cards-tilt.js`.

## Acessibilidade e desempenho
- Sem WebGL: o card do topo mantém a foto original, as partículas não aparecem e o modelo vira um ícone ✈.
- Com "reduzir movimento" ligado no sistema, rotações e flutuação param.
- As animações pausam quando saem da tela ou quando a aba fica oculta.
- O pixel ratio é limitado a 2.

## Voltar para versões anteriores
Há dois pontos de retorno guardados como branches no GitHub:
- `antes-3d`: o site original, sem nenhum efeito 3D;
- `antes-recursos`: com o primeiro 3D, mas sem os recursos de outubro (página do destino, cupons, cartão de embarque etc.).

```bash
git fetch origin
git reset --hard origin/antes-recursos && git push --force origin main   # ou origin/antes-3d
```

## Recursos adicionados depois (outubro/2026)

| Arquivo | O que faz |
|---|---|
| `globo3d.js` | Globo reutilizável (pinos, rotas, avião, clique nos pinos) usado em todas as páginas |
| `hero3d.js` | Globo da página inicial: tocar num pino abre um mini card com foto, preço, "Ver destino" e "Reservar" |
| `rota3d.js` | Mini globo com a rota São Paulo → destino (página do destino e reserva) |
| `mapa-viagens.js` | Globo de "Minhas viagens" (verde = pago, amarelo = aguardando) |
| `panorama.js` | Vista panorâmica da foto do destino (arrastar ou mover o celular) |
| `transicao.js` | Aviãozinho cruzando a tela ao clicar em "Reservar" |
| `particulas.js` | Agora muda as cores conforme o horário (dia, pôr do sol, noite) |

Arquivos fora desta pasta: `destino.php`, `comparar.php`, `embarque.php`, `favoritar.php`, `avaliar.php`, `aplicar_cupom.php`, `includes/Migracoes.php`, `includes/DestinoInfo.php` (coordenadas, moeda, fuso e melhor época de cada destino), `includes/layout.php`, `css/recursos.css` e `js/` (`ao-vivo.js`, `favoritos.js`, `comparar-selecao.js`, `reserva.js`, `embarque.js`, `barras3d.js`).

Voltar para antes destes recursos: branch `antes-recursos` no GitHub.
