<?php

require_once __DIR__ . '/../config/Conexao.php';

class Quiz
{
    private $conexao;

    public function __construct($conexao = null)
    {
        $this->conexao = $conexao ?: Conexao::obter();
    }

    public function listarAssuntos()
    {
        $sql = "SELECT qa.id_assunto, qa.titulo, qa.slug, qa.descricao, COUNT(qp.id_pergunta) AS total_perguntas FROM quiz_assunto qa LEFT JOIN quiz_pergunta qp ON qp.id_assunto = qa.id_assunto GROUP BY qa.id_assunto, qa.titulo, qa.slug, qa.descricao ORDER BY qa.id_assunto";
        $resultado = mysqli_query($this->conexao, $sql);
        return $resultado ? mysqli_fetch_all($resultado, MYSQLI_ASSOC) : [];
    }

    public function buscarAssunto($slug)
    {
        $consulta = mysqli_prepare($this->conexao, 'SELECT * FROM quiz_assunto WHERE slug = ? LIMIT 1');
        mysqli_stmt_bind_param($consulta, 's', $slug);
        mysqli_stmt_execute($consulta);
        $resultado = mysqli_stmt_get_result($consulta);
        return $resultado && mysqli_num_rows($resultado) === 1 ? mysqli_fetch_assoc($resultado) : false;
    }

    public function buscarPerguntas($slug)
    {
        $sql = 'SELECT qp.id_pergunta, qp.enunciado, qp.explicacao, qa.id_alternativa, qa.texto, qa.correta FROM quiz_assunto qas INNER JOIN quiz_pergunta qp ON qp.id_assunto = qas.id_assunto INNER JOIN quiz_alternativa qa ON qa.id_pergunta = qp.id_pergunta WHERE qas.slug = ? ORDER BY qp.id_pergunta, qa.id_alternativa';
        $consulta = mysqli_prepare($this->conexao, $sql);
        mysqli_stmt_bind_param($consulta, 's', $slug);
        mysqli_stmt_execute($consulta);
        $resultado = mysqli_stmt_get_result($consulta);
        $perguntas = [];
        while ($resultado && $linha = mysqli_fetch_assoc($resultado)) {
            $id = $linha['id_pergunta'];
            if (!isset($perguntas[$id])) {
                $perguntas[$id] = ['id_pergunta' => $id, 'enunciado' => $linha['enunciado'], 'explicacao' => $linha['explicacao'], 'alternativas' => []];
            }
            $perguntas[$id]['alternativas'][] = ['id_alternativa' => $linha['id_alternativa'], 'texto' => $linha['texto'], 'correta' => (int) $linha['correta']];
        }
        return array_values($perguntas);
    }

    public function salvarTentativa($idUsuario, $slug, $respostasUsuario)
    {
        $idUsuario = (int) $idUsuario;
        $assunto = $this->buscarAssunto($slug);
        if (!$assunto) return false;
        $perguntas = $this->buscarPerguntas($slug);
        $totalAcertos = 0;
        $calculadas = [];
        foreach ($perguntas as $pergunta) {
            $idPergunta = (int) $pergunta['id_pergunta'];
            $marcada = isset($respostasUsuario[$idPergunta]) ? (int) $respostasUsuario[$idPergunta] : null;
            $correta = null;
            foreach ($pergunta['alternativas'] as $alternativa) {
                if ((int) $alternativa['correta'] === 1) { $correta = (int) $alternativa['id_alternativa']; break; }
            }
            $acertou = $marcada !== null && $marcada === $correta;
            if ($acertou) $totalAcertos++;
            $calculadas[] = [$idPergunta, $marcada, $correta, $acertou ? 1 : 0];
        }
        $idAssunto = (int) $assunto['id_assunto'];
        $totalPerguntas = count($perguntas);
        $consulta = mysqli_prepare($this->conexao, 'INSERT INTO quiz_tentativa (id_usuario, id_assunto, total_perguntas, total_acertos) VALUES (?, ?, ?, ?)');
        mysqli_stmt_bind_param($consulta, 'iiii', $idUsuario, $idAssunto, $totalPerguntas, $totalAcertos);
        if (!mysqli_stmt_execute($consulta)) return false;
        $idTentativa = mysqli_insert_id($this->conexao);
        foreach ($calculadas as $resposta) {
            [$idPergunta, $marcada, $correta, $acertou] = $resposta;
            $consulta = mysqli_prepare($this->conexao, 'INSERT INTO quiz_resposta (id_tentativa, id_pergunta, id_alternativa_marcada, id_alternativa_correta, acertou) VALUES (?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($consulta, 'iiiii', $idTentativa, $idPergunta, $marcada, $correta, $acertou);
            mysqli_stmt_execute($consulta);
        }
        return $idTentativa;
    }

    public function buscarDesempenho($idUsuario)
    {
        $idUsuario = (int) $idUsuario;
        $consulta = mysqli_prepare($this->conexao, 'SELECT COUNT(*) AS total_tentativas, COALESCE(SUM(total_perguntas), 0) AS total_perguntas, COALESCE(SUM(total_acertos), 0) AS total_acertos FROM quiz_tentativa WHERE id_usuario = ?');
        mysqli_stmt_bind_param($consulta, 'i', $idUsuario); mysqli_stmt_execute($consulta);
        $resumo = mysqli_fetch_assoc(mysqli_stmt_get_result($consulta));
        $sql = 'SELECT qt.id_tentativa, qt.total_perguntas, qt.total_acertos, qt.data_tentativa, qa.titulo, qa.slug FROM quiz_tentativa qt INNER JOIN quiz_assunto qa ON qa.id_assunto = qt.id_assunto WHERE qt.id_usuario = ? ORDER BY qt.data_tentativa DESC';
        $consulta = mysqli_prepare($this->conexao, $sql); mysqli_stmt_bind_param($consulta, 'i', $idUsuario); mysqli_stmt_execute($consulta);
        $resultado = mysqli_stmt_get_result($consulta);
        return ['resumo' => $resumo, 'tentativas' => $resultado ? mysqli_fetch_all($resultado, MYSQLI_ASSOC) : []];
    }

    public function buscarTentativa($idTentativa, $idUsuario)
    {
        $idTentativa = (int) $idTentativa; $idUsuario = (int) $idUsuario;
        $sql = 'SELECT qt.id_tentativa, qt.total_perguntas, qt.total_acertos, qt.data_tentativa, qa.titulo, qa.slug FROM quiz_tentativa qt INNER JOIN quiz_assunto qa ON qa.id_assunto = qt.id_assunto WHERE qt.id_tentativa = ? AND qt.id_usuario = ? LIMIT 1';
        $consulta = mysqli_prepare($this->conexao, $sql); mysqli_stmt_bind_param($consulta, 'ii', $idTentativa, $idUsuario); mysqli_stmt_execute($consulta);
        $resultado = mysqli_stmt_get_result($consulta);
        if (!$resultado || mysqli_num_rows($resultado) !== 1) return false;
        $tentativa = mysqli_fetch_assoc($resultado);
        $sql = 'SELECT qr.acertou, qp.enunciado, qp.explicacao, marcada.texto AS resposta_marcada, correta.texto AS resposta_correta FROM quiz_resposta qr INNER JOIN quiz_pergunta qp ON qp.id_pergunta = qr.id_pergunta LEFT JOIN quiz_alternativa marcada ON marcada.id_alternativa = qr.id_alternativa_marcada INNER JOIN quiz_alternativa correta ON correta.id_alternativa = qr.id_alternativa_correta WHERE qr.id_tentativa = ? ORDER BY qr.id_resposta';
        $consulta = mysqli_prepare($this->conexao, $sql); mysqli_stmt_bind_param($consulta, 'i', $idTentativa); mysqli_stmt_execute($consulta);
        $resultado = mysqli_stmt_get_result($consulta);
        $tentativa['respostas'] = $resultado ? mysqli_fetch_all($resultado, MYSQLI_ASSOC) : [];
        return $tentativa;
    }
}

