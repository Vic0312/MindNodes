<?php

// Adaptador legado. Novos fluxos devem usar Usuario e Quiz diretamente.
require_once __DIR__ . '/Usuario.php';
require_once __DIR__ . '/Quiz.php';

class BancoDeDados
{
    private $conexao;
    private $usuarioModel;
    private $quizModel;

    public function __construct($host = null, $login = null, $senha = null, $dataBase = null)
    {
        $this->conexao = Conexao::obter();
        $this->usuarioModel = new Usuario(null, null, null, null, null, null, null, null, $this->conexao);
        $this->quizModel = new Quiz($this->conexao);
    }

    public function conectarBD() { return $this->conexao; }
    public function autenticarUsuario($email, $senha) { return $this->usuarioModel->buscarPorEmailESenha($email, $senha); }
    public function inserirUsuario($usuario) { return $usuario->cadastrar(); }
    public function editarPerfilUsuario($id, $nome, $sobrenome, $email, $telefone, $senha, $foto) { return $this->usuarioModel->atualizar($id, $nome, $sobrenome, $email, $telefone, $senha, $foto); }
    public function listarAssuntosQuiz() { return $this->quizModel->listarAssuntos(); }
    public function buscarAssuntoQuiz($slug) { return $this->quizModel->buscarAssunto($slug); }
    public function buscarPerguntasQuiz($slug) { return $this->quizModel->buscarPerguntas($slug); }
    public function salvarTentativaQuiz($id, $slug, $respostas) { return $this->quizModel->salvarTentativa($id, $slug, $respostas); }
    public function buscarDesempenhoUsuario($id) { return $this->quizModel->buscarDesempenho($id); }
    public function buscarTentativaQuiz($tentativa, $usuario) { return $this->quizModel->buscarTentativa($tentativa, $usuario); }
}
