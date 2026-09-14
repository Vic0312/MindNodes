<?php
require_once __DIR__ . '/../model/Quiz.php';
require_once __DIR__ . '/../model/QuizHabilidades.php';

class QuizController
{
    private $quizModel;
    private $habilidadesModel;
    public function __construct($quizModel = null, $habilidadesModel = null) {
        $this->quizModel = $quizModel ?: new Quiz();
        $this->habilidadesModel = $habilidadesModel ?: new QuizHabilidades($this->quizModel);
    }
    private function usuarioAtual() {
        if (empty($_SESSION['estaLogado']) || !isset($_SESSION['usuario_id'])) throw new LogicException('Autenticação necessária.');
        return Item::validarId($_SESSION['usuario_id']);
    }
    public function iniciarTentativaQuiz($slug) { return $this->habilidadesModel->iniciar($this->usuarioAtual(), $slug); }
    public function reiniciarTentativaQuiz($token) { return $this->habilidadesModel->reiniciar($this->usuarioAtual(), $token); }
    public function usarDicaQuiz($token, $idPergunta) { return $this->habilidadesModel->usar($this->usuarioAtual(), $token, 'dica', $idPergunta); }
    public function eliminarAlternativaQuiz($token, $idPergunta) { return $this->habilidadesModel->usar($this->usuarioAtual(), $token, 'eliminar_alternativa', $idPergunta); }
    public function usarResumoQuiz($token) { return $this->habilidadesModel->usar($this->usuarioAtual(), $token, 'resumo_rapido'); }
    public function finalizarTentativaQuiz($token, $slug, $respostas) { return $this->habilidadesModel->finalizar($this->usuarioAtual(), $token, $slug, $respostas); }
    public function listarAssuntosQuiz() { return $this->quizModel->listarAssuntos(); }
    public function buscarAssuntoQuiz($slug) { return $this->quizModel->buscarAssunto($slug); }
    public function buscarPerguntasQuiz($slug) { return $this->quizModel->buscarPerguntas($slug); }
    public function salvarTentativaQuiz($id, $slug, $respostas) { return $this->quizModel->salvarTentativa($id, $slug, $respostas); }
    public function buscarDesempenhoUsuario($id) { return $this->quizModel->buscarDesempenho($id); }
    public function buscarTentativaQuiz($tentativa, $usuario) { return $this->quizModel->buscarTentativa($tentativa, $usuario); }
}
