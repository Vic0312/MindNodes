<?php
// php tests/pilha_encadeada.php — HTTP e compilação do código efetivamente renderizado.
if (PHP_SAPI !== 'cli') exit(1);
function verificar($condicao, $mensagem) {
    if (!$condicao) throw new RuntimeException($mensagem);
}
$temporario = sys_get_temp_dir() . '/mindnodes_pilha_' . bin2hex(random_bytes(8));
mkdir($temporario);
$servidor = null;
$db = null;
$banco = 'mindnodes_test_pilha_' . bin2hex(random_bytes(8));
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
    file_put_contents($temporario . '/sess_testepilha', 'usuario_id|i:1;usuario_nome|s:5:"Teste";estaLogado|b:1;');
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
            CURLOPT_COOKIE => $autenticado ? 'PHPSESSID=testepilha' : '']);
        $html = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return [$status, $html];
    };
    for ($i = 0; $i < 30; $i++) {
        [$status] = $pedir('/view/pilha_encadeada.php', false);
        if ($status) break;
        usleep(100000);
    }
    verificar($status === 302, 'Protecao de acesso falhou');
    [$status, $html] = $pedir('/view/pilha_encadeada.php');
    verificar($status === 200, 'Aula nao carregou');
    $dom = new DOMDocument();
    @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
    $xpath = new DOMXPath($dom);
    foreach ($xpath->query('//main//a') as $link) {
        $href = $link->getAttribute('href');
        if (str_starts_with($href, '#')) {
            verificar($dom->getElementById(substr($href, 1)) !== null, 'Ancora quebrada: ' . $href);
        } else {
            [$codigo] = $pedir('/view/' . $href);
            verificar($codigo === 200, 'Link quebrado: ' . $href . ' HTTP ' . $codigo);
        }
    }
    foreach (['tad', 'simples', 'dupla'] as $estrutura) {
        [$status] = $pedir('/view/exemplos.php?estrutura=' . $estrutura);
        verificar($status === 200, 'Conteudo anterior indisponivel');
    }
    [$status, $indice] = $pedir('/view/estruturas.php');
    verificar($status === 200 && str_contains($indice, 'href="../view/pilha_encadeada.php"'), 'Card ausente');
    [$status, $quiz] = $pedir('/view/quiz.php?assunto=pilha-encadeada');
    verificar($status === 200 && str_contains($quiz, '<h2>Pilha Encadeada</h2>'), 'CTA nao selecionou Pilha Encadeada: HTTP ' . $status . ' ' . substr(strip_tags($quiz), -1800));
    foreach (['Last In, First Out', 'último a entrar, primeiro a sair', 'PRÓXIMO', 'O(1)', 'O(n)', 'Vantagens', 'Desvantagens', 'Pilha com vetor', 'TAD', 'FIFO', 'Erros comuns'] as $trecho) {
        verificar(str_contains($html, $trecho), 'Conteudo ausente: ' . $trecho);
    }
    echo "PASSOU: HTTP, autenticacao, ancoras, navegacao anterior, conteudo e CTA do Quiz.\n";
    $codigo = $xpath->query('//pre[@id="codigo-completo"]/code')->item(0)->textContent;
    $uso = $xpath->query('//pre[@id="codigo-uso"]/code')->item(0)->textContent;
    $teste = <<<'CS'
public static class Teste
{
    public static void Main()
    {
        var pilha = new PilhaEncadeada();
        if (!pilha.EstaVazia()) throw new Exception("Estado inicial");
        for (int ciclo = 0; ciclo < 2; ciclo++)
        {
            try { pilha.Topo(); throw new Exception("Peek vazio aceito"); }
            catch (InvalidOperationException) { }
            try { pilha.Desempilhar(); throw new Exception("Pop vazio aceito"); }
            catch (InvalidOperationException) { }
            pilha.Empilhar(10);
            pilha.Empilhar(20);
            pilha.Empilhar(30);
            if (pilha.EstaVazia() || pilha.Topo() != 30 || pilha.Topo() != 30)
                throw new Exception("Push ou Peek");
            if (pilha.Desempilhar() != 30 || pilha.Topo() != 20 ||
                pilha.Desempilhar() != 20 || pilha.Desempilhar() != 10 || !pilha.EstaVazia())
                throw new Exception("LIFO, ultimo no ou reinsercao");
        }
        pilha.Empilhar(0);
        pilha.Empilhar(-1);
        pilha.Empilhar(-1);
        if (pilha.Desempilhar() != -1 || pilha.Desempilhar() != -1 || pilha.Desempilhar() != 0)
            throw new Exception("Valores validos e repetidos");
        var saida = new System.IO.StringWriter();
        var console = Console.Out;
        Console.SetOut(saida);
        Programa.Main();
        Console.SetOut(console);
        if (saida.ToString().Replace("\r", "") != "30\n20\n") throw new Exception("Saida do exemplo");
        Console.WriteLine("PASSOU: C# compilado; Push, Pop, Peek, vazio, ultimo no, reinsercao e saida 30/20.");
    }
}
CS;
    file_put_contents($temporario . '/Teste.csproj', '<Project Sdk="Microsoft.NET.Sdk"><PropertyGroup><OutputType>Exe</OutputType><TargetFramework>net8.0</TargetFramework><Nullable>disable</Nullable><StartupObject>Teste</StartupObject></PropertyGroup></Project>');
    file_put_contents($temporario . '/Program.cs', $codigo . "\n" . $uso . "\n" . $teste);
    $processo = proc_open(['dotnet', 'run', '--project', $temporario . '/Teste.csproj', '--no-launch-profile'],
        [0 => ['pipe', 'r'], 1 => ['file', $temporario . '/csharp.log', 'a'], 2 => ['file', $temporario . '/csharp.log', 'a']], $pipes);
    verificar(is_resource($processo), 'SDK .NET indisponivel');
    fclose($pipes[0]);
    $resultado = proc_close($processo);
    $saida = file_get_contents($temporario . '/csharp.log');
    verificar($resultado === 0 && str_contains($saida, 'PASSOU: C# compilado'), $saida);
    echo $saida;
    $raizUrl = 'file:///' . str_replace('\\', '/', realpath(__DIR__ . '/..')) . '/view/';
    file_put_contents($temporario . '/aula.html', str_replace('<head>', '<head><base href="' . $raizUrl . '">', $html));
    $processo = proc_open(['node', __DIR__ . '/pilha_responsividade.cjs', $temporario],
        [0 => ['pipe', 'r'], 1 => ['file', $temporario . '/layout.log', 'a'], 2 => ['file', $temporario . '/layout.log', 'a']], $pipes);
    verificar(is_resource($processo), 'Node indisponivel');
    fclose($pipes[0]);
    $resultado = proc_close($processo);
    $saida = file_get_contents($temporario . '/layout.log');
    verificar($resultado === 0, $saida);
    echo $saida;
} finally {
    if (is_resource($servidor)) { proc_terminate($servidor); proc_close($servidor); }
    if ($bancoCriado) $db->query('DROP DATABASE `' . $banco . '`');
    if ($db) $db->close();
    $alvo = realpath($temporario);
    $raiz = realpath(sys_get_temp_dir());
    if ($alvo && str_starts_with(strtolower($alvo), strtolower($raiz . DIRECTORY_SEPARATOR . 'mindnodes_pilha_'))) {
        $arquivos = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($alvo, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($arquivos as $arquivo) {
            if ($arquivo->isDir()) rmdir($arquivo->getPathname());
            else unlink($arquivo->getPathname());
        }
        rmdir($alvo);
    }
}
