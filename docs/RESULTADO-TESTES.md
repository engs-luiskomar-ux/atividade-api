# Resultado dos testes

Verificação local realizada em 17/10/2026 (horário de São Paulo).

## Laravel

- PHP 8.4.25, Laravel 13.35.0 e SQLite.
- `php artisan test`: 13 testes aprovados, 96 assertions.
- `php vendor/bin/pint --test`: formatação aprovada.
- `composer validate --no-check-publish`: composer.json válido.

Os testes do Laravel usam SQLite em memória e simulam respostas externas.

## Coleção Postman executada com Newman

- Requisições executadas: 16.
- Assertions executadas: 32.
- Falhas: 0.
- Endereço local: http://127.0.0.1:8000.
- As consultas de consumo externo acessaram a DummyJSON real pela aplicação Laravel.
- Execução pela linha de comando; sem captura ou execução da interface gráfica do Postman.

| Requisição | HTTP recebido | Testes |
| --- | --- | --- |
| 01 - Listar produtos locais | 200 | 2/2 aprovados |
| 02 - Cadastrar produto | 201 | 2/2 aprovados |
| 03 - Consultar produto cadastrado | 200 | 2/2 aprovados |
| 04 - Atualizar produto com PUT | 200 | 2/2 aprovados |
| 05 - Atualizar quantidade com PATCH | 200 | 2/2 aprovados |
| 06 - Rejeitar preço e estoque negativos | 422 | 2/2 aprovados |
| 07 - Rejeitar campos obrigatórios ausentes | 422 | 2/2 aprovados |
| 08 - Rejeitar PUT incompleto | 422 | 2/2 aprovados |
| 09 - Cadastrar com estoque padrão | 201 | 2/2 aprovados |
| 10 - Excluir amostra de teste | 204 | 2/2 aprovados |
| 11 - Excluir produto de teste | 204 | 2/2 aprovados |
| 12 - Confirmar exclusão | 404 | 2/2 aprovados |
| 13 - Consumir lista real da DummyJSON | 200 | 2/2 aprovados |
| 14 - Consumir produto externo | 200 | 2/2 aprovados |
| 15 - Consultar produto externo inexistente | 404 | 2/2 aprovados |
| 16 - Rejeitar paginação externa inválida | 422 | 2/2 aprovados |

A coleção cria e exclui os próprios produtos de teste. Os três produtos do seeder foram preservados.

Para repetir, siga o README e execute a coleção inteira com o servidor ativo e conexão à internet.
O relatório detalhado `postman/relatorio-newman.json` é gerado localmente e ignorado pelo Git.
