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
