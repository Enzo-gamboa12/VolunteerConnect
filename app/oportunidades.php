<?php
require_once __DIR__ . '/init.php';
$busca = appText($_GET, 'buscaFiltro');
$areas = isset($_GET['area']) && is_array($_GET['area']) ? array_values(array_intersect($_GET['area'], ['saude','tecnologia','educacao'])) : [];
$campanhas = $busca !== '' ? readAll($pdo, 'campanhas', 'nome LIKE ?', ['%'.$busca.'%']) : readAll($pdo, 'campanhas');
$campanhas = array_values(array_filter($campanhas, static function(array $c) use ($areas): bool {
    return normalizarTexto($c['status'] ?? '') === 'ativa' && (!$areas || in_array(normalizarTexto($c['tipo'] ?? ''), $areas, true));
}));
$instituicoes = readAll($pdo, 'instituicoes');
$inscricoes = readAll($pdo, 'inscricoes');
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oportunidades — VolunteerConnect</title>
    <link rel="stylesheet" href="css/oportunidades.css">
    <link rel="stylesheet" href="css/navigation.css">
    <script src="js/navigation.js" defer></script>
</head>

<body>
    <?php require __DIR__ . '/partials/navigation.php'; ?>

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
                                value="<?php echo indexEsc($busca); ?>"
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

                            $tipo = normalizarTexto($c['tipo'] ?? '');
                            $visual = ['saude'=>['cover-health','Saúde'], 'tecnologia'=>['cover-tech','Tecnologia'], 'educacao'=>['cover-education','Educação']][$tipo] ?? ['cover-education','Área não informada'];
                            [$classe_capa, $rotulo_area] = $visual;
                            $icone = '';
                        ?>
                            <article class="opportunity-card">
                                <div class="card-cover <?php echo $classe_capa; ?>">
                                    <div class="cover-icon"><?php echo $icone; ?></div>
                                    <span class="pill"><?php echo $rotulo_area; ?></span>
                                </div>

                                <div class="card-body">
                                    <h3><?php echo indexEsc($c['nome'] ?? ''); ?></h3>
                                    <p class="card-org"><?php echo indexEsc($inst_nome); ?> · <?php echo indexEsc($inst_endereco); ?></p>
                                    
                                    <div class="card-meta">
                                        <span>📍 Presencial</span>
                                        <span>Status: <strong>Ativa</strong></span>
                                    </div>

                                    <p class="muted" style="font-size:.82rem">
                                        <?php echo indexEsc($c['descricao'] ?? ''); ?>
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
                                        <span class="status"><?= (int)$c['vagas'] > 0 ? 'Vagas abertas' : 'Sem vagas' ?></span>
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

    <footer>
        <p>&copy; <?= date('Y') ?> VolunteerConnect. Todos os direitos reservados.</p>
    </footer>
</body>
</html>