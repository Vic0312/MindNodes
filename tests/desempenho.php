<?php
if (PHP_SAPI !== 'cli') exit(1);
require_once __DIR__ . '/../controller/QuizController.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function conferirDesempenho($condicao, $mensagem) {
    if (!$condicao) throw new RuntimeException('FALHOU: ' . $mensagem);
    echo "OK: $mensagem\n";
}

$db = new mysqli('127.0.0.1', getenv('MINDNODES_TEST_USER') ?: 'root', getenv('MINDNODES_TEST_PASSWORD') ?: '', '', (int) (getenv('MINDNODES_TEST_PORT') ?: 3306));
$db->set_charset('utf8mb4');
$banco = 'mindnodes_test_desempenho_' . bin2hex(random_bytes(8));
$criado = false;
try {
    $db->query('CREATE DATABASE `' . $banco . '` CHARACTER SET utf8mb4');
    $criado = true;
    $db->select_db($banco);
    $db->multi_query(file_get_contents(__DIR__ . '/../mindnode.sql'));
    do { if ($r = $db->store_result()) $r->free(); } while ($db->more_results() && $db->next_result());

    $quiz = new Quiz($db);
    $controller = new QuizController($quiz);
    $moeda = new Moeda($db);
    $_SESSION = ['estaLogado' => true, 'usuario_id' => 1];
    $tentativaOutro = 4;
    conferirDesempenho($controller->buscarMinhaTentativa($tentativaOutro) === false, 'revisao de outro usuario negada');
    conferirDesempenho($controller->buscarMinhaTentativa(999999) === false && $controller->buscarMinhaTentativa('invalida') === false, 'tentativa inexistente ou invalida tratada');

    $db->query('DELETE FROM quiz_tentativa WHERE id_usuario = 1');
    $vazio = $controller->buscarMeuDesempenho();
    conferirDesempenho((int) $vazio['resumo']['total_tentativas'] === 0 && (int) $vazio['resumo']['total_perguntas'] === 0
        && (int) $vazio['resumo']['total_acertos'] === 0 && (int) $vazio['resumo']['moedas_ganhas'] === 0
        && $vazio['tentativas'] === [], 'estado sem historico e somas zeradas');
    conferirDesempenho(count($vazio['assuntos']) === 6 && count(array_filter($vazio['assuntos'], fn($a) => (int) $a['total_tentativas'] === 0)) === 6,
        'seis assuntos dinamicos sem tentativas');

    $respostas = [];
    foreach ($quiz->buscarPerguntas('tad') as $pergunta) {
        foreach ($pergunta['alternativas'] as $alternativa) if ($alternativa['correta']) $respostas[$pergunta['id_pergunta']] = $alternativa['id_alternativa'];
    }
    $idProprio = $quiz->salvarTentativa(1, 'tad', $respostas);
    $uma = $controller->buscarMeuDesempenho();
    conferirDesempenho((int) $uma['resumo']['total_tentativas'] === 1 && (int) $uma['resumo']['total_perguntas'] === 4
        && (int) $uma['resumo']['total_acertos'] === 4 && (int) $uma['resumo']['moedas_ganhas'] === 0,
        'uma tentativa antiga, quatro acertos e zero moedas');
    $revisao = $controller->buscarMinhaTentativa($idProprio);
    conferirDesempenho(count($revisao['respostas']) === 4 && count(array_filter($revisao['respostas'], fn($r) => $r['tipo'] === 'codigo' && $r['codigo'] !== '')) === 1,
        'revisao propria inclui questao teorica e codigo C#');

    $stmt = $db->prepare('INSERT INTO quiz_tentativa (id_usuario, id_assunto, total_perguntas, total_acertos, moedas_ganhas, data_tentativa) VALUES (1, 4, 6, 4, 55, ?)');
    $dataNova = '2030-09-15 16:30:00';
    $stmt->bind_param('s', $dataNova);
    $stmt->execute();
    $idNovo = $db->insert_id;
    $stmt->close();
    $dados = $controller->buscarMeuDesempenho();
    conferirDesempenho((int) $dados['resumo']['total_tentativas'] === 2 && (int) $dados['resumo']['total_perguntas'] === 10
        && (int) $dados['resumo']['total_acertos'] === 8 && (int) $dados['resumo']['moedas_ganhas'] === 55
        && round((int) $dados['resumo']['total_acertos'] / (int) $dados['resumo']['total_perguntas'] * 100, 1) === 80.0,
        'resumo soma 2 quizzes, 10 questoes, 8 acertos, 80% e 55 moedas');
    $fila = array_values(array_filter($dados['assuntos'], fn($a) => $a['slug'] === 'fila-fifo'))[0];
    conferirDesempenho((int) $fila['total_tentativas'] === 1 && (int) $fila['total_perguntas'] === 6
        && (int) $fila['total_acertos'] === 4 && (int) $fila['moedas_ganhas'] === 55,
        'agregacao por assunto inclui Fila FIFO');
    conferirDesempenho((int) $dados['tentativas'][0]['id_tentativa'] === $idNovo
        && (int) $dados['tentativas'][0]['moedas_ganhas'] === 55
        && (int) $dados['tentativas'][1]['moedas_ganhas'] === 0, 'historico recente primeiro e moedas persistidas');
    $saldoAntes = $moeda->obterSaldo(1);
    $controller->buscarMeuDesempenho();
    $controller->buscarMeuDesempenho();
    conferirDesempenho($moeda->obterSaldo(1) === $saldoAntes, 'consultar desempenho nao credita moedas');
    $moeda->creditar(1, 100, 'bonus', 'Preparacao do teste');
    $moeda->debitar(1, 30, 'loja', 'Compra de teste');
    conferirDesempenho($moeda->obterSaldo(1) === $saldoAntes + 70
        && (int) $controller->buscarMeuDesempenho()['resumo']['moedas_ganhas'] === 55,
        'gasto reduz saldo mas preserva moedas historicas do Quiz');
    $_SESSION['usuario_id'] = 2;
    conferirDesempenho($controller->buscarMinhaTentativa($idProprio) === false
        && (int) $controller->buscarMeuDesempenho()['resumo']['total_tentativas'] === 1,
        'sessao de outro usuario nao consulta nem revisa dados alheios');
    echo "PASSOU: desempenho e historico.\n";
} finally {
    if ($criado) $db->query('DROP DATABASE `' . $banco . '`');
    $db->close();
}
