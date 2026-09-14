<?php
if (PHP_SAPI !== 'cli') exit(1);
require_once __DIR__ . '/../model/Quiz.php';
require_once __DIR__ . '/../model/QuizHabilidades.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
if (($argv[1] ?? '') === 'worker') {
    session_save_path($argv[3]);
    session_id($argv[4]);
    session_start();
    $conexao = new mysqli('127.0.0.1', getenv('MINDNODES_TEST_USER') ?: 'root', getenv('MINDNODES_TEST_PASSWORD') ?: '', $argv[2], (int) (getenv('MINDNODES_TEST_PORT') ?: 3306));
    $conexao->set_charset('utf8mb4');
    $quizWorker = new Quiz($conexao);
    $respostasWorker = [];
    foreach ($quizWorker->buscarPerguntas('tad') as $pergunta) foreach ($pergunta['alternativas'] as $alternativa) {
        if ($alternativa['correta']) $respostasWorker[$pergunta['id_pergunta']] = $alternativa['id_alternativa'];
    }
    try { (new QuizHabilidades($quizWorker))->finalizar(1, $argv[5], 'tad', $respostasWorker); echo 'pago'; }
    catch (LogicException $erro) { echo 'rejeitado'; }
    session_write_close();
    exit;
}
ob_start();
function okRecompensa($condicao, $nome) { if (!$condicao) throw new RuntimeException('FALHOU: ' . $nome); echo "OK: $nome\n"; }
function sqlRecompensa($db, $sql, $tipos = '', ...$valores) {
    $stmt = $db->prepare($sql);
    try { if ($tipos !== '') $stmt->bind_param($tipos, ...$valores); $stmt->execute(); return $stmt->field_count ? $stmt->get_result()->fetch_all(MYSQLI_ASSOC) : []; }
    finally { $stmt->close(); }
}
$db = new mysqli('127.0.0.1', getenv('MINDNODES_TEST_USER') ?: 'root', getenv('MINDNODES_TEST_PASSWORD') ?: '', '', (int) (getenv('MINDNODES_TEST_PORT') ?: 3306));
$db->set_charset('utf8mb4');
$banco = 'mindnodes_test_recompensa_' . bin2hex(random_bytes(8));
$criado = false;
try {
    sqlRecompensa($db, 'CREATE DATABASE `' . $banco . '` CHARACTER SET utf8mb4');
    $criado = true;
    $db->select_db($banco);
    $db->multi_query(file_get_contents(__DIR__ . '/../mindnode.sql'));
    do { if ($r = $db->store_result()) $r->free(); } while ($db->more_results() && $db->next_result());
    // O dump tem quatro perguntas por assunto; a quinta testa os exemplos da regra.
    sqlRecompensa($db, 'INSERT INTO quiz_pergunta (id_assunto, enunciado, explicacao) VALUES (1, ?, ?)', 'ss', 'Pergunta extra de teste', 'Explicacao de teste');
    $idExtra = $db->insert_id;
    sqlRecompensa($db, 'INSERT INTO quiz_alternativa (id_pergunta, texto, correta) VALUES (?, ?, 1), (?, ?, 0)', 'isis', $idExtra, 'Certa', $idExtra, 'Errada');
    $quiz = new Quiz($db);
    $moeda = new Moeda($db);
    $perguntas = $quiz->buscarPerguntas('tad');
    okRecompensa(count($perguntas) === 5, 'cinco perguntas, inclusive a de codigo');
    foreach ([0 => 5, 1 => 15, 3 => 35, 5 => 75] as $acertos => $esperado) {
        $respostas = [];
        foreach ($perguntas as $indice => $pergunta) {
            foreach ($pergunta['alternativas'] as $alternativa) {
                if ((bool) $alternativa['correta'] === ($indice < $acertos)) {
                    $respostas[$pergunta['id_pergunta']] = $alternativa['id_alternativa']; break;
                }
            }
        }
        $saldoAntes = $moeda->obterSaldo(1);
        $id = $quiz->concluirTentativa(1, 'tad', $respostas);
        $tentativa = $quiz->buscarTentativa($id, 1);
        $historico = $moeda->listarHistorico(1);
        okRecompensa((int) $tentativa['total_acertos'] === $acertos && (int) $tentativa['moedas_ganhas'] === $esperado, "formula com $acertos acertos");
        okRecompensa($moeda->obterSaldo(1) === $saldoAntes + $esperado && $historico[0]['origem'] === 'quiz'
            && $historico[0]['tipo'] === 'credito' && (int) $historico[0]['valor'] === $esperado
            && str_contains($historico[0]['descricao'], 'TAD'), 'saldo e transacao da tentativa');
        okRecompensa(count($tentativa['respostas']) === 5, 'cinco respostas persistidas');
    }
    okRecompensa(Quiz::calcularRecompensa(5, 0) === 5 && Quiz::calcularRecompensa(5, 5) === 75, 'calculo centralizado');
    foreach ([[0, 0], [5, -1], [5, 6]] as [$total, $acertos]) {
        try { Quiz::calcularRecompensa($total, $acertos); throw new RuntimeException('Aceitou resultado invalido'); }
        catch (InvalidArgumentException $erro) { okRecompensa(true, 'resultado invalido rejeitado'); }
    }
    $antigo = $quiz->salvarTentativa(1, 'tad', $respostas);
    okRecompensa((int) $quiz->buscarTentativa($antigo, 1)['moedas_ganhas'] === 0, 'tentativa antiga sem pagamento retroativo');
    $saldo = $moeda->obterSaldo(1);
    $totalTentativas = count($quiz->buscarDesempenho(1)['tentativas']);
    try { $quiz->concluirTentativa(1, 'tad', []); throw new RuntimeException('Aceitou quiz incompleto'); }
    catch (InvalidArgumentException $erro) { okRecompensa($moeda->obterSaldo(1) === $saldo, 'quiz incompleto sem credito'); }
    $db->query("CREATE TRIGGER falha_quiz_credito BEFORE INSERT ON transacao_moeda FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'falha simulada'");
    try { $quiz->concluirTentativa(1, 'tad', $respostas); throw new RuntimeException('Aceitou falha de credito'); }
    catch (mysqli_sql_exception $erro) {
        okRecompensa($moeda->obterSaldo(1) === $saldo && count($quiz->buscarDesempenho(1)['tentativas']) === $totalTentativas,
            'falha do credito reverte tentativa e saldo');
    }
    $db->query('DROP TRIGGER falha_quiz_credito');
    $historicoAntigo = array_values(array_filter($quiz->buscarDesempenho(1)['tentativas'], fn($linha) => (int) $linha['id_tentativa'] === $antigo));
    okRecompensa(count($historicoAntigo) === 1 && (int) $historicoAntigo[0]['moedas_ganhas'] === 0, 'desempenho distingue historico antigo');
    $pastaSessao = __DIR__ . '/.quiz_sessoes_' . bin2hex(random_bytes(8));
    mkdir($pastaSessao);
    $idSessao = 'quiz' . bin2hex(random_bytes(12));
    $token = bin2hex(random_bytes(32));
    session_save_path($pastaSessao);
    session_id($idSessao);
    session_start();
    $_SESSION['quiz_tentativa_atual'] = [
        'id_usuario' => 1, 'slug' => 'tad', 'token' => $token, 'iniciada_em' => time(),
        'perguntas' => array_map(fn($p) => (int) $p['id_pergunta'], $perguntas),
        'habilidades' => [], 'eliminadas' => [], 'dicas_reveladas' => [], 'resumo_revelado' => null,
    ];
    session_write_close();
    $antesConcorrencia = $moeda->obterSaldo(1);
    $processos = [];
    for ($i = 0; $i < 2; $i++) {
        $processo = proc_open([PHP_BINARY, __FILE__, 'worker', $banco, $pastaSessao, $idSessao, $token],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        if (!is_resource($processo)) throw new RuntimeException('Falha ao iniciar worker de Quiz.');
        fclose($pipes[0]);
        $processos[] = [$processo, $pipes];
    }
    $resultados = [];
    foreach ($processos as [$processo, $pipes]) {
        $resultados[] = trim(stream_get_contents($pipes[1]));
        $erro = stream_get_contents($pipes[2]);
        fclose($pipes[1]); fclose($pipes[2]);
        okRecompensa(proc_close($processo) === 0 && $erro === '', 'worker concorrente sem erro');
    }
    sort($resultados);
    okRecompensa($resultados === ['pago', 'rejeitado'] && $moeda->obterSaldo(1) === $antesConcorrencia + 75,
        'duas finalizacoes simultaneas geram um credito');
    unlink($pastaSessao . '/sess_' . $idSessao);
    rmdir($pastaSessao);
    echo "PASSOU: recompensa do Quiz.\n";
} finally {
    $db->rollback();
    if ($criado) sqlRecompensa($db, 'DROP DATABASE `' . $banco . '`');
    $db->close();
}
