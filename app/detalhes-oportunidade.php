<?php

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
}

// Recupera o ID da campanha vindo da URL ($_GET['id']). Caso não exista, assume o ID 1 como padrão.
$id_campanha = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// Busca a campanha específica na tabela 'campanhas'
$campanha = read($pdo, "campanhas", "id = $id_campanha");

// Se a campanha informada não for encontrada no banco, recupera a primeira existente
if (!$campanha) {
    $campanha = read($pdo, "campanhas", "id = 1");
}

// Busca a instituição dona desta campanha através da FK 'id_instituicao'
$instituicao = read($pdo, "instituicoes", "id = " . (int)$campanha['id_instituicao']);

// Se por algum motivo a instituição não for encontrada, cria um valor padrão legível
if (!$instituicao) {
    $instituicao = [
        'nome' => 'Instituição não informada',
        'area' => 'educacao',
        'endereco' => 'Endereço não cadastrado',
        'email' => 'contato@instituicao.org'
    ];
}

// Busca todas as inscrições para calcular o preenchimento de vagas desta campanha
$inscricoes_campanha = readAll($pdo, "inscricoes", "id_campanha = " . (int)$campanha['id']);

// Conta quantas inscrições estão ativas (ignora inscrições com status 'cancelado')
$vagas_preenchidas = 0;
if ($inscricoes_campanha) {
    foreach ($inscricoes_campanha as $ins) {
        if ($ins['status'] !== 'cancelado') {
            $vagas_preenchidas++;
        }
    }
}

// Cálculo do percentual de preenchimento para a barra de progresso
$vagas_totais = (int)$campanha['vagas'];
$porcentagem = $vagas_totais > 0 ? min(100, round(($vagas_preenchidas / $vagas_totais) * 100)) : 0;

// Configuração dinâmica de estilo e ícone com base na área da instituição ('saude', 'tecnologia', 'educacao')
$area = $instituicao['area'] ?? 'educacao';
$rotulo_area = ucfirst($area);
$classe_capa = "cover-education";
$icone = "📚";

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
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
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

    <main>
        <div class="container">
            <!-- Navegação Breadcrumb Dinâmica -->
            <div class="breadcrumb">
                <a href="index.php">Início</a> &nbsp;›&nbsp;
                <a href="oportunidades.php">Oportunidades</a> &nbsp;›&nbsp;
                <?php echo htmlspecialchars($campanha['nome']); ?>
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
                <!-- CONTEÚDO PRINCIPAL (DETALHES DA CAMPANHA) -->
                <div class="detail-main">
                    <!-- Hero / Destaque do topo -->
                    <section class="detail-hero">
                        <span class="pill">
                            <?php echo $icone; ?> <?php echo htmlspecialchars($rotulo_area); ?> · Status: <?php echo htmlspecialchars(ucfirst($campanha['status'])); ?>
                        </span>

                        <h1><?php echo htmlspecialchars($campanha['nome']); ?></h1>

                        <p><?php echo htmlspecialchars($campanha['descricao']); ?></p>

                        <div class="detail-meta">
                            <span>🏢 <?php echo htmlspecialchars($instituicao['nome']); ?></span>
                            <span>📍 <?php echo htmlspecialchars($instituicao['endereco']); ?></span>
                        </div>
                    </section>

                    <!-- Bloco Sobre a ação -->
                    <section class="content-card">
                        <h2>Sobre esta ação</h2>
                        <p><?php echo htmlspecialchars($campanha['descricao']); ?></p>
                        <p>Esta oportunidade é gerenciada pela instituição <strong><?php echo htmlspecialchars($instituicao['nome']); ?></strong>. Os voluntários selecionados receberão suporte e orientações antes do início dos trabalhos.</p>
                    </section>

                    <!-- Bloco Atividades recomendadas -->
                    <section class="content-card">
                        <h2>O que você vai fazer</h2>
                        <ul style="padding-left:20px;color:#58717f">
                            <li>Apoiar as atividades presenciais ou remotas organizadas pela equipe.</li>
                            <li>Colaborar diretamente com o público beneficiado pelo projeto.</li>
                            <li>Auxiliar na organização, acolhimento e logística da campanha.</li>
                            <li>Compartilhar ideias e experiências com outros voluntários.</li>
                        </ul>
                    </section>

                    <!-- Bloco Requisitos -->
                    <section class="content-card">
                        <h2>Requisitos</h2>
                        <ul style="padding-left:20px;color:#58717f">
                            <li>Vontade de aprender e colaborar com a causa social.</li>
                            <li>Comprometimento com as horas e datas combinadas.</li>
                            <li>Respeito às diretrizes da instituição responsável.</li>
                        </ul>
                    </section>

                    <!-- Bloco Localização e contato -->
                    <section class="content-card">
                        <h2>Local e contato</h2>
                        <p><strong><?php echo htmlspecialchars($instituicao['nome']); ?></strong><br>
                        <?php echo htmlspecialchars($instituicao['endereco']); ?></p>
                        <p class="muted" style="font-size:.82rem;margin-top:8px">
                            E-mail da instituição: <strong><?php echo htmlspecialchars($instituicao['email']); ?></strong>
                        </p>
                    </section>
                </div>

                <!-- BARRA LATERAL (FORMULÁRIO DE INSCRIÇÃO E CARD DA ONGS) -->
                <aside class="detail-side">
                    <!-- Form Card / Inscrição -->
                    <section class="content-card">
                        <span class="eyebrow">Participe desta ação</span>
                        <h2 style="margin-top:10px">Inscreva-se como voluntário</h2>
                        <p class="price-free">Gratuito</p>

                        <!-- Progresso dinamizado com a contagem de inscritos do banco -->
                        <p class="muted" style="font-size:.82rem;margin:5px 0 16px">
                            <?php echo $vagas_preenchidas; ?> de <?php echo $vagas_totais; ?> vagas já preenchidas (<?php echo $porcentagem; ?>%)
                        </p>

                        <div class="progress-track">
                            <span style="width:<?php echo $porcentagem; ?>%"></span>
                        </div>

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
                        <?php endif; ?>
                    </section>

                    <!-- Card Informativo da Instituição -->
                    <section class="content-card">
                        <h3>Sobre a instituição</h3>
                        <div class="institution-head">
                            <div class="institution-logo">
                                <?php echo strtoupper(substr((string) $instituicao['nome'], 0, 2)); ?>
                            </div>
                            <div>
                                <strong><?php echo htmlspecialchars($instituicao['nome']); ?></strong>
                                <p>Área: <?php echo htmlspecialchars($rotulo_area); ?></p>
                            </div>
                        </div>
                        <p class="muted" style="font-size:.84rem; margin-top: 10px;">
                            Localizada em <?php echo htmlspecialchars($instituicao['endereco']); ?>.
                        </p>
                    </section>

                    <!-- Card de sugestão -->
                    <section class="content-card">
                        <h3>Você também pode gostar</h3>
                        <p class="muted" style="font-size:.85rem">Conheça outras ações no sistema.</p>
                        <a class="text-link" href="oportunidades.php">Explorar oportunidades →</a>
                    </section>
                </aside>
            </div>
        </div>
    </main>

    <!-- RODAPÉ DO SITE (FOOTER) -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-bottom">
                <span>© 2026 VolunteerConnect. Projeto demonstrativo.</span>
                <a href="contato.php">Entre em contato</a>
            </div>
        </div>
    </footer>
</body>

</html>