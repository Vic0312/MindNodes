<?php
// Somente CLI; cria e remove exclusivamente um banco aleatorio de teste.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../controller/MoedaController.php';
require_once __DIR__ . '/../controller/AuthController.php';
require_once __DIR__ . '/../controller/UsuarioController.php';
require_once __DIR__ . '/../controller/QuizController.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
function conectarTeste($banco = '') {
    $db = new mysqli('127.0.0.1', getenv('MINDNODES_TEST_USER') ?: 'root', getenv('MINDNODES_TEST_PASSWORD') ?: '', $banco, (int) (getenv('MINDNODES_TEST_PORT') ?: 3306));
    $db->set_charset('utf8mb4');
    return $db;
}
function sqlTeste($db, $sql, $tipos = '', ...$valores) {
    $stmt = $db->prepare($sql);
    try {
        if ($tipos) $stmt->bind_param($tipos, ...$valores);
        $stmt->execute();
        return $stmt->field_count ? $stmt->get_result()->fetch_all(MYSQLI_ASSOC) : [];
    } finally { $stmt->close(); }
}
if (($argv[1] ?? '') === 'worker') {
    if (!preg_match('/^mindnodes_test_moedas_[a-f0-9]{16}$/D', $argv[2] ?? '')) exit(2);
    $db = conectarTeste($argv[2]);
    echo "pronto\n";
    fflush(STDOUT);
    try { (new Moeda($db))->debitar($argv[3], 30, 'bonus', 'concorrente'); echo 'sucesso'; }
    catch (DomainException $e) { echo 'insuficiente'; }
    exit;
}
ob_start();
session_start();
$total = 0;
function verificar($condicao, $nome) {
    global $total;
    if (!$condicao) throw new RuntimeException('FALHOU: ' . $nome);
    $total++;
    echo "OK: $nome\n";
}
function rejeitar($acao, $classe, $nome) {
    try { $acao(); } catch (Throwable $e) {
        verificar($e instanceof $classe, $nome . ' (' . get_class($e) . ')'); return;
    }
    throw new RuntimeException('Nao rejeitou: ' . $nome);
}
$db = conectarTeste();
$banco = 'mindnodes_test_moedas_' . bin2hex(random_bytes(8));
$criado = false;
try {
    // Identificador gerado internamente, nunca recebido do usuario.
    sqlTeste($db, 'CREATE DATABASE `' . $banco . '` CHARACTER SET utf8mb4');
    $criado = true;
    $db->select_db($banco);
    $db->multi_query(file_get_contents(__DIR__ . '/../mindnode.sql'));
    do { if ($resultado = $db->store_result()) $resultado->free(); }
    while ($db->more_results() && $db->next_result());
    $usuario = new Usuario(null, null, null, null, null, null, null, null, $db);
    $auth = new AuthController($usuario);
    verificar($auth->cadastrarUsuario('98765432100', 'Teste', 'Moeda', '2000-01-01', '11999999999', 'moeda@example.test', 'Senha123!', null)['sucesso'], 'regressao cadastro');
    $id = (int) $usuario->buscarPorEmail('moeda@example.test')['id_usuario'];
    $moeda = new MoedaController(new Moeda($db));
    verificar($moeda->obterSaldo($id) === 0, '1 saldo zero');
    verificar($moeda->creditar($id, 50, 'bonus', ' credito ') === 50, '2 credito 50');
    $h = $moeda->listarHistorico($id);
    verificar(count($h) === 1 && $h[0]['tipo'] === 'credito' && (int) $h[0]['valor'] === 50 && $h[0]['descricao'] === 'credito', '3 historico credito');
    verificar($moeda->creditar($id, 20, 'quiz') === 70, '4 credito 20');
    verificar($moeda->debitar($id, 30, 'loja', 'debito') === 40, '5 debito 30');
    $h = $moeda->listarHistorico($id);
    verificar($h[0]['tipo'] === 'debito' && (int) $h[0]['valor'] === 30, '6 historico debito');
    rejeitar(fn() => $moeda->debitar($id, 100, 'bonus'), DomainException::class, '7 saldo insuficiente');
    verificar($moeda->obterSaldo($id) === 40 && $moeda->listarHistorico($id) === $h, '7 sem alteracoes');
    rejeitar(fn() => $moeda->creditar($id, 0, 'bonus'), InvalidArgumentException::class, '8 credito zero');
    rejeitar(fn() => $moeda->creditar($id, -1, 'bonus'), InvalidArgumentException::class, '9 credito negativo');
    rejeitar(fn() => $moeda->debitar($id, -1, 'bonus'), InvalidArgumentException::class, '10 debito negativo');
    foreach (['obterSaldo', 'listarHistorico', 'creditar', 'debitar'] as $metodo) {
        rejeitar(fn() => $moeda->$metodo(2147483647, 1, 'bonus'), OutOfBoundsException::class, '11 inexistente ' . $metodo);
    }
    // DDL fixa de teste: MariaDB nao suporta CREATE TRIGGER no protocolo preparado.
    $db->query("CREATE TRIGGER falha_moeda BEFORE INSERT ON transacao_moeda FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'falha simulada'");
    foreach (['creditar', 'debitar'] as $metodo) {
        rejeitar(fn() => $moeda->$metodo($id, 1, 'bonus'), mysqli_sql_exception::class, '12 falha ' . $metodo);
        verificar($moeda->obterSaldo($id) === 40 && $moeda->listarHistorico($id) === $h, '12 rollback ' . $metodo);
    }
    $db->query('DROP TRIGGER falha_moeda');
    $ids = array_column($h, 'id_transacao');
    $ordenados = $ids; rsort($ordenados);
    verificar($ids === $ordenados && count($moeda->listarHistorico($id, 2)) === 2, '13 ordem e limite');
    sqlTeste($db, 'UPDATE transacao_moeda SET data_transacao = ? WHERE id_transacao = ?', 'si', '2000-01-01 00:00:00', $ids[0]);
    verificar($moeda->listarHistorico($id)[2]['id_transacao'] === $ids[0], '13 prioridade da data');
    foreach ([0, -1, 1.5, true, [], '1e2', '1 OR 1=1', 2147483648] as $invalido) {
        rejeitar(fn() => $moeda->obterSaldo($invalido), InvalidArgumentException::class, 'id invalido');
        rejeitar(fn() => $moeda->creditar($id, $invalido, 'bonus'), InvalidArgumentException::class, 'valor invalido');
        rejeitar(fn() => $moeda->listarHistorico($id, $invalido), InvalidArgumentException::class, 'limite invalido');
    }
    rejeitar(fn() => $moeda->creditar($id, 1, 'admin'), InvalidArgumentException::class, 'origem invalida');
    foreach ([[], str_repeat('a', 256), "\xFF", "a\0b"] as $descricao) {
        rejeitar(fn() => $moeda->creditar($id, 1, 'bonus', $descricao), InvalidArgumentException::class, 'descricao invalida');
    }
    rejeitar(fn() => $moeda->creditar($id, 2147483647, 'bonus'), OverflowException::class, 'overflow');
    $db->begin_transaction();
    rejeitar(fn() => $moeda->creditar($id, 1, 'bonus'), LogicException::class, 'transacao externa preservada');
    verificar((int) sqlTeste($db, 'SELECT @@in_transaction AS ativa')[0]['ativa'] === 1, 'transacao externa ainda ativa');
    $db->rollback();
    // Duas conexoes/processos disputam a mesma linha bloqueada pelo coordenador.
    $db->begin_transaction();
    sqlTeste($db, 'SELECT moedas FROM usuario WHERE id_usuario = ? FOR UPDATE', 'i', $id);
    $workers = [];
    for ($i = 0; $i < 2; $i++) {
        $processo = proc_open([PHP_BINARY, __FILE__, 'worker', $banco, (string) $id], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        if (!is_resource($processo)) throw new RuntimeException('Falha ao iniciar worker');
        fclose($pipes[0]);
        verificar(trim(fgets($pipes[1])) === 'pronto', 'worker conectado');
        $workers[] = [$processo, $pipes];
    }
    $db->commit();
    $resultados = [];
    foreach ($workers as [$processo, $pipes]) {
        $resultados[] = stream_get_contents($pipes[1]);
        $erro = stream_get_contents($pipes[2]);
        fclose($pipes[1]); fclose($pipes[2]);
        verificar(proc_close($processo) === 0 && $erro === '', 'worker sem erros');
    }
    sort($resultados);
    verificar($resultados === ['insuficiente', 'sucesso'] && $moeda->obterSaldo($id) === 10 && count($moeda->listarHistorico($id)) === 4, 'concorrencia: apenas um debito confirmado');
    verificar(!$auth->efetuarLogin('moeda@example.test', 'errada'), 'regressao login invalido');
    verificar((bool) $auth->efetuarLogin('moeda@example.test', 'Senha123!'), 'regressao login');
    $perfil = new UsuarioController($usuario);
    verificar($perfil->editarPerfilUsuario($id, 'Novo', 'Moeda', 'moeda@example.test', '11999999999', '', null) && $perfil->buscarPerfil($id)['nome'] === 'Novo', 'regressao perfil');
    verificar($auth->iniciarRecuperacao('98765432100', 'moeda@example.test', '2000-01-01', 'invalido') === 'sessao', 'regressao CSRF recuperacao');
    verificar($auth->iniciarRecuperacao('98765432100', 'moeda@example.test', '2000-01-01', AuthController::tokenRecuperacao()) === null, 'regressao recuperacao');
    verificar($auth->redefinirSenha('NovaSenha123!', 'NovaSenha123!', AuthController::tokenRecuperacao()) === null, 'regressao redefinicao');
    verificar(!$auth->efetuarLogin('moeda@example.test', 'Senha123!') && (bool) $auth->efetuarLogin('moeda@example.test', 'NovaSenha123!'), 'regressao nova senha');
    $quiz = new QuizController(new Quiz($db));
    $respostas = [];
    foreach ($quiz->buscarPerguntasQuiz('tad') as $p) foreach ($p['alternativas'] as $a) if ($a['correta']) $respostas[$p['id_pergunta']] = $a['id_alternativa'];
    $tentativa = $quiz->salvarTentativaQuiz($id, 'tad', $respostas);
    verificar($tentativa && (int) $quiz->buscarTentativaQuiz($tentativa, $id)['total_acertos'] === 3, 'regressao quiz');
    verificar((int) $quiz->buscarDesempenhoUsuario($id)['resumo']['total_tentativas'] === 1, 'regressao desempenho');
    verificar($moeda->obterSaldo($id) === 10 && count($moeda->listarHistorico($id)) === 4, 'quiz e perfil nao alteram moedas');
    $auth->logout();
    verificar($_SESSION === [] && session_status() === PHP_SESSION_NONE, 'regressao logout');
    echo "PASSOU: $total verificacoes.\n";
} finally {
    $db->rollback();
    if ($criado) sqlTeste($db, 'DROP DATABASE `' . $banco . '`');
    $db->close();
    ob_end_flush();
}
