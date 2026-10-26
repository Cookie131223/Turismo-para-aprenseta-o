# Explorer Turismo

Sistema acadêmico de turismo em **PHP + MySQL** preparado para apresentação.

## Funcionalidades
- Cadastro e login com senha protegida por hash
- Listagem de destinos
- Reserva de viagens
- Checkout/pagamento simulado (PIX, cartão ou boleto)
- Área "Minhas viagens" para acompanhar e cancelar reservas
- Painel administrativo para destinos e status de reservas
- Interface responsiva
- Configuração por variáveis de ambiente
- Globo 3D interativo na página inicial (toque num pino para ver o destino)
- Página de cada destino com vista panorâmica, rota no globo, clima e hora local, conversor de moeda e avaliações
- Reserva com calendário de ida/volta, número de viajantes e total calculado na hora
- Cupons de desconto no pagamento (TRIPORA10, BEMVINDO15, APRESENTACAO20)
- Cartão de embarque 3D após o pagamento, com download em imagem
- "Minhas viagens" com globo das viagens, países, quilômetros e favoritos
- Comparação de até 3 destinos lado a lado, com gráficos em 3D
- Painel do admin com gráficos (2D/3D)
- Efeitos: cards que inclinam com o mouse, partículas que mudam com o horário e transição de avião
- Docker pronto para deploy

## Estrutura
Os diretórios e referências usam nomes consistentes em minúsculo, como `css/` e `includes/`, para evitar problemas em servidores Linux case-sensitive.

## Banco de dados
Importe `schema.sql` em um banco MySQL. Em bancos já existentes, as tabelas e colunas novas (favoritos, avaliações, cupons, datas da viagem) são criadas automaticamente no primeiro acesso, por `includes/Migracoes.php`.

Clima (Open-Meteo) e câmbio (open.er-api.com) são consultados direto do navegador, sem chave de API.

Depois configure:
```
DB_HOST=seu-host
DB_PORT=3306
DB_NAME=turismo
DB_USER=seu-usuario
DB_PASS=sua-senha
```

## Rodando localmente
Com PHP 8+ e MySQL:
```bash
php -S localhost:8000
```

## Docker
```bash
docker build -t explorer-turismo .
docker run -p 8080:80 --env-file .env explorer-turismo
```

## Deploy
O projeto não é um site estático como um portfólio HTML. Ele precisa de um servidor PHP e um banco MySQL. O `Dockerfile` foi incluído justamente para facilitar o deploy em plataformas que aceitam containers.

> O pagamento implementado é **demonstrativo**, adequado para apresentação acadêmica. Nenhuma cobrança real é processada.


## Admin para apresentação
1. Crie uma conta normalmente pelo site.
2. No MySQL, execute:
```sql
UPDATE clientes SET role = 'admin' WHERE email = 'SEU_EMAIL';
```
3. Saia e entre novamente. O link **Admin** aparecerá no menu.

## Colocar online no Railway
O Railway aceita deploy direto do GitHub e também oferece MySQL. O projeto já possui `Dockerfile`.

1. Crie um projeto no Railway e conecte este repositório.
2. Adicione um serviço MySQL ao mesmo projeto.
3. No serviço da aplicação, referencie as variáveis do MySQL como `MYSQLHOST`, `MYSQLPORT`, `MYSQLUSER`, `MYSQLPASSWORD` e `MYSQLDATABASE`.
4. Importe `schema.sql` no banco.
5. Gere um domínio público para o serviço web.

A aplicação também aceita `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` e `DB_PASS`, caso prefira outro provedor.
