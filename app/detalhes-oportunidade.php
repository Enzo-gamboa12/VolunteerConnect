<?php
require_once __DIR__ . '/init.php';

<<<<<<< HEAD
// Valida a oportunidade solicitada.
$id_campanha = filter_var(
    $_GET['id'] ?? '',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if (!$id_campanha) {
    appError(404, 'Oportunidade não encontrada.');
=======
require_once __DIR__ . "/init.php";

$mensagem_sucesso = "";
$mensagem_erro = "";

if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'sucesso':
            $mensagem_sucesso = "Inscrição realizada com sucesso!";
            break;
        case 'ja_inscrito':
            $mensagem_erro = "Você já está inscrito nesta campanha!";
            break;
        case 'invalido':
            $mensagem_erro = "Por favor, preencha todos os campos obrigatórios.";
            break;
        case 'erro':
            $mensagem_erro = "Ocorreu um erro ao salvar no banco de dados.";
            break;
    }
>>>>>>> teste
}

$campanha = read(
    $pdo,
    'campanhas',
    'id = ?',
    [$id_campanha]
);

<<<<<<< HEAD
=======
// Busca a campanha específica na tabela 'campanhas'
$campanha = read($pdo, "campanhas", "id = $id_campanha");

// Se a campanha informada não for encontrada no banco, recupera a primeira existente
>>>>>>> teste
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

<<<<<<< HEAD
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
=======
if ($area === 'tecnologia') {
    $classe_capa = "cover-tech";
    $icone = "💻";
    $rotulo_area = "Tecnologia";
} elseif ($area === 'saude') {
    $classe_capa = "cover-health";
    $icone = "🩸";
    $rotulo_area = "Saúde";
}

// Dados da sessão para cabeçalho e permissão de inscrição
$contaConectada = false;
$conta = false;
$tipoConta = $_SESSION['tipo_conta'] ?? null;

if ($tipoConta === 'voluntario' && !empty($_SESSION['id_voluntario'])) {
    $idConta = (int) $_SESSION['id_voluntario'];
    $conta = read($pdo, 'voluntarios', 'id = ?', [$idConta]);
    $contaConectada = is_array($conta) && !empty($conta);
} elseif ($tipoConta === 'instituicao' && !empty($_SESSION['id_instituicao'])) {
    $idConta = (int) $_SESSION['id_instituicao'];
    $conta = read($pdo, 'instituicoes', 'id = ?', [$idConta]);
    $contaConectada = is_array($conta) && !empty($conta);
}

$nomeConta = $contaConectada ? ($conta['nome'] ?? ($_SESSION['nome_usuario'] ?? 'Conta')) : '';
$iniciaisConta = function_exists('indexIniciais') ? indexIniciais((string) $nomeConta) : 'VC';
$linkMeuPainel = $linkMeuPainel ?? ($contaConectada
    ? (($tipoConta === 'instituicao') ? 'painel-instituicao.php' : 'painel-voluntario.php')
    : 'cadastro.php');
$podeInscrever = ($campanha['status'] ?? '') === 'ativa';
>>>>>>> teste
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
<<<<<<< HEAD

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
=======
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Título dinâmico com o nome da campanha trazida do banco -->
    <title><?php echo htmlspecialchars($campanha['nome']); ?> — VolunteerConnect</title>
    <link rel="stylesheet" href="css/detalhes-oportunidades.css">
    <script src="app.js" defer></script>
</head>

<body>
    <header class="cabecalho">
        <a class="link-logo" href="index.php">
            <img class="logo" src="imagens/logo-volunteerconnect.png" alt="Logo VolunteerConnect">
            <span>Volunteer<span>Connect</span></span>
        </a>

        <nav class="menu">
            <a href="oportunidades.php">Encontre Oportunidades</a>
            <a href="<?= indexEsc($linkMeuPainel) ?>">Meu Painel</a>
            <a href="sobre.php">Sobre Nós</a>
        </nav>

        <?php if ($contaConectada): ?>
            <?php
                $linkPainel = ($tipoConta === 'voluntario')
                    ? 'painel-voluntario.php'
                    : 'painel-instituicao.php';
            ?>
            <div class="menu-usuario" title="Ir para o meu painel">
                <a href="<?= indexEsc($linkPainel) ?>">
                    <span class="avatar-usuario"><?= indexEsc($iniciaisConta) ?></span>
                    <span><?= indexEsc($nomeConta) ?></span>
                </a>
            </div>
        <?php else: ?>
            <div class="acoes-autenticacao" aria-label="Acesso à conta">
                <a class="botao-autenticacao" href="login.php">Entrar</a>
                <a class="botao-autenticacao botao-autenticacao-cadastro" href="cadastro.php">Cadastrar</a>
            </div>
        <?php endif; ?>
    </header>
>>>>>>> teste

    <main>
        <div class="container">
            <div class="breadcrumb">
<<<<<<< HEAD
                <a href="index.php">Início</a>
                &nbsp;›&nbsp;

                <a href="oportunidades.php">Oportunidades</a>
                &nbsp;›&nbsp;

                <?= indexEsc($campanha['nome']) ?>
=======
                <a href="index.php">Início</a> &nbsp;›&nbsp;
                <a href="oportunidades.php">Oportunidades</a> &nbsp;›&nbsp;
                <?php echo htmlspecialchars($campanha['nome']); ?>
>>>>>>> teste
            </div>

            <?php if ($mensagem_sucesso !== ''): ?>
                <p role="status" style="padding: 12px 0; color: #18794e; font-weight: 600;">
                    <?php echo htmlspecialchars($mensagem_sucesso); ?>
                </p>
            <?php endif; ?>

            <?php if ($mensagem_erro !== ''): ?>
                <p role="alert" style="padding: 12px 0; color: #bd3030; font-weight: 600;">
                    <?php echo htmlspecialchars($mensagem_erro); ?>
                </p>
            <?php endif; ?>

            <div class="detail-layout">
                <div class="detail-main">
                    <section class="detail-hero">
                        <span class="pill">
                            <?= indexEsc($rotulo_area) ?>
                            · Status:
                            <?= indexEsc(ucfirst($campanha['status'])) ?>
                        </span>

<<<<<<< HEAD
                        <h1>
                            <?= indexEsc($campanha['nome']) ?>
                        </h1>

                        <p>
                            <?= indexEsc($campanha['descricao']) ?>
                        </p>
=======
                        <h1><?php echo htmlspecialchars($campanha['nome']); ?></h1>

                        <p><?php echo htmlspecialchars($campanha['descricao']); ?></p>
>>>>>>> teste

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

<<<<<<< HEAD
=======
                <!-- BARRA LATERAL (FORMULÁRIO DE INSCRIÇÃO E CARD DA ONGS) -->
>>>>>>> teste
                <aside class="detail-side">
                    <section class="content-card">
<<<<<<< HEAD
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

=======
                        <span class="eyebrow">Participe desta ação</span>
                        <h2 style="margin-top:10px">Inscreva-se como voluntário</h2>
                        <p class="price-free">Gratuito</p>

                        <!-- Progresso dinamizado com a contagem de inscritos do banco -->
                        <p class="muted" style="font-size:.82rem;margin:5px 0 16px">
                            <?php echo $vagas_preenchidas; ?> de <?php echo $vagas_totais; ?> vagas já preenchidas (<?php echo $porcentagem; ?>%)
                        </p>

>>>>>>> teste
                        <div class="progress-track">
                            <span
                                style="width: <?= $porcentagem ?>%;"
                            ></span>
                        </div>

<<<<<<< HEAD
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
=======
                        <?php if ($contaConectada && $tipoConta === 'voluntario'): ?>
                            <form id="enrollment-form" action="salvarVoluntario.php" method="POST" style="display:grid;gap:13px;margin-top:20px">
                                <input type="hidden" name="id_campanha" value="<?php echo (int) $campanha['id']; ?>">

                                <p class="muted" style="font-size:.85rem">
                                    A inscrição será vinculada à sua conta (<strong><?php echo htmlspecialchars($nomeConta); ?></strong>).
                                </p>

                                <label class="checkbox-line">
                                    <input type="checkbox" name="confirmacao" value="1" required>
                                    <span>Confirmo que tenho disponibilidade para participar desta campanha.</span>
                                </label>

                                <button class="btn btn-primary btn-block" type="submit" <?php echo !$podeInscrever ? 'disabled' : ''; ?>>
                                    <?php echo $podeInscrever ? 'Quero participar →' : 'Campanha ' . ucfirst((string) $campanha['status']); ?>
                                </button>
                            </form>
                        <?php elseif ($contaConectada && $tipoConta === 'instituicao'): ?>
                            <p class="muted" style="font-size:.85rem;margin-top:16px">
                                Inscrições são realizadas por contas de voluntários.
                            </p>
                        <?php else: ?>
                            <p class="muted" style="font-size:.85rem;margin-top:16px">
                                Crie uma conta de voluntário para se inscrever nesta oportunidade.
                            </p>
                            <div style="display:grid;gap:10px;margin-top:14px">
                                <a class="btn btn-primary btn-block" href="cadastro.php">
                                    Fazer cadastro →
                                </a>
                                <a class="btn btn-outline btn-block" href="login.php">
                                    Fazer login
                                </a>
                            </div>
>>>>>>> teste
                        <?php endif; ?>
                    </section>

                    <section class="content-card">
                        <h3>Sobre a instituição</h3>

                        <div class="institution-head">
                            <div class="institution-logo">
<<<<<<< HEAD
                                <?= indexEsc(
                                    strtoupper(
                                        substr($instituicao['nome'], 0, 2)
                                    )
                                ) ?>
=======
                                <?php echo strtoupper(substr((string) $instituicao['nome'], 0, 2)); ?>
>>>>>>> teste
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
<<<<<<< HEAD

                        <a
                            class="text-link"
                            href="verificar-instituições.php"
                        >
                            Ver mais instituições →
                        </a>
=======
>>>>>>> teste
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
