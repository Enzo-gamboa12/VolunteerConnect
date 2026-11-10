<?php
// Inclui o arquivo CRUD para ter acesso ao $pdo e à função readAll()
require_once __DIR__ . "/crud.php";

// Busca os dados das tabelas do banco VolunteerConnect
$campanhas = readAll($pdo, "campanhas");
$instituicoes = readAll($pdo, "instituicoes");
$inscricoes = readAll($pdo, "inscricoes");
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oportunidades — VolunteerConnect</title>
    <link rel="stylesheet" href="css/oportunidades.css">
    <script src="app.js" defer></script>
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
        <section class="container">
            <div class="page-heading">
                <span class="eyebrow">Encontre sua causa</span>
                <h1>Oportunidades de voluntariado</h1>
                <p>Explore projetos, conheça instituições e escolha uma ação que combine com seus interesses e sua rotina.</p>
            </div>
            
            <div class="listing-layout">
                <aside class="filter-panel">
                    <form id="opportunity-filters">
                        <h2>Filtros</h2>
                        <div class="filter-group">
                            <h3>Buscar oportunidade</h3>
                            <div class="form-field">
                                <input id="keyword" type="search" placeholder="Nome da campanha ou instituição">
                            </div>
                        </div>
                        <div class="filter-group">
                            <h3>Área de atuação</h3>
                            <label class="filter-option"><input type="checkbox" name="area" value="educacao"> Educação</label>
                            <label class="filter-option"><input type="checkbox" name="area" value="tecnologia"> Tecnologia</label>
                            <label class="filter-option"><input type="checkbox" name="area" value="saude"> Saúde</label>
                        </div>
                        <div class="filter-group">
                            <h3>Localização</h3>
                            <div class="form-field">
                                <input id="filter-location" type="text" placeholder="Cidade ou estado">
                            </div>
                        </div>
                        <div class="filter-group">
                            <label class="filter-option">
                                <input id="filter-open" type="checkbox" checked> Apenas vagas abertas
                            </label>
                        </div>
                        <div style="display:grid;gap:9px">
                            <button class="btn btn-primary btn-block" type="submit">Aplicar filtros</button>
                            <button class="btn btn-outline btn-block" id="clear-filters" type="button">Limpar filtros</button>
                        </div>
                    </form>
                </aside>

                <section>
                    <div class="listing-topline">
                        <p id="results-count" aria-live="polite">Exibindo <?php echo count($campanhas); ?> oportunidades</p>
                        <label class="muted" style="font-size:.8rem">
                            Ordenar por 
                            <select id="sort-order">
                                <option value="featured">Destaque</option>
                                <option value="title">Nome (A–Z)</option>
                            </select>
                        </label>
                    </div>
                    <?php $vagas_preenchidas = 0; ?>

                    <?php if (!empty($campanhas)){ ?>
                        <div class="listing-cards" id="opportunity-results">
                            <?php 
    foreach ($campanhas as $c) {
        // 1. Localiza a instituição correspondente
        $inst_nome = "Instituição não informada";
        $inst_area = "Geral";
        $inst_endereco = "Localização não cadastrada";

        foreach ($instituicoes as $i) {
            if ($i['id'] == $c['id_instituicao']) {
                $inst_nome = $i['nome'];
                $inst_area = $i['area'];
                $inst_endereco = $i['endereco'];
                break;
            }
        }

        // 2. Contagem real das inscrições ativas para a campanha atual
        $vagas_preenchidas = 0;
        foreach ($inscricoes as $ins) {
            if ($ins['id_campanha'] == $c['id'] && $ins['status'] !== 'cancelado') {
                $vagas_preenchidas++;
            }
        }

        // 3. Obtenção do total de vagas e cálculo da percentagem
        $vagas_totais = (int) $c['vagasTotais'];
        $porcentagem = $vagas_totais > 0 ? min(100, round(($vagas_preenchidas / $vagas_totais) * 100)) : 0;

        // 4. Definição do estilo e ícone conforme a área
        $classe_capa = "cover-education";
        $icone = "📚";
        $rotulo_area = ucfirst($inst_area);

        if ($inst_area === 'tecnologia') {
            $classe_capa = "cover-tech";
            $icone = "💻";
            $rotulo_area = "Tecnologia";
        } elseif ($inst_area === 'saude') {
            $classe_capa = "cover-health";
            $icone = "🩸";
            $rotulo_area = "Saúde";
        }   
    ?>
        <article class="opportunity-card" 
                data-title="<?php echo htmlspecialchars(mb_strtolower($c['nome'])); ?>" 
                data-category="<?php echo htmlspecialchars(mb_strtolower($inst_area)); ?>" 
                data-location="<?php echo htmlspecialchars(mb_strtolower($inst_endereco)); ?>" 
                data-status="<?php echo htmlspecialchars($c['status']); ?>">
            
            <div class="card-cover <?php echo $classe_capa; ?>">
                <div class="cover-icon"><?php echo $icone; ?></div>
                <span class="pill"><?php echo htmlspecialchars($rotulo_area); ?></span>
            </div>

            <div class="card-body">
                <h3><?php echo htmlspecialchars($c['nome']); ?></h3>
                <p class="card-org"><?php echo htmlspecialchars($inst_nome); ?> · <?php echo htmlspecialchars($inst_endereco); ?></p>
                
                <div class="card-meta">
                    <span>📍 Presencial</span>
                    <span>Status: <strong><?php echo htmlspecialchars(ucfirst($c['status'])); ?></strong></span>
                </div>

                <p class="muted" style="font-size:.82rem">
                    <?php echo htmlspecialchars($c['descricao']); ?>
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
                    <span class="status">
                        <?php echo ($c['status'] === 'ativa') ? 'Vagas abertas' : ucfirst($c['status']); ?>
                    </span>
                    <a class="btn btn-primary btn-small" href="detalhes-oportunidade.php?id=<?php echo $c['id']; ?>">Ver detalhes</a>
                </div>
            </div>
        </article>
    <?php } ?>
                        </div>
                    <?php }else { ?>
                                <div class="empty-state" id="empty-state" style="<?php echo empty($campanhas) ? 'display:block;' : ''; ?>">
                                    <strong>Nenhuma oportunidade encontrada</strong>
                                    <p class="muted">Tente alterar os filtros ou buscar por outro termo.</p>
                                </div>
                            <?php } ?>
                </section>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-top">
                <div class="footer-brand">
                    <a class="brand" href="index.php">
                        <img src="imagens/logo.png" alt="">
                        <span>Volunteer<em>Connect</em></span>
                    </a>
                    <p>Conectando pessoas e boas causas.</p>
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
                    <a href="contato.php">Contato</a>
                    <a href="login.php">Entrar</a>
                </div>
            </div>
            <div class="footer-bottom">
                <span>© 2026 VolunteerConnect. Projeto demonstrativo.</span>
                <span>Conectando quem quer ajudar a quem precisa.</span>
            </div>
        </div>
    </footer>
</body>

</html>