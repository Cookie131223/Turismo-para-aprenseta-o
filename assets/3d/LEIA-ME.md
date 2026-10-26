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

## Voltar para a versão sem 3D
A versão anterior está marcada com a tag `antes-3d`:

```bash
git revert --no-edit antes-3d..HEAD   # desfaz os commits do 3D e mantém o histórico (recomendado)
# ou, apagando o 3D do histórico:
git reset --hard antes-3d && git push --force
```
