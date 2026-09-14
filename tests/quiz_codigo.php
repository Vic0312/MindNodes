<?php
if (PHP_SAPI !== 'cli') exit(1);
require_once __DIR__ . '/../controller/QuizController.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
function conferirQuiz($ok, $nome) { if (!$ok) throw new RuntimeException('FALHOU: ' . $nome); echo "OK: $nome\n"; }
function consultarQuiz($db, $sql, $tipos = '', ...$valores) {
    $stmt = $db->prepare($sql);
    try { if ($tipos !== '') $stmt->bind_param($tipos, ...$valores); $stmt->execute(); return $stmt->field_count ? $stmt->get_result()->fetch_all(MYSQLI_ASSOC) : []; }
    finally { $stmt->close(); }
}
$db = new mysqli('127.0.0.1', getenv('MINDNODES_TEST_USER') ?: 'root', getenv('MINDNODES_TEST_PASSWORD') ?: '', '', (int) (getenv('MINDNODES_TEST_PORT') ?: 3306));
$db->set_charset('utf8mb4');
$banco = 'mindnodes_test_quiz_' . bin2hex(random_bytes(8));
$criado = false;
try {
    consultarQuiz($db, 'CREATE DATABASE `' . $banco . '` CHARACTER SET utf8mb4');
    $criado = true;
    $db->select_db($banco);
    $db->multi_query(file_get_contents(__DIR__ . '/../mindnode.sql'));
    do { if ($r = $db->store_result()) $r->free(); } while ($db->more_results() && $db->next_result());
    $quiz = new QuizController(new Quiz($db));
    $assuntos = $quiz->listarAssuntosQuiz();
    conferirQuiz(count($assuntos) === 6 && array_map('intval', array_column($assuntos, 'total_perguntas')) === [4, 4, 4, 6, 6, 6], 'seis assuntos, mantendo os tres antigos');
    foreach (['tad' => 10, 'lista-simples' => 11, 'lista-dupla' => 12] as $slug => $idCodigo) {
        $perguntas = $quiz->buscarPerguntasQuiz($slug);
        conferirQuiz(count($perguntas) === 4 && $perguntas[0]['tipo'] === 'teorica' && $perguntas[0]['codigo'] === null, 'teoricas antigas em ' . $slug);
        $codigo = array_values(array_filter($perguntas, fn($p) => (int) $p['id_pergunta'] === $idCodigo))[0];
        conferirQuiz($codigo['tipo'] === 'codigo' && count($codigo['alternativas']) === 3 && $codigo['codigo'] !== '' && $codigo['dica'] !== '', 'campos e alternativas de codigo em ' . $slug);
        conferirQuiz(str_contains($codigo['codigo'], "\n") && str_contains($codigo['codigo'], '    '), 'quebras e indentacao em ' . $slug);
    }
    $perguntas = $quiz->buscarPerguntasQuiz('tad');
    $respostas = [];
    foreach ($perguntas as $pergunta) {
        foreach ($pergunta['alternativas'] as $alternativa) if ($alternativa['correta']) $respostas[$pergunta['id_pergunta']] = $alternativa['id_alternativa'];
    }
    $tentativa = $quiz->salvarTentativaQuiz(1, 'tad', $respostas);
    $revisao = $quiz->buscarTentativaQuiz($tentativa, 1);
    conferirQuiz((int) $revisao['total_perguntas'] === 4 && (int) $revisao['total_acertos'] === 4 && count($revisao['respostas']) === 4, 'acerto e persistencia de respostas');
    $respostaCodigo = array_values(array_filter($revisao['respostas'], fn($r) => $r['tipo'] === 'codigo'))[0];
    conferirQuiz($respostaCodigo['codigo'] === $perguntas[3]['codigo'] && $respostaCodigo['explicacao'] !== '' && (int) $respostaCodigo['acertou'] === 1, 'revisao preserva codigo e explicacao');
    $errada = $respostas;
    $errada[10] = 29;
    $tentativaErrada = $quiz->salvarTentativaQuiz(1, 'tad', $errada);
    $revisaoErrada = $quiz->buscarTentativaQuiz($tentativaErrada, 1);
    conferirQuiz((int) $revisaoErrada['total_acertos'] === 3 && (int) $revisaoErrada['respostas'][3]['acertou'] === 0, 'erro de codigo contado normalmente');
    conferirQuiz((int) consultarQuiz($db, 'SELECT moedas_ganhas FROM quiz_tentativa WHERE id_tentativa = ?', 'i', $tentativa)[0]['moedas_ganhas'] === 0, 'sem recompensa em moedas');
    $db->query("ALTER TABLE quiz_pergunta MODIFY tipo enum('teorica','codigo') NULL DEFAULT 'teorica'");
    consultarQuiz($db, 'UPDATE quiz_pergunta SET tipo = NULL WHERE id_pergunta = ?', 'i', 1);
    conferirQuiz($quiz->buscarPerguntasQuiz('tad')[0]['tipo'] === 'teorica', 'tipo NULL tratado como teorica');
    $codigoEspecial = "if (a < b && texto != \"<> &\")\n{\n    Console.WriteLine(texto);\n}";
    consultarQuiz($db, 'UPDATE quiz_pergunta SET codigo = ? WHERE id_pergunta = ?', 'si', $codigoEspecial, 10);
    conferirQuiz($quiz->buscarPerguntasQuiz('tad')[3]['codigo'] === $codigoEspecial, 'codigo especial preservado como texto');
    echo "PASSOU: Quiz teorico e codigo.\n";
} finally {
    $db->rollback();
    if ($criado) consultarQuiz($db, 'DROP DATABASE `' . $banco . '`');
    $db->close();
}
