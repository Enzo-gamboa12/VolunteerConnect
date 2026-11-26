<?php
require_once __DIR__ . "/init.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bloqueia qualquer tipo de cache no navegador (inclusive cache do botão Voltar)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// VERIFICAÇÃO: Só permite entrar se estiver logado como voluntário
if (($_SESSION['tipo_conta'] ?? '') !== 'voluntario' || empty($_SESSION['id_voluntario'])) {
    header("Location: login.php?erro=acesso_negado");
    exit;
}

$idVoluntario = (int) $_SESSION['id_voluntario'];
$voluntario   = read($pdo, 'voluntarios', 'id = ?', [$idVoluntario]);

// Se o ID da sessão não existir mais no banco, encerra a sessão
if (!$voluntario) {
    header("Location: index.php");
    exit;
}

$proximas_participacoes  = readAll($pdo, 'inscricoes_por_voluntario', 'id_voluntario = ? AND status = ?', [$idVoluntario, 'Aguardando']) ?: [];
$participacoes_andamento = readAll($pdo, 'inscricoes_por_voluntario', 'id_voluntario = ? AND status = ?', [$idVoluntario, 'Andamento']) ?: [];
$historico_participacoes = readAll($pdo, 'inscricoes_por_voluntario', 'id_voluntario = ? AND status = ?', [$idVoluntario, 'Finalizado']) ?: [];
$horas_voluntariado = array_sum(array_column($historico_participacoes, 'horas'));
$meta_horas = 100;
$porcentagem_meta = ($meta_horas > 0) ? min(100, round(($horas_voluntariado / $meta_horas) * 100)) : 0;

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Voluntario - VolunteerConnect</title>
    <link rel="stylesheet" href="css/painel-voluntario.css">
</head>

<body>
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

        <a href="painel-voluntario.php">
            <div class="menu-usuario">
                <span class="avatar-usuario"><?= indexIniciais($voluntario['nome']) ?></span>
                <span><?= htmlspecialchars($voluntario['nome']) ?></span>
            </div>
        </a>
    </header>

    <main>
        <aside class="menu-lateral">
            <div class="bloco-perfil">
                <span class="avatar-perfil"><?= indexIniciais($voluntario['nome']) ?></span>
                <strong><?= htmlspecialchars($voluntario['nome']) ?></strong>
            </div>

            <span class="rotulo-lateral">MENU PRINCIPAL</span>
            <a class="item-menu ativo" href="painel-voluntario.php">Visão Geral</a>
            <a class="item-menu" href="#proximos">Futuras Participações</a>
            <a class="item-menu" href="#historico">Histórico</a>
            <a class="item-menu" id="botao-sair" href="logout.php">Sair</a>

            <div class="caixa-ajuda">
                <p>Precisa de ajuda?</p>
                <a href="suporte@volunteerconnect.org">Fale com a gente ↗</a>
            </div>
        </aside>

        <div class="conteudo-painel">
            <header class="topo-boas-vindas">
                <div>
                    <h1>Olá, <?= htmlspecialchars($voluntario['nome']) ?>!</h1>
                    <p>Boas-vindas ao seu painel.</p>
                </div>
                <a class="botao-principal" href="oportunidades.php">Encontrar oportunidades ↗</a>
            </header>

            <section class="grade-estatisticas">
                <article class="cartao-estatistica">
                    <small>Horas de voluntariado</small>
                    <strong><?= $horas_voluntariado ?> <small>h</small></strong>
                </article>
                <article class="cartao-estatistica">
                    <small>Ações concluídas</small>
                    <strong><?= count($historico_participacoes); ?></strong>
                </article>
                <article class="cartao-estatistica">
                    <small>Certificados</small>
                    <strong><?= count($historico_participacoes); ?></strong>
                </article>
            </section>

            <div class="colunas-destaque">
                <section class="cartao-meta" id="meta">
                    <header class="topo-meta">
                        <div>
                            <span class="chapeu-titulo">SUA META ANUAL</span>
                        </div>
                        <span class="status status-ativo"><?= $porcentagem_meta >= 100 ? 'Concluída!' : 'Em andamento' ?></span>
                    </header>
                    <div class="numero-meta">
                        <strong><?= $horas_voluntariado ?>h</strong>
                        <span>de <?= $meta_horas ?> horas</span>
                    </div>
                    <div class="barra-progresso">
                        <span style="width: <?= $porcentagem_meta ?>%;"></span>
                    </div>
                    <footer class="rodape-meta">
                        <span>Você já completou <?= $porcentagem_meta ?>% da sua meta.</span>
                        <strong><?= $porcentagem_meta ?>%</strong>
                    </footer>
                </section>
            </div>

            <!-- SEÇÃO DE PRÓXIMAS PARTICIPAÇÕES (AGENDA) -->
            <section class="secao-proximos" id="proximos">
                <header class="cabecalho-secao">
                    <div>
                        <span class="chapeu-titulo">AGENDA</span>
                        <h2>Próximas Participações</h2>
                    </div>
                    <a class="link-texto" href="oportunidades.php">Buscar mais ↗</a>
                </header>

                <div class="grade-proximos">
                    <?php if (empty($proximas_participacoes)): ?>
                        <div class="cartao-vazio">
                            <p>Você não tem nenhuma ação agendada no momento.</p><br>
                            <a class="botao-principal" href="oportunidades.php">Encontrar oportunidades ↗</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($proximas_participacoes as $p): ?>
                            <article class="cartao-proximo">
                                <div class="topo-proximo">
                                    <span class="categoria-tag"><?= htmlspecialchars($p['categoria']) ?></span>
                                    <span class="status status-pendente">A confirmar</span>
                                </div>

                                <h3><?= htmlspecialchars($p['nome_campanha']) ?></h3>
                                <small class="nome-instituicao"><?= htmlspecialchars($p['nome_instituicao']) ?></small>

                                <div class="detalhes-proximo">
                                    <time datetime="<?= htmlspecialchars($p['data_inicio']) ?>">
                                         <?= date('d/m/Y', strtotime($p['data_inicio'])) ?>
                                    </time>
                                    <span>⏱ 
                                        <?php if ($p['horas_trabalhadas'] <= 0): ?>
                                            Carga horária ainda não definida.
                                        <?php else: ?>
                                            <?= $p['horas_trabalhadas'] ?>h estimadas
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <footer class="rodape-proximo">
                                    <a class="link-texto" href="detalhes-oportunidade.php?id=<?= $p['id_campanha'] ?>">Ver detalhes ↗</a>
                                </footer>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <section class="secao-historico" id="historico">
                <header class="cabecalho-secao">
                    <div>
                        <span class="chapeu-titulo">SUA TRAJETÓRIA</span>
                        <h2>Histórico de Participações</h2>
                    </div>
                </header>

                <div class="container-tabela">
                    <?php if (empty($historico_participacoes)): ?>
                        <p>Você ainda não concluiu nenhuma campanha.</p><br>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Ação</th>
                                    <th>Instituição</th>
                                    <th>Data</th>
                                    <th>Horas</th>
                                    <th>Situação</th>
                                    <th>Certificado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historico_participacoes as $h): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($h['nome_campanha']) ?></strong></td>
                                        <td><?= htmlspecialchars($h['nome_instituicao']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($h['data_inicio'])) ?></td>
                                        <td><?= $h['horas'] ?></td>
                                        <td><span class="status status-concluido">Concluída</span></td>
                                        <td><a class="link-texto" href="#historico">Visualizar</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <a class="link-texto" href="#historico">Ver histórico completo ↗</a>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> VolunteerConnect. Todos os direitos reservados.</p>
    </footer>
</body>

</html>