<?php


require_once __DIR__ . "/crud.php";

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

// Se a campanha informada não for encontrada no banco, redireciona ou recupera a primeira existente
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
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Título dinâmico com o nome da campanha trazida do banco -->
    <title><?php echo htmlspecialchars($campanha['nome']); ?> — VolunteerConnect</title>
    <link rel="stylesheet" href="css/detalhes-oportunidade.css">
    <script src="app.js" defer></script>
</head>

<body>
    <!-- CABEÇALHO DO SITE (HEADER) -->
    <header class="site-header">
        <div class="container header-inner">
            <a class="brand" href="index.php">
                <img src="imagens/logo.png" alt="Logo VolunteerConnect">
                <span>Volunteer<em>Connect</em></span>
            </a>
            <button class="menu-toggle" type="button" aria-expanded="false" aria-label="Abrir menu">☰</button>
            <nav class="main-nav">
                <a href="oportunidades.php" aria-current="page">Oportunidades</a>
                <a href="instituicoes.php">Instituições</a>
                <a href="como-funciona.php">Como funciona</a>
                <a href="sobre.php">Sobre</a>
            </nav>
            <div class="header-actions">
                <a class="btn btn-light btn-small" href="login.php">Entrar</a>
                <a class="btn btn-primary btn-small" href="cadastro-e-campanha.php">Cadastrar-se</a>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <!-- Navegação Breadcrumb Dinâmica -->
            <div class="breadcrumb">
                <a href="index.php">Início</a> &nbsp;›&nbsp; 
                <a href="oportunidades.php">Oportunidades</a> &nbsp;›&nbsp; 
                <?php echo htmlspecialchars($campanha['nome']); ?>
            </div>

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

                <!--  4. BARRA LATERAL (FORMULÁRIO DE INSCRIÇÃO E CARD DA ONGS)-->
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

                        <!-- Formulário de Inscrição -->
                        <form id="enrollment-form" action="salvarVoluntario.php" method="POST" style="display:grid;gap:13px;margin-top:20px">   
                            <!-- ID Oculto da Campanha -->
                            <input type="hidden" name="id_campanha" value="<?php echo $campanha['id']; ?>">

                            <div class="form-field">
                                <label for="enroll-name">Nome completo</label>
                                <input id="enroll-name" name="name" required placeholder="Seu nome">
                            </div>
                            <div class="form-field">
                                <label for="enroll-email">E-mail</label>
                                <input id="enroll-email" name="email" type="email" required placeholder="voce@email.com">
                            </div>
                            <div class="form-field">
                                <label for="enroll-cpf">CPF</label>
                                <input id="enroll-cpf" name="cpf" type="text" required placeholder="XXX.XXX.XXX-XX">
                            </div>
                            <div class="form-field">
                                <label for="enroll-endereco">Endereço</label>
                                <input id="enroll-endereco" name="endereco" type="text" required placeholder="Rua XXXX, 000">
                            </div>

                            <label class="checkbox-line">
                                <input type="checkbox" required>
                                <span>Confirmo que tenho disponibilidade para participar desta campanha.</span>
                            </label>

                            <button class="btn btn-primary btn-block" type="submit" <?php echo ($campanha['status'] !== 'ativa') ? 'disabled' : ''; ?>>
                                <?php echo ($campanha['status'] === 'ativa') ? 'Quero participar →' : 'Campanha ' . ucfirst($campanha['status']); ?>
                            </button>
                        </form>
                    </section>

                    <!-- Card Informativo da Instituição -->
                    <section class="content-card">
                        <h3>Sobre a instituição</h3>
                        <div class="institution-head">
                            <div class="institution-logo">
                                <?php echo strtoupper(substr($instituicao['nome'], 0, 2)); ?>
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