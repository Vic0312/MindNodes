<?php
if (PHP_SAPI !== 'cli') exit(1);
require_once __DIR__ . '/../controller/QuizController.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
function okNovos($ok, $nome) { if (!$ok) throw new RuntimeException('FALHOU: ' . $nome); echo "OK: $nome\n"; }
function sqlNovos($db, $sql, $tipos = '', ...$valores) {
    $stmt = $db->prepare($sql);
    try { if ($tipos !== '') $stmt->bind_param($tipos, ...$valores); $stmt->execute(); return $stmt->field_count ? $stmt->get_result()->fetch_all(MYSQLI_ASSOC) : []; }
    finally { $stmt->close(); }
}
$db = new mysqli('127.0.0.1', getenv('MINDNODES_TEST_USER') ?: 'root', getenv('MINDNODES_TEST_PASSWORD') ?: '', '', (int) (getenv('MINDNODES_TEST_PORT') ?: 3306));
$db->set_charset('utf8mb4');
$banco = 'mindnodes_test_novos_' . bin2hex(random_bytes(8));
$criado = false;
try {
    sqlNovos($db, 'CREATE DATABASE `' . $banco . '` CHARACTER SET utf8mb4');
    $criado = true;
    $db->select_db($banco);
    $db->multi_query(file_get_contents(__DIR__ . '/../mindnode.sql'));
    do { if ($r = $db->store_result()) $r->free(); } while ($db->more_results() && $db->next_result());
    $_SESSION = ['estaLogado' => true, 'usuario_id' => 1];
    $quiz = new Quiz($db);
    $avatar = new Avatar($db);
    $controller = new QuizController($quiz, new QuizHabilidades($quiz, $avatar));
    $assuntos = $controller->listarAssuntosQuiz();
    okNovos(array_column($assuntos, 'slug') === ['tad', 'lista-simples', 'lista-dupla', 'fila-fifo', 'fila-prioridade', 'pilha-encadeada'], 'seis assuntos em ordem');
    okNovos(array_map('intval', array_column($assuntos, 'total_perguntas')) === [4, 4, 4, 6, 6, 6], 'assuntos antigos preservados e novos com seis perguntas');
    $inventario = new Inventario($db);
    foreach ([4, 5, 6] as $item) { $inventario->adicionarItem(1, $item); $avatar->equiparItem(1, $item); }
    $saldo = (new Moeda($db))->obterSaldo(1);
    foreach (['fila-fifo', 'fila-prioridade', 'pilha-encadeada'] as $slug) {
        $perguntas = $controller->buscarPerguntasQuiz($slug);
        okNovos(count($perguntas) === 6 && count(array_filter($perguntas, fn($p) => $p['tipo'] === 'teorica')) === 3
            && count(array_filter($perguntas, fn($p) => $p['tipo'] === 'codigo')) === 3, "$slug: 3 teoricas e 3 codigo");
        foreach ($perguntas as $pergunta) {
            okNovos(trim($pergunta['dica']) !== '' && trim($pergunta['explicacao']) !== ''
                && count($pergunta['alternativas']) === 4
                && count(array_filter($pergunta['alternativas'], fn($a) => $a['correta'])) === 1
                && ($pergunta['tipo'] === 'teorica' ? $pergunta['codigo'] === null : str_contains($pergunta['codigo'], "\n")),
                "$slug: pergunta {$pergunta['id_pergunta']} completa");
        }
        $estado = $controller->iniciarTentativaQuiz($slug);
        $dica = $controller->usarDicaQuiz($estado['token'], $perguntas[3]['id_pergunta']);
        $eliminada = $controller->eliminarAlternativaQuiz($estado['token'], $perguntas[4]['id_pergunta']);
        $resumo = $controller->usarResumoQuiz($estado['token']);
        $alternativaEliminada = array_values(array_filter($perguntas[4]['alternativas'], fn($a) => (int) $a['id_alternativa'] === $eliminada['id_alternativa']))[0];
        okNovos($dica['restante'] === 0 && $eliminada['restante'] === 0 && !$alternativaEliminada['correta']
            && $resumo['restante'] === 0 && $resumo['conteudo'] !== '', "$slug: tres habilidades funcionam");
        $respostas = [];
        foreach ($perguntas as $pergunta) foreach ($pergunta['alternativas'] as $alternativa) if ($alternativa['correta']) $respostas[$pergunta['id_pergunta']] = $alternativa['id_alternativa'];
        $idTentativa = $controller->finalizarTentativaQuiz($estado['token'], $slug, $respostas);
        $revisao = $controller->buscarTentativaQuiz($idTentativa, 1);
        $saldo += 85;
        okNovos((int) $revisao['total_acertos'] === 6 && (int) $revisao['moedas_ganhas'] === 85
            && (new Moeda($db))->obterSaldo(1) === $saldo, "$slug: correcao e recompensa integral");
        okNovos(count($revisao['respostas']) === 6 && count(array_filter($revisao['respostas'], fn($r) => $r['tipo'] === 'codigo' && $r['codigo'] !== '' && $r['explicacao'] !== '')) === 3,
            "$slug: revisao de codigo e explicacao");
    }
    $historico = $controller->buscarDesempenhoUsuario(1)['tentativas'];
    okNovos(count(array_filter($historico, fn($t) => (int) $t['moedas_ganhas'] === 85
        && in_array($t['slug'], ['fila-fifo', 'fila-prioridade', 'pilha-encadeada'], true))) === 3,
        'desempenho registra tres novos assuntos');
    $empate = $controller->buscarPerguntasQuiz('fila-prioridade')[1];
    okNovos(str_contains($empate['enunciado'], 'A(1)') && str_contains($empate['explicacao'], 'A antes de B'), 'empate preserva FIFO');
    foreach ($controller->buscarPerguntasQuiz('pilha-encadeada') as $pergunta) {
        okNovos(!str_contains(strtolower($pergunta['codigo'] ?? ''), '[]') && !str_contains(strtolower($pergunta['codigo'] ?? ''), 'vetor'), 'pilha de nos, sem vetor no codigo');
    }
    echo "PASSOU: tres novos assuntos do Quiz.\n";
} finally {
    $db->rollback();
    if ($criado) sqlNovos($db, 'DROP DATABASE `' . $banco . '`');
    $db->close();
}
