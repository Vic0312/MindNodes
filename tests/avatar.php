<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../controller/AvatarController.php';
require_once __DIR__ . '/../controller/AuthController.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
function verificar($ok, $nome) { if (!$ok) throw new RuntimeException($nome); echo "OK: $nome\n"; }
function rejeitar($acao, $classe, $nome) {
    try { $acao(); } catch (Throwable $erro) { verificar($erro instanceof $classe, $nome); return; }
    throw new RuntimeException("Nao rejeitou: $nome");
}
function consultar($db, $sql, $tipos = '', ...$valores) {
    $stmt = $db->prepare($sql);
    try {
        if ($tipos) $stmt->bind_param($tipos, ...$valores);
        $stmt->execute();
        return $stmt->field_count ? $stmt->get_result()->fetch_all(MYSQLI_ASSOC) : [];
    } finally { $stmt->close(); }
}
$db = new mysqli('127.0.0.1', getenv('MINDNODES_TEST_USER') ?: 'root', getenv('MINDNODES_TEST_PASSWORD') ?: '', '', (int) (getenv('MINDNODES_TEST_PORT') ?: 3306));
$db->set_charset('utf8mb4');
$banco = 'mindnodes_test_avatar_' . bin2hex(random_bytes(8));
$criado = false;
try {
    consultar($db, 'CREATE DATABASE `' . $banco . '` CHARACTER SET utf8mb4');
    $criado = true;
    $db->select_db($banco);
    $db->multi_query(file_get_contents(__DIR__ . '/../mindnode.sql'));
    do { if ($r = $db->store_result()) $r->free(); } while ($db->more_results() && $db->next_result());
    $avatar = new Avatar($db);
    $inventario = new Inventario($db);
    $atual = $avatar->buscarDoUsuario(1);
    verificar($atual['base']['imagem'] === 'img/avatar/base/node_base.png' && $atual['cabelo']['nome'] === 'Cabelo Padrão' && $atual['rosto']['nome'] === 'Rosto Padrão' && $atual['roupa']['nome'] === 'Roupa Padrão' && $atual['acessorio'] === null, 'avatar legado');
    foreach (['base', 'cabelo', 'rosto', 'roupa'] as $camada) verificar(is_file(__DIR__ . '/../' . $atual[$camada]['imagem']), 'asset ' . $camada);
    foreach ((new Item($db))->listarTodos() as $item) verificar(is_file(__DIR__ . '/../' . $item['imagem']), 'asset item ' . $item['id_item']);
    $usuario = new Usuario(null, null, null, null, null, null, null, null, $db);
    $auth = new AuthController($usuario);
    verificar($auth->cadastrarUsuario('98765432100', 'Teste', 'Avatar', '2000-01-01', '11999999999', 'avatar@example.test', 'Senha123!', null)['sucesso'], 'cadastro');
    $id = (int) $usuario->buscarPorEmail('avatar@example.test')['id_usuario'];
    verificar(count($inventario->listarDoUsuario($id)) === 3 && $avatar->buscarDoUsuario($id)['roupa']['id_item'] == 3, 'cadastro concede e equipa');
    $db->query("CREATE TRIGGER falha_avatar BEFORE INSERT ON avatar_usuario FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'falha avatar'");
    verificar(!$auth->cadastrarUsuario('98765432101', 'Falha', 'Avatar', '2000-01-01', '11999999999', 'falha-avatar@example.test', 'Senha123!', null)['sucesso'] && !$usuario->buscarPorEmail('falha-avatar@example.test'), 'falha avatar reverte cadastro');
    $db->query('DROP TRIGGER falha_avatar');
    $avatar->criarAvatarPadrao($id);
    verificar(count(consultar($db, 'SELECT id_avatar FROM avatar_usuario WHERE id_usuario = ?', 'i', $id)) === 1, 'criacao idempotente');
    rejeitar(fn() => $avatar->equiparItem($id, 4), DomainException::class, 'item nao possuido');
    $inventario->adicionarItem(1, 4);
    rejeitar(fn() => $avatar->equiparItem($id, 4), DomainException::class, 'item de outro usuario');
    $inventario->adicionarItem($id, 4);
    consultar($db, 'UPDATE item SET ativo = 0 WHERE id_item = ?', 'i', 4);
    $avatar->equiparItem($id, 4);
    $atual = $avatar->buscarDoUsuario($id);
    verificar($atual['cabelo']['id_item'] == 4 && $atual['rosto']['id_item'] == 2 && $atual['roupa']['id_item'] == 3, 'troca apenas cabelo inativo');
    foreach ([5 => 'rosto', 6 => 'roupa'] as $item => $categoria) {
        $inventario->adicionarItem($id, $item);
        $avatar->equiparItem($id, $item);
        verificar($avatar->buscarDoUsuario($id)[$categoria]['id_item'] == $item, 'troca ' . $categoria);
    }
    rejeitar(fn() => $avatar->equiparItem($id, 2147483647), OutOfBoundsException::class, 'item inexistente');
    rejeitar(fn() => $avatar->equiparItem(2147483647, 1), OutOfBoundsException::class, 'usuario inexistente');
    rejeitar(fn() => $avatar->buscarDoUsuario(2147483647), OutOfBoundsException::class, 'busca usuario inexistente');
    verificar(!method_exists($avatar, 'removerCabelo') && !method_exists($avatar, 'removerRosto') && !method_exists($avatar, 'removerRoupa'), 'slots obrigatorios sem remocao');
    $avatar->removerAcessorio($id);
    verificar($avatar->buscarDoUsuario($id)['acessorio'] === null, 'acessorio removido');
    consultar($db, 'INSERT INTO item (nome, categoria, imagem) VALUES (?, ?, ?)', 'sss', 'Acessorio teste', 'acessorio', 'img/avatar/base/node_base.png');
    $idAcessorio = $db->insert_id;
    $inventario->adicionarItem($id, $idAcessorio);
    $avatar->equiparItem($id, $idAcessorio);
    verificar($avatar->buscarDoUsuario($id)['acessorio']['id_item'] == $idAcessorio && $avatar->buscarDoUsuario($id)['cabelo']['id_item'] == 4, 'acessorio usa apenas slot opcional');
    $avatar->removerAcessorio($id);
    verificar($avatar->buscarDoUsuario($id)['acessorio'] === null, 'remocao do acessorio equipado');
    consultar($db, 'DELETE FROM avatar_usuario WHERE id_usuario = ?', 'i', $id);
    verificar($avatar->buscarDoUsuario($id)['cabelo']['id_item'] == 1 && count(consultar($db, 'SELECT id_avatar FROM avatar_usuario WHERE id_usuario = ?', 'i', $id)) === 1, 'autocorrecao sem duplicar');
    $_SESSION = ['estaLogado' => true, 'usuario_id' => 2];
    $controller = new AvatarController($avatar);
    rejeitar(fn() => $controller->equiparItem(5), DomainException::class, 'controller usa sessao');
    $_SESSION = [];
    rejeitar(fn() => $controller->removerAcessorio(), LogicException::class, 'controller exige login');
    echo "PASSOU: avatar.\n";
} finally {
    $db->rollback();
    if ($criado) consultar($db, 'DROP DATABASE `' . $banco . '`');
    $db->close();
}
