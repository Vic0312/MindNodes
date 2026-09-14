<?php
if (PHP_SAPI !== 'cli') exit(1);
$fonte = file_get_contents(__DIR__ . '/../view/fila_prioridade.php');
if (!preg_match('/\$codigoCompleto = <<<\x27CS\x27\R(.*?)\RCS;/s', $fonte, $partes)) {
    throw new RuntimeException('Exemplo C# completo nao encontrado na aula.');
}
$codigo = $partes[1];
$teste = <<<'CS'

public static class Teste
{
    public static void Main()
    {
        var fila = new FilaPrioridadeEncadeada();
        if (!fila.EstaVazia()) throw new Exception("Fila inicial nao vazia");
        try { fila.Frente(); throw new Exception("Frente vazia aceita"); }
        catch (InvalidOperationException) { }
        try { fila.Desenfileirar(); throw new Exception("Remocao vazia aceita"); }
        catch (InvalidOperationException) { }

        fila.Enfileirar(10, 2);
        fila.Enfileirar(20, 1);
        fila.Enfileirar(30, 2);
        fila.Enfileirar(40, 3);
        if (fila.Frente() != 20 || fila.Frente() != 20) throw new Exception("Frente alterou fila");
        if (fila.Desenfileirar() != 20 || fila.Desenfileirar() != 10 ||
            fila.Desenfileirar() != 30 || fila.Desenfileirar() != 40 || !fila.EstaVazia())
            throw new Exception("Prioridade ou FIFO P2 incorreto");

        fila.Enfileirar(1, 2); // A
        fila.Enfileirar(2, 1); // B
        fila.Enfileirar(3, 2); // C
        fila.Enfileirar(4, 1); // D
        if (fila.Desenfileirar() != 2 || fila.Desenfileirar() != 4 ||
            fila.Desenfileirar() != 1 || fila.Desenfileirar() != 3)
            throw new Exception("FIFO no empate P1/P2 incorreto");
        Console.WriteLine("PASSOU: C# compila; prioridade, empate FIFO, Frente e fila vazia.");
    }
}
CS;
$raizTemporaria = realpath(sys_get_temp_dir());
$pasta = $raizTemporaria . DIRECTORY_SEPARATOR . 'mindnodes_csharp_' . bin2hex(random_bytes(8));
if (!mkdir($pasta)) throw new RuntimeException('Pasta temporaria indisponivel.');
try {
    file_put_contents($pasta . '/Teste.csproj', '<Project Sdk="Microsoft.NET.Sdk"><PropertyGroup><OutputType>Exe</OutputType><TargetFramework>net10.0</TargetFramework><Nullable>disable</Nullable></PropertyGroup></Project>');
    file_put_contents($pasta . '/Program.cs', $codigo . "\n" . $teste . "\n");
    $processo = proc_open(['C:\\Program Files\\dotnet\\dotnet.exe', 'run', '--project', $pasta . '/Teste.csproj', '--no-launch-profile'],
        [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (!is_resource($processo)) throw new RuntimeException('SDK .NET nao iniciou.');
    fclose($pipes[0]);
    $saida = stream_get_contents($pipes[1]);
    $erro = stream_get_contents($pipes[2]);
    fclose($pipes[1]); fclose($pipes[2]);
    if (proc_close($processo) !== 0 || !str_contains($saida, 'PASSOU: C# compila')) {
        throw new RuntimeException("Exemplo C# falhou:\n" . $saida . $erro);
    }
    echo $saida;
} finally {
    $alvo = realpath($pasta);
    if ($alvo === false || !str_starts_with(strtolower($alvo), strtolower($raizTemporaria . DIRECTORY_SEPARATOR . 'mindnodes_csharp_'))) {
        throw new RuntimeException('Pasta temporaria fora do destino esperado.');
    }
    $arquivos = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($alvo, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($arquivos as $arquivo) {
        if ($arquivo->isDir()) rmdir($arquivo->getPathname());
        else unlink($arquivo->getPathname());
    }
    rmdir($alvo);
}
