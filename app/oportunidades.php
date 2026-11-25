<?php

// Inclui a inicialização (sessão, conexão $pdo e funções CRUD)
require_once __DIR__ . "/init.php";

// Função nativa (sem precisar de mb_strtolower) para remover acentos e padronizar texto
function normalizarTexto($texto) {
    $texto = strtolower(trim((string) $texto));
    return str_replace(
        ['á', 'à', 'ã', 'â', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ú', 'ü', 'ç', 'Á', 'À', 'Ã', 'Â', 'É', 'Ê', 'Í', 'Ó', 'Ô', 'Õ', 'Ú', 'Ü', 'Ç'],
        ['a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'o', 'u', 'u', 'c', 'a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'o', 'u', 'u', 'c'],
        $texto
    );
}

$busca = '';
$areas = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['filtrar'])) {
    $busca = trim($_GET['buscaFiltro'] ?? '');
    $areas = isset($_GET['area']) && is_array($_GET['area']) ? $_GET['area'] : [];
}

if ($busca !== '') {
    $campanhas = readAll($pdo, "campanhas", "nome LIKE ?", ["%{$busca}%"]);
} else {
    $campanhas = readAll($pdo, "campanhas");
}

$instituicoes = readAll($pdo, "instituicoes");
$inscricoes   = readAll($pdo, "inscricoes");

// Filtra sempre apenas as campanhas ativas e aplica o filtro de área, se selecionado
$campanhasFiltradas = [];

foreach ($campanhas as $c) {
    $statusCampanha = normalizarTexto($c['status'] ?? '');

    // Mantém apenas campanhas com status 'ativa'
    if ($statusCampanha !== 'ativa') {
        continue;
    }

    // Se o usuário selecionou áreas no filtro, verifica se inclui 'saude'
    if (!empty($areas) && !in_array('saude' , $areas)) {
        continue;
    }

    $campanhasFiltradas[] = $c;
}

$campanhas = $campanhasFiltradas;
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oportunidades — VolunteerConnect</title>
    <link rel="stylesheet" href="css/oportunidades.css">
</head>

<body>
    <header class="site-header">
        <div class="container header-inner">
            <a class="brand" href="index.php">
                <img src="imagens/logo.png" alt="">
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
            <div class="page-heading">
                <span class="eyebrow">Encontre a sua causa</span>
                <h1>Oportunidades de voluntariado</h1>
                <p>Explore projetos, conheça instituições e escolha uma ação que combine com os seus interesses e rotina.</p>
            </div>
            
            <aside class="filter-panel">
                <form id="filters" method="GET" action="oportunidades.php">   
                    <input type="hidden" name="filtrar" value="1">
                    <h2>Filtros</h2>

                    <div class="filter-group">
                        <h3>Pesquisar oportunidade</h3>
                        <div class="form-field">
                            <input 
                                id="buscaFiltro" 
                                name="buscaFiltro" 
                                type="search" 
                                placeholder="Nome da campanha"
                                value="<?php echo htmlspecialchars($busca); ?>"
                            >
                        </div>
                    </div>

                    <div class="filter-group">
                        <h3>Área de atuação</h3>
                        <label class="filter-option">
                            <input type="checkbox" name="area[]" value="educacao" <?php echo in_array('educacao', $areas) ? 'checked' : ''; ?>> Educação
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" name="area[]" value="tecnologia" <?php echo in_array('tecnologia', $areas) ? 'checked' : ''; ?>> Tecnologia
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" name="area[]" value="saude" <?php echo in_array('saude', $areas) ? 'checked' : ''; ?>> Saúde
                        </label>
                    </div>

                    <div>
                        <button class="btn btn-primary btn-block" type="submit">Aplicar filtros</button>
                        <a class="btn btn-outline btn-block" href="oportunidades.php">Limpar filtros</a>
                    </div>
                </form>
            </aside>

            <section>
                <div class="listing-topline">
                    <p id="results-count" aria-live="polite">A exibir <?php echo count($campanhas); ?> oportunidades</p>
                </div>
                
                <?php if (!empty($campanhas)) { ?>
                    <div class="listing-cards" id="opportunity-results">
                        <?php 
                        foreach ($campanhas as $c) {
                            $inst_nome     = "Instituição não informada";
                            $inst_endereco = "Localização não cadastrada";

                            foreach ($instituicoes as $i) {
                                if ($i['id'] == $c['id_instituicao']) {
                                    $inst_nome     = $i['nome'];
                                    $inst_endereco = $i['endereco'];
                                    break;
                                }
                            }

                            $vagas_preenchidas = 0;
                            foreach ($inscricoes as $ins) {
                                if ($ins['id_campanha'] == $c['id'] && normalizarTexto($ins['status'] ?? '') !== 'cancelado') {
                                    $vagas_preenchidas++;
                                }
                            }

                            $vagas_totais = (int) ($c['vagasTotais'] ?? $c['vagas_totais'] ?? 0);
                            $porcentagem  = $vagas_totais > 0 ? min(100, round(($vagas_preenchidas / $vagas_totais) * 100)) : 0;

                            // Todas as campanhas definidas como Saúde
                            $classe_capa = "cover-health";
                            $icone       = "🩺";
                            $rotulo_area = "Saúde";
                        ?>
                            <article class="opportunity-card">
                                <div class="card-cover <?php echo $classe_capa; ?>">
                                    <div class="cover-icon"><?php echo $icone; ?></div>
                                    <span class="pill"><?php echo $rotulo_area; ?></span>
                                </div>

                                <div class="card-body">
                                    <h3><?php echo htmlspecialchars($c['nome'] ?? ''); ?></h3>
                                    <p class="card-org"><?php echo htmlspecialchars($inst_nome); ?> · <?php echo htmlspecialchars($inst_endereco); ?></p>
                                    
                                    <div class="card-meta">
                                        <span>📍 Presencial</span>
                                        <span>Status: <strong>Ativa</strong></span>
                                    </div>

                                    <p class="muted" style="font-size:.82rem">
                                        <?php echo htmlspecialchars($c['descricao'] ?? ''); ?>
                                    </p>

                                    <div class="card-progress">
                                        <div class="progress-label">
                                            <span><?php echo $vagas_preenchidas; ?> de <?php echo $vagas_totais; ?> vagas preenchidas</span>
                                            <strong><?php echo $porcentagem; ?>%</strong>
                                        </div>
                                        <div class="progress-track">
                                            <span style="width: <?php echo $porcentagem; ?>%;"></span>
                                        </div>
                                    </div>

                                    <div class="card-actions">
                                        <span class="status">Vagas abertas</span>
                                        <a class="btn btn-primary btn-small" href="detalhes-oportunidade.php?id=<?php echo $c['id']; ?>">Ver detalhes</a>
                                    </div>
                                </div>
                            </article>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <div class="empty-state" id="empty-state" style="display:block;">
                        <strong>Nenhuma oportunidade encontrada</strong>
                        <p class="muted">Tente alterar os filtros ou pesquisar por outro termo.</p>
                    </div>
                <?php } ?>
            </section>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-top">
                <div class="footer-brand">
                    <a class="brand" href="index.php">
                        <img src="imagens/logo.png" alt="">
                        <span>Volunteer<em>Connect</em></span>
                    </a>
                    <p>A conectar pessoas e boas causas.</p>
                </div>
                <div class="footer-col">
                    <h3>Explorar</h3>
                    <a href="oportunidades.php">Oportunidades</a>
                    <a href="instituicoes.php">Instituições</a>
                </div>
                <div class="footer-col">
                    <h3>Conheça</h3>
                    <a href="como-funciona.php">Como funciona</a>
                    <a href="sobre.php">Sobre</a>
                </div>
                <div class="footer-col">
                    <h3>Ajuda</h3>
                    <a href="contato.php">Contacto</a>
                    <a href="login.php">Entrar</a>
                </div>
            </div>
            <div class="footer-bottom">
                <span>© 2026 VolunteerConnect. Projeto demonstrativo.</span>
                <span>A conectar quem quer ajudar a quem precisa.</span>
            </div>
        </div>
    </footer>
</body>
</html>