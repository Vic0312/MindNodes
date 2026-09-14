<?php
if (PHP_SAPI !== 'cli') exit(1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli('127.0.0.1', getenv('MINDNODES_TEST_USER') ?: 'root', getenv('MINDNODES_TEST_PASSWORD') ?: '', '', (int) (getenv('MINDNODES_TEST_PORT') ?: 3306));
$db->set_charset('utf8mb4');
$banco = 'mindnodes_test_http_' . bin2hex(random_bytes(8));
$criado = false;
$servidor = null;
$canal = null;
$sessoes = __DIR__ . '/.avatar_sessions_' . bin2hex(random_bytes(8));
try {
    $db->query('CREATE DATABASE `' . $banco . '` CHARACTER SET utf8mb4');
    $criado = true;
    $db->select_db($banco);
    $db->multi_query(file_get_contents(__DIR__ . '/../mindnode.sql'));
    do { if ($r = $db->store_result()) $r->free(); } while ($db->more_results() && $db->next_result());
    $portaSocket = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
    if (!$portaSocket) throw new RuntimeException($errstr);
    $endereco = stream_socket_get_name($portaSocket, false);
    $porta = (int) substr(strrchr($endereco, ':'), 1);
    fclose($portaSocket);
    $raiz = realpath(__DIR__ . '/..');
    if (!mkdir($sessoes)) throw new RuntimeException('Diretorio de sessoes indisponivel.');
    $comando = [PHP_BINARY, '-d', 'session.save_path=' . $sessoes, '-S', '127.0.0.1:' . $porta, '-t', $raiz];
    $ambiente = array_merge(getenv(), ['MINDNODES_DB' => $banco]);
    $servidor = proc_open($comando, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $raiz, $ambiente);
    if (!is_resource($servidor)) throw new RuntimeException('Servidor HTTP nao iniciou.');
    fclose($pipes[0]);
    $base = 'http://127.0.0.1:' . $porta;
    $canal = curl_init();
    curl_setopt_array($canal, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEFILE => '', CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 5]);
    $pedir = function ($caminho, $post = null) use ($canal, $base) {
        curl_setopt($canal, CURLOPT_URL, $base . $caminho);
        curl_setopt($canal, CURLOPT_POST, $post !== null);
        if ($post !== null) curl_setopt($canal, CURLOPT_POSTFIELDS, http_build_query($post));
        $corpo = curl_exec($canal);
        if ($corpo === false) throw new RuntimeException(curl_error($canal));
        return [$corpo, curl_getinfo($canal, CURLINFO_HTTP_CODE), curl_getinfo($canal, CURLINFO_REDIRECT_URL)];
    };
    $pronto = false;
    for ($i = 0; $i < 30; $i++) {
        try { [$corpo, $codigo] = $pedir('/view/avatar.php'); $pronto = true; break; }
        catch (RuntimeException $erro) { usleep(100000); }
    }
    if (!$pronto) throw new RuntimeException('Servidor HTTP indisponivel.');
    if ($codigo !== 302) throw new RuntimeException('Acesso anonimo nao redirecionou.');
    echo "OK: acesso anonimo redirecionado\n";
    [$corpo, $codigo] = $pedir('/view/loja.php');
    if ($codigo !== 302) throw new RuntimeException('Loja anonima nao redirecionou.');
    echo "OK: loja anonima redirecionada\n";
    [$corpo, $codigo] = $pedir('/processamento/processamento.php', ['inputEmailLog' => 'maria@gmail.com', 'inputSenhaLog' => '1234']);
    if ($codigo !== 302) throw new RuntimeException('Login falhou.');
    $db->query('INSERT INTO usuario_item (id_usuario, id_item) VALUES (1, 5)');
    [$corpo, $codigo] = $pedir('/view/avatar.php');
    if ($codigo !== 200 || !str_contains($corpo, 'Meu Avatar') || !str_contains($corpo, 'Cabelo Padrão') || !str_contains($corpo, 'Nenhum acessório disponível.')) throw new RuntimeException('Pagina autenticada incompleta.');
    if (!preg_match('~avatar-base.*?avatar-roupa.*?avatar-rosto.*?avatar-cabelo~s', $corpo)
        || !str_contains($corpo, '../img/avatar/base/node_base.png')
        || !str_contains($corpo, '../img/avatar/roupa/roupa_padrao.png')
        || !str_contains($corpo, '../img/avatar/rosto/rosto_padrao.png')
        || !str_contains($corpo, '../img/avatar/cabelo/cabelo_padrao.png')) throw new RuntimeException('Camadas ou URLs incorretas.');
    if (str_contains($corpo, 'Boné FIFO')) throw new RuntimeException('Item nao possuido aparece na pagina.');
    if (!preg_match('~name="csrf" value="([a-f0-9]{64})"~', $corpo, $partes)) throw new RuntimeException('CSRF ausente.');
    $token = $partes[1];
    echo "OK: pagina autenticada, camadas e inventario\n";
    [$corpo, $codigo] = $pedir('/processamento/processamento.php', ['acao' => 'equiparAvatar', 'csrf' => $token, 'id_item' => 4, 'id_usuario' => 2]);
    if ($codigo !== 303) throw new RuntimeException('POST negado sem redirecionamento.');
    [$corpo] = $pedir('/view/avatar.php');
    if (!str_contains($corpo, 'Você não possui esse item.')) throw new RuntimeException('Item de terceiro nao foi negado.');
    echo "OK: propriedade validada com usuario da sessao\n";
    $db->query('INSERT INTO usuario_item (id_usuario, id_item) VALUES (1, 4)');
    [$corpo, $codigo] = $pedir('/processamento/processamento.php', ['acao' => 'equiparAvatar', 'csrf' => $token, 'id_item' => 4]);
    if ($codigo !== 303) throw new RuntimeException('POST valido sem 303.');
    [$corpo, $codigo] = $pedir('/view/avatar.php');
    if ($codigo !== 200 || !str_contains($corpo, 'Boné FIFO') || !str_contains($corpo, 'Item equipado com sucesso.')) throw new RuntimeException('Equipamento nao refletido.');
    if (!preg_match('~<article class="avatar-item equipado">.*?Boné FIFO.*?avatar-equipped-badge~s', $corpo)) throw new RuntimeException('Item equipado sem destaque.');
    [$corpo] = $pedir('/view/avatar.php');
    if (str_contains($corpo, 'Item equipado com sucesso.')) throw new RuntimeException('Flash repetido no refresh.');
    $atual = $db->query('SELECT id_cabelo, id_rosto, id_roupa FROM avatar_usuario WHERE id_usuario = 1')->fetch_assoc();
    if ((int) $atual['id_cabelo'] !== 4 || (int) $atual['id_rosto'] !== 2 || (int) $atual['id_roupa'] !== 3) throw new RuntimeException('Slot incorreto.');
    echo "OK: equipamento, PRG e refresh sem reenvio\n";
    $db->query('UPDATE usuario SET moedas = 200 WHERE id_usuario = 1');
    [$corpo, $codigo] = $pedir('/view/loja.php');
    if ($codigo !== 200 || !str_contains($corpo, 'Seu saldo') || !str_contains($corpo, '<strong>200</strong>')
        || !str_contains($corpo, 'Camiseta Stack') || !str_contains($corpo, 'Adquirido')
        || str_contains($corpo, 'Cabelo Padrão')) throw new RuntimeException('Loja autenticada incompleta.');
    if (!preg_match('~name="csrf" value="([a-f0-9]{64})"~', $corpo, $partes)) throw new RuntimeException('CSRF da Loja ausente.');
    $tokenLoja = $partes[1];
    echo "OK: loja autenticada e saldo\n";
    [$corpo, $codigo] = $pedir('/processamento/processamento.php', ['acao' => 'comprarItem', 'csrf' => $tokenLoja, 'id_item' => 6, 'preco' => 1, 'id_usuario' => 2]);
    if ($codigo !== 303) throw new RuntimeException('Compra sem PRG.');
    [$corpo, $codigo] = $pedir('/view/loja.php');
    if ($codigo !== 200 || !str_contains($corpo, 'Camiseta Stack comprado com sucesso.') || !str_contains($corpo, '<strong>50</strong>')) throw new RuntimeException('Compra HTTP nao refletida.');
    [$corpo] = $pedir('/view/loja.php');
    if (str_contains($corpo, 'Camiseta Stack comprado com sucesso.')) throw new RuntimeException('Flash da Loja repetido.');
    $historico = $db->query("SELECT tipo, valor, origem, descricao FROM transacao_moeda WHERE id_usuario = 1 ORDER BY id_transacao DESC LIMIT 1")->fetch_assoc();
    $roupa = $db->query('SELECT id_roupa FROM avatar_usuario WHERE id_usuario = 1')->fetch_assoc();
    if ($historico['tipo'] !== 'debito' || (int) $historico['valor'] !== 150 || $historico['origem'] !== 'loja'
        || $historico['descricao'] !== 'Compra: Camiseta Stack' || (int) $roupa['id_roupa'] !== 3) throw new RuntimeException('Compra ou Avatar inconsistente.');
    [$corpo] = $pedir('/view/avatar.php');
    if (!str_contains($corpo, 'Camiseta Stack')) throw new RuntimeException('Item comprado ausente do Avatar.');
    echo "OK: compra usa preco do banco, entrega, registra e nao equipa\n";
    [$corpo, $codigo] = $pedir('/processamento/processamento.php', ['acao' => 'comprarItem', 'csrf' => $tokenLoja, 'id_item' => 6]);
    [$corpo] = $pedir('/view/loja.php');
    if ($codigo !== 303 || !str_contains($corpo, 'Você já possui este item.') || !str_contains($corpo, '<strong>50</strong>')) throw new RuntimeException('Compra repetida cobrada.');
    $db->query('DELETE FROM usuario_item WHERE id_usuario = 1 AND id_item = 5');
    [$corpo, $codigo] = $pedir('/processamento/processamento.php', ['acao' => 'comprarItem', 'csrf' => $tokenLoja, 'id_item' => 5]);
    [$corpo] = $pedir('/view/loja.php');
    if ($codigo !== 303 || !str_contains($corpo, 'Saldo insuficiente.') || !str_contains($corpo, '<strong>50</strong>')) throw new RuntimeException('Saldo insuficiente nao tratado.');
    echo "OK: compra repetida e saldo insuficiente sem novo debito\n";
} finally {
    if ($canal) curl_close($canal);
    if (is_resource($servidor)) { proc_terminate($servidor); foreach ([$pipes[1], $pipes[2]] as $pipe) fclose($pipe); proc_close($servidor); }
    if (is_dir($sessoes)) { foreach (glob($sessoes . '/sess_*') as $arquivo) unlink($arquivo); rmdir($sessoes); }
    $db->select_db('mysql');
    if ($criado) $db->query('DROP DATABASE `' . $banco . '`');
    $db->close();
}
