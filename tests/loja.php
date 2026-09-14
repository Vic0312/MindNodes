<?php
if (PHP_SAPI !== 'cli') exit(1);
require_once __DIR__ . '/../controller/LojaController.php';
require_once __DIR__ . '/../model/Avatar.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
function dbLoja($banco = '') {
    $db = new mysqli('127.0.0.1', getenv('MINDNODES_TEST_USER') ?: 'root', getenv('MINDNODES_TEST_PASSWORD') ?: '', $banco, (int) (getenv('MINDNODES_TEST_PORT') ?: 3306));
    $db->set_charset('utf8mb4');
    return $db;
}
function qLoja($db, $sql, $tipos = '', ...$valores) {
    $stmt = $db->prepare($sql);
    try { if ($tipos) $stmt->bind_param($tipos, ...$valores); $stmt->execute(); return $stmt->field_count ? $stmt->get_result()->fetch_all(MYSQLI_ASSOC) : []; }
    finally { $stmt->close(); }
}
function okLoja($condicao, $nome) { if (!$condicao) throw new RuntimeException('FALHOU: ' . $nome); echo "OK: $nome\n"; }
function negarLoja($acao, $classe, $nome) {
    try { $acao(); } catch (Throwable $erro) { okLoja($erro instanceof $classe, $nome); return; }
    throw new RuntimeException('Nao rejeitou: ' . $nome);
}
if (($argv[1] ?? '') === 'worker') {
    if (!preg_match('/^mindnodes_test_loja_[a-f0-9]{16}$/D', $argv[2] ?? '')) exit(2);
    $db = dbLoja($argv[2]);
    echo "pronto\n"; fflush(STDOUT);
    fgets(STDIN);
    try { (new Loja($db))->comprar(2, 4); echo 'comprou'; }
    catch (ItemJaPossuidoException $erro) { echo 'duplicado'; }
    exit;
}
$db = dbLoja();
$banco = 'mindnodes_test_loja_' . bin2hex(random_bytes(8));
$criado = false;
try {
    qLoja($db, 'CREATE DATABASE `' . $banco . '` CHARACTER SET utf8mb4');
    $criado = true;
    $db->select_db($banco);
    $db->multi_query(file_get_contents(__DIR__ . '/../mindnode.sql'));
    do { if ($r = $db->store_result()) $r->free(); } while ($db->more_results() && $db->next_result());
    $loja = new Loja($db);
    $moeda = new Moeda($db);
    $inventario = new Inventario($db);
    $dados = $loja->carregarDados(1);
    okLoja($dados['saldo'] === 0 && count($dados['itens']) === 3 && !in_array(1, array_column($dados['itens'], 'id_item')), 'catalogo pago e saldo');
    negarLoja(fn() => $loja->comprar(1, 4), DomainException::class, 'saldo insuficiente');
    okLoja($moeda->obterSaldo(1) === 0 && !$inventario->possuiItem(1, 4) && $moeda->listarHistorico(1) === [], 'insuficiencia nao altera dados');
    $moeda->creditar(1, 200, 'bonus', 'Credito de teste');
    $avatarAntes = (new Avatar($db))->buscarDoUsuario(1);
    $comprado = $loja->comprar(1, 4);
    $historico = $moeda->listarHistorico(1);
    okLoja($comprado['nome'] === 'Boné FIFO' && $moeda->obterSaldo(1) === 100 && $inventario->possuiItem(1, 4), 'compra entrega item e debita cem');
    okLoja($historico[0]['tipo'] === 'debito' && (int) $historico[0]['valor'] === 100 && $historico[0]['origem'] === 'loja' && $historico[0]['descricao'] === 'Compra: Boné FIFO', 'historico de compra');
    okLoja((new Avatar($db))->buscarDoUsuario(1)['cabelo']['id_item'] === $avatarAntes['cabelo']['id_item'] && count($inventario->listarDoUsuario(1)) === 4, 'inventario atualizado sem equipar');
    negarLoja(fn() => $loja->comprar(1, 4), ItemJaPossuidoException::class, 'compra duplicada');
    okLoja($moeda->obterSaldo(1) === 100 && count($moeda->listarHistorico(1)) === 2, 'duplicada sem novo debito');
    negarLoja(fn() => $loja->comprar(1, 2147483647), ItemIndisponivelException::class, 'item inexistente');
    qLoja($db, 'UPDATE item SET ativo = 0 WHERE id_item = ?', 'i', 5);
    negarLoja(fn() => $loja->comprar(1, 5), ItemIndisponivelException::class, 'item inativo');
    okLoja(count($loja->carregarDados(1)['itens']) === 2, 'inativo fora do catalogo');
    qLoja($db, 'UPDATE item SET ativo = 1 WHERE id_item = ?', 'i', 5);
    $moeda->creditar(1, 100, 'bonus', 'Credito para falha');
    $db->query("CREATE TRIGGER falha_entrega BEFORE INSERT ON usuario_item FOR EACH ROW BEGIN IF NEW.id_item = 5 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'falha simulada'; END IF; END");
    negarLoja(fn() => $loja->comprar(1, 5), mysqli_sql_exception::class, 'falha apos debito');
    okLoja($moeda->obterSaldo(1) === 200 && !$inventario->possuiItem(1, 5) && count($moeda->listarHistorico(1)) === 3, 'rollback integral');
    $db->query('DROP TRIGGER falha_entrega');
    negarLoja(fn() => $loja->comprar(2147483647, 4), OutOfBoundsException::class, 'usuario inexistente');
    negarLoja(fn() => $loja->comprar(1, '4 OR 1=1'), InvalidArgumentException::class, 'id adulterado');
    $db->begin_transaction();
    negarLoja(fn() => $loja->comprar(1, 5), LogicException::class, 'transacao externa preservada');
    $db->rollback();
    $moeda->creditar(2, 200, 'bonus', 'Credito para concorrencia');
    $trabalhos = [];
    for ($i = 0; $i < 2; $i++) {
        $proc = proc_open([PHP_BINARY, __FILE__, 'worker', $banco], [0 => ['pipe','r'], 1 => ['pipe','w'], 2 => ['pipe','w']], $pipes);
        if (!is_resource($proc)) throw new RuntimeException('Worker nao iniciou.');
        okLoja(trim(fgets($pipes[1])) === 'pronto', 'worker pronto');
        $trabalhos[] = [$proc, $pipes];
    }
    foreach ($trabalhos as [$proc, $pipes]) { fwrite($pipes[0], "ir\n"); fclose($pipes[0]); }
    $saidas = [];
    foreach ($trabalhos as [$proc, $pipes]) {
        $saidas[] = stream_get_contents($pipes[1]);
        $erro = stream_get_contents($pipes[2]);
        fclose($pipes[1]); fclose($pipes[2]);
        okLoja(proc_close($proc) === 0 && $erro === '', 'worker sem erro');
    }
    sort($saidas);
    okLoja($saidas === ['comprou', 'duplicado'] && $moeda->obterSaldo(2) === 100 && count(qLoja($db, 'SELECT id_usuario_item FROM usuario_item WHERE id_usuario = ? AND id_item = ?', 'ii', 2, 4)) === 1, 'concorrencia: uma compra e um debito');
    echo "PASSOU: Loja.\n";
} finally {
    $db->rollback();
    if ($criado) qLoja($db, 'DROP DATABASE `' . $banco . '`');
    $db->close();
}
