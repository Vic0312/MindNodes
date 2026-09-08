# Catalogo e inventario — etapa 7

`Item` e `Inventario` usam `Conexao::obter()` e aceitam a mesma conexao mysqli por injecao. Nao existem endpoints ou Views novos. `InventarioController` oferece somente leitura; em contexto HTTP, o chamador deve determinar o usuario por `$_SESSION['usuario_id']`, nunca por um ID livre do navegador.

## Contratos

- `Item::listarTodos()`, `listarAtivos()`, `buscarPorId($idItem)`, `listarPorCategoria($categoria)` e `buscarPadroes()`. Busca inexistente retorna null. Padroes sao identificados por nome e categoria; ausencia ou ambiguidade gera `UnexpectedValueException`. Inatividade nao elimina propriedade nem impede concessao interna.
- `Item::validarId()` aceita inteiro positivo ou string decimal canonica ate 2147483647. `validarCategoria()` aceita somente cabelo, rosto, roupa e acessorio. Entradas invalidas geram `InvalidArgumentException`.
- `Inventario::listarDoUsuario($idUsuario)` e `listarPorCategoria($idUsuario, $categoria)` retornam todos os campos do item mais data_compra, inclusive itens inativos. `possuiItem($idUsuario, $idItem)` retorna booleano; item inexistente retorna false. Usuario inexistente gera `OutOfBoundsException` nas quatro operacoes.
- `Inventario::adicionarItem($idUsuario, $idItem)` e exclusivamente interna. Item inexistente gera `OutOfBoundsException`. Retorna true se o vinculo foi criado ou ja existia, preservando a data original. Usa UNIQUE e upsert, inclusive sob concorrencia; nao usa INSERT IGNORE nem oculta erros reais. Nao abre ou confirma transacao: participa da transacao do chamador quando houver.
- `InventarioController` oferece `listarDoUsuario`, `listarPorCategoria` e `possuiItem`, com os mesmos argumentos. Nao existe metodo de concessao no controller. ItemController nao foi necessario.

`Usuario::cadastrar()` abre uma transacao, localiza os tres padroes, insere usuario, concede os tres itens pela mesma conexao e confirma. Qualquer falha reverte tudo. Transacao externa ou autocommit desativado sao rejeitados antes de iniciar o cadastro, evitando commits implicitos. `AuthController` preserva validacoes e password_hash e transforma falhas de persistencia/configuracao em erro `banco`, registrando a causa no log. Nao cria avatar.

## Banco existente

O dump preserva os registros e acrescenta `UNIQUE KEY uk_usuario_item (id_usuario, id_item)`. Seus seis vinculos iniciais nao possuem duplicatas. Importar o dump em banco vazio cria a constraint; editar o dump nao atualiza automaticamente bancos existentes.

Para uma instalacao que ja possui as tabelas, verificar primeiro:

```sql
SELECT id_usuario, id_item, COUNT(*) AS total
FROM usuario_item
GROUP BY id_usuario, id_item
HAVING COUNT(*) > 1;
SHOW INDEX FROM usuario_item;
```

Somente se nao houver duplicatas e nao existir indice UNIQUE equivalente:

```sql
ALTER TABLE usuario_item
ADD UNIQUE KEY uk_usuario_item (id_usuario, id_item);
```

Se houver duplicatas, interromper a atualizacao e revisar os registros; nao apagar dados automaticamente. O ALTER tambem rejeita duplicatas inseridas entre a verificacao e sua execucao.

Na verificacao deste checkout, o servidor local respondeu que `mindnode.item` nao existe. O banco real nao foi alterado; sua estrutura precisa ser atualizada antes de usar esta etapa. Nao reimportar o dump indiscriminadamente sobre dados existentes.

Os sete PNG oficiais existem. Os seis caminhos de item no dump ainda sao antigos (`img/avatar/cabelo-padrao.png`, `rosto-padrao.png`, `roupa-padrao.png`, `bone-fifo.png`, `oculos-debug.png`, `camiseta-stack.png`, todos sob img/avatar/). Foram preservados conforme escopo. Os arquivos reais ficam nas subpastas cabelo, rosto e roupa, com underscore nos nomes. Item devolve imagem exatamente como armazenada.

## Testes

Executar na raiz com PHP CLI/mysqli e MariaDB local:

```text
C:/xampp/php/php.exe tests/inventario.php
C:/xampp/php/php.exe tests/moedas.php
```

Ambos criam bancos temporarios aleatorios, importam o dump, injetam a conexao e removem somente seus bancos em finally. Aceitam MINDNODES_TEST_USER, MINDNODES_TEST_PASSWORD e MINDNODES_TEST_PORT. Nao acessam mindnode. As falhas de cadastro simuladas produzem mensagens esperadas no log.

A suite de inventario cobre os 15 cenarios obrigatorios, entradas invalidas, campos completos, inativos, padrao ausente/ambiguo, rollback externo, duas conexoes concorrentes e preservacao de moedas/avatar legado. A suite de moedas cobre cadastro, login, logout, recuperacao/redefinicao, leitura/edicao de perfil, quiz, desempenho e moedas. Sao testes de integracao via models/controllers, sem automacao visual de navegador.

Nao foram implementados Loja, compra, Avatar funcional, AvatarController, avatar.php, telas ou efeitos de habilidades.
