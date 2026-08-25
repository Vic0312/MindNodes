<?php
require_once __DIR__ . '/../model/Quiz.php';

class QuizController
{
    private $quizModel;
    public function __construct($quizModel = null) { $this->quizModel = $quizModel ?: new Quiz(); }
    public function listarAssuntosQuiz() { return $this->quizModel->listarAssuntos(); }
    public function buscarAssuntoQuiz($slug) { return $this->quizModel->buscarAssunto($slug); }
    public function buscarPerguntasQuiz($slug) { return $this->quizModel->buscarPerguntas($slug); }
    public function salvarTentativaQuiz($id, $slug, $respostas) { return $this->quizModel->salvarTentativa($id, $slug, $respostas); }
    public function buscarDesempenhoUsuario($id) { return $this->quizModel->buscarDesempenho($id); }
    public function buscarTentativaQuiz($tentativa, $usuario) { return $this->quizModel->buscarTentativa($tentativa, $usuario); }
}
