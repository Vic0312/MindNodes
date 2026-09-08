# Sistema central de moedas — etapa 6

`model/Moeda.php` usa `Conexao::obter()` por padrao e permite injetar a mesma conexao mysqli utilizada nos testes. Todo modulo futuro deve alterar saldo exclusivamente por essa camada, nunca por UPDATE direto em `usuario.moedas`.

`controller/MoedaController.php` oferece `obterSaldo($idUsuario)`, `listarHistorico($idUsuario, $limite = null)`, `creditar($idUsuario, $valor, $origem, $descricao = null)` e `debitar(...)`. O model oferece os mesmos metodos publicos. Credito/debito retornam o saldo apos COMMIT; historico retorna um array, vazio quando nao ha movimentacoes. Nao existem novas rotas ou processamento GET/POST. O chamador PHP deve autorizar a operacao e definir os valores no servidor; esses metodos nao sao endpoints nem devem ser expostos por despacho dinamico.

IDs, valores e limites aceitam inteiros positivos ou strings decimais canonicas, ate 2147483647. Origens: `quiz`, `loja`, `bonus`. Descricao aceita null ou UTF-8 com ate 255 caracteres, remove espacos externos e rejeita controles invalidos; vazio vira null. Texto permanece texto: futuras views devem escapar a saida HTML.

Falhas lancam excecoes: `InvalidArgumentException` para entradas invalidas, `OutOfBoundsException` para usuario inexistente, `DomainException` para saldo insuficiente, `OverflowException` para excesso de saldo, `UnexpectedValueException` para saldo negativo preexistente e excecoes de banco/`RuntimeException` para falhas de persistencia. Nao se deve converter falha de consulta em saldo zero.

Cada movimentacao abre transacao, bloqueia o usuario com SELECT FOR UPDATE, valida o saldo, atualiza e registra o historico antes do COMMIT. Qualquer falha reverte a operacao. As tabelas devem continuar InnoDB, conforme mindnode.sql. Transacoes externas ou autocommit desativado sao rejeitados com `LogicException` antes de iniciar outra transacao; nao ha suporte a composicao transacional nesta etapa. Historico ordena por data decrescente e ID decrescente para desempate, com LIMIT parametrizado.

## Testes

Execute na raiz: `php tests/moedas.php` (PHP CLI com mysqli, MySQL/MariaDB local e permissao para criar/remover banco e trigger de teste). O script usa 127.0.0.1:3306, root sem senha por padrao; aceita `MINDNODES_TEST_USER`, `MINDNODES_TEST_PASSWORD` e `MINDNODES_TEST_PORT`.

Cria um banco `mindnodes_test_moedas_<hex aleatorio>`, importa mindnode.sql nele, injeta a conexao nos models e remove apenas esse banco em finally. Nao acessa o banco real mindnode. O teste recusa execucao HTTP. Apenas DDL fixa de triggers usa query, pois MariaDB nao suporta essas instrucoes no protocolo preparado.

Cobertura: 13 cenarios solicitados, entradas invalidas, overflow, transacao externa, rollback de credito e debito por trigger que falha no INSERT, ordenacao por data/desempate e dois processos concorrentes com conexoes independentes. Regressao via controllers/models: cadastro, login, logout, recuperacao/redefinicao, leitura/edicao de perfil, resposta de quiz e desempenho; nao inclui automacao visual de navegador. Quiz e perfil sao verificados sem alterar moedas.

Loja, recompensas de quiz, avatar, inventario e exibicao visual de saldo nao foram implementados. O uso das origens quiz/loja nos testes valida somente a compatibilidade do enum.
