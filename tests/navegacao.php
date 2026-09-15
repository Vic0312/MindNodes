<?php
if (PHP_SAPI !== 'cli') exit(1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli('127.0.0.1', getenv('MINDNODES_TEST_USER') ?: 'root', getenv('MINDNODES_TEST_PASSWORD') ?: '', '', (int) (getenv('MINDNODES_TEST_PORT') ?: 3306));
$db->set_charset('utf8mb4');
$banco = 'mindnodes_test_http_' . bin2hex(random_bytes(8));
$criado = false;
$servidor = null;
$canal = null;
$sessoes = sys_get_temp_dir() . '/mindnodes_nav_' . bin2hex(random_bytes(8));
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
    $servidor = proc_open($comando, [0 => ['pipe', 'r'], 1 => ['file', $sessoes . '/servidor.log', 'a'], 2 => ['file', $sessoes . '/servidor.log', 'a']], $pipes, $raiz, $ambiente);
    if (!is_resource($servidor)) throw new RuntimeException('Servidor HTTP nao iniciou.');
    fclose($pipes[0]);
    $base = 'http://127.0.0.1:' . $porta;
    $canal = curl_init();
    curl_setopt_array($canal, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEFILE => '', CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 5]);
    $pedir = function ($caminho, $post = null, $json = false) use ($canal, $base) {
        curl_setopt($canal, CURLOPT_URL, $base . $caminho);
        curl_setopt($canal, CURLOPT_POST, $post !== null);
        curl_setopt($canal, CURLOPT_HTTPHEADER, $json ? ['Accept: application/json'] : []);
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
    [$corpo, $codigo] = $pedir('/view/home.php');
    if ($codigo !== 200 || str_contains($corpo, 'Warning') || str_contains($corpo, 'data-saldo-navegacao')) throw new RuntimeException('Home visitante incorreta');
    foreach (['Home', 'Conteúdos', 'Exemplos', 'Login', 'Cadastro'] as $texto) if (!str_contains($corpo, '>' . $texto . '</a>')) throw new RuntimeException('Menu visitante: ' . $texto);
    $protegidas = ['perfil', 'quiz', 'desempenho', 'loja', 'avatar', 'estruturas', 'exemplos', 'fila_fifo', 'fila_prioridade', 'pilha_encadeada', 'simulador'];
    foreach ($protegidas as $rota) {
        [, $codigo] = $pedir('/view/' . $rota . '.php');
        if ($codigo !== 302) throw new RuntimeException('Rota desprotegida: ' . $rota);
    }
    foreach (['sobre', 'login', 'cadastrar_usuario', 'recuperar_senha'] as $rota) {
        [, $codigo] = $pedir('/view/' . $rota . '.php');
        if ($codigo !== 200) throw new RuntimeException('Pagina publica bloqueada: ' . $rota);
    }
    [, $codigo, $destino] = $pedir('/index.php');
    if ($codigo !== 302 || !str_ends_with($destino, '/view/home.php')) throw new RuntimeException('Entrada publica quebrada');
    echo "OK: visitante, entrada publica, menu e rotas protegidas.\n";

    [, $codigo] = $pedir('/processamento/processamento.php', ['inputEmailLog' => 'maria@gmail.com', 'inputSenhaLog' => '1234']);
    if ($codigo !== 302) throw new RuntimeException('Login falhou');
    $saldoBanco = fn() => (int) $db->query('SELECT moedas FROM usuario WHERE id_usuario = 1')->fetch_assoc()['moedas'];
    $verSaldo = function () use ($pedir, $saldoBanco) {
        foreach (['home', 'perfil', 'loja', 'avatar', 'quiz', 'desempenho'] as $rota) {
            [$html, $status] = $pedir('/view/' . $rota . '.php');
            if ($status !== 200 || !str_contains($html, 'data-saldo-navegacao>' . $saldoBanco() . ' moedas')) throw new RuntimeException('Saldo desatualizado: ' . $rota);
            if ($rota === 'perfil' && !str_contains($html, 'data-saldo-perfil>' . $saldoBanco() . '</strong>')) throw new RuntimeException('Saldo perfil incorreto');
        }
    };
    $verSaldo();
    [$home] = $pedir('/view/home.php');
    if (!str_contains($home, 'Olá, Maria!')) throw new RuntimeException('Nome incorreto na Home');
    $dom = new DOMDocument(); @$dom->loadHTML('<?xml encoding="UTF-8">' . $home); $xp = new DOMXPath($dom);
    if ($xp->query('//details[@class="mn-estruturas"]//a')->length !== 6) throw new RuntimeException('Faltam estruturas');
    foreach ($xp->query('//header//a') as $link) {
        $href = $link->getAttribute('href');
        if (str_contains($href, 'logout')) continue;
        [$html, $status] = $pedir('/view/' . $href);
        if ($status !== 200 || str_contains($html, 'Fatal error')) throw new RuntimeException('Link quebrado: ' . $href);
        if (str_contains($href, '#')) {
            $alvo = new DOMDocument(); @$alvo->loadHTML('<?xml encoding="UTF-8">' . $html);
            if (!$alvo->getElementById(explode('#', $href)[1])) throw new RuntimeException('Ancora quebrada: ' . $href);
        }
    }
    [$perfil] = $pedir('/view/perfil.php');
    $hash = $db->query('SELECT senha FROM usuario WHERE id_usuario = 1')->fetch_assoc()['senha'];
    if (str_contains($perfil, $hash) || !str_contains($perfil, 'maria@gmail.com')) throw new RuntimeException('Dados do perfil incorretos');
    $edicao = ['acao' => 'editarPerfil', 'inputNomePerfil' => 'Maria <Teste>', 'inputSobrenomePerfil' => 'Brito', 'inputEmailPerfil' => 'maria@gmail.com', 'inputTelefonePerfil' => '18997289078', 'inputSenhaPerfil' => ''];
    [, $codigo, $destino] = $pedir('/processamento/processamento.php', $edicao);
    if ($codigo !== 302 || !str_contains($destino, 'sucesso=1')) throw new RuntimeException('Edicao falhou');
    [$perfil] = $pedir('/view/perfil.php?sucesso=1');
    if (!str_contains($perfil, 'Maria &lt;Teste&gt;') || str_contains($perfil, 'Maria <Teste>') || !str_contains($perfil, 'Perfil atualizado com sucesso.')) throw new RuntimeException('Escape/flash perfil falhou');
    [, , $destino] = $pedir('/processamento/processamento.php', array_merge($edicao, ['inputEmailPerfil' => 'invalido']));
    if (!str_contains($destino, 'erro=campos')) throw new RuntimeException('Validacao perfil falhou');
    if ($db->query('SELECT senha FROM usuario WHERE id_usuario = 1')->fetch_assoc()['senha'] !== $hash) throw new RuntimeException('Senha vazia mudou hash');
    echo "OK: links, seis estruturas, dados do perfil, edicao, validacao e escape.\n";

    require_once __DIR__ . '/../model/Moeda.php';
    $moeda = new Moeda($db);
    if ($saldoBanco() < 200) $moeda->creditar(1, 200 - $saldoBanco(), 'bonus', 'Preparacao do teste');
    [$loja] = $pedir('/view/loja.php');
    preg_match('~name="csrf" value="([a-f0-9]{64})"~', $loja, $token);
    $db->query('DELETE FROM usuario_item WHERE id_usuario = 1 AND id_item = 6');
    $antes = $saldoBanco();
    $preco = (int) $db->query('SELECT preco FROM item WHERE id_item = 6')->fetch_assoc()['preco'];
    [, $codigo] = $pedir('/processamento/processamento.php', ['acao' => 'comprarItem', 'csrf' => $token[1], 'id_item' => 6]);
    if ($codigo !== 303 || $saldoBanco() !== $antes - $preco) throw new RuntimeException('Compra nao debitou');
    $verSaldo();
    [$quiz] = $pedir('/view/quiz.php?assunto=tad');
    preg_match('~<form id="quiz-respostas".*?name="csrf" value="([a-f0-9]{64})"~s', $quiz, $token);
    $respostas = [];
    foreach ($db->query('SELECT qa.id_pergunta, qa.id_alternativa FROM quiz_alternativa qa JOIN quiz_pergunta qp ON qp.id_pergunta=qa.id_pergunta WHERE qa.correta=1 AND qp.id_assunto=1') as $r) $respostas[$r['id_pergunta']] = $r['id_alternativa'];
    $antes = $saldoBanco();
    [, $codigo] = $pedir('/processamento/processamento.php', ['acao' => 'salvarQuiz', 'assunto' => 'tad', 'csrf' => $token[1], 'respostas' => $respostas]);
    if ($codigo !== 303 || $saldoBanco() <= $antes) throw new RuntimeException('Quiz nao recompensou');
    $verSaldo();
    echo "OK: compra e recompensa reais; saldo atualizado na proxima carga das paginas.\n";

    [, $codigo] = $pedir('/processamento/logout.php');
    [, $protegido] = $pedir('/view/perfil.php');
    if ($codigo !== 302 || $protegido !== 302) throw new RuntimeException('Logout falhou');
    $cadastro = ['inputNome' => 'Teste', 'inputSobrenome' => 'Navegacao', 'inputCPF' => '98765432100', 'inputDataNasc' => '2000-01-01', 'inputTelefone' => '11999999999', 'inputEmail' => 'teste.nav@example.com', 'inputSenha' => 'TesteSenha123', 'inputConfirmarSenha' => 'TesteSenha123'];
    [, , $destino] = $pedir('/processamento/processamento.php', $cadastro);
    if (!str_contains($destino, 'cadastro=1')) throw new RuntimeException('Cadastro falhou: ' . $destino);
    [$recuperacao] = $pedir('/view/recuperar_senha.php');
    preg_match('~name="csrf" value="([a-f0-9]{64})"~', $recuperacao, $token);
    [, , $destino] = $pedir('/processamento/processamento.php', ['acao' => 'recuperarSenha', 'csrf' => $token[1], 'cpf' => '98765432100', 'email' => 'teste.nav@example.com', 'dataNascimento' => '2000-01-01']);
    if (!str_ends_with($destino, '/view/redefinir_senha.php')) throw new RuntimeException('Recuperacao falhou');
    [$redefinicao] = $pedir('/view/redefinir_senha.php');
    preg_match('~name="csrf" value="([a-f0-9]{64})"~', $redefinicao, $token);
    [, , $destino] = $pedir('/processamento/processamento.php', ['acao' => 'redefinirSenha', 'csrf' => $token[1], 'novaSenha' => 'NovaSenha123', 'confirmacao' => 'NovaSenha123']);
    if (!str_ends_with($destino, '/view/login.php')) throw new RuntimeException('Redefinicao falhou');
    [, , $destino] = $pedir('/processamento/processamento.php', ['inputEmailLog' => 'teste.nav@example.com', 'inputSenhaLog' => 'NovaSenha123']);
    if (!str_ends_with($destino, '/view/home.php')) throw new RuntimeException('Nova senha nao autenticou');
    echo "OK: logout, cadastro, recuperacao, redefinicao e login.\n";
    $pedir('/processamento/logout.php');
    $pedir('/processamento/processamento.php', ['inputEmailLog' => 'maria@gmail.com', 'inputSenhaLog' => '1234']);
    $cookie = '';
    foreach (curl_getinfo($canal, CURLINFO_COOKIELIST) as $linha) { $partes = explode("\t", $linha); if ($partes[5] === 'PHPSESSID') $cookie = $partes[6]; }
    $processo = proc_open(['node', __DIR__ . '/navegacao_navegador.cjs', $sessoes, $base, $cookie, (string) $saldoBanco()], [0 => ['pipe', 'r'], 1 => ['file', $sessoes . '/browser.log', 'a'], 2 => ['file', $sessoes . '/browser.log', 'a']], $pipes);
    fclose($pipes[0]);
    $resultado = proc_close($processo);
    $saida = file_get_contents($sessoes . '/browser.log');
    if ($resultado !== 0) throw new RuntimeException($saida);
    echo $saida;

} finally {
    if ($canal) curl_close($canal);
    if (is_resource($servidor)) { proc_terminate($servidor); proc_close($servidor); }
    $alvo = realpath($sessoes);
    $raizTmp = realpath(sys_get_temp_dir());
    if ($alvo && str_starts_with(strtolower($alvo), strtolower($raizTmp . DIRECTORY_SEPARATOR . 'mindnodes_nav_'))) {
        $arquivos = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($alvo, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($arquivos as $arquivo) {
            if ($arquivo->isDir()) rmdir($arquivo->getPathname());
            else unlink($arquivo->getPathname());
        }
        rmdir($alvo);
    }
    $db->select_db('mysql');
    if ($criado) $db->query('DROP DATABASE `' . $banco . '`');
    $db->close();
}
