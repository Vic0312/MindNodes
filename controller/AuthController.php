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

    public static function tokenRecuperacao()
    {
        if (empty($_SESSION['recuperacao_csrf'])) {
            $_SESSION['recuperacao_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['recuperacao_csrf'];
    }

    private function validarTokenRecuperacao($token)
    {
        return is_string($token) && isset($_SESSION['recuperacao_csrf'])
            && hash_equals($_SESSION['recuperacao_csrf'], $token);
    }

    public static function recuperacaoValida()
    {
        if (!empty($_SESSION['recuperacao_usuario_id']) && ($_SESSION['recuperacao_expira'] ?? 0) > time()) {
            return true;
        }
        unset($_SESSION['recuperacao_usuario_id'], $_SESSION['recuperacao_expira']);
        return false;
    }

    public function iniciarRecuperacao($cpf, $email, $dataNascimento, $token)
    {
        if (!$this->validarTokenRecuperacao($token)) return 'sessao';
        unset($_SESSION['recuperacao_usuario_id'], $_SESSION['recuperacao_expira']);
        if (!is_string($cpf) || !is_string($email) || !is_string($dataNascimento)) return 'campos';
        $cpf = preg_replace('/\D+/', '', $cpf);
        $email = strtolower(trim($email));
        $dataNascimento = trim($dataNascimento);
        if ($cpf === '' || $email === '' || $dataNascimento === '') return 'campos';
        $data = DateTime::createFromFormat('!Y-m-d', $dataNascimento);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !$data || $data->format('Y-m-d') !== $dataNascimento) return 'dados';
        try {
            $usuario = $this->usuarioModel->buscarParaRecuperacao($cpf, $email, $dataNascimento);
        } catch (mysqli_sql_exception $e) {
            return 'banco';
        }
        if (!$usuario) return 'dados';
        session_regenerate_id(true);
        $_SESSION['recuperacao_usuario_id'] = (int) $usuario['id_usuario'];
        $_SESSION['recuperacao_expira'] = time() + 900;
        unset($_SESSION['recuperacao_csrf']);
        return null;
    }

    public function redefinirSenha($novaSenha, $confirmacao, $token)
    {
        if (!self::recuperacaoValida()) return 'sessao';
        if (!$this->validarTokenRecuperacao($token)) return 'sessao';
        if (!is_string($novaSenha) || !is_string($confirmacao)) return 'campos';
        // O processamento atual de login e cadastro também remove espaços nas extremidades.
        $novaSenha = trim($novaSenha);
        $confirmacao = trim($confirmacao);
        if (strlen($novaSenha) < 8) return 'senha';
        if (!hash_equals($novaSenha, $confirmacao)) return 'confirmacao';
        try {
            $atualizou = $this->usuarioModel->atualizarSenha(
                $_SESSION['recuperacao_usuario_id'], password_hash($novaSenha, PASSWORD_DEFAULT)
            );
        } catch (mysqli_sql_exception $e) {
            return 'banco';
        }
        if (!$atualizou) return 'banco';
        unset($_SESSION['recuperacao_usuario_id'], $_SESSION['recuperacao_expira'], $_SESSION['recuperacao_csrf']);
        session_regenerate_id(true);
        $_SESSION['recuperacao_sucesso'] = true;
        return null;
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
