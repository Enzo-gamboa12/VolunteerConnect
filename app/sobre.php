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

// Variáveis simples para o topo e rodapé
$tituloPagina = "Sobre a VolunteerConnect";
$nomeUsuario  = "Instituto Caminhos";
$siglaUsuario = "IC";
$anoAtual     = date("Y"); // Pega o ano atual automaticamente

// Array com os blocos de apresentação da empresa
$secoesSobre = [
    [
        "imagem" => "imagens/logo-melhorado.png",
        "alt"    => "Logo VolunteerConnect",
        "titulo" => "Quem nós somos?",
        "texto"  => "Somos uma conexão entre voluntários e instituições que procuram um bem só: ajudar quem mais precisa de ajuda."
    ],
    [
        "imagem" => "imagens/pessoa-olhando.jpg",
        "alt"    => "Voluntária olhando para o horizonte",
        "titulo" => "O que nos motiva?",
        "texto"  => "Promover campanhas para as comunidades e pessoas que precisam, mostrando que existe alguém que se importa no mundo complexo da caridade."
    ],
    [
        "imagem" => "imagens/ponto.png",
        "alt"    => "Comunidade unida",
        "titulo" => "Por que nós fazemos isso?",
        "texto"  => "Acreditamos que a solidariedade transforma vidas. Atuamos para oferecer apoio, acolhimento e esperança a quem mais precisa, fortalecendo a comunidade através do voluntariado ativo."
    ]
];

// Array com os números de impacto (cards)
$numerosImpacto = [
    [
        "titulo"    => "+5 mil famílias atendidas",
        "descricao" => "Garantimos suporte e itens de necessidades básicas para pessoas em situação de vulnerabilidade."
    ],
    [
        "titulo"    => "+1.200 crianças alcançadas",
        "descricao" => "Criamos campanhas e projetos que levam acesso à tecnologia e inclusão digital para crianças."
    ],
    [
        "titulo"    => "+800 pessoas auxiliadas",
        "descricao" => "Oferecemos suporte na resolução de questões judiciais para pessoas com deficiência motora."
    ]
];

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
    <title><?php echo $tituloPagina; ?></title>
    <link rel="stylesheet" href="css/sobre.css">
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

        <!-- Primeiro bloco exibido direto do índice 0 do array -->
        <section class="secao-tecnologia">
            <div class="container-tecnologia">
                <img src="<?php echo $secoesSobre[0]['imagem']; ?>" alt="<?php echo $secoesSobre[0]['alt']; ?>" class="foto-destaque zoom-suave">

                <div class="texto-tecnologia">
                    <h2><?php echo $secoesSobre[0]['titulo']; ?></h2>
                    <p><?php echo $secoesSobre[0]['texto']; ?></p>
                </div>
            </div>
        </section>

        <!-- Faixa intermediária -->
        <section class="secao-numeros">
            <h2 class="titulo-central">
                Solidariedade que gera resultados!
            </h2>
            <div class="container-cards">
                <h3 class="subtitulo-central">Unimos pessoas dispostas a ajudar a iniciativas sociais que fazem a diferença nas comunidades.</h3>
            </div>
        </section>

        <!-- Laço foreach para exibir os outros blocos (índices 1 e 2) -->
        <?php foreach ($secoesSobre as $indice =>$secao): ?>
            <?php if ($indice > 0): ?>
                <section class="secao-tecnologia">
                    <div class="container-tecnologia">
                        <img src="<?php echo $secao['imagem']; ?>" alt="<?php echo $secao['alt']; ?>" class="foto-destaque zoom-suave">

                        <div class="texto-tecnologia">
                            <h2><?php echo $secao['titulo']; ?></h2>
                            <p><?php echo $secao['texto']; ?></p>
                        </div>
                    </div>
                </section>
            <?php endif; ?>
        <?php endforeach; ?>

        <!-- Cards de números gerados com foreach -->
        <section class="secao-numeros">
            <h2 class="titulo-central">
                Números das mudanças de vidas
            </h2>

            <div class="container-cards">
                <?php foreach ($numerosImpacto as$card): ?>
                    <div class="card zoom-suave">
                        <h3><?php echo $card['titulo']; ?></h3>
                        <p><?php echo $card['descricao']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Seção de liderança -->
        <section class="secao-lideranca">
            <div class="container-lideranca">
                <div class="coluna-texto">
                    <h1>Conheça nossa liderança</h1>
                    <p>Conheça mais sobre o time que comanda todo esse projeto, que ajuda milhares de pessoas.</p>

                    <a href="colaboradores.php" class="botao-azul zoom-suave">Conhecer nossa liderança</a>
                </div>

                <div class="coluna-imagem">
                    <a href="colaboradores.php" class="link-foto-lideranca">
                        <img src="imagens/gamboa-na-palestra.webp" alt="Liderança em apresentação" class="foto-lideranca zoom-suave">
                    </a>
                </div>
            </div>
        </section>

    </main>

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