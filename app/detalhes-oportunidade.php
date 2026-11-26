<?php

try {
    // O init.php inicia a sessão, cria a conexão PDO ($pdo) e carrega as funções do CRUD.
    require_once __DIR__ . '/init.php';

    /*
     * Só existe uma conta conectada quando o login definiu os dados da sessão.
     * Sem essas informações, a página não assume uma conta demonstrativa.
     */

    $contaConectada = false;
    $tipoConta = $_SESSION['tipo_conta'] ?? null;

    if ($tipoConta === 'voluntario' && isset($_SESSION['id_voluntario'])) {
        $idConta = (int) $_SESSION['id_voluntario'];
        $conta = read($pdo, 'voluntarios', 'id = ?', [$idConta]);
        $contaConectada = is_array($conta) && !empty($conta);
    } elseif ($tipoConta === 'instituicao' && isset($_SESSION['id_instituicao'])) {
        $idConta = (int) $_SESSION['id_instituicao'];
        $conta = read($pdo, 'instituicoes', 'id = ?', [$idConta]);
        $contaConectada = is_array($conta) && !empty($conta);
    }

    // READ ALL: carrega as tabelas usadas nos cards e nos indicadores inferiores.
    $campanhas = readAll($pdo, 'campanhas');
    $instituicoes = readAll($pdo, 'instituicoes');
    $voluntarios = readAll($pdo, 'voluntarios');

    // Na área de destaque aparecem somente campanhas que estão ativas.
    $campanhasDestaque = array_slice(
        array_values(array_filter(
            $campanhas,
            static fn (array $campanha): bool => ($campanha['status'] ?? '') === 'ativa'
        )),
        0,
        3
    );
} catch (Throwable $erro) {
    // Registra o detalhe no log do PHP sem expor informações do banco na página.
    error_log('Erro ao carregar os dados da página inicial: ' . $erro->getMessage());
    $erroBanco = true;
    $conta = false;
    $contaConectada = false;
    $campanhas = [];
    $campanhasDestaque = [];
    $instituicoes = [];
    $voluntarios = [];
}

$nomeConta = $contaConectada ? ($conta['nome'] ?? 'Conta') : '';
$iniciaisConta = indexIniciais((string)$nomeConta);

if ($contaConectada && $tipoConta === 'voluntario') {
    $linkMeuPainel = 'painel-voluntario.php';
} elseif ($contaConectada && $tipoConta === 'instituicao') {
    $linkMeuPainel = 'painel-instituicao.php';
} else {
    $linkMeuPainel = 'cadastro.php';
}

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

// Recupera o ID da campanha vindo da URL. Assume 1 como padrão.
$id_campanha = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// Busca a campanha usando Prepared Statements (Segurança)
$campanha = read($pdo, "campanhas", "id = ?", [$id_campanha]);

// Se não encontrar, recupera a primeira existente
if (!$campanha) {
    $campanha = read($pdo, "campanhas", "id = ?", [1]);
}

// Busca a instituição dona através da FK, também com Prepared Statement
$instituicao = read($pdo, "instituicoes", "id = ?", [(int)$campanha['id_instituicao']]);

if (!$instituicao) {
    $instituicao = [
        'nome' => 'Instituição não informada',
        'area' => 'educacao',
        'endereco' => 'Endereço não cadastrado',
        'email' => 'contato@instituicao.org'
    ];
}

// Busca todas as inscrições para calcular o preenchimento de vagas
$inscricoes_campanha = readAll($pdo, "inscricoes", "id_campanha = ?", [(int)$campanha['id']]);

$vagas_preenchidas = 0;
if ($inscricoes_campanha) {
    foreach ($inscricoes_campanha as $ins) {
        if ($ins['status'] !== 'cancelado') {
            $vagas_preenchidas++;
        }
    }
}

// Correção: Uso padronizado de 'vagasTotais' em vez de 'vagas'
$vagas_totais = (int)($campanha['vagasTotais'] ?? 0);
$porcentagem = $vagas_totais > 0 ? min(100, round(($vagas_preenchidas / $vagas_totais) * 100)) : 0;

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
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($campanha['nome']); ?> — VolunteerConnect</title>
    <link rel="stylesheet" href="css/detalhes-oportunidades.css">
    <script src="app.js" defer></script>
</head>

<body>

    <?php if (!empty($mensagem_sucesso)): ?>
        <script>
            alert("<?php echo $mensagem_sucesso; ?>");
            
            window.history.replaceState(null, null, window.location.pathname + "?id=<?php echo $id_campanha; ?>");
        </script>
    <?php endif; ?>

    <?php if (!empty($mensagem_erro)): ?>
        <script>
            alert("<?php echo $mensagem_erro; ?>");
            window.history.replaceState(null, null, window.location.pathname + "?id=<?php echo $id_campanha; ?>");
        </script>
    <?php endif; ?>

    <header class="cabecalho">
        <a class="link-logo" href="index.php">
            <img class="logo" src="imagens/logo-volunteerconnect.png" alt="Logo VolunteerConnect">
            <span>Volunteer<span>Connect</span></span>
        </a>

        <nav class="menu">
            <a href="oportunidades.php">Encontre Oportunidades</a>
            <a href="<?= $linkMeuPainel ?>">Meu Painel</a>
            <a href="sobre.php">Sobre Nós</a>
        </nav>

        <?php if ($contaConectada): ?>
            <?php 
                $linkPainel = ($tipoConta === 'voluntario') 
                    ? 'painel-voluntario.php' 
                    : 'painel-instituicao.php'; 
            ?>
            <div class="menu-usuario" title="Ir para o meu painel">
                <a href="<?= $linkPainel ?>">
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
            <div class="breadcrumb">
                <a href="index.php">Início</a> &nbsp;›&nbsp; 
                <a href="oportunidades.php">Oportunidades</a> &nbsp;›&nbsp; 
                <?php echo htmlspecialchars($campanha['nome']); ?>
            </div>

            <div class="detail-layout">
                <div class="detail-main">
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

                    <section class="content-card">
                        <h2>Sobre esta ação</h2>
                        <p><?php echo htmlspecialchars($campanha['descricao']); ?></p>
                        <p>Esta oportunidade é gerida pela instituição <strong><?php echo htmlspecialchars($instituicao['nome']); ?></strong>. Os voluntários selecionados receberão suporte e orientações.</p>
                    </section>

                    <section class="content-card">
                        <h2>O que vai fazer</h2>
                        <ul style="padding-left:20px;color:#58717f">
                            <li>Apoiar as atividades presenciais ou remotas organizadas pela equipa.</li>
                            <li>Colaborar diretamente com o público beneficiado pelo projeto.</li>
                            <li>Auxiliar na organização, acolhimento e logística da campanha.</li>
                            <li>Partilhar ideias e experiências com outros voluntários.</li>
                        </ul>
                    </section>

                    <section class="content-card">
                        <h2>Requisitos</h2>
                        <ul style="padding-left:20px;color:#58717f">
                            <li>Vontade de aprender e colaborar com a causa social.</li>
                            <li>Comprometimento com as horas e datas combinadas.</li>
                            <li>Respeito pelas diretrizes da instituição responsável.</li>
                        </ul>
                    </section>

                    <section class="content-card">
                        <h2>Local e Contato</h2>
                        <p><strong><?php echo htmlspecialchars($instituicao['nome']); ?></strong><br>
                        <?php echo htmlspecialchars($instituicao['endereco']); ?></p>
                        <p class="muted" style="font-size:.82rem;margin-top:8px">
                            E-mail da instituição: <strong><?php echo htmlspecialchars($instituicao['email']); ?></strong>
                        </p>
                    </section>
                </div>

                <aside class="detail-side">
                    <section class="content-card">
                        <span class="eyebrow">Participe nesta ação</span>
                        <h2 style="margin-top:10px">Inscreva-se como voluntário</h2>
                        <p class="price-free">Gratuito</p>
                        
                        <p class="muted" style="font-size:.82rem;margin:5px 0 16px">
                            <?php echo $campanha['vagas']; ?> de <?php echo $vagas_totais; ?> vagas já preenchidas (<?php echo $porcentagem; ?>%)
                        </p>
                        
                        <div class="progress-track">
                            <span style="width:<?php echo $porcentagem; ?>%"></span>
                        </div>

                        <form id="enrollment-form" action="salvarVoluntario.php" method="POST" style="display:grid;gap:13px;margin-top:20px">   
                            <input type="hidden" name="id_campanha" value="<?php echo (int)$campanha['id']; ?>">

                            <div class="form-field">
                                <label for="enroll-name">Nome completo</label>
                                <input id="enroll-name" name="name" required placeholder="O seu nome">
                            </div>
                            <div class="form-field">
                                <label for="enroll-email">E-mail</label>
                                <input id="enroll-email" name="email" type="email" required placeholder="voce@email.com">
                            </div>
                            <div class="form-field">
                                <label for="enroll-cpf">NIF / CPF</label>
                                <input id="enroll-cpf" name="cpf" type="text" required placeholder="XXX.XXX.XXX-XX">
                            </div>
                            <div class="form-field">
                                <label for="enroll-endereco">Endereço</label>
                                <input id="enroll-endereco" name="endereco" type="text" required placeholder="Rua XXXX, 000">
                            </div>

                            <label class="checkbox-line">
                                <input type="checkbox" required>
                                <span>Confirmo que tenho disponibilidade para participar nesta campanha.</span>
                            </label>

                            <button class="btn btn-primary btn-block" type="submit" <?php echo ($campanha['status'] !== 'ativa') ? 'disabled' : ''; ?>>
                                <?php echo ($campanha['status'] === 'ativa') ? 'Quero participar →' : 'Campanha ' . ucfirst($campanha['status']); ?>
                            </button>
                        </form>
                    </section>

                    <section class="content-card">
                        <h3>Sobre a instituição</h3>
                        <div class="institution-head">
                            <div class="institution-logo">
                                <?php echo htmlspecialchars(strtoupper(substr($instituicao['nome'], 0, 2))); ?>
                            </div>
                            <div>
                                <strong><?php echo htmlspecialchars($instituicao['nome']); ?></strong>
                                <p>Área: <?php echo htmlspecialchars($rotulo_area); ?></p>
                            </div>
                        </div>
                        <p class="muted" style="font-size:.84rem; margin-top: 10px;">
                            Localizada em <?php echo htmlspecialchars($instituicao['endereco']); ?>.
                        </p>
                        <a class="text-link" href="instituicoes.php">Ver mais instituições →</a>
                    </section>

                    <section class="content-card">
                        <h3>Também pode gostar</h3>
                        <p class="muted" style="font-size:.85rem">Conheça outras ações no sistema.</p>
                        <a class="text-link" href="oportunidades.php">Explorar oportunidades →</a>
                    </section>
                </aside>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> VolunteerConnect. Todos os direitos reservados.</p>
    </footer>
</body>
</html>