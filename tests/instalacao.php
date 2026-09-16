<?php
if (PHP_SAPI !== 'cli') exit(1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function verificarInstalacao($condicao, $mensagem) {
    if (!$condicao) throw new RuntimeException('FALHOU: ' . $mensagem);
    echo "OK: $mensagem\n";
}

function rejeitarInstalacao($acao, $codigo, $mensagem) {
    try { $acao(); } catch (mysqli_sql_exception $erro) {
        verificarInstalacao($erro->getCode() === $codigo, $mensagem);
        return;
    }
    throw new RuntimeException('FALHOU: ' . $mensagem);
}

$db = new mysqli('127.0.0.1', getenv('MINDNODES_TEST_USER') ?: 'root', getenv('MINDNODES_TEST_PASSWORD') ?: '', '', (int) (getenv('MINDNODES_TEST_PORT') ?: 3306));
$db->set_charset('utf8mb4');
$banco = 'mindnodes_test_instalacao_' . bin2hex(random_bytes(8));
$criado = false;
try {
    $db->query('CREATE DATABASE `' . $banco . '` CHARACTER SET utf8mb4');
    $criado = true;
    $db->select_db($banco);
    $db->multi_query(file_get_contents(__DIR__ . '/../mindnode.sql'));
    do { if ($resultado = $db->store_result()) $resultado->free(); } while ($db->more_results() && $db->next_result());

    $tabelas = array_column($db->query('SHOW TABLES')->fetch_all(MYSQLI_NUM), 0);
    verificarInstalacao(count($tabelas) === 10, 'instalacao limpa cria as 10 tabelas');
    verificarInstalacao((int) $db->query('SELECT COUNT(*) AS total FROM quiz_assunto')->fetch_assoc()['total'] === 6,
        'seis assuntos iniciais');
    verificarInstalacao((int) $db->query('SELECT COUNT(*) AS total FROM item WHERE ativo = 1')->fetch_assoc()['total'] === 6
        && (int) $db->query("SELECT COUNT(*) AS total FROM item WHERE nome IN ('Cabelo Padrão', 'Rosto Padrão', 'Roupa Padrão')")->fetch_assoc()['total'] === 3,
        'catalogo e itens padrao iniciais');
    verificarInstalacao((int) $db->query('SELECT COUNT(*) AS total FROM usuario_item')->fetch_assoc()['total'] === 6
        && (int) $db->query('SELECT COUNT(*) AS total FROM avatar_usuario')->fetch_assoc()['total'] === 2,
        'inventario e Avatar iniciais');

    rejeitarInstalacao(fn() => $db->query("UPDATE usuario SET cpf = '22222222222' WHERE id_usuario = 1"), 1062, 'CPF unico');
    rejeitarInstalacao(fn() => $db->query("UPDATE usuario SET email = 'bru@gmail.com' WHERE id_usuario = 1"), 1062, 'email unico');
    rejeitarInstalacao(fn() => $db->query('INSERT INTO usuario_item (id_usuario, id_item) VALUES (1, 1)'), 1062,
        'inventario impede par duplicado');
    rejeitarInstalacao(fn() => $db->query('INSERT INTO usuario_item (id_usuario, id_item) VALUES (999999, 1)'), 1452,
        'FK impede item de usuario inexistente');
    rejeitarInstalacao(fn() => $db->query('INSERT INTO quiz_resposta (id_tentativa, id_pergunta, id_alternativa_correta) VALUES (999999, 1, 1)'), 1452,
        'FK impede resposta de tentativa inexistente');

    verificarInstalacao((int) $db->query('SELECT COUNT(*) AS total FROM usuario WHERE moedas < 0')->fetch_assoc()['total'] === 0
        && (int) $db->query('SELECT COUNT(*) AS total FROM usuario_item ui LEFT JOIN usuario u ON u.id_usuario = ui.id_usuario LEFT JOIN item i ON i.id_item = ui.id_item WHERE u.id_usuario IS NULL OR i.id_item IS NULL')->fetch_assoc()['total'] === 0
        && (int) $db->query('SELECT COUNT(*) AS total FROM quiz_resposta qr LEFT JOIN quiz_tentativa qt ON qt.id_tentativa = qr.id_tentativa WHERE qt.id_tentativa IS NULL')->fetch_assoc()['total'] === 0,
        'dados iniciais sem saldo negativo ou registros orfaos');
    echo "PASSOU: instalacao e integridade do banco temporario.\n";
} finally {
    if ($criado) $db->query('DROP DATABASE `' . $banco . '`');
    $db->close();
}
