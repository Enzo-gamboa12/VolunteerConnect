<?php

// Inclui a inicialização (sessão, conexão $pdo e funções CRUD)
require_once __DIR__ . "/init.php";

// Função nativa (sem precisar de mb_strtolower) para remover acentos e padronizar texto
if (!function_exists('normalizarTexto')) {
    function normalizarTexto($texto): string
    {
        $texto = strtolower(trim((string) $texto));
        return str_replace(
            ['á', 'à', 'ã', 'â', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ú', 'ü', 'ç', 'Á', 'À', 'Ã', 'Â', 'É', 'Ê', 'Í', 'Ó', 'Ô', 'Õ', 'Ú', 'Ü', 'Ç'],
            ['a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'o', 'u', 'u', 'c', 'a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'o', 'u', 'u', 'c'],
            $texto
        );
    }
}

$busca = '';
$areas = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['filtrar'])) {
    $busca = trim((string) ($_GET['buscaFiltro'] ?? ''));
    $areasRaw = isset($_GET['area']) && is_array($_GET['area']) ? $_GET['area'] : [];
    $areas = array_map(static fn ($a): string => normalizarTexto($a), $areasRaw);
}

$contaConectada = false;
$conta = false;
$tipoConta = $_SESSION['tipo_conta'] ?? null;
$campanhas = [];
$instituicoes = [];
$inscricoes = [];
$erroBanco = false;

try {
    if ($tipoConta === 'voluntario' && isset($_SESSION['id_voluntario'])) {
        $idConta = (int) $_SESSION['id_voluntario'];
        $conta = read($pdo, 'voluntarios', 'id = ?', [$idConta]);
        $contaConectada = is_array($conta) && !empty($conta);
    } elseif ($tipoConta === 'instituicao' && isset($_SESSION['id_instituicao'])) {
        $idConta = (int) $_SESSION['id_instituicao'];
        $conta = read($pdo, 'instituicoes', 'id = ?', [$idConta]);
        $contaConectada = is_array($conta) && !empty($conta);
    }

    if ($busca !== '') {
        $campanhas = readAll($pdo, "campanhas", "nome LIKE ?", ["%{$busca}%"]);
    } else {
        $campanhas = readAll($pdo, "campanhas");
    }

    $instituicoes = readAll($pdo, "instituicoes");
    $inscricoes   = readAll($pdo, "inscricoes");
} catch (Throwable $erro) {
    error_log('Erro ao carregar os dados de oportunidades: ' . $erro->getMessage());
    $erroBanco = true;
    $conta = false;
    $contaConectada = false;
    $campanhas = [];
    $instituicoes = [];
    $inscricoes = [];
}

$instituicoesPorId = [];
foreach ($instituicoes as $inst) {
    $instituicoesPorId[(int) ($inst['id'] ?? 0)] = $inst;
}

// Filtra sempre apenas as campanhas ativas e aplica o filtro de área, se selecionado
$campanhasFiltradas = [];

foreach ($campanhas as $c) {
    $statusCampanha = normalizarTexto($c['status'] ?? '');

    // Mantém apenas campanhas com status 'ativa'
    if ($statusCampanha !== 'ativa') {
        continue;
    }

    if (!empty($areas)) {
        $inst = $instituicoesPorId[(int) ($c['id_instituicao'] ?? 0)] ?? null;
        $areaCampanha = normalizarTexto($c['tipo'] ?? '');
        $areaInstituicao = normalizarTexto($inst['area'] ?? '');
        $areaEfetiva = $areaCampanha !== '' ? $areaCampanha : $areaInstituicao;

        if (!in_array($areaEfetiva, $areas, true)) {
            continue;
        }
    }

    $campanhasFiltradas[] = $c;
}

$campanhas = $campanhasFiltradas;

$nomeConta = $contaConectada ? ($conta['nome'] ?? 'Conta') : '';
$iniciaisConta = function_exists('indexIniciais') ? indexIniciais((string) $nomeConta) : 'VC';

if ($contaConectada && $tipoConta === 'voluntario') {
    $linkMeuPainel = 'painel-voluntario.php';
} elseif ($contaConectada && $tipoConta === 'instituicao') {
    $linkMeuPainel = 'painel-instituicao.php';
} else {
    $linkMeuPainel = 'cadastro.php';
}
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
                                value="<?= indexEsc($busca) ?>"
                            >
                        </div>
                    </div>

                    <div class="filter-group">
                        <h3>Área de atuação</h3>
                        <label class="filter-option">
                            <input type="checkbox" name="area[]" value="educacao" <?= in_array('educacao', $areas, true) ? 'checked' : '' ?>> Educação
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" name="area[]" value="tecnologia" <?= in_array('tecnologia', $areas, true) ? 'checked' : '' ?>> Tecnologia
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" name="area[]" value="saude" <?= in_array('saude', $areas, true) ? 'checked' : '' ?>> Saúde
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
                    <p id="results-count" aria-live="polite">A exibir <?= count($campanhas) ?> oportunidades</p>
                </div>

                <?php if (!empty($campanhas)): ?>
                    <div class="listing-cards" id="opportunity-results">
                        <?php foreach ($campanhas as $c):
                            $inst = $instituicoesPorId[(int) ($c['id_instituicao'] ?? 0)] ?? null;
                            $inst_nome     = $inst['nome'] ?? "Instituição não informada";
                            $inst_endereco = $inst['endereco'] ?? "Localização não cadastrada";

                            $vagas_preenchidas = 0;
                            foreach ($inscricoes as $ins) {
                                if (($ins['id_campanha'] ?? 0) == ($c['id'] ?? 0) && normalizarTexto($ins['status'] ?? '') !== 'cancelado') {
                                    $vagas_preenchidas++;
                                }
                            }

                            $vagas_totais = (int) ($c['vagasTotais'] ?? $c['vagas_totais'] ?? 0);
                            $porcentagem  = $vagas_totais > 0 ? min(100, (int) round(($vagas_preenchidas / $vagas_totais) * 100)) : 0;

                            $areaCard = normalizarTexto(!empty($c['tipo']) ? $c['tipo'] : ($inst['area'] ?? 'saude'));
                            $classe_capa = "cover-health";
                            $icone       = "🩺";
                            $rotulo_area = "Saúde";

                            if ($areaCard === 'educacao') {
                                $classe_capa = "cover-education";
                                $icone       = "📚";
                                $rotulo_area = "Educação";
                            } elseif ($areaCard === 'tecnologia') {
                                $classe_capa = "cover-tech";
                                $icone       = "💻";
                                $rotulo_area = "Tecnologia";
                            }
                        ?>
                            <article class="opportunity-card">
                                <div class="card-cover <?= indexEsc($classe_capa) ?>">
                                    <div class="cover-icon"><?= indexEsc($icone) ?></div>
                                    <span class="pill"><?= indexEsc($rotulo_area) ?></span>
                                </div>

                                <div class="card-body">
                                    <h3><?= indexEsc($c['nome'] ?? '') ?></h3>
                                    <p class="card-org"><?= indexEsc($inst_nome) ?> · <?= indexEsc($inst_endereco) ?></p>

                                    <div class="card-meta">
                                        <span>📍 Presencial</span>
                                        <span>Status: <strong>Ativa</strong></span>
                                    </div>

                                    <p class="muted" style="font-size:.82rem">
                                        <?= indexEsc($c['descricao'] ?? '') ?>
                                    </p>

                                    <div class="card-progress">
                                        <div class="progress-label">
                                            <span><?= $vagas_preenchidas ?> de <?= $vagas_totais ?> vagas preenchidas</span>
                                            <strong><?= $porcentagem ?>%</strong>
                                        </div>
                                        <div class="progress-track">
                                            <span style="width: <?= $porcentagem ?>%;"></span>
                                        </div>
                                    </div>

                                    <div class="card-actions">
                                        <span class="status">Vagas abertas</span>
                                        <a class="btn btn-primary btn-small" href="detalhes-oportunidade.php?id=<?= (int) ($c['id'] ?? 0) ?>">Ver detalhes</a>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state" id="empty-state" style="display:block;">
                        <strong>Nenhuma oportunidade encontrada</strong>
                        <p class="muted">Tente alterar os filtros ou pesquisar por outro termo.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> VolunteerConnect. Todos os direitos reservados.</p>
    </footer>
</body>

</html>