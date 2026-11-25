<?php
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
        "imagem" => "imagens/antonio-fundador.png",
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
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $tituloPagina; ?></title>
    <link rel="stylesheet" href="css/colaboradores.css">
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
            <span class="avatar-usuario"><?php echo $siglaUsuario; ?></span>
            <span><?php echo $nomeUsuario; ?></span>
        </div>
    </header>

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
    </main>
</body>

</html>