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

$tituloPagina = "Liderança do VolunteerConnect: executivos e líderes da empresa";
$nomeUsuario  = "Instituto Caminhos";
$siglaUsuario = "IC";
$anoAtual     = date("Y");

// Primeira fileira de líderes
$lideresLinha1 = [
    [
        "nome"   => "Enzo Gamboa",
        "cargo"  => "Gerente da VolunteerConnect.",
        "imagem" => "imagens/enzo.webp",
        "link"   => "fundador.php"
    ],
    [
        "nome"   => "Nichollas Mastropietro",
        "cargo"  => "Diretor da VolunteerConnect.",
        "imagem" => "imagens/nichollas.webp",
        "link"   => "fundador1.php"
    ],
    [
        "nome"   => "Gustavo Fazini",
        "cargo"  => "Idealizador da VolunteerConnect.",
        "imagem" => "imagens/gustavo.webp",
        "link"   => "fundador2.php"
    ]
];

// Segunda fileira de líderes
$lideresLinha2 = [
    [
        "nome"   => "Antonio Gabriel",
        "cargo"  => "Desenvolvedor da VolunteerConnect.",
        "imagem" => "imagens/antonio-fundador.jpg",
        "link"   => "fundador3.php"
    ],
    [
        "nome"   => "Nycolas Campos",
        "cargo"  => "Desenvolvedor da VolunteerConnect.",
        "imagem" => "imagens/nycolas.webp",
        "link"   => "fundador4.php"
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
    <link rel="stylesheet" href="css/colaboradores.css">
    <link rel="stylesheet" href="css/navigation.css">
    <script src="js/navigation.js" defer></script>
</head>

<body>
    <?php require __DIR__ . '/partials/navigation.php'; ?>

    <main class="conteudo">

        <!-- Primeira seção de Fundadores -->
        <section class="card-secao-categoria">
            <div class="colaboradores-titulo">
                <h1>Liderança da VolunteerConnect</h1>
            </div>

            <div class="grid-produtos">
                <?php foreach ($lideresLinha1 as $lider): ?>
                    <div class="card-produto">
                        <a href="<?php echo $lider['link']; ?>">
                            <?php if (!empty($lider['imagem'])): ?>
                                <img src="<?php echo $lider['imagem']; ?>" alt="<?php echo $lider['nome']; ?>">
                            <?php endif; ?>
                            <h3><?php echo $lider['nome']; ?></h3>
                        </a>
                        <p><?php echo $lider['cargo']; ?></p>
                        <a href="<?php echo $lider['link']; ?>" class="link-clean">Ver a Bio</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Segunda seção de Fundadores -->
        <section class="card-secao-categoria">
            <div class="grid-produtos">
                <?php foreach ($lideresLinha2 as $lider): ?>
                    <div class="card-produto">
                        <a href="<?php echo $lider['link']; ?>">
                            <?php if (!empty($lider['imagem'])): ?>
                                <img src="<?php echo $lider['imagem']; ?>" alt="<?php echo $lider['nome']; ?>">
                            <?php endif; ?>
                        </a>
                        <h3><?php echo $lider['nome']; ?></h3>
                        <p><?php echo $lider['cargo']; ?></p>
                        <a href="<?php echo $lider['link']; ?>" class="link-clean">Ver a Bio</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
    <footer>
        <p>&copy; <?= date('Y') ?> VolunteerConnect. Todos os direitos reservados.</p>
    </footer>
</body>

</html>