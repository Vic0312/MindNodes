<?php
// Fachada temporária para compatibilidade com páginas ainda não migradas.
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/UsuarioController.php';
require_once __DIR__ . '/QuizController.php';

class Controlador
{
    private $authController;
    private $usuarioController;
    private $quizController;
    public function __construct()
    {
        $this->authController = new AuthController();
        $this->usuarioController = new UsuarioController();
        $this->quizController = new QuizController();
    }
    public function efetuarLogin($email, $senha) { return $this->authController->efetuarLogin($email, $senha); }
    public function cadastrarUsuario($cpf, $nome, $sobrenome, $dataNasc, $telefone, $email, $senha, $foto) { return $this->authController->cadastrarUsuario($cpf, $nome, $sobrenome, $dataNasc, $telefone, $email, $senha, $foto); }
    public function editarPerfilUsuario($id, $nome, $sobrenome, $email, $telefone, $senha, $foto) { return $this->usuarioController->editarPerfilUsuario($id, $nome, $sobrenome, $email, $telefone, $senha, $foto); }
    public function listarAssuntosQuiz() { return $this->quizController->listarAssuntosQuiz(); }
    public function buscarAssuntoQuiz($slug) { return $this->quizController->buscarAssuntoQuiz($slug); }
    public function buscarPerguntasQuiz($slug) { return $this->quizController->buscarPerguntasQuiz($slug); }
    public function salvarTentativaQuiz($id, $slug, $respostas) { return $this->quizController->salvarTentativaQuiz($id, $slug, $respostas); }
    public function buscarDesempenhoUsuario($id) { return $this->quizController->buscarDesempenhoUsuario($id); }
    public function buscarTentativaQuiz($tentativa, $usuario) { return $this->quizController->buscarTentativaQuiz($tentativa, $usuario); }
}
