<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel da Instituição | VolunteerConnect</title>
    <link rel="stylesheet" href="css/painel-instituicao.css">
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

        <div class="menu-usuario">
            <span class="avatar-usuario">IC</span>
            <span>Instituto Caminhos</span>
        </div>
    </header>

    <main class="painel-principal">
        <aside class="menu-lateral">
            <div class="bloco-perfil">
                <span class="avatar-perfil">IC</span>
                <strong>Instituto Caminhos</strong>
            </div>

            <span class="rotulo-lateral">GESTÃO</span>
            <a class="item-menu ativo" href="painel-instituicao.php">Visão Geral</a>
            <a class="item-menu" href="#campanhas">Minhas Campanhas</a>
            <a class="item-menu" href="#participantes">Participantes</a>

            <span class="rotulo-lateral">CONTA</span>
            <a class="item-menu" href="cadastro.php">Dados da Instituição</a>
            <a class="item-menu" href="index.php">Sair</a>

            <div class="caixa-ajuda">
                <p>Precisa de ajuda?</p>
                <a href="mailto:ajuda@volunteerconnect.org">Fale com a gente ↗</a>
            </div>
        </aside>

        <div class="conteudo-painel">
            <header class="topo-boas-vindas">
                <div>
                    <h1>Olá, Instituto Caminhos!</h1>
                    <p>Boas-vindas ao seu painel.</p>
                </div>
                <a class="botao-principal" href="cadastro.php#nova-campanha">+ Nova campanha</a>
            </header>

            <section class="grade-estatisticas">
                <article class="cartao-estatistica">
                    <small>Campanhas ativas</small>
                    <strong>5</strong>
                </article>
                <article class="cartao-estatistica">
                    <small>Voluntários inscritos</small>
                    <strong>128</strong>
                </article>
                <article class="cartao-estatistica">
                    <small>Horas doadas</small>
                    <strong>1.540 <small>h</small></strong>
                </article>
            </section>

            <section class="secao-campanhas" id="campanhas">
                <header class="cabecalho-secao">
                    <div>
                        <h2>Minhas Campanhas</h2>
                    </div>
                </header>

                <div class="container-tabela">
                    <table>
                        <thead>
                            <tr>
                                <th>Campanha</th>
                                <th>Período</th>
                                <th>Vagas</th>
                                <th>Situação</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <strong>Aulas de reforço escolar</strong>
                                    <small>Educação</small>
                                </td>
                                <td>01/11 a 20/12/2026</td>
                                <td>12/20</td>
                                <td><span class="status status-ativo">Em andamento</span></td>
                                <td><a href="detalhes.php?id=1">Ver ↗</a></td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Plantio de mudas no bairro</strong>
                                    <small>Meio ambiente</small>
                                </td>
                                <td>15/11/2026</td>
                                <td>8/15</td>
                                <td><span class="status status-ativo">Inscrições abertas</span></td>
                                <td><a href="detalhes.php?id=2">Ver ↗</a></td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Oficina de leitura</strong>
                                    <small>Educação</small>
                                </td>
                                <td>01/10 a 30/10/2026</td>
                                <td>10/10</td>
                                <td><span class="status status-encerrado">Encerrada</span></td>
                                <td><a href="#campanhas">Relatório ↗</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="secao-participantes" id="participantes">
                <header class="cabecalho-secao">
                    <div>
                        <h2>Participantes Recentes</h2>
                    </div>
                </header>

                <ul class="lista-participantes">
                    <li class="item-participante">
                        <span class="avatar-pessoa">MA</span>
                        <div>
                            <strong>Maria Alves</strong>
                            <small>Aulas de reforço escolar</small>
                        </div>
                        <time datetime="2026-11-07">07/11/2026</time>
                        <span class="status status-ativo">Confirmada</span>
                        <button class="botao-opcoes" aria-label="Mais opções">···</button>
                    </li>
                    <li class="item-participante">
                        <span class="avatar-pessoa">JP</span>
                        <div>
                            <strong>João Pedro</strong>
                            <small>Plantio de mudas no bairro</small>
                        </div>
                        <time datetime="2026-11-06">06/11/2026</time>
                        <span class="status status-pendente">Aguardando</span>
                        <button class="botao-opcoes" aria-label="Mais opções">···</button>
                    </li>
                    <li class="item-participante">
                        <span class="avatar-pessoa">LC</span>
                        <div>
                            <strong>Luiza Costa</strong>
                            <small>Aulas de reforço escolar</small>
                        </div>
                        <time datetime="2026-11-05">05/11/2026</time>
                        <span class="status status-ativo">Confirmada</span>
                        <button class="botao-opcoes" aria-label="Mais opções">···</button>
                    </li>
                </ul>
            </section>
        </div>
    </main>
</body>

</html>