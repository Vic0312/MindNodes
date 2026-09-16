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
    if ($codigo !== 302) throw new RuntimeException('Acesso anonimo nao redirecionou.');
    echo "OK: acesso anonimo redirecionado\n";
    [$corpo, $codigo] = $pedir('/view/fila_fifo.php');
    if ($codigo !== 302) throw new RuntimeException('Aula FIFO anonima nao redirecionou.');
    [$corpo, $codigo] = $pedir('/view/fila_prioridade.php');
    if ($codigo !== 302) throw new RuntimeException('Aula de prioridades anonima nao redirecionou.');
    [$corpo, $codigo] = $pedir('/view/loja.php');
    if ($codigo !== 302) throw new RuntimeException('Loja anonima nao redirecionou.');
    echo "OK: loja anonima redirecionada\n";
    [$corpo, $codigo] = $pedir('/processamento/processamento.php', ['inputEmailLog' => 'maria@gmail.com', 'inputSenhaLog' => '1234']);
    if ($codigo !== 302) throw new RuntimeException('Login falhou.');
    [$indiceEstruturas, $codigoIndice] = $pedir('/view/estruturas.php');
    if ($codigoIndice !== 200 || !str_contains($indiceEstruturas, 'TAD — Tipo Abstrato de Dados')
        || !str_contains($indiceEstruturas, 'Lista Simplesmente Encadeada')
        || !str_contains($indiceEstruturas, 'Lista Duplamente Encadeada')
        || !str_contains($indiceEstruturas, 'href="../view/fila_fifo.php"')
        || !str_contains($indiceEstruturas, 'href="../view/fila_prioridade.php"')) throw new RuntimeException('Indice de estruturas perdeu navegacao.');
    [$exemplos, $codigoExemplos] = $pedir('/view/exemplos.php?estrutura=simples');
    if ($codigoExemplos !== 200 || !str_contains($exemplos, 'Lista Simplesmente Encadeada')
        || !str_contains($exemplos, 'Lista Duplamente Encadeada')) throw new RuntimeException('Exemplos antigos nao carregaram.');
    [$aula, $codigoAula] = $pedir('/view/fila_fifo.php');
    foreach (['First In, First Out', 'inicio', 'fim', 'Proximo', 'public class No',
              'public class FilaEncadeada', 'public void Enfileirar', 'public int Desenfileirar',
              'public int Frente', 'public bool EstaVazia', 'fim.Proximo = novo',
              'fim = null', 'O(1)', 'O(n)', 'Vantagens', 'Desvantagens', 'Fila com vetor',
              'LIFO', 'Erros comuns', 'Enfileirar(10)', 'Enfileirar(20)', 'Enfileirar(30)',
              '../view/quiz.php?assunto=fila-fifo'] as $trechoAula) {
        if (!str_contains($aula, $trechoAula)) throw new RuntimeException('Aula FIFO incompleta: ' . $trechoAula);
    }
    if ($codigoAula !== 200 || !str_contains($aula, "\n        fim.Proximo = novo;")
        || !str_contains($aula, 'class="fila-diagrama"') || !str_contains($aula, 'class="fila-tabela"'))
        throw new RuntimeException('Codigo indentado ou componentes visuais da aula ausentes.');
    [$estiloAula, $codigoEstilo] = $pedir('/css/fila_fifo.css');
    if ($codigoEstilo !== 200 || !str_contains($estiloAula, 'overflow-x: auto') || !str_contains($estiloAula, '@media (max-width: 520px)'))
        throw new RuntimeException('Estilos responsivos da aula ausentes.');
    echo "OK: aula FIFO, indice, codigo C# e regras responsivas\n";
    [$aulaPrioridade, $statusPrioridade] = $pedir('/view/fila_prioridade.php');
    foreach (['menor número = maior prioridade', 'FIFO no empate', 'Bruno → Ana → Carla',
              'Valor', 'Prioridade', 'Proximo', 'public class No', 'public class FilaPrioridadeEncadeada',
              'public void Enfileirar', 'public int Desenfileirar', 'public int Frente', 'public bool EstaVazia',
              'atual.Proximo.Prioridade &lt;= prioridade', 'inicio = inicio.Proximo',
              '20 → 30 → 50', 'Enfileirar(A, P2)', 'Enfileirar(D, P1)', 'O(n)', 'O(1)',
              'Fila FIFO comum', 'estável', 'Vantagens', 'Desvantagens', 'Erros comuns',
              '../view/quiz.php?assunto=fila-prioridade'] as $trechoPrioridade) {
        if (!str_contains($aulaPrioridade, $trechoPrioridade)) throw new RuntimeException('Aula de prioridades incompleta: ' . $trechoPrioridade);
    }
    if ($statusPrioridade !== 200 || !str_contains($aulaPrioridade, "\n               atual.Proximo.Prioridade &lt;= prioridade")
        || substr_count($aulaPrioridade, 'class="fila-diagrama"') < 2)
        throw new RuntimeException('Codigo ou diagramas de prioridades ausentes.');
    [$cssPrioridade, $statusCssPrioridade] = $pedir('/css/fila_prioridade.css');
    if ($statusCssPrioridade !== 200 || !str_contains($cssPrioridade, '@media (max-width: 520px)')
        || !str_contains($cssPrioridade, 'overflow-x: auto')) throw new RuntimeException('CSS mobile de prioridades ausente.');
    echo "OK: aula de prioridades, regra estavel, navegacao e CSS mobile\n";
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
    [$corpo, $codigo] = $pedir('/processamento/processamento.php', ['acao' => 'equiparAvatar', 'id_item' => 4]);
    if ($codigo !== 303 || (int) $db->query('SELECT id_cabelo FROM avatar_usuario WHERE id_usuario = 1')->fetch_assoc()['id_cabelo'] !== 1)
        throw new RuntimeException('Avatar aceitou POST sem CSRF.');
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
    [$corpo, $codigo] = $pedir('/processamento/processamento.php', ['acao' => 'comprarItem', 'id_item' => 6]);
    if ($codigo !== 303 || (int) $db->query('SELECT moedas FROM usuario WHERE id_usuario = 1')->fetch_assoc()['moedas'] !== 200
        || (int) $db->query('SELECT COUNT(*) AS total FROM usuario_item WHERE id_usuario = 1 AND id_item = 6')->fetch_assoc()['total'] !== 0)
        throw new RuntimeException('Loja aceitou POST sem CSRF.');
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
    [$corpo, $codigo] = $pedir('/view/quiz.php?assunto=tad');
    foreach (['Fila Encadeada FIFO', 'Fila de Prioridades Encadeada FIFO', 'Pilha Encadeada'] as $tituloNovo) {
        if (!str_contains($corpo, $tituloNovo)) throw new RuntimeException('Assunto novo ausente da lista do Quiz.');
    }
    if ($codigo !== 200 || !str_contains($corpo, 'public interface IPilha') || !str_contains($corpo, 'void Empilhar(int valor);')
        || !str_contains($corpo, 'Questão 4') || str_contains($corpo, 'Observe que há apenas assinaturas de métodos.')) throw new RuntimeException('Quiz de codigo nao renderizou corretamente.');
    if (!preg_match('~<article class="quiz-card" id="questao-1">.*?</article>~s', $corpo, $primeira) || str_contains($primeira[0], 'codigo-questao')) throw new RuntimeException('Teorica exibiu bloco de codigo.');
    echo "OK: quiz teorico e codigo sem dica automatica\n";
    foreach (['fila-fifo', 'fila-prioridade', 'pilha-encadeada'] as $slugNovo) {
        [$paginaNova, $statusNovo] = $pedir('/view/quiz.php?assunto=' . $slugNovo);
        if ($statusNovo !== 200 || !str_contains($paginaNova, 'Questão 6') || substr_count($paginaNova, 'class="codigo-questao"') !== 3)
            throw new RuntimeException('Seis perguntas ou tres blocos de codigo ausentes em ' . $slugNovo);
    }
    echo "OK: tres novos assuntos com codigo renderizado\n";
    $codigoEspecial = "if (a < b && texto != \"<> &\")\n{\n    Console.WriteLine(texto);\n}";
    $stmt = $db->prepare('UPDATE quiz_pergunta SET codigo = ? WHERE id_pergunta = 10');
    $stmt->bind_param('s', $codigoEspecial); $stmt->execute(); $stmt->close();
    [$corpo] = $pedir('/view/quiz.php?assunto=tad');
    if (!str_contains($corpo, 'if (a &lt; b &amp;&amp; texto != &quot;&lt;&gt; &amp;&quot;)')
        || !str_contains($corpo, "\n    Console.WriteLine(texto);\n")) throw new RuntimeException('Escape ou formatacao do codigo falhou.');
    if (!preg_match('~<form id="quiz-respostas".*?name="csrf" value="([a-f0-9]{64})"~s', $corpo, $tokenQuiz)) throw new RuntimeException('Token da tentativa ausente.');
    $respostasQuiz = [];
    foreach ($db->query('SELECT id_pergunta, id_alternativa FROM quiz_alternativa WHERE correta = 1 AND id_pergunta IN (1,2,3,10)') as $linha) $respostasQuiz[$linha['id_pergunta']] = $linha['id_alternativa'];
    [$corpo, $codigo, $destino] = $pedir('/processamento/processamento.php', ['acao' => 'salvarQuiz', 'assunto' => 'tad', 'csrf' => $tokenQuiz[1], 'respostas' => $respostasQuiz]);
    if ($codigo !== 303 || !str_contains($destino, 'desempenho.php?tentativa=')) throw new RuntimeException('Tentativa de codigo nao salva.');
    $saldoQuiz = (int) $db->query('SELECT moedas FROM usuario WHERE id_usuario = 1')->fetch_assoc()['moedas'];
    if ($saldoQuiz !== 115 || (int) $db->query("SELECT COUNT(*) AS total FROM transacao_moeda WHERE id_usuario = 1 AND origem = 'quiz'")->fetch_assoc()['total'] !== 1)
        throw new RuntimeException('Recompensa HTTP nao creditada uma vez.');
    [$corpo, $codigo] = $pedir(parse_url($destino, PHP_URL_PATH) . '?' . parse_url($destino, PHP_URL_QUERY));
    if ($codigo !== 200 || !str_contains($corpo, 'if (a &lt; b &amp;&amp; texto != &quot;&lt;&gt; &amp;&quot;)')
        || !str_contains($corpo, 'A interface declara as operações públicas do TAD pilha') || !str_contains($corpo, '+65 moedas')) throw new RuntimeException('Revisao perdeu codigo, explicacao ou recompensa.');
    [$corpo, $codigo] = $pedir('/processamento/processamento.php', ['acao' => 'salvarQuiz', 'assunto' => 'tad', 'csrf' => $tokenQuiz[1], 'respostas' => $respostasQuiz, 'moedas' => 1000]);
    if ($codigo !== 303 || (int) $db->query('SELECT moedas FROM usuario WHERE id_usuario = 1')->fetch_assoc()['moedas'] !== $saldoQuiz)
        throw new RuntimeException('Reenvio ou valor do navegador alterou saldo.');
    [$corpo, $codigo] = $pedir('/view/loja.php');
    if ($codigo !== 200 || !str_contains($corpo, '<strong>115</strong>')) throw new RuntimeException('Loja nao mostrou saldo do Quiz.');
    echo "OK: codigo escapado, tentativa salva e revisao completa\n";
    $db->query('INSERT INTO usuario_item (id_usuario, id_item) VALUES (1, 5)');
    $db->query('UPDATE avatar_usuario SET id_cabelo = 4, id_rosto = 5, id_roupa = 6 WHERE id_usuario = 1');
    [$corpo, $codigo] = $pedir('/view/quiz.php?assunto=tad');
    if ($codigo !== 200 || !str_contains($corpo, 'Poderes do Avatar') || !str_contains($corpo, 'Eliminar alternativa')
        || !str_contains($corpo, 'Resumo rápido') || str_contains($corpo, 'Observe que há apenas assinaturas de métodos.')) throw new RuntimeException('Poderes equipados nao aparecem corretamente.');
    if (!preg_match('~<form id="quiz-respostas".*?name="csrf" value="([a-f0-9]{64})"~s', $corpo, $tokenPoder)) throw new RuntimeException('Token do poder ausente.');
    $tokenPoder = $tokenPoder[1];
    [$corpo, $codigo] = $pedir('/processamento/processamento.php', ['acao' => 'usarHabilidade', 'csrf' => $tokenPoder, 'habilidade' => 'dica', 'id_pergunta' => 2], true);
    $dadosPoder = json_decode($corpo, true);
    if ($codigo !== 422 || $dadosPoder['sucesso'] !== false) throw new RuntimeException('Dica ausente consumiu uso.');
    [$corpo, $codigo] = $pedir('/processamento/processamento.php', ['acao' => 'usarHabilidade', 'csrf' => $tokenPoder, 'habilidade' => 'dica', 'id_pergunta' => 10, 'id_usuario' => 2], true);
    $dadosPoder = json_decode($corpo, true);
    if ($codigo !== 200 || $dadosPoder['restante'] !== 0 || !str_contains($dadosPoder['conteudo'], 'assinaturas')) throw new RuntimeException('Dica HTTP falhou.');
    [$corpo, $codigo] = $pedir('/processamento/processamento.php', ['acao' => 'usarHabilidade', 'csrf' => $tokenPoder, 'habilidade' => 'dica', 'id_pergunta' => 1], true);
    if ($codigo !== 422 || json_decode($corpo, true)['sucesso'] !== false) throw new RuntimeException('Uso extra de dica aceito.');
    [$corpo, $codigo] = $pedir('/processamento/processamento.php', ['acao' => 'usarHabilidade', 'csrf' => $tokenPoder, 'habilidade' => 'eliminar_alternativa', 'id_pergunta' => 10], true);
    $dadosPoder = json_decode($corpo, true);
    if ($codigo !== 200 || $dadosPoder['restante'] !== 0) throw new RuntimeException('Eliminacao HTTP falhou.');
    $idEliminada = (int) $dadosPoder['id_alternativa'];
    if ((int) $db->query('SELECT correta FROM quiz_alternativa WHERE id_alternativa = ' . $idEliminada)->fetch_assoc()['correta'] !== 0) throw new RuntimeException('Alternativa correta eliminada.');
    [$corpo, $codigo] = $pedir('/processamento/processamento.php', ['acao' => 'usarHabilidade', 'csrf' => $tokenPoder, 'habilidade' => 'resumo_rapido'], true);
    $dadosPoder = json_decode($corpo, true);
    if ($codigo !== 200 || $dadosPoder['restante'] !== 0 || !str_contains($dadosPoder['conteudo'], 'Tipo Abstrato')) throw new RuntimeException('Resumo HTTP falhou.');
    [$corpo] = $pedir('/view/quiz.php?assunto=tad');
    if (!str_contains($corpo, 'alternativa-eliminada') || !str_contains($corpo, 'assinaturas') || !str_contains($corpo, 'Tipo Abstrato')) throw new RuntimeException('Estado dos poderes nao persistiu no refresh.');
    echo "OK: poderes via POST/JSON, limites e estado no refresh\n";
} finally {
    if ($canal) curl_close($canal);
    if (is_resource($servidor)) { proc_terminate($servidor); proc_close($servidor); }
    if (is_dir($sessoes)) { foreach (glob($sessoes . '/sess_*') as $arquivo) unlink($arquivo); if (is_file($sessoes . '/servidor.log')) unlink($sessoes . '/servidor.log'); rmdir($sessoes); }
    $db->select_db('mysql');
    if ($criado) $db->query('DROP DATABASE `' . $banco . '`');
    $db->close();
}
