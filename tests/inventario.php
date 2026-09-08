<?php
// CLI: importa o dump em banco aleatorio e remove somente esse banco ao finalizar.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../controller/InventarioController.php';
require_once __DIR__ . '/../controller/AuthController.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
function conectarInventario($banco = '') {
    $db = new mysqli('127.0.0.1', getenv('MINDNODES_TEST_USER') ?: 'root', getenv('MINDNODES_TEST_PASSWORD') ?: '', $banco, (int) (getenv('MINDNODES_TEST_PORT') ?: 3306));
    $db->set_charset('utf8mb4');
    return $db;
}
function consultaInventario($db, $sql, $tipos = '', ...$valores) {
    $stmt = $db->prepare($sql);
    try {
        if ($tipos) $stmt->bind_param($tipos, ...$valores);
        $stmt->execute();
        return $stmt->field_count ? $stmt->get_result()->fetch_all(MYSQLI_ASSOC) : [];
    } finally { $stmt->close(); }
}
if (($argv[1] ?? '') === 'worker') {
    if (!preg_match('/^mindnodes_test_inventario_[a-f0-9]{16}$/D', $argv[2] ?? '')) exit(2);
    $db = conectarInventario($argv[2]);
    echo "pronto\n"; fflush(STDOUT);
    (new Inventario($db))->adicionarItem(2, 4);
    echo 'sucesso';
    exit;
}
$total = 0;
function conferirInventario($ok, $nome) {
    global $total;
    if (!$ok) throw new RuntimeException('FALHOU: ' . $nome);
    $total++; echo "OK: $nome\n";
}
function negarInventario($acao, $classe, $nome) {
    try { $acao(); } catch (Throwable $erro) {
        conferirInventario($erro instanceof $classe, $nome); return;
    }
    throw new RuntimeException('Nao rejeitou: ' . $nome);
}
$db = conectarInventario();
$banco = 'mindnodes_test_inventario_' . bin2hex(random_bytes(8));
$criado = false;
try {
    consultaInventario($db, 'CREATE DATABASE `' . $banco . '` CHARACTER SET utf8mb4');
    $criado = true;
    $db->select_db($banco);
    $db->multi_query(file_get_contents(__DIR__ . '/../mindnode.sql'));
    do { if ($r = $db->store_result()) $r->free(); } while ($db->more_results() && $db->next_result());
    $item = new Item($db);
    $inventario = new Inventario($db);
    $controller = new InventarioController($inventario);
    conferirInventario(count($item->listarTodos()) === 6, 'catalogo completo');
    consultaInventario($db, 'UPDATE item SET ativo = 0 WHERE id_item = ?', 'i', 4);
    conferirInventario(count($item->listarAtivos()) === 5 && array_unique(array_column($item->listarAtivos(), 'ativo')) === [1], '1 somente ativos');
    conferirInventario($item->buscarPorId('4')['nome'] === 'Boné FIFO', '2 item existente inclusive inativo');
    conferirInventario($item->buscarPorId(2147483647) === null, '3 item inexistente');
    conferirInventario(count($item->listarPorCategoria('cabelo')) === 2 && array_unique(array_column($item->listarPorCategoria('cabelo'), 'categoria')) === ['cabelo'], '4 categoria cabelo');
    negarInventario(fn() => $item->listarPorCategoria('invalida'), InvalidArgumentException::class, '5 categoria invalida');
    $iniciais = $controller->listarDoUsuario(1);
    conferirInventario(count($iniciais) === 3 && array_column($iniciais, 'nome') === ['Cabelo Padrão', 'Rosto Padrão', 'Roupa Padrão'], '6 inventario inicial');
    conferirInventario(count(array_intersect(['id_item','nome','descricao','categoria','preco','imagem','habilidade','descricao_habilidade','valor_habilidade','ativo','data_compra'], array_keys($iniciais[0]))) === 11, 'JOIN retorna todos os campos');
    conferirInventario($controller->possuiItem(1, 1) === true, '7 possui');
    conferirInventario($controller->possuiItem(1, 4) === false, '8 nao possui');
    conferirInventario($inventario->adicionarItem(1, 4) && $controller->possuiItem(1, 4), '9 adicionar item inativo');
    $antes = $controller->listarDoUsuario(1);
    conferirInventario($inventario->adicionarItem(1, 4) && $antes === $controller->listarDoUsuario(1), '10 repeticao preserva registro e data');
    negarInventario(fn() => $inventario->adicionarItem(2147483647, 1), OutOfBoundsException::class, '11 usuario inexistente');
    negarInventario(fn() => $inventario->adicionarItem(1, 2147483647), OutOfBoundsException::class, '12 item inexistente');
    negarInventario(fn() => consultaInventario($db, 'INSERT INTO usuario_item (id_usuario,id_item) VALUES (?,?)', 'ii', 1, 1), mysqli_sql_exception::class, '13 UNIQUE rejeita duplicacao direta');
    $usuario = new Usuario(null, null, null, null, null, null, null, null, $db);
    $auth = new AuthController($usuario);
    $cadastro = fn($cpf, $email) => $auth->cadastrarUsuario($cpf, 'Teste', 'Inventario', '2000-01-01', '11999999999', $email, 'Senha123!', null);
    conferirInventario($cadastro('98765432100', 'inventario@example.test')['sucesso'], '14 cadastro normal');
    $novo = $usuario->buscarPorEmail('inventario@example.test');
    $id = $novo['id_usuario'];
    conferirInventario(password_verify('Senha123!', $novo['senha']) && $novo['senha'] !== 'Senha123!', '14 hash preservado');
    conferirInventario(array_column($controller->listarDoUsuario($id), 'nome') === array_column($iniciais, 'nome'), '14 tres itens padrao');
    conferirInventario(!consultaInventario($db, 'SELECT * FROM avatar_usuario WHERE id_usuario = ?', 'i', $id), 'cadastro nao cria avatar');
    $db->query("CREATE TRIGGER falha_inventario BEFORE INSERT ON usuario_item FOR EACH ROW BEGIN IF NEW.id_item = 2 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'falha simulada'; END IF; END");
    conferirInventario($cadastro('98765432101', 'falha@example.test') === ['sucesso' => false, 'erro' => 'banco'], '15 falha na segunda concessao');
    conferirInventario(!$usuario->buscarPorEmail('falha@example.test') && count(consultaInventario($db, 'SELECT * FROM usuario_item')) === 10, '15 rollback usuario e primeiro item');
    $db->query('DROP TRIGGER falha_inventario');
    consultaInventario($db, 'UPDATE item SET nome = ? WHERE id_item = ?', 'si', 'Outro nome', 1);
    conferirInventario(!$cadastro('98765432102', 'ausente@example.test')['sucesso'] && !$usuario->buscarPorEmail('ausente@example.test'), 'padrao ausente impede cadastro');
    consultaInventario($db, 'UPDATE item SET nome = ? WHERE id_item = ?', 'si', 'Cabelo Padrão', 1);
    consultaInventario($db, 'INSERT INTO item (nome,categoria,imagem) VALUES (?,?,?)', 'sss', 'Cabelo Padrão', 'cabelo', 'teste');
    $duplicado = $db->insert_id;
    conferirInventario(!$cadastro('98765432103', 'ambiguo@example.test')['sucesso'], 'padrao ambiguo impede cadastro');
    consultaInventario($db, 'DELETE FROM item WHERE id_item = ?', 'i', $duplicado);
    foreach ([0, -1, 1.5, true, [], null, '1e2', '1 OR 1=1', '01', 2147483648] as $invalido) {
        negarInventario(fn() => $item->buscarPorId($invalido), InvalidArgumentException::class, 'ID item invalido');
        negarInventario(fn() => $inventario->listarDoUsuario($invalido), InvalidArgumentException::class, 'ID usuario invalido');
        negarInventario(fn() => $inventario->adicionarItem(1, $invalido), InvalidArgumentException::class, 'adicao invalida');
    }
    negarInventario(fn() => $controller->listarPorCategoria(1, []), InvalidArgumentException::class, 'categoria inventario invalida');
    conferirInventario(count($controller->listarPorCategoria(1, 'cabelo')) === 2 && $controller->listarPorCategoria(1, 'acessorio') === [], 'filtro inventario e acessorio vazio');
    conferirInventario(!$controller->possuiItem(1, 2147483647), 'propriedade item inexistente falsa');
    $db->begin_transaction();
    negarInventario(fn() => $usuario->cadastrar(), LogicException::class, 'cadastro rejeita transacao externa');
    $inventario->adicionarItem($id, 5);
    $db->rollback();
    conferirInventario(!$inventario->possuiItem($id, 5), 'adicao participa do rollback externo');
    $db->begin_transaction();
    consultaInventario($db, 'SELECT id_usuario FROM usuario WHERE id_usuario = ? FOR UPDATE', 'i', 2);
    $workers = [];
    for ($i = 0; $i < 2; $i++) {
        $p = proc_open([PHP_BINARY, __FILE__, 'worker', $banco], [0 => ['pipe','r'], 1 => ['pipe','w'], 2 => ['pipe','w']], $pipes);
        if (!is_resource($p)) throw new RuntimeException('Falha worker');
        fclose($pipes[0]);
        conferirInventario(trim(fgets($pipes[1])) === 'pronto', 'worker conectado');
        $workers[] = [$p, $pipes];
    }
    $db->commit();
    foreach ($workers as [$p, $pipes]) {
        $saida = stream_get_contents($pipes[1]); $erro = stream_get_contents($pipes[2]);
        fclose($pipes[1]); fclose($pipes[2]);
        conferirInventario(proc_close($p) === 0 && $saida === 'sucesso' && $erro === '', 'adicao concorrente sem erro');
    }
    conferirInventario(count(consultaInventario($db, 'SELECT * FROM usuario_item WHERE id_usuario = ? AND id_item = ?', 'ii', 2, 4)) === 1, 'concorrencia sem duplicacao');
    conferirInventario(count(consultaInventario($db, 'SELECT * FROM avatar_usuario')) === 2 && !consultaInventario($db, 'SELECT * FROM transacao_moeda') && (int) $usuario->buscarPerfil($id)['moedas'] === 0, 'avatar legado e moedas preservados');
    conferirInventario($item->buscarPorId(1)['imagem'] === 'img/avatar/cabelo-padrao.png', 'imagem retornada sem reescrita');
    echo "PASSOU: $total verificacoes.\n";
} finally {
    $db->rollback();
    if ($criado) consultaInventario($db, 'DROP DATABASE `' . $banco . '`');
    $db->close();
}
