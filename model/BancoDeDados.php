<?php
require_once("Usuario.php");

class BancoDeDados{

    private $host; //IP ou localhost
    private $login;
    private $senha;
    private $dataBase;

    public function __construct($Host, $Login, $Senha, $DataBase){
        $this->host = $Host;
        $this->login = $Login;
        $this->senha = $Senha;
        $this->dataBase = $DataBase;
    }

    //Métodos
    public function conectarBD(){

        $conexao = mysqli_connect($this->host,$this->login,$this->senha,$this->dataBase);
        return($conexao);
    }

    public function autenticarUsuario($email, $senha) {
        $conexao = $this->conectarBD();
        
        $email = mysqli_real_escape_string($conexao, $email);
        $senha = mysqli_real_escape_string($conexao, $senha);

        $consulta = "SELECT * FROM usuario WHERE email = '$email' AND senha = '$senha'";
        $resultado = mysqli_query($conexao, $consulta);

        if (mysqli_num_rows($resultado) == 1) {
            return mysqli_fetch_assoc($resultado); 
        } else {
            return false;
        }
    }
    
    public function inserirUsuario($usuario){
        
        $conexao = $this->conectarBD();
        $consulta = "INSERT INTO usuario (cpf, nome, sobrenome, dataNasc, telefone, email, senha, foto_perfil) 
                     VALUES ('{$usuario->get_Cpf()}',
                             '{$usuario->get_Nome()}',
                             '{$usuario->get_Sobrenome()}',
                             '{$usuario->get_DataNasc()}',
                             '{$usuario->get_Telefone()}',
                             '{$usuario->get_Email()}',
                             '{$usuario->get_Senha()}',
                             '{$usuario->get_Foto()}')";
        mysqli_query($conexao,$consulta);
    }

    public function editarPerfilUsuario($id_usuario, $nome, $sobrenome, $email, $telefone, $senha, $foto_perfil) {
        $conexao = $this->conectarBD();
    
        $id_usuario = mysqli_real_escape_string($conexao, $id_usuario);
        $nome = mysqli_real_escape_string($conexao, $nome);
        $sobrenome = mysqli_real_escape_string($conexao, $sobrenome);
        $email = mysqli_real_escape_string($conexao, $email);
        $telefone = mysqli_real_escape_string($conexao, $telefone);
        $foto_perfil = mysqli_real_escape_string($conexao, $foto_perfil);
    
        if (!empty($senha)) {
            $senha = mysqli_real_escape_string($conexao, $senha);
    
            $consulta = "UPDATE usuario
                         SET nome = '$nome',
                             sobrenome = '$sobrenome',
                             email = '$email',
                             telefone = '$telefone',
                             senha = '$senha',
                             foto_perfil = '$foto_perfil'
                         WHERE id_usuario = '$id_usuario'";
        } else {
            $consulta = "UPDATE usuario
                         SET nome = '$nome',
                             sobrenome = '$sobrenome',
                             email = '$email',
                             telefone = '$telefone',
                             foto_perfil = '$foto_perfil'
                         WHERE id_usuario = '$id_usuario'";
        }
    
        return mysqli_query($conexao, $consulta);
    }

    public function listarAssuntosQuiz() {
        $conexao = $this->conectarBD();

        $consulta = "SELECT qa.id_assunto,
                            qa.titulo,
                            qa.slug,
                            qa.descricao,
                            COUNT(qp.id_pergunta) AS total_perguntas
                       FROM quiz_assunto qa
                  LEFT JOIN quiz_pergunta qp ON qp.id_assunto = qa.id_assunto
                   GROUP BY qa.id_assunto, qa.titulo, qa.slug, qa.descricao
                   ORDER BY qa.id_assunto";

        $resultado = mysqli_query($conexao, $consulta);
        $assuntos = [];

        if ($resultado) {
            while ($linha = mysqli_fetch_assoc($resultado)) {
                $assuntos[] = $linha;
            }
        }

        return $assuntos;
    }

    public function buscarAssuntoQuiz($slug) {
        $conexao = $this->conectarBD();
        $slug = mysqli_real_escape_string($conexao, $slug);

        $consulta = "SELECT * FROM quiz_assunto WHERE slug = '$slug' LIMIT 1";
        $resultado = mysqli_query($conexao, $consulta);

        if ($resultado && mysqli_num_rows($resultado) === 1) {
            return mysqli_fetch_assoc($resultado);
        }

        return false;
    }

    public function buscarPerguntasQuiz($slug) {
        $conexao = $this->conectarBD();
        $slug = mysqli_real_escape_string($conexao, $slug);

        $consulta = "SELECT qp.id_pergunta,
                            qp.enunciado,
                            qp.explicacao,
                            qa.id_alternativa,
                            qa.texto,
                            qa.correta
                       FROM quiz_assunto qas
                 INNER JOIN quiz_pergunta qp ON qp.id_assunto = qas.id_assunto
                 INNER JOIN quiz_alternativa qa ON qa.id_pergunta = qp.id_pergunta
                      WHERE qas.slug = '$slug'
                   ORDER BY qp.id_pergunta, qa.id_alternativa";

        $resultado = mysqli_query($conexao, $consulta);
        $perguntas = [];

        if ($resultado) {
            while ($linha = mysqli_fetch_assoc($resultado)) {
                $idPergunta = $linha['id_pergunta'];

                if (!isset($perguntas[$idPergunta])) {
                    $perguntas[$idPergunta] = [
                        'id_pergunta' => $linha['id_pergunta'],
                        'enunciado' => $linha['enunciado'],
                        'explicacao' => $linha['explicacao'],
                        'alternativas' => []
                    ];
                }

                $perguntas[$idPergunta]['alternativas'][] = [
                    'id_alternativa' => $linha['id_alternativa'],
                    'texto' => $linha['texto'],
                    'correta' => (int) $linha['correta']
                ];
            }
        }

        return array_values($perguntas);
    }

    public function salvarTentativaQuiz($idUsuario, $slug, $respostasUsuario) {
        $conexao = $this->conectarBD();
        $idUsuario = (int) $idUsuario;
        $assunto = $this->buscarAssuntoQuiz($slug);

        if (!$assunto) {
            return false;
        }

        $perguntas = $this->buscarPerguntasQuiz($slug);
        $totalPerguntas = count($perguntas);
        $totalAcertos = 0;
        $respostasCalculadas = [];

        foreach ($perguntas as $pergunta) {
            $idPergunta = (int) $pergunta['id_pergunta'];
            $idAlternativaMarcada = isset($respostasUsuario[$idPergunta]) ? (int) $respostasUsuario[$idPergunta] : null;
            $idAlternativaCorreta = null;

            foreach ($pergunta['alternativas'] as $alternativa) {
                if ((int) $alternativa['correta'] === 1) {
                    $idAlternativaCorreta = (int) $alternativa['id_alternativa'];
                    break;
                }
            }

            $acertou = $idAlternativaMarcada !== null && $idAlternativaMarcada === $idAlternativaCorreta;

            if ($acertou) {
                $totalAcertos++;
            }

            $respostasCalculadas[] = [
                'id_pergunta' => $idPergunta,
                'id_alternativa_marcada' => $idAlternativaMarcada,
                'id_alternativa_correta' => $idAlternativaCorreta,
                'acertou' => $acertou ? 1 : 0
            ];
        }

        $idAssunto = (int) $assunto['id_assunto'];
        $consultaTentativa = "INSERT INTO quiz_tentativa (id_usuario, id_assunto, total_perguntas, total_acertos)
                              VALUES ($idUsuario, $idAssunto, $totalPerguntas, $totalAcertos)";

        if (!mysqli_query($conexao, $consultaTentativa)) {
            return false;
        }

        $idTentativa = mysqli_insert_id($conexao);

        foreach ($respostasCalculadas as $resposta) {
            $idPergunta = (int) $resposta['id_pergunta'];
            $idCorreta = (int) $resposta['id_alternativa_correta'];
            $acertou = (int) $resposta['acertou'];
            $idMarcada = $resposta['id_alternativa_marcada'];
            $valorMarcada = $idMarcada === null ? "NULL" : (int) $idMarcada;

            $consultaResposta = "INSERT INTO quiz_resposta
                                (id_tentativa, id_pergunta, id_alternativa_marcada, id_alternativa_correta, acertou)
                                 VALUES ($idTentativa, $idPergunta, $valorMarcada, $idCorreta, $acertou)";

            mysqli_query($conexao, $consultaResposta);
        }

        return $idTentativa;
    }

    public function buscarDesempenhoUsuario($idUsuario) {
        $conexao = $this->conectarBD();
        $idUsuario = (int) $idUsuario;

        $consultaResumo = "SELECT COUNT(*) AS total_tentativas,
                                  COALESCE(SUM(total_perguntas), 0) AS total_perguntas,
                                  COALESCE(SUM(total_acertos), 0) AS total_acertos
                             FROM quiz_tentativa
                            WHERE id_usuario = $idUsuario";

        $resultadoResumo = mysqli_query($conexao, $consultaResumo);
        $resumo = mysqli_fetch_assoc($resultadoResumo);

        $consultaTentativas = "SELECT qt.id_tentativa,
                                      qt.total_perguntas,
                                      qt.total_acertos,
                                      qt.data_tentativa,
                                      qa.titulo,
                                      qa.slug
                                 FROM quiz_tentativa qt
                           INNER JOIN quiz_assunto qa ON qa.id_assunto = qt.id_assunto
                                WHERE qt.id_usuario = $idUsuario
                             ORDER BY qt.data_tentativa DESC";

        $resultadoTentativas = mysqli_query($conexao, $consultaTentativas);
        $tentativas = [];

        if ($resultadoTentativas) {
            while ($linha = mysqli_fetch_assoc($resultadoTentativas)) {
                $tentativas[] = $linha;
            }
        }

        return [
            'resumo' => $resumo,
            'tentativas' => $tentativas
        ];
    }

    public function buscarTentativaQuiz($idTentativa, $idUsuario) {
        $conexao = $this->conectarBD();
        $idTentativa = (int) $idTentativa;
        $idUsuario = (int) $idUsuario;

        $consultaTentativa = "SELECT qt.id_tentativa,
                                     qt.total_perguntas,
                                     qt.total_acertos,
                                     qt.data_tentativa,
                                     qa.titulo,
                                     qa.slug
                                FROM quiz_tentativa qt
                          INNER JOIN quiz_assunto qa ON qa.id_assunto = qt.id_assunto
                               WHERE qt.id_tentativa = $idTentativa
                                 AND qt.id_usuario = $idUsuario
                               LIMIT 1";

        $resultadoTentativa = mysqli_query($conexao, $consultaTentativa);

        if (!$resultadoTentativa || mysqli_num_rows($resultadoTentativa) !== 1) {
            return false;
        }

        $tentativa = mysqli_fetch_assoc($resultadoTentativa);

        $consultaRespostas = "SELECT qr.acertou,
                                     qp.enunciado,
                                     qp.explicacao,
                                     marcada.texto AS resposta_marcada,
                                     correta.texto AS resposta_correta
                                FROM quiz_resposta qr
                          INNER JOIN quiz_pergunta qp ON qp.id_pergunta = qr.id_pergunta
                           LEFT JOIN quiz_alternativa marcada ON marcada.id_alternativa = qr.id_alternativa_marcada
                          INNER JOIN quiz_alternativa correta ON correta.id_alternativa = qr.id_alternativa_correta
                               WHERE qr.id_tentativa = $idTentativa
                            ORDER BY qr.id_resposta";

        $resultadoRespostas = mysqli_query($conexao, $consultaRespostas);
        $respostas = [];

        if ($resultadoRespostas) {
            while ($linha = mysqli_fetch_assoc($resultadoRespostas)) {
                $respostas[] = $linha;
            }
        }

        $tentativa['respostas'] = $respostas;

        return $tentativa;
    }

}

?>
