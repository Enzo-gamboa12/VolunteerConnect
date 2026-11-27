<?php

// Dados usados no menu.
$navType = $_SESSION['tipo_conta'] ?? '';

$navLogged = in_array(
    $navType,
    ['voluntario', 'instituicao'],
    true
) && !empty($_SESSION['id_' . $navType]);

$navPanel = $navType === 'instituicao'
    ? 'painel-instituicao.php'
    : 'painel-voluntario.php';

$navName = $navLogged
    ? ($_SESSION['nome_' . $navType] ?? 'Minha conta')
    : '';

$navCurrent = basename($_SERVER['SCRIPT_NAME'] ?? '');

?>

<header class="vc-header">
    <a class="vc-brand" href="index.php">
        <img src="imagens/logo-volunteerconnect.png" alt="">
        <span>Volunteer<span>Connect</span></span>
    </a>

    <div class="vc-navigation" id="vc-navigation">
        <nav class="vc-links" aria-label="Navegação principal">
            <a
                href="oportunidades.php"
                <?= $navCurrent === 'oportunidades.php'
                    ? 'aria-current="page"'
                    : '' ?>
            >
                Encontre Oportunidades
            </a>

            <a
                href="<?= $navLogged ? $navPanel : 'login.php' ?>"
                <?= in_array(
                    $navCurrent,
                    ['painel-voluntario.php', 'painel-instituicao.php'],
                    true
                ) ? 'aria-current="page"' : '' ?>
            >
                Meu Painel
            </a>

            <a
                href="sobre.php"
                <?= $navCurrent === 'sobre.php'
                    ? 'aria-current="page"'
                    : '' ?>
            >
                Sobre Nós
            </a>
        </nav>

        <div class="vc-actions">
            <?php if ($navLogged): ?>
                <a class="vc-profile" href="<?= $navPanel ?>">
                    <span class="vc-avatar">
                        <?= indexEsc(indexIniciais($navName)) ?>
                    </span>

                    <span class="vc-name">
                        <?= indexEsc($navName) ?>
                    </span>
                </a>

                <form method="post" action="logout.php">
                    <?= csrfField() ?>

                    <button class="vc-button" type="submit">
                        Sair
                    </button>
                </form>
            <?php else: ?>
                <a class="vc-button" href="login.php">
                    Entrar
                </a>

                <a class="vc-button vc-primary" href="cadastro.php">
                    Cadastrar
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>
