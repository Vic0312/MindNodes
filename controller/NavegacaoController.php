<?php
require_once __DIR__ . '/MoedaController.php';

/** Dados de apresentação compartilhados durante uma única requisição. */
class NavegacaoController
{
    private static $dados;

    public static function carregar($saldoExistente = null)
    {
        if (self::$dados !== null) return self::$dados;
        $autenticado = isset($_SESSION['usuario_id']);
        $saldo = null;
        if ($autenticado) {
            try {
                $saldo = $saldoExistente ?? (new MoedaController())->obterSaldo($_SESSION['usuario_id']);
            } catch (Throwable $erro) {
                error_log('Falha ao consultar saldo da navegacao: ' . $erro->getMessage());
            }
        }
        return self::$dados = [
            'autenticado' => $autenticado,
            'nome' => $_SESSION['usuario_nome'] ?? 'Usuário',
            'saldo' => $saldo,
            'estruturas' => [
                'TAD' => 'estruturas.php#tad',
                'Lista Simplesmente Encadeada' => 'estruturas.php#lista-simples',
                'Lista Duplamente Encadeada' => 'estruturas.php#lista-dupla',
                'Fila Encadeada FIFO' => 'fila_fifo.php',
                'Fila de Prioridades Encadeada FIFO' => 'fila_prioridade.php',
                'Pilha Encadeada' => 'pilha_encadeada.php',
            ],
        ];
    }
}
