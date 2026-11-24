<?php

declare(strict_types=1);

/** Evita que valores vindos do banco sejam interpretados como HTML. */
function indexEsc(mixed $valor): string
{
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

/** Gera as iniciais para o avatar do usuário exibido no cabeçalho. */
function indexIniciais(string $nome): string
{
    $partes = preg_split('/\s+/', trim($nome), -1, PREG_SPLIT_NO_EMPTY);

    if (!$partes) {
        return 'VC';
    }

    $primeira = substr($partes[0], 0, 1);
    $ultima = count($partes) > 1 ? substr($partes[count($partes) - 1], 0, 1) : '';

    return strtoupper($primeira . $ultima);
}

$erroBanco = false;
$conta = false;
$campanhas = [];
$campanhasDestaque = [];
$instituicoes = [];
$voluntarios = [];

try {
    // O init.php inicia a sessão, cria a conexão PDO ($pdo) e carrega as funções do CRUD.
    require_once __DIR__ . '/init.php';

    /*
     * Só existe uma conta conectada quando o login definiu os dados da sessão.
     * Sem essas informações, a página não assume uma conta demonstrativa.
     */
    $contaConectada = false;
    $tipoConta = $_SESSION['tipo_conta'] ?? null;
    $idConta = filter_var($_SESSION['id_conta'] ?? null, FILTER_VALIDATE_INT);

    if (
        in_array($tipoConta, ['instituicao', 'voluntario'], true)
        && $idConta !== false
        && $idConta !== null
        && $idConta > 0
    ) {
        $tabelaConta = $tipoConta === 'voluntario' ? 'voluntarios' : 'instituicoes';

        // READ: busca o registro da conta para mostrar o nome no cabeçalho.
        $conta = read($pdo, $tabelaConta, 'id = ?', [$idConta]);
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
            static fn (array $campanha): bool => ($campanha['status'] ?? '') === 'ativa'
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

$nomeConta = $contaConectada ? ($conta['nome'] ?? 'Conta') : '';
$iniciaisConta = indexIniciais((string)$nomeConta);
$areas = [
    'saude' => 'Saúde',
    'tecnologia' => 'Tecnologia',
    'educacao' => 'Educação',
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/index.css">
    <title>VolunteerConnect | Página inicial</title>
</head>
<body>
    <header class="cabecalho">
        <a class="link-logo" href="index.php">
            <img class="logo" src="imagens/logo-volunteerconnect.png" alt="Logo VolunteerConnect">
            <span>Volunteer<span>Connect</span></span>
        </a>

        <nav class="menu">
            <a href="oportunidades.php">Encontre Oportunidades</a>
            <a href="sobre.php">Sobre Nós</a>
        </nav>

        <?php if ($contaConectada): ?>
            <div class="menu-usuario" title="Conta exibida a partir do banco de dados">
                <span class="avatar-usuario"><?= indexEsc($iniciaisConta) ?></span>
                <span><?= indexEsc($nomeConta) ?></span>
            </div>
        <?php else: ?>
            <div class="acoes-autenticacao" aria-label="Acesso à conta">
                <a class="botao-autenticacao" href="login.php">Entrar</a>
                <a class="botao-autenticacao botao-autenticacao-cadastro" href="cadastro.php">Cadastrar</a>
            </div>
        <?php endif; ?>
    </header>

    <main>
        <section class="hero">
            <div class="hero-container">
                <div class="hero-text">
                    <h1>Encontre uma <br>causa.</h1>
                    <h1 class="second">Faça a diferença.</h1>
                    <p>Seu tempo e suas habilidades podem transformar realidades.</p>
                    <p>Descubra projetos que precisam de você.</p>
                </div>
                <div class="hero-actions">
                    <a class="button" href="oportunidades.php">Encontrar oportunidades</a>
                    <a class="text-link" href="#como-funciona">Como funciona</a>
                </div>
            </div>
            <img src="imagens/tela-inicial.jpg" alt="Pessoas participando de uma ação voluntária">
        </section>

        <section class="feature">
            <div class="title">
                <h2>• OPORTUNIDADES EM DESTAQUE</h2>
                <h1>Boas causas precisam de você.</h1>
            </div>

            <div class="cards">
                <?php if ($erroBanco): ?>
                    <p class="mensagem-vazia">Não foi possível carregar as oportunidades. Verifique a conexão com o banco de dados.</p>
                <?php elseif (empty($campanhasDestaque)): ?>
                    <p class="mensagem-vazia">Ainda não há oportunidades ativas cadastradas.</p>
                <?php else: ?>
                    <?php foreach ($campanhasDestaque as $indiceCard => $campanha): ?>
                        <?php
                        // Cada oportunidade recebe uma das três imagens disponíveis, na ordem dos cards.
                        $imagensCards = [
                            '/imagens/card-1.png',
                            '/imagens/card-2.png',
                            '/imagens/card-3.jpg',
                        ];
                        $imagemCard = $imagensCards[$indiceCard] ?? $imagensCards[0];

                        // READ: localiza a instituição responsável por cada campanha.
                        $instituicaoCampanha = read(
                            $pdo,
                            'instituicoes',
                            'id = ?',
                            [(int)$campanha['id_instituicao']]
                        );

                        $nomeInstituicao = $instituicaoCampanha['nome'] ?? 'Instituição não informada';
                        $enderecoInstituicao = $instituicaoCampanha['endereco'] ?? 'Local não informado';
                        $areaInstituicao = $areas[$instituicaoCampanha['area'] ?? ''] ?? 'Área geral';
                        $descricaoCampanha = trim((string)($campanha['descricao'] ?? ''));
                        ?>
                        <a href="detalhes-oportunidade.php?id=<?= (int)$campanha['id'] ?>" class="card">
                            <img src="<?= indexEsc($imagemCard) ?>" alt="Imagem ilustrativa da oportunidade">
                            <div>
                                <p class="local"><?= indexEsc($nomeInstituicao) ?> • <?= indexEsc($areaInstituicao) ?></p>
                                <h3 class="title"><?= indexEsc($campanha['nome'] ?? 'Oportunidade sem título') ?></h3>
                                <p class="desc">
                                    <?= indexEsc($descricaoCampanha !== '' ? $descricaoCampanha : 'Entre em contato com a instituição para saber mais sobre esta ação.') ?>
                                    <br><strong>Local:</strong> <?= indexEsc($enderecoInstituicao) ?>
                                </p>
                                <hr>
                                <p class="data"><?= (int)($campanha['vagas'] ?? 0) ?> vagas disponíveis</p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="informacao">
            <hr>
            <div class="dados">
                <div class="dado">
                    <p><?= number_format(count($campanhas), 0, ',', '.') ?></p>
                    <p>oportunidades cadastradas</p>
                </div>
                <div class="dado">
                    <p><?= number_format(count($instituicoes), 0, ',', '.') ?></p>
                    <p>instituições parceiras</p>
                </div>
                <div class="dado">
                    <p><?= number_format(count($voluntarios), 0, ',', '.') ?></p>
                    <p>pessoas voluntárias</p>
                </div>
                <div class="impacto">
                    <p>Tem um projeto?</p>
                    <a href="cadastro.php">Cadastre sua instituição</a>
                </div>
            </div>
        </section>
    </main>

    <footer></footer>
</body>
</html>
