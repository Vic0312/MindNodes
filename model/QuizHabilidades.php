<?php
require_once __DIR__ . '/Quiz.php';
require_once __DIR__ . '/Avatar.php';

/** Estado temporario de uma tentativa. A sessao PHP serializa POSTs concorrentes. */
class QuizHabilidades
{
    private const CHAVE = 'quiz_tentativa_atual';
    private const VALIDADE = 7200;
    private const RESUMOS = [
        'tad' => 'Um Tipo Abstrato de Dados define operações e comportamento sem exigir uma implementação interna específica.',
        'lista-simples' => 'Em uma lista simplesmente encadeada, cada nó guarda um valor e uma referência para o próximo nó.',
        'lista-dupla' => 'Em uma lista duplamente encadeada, cada nó mantém referências para o próximo e para o anterior.',
    ];

    private $quiz;
    private $avatar;

    public function __construct($quiz = null, $avatar = null)
    {
        $this->quiz = $quiz ?: new Quiz();
        $this->avatar = $avatar;
    }

    private function contexto($idUsuario, $token = null)
    {
        $idUsuario = Item::validarId($idUsuario);
        $estado = $_SESSION[self::CHAVE] ?? null;
        if (!is_array($estado) || ($estado['id_usuario'] ?? null) !== $idUsuario
            || ($estado['iniciada_em'] ?? 0) + self::VALIDADE < time()) {
            unset($_SESSION[self::CHAVE]);
            throw new LogicException('Tentativa expirada. Abra o Quiz novamente.');
        }
        if ($token !== null && (!is_string($token) || !hash_equals($estado['token'], $token))) {
            throw new LogicException('Sessão da tentativa inválida.');
        }
        return $estado;
    }

    public function iniciar($idUsuario, $slug, $reiniciar = false)
    {
        $idUsuario = Item::validarId($idUsuario);
        if (!is_string($slug) || !$this->quiz->buscarAssunto($slug)) throw new OutOfBoundsException('Assunto inexistente.');
        if (!$reiniciar) {
            try {
                $estado = $this->contexto($idUsuario);
                if ($estado['slug'] === $slug) return $estado;
            } catch (LogicException $erro) { /* Sem tentativa ativa. */ }
        }
        $perguntas = $this->quiz->buscarPerguntas($slug);
        if (!$perguntas) throw new OutOfBoundsException('Assunto sem perguntas.');
        $estado = [
            'id_usuario' => $idUsuario,
            'slug' => $slug,
            'token' => bin2hex(random_bytes(32)),
            'iniciada_em' => time(),
            'perguntas' => array_map(fn($pergunta) => (int) $pergunta['id_pergunta'], $perguntas),
            'habilidades' => ($this->avatar ?: new Avatar())->obterHabilidadesEquipadas($idUsuario),
            'eliminadas' => [],
            'dicas_reveladas' => [],
            'resumo_revelado' => null,
        ];
        $_SESSION[self::CHAVE] = $estado;
        return $estado;
    }

    public function reiniciar($idUsuario, $token)
    {
        $estado = $this->contexto($idUsuario, $token);
        return $this->iniciar($idUsuario, $estado['slug'], true);
    }

    public function usar($idUsuario, $token, $habilidade, $idPergunta = null)
    {
        $estado = $this->contexto($idUsuario, $token);
        if (!is_string($habilidade) || !in_array($habilidade, ['dica', 'eliminar_alternativa', 'resumo_rapido'], true)
            || !isset($estado['habilidades'][$habilidade])) throw new DomainException('Habilidade indisponível.');
        if ($estado['habilidades'][$habilidade]['quantidade_restante'] <= 0) throw new DomainException('Habilidade esgotada.');

        $pergunta = null;
        if ($habilidade !== 'resumo_rapido') {
            $idPergunta = Item::validarId($idPergunta);
            if (!in_array($idPergunta, $estado['perguntas'], true)) throw new OutOfBoundsException('Pergunta fora da tentativa.');
            foreach ($this->quiz->buscarPerguntas($estado['slug']) as $candidata) {
                if ((int) $candidata['id_pergunta'] === $idPergunta) { $pergunta = $candidata; break; }
            }
            if ($pergunta === null) throw new OutOfBoundsException('Pergunta indisponível.');
        }

        if ($habilidade === 'dica') {
            if (isset($estado['dicas_reveladas'][$idPergunta])) {
                return ['sucesso' => true, 'habilidade' => 'dica', 'restante' => $estado['habilidades']['dica']['quantidade_restante'], 'conteudo' => $estado['dicas_reveladas'][$idPergunta], 'id_pergunta' => $idPergunta];
            }
            $conteudo = trim((string) $pergunta['dica']);
            if ($conteudo === '') throw new DomainException('Esta questão não possui uma dica disponível.');
            $estado['dicas_reveladas'][$idPergunta] = $conteudo;
        } elseif ($habilidade === 'eliminar_alternativa') {
            $jaEliminadas = $estado['eliminadas'][$idPergunta] ?? [];
            $elegiveis = [];
            foreach ($pergunta['alternativas'] as $alternativa) {
                $id = (int) $alternativa['id_alternativa'];
                if ((int) $alternativa['correta'] === 0 && !in_array($id, $jaEliminadas, true)) $elegiveis[] = $id;
            }
            if (!$elegiveis) throw new DomainException('Não há alternativas incorretas disponíveis.');
            $idEliminada = $elegiveis[random_int(0, count($elegiveis) - 1)];
            $estado['eliminadas'][$idPergunta][] = $idEliminada;
            $conteudo = null;
        } else {
            $conteudo = self::RESUMOS[$estado['slug']] ?? null;
            if ($conteudo === null) throw new DomainException('Resumo indisponível para este assunto.');
            $estado['resumo_revelado'] = $conteudo;
        }

        $estado['habilidades'][$habilidade]['quantidade_restante']--;
        $_SESSION[self::CHAVE] = $estado;
        $resultado = ['sucesso' => true, 'habilidade' => $habilidade, 'restante' => $estado['habilidades'][$habilidade]['quantidade_restante']];
        if ($habilidade === 'eliminar_alternativa') {
            $resultado['id_pergunta'] = $idPergunta;
            $resultado['id_alternativa'] = $idEliminada;
        } else {
            $resultado['conteudo'] = $conteudo;
            if ($habilidade === 'dica') $resultado['id_pergunta'] = $idPergunta;
        }
        return $resultado;
    }

    public function finalizar($idUsuario, $token, $slug, $respostas)
    {
        $estado = $this->contexto($idUsuario, $token);
        if (!is_string($slug) || $estado['slug'] !== $slug || !is_array($respostas)) {
            throw new InvalidArgumentException('Tentativa inválida.');
        }
        foreach ($estado['eliminadas'] as $idPergunta => $ids) {
            if (isset($respostas[$idPergunta]) && in_array((int) $respostas[$idPergunta], $ids, true)) unset($respostas[$idPergunta]);
        }
        $idTentativa = $this->quiz->concluirTentativa($idUsuario, $slug, $respostas);
        if ($idTentativa) unset($_SESSION[self::CHAVE]);
        return $idTentativa;
    }
}
