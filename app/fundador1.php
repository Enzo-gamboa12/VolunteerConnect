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
$nomeFundador   = "Nichollas Mastropietro";
$cargoResumo    = "Diretor, Organizador.";
$fotoFundador   = "imagens/nichollas.webp";
$tituloSecao    = "Cargo:";
$descricaoBio   = "Como Diretor e Organizador da VolunteerConnect, Nichollas atua na estruturação das ações internas, no alinhamento das parcerias com instituições e na organização das campanhas solidárias da plataforma. Sua participação no projeto surgiu da vontade de criar processos claros que facilitem o engajamento entre voluntários e projetos sociais, garantindo que cada iniciativa tenha suporte e continuidade.";
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
    <link rel="stylesheet" href="css/navigation.css">
    <script src="js/navigation.js" defer></script>
</head>

<body>
    <?php require __DIR__ . '/partials/navigation.php'; ?>

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

    <footer>
        <p>&copy; <?= date('Y') ?> VolunteerConnect. Todos os direitos reservados.</p>
    </footer>
</body>

</html>