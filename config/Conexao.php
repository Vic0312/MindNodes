<?php

class Conexao
{
    private const HOST = 'localhost';
    private const USUARIO = 'root';
    private const SENHA = '';
    private const BANCO = 'mindnode';

    private static $conexao = null;

    private function __construct()
    {
    }

    public static function obter()
    {
        if (self::$conexao === null) {
            self::$conexao = mysqli_connect(
                self::HOST,
                self::USUARIO,
                self::SENHA,
                self::BANCO
            );

            if (!self::$conexao) {
                throw new RuntimeException('Não foi possível conectar ao banco de dados.');
            }

            mysqli_set_charset(self::$conexao, 'utf8mb4');
        }

        return self::$conexao;
    }
}

