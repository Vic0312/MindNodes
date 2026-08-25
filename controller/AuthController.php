<?php
require_once __DIR__ . '/../model/Usuario.php';

class AuthController
{
    private $usuarioModel;
    public function __construct($usuarioModel = null) { $this->usuarioModel = $usuarioModel ?: new Usuario(); }
    public function efetuarLogin($email, $senha)
    {
        $email = strtolower(trim($email));
        $usuario = $this->usuarioModel->buscarPorEmail($email);

        if (!$usuario) {
            $_SESSION['estaLogado'] = false;
            return false;
        }

        $senhaArmazenada = (string) $usuario['senha'];
        $senhaValida = password_verify($senha, $senhaArmazenada);

        // Compatibilidade temporária: converte a senha legada em texto puro após login válido.
        if (!$senhaValida && password_get_info($senhaArmazenada)['algoName'] === 'unknown' && hash_equals($senhaArmazenada, $senha)) {
            $senhaValida = true;
            $novoHash = password_hash($senha, PASSWORD_DEFAULT);
            $this->usuarioModel->atualizarSenha((int) $usuario['id_usuario'], $novoHash);
            $usuario['senha'] = $novoHash;
        }

        if (!$senhaValida) {
            $_SESSION['estaLogado'] = false;
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['estaLogado'] = true;
        $_SESSION['usuario_id'] = $usuario['id_usuario'] ?? ($usuario['id'] ?? null);
        $_SESSION['usuario_nome'] = $usuario['nome'] ?? 'Usuario';
        $_SESSION['usuario_sobrenome'] = $usuario['sobrenome'] ?? '';
        $_SESSION['usuario_email'] = $usuario['email'] ?? $email;
        $_SESSION['usuario_telefone'] = $usuario['telefone'] ?? '';
        $_SESSION['usuario_foto'] = $usuario['foto_perfil'] ?? null;
        $_SESSION['login_sucesso'] = true;
        return $usuario;
    }

    public function validarCadastro($cpf, $nome, $sobrenome, $dataNasc, $telefone, $email, $senha, $confirmacaoSenha)
    {
        $cpf = preg_replace('/\D+/', '', $cpf);
        $email = strtolower(trim($email));

        if (trim($nome) === '' || trim($sobrenome) === '' || trim($dataNasc) === '' || trim($telefone) === '' || $cpf === '') return 'campos';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return 'email';
        if (strlen($senha) < 8) return 'senha';
        if (!hash_equals($senha, $confirmacaoSenha)) return 'confirmacao';
        if ($this->usuarioModel->buscarPorEmail($email)) return 'email_duplicado';
        if ($this->usuarioModel->buscarPorCPF($cpf)) return 'cpf_duplicado';
        return null;
    }

    public function cadastrarUsuario($cpf, $nome, $sobrenome, $dataNasc, $telefone, $email, $senha, $fotoPerfil, $confirmacaoSenha = null)
    {
        $confirmacaoSenha = $confirmacaoSenha === null ? $senha : $confirmacaoSenha;
        $erro = $this->validarCadastro($cpf, $nome, $sobrenome, $dataNasc, $telefone, $email, $senha, $confirmacaoSenha);
        if ($erro !== null) return ['sucesso' => false, 'erro' => $erro];

        $cpf = preg_replace('/\D+/', '', $cpf);
        $email = strtolower(trim($email));
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $this->usuarioModel->set_Cpf($cpf);
        $this->usuarioModel->set_Nome(trim($nome));
        $this->usuarioModel->set_Sobrenome(trim($sobrenome));
        $this->usuarioModel->set_DataNasc(trim($dataNasc));
        $this->usuarioModel->set_Telefone(trim($telefone));
        $this->usuarioModel->set_Email($email);
        $this->usuarioModel->set_Senha($senhaHash);
        $this->usuarioModel->set_Foto($fotoPerfil);
        $cadastrou = $this->usuarioModel->cadastrar();
        return ['sucesso' => $cadastrou, 'erro' => $cadastrou ? null : 'banco'];
    }

    public function logout()
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $parametros = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $parametros['path'], $parametros['domain'], $parametros['secure'], $parametros['httponly']);
        }
        session_destroy();
    }
}
