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
- Docker pronto para deploy

## Estrutura
Os diretórios e referências usam nomes consistentes em minúsculo, como `css/` e `includes/`, para evitar problemas em servidores Linux case-sensitive.

## Banco de dados
Importe `schema.sql` em um banco MySQL.

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
