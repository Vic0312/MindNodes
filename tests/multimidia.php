<?php
// php tests/multimidia.php — PHP, Node e Chrome locais. Sem banco de dados.
if (PHP_SAPI !== 'cli') exit(1);
function exigirMidia($ok, $mensagem) {
    if (!$ok) throw new RuntimeException($mensagem);
}
$pasta = sys_get_temp_dir() . '/mindnodes_midia_' . bin2hex(random_bytes(8));
mkdir($pasta);
$servidor = null;
try {
    file_put_contents($pasta . '/sess_testemidia', 'usuario_id|i:1;usuario_nome|s:5:"Teste";estaLogado|b:1;');
    $socket = stream_socket_server('tcp://127.0.0.1:0');
    $endereco = stream_socket_get_name($socket, false);
    fclose($socket);
    $servidor = proc_open([PHP_BINARY, '-d', 'session.save_path=' . $pasta, '-S', $endereco, '-t', realpath(__DIR__ . '/..')],
        [0 => ['pipe', 'r'], 1 => ['file', $pasta . '/http.log', 'a'], 2 => ['file', $pasta . '/http.log', 'a']], $pipes);
    exigirMidia(is_resource($servidor), 'Servidor indisponivel');
    fclose($pipes[0]);
    $get = function ($rota, $autenticado = true) use ($endereco) {
        $curl = curl_init('http://' . $endereco . '/view/' . $rota);
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5, CURLOPT_COOKIE => $autenticado ? 'PHPSESSID=testemidia' : '']);
        $html = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return [$status, $html];
    };
    for ($i = 0; $i < 30; $i++) {
        [$status] = $get('estruturas.php', false);
        if ($status) break;
        usleep(100000);
    }
    exigirMidia($status === 302, 'Autenticacao alterada');
    $paginas = ['estruturas.php' => ['tad', 'simples', 'dupla'], 'fila_fifo.php' => ['fila-fifo'], 'fila_prioridade.php' => ['fila-prioridade'], 'pilha_encadeada.php' => ['pilha-encadeada']];
    $total = 0;
    foreach ($paginas as $rota => $chaves) {
        [$status, $html] = $get($rota);
        exigirMidia($status === 200 && !str_contains($html, 'Fatal error'), 'Erro HTTP ' . $rota);
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        $xp = new DOMXPath($dom);
        exigirMidia($xp->query('//iframe')->length === 0, 'Video carregado sem escolha do aluno');
        foreach ($chaves as $chave) {
            $secao = $dom->getElementById('midia-' . $chave);
            exigirMidia($secao !== null, 'Recurso ausente: ' . $chave);
            $passos = $xp->query('.//article[@class="midia-passo"]', $secao);
            exigirMidia($passos->length >= 3, 'Sequencia incompleta');
            foreach ($passos as $passo) exigirMidia(!$passo->hasAttribute('hidden'), 'Sem JS deve mostrar todos os passos');
            $total++;
        }
        foreach ($xp->query('//nav[@class="midia-navegacao"]/a') as $link) {
            $href = $link->getAttribute('href');
            [$pagina, $ancora] = explode('#', $href);
            if ($pagina === '') $destino = $dom;
            else {
                [$codigo, $conteudo] = $get($pagina);
                exigirMidia($codigo === 200, 'Link quebrado');
                $destino = new DOMDocument();
                @$destino->loadHTML('<?xml encoding="UTF-8">' . $conteudo);
            }
            exigirMidia($destino->getElementById($ancora) !== null, 'Ancora quebrada');
        }
    }
    exigirMidia($total === 6, 'Faltam estruturas');
    [$status] = $get('simulador.php');
    exigirMidia($status === 200, 'Simulador nao carregou');
    echo "PASSOU: seis recursos, HTTP, autenticacao, ancoras, simulador e fallback sem JS.\n";
    $processo = proc_open(['node', __DIR__ . '/multimidia_navegador.cjs', $pasta, 'http://' . $endereco],
        [0 => ['pipe', 'r'], 1 => ['file', $pasta . '/browser.log', 'a'], 2 => ['file', $pasta . '/browser.log', 'a']], $pipes);
    exigirMidia(is_resource($processo), 'Node indisponivel');
    fclose($pipes[0]);
    $resultado = proc_close($processo);
    $saida = file_get_contents($pasta . '/browser.log');
    exigirMidia($resultado === 0, $saida);
    echo $saida;
} finally {
    if (is_resource($servidor)) { proc_terminate($servidor); proc_close($servidor); }
    $alvo = realpath($pasta);
    $raiz = realpath(sys_get_temp_dir());
    if ($alvo && str_starts_with(strtolower($alvo), strtolower($raiz . DIRECTORY_SEPARATOR . 'mindnodes_midia_'))) {
        $arquivos = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($alvo, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($arquivos as $arquivo) {
            if ($arquivo->isDir()) rmdir($arquivo->getPathname());
            else unlink($arquivo->getPathname());
        }
        rmdir($alvo);
    }
}
