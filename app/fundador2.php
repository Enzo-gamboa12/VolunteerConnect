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
            static fn (array $campanha): bool => ($campanha['status'] ?? '') === 'Ativa'
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

// Dados do fundador armazenados em variáveis
$nomeFundador   = "Gustavo Fazani";
$cargoResumo    = "Idealizador.";
$fotoFundador   = "imagens/gustavo.webp";
$tituloSecao    = "Cargo:";
$descricaoBio   = "Como Idealizador da VolunteerConnect, Gustavo é o responsável por manter viva a essência, o propósito e a visão de impacto social do projeto, guiando os rumos da plataforma para atender às reais necessidades das comunidades. A ideia da empresa nasceu da sua vontade de tornar o voluntariado mais acessível, direto e confiável, eliminando a distância entre quem quer fazer o bem e as instituições que precisam de apoio. A partir dessa inquietação, reuniu a equipe fundadora e deu início ao conceito da VolunteerConnect, transformando o desejo de ajudar em uma ponte real de solidariedade.";
$anoAtual       = date("Y");

$nomeConta = $contaConectada ? ($conta['nome'] ?? 'Conta') : '';
$iniciaisConta = indexIniciais((string)$nomeConta);

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
    <title><?php echo $nomeFundador; ?> - Fundadores</title>
    <link rel="stylesheet" href="css/fundador.css">
</head>

<body>
    <header class="cabecalho">
        <a class="link-logo" href="index.php">
            <img class="logo" src="/imagens/logo-volunteerconnect.png" alt="Logo VolunteerConnect">
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

    <section class="card-secao">
        <div class="perfil-topo">
            <img class="imagem-card" src="<?php echo $fotoFundador; ?>" alt="<?php echo $nomeFundador; ?>">
            <div class="nomeie">
                <h1><?php echo $nomeFundador; ?></h1>
                <small><?php echo $cargoResumo; ?></small>
            </div>
        </div>

        <div class="perfil-baixo">
            <h2><?php echo $tituloSecao; ?></h2>
            <p><?php echo $descricaoBio; ?></p>
        </div>
    </section>

    <footer class="rodape-simples">
        <div class="rodape-conteudo">
            <p>&copy; <?php echo $anoAtual; ?> VolunteerConnect. Todos os direitos reservados.</p>
            <ul class="rodape-links">
                <li><a href="sobre.php">Sobre Nós</a></li>
                <li><a href="#">Termos de Uso</a></li>
                <li><a href="#">Política de Privacidade</a></li>
            </ul>
        </div>
    </footer>
</body>

</html>