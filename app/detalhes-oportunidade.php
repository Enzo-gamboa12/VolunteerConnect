<?php
require_once __DIR__ . '/init.php';

// Valida a oportunidade solicitada.
$id_campanha = filter_var(
    $_GET['id'] ?? '',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if (!$id_campanha) {
    appError(404, 'Oportunidade não encontrada.');
}

$campanha = read(
    $pdo,
    'campanhas',
    'id = ?',
    [$id_campanha]
);

if (!$campanha) {
    appError(404, 'Oportunidade não encontrada.');
}

$instituicao = read(
    $pdo,
    'instituicoes',
    'id = ?',
    [(int) $campanha['id_instituicao']]
);

if (!$instituicao) {
    appError(404, 'Instituição não encontrada.');
}

// Calcula as vagas.
$inscricoes_campanha = readAll(
    $pdo,
    'inscricoes',
    'id_campanha = ?',
    [$id_campanha]
);

$vagas_preenchidas = count(
    array_filter(
        $inscricoes_campanha,
        static fn(array $inscricao): bool =>
            normalizarTexto($inscricao['status']) !== 'cancelado'
    )
);

$vagas_totais = (int) $campanha['vagasTotais'];

$vagas_disponiveis = max(
    0,
    min(
        (int) $campanha['vagas'],
        $vagas_totais - $vagas_preenchidas
    )
);

$porcentagem = $vagas_totais > 0
    ? min(100, round(100 * $vagas_preenchidas / $vagas_totais))
    : 0;

// Identifica a área.
$area = normalizarTexto($campanha['tipo'] ?? '');

$areas = [
    'saude' => 'Saúde',
    'educacao' => 'Educação',
    'tecnologia' => 'Tecnologia',
];

$rotulo_area = $areas[$area] ?? 'Área não informada';

// Mensagens de inscrição.
$mensagens = [
    'sucesso' => 'Inscrição realizada com sucesso!',
    'ja_inscrito' => 'Você já está inscrito nesta campanha.',
    'indisponivel' => 'Esta campanha não está ativa ou não possui vagas.',
    'invalido' => 'Confirme sua disponibilidade para participar.',
    'erro' => 'Não foi possível realizar a inscrição. Tente novamente.',
];

$status = appText($_GET, 'status');

$mensagem_sucesso = $status === 'sucesso'
    ? $mensagens['sucesso']
    : '';

$mensagem_erro = $status !== 'sucesso'
    ? ($mensagens[$status] ?? '')
    : '';

$podeInscrever =
    normalizarTexto($campanha['status']) === 'ativa'
    && $vagas_disponiveis > 0;

$tipo_conta = $_SESSION['tipo_conta'] ?? '';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= indexEsc($campanha['nome']) ?> — VolunteerConnect
    </title>

    <link
        rel="stylesheet"
        href="css/detalhes-oportunidades.css"
    >

    <link
        rel="stylesheet"
        href="css/navigation.css"
    >
</head>

<body>
    <!-- Único cabeçalho da página. Não adicione outro header aqui. -->
    <?php require __DIR__ . '/partials/navigation.php'; ?>

    <?php if ($mensagem_sucesso !== ''): ?>
        <p role="status" style="padding: 16px;">
            <?= indexEsc($mensagem_sucesso) ?>
        </p>
    <?php endif; ?>

    <?php if ($mensagem_erro !== ''): ?>
        <p role="alert" style="padding: 16px;">
            <?= indexEsc($mensagem_erro) ?>
        </p>
    <?php endif; ?>

    <main>
        <div class="container">
            <div class="breadcrumb">
                <a href="index.php">Início</a>
                &nbsp;›&nbsp;

                <a href="oportunidades.php">Oportunidades</a>
                &nbsp;›&nbsp;

                <?= indexEsc($campanha['nome']) ?>
            </div>

            <div class="detail-layout">
                <div class="detail-main">
                    <section class="detail-hero">
                        <span class="pill">
                            <?= indexEsc($rotulo_area) ?>
                            · Status:
                            <?= indexEsc(ucfirst($campanha['status'])) ?>
                        </span>

                        <h1>
                            <?= indexEsc($campanha['nome']) ?>
                        </h1>

                        <p>
                            <?= indexEsc($campanha['descricao']) ?>
                        </p>

                        <div class="detail-meta">
                            <span>
                                🏢 <?= indexEsc($instituicao['nome']) ?>
                            </span>

                            <span>
                                📍 <?= indexEsc($instituicao['endereco']) ?>
                            </span>
                        </div>
                    </section>

                    <section class="content-card">
                        <h2>Sobre esta ação</h2>

                        <p>
                            <?= indexEsc($campanha['descricao']) ?>
                        </p>

                        <p>
                            Esta oportunidade é gerida pela instituição
                            <strong>
                                <?= indexEsc($instituicao['nome']) ?>
                            </strong>.
                            Os voluntários selecionados receberão suporte
                            e orientações.
                        </p>
                    </section>

                    <section class="content-card">
                        <h2>O que vai fazer</h2>

                        <ul style="padding-left: 20px; color: #58717f;">
                            <li>
                                Apoiar as atividades presenciais ou remotas
                                organizadas pela equipe.
                            </li>

                            <li>
                                Colaborar diretamente com o público
                                beneficiado pelo projeto.
                            </li>

                            <li>
                                Auxiliar na organização, acolhimento
                                e logística da campanha.
                            </li>

                            <li>
                                Compartilhar ideias e experiências
                                com outros voluntários.
                            </li>
                        </ul>
                    </section>

                    <section class="content-card">
                        <h2>Requisitos</h2>

                        <ul style="padding-left: 20px; color: #58717f;">
                            <li>
                                Vontade de aprender e colaborar
                                com a causa social.
                            </li>

                            <li>
                                Comprometimento com os horários
                                e datas combinados.
                            </li>

                            <li>
                                Respeito pelas diretrizes da instituição
                                responsável.
                            </li>
                        </ul>
                    </section>

                    <section class="content-card">
                        <h2>Local e contato</h2>

                        <p>
                            <strong>
                                <?= indexEsc($instituicao['nome']) ?>
                            </strong>

                            <br>

                            <?= indexEsc($instituicao['endereco']) ?>
                        </p>

                        <p
                            class="muted"
                            style="font-size: .82rem; margin-top: 8px;"
                        >
                            E-mail da instituição:

                            <strong>
                                <?= indexEsc($instituicao['email']) ?>
                            </strong>
                        </p>
                    </section>
                </div>

                <aside class="detail-side">
                    <section class="content-card">
                        <span class="eyebrow">
                            Participe nesta ação
                        </span>

                        <h2 style="margin-top: 10px;">
                            Inscreva-se como voluntário
                        </h2>

                        <p class="price-free">
                            Gratuito
                        </p>

                        <p
                            class="muted"
                            style="font-size: .82rem; margin: 5px 0 16px;"
                        >
                            <?= $vagas_preenchidas ?>
                            de
                            <?= $vagas_totais ?>
                            vagas já preenchidas
                            (<?= $porcentagem ?>%)
                        </p>

                        <div class="progress-track">
                            <span
                                style="width: <?= $porcentagem ?>%;"
                            ></span>
                        </div>

                        <?php if ($tipo_conta === 'voluntario'): ?>
                            <form
                                id="enrollment-form"
                                action="salvarVoluntario.php"
                                method="POST"
                                style="
                                    display: grid;
                                    gap: 13px;
                                    margin-top: 20px;
                                "
                            >
                                <?= csrfField() ?>

                                <input
                                    type="hidden"
                                    name="id_campanha"
                                    value="<?= (int) $campanha['id'] ?>"
                                >

                                <p>
                                    A inscrição será vinculada à sua conta
                                    conectada, sem solicitar novamente
                                    CPF ou endereço.
                                </p>

                                <p>
                                    <?= $vagas_disponiveis ?>
                                    vagas disponíveis.
                                </p>

                                <label class="checkbox-line">
                                    <input
                                        type="checkbox"
                                        name="confirmacao"
                                        value="1"
                                        required
                                    >

                                    <span>
                                        Confirmo minha disponibilidade
                                        para participar.
                                    </span>
                                </label>

                                <button
                                    class="btn btn-primary btn-block"
                                    type="submit"
                                    <?= !$podeInscrever ? 'disabled' : '' ?>
                                >
                                    <?= $podeInscrever
                                        ? 'Quero participar'
                                        : 'Inscrições indisponíveis' ?>
                                </button>
                            </form>

                        <?php elseif ($tipo_conta === 'instituicao'): ?>
                            <p>
                                Inscrições são realizadas por contas
                                de voluntários.
                            </p>

                        <?php else: ?>
                            <a
                                class="btn btn-primary btn-block"
                                href="login.php"
                            >
                                Entre como voluntário para participar
                            </a>
                        <?php endif; ?>
                    </section>

                    <section class="content-card">
                        <h3>Sobre a instituição</h3>

                        <div class="institution-head">
                            <div class="institution-logo">
                                <?= indexEsc(
                                    strtoupper(
                                        substr($instituicao['nome'], 0, 2)
                                    )
                                ) ?>
                            </div>

                            <div>
                                <strong>
                                    <?= indexEsc($instituicao['nome']) ?>
                                </strong>

                                <p>
                                    Área:
                                    <?= indexEsc($rotulo_area) ?>
                                </p>
                            </div>
                        </div>

                        <p
                            class="muted"
                            style="font-size: .84rem; margin-top: 10px;"
                        >
                            Localizada em
                            <?= indexEsc($instituicao['endereco']) ?>.
                        </p>

                        <a
                            class="text-link"
                            href="verificar-instituições.php"
                        >
                            Ver mais instituições →
                        </a>
                    </section>

                    <section class="content-card">
                        <h3>Também pode gostar</h3>

                        <p class="muted" style="font-size: .85rem;">
                            Conheça outras ações no sistema.
                        </p>

                        <a
                            class="text-link"
                            href="oportunidades.php"
                        >
                            Explorar oportunidades →
                        </a>
                    </section>
                </aside>
            </div>
        </div>
    </main>

    <footer>
        <p>
            &copy; <?= date('Y') ?> VolunteerConnect.
            Todos os direitos reservados.
        </p>
    </footer>
</body>
</html>
