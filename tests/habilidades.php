<?php
if (PHP_SAPI !== 'cli') exit(1);
require_once __DIR__ . '/../controller/QuizController.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
function okPoder($ok, $nome) { if (!$ok) throw new RuntimeException('FALHOU: ' . $nome); echo "OK: $nome\n"; }
function negarPoder($acao, $classe, $nome) {
    try { $acao(); } catch (Throwable $erro) { okPoder($erro instanceof $classe, $nome); return; }
    throw new RuntimeException('Nao rejeitou: ' . $nome);
}
function sqlPoder($db, $sql, $tipos = '', ...$valores) {
    $stmt = $db->prepare($sql);
    try { if ($tipos) $stmt->bind_param($tipos, ...$valores); $stmt->execute(); return $stmt->field_count ? $stmt->get_result()->fetch_all(MYSQLI_ASSOC) : []; }
    finally { $stmt->close(); }
}
$db = new mysqli('127.0.0.1', getenv('MINDNODES_TEST_USER') ?: 'root', getenv('MINDNODES_TEST_PASSWORD') ?: '', '', (int) (getenv('MINDNODES_TEST_PORT') ?: 3306));
$db->set_charset('utf8mb4');
$banco = 'mindnodes_test_poder_' . bin2hex(random_bytes(8));
$criado = false;
try {
    sqlPoder($db, 'CREATE DATABASE `' . $banco . '` CHARACTER SET utf8mb4');
    $criado = true;
    $db->select_db($banco);
    $db->multi_query(file_get_contents(__DIR__ . '/../mindnode.sql'));
    do { if ($r = $db->store_result()) $r->free(); } while ($db->more_results() && $db->next_result());
    $_SESSION = ['estaLogado' => true, 'usuario_id' => 1];
    $avatar = new Avatar($db);
    $inventario = new Inventario($db);
    $quiz = new Quiz($db);
    $controller = new QuizController($quiz, new QuizHabilidades($quiz, $avatar));
    $saldo = (int) sqlPoder($db, 'SELECT moedas FROM usuario WHERE id_usuario = ?', 'i', 1)[0]['moedas'];
    $estado = $controller->iniciarTentativaQuiz('tad');
    okPoder($estado['habilidades'] === [] && $controller->iniciarTentativaQuiz('tad')['token'] === $estado['token'], 'padrao sem poderes e refresh nao reinicia');
    negarPoder(fn() => $controller->usarDicaQuiz($estado['token'], 10), DomainException::class, 'dica sem item negada');
    $inventario->adicionarItem(1, 5);
    $estado = $controller->reiniciarTentativaQuiz($estado['token']);
    okPoder($estado['habilidades'] === [], 'posse sem equipamento nao concede');
    $avatar->equiparItem(1, 5);
    $estado = $controller->reiniciarTentativaQuiz($estado['token']);
    okPoder($estado['habilidades']['dica']['quantidade_restante'] === 1, 'oculos equipados concedem dica');
    negarPoder(fn() => $controller->usarDicaQuiz($estado['token'], 4), OutOfBoundsException::class, 'pergunta de outro assunto negada');
    negarPoder(fn() => $controller->usarDicaQuiz($estado['token'], 2), DomainException::class, 'pergunta sem dica negada');
    okPoder($_SESSION['quiz_tentativa_atual']['habilidades']['dica']['quantidade_restante'] === 1, 'falha nao consome dica');
    negarPoder(fn() => $controller->usarDicaQuiz('token-invalido', 10), LogicException::class, 'token invalido negado');
    $dica = $controller->usarDicaQuiz($estado['token'], 10);
    okPoder($dica['sucesso'] && $dica['restante'] === 0 && str_contains($dica['conteudo'], 'assinaturas'), 'dica de codigo revelada uma vez');
    negarPoder(fn() => $controller->usarDicaQuiz($estado['token'], 1), DomainException::class, 'dica esgotada');
    $inventario->adicionarItem(1, 4);
    $inventario->adicionarItem(1, 6);
    $avatar->equiparItem(1, 4);
    $avatar->equiparItem(1, 6);
    $estado = $controller->reiniciarTentativaQuiz($estado['token']);
    okPoder(count($estado['habilidades']) === 3 && $estado['habilidades']['eliminar_alternativa']['quantidade_total'] === 1 && $estado['habilidades']['resumo_rapido']['quantidade_total'] === 1, 'tres poderes equipados na nova tentativa');
    $eliminada = $controller->eliminarAlternativaQuiz($estado['token'], 10);
    $idEliminada = $eliminada['id_alternativa'];
    okPoder($eliminada['restante'] === 0 && (int) sqlPoder($db, 'SELECT correta FROM quiz_alternativa WHERE id_alternativa = ?', 'i', $idEliminada)[0]['correta'] === 0, 'eliminacao de codigo nunca escolhe correta');
    negarPoder(fn() => $controller->eliminarAlternativaQuiz($estado['token'], 10), DomainException::class, 'eliminacao esgotada');
    $resumo = $controller->usarResumoQuiz($estado['token']);
    okPoder($resumo['restante'] === 0 && str_contains($resumo['conteudo'], 'Tipo Abstrato') && !str_contains($resumo['conteudo'], 'alternativa'), 'resumo conceitual do assunto');
    negarPoder(fn() => $controller->usarResumoQuiz($estado['token']), DomainException::class, 'resumo esgotado');
    $avatarAntes = sqlPoder($db, 'SELECT id_cabelo, id_rosto, id_roupa FROM avatar_usuario WHERE id_usuario = ?', 'i', 1)[0];
    okPoder((int) sqlPoder($db, 'SELECT moedas FROM usuario WHERE id_usuario = ?', 'i', 1)[0]['moedas'] === $saldo
        && count($inventario->listarDoUsuario(1)) === 6
        && sqlPoder($db, 'SELECT id_cabelo, id_rosto, id_roupa FROM avatar_usuario WHERE id_usuario = ?', 'i', 1)[0] === $avatarAntes, 'poder nao consome moedas, itens ou avatar');
    sqlPoder($db, 'UPDATE item SET valor_habilidade = ? WHERE id_item = ?', 'ii', 2, 4);
    $estado = $controller->reiniciarTentativaQuiz($estado['token']);
    okPoder($estado['habilidades']['eliminar_alternativa']['quantidade_restante'] === 2, 'valor dois concede dois usos');
    $primeira = $controller->eliminarAlternativaQuiz($estado['token'], 10);
    $segunda = $controller->eliminarAlternativaQuiz($estado['token'], 10);
    okPoder($primeira['id_alternativa'] !== $segunda['id_alternativa'] && $segunda['restante'] === 0, 'duas incorretas distintas eliminadas');
    negarPoder(fn() => $controller->eliminarAlternativaQuiz($estado['token'], 10), DomainException::class, 'terceiro uso negado');
    sqlPoder($db, 'UPDATE item SET valor_habilidade = ? WHERE id_item = ?', 'ii', 3, 4);
    $estado = $controller->reiniciarTentativaQuiz($estado['token']);
    $controller->eliminarAlternativaQuiz($estado['token'], 10);
    $controller->eliminarAlternativaQuiz($estado['token'], 10);
    negarPoder(fn() => $controller->eliminarAlternativaQuiz($estado['token'], 10), DomainException::class, 'sem incorretas elegiveis');
    okPoder($_SESSION['quiz_tentativa_atual']['habilidades']['eliminar_alternativa']['quantidade_restante'] === 1, 'sem elegiveis nao consome uso');
    $avatar->equiparItem(1, 1);
    okPoder($controller->iniciarTentativaQuiz('tad')['habilidades']['eliminar_alternativa']['quantidade_total'] === 3, 'troca de avatar nao muda tentativa em andamento');
    $respostas = [];
    foreach ($quiz->buscarPerguntas('tad') as $pergunta) foreach ($pergunta['alternativas'] as $alternativa) if ($alternativa['correta']) $respostas[$pergunta['id_pergunta']] = $alternativa['id_alternativa'];
    $tentativa = $controller->finalizarTentativaQuiz($estado['token'], 'tad', $respostas);
    okPoder($tentativa && !isset($_SESSION['quiz_tentativa_atual']) && (int) $quiz->buscarTentativa($tentativa, 1)['total_acertos'] === 4, 'finalizacao encerra estado e mantem correcao');
    okPoder(!isset($controller->iniciarTentativaQuiz('tad')['habilidades']['eliminar_alternativa']), 'nova tentativa reflete avatar atual');
    echo "PASSOU: habilidades do Quiz.\n";
} finally {
    $db->rollback();
    if ($criado) sqlPoder($db, 'DROP DATABASE `' . $banco . '`');
    $db->close();
}
