<?php

require_once __DIR__ . "/init.php";

$voluntario = read($pdo, 'voluntarios', 'id = ?', [$_SESSION['id_voluntario']]);
$proximas_participacoes = readAll($pdo, 'inscricoes_por_voluntario',  'id_voluntario = ? AND status = ?', [$_SESSION['id_voluntario'], 'Aguardando']);
$participacoes_andamento = readAll($pdo, 'inscricoes_por_voluntario',  'id_voluntario = ? AND status = ?', [$_SESSION['id_voluntario'], 'Andamento']);
$historico_participacoes = readAll($pdo, 'inscricoes_por_voluntario',  'id_voluntario = ? AND status = ?', [$_SESSION['id_voluntario'], 'Finalizado']);
$horas_voluntariado = array_sum(array_column($historico_participacoes, 'horas'));

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
            <a href="inicio.php">Início</a>
            <a href="sobre.php">Sobre Nós</a>
            <a href="index.php#como-funciona">Como funciona</a>
        </nav>

        <a href="painel-voluntario.php">
            <div class="menu-usuario">
                <span class="avatar-usuario"><?= indexIniciais($voluntario['nome']) ?></span>
                <span><?= $voluntario['nome']; ?></span>
            </div>
        </a>
    </header>

    <main>
        <aside class="menu-lateral">
            <div class="bloco-perfil">
                <span class="avatar-perfil"><?= indexIniciais($voluntario['nome']) ?></span>
                <strong><?= $voluntario['nome']; ?></strong>
            </div>

            <span class="rotulo-lateral">MENU PRINCIPAL</span>
            <a class="item-menu ativo" href="painel-voluntario.php">Visão Geral</a>
            <a class="item-menu" href="#proximos">Futuras Participações</a>
            <a class="item-menu" href="#historico">Histórico</a>

            <span class="rotulo-lateral">CONTA</span>
            <a class="item-menu" href="cadastro.php">Meu Cadastro</a>
            <a class="item-menu" href="logout.php">Sair</a>

            <div class="caixa-ajuda">
                <p>Precisa de ajuda?</p>
                <a href="mailto:ajuda@volunteerconnect.org">Fale com a gente!</a>
            </div>
        </aside>

        <div class="conteudo-painel">
            <header class="topo-boas-vindas">
                <div>
                    <h1>Olá, <?= $voluntario['nome'] ?>!</h1>
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
                <article class="cartao-estatistica">
                    <small>Pessoas impactadas</small>
                    <strong>430 <small>+</small></strong>
                </article>
            </section>

            <div class="colunas-destaque">
                <section class="cartao-meta" id="conquistas">
                    <header class="topo-meta">
                        <div>
                            <span class="chapeu-titulo">SUA META ANUAL</span>
                        </div>
                        <span class="status status-ativo">Em andamento</span>
                    </header>
                    <div class="numero-meta">
                        <strong><?= $horas_voluntariado ?>h</strong>
                        <span>de 100 horas</span>
                    </div>
                    <div class="barra-progresso">
                        <span style="width: 86%;"></span>
                    </div>
                    <footer class="rodape-meta">
                        <span>Você já completou 86% da sua meta.</span>
                        <strong>86%</strong>
                    </footer>
                </section>

                <section class="cartao-conquista">
                    <span class="chapeu-titulo">CONQUISTA RECENTE</span>
                    <div class="icone-conquista">✦</div>
                    <h2>Mãos que acolhem</h2>
                    <p>Você completou 10 ações voluntárias. Que venham as próximas!</p>
                    <a class="link-texto" href="#historico">Ver minhas conquistas ↗</a>
                </section>
            </div>

            <!-- SEÇÃO DE PRÓXIMAS PARTICIPAÇÕES (AGENDA) -->
            <section class="secao-proximos" id="proximos">
                <header class="cabecalho-secao">
                    <div>
                        <span class="chapeu-titulo">AGENDA</span>
                        <h2>Próximas participações</h2>
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
                                    <span class="categoria-tag"><?= $p['categoria'] ?></span>
                                        <span class="status status-pendente">A confirmar</span>
                                </div>

                                <h3><?= $p['nome_campanha'] ?></h3>
                                <small class="nome-instituicao"><?= $p['nome_instituicao'] ?></small>

                                <div class="detalhes-proximo">
                                    <time datetime="<?= $p['data_inicio'] ?>">
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
                                    <a class="link-texto" href="detalhes.php?id=<?= $p['campanha_id'] ?>">Ver detalhes ↗</a>
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
                                <td><strong><?= $h['nome_campanha'] ?></strong></td>
                                <td><?= $h['nome_instituicao'] ?></td>
                                <td><?= date('d/m/Y', strtotime($p['data_inicio'])) ?></time></td>
                                <td><?= $h['horas'] ?></td>
                                <td><span class="status status-concluido">Concluída</span></td>
                                <td><a class="link-texto" href="#historico">Visualizar</a></td>
                            </tr>
                        </tbody>
                    </table>
                    <a class="link-texto" href="#historico">Ver histórico completo ↗</a>
                    <?php endforeach;
                    endif; ?>
                </div>
            </section>
        </div>
    </main>

    <footer class="rodape">
        <p>© 2026 VolunteerConnect</p>
        <p>Feito para conectar boas ideias a boas atitudes.</p>
    </footer>
</body>

</html>