<?php
// php tests/exemplos.php — requer PHP/MySQL, .NET 8, Node e Chrome locais.
// Banco e sessão descartáveis; não altera dados da aplicação.
if (PHP_SAPI !== 'cli') exit(1);
function verificar($condicao, $mensagem) {
    if (!$condicao) throw new RuntimeException($mensagem);
}
function documento($html) {
    $dom = new DOMDocument();
    @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
    return $dom;
}
$temporario = sys_get_temp_dir() . '/mindnodes_exemplos_' . bin2hex(random_bytes(8));
mkdir($temporario);
$servidor = null;
$db = null;
$banco = 'mindnodes_test_exemplos_' . bin2hex(random_bytes(8));
$bancoCriado = false;
try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $db = new mysqli('127.0.0.1', getenv('MINDNODES_TEST_USER') ?: 'root', getenv('MINDNODES_TEST_PASSWORD') ?: '');
    $db->set_charset('utf8mb4');
    $db->query('CREATE DATABASE `' . $banco . '` CHARACTER SET utf8mb4');
    $bancoCriado = true;
    $db->select_db($banco);
    $db->multi_query(file_get_contents(__DIR__ . '/../mindnode.sql'));
    do { if ($r = $db->store_result()) $r->free(); } while ($db->more_results() && $db->next_result());
    file_put_contents($temporario . '/sess_testeexemplos', 'usuario_id|i:1;usuario_nome|s:5:"Teste";estaLogado|b:1;');
    $socket = stream_socket_server('tcp://127.0.0.1:0');
    $endereco = stream_socket_get_name($socket, false);
    fclose($socket);
    $servidor = proc_open([PHP_BINARY, '-d', 'session.save_path=' . $temporario, '-S', $endereco, '-t', realpath(__DIR__ . '/..')],
        [0 => ['pipe', 'r'], 1 => ['file', $temporario . '/http.log', 'a'], 2 => ['file', $temporario . '/http.log', 'a']], $pipes, null, array_merge(getenv(), ['MINDNODES_DB' => $banco]));
    verificar(is_resource($servidor), 'Servidor indisponivel');
    fclose($pipes[0]);
    $pedir = function ($rota, $autenticado = true) use ($endereco) {
        $curl = curl_init('http://' . $endereco . $rota);
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5,
            CURLOPT_COOKIE => $autenticado ? 'PHPSESSID=testeexemplos' : '']);
        $html = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return [$status, $html];
    };
    for ($i = 0; $i < 30; $i++) {
        [$status] = $pedir('/view/exemplos.php', false);
        if ($status) break;
        usleep(100000);
    }
    verificar($status === 302, 'Protecao de acesso falhou');
    [$status, $html] = $pedir('/view/exemplos.php');
    verificar($status === 200, 'Exemplos nao carregaram');
    $dom = documento($html);
    $dados = json_decode($dom->getElementById('dados-exemplos')->textContent, true, 512, JSON_THROW_ON_ERROR);
    verificar(array_keys($dados) === ['tad', 'simples', 'dupla', 'fila-fifo', 'fila-prioridade', 'pilha-encadeada'], 'Seis estruturas fora de ordem');
    $fontes = '';
    $chamadas = '';
    $indice = 0;
    foreach ($dados as $chave => $exemplo) {
        [$status, $pagina] = $pedir('/view/exemplos.php?estrutura=' . $chave);
        verificar($status === 200 && !str_contains($pagina, 'Fatal error'), 'Falha HTTP ' . $chave);
        $dom = documento($pagina);
        $xpath = new DOMXPath($dom);
        verificar($xpath->query('//a[@data-exemplo]')->length === 6, 'Menu incompleto');
        verificar($xpath->query('//a[@data-exemplo and @aria-current="page"]')->item(0)->getAttribute('data-exemplo') === $chave, 'Ativo incorreto');
        foreach (['codigo-exemplo' => 'codigo', 'uso-exemplo' => 'uso', 'saida-exemplo' => 'saida'] as $id => $campo) {
            verificar($dom->getElementById($id)->textContent === $exemplo[$campo], 'Texto/escape incorreto: ' . $id);
        }
        foreach (['teoria-exemplo', 'quiz-exemplo'] as $id) {
            $href = $dom->getElementById($id)->getAttribute('href');
            [$codigo, $destino] = $pedir('/view/' . $href);
            verificar($codigo === 200 && !str_contains($destino, 'Fatal error'), 'Link quebrado: ' . $href);
            $alvo = documento($destino);
            if (str_contains($href, '#')) verificar($alvo->getElementById(explode('#', $href)[1]) !== null, 'Ancora ausente');
            if ($id === 'quiz-exemplo') verificar(str_contains($destino, '<h2>' . $exemplo['titulo'] . '</h2>'), 'Quiz incorreto');
        }
        // Compila os textos obtidos dos blocos HTML, isolados por namespace.
        $fontes .= "\nnamespace Exemplo$indice {\n" . $dom->getElementById('codigo-exemplo')->textContent . "\n" . $dom->getElementById('uso-exemplo')->textContent . "\n}\n";
        $esperado = json_encode(str_replace("\r", '', $exemplo['saida']) . "\n", JSON_UNESCAPED_UNICODE);
        $chamadas .= "if (Testes.Capturar(Exemplo$indice.Programa.Main) != $esperado) throw new System.Exception(\"Saida $chave\");\n";
        $indice++;
    }
    foreach (['inexistente', '%3Cscript%3E', '%5B%5D'] as $invalido) {
        [$status, $pagina] = $pedir('/view/exemplos.php?estrutura=' . $invalido);
        verificar($status === 200 && documento($pagina)->getElementById('titulo-codigo')->textContent === $dados['tad']['titulo'], 'Fallback incorreto');
    }
    [$status, $pagina] = $pedir('/view/exemplos.php?estrutura[]=tad');
    verificar($status === 200 && !str_contains($pagina, 'Fatal error'), 'Parametro array nao tratado');
    echo "PASSOU: HTTP, autenticacao, seis exemplos, escape, selecao sem JS, teoria e Quiz.\n";
    file_put_contents($temporario . '/Program.cs', file_get_contents(__DIR__ . '/exemplos_csharp.cs') . $fontes . "\npublic static class Executor { public static void Main() {\n" . $chamadas . "Testes.Validar();\n} }\n");
    file_put_contents($temporario . '/Teste.csproj', '<Project Sdk="Microsoft.NET.Sdk"><PropertyGroup><OutputType>Exe</OutputType><TargetFramework>net8.0</TargetFramework><Nullable>disable</Nullable><StartupObject>Executor</StartupObject></PropertyGroup></Project>');
    $rodar = function ($comando, $arquivo) use ($temporario) {
        $processo = proc_open($comando, [0 => ['pipe', 'r'], 1 => ['file', $temporario . '/' . $arquivo, 'a'], 2 => ['file', $temporario . '/' . $arquivo, 'a']], $pipes);
        verificar(is_resource($processo), 'Ferramenta nao iniciou');
        fclose($pipes[0]);
        $resultado = proc_close($processo);
        $saida = file_get_contents($temporario . '/' . $arquivo);
        verificar($resultado === 0, $saida);
        echo $saida;
    };
    $rodar(['dotnet', 'run', '--project', $temporario . '/Teste.csproj', '--no-launch-profile'], 'csharp.log');
    $rodar(['node', __DIR__ . '/exemplos_navegador.cjs', $temporario, 'http://' . $endereco], 'browser.log');
} finally {
    if (is_resource($servidor)) { proc_terminate($servidor); proc_close($servidor); }
    if ($bancoCriado) $db->query('DROP DATABASE `' . $banco . '`');
    if ($db) $db->close();
    $alvo = realpath($temporario);
    $raiz = realpath(sys_get_temp_dir());
    if ($alvo && str_starts_with(strtolower($alvo), strtolower($raiz . DIRECTORY_SEPARATOR . 'mindnodes_exemplos_'))) {
        $arquivos = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($alvo, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($arquivos as $arquivo) {
            if ($arquivo->isDir()) rmdir($arquivo->getPathname());
            else unlink($arquivo->getPathname());
        }
        rmdir($alvo);
    }
}
