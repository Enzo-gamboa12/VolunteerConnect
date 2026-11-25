<?php

require_once __DIR__ . '/init.php';

// A campanha pertence a uma instituição (campanhas.id_instituicao -> instituicoes.id).
// A instituição é a que fez login; sem login válido, o acesso segue o padrão do projeto.
$idInstituicao = (int) ($_SESSION['id_instituicao'] ?? 0);
$instituicao = $idInstituicao > 0 ? read($pdo, 'instituicoes', 'id = ?', [$idInstituicao]) : false;

if ($instituicao === false) {
    header('Location: login.php?erro=acesso_negado');
    exit;
}

// Valores exatamente como definidos nos ENUMs de database/schema.sql.
$disponibilidades = [
    'Manhã',
    'Tarde',
    'Noite',
    'Fim de Semana',
    'Qualquer Horário',
];

$statusCampanha = ['Planejada', 'Ativa', 'Encerrada', 'Cancelada'];
$tiposCampanha = ['Saúde', 'Tecnologia', 'Educação'];

/** Lê um campo de texto do POST (ignora arrays) já sem espaços nas pontas. */
function campanhaCampo(string $nome): string
{
    $valor = $_POST[$nome] ?? '';

    return is_string($valor) ? trim($valor) : '';
}

/** Monta as <option> de um select, marcando a opção escolhida. */
function campanhaOpcoes(array $lista, string $selecionado): string
{
    $html = '<option value="">Selecione</option>';

    foreach ($lista as $valor) {
        $marcada = $valor === $selecionado ? ' selected' : '';
        $html .= '<option value="' . indexEsc($valor) . '"' . $marcada . '>' . indexEsc($valor) . '</option>';
    }

    return $html;
}

/** Confere se o texto é uma data real no formato AAAA-MM-DD. */
function campanhaDataValida(string $data): bool
{
    $objeto = DateTimeImmutable::createFromFormat('!Y-m-d', $data);

    return $objeto !== false && $objeto->format('Y-m-d') === $data;
}

$erros = [];
$d = [
    'nome'            => '',
    'descricao'       => '',
    'endereco'        => '',
    'data_inicio'     => '',
    'data_termino'    => '',
    'horario'         => '',
    'disponibilidade' => '',
    'status'          => 'Planejada',
    'tipo'            => '',
    'vagas'           => '',
    'vagasTotais'     => '',
];

// Mensagem de sucesso exibida uma única vez, só depois de o CRUD confirmar a gravação.
$sucesso = '';
if (isset($_SESSION['campanha_cadastrada'])) {
    $sucesso = 'Campanha cadastrada com sucesso!';
    unset($_SESSION['campanha_cadastrada']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($d as $campo => $padrao) {
        $d[$campo] = campanhaCampo($campo);
    }

    // Nome: VARCHAR(100) NOT NULL
    if ($d['nome'] === '') {
        $erros[] = 'Informe o título da campanha.';
    } elseif (mb_strlen($d['nome']) > 100) {
        $erros[] = 'O título deve ter no máximo 100 caracteres.';
    }

    // Descrição: TEXT, opcional (limite de 65.535 bytes)
    if (strlen($d['descricao']) > 65535) {
        $erros[] = 'A descrição é longa demais.';
    }

    // Endereço: VARCHAR(200) NOT NULL
    if ($d['endereco'] === '') {
        $erros[] = 'Informe o endereço.';
    } elseif (mb_strlen($d['endereco']) > 200) {
        $erros[] = 'O endereço deve ter no máximo 200 caracteres.';
    }

    // Datas: DATE NULL (opcionais)
    $datasValidas = true;
    foreach (['data_inicio' => 'início', 'data_termino' => 'término'] as $campo => $rotulo) {
        if ($d[$campo] !== '' && !campanhaDataValida($d[$campo])) {
            $erros[] = 'Informe uma data de ' . $rotulo . ' válida.';
            $datasValidas = false;
        }
    }

    if ($datasValidas && $d['data_inicio'] !== '' && $d['data_termino'] !== '' && $d['data_termino'] < $d['data_inicio']) {
        $erros[] = 'A data de término não pode ser anterior à data de início.';
    }

    // Horário: TIME NOT NULL (aceita HH:MM ou HH:MM:SS e grava HH:MM:SS)
    $horario = $d['horario'];
    if (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $horario)) {
        $erros[] = 'Informe um horário válido.';
    } elseif (strlen($horario) === 5) {
        $horario .= ':00';
    }

    // ENUMs NOT NULL
    if (!in_array($d['disponibilidade'], $disponibilidades, true)) {
        $erros[] = 'Escolha uma disponibilidade válida.';
    }

    if (!in_array($d['status'], $statusCampanha, true)) {
        $erros[] = 'Escolha um status válido.';
    }

    if (!in_array($d['tipo'], $tiposCampanha, true)) {
        $erros[] = 'Escolha um tipo de campanha válido.';
    }

    // Vagas: INT NOT NULL
    $vagas = filter_var($d['vagas'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 2147483647]]);
    $vagasTotais = filter_var($d['vagasTotais'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2147483647]]);

    if ($vagas === false) {
        $erros[] = 'Informe a quantidade de vagas disponíveis.';
    }

    if ($vagasTotais === false) {
        $erros[] = 'Informe o total de vagas.';
    }

    if ($vagas !== false && $vagasTotais !== false && $vagas > $vagasTotais) {
        $erros[] = 'As vagas disponíveis não podem passar do total de vagas.';
    }

    if (!$erros) {
        try {
            $id = create($pdo, 'campanhas', [
                'id_instituicao'  => (int) $instituicao['id'],
                'nome'            => $d['nome'],
                'descricao'       => $d['descricao'] !== '' ? $d['descricao'] : null,
                'endereco'        => $d['endereco'],
                'data_inicio'     => $d['data_inicio'] !== '' ? $d['data_inicio'] : null,
                'data_termino'    => $d['data_termino'] !== '' ? $d['data_termino'] : null,
                'horario'         => $horario,
                'disponibilidade' => $d['disponibilidade'],
                'status'          => $d['status'],
                'tipo'            => $d['tipo'],
                'vagas'           => $vagas,
                'vagasTotais'     => $vagasTotais,
            ]);

            // Só considera salvo se o CRUD devolveu o ID da nova campanha.
            if ($id !== '' && $id !== '0') {
                $_SESSION['campanha_cadastrada'] = true;
                header('Location: campanha.php');
                exit;
            }

            $erros[] = 'Não foi possível confirmar o cadastro da campanha. Tente novamente.';
        } catch (Exception $erro) {
            error_log($erro->getMessage());
            $erros[] = 'Não foi possível salvar a campanha. Tente novamente em instantes.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova campanha | VolunteerConnect</title>
    <link rel="stylesheet" href="css/cadastro.css">
</head>

<body>
    <header class="site-header">
        <a class="brand" href="index.php" aria-label="VolunteerConnect, início">
            <img class="brand-logo" src="imagens/logo-volunteerconnect.png" alt="">
            <span>Volunteer<span>Connect</span></span>
        </a>

        <button class="menu-toggle" type="button" aria-label="Abrir menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>

        <nav class="main-nav">
            <a href="index.php">Início</a>
            <a href="oportunidades.php">Oportunidades</a>
            <a href="login.php">Painel</a>
            <a href="index.php#como-funciona">Como funciona</a>
        </nav>

        <div class="header-actions">
            <a class="login-link" href="painel-instituicao.php">Voltar ao painel</a>
        </div>
    </header>

    <main class="signup-shell">
        <div class="forms-grid campanha-unica">
            <section class="form-card campaign-card" id="nova-campanha" aria-labelledby="campaign-title-heading">
                <div class="form-card-heading">
                    <div>
                        <span class="form-icon campaign-icon">✳</span>
                        <span class="eyebrow eyebrow-coral">PARA INSTITUIÇÕES</span>
                        <h2 id="campaign-title-heading">Divulgue uma campanha</h2>
                        <p>As instituições podem cadastrar uma ação e encontrar voluntários.</p>
                    </div>
                </div>

                <div class="campaign-tip">
                    <span>✦</span>
                    <p>Campanha da instituição <strong><?= indexEsc($instituicao['nome']) ?></strong>. Para gerenciar suas campanhas, volte ao painel da instituição.</p>
                </div>

                <?php if ($sucesso !== ''): ?>
                    <div class="form-success" role="status"><?= indexEsc($sucesso) ?></div>
                <?php endif; ?>

                <?php if ($erros): ?>
                    <div class="form-error" role="alert">
                        <ul>
                            <?php foreach ($erros as $mensagem): ?>
                                <li><?= indexEsc($mensagem) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" action="campanha.php#nova-campanha">
                    <label for="campaign-title">Título da campanha</label>
                    <input id="campaign-title" name="nome" type="text" maxlength="100" placeholder="Ex.: Aulas de reforço escolar" required value="<?= indexEsc($d['nome']) ?>">

                    <label for="campaign-description">Descrição <span class="optional-label">(opcional)</span></label>
                    <textarea id="campaign-description" name="descricao" rows="3" placeholder="Explique o objetivo e como os voluntários podem ajudar..."><?= indexEsc($d['descricao']) ?></textarea>

                    <label for="campaign-address">Endereço</label>
                    <input id="campaign-address" name="endereco" type="text" maxlength="200" placeholder="Rua, número, bairro, cidade" required value="<?= indexEsc($d['endereco']) ?>">

                    <div class="two-fields">
                        <div>
                            <label for="campaign-tipo">Tipo</label>
                            <select id="campaign-tipo" name="tipo" required>
                                <?= campanhaOpcoes($tiposCampanha, $d['tipo']) ?>
                            </select>
                        </div>
                        <div>
                            <label for="campaign-status">Status</label>
                            <select id="campaign-status" name="status" required>
                                <?= campanhaOpcoes($statusCampanha, $d['status']) ?>
                            </select>
                        </div>
                    </div>

                    <div class="two-fields">
                        <div>
                            <label for="campaign-date">Data de início <span class="optional-label">(opcional)</span></label>
                            <input id="campaign-date" name="data_inicio" type="date" value="<?= indexEsc($d['data_inicio']) ?>">
                        </div>
                        <div>
                            <label for="campaign-end">Data de término <span class="optional-label">(opcional)</span></label>
                            <input id="campaign-end" name="data_termino" type="date" value="<?= indexEsc($d['data_termino']) ?>">
                        </div>
                    </div>

                    <div class="two-fields">
                        <div>
                            <label for="campaign-time">Horário</label>
                            <input id="campaign-time" name="horario" type="time" required value="<?= indexEsc($d['horario']) ?>">
                        </div>
                        <div>
                            <label for="campaign-availability">Disponibilidade</label>
                            <select id="campaign-availability" name="disponibilidade" required>
                                <?= campanhaOpcoes($disponibilidades, $d['disponibilidade']) ?>
                            </select>
                        </div>
                    </div>

                    <div class="two-fields">
                        <div>
                            <label for="campaign-vacancies">Vagas disponíveis</label>
                            <input id="campaign-vacancies" name="vagas" type="number" min="0" max="2147483647" placeholder="Ex.: 10" required value="<?= indexEsc($d['vagas']) ?>">
                        </div>
                        <div>
                            <label for="campaign-total">Total de vagas</label>
                            <input id="campaign-total" name="vagasTotais" type="number" min="1" max="2147483647" placeholder="Ex.: 10" required value="<?= indexEsc($d['vagasTotais']) ?>">
                        </div>
                    </div>

                    <div class="campaign-actions">
                        <a class="button button-outline" href="campanha.php">Limpar campos</a>
                        <button class="button button-coral" type="submit">Publicar campanha <span>↗</span></button>
                    </div>
                </form>
            </section>
        </div>
    </main>

    <footer class="site-footer">
        <a class="brand brand-footer" href="index.php">
            <img class="brand-logo" src="imagens/logo-volunteerconnect.png" alt="">
            <span>Volunteer<span>Connect</span></span>
        </a>
        <p>Conectando pessoas a causas que importam.</p>
        <span>© 2026 VolunteerConnect</span>
    </footer>

    <script>
        var botaoMenu = document.querySelector('.menu-toggle');
        var menu = document.querySelector('.main-nav');

        if (botaoMenu && menu) {
            botaoMenu.addEventListener('click', function() {
                var aberto = menu.classList.toggle('is-open');
                botaoMenu.setAttribute('aria-expanded', aberto);
            });
        }
    </script>
</body>

</html>
