<?php

declare(strict_types=1);

require_once __DIR__ . '/init.php';

$instituicoes = [];
$erroConsulta = '';
$filtroArea = isset($_GET['area']) && is_string($_GET['area']) ? trim($_GET['area']) : '';

$areas = [
    'Meio Ambiente',
    'Educação',
    'Assistência Social',
    'Saúde',
    'Proteção Animal',
    'Cultura e Esportes',
];

try {
    if ($filtroArea !== '' && in_array($filtroArea, $areas, true)) {
        $instituicoes = readAll($pdo, 'instituicoes', 'area = ? ORDER BY id DESC', [$filtroArea]);
    } else {
        $filtroArea = '';
        $instituicoes = readAll($pdo, 'instituicoes', '1=1 ORDER BY id DESC');
    }
} catch (Throwable $erro) {
    error_log($erro->getMessage());
    $erroConsulta = 'Não foi possível carregar a lista de instituições no momento.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instituições | VolunteerConnect</title>
    <link rel="stylesheet" href="css/cadastro.css">
    <style>
        .instituicoes-shell {
            max-width: 1120px;
            margin: 0 auto;
            padding: 40px 20px 64px;
        }

        .toolbar {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin: 28px 0 20px;
            background: #FFFFFF;
            padding: 16px 20px;
            border-radius: 12px;
            border: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.03);
        }

        .toolbar-info {
            font-size: 14px;
            color: #4A524E;
        }

        .toolbar-info strong {
            color: #1F2421;
        }

        .filter-form {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-form select {
            height: 40px;
            padding: 0 12px;
            border-radius: 6px;
            border: 1px solid #D8DDD9;
            font-size: 13.5px;
            background: #FFFFFF;
            color: #1F2421;
        }

        .btn-filter {
            height: 40px;
            padding: 0 18px;
            border: none;
            border-radius: 6px;
            background-color: #CD6B47;
            color: #FFFFFF;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-filter:hover {
            background-color: #B55836;
        }

        .btn-clear {
            height: 40px;
            padding: 0 14px;
            border-radius: 6px;
            border: 1px solid #D8DDD9;
            color: #5C6460;
            font-size: 13px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .table-card {
            background: #FFFFFF;
            border-radius: 12px;
            border-top: 4px solid #CD6B47;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .instituicoes-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 14px;
        }

        .instituicoes-table thead {
            background-color: #FCF7F5;
            border-bottom: 1px solid #EFE3DE;
        }

        .instituicoes-table th {
            padding: 14px 18px;
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #5E4B44;
            white-space: nowrap;
        }

        .instituicoes-table td {
            padding: 15px 18px;
            border-bottom: 1px solid #F3ECE9;
            color: #262C29;
            vertical-align: middle;
        }

        .instituicoes-table tbody tr:hover {
            background-color: #FDF9F7;
        }

        .instituicoes-table tbody tr:last-child td {
            border-bottom: none;
        }

        .badge-area {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            background-color: #F9EAE4;
            color: #CD6B47;
            white-space: nowrap;
        }

        .badge-disp {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            background-color: #E2EFF2;
            color: #3B7380;
            white-space: nowrap;
        }

        .empty-state {
            padding: 48px 20px;
            text-align: center;
            color: #68706C;
        }
    </style>
</head>

<body>
    <header class="site-header">
        <a class="brand" href="index.php" aria-label="VolunteerConnect, início">
            <img class="brand-logo" src="imagens/logo-volunteerconnect.png" alt="">
            <span>Volunteer<span>Connect</span></span>
        </a>

        <button class="menu-toggle" type="button" aria-label="Abrir menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>

        <nav class="main-nav">
            <a href="index.php">Início</a>
            <a href="oportunidades.php">Oportunidades</a>
            <a href="voluntarios.php">Voluntários</a>
            <a href="instituicoes.php" aria-current="page">Instituições</a>
            <a href="login.php">Painel</a>
        </nav>

        <div class="header-actions">
            <a class="login-link" href="cadastro.php">Novo cadastro</a>
        </div>
    </header>

    <main class="instituicoes-shell">
        <div class="signup-heading">
            <span class="eyebrow eyebrow-coral"><span class="eyebrow-dot"></span> ORGANIZAÇÕES PARCEIRAS</span>
            <h1>Instituições cadastradas na<br><em>plataforma VolunteerConnect.</em></h1>
            <p>Visualize as entidades parceiras, suas áreas de atuação e disponibilidade.</p>
        </div>

        <?php if ($erroConsulta !== ''): ?>
            <div class="form-error" role="alert">
                <p><?= indexEsc($erroConsulta) ?></p>
            </div>
        <?php endif; ?>

        <div class="toolbar">
            <div class="toolbar-info">
                Total exibido: <strong><?= count($instituicoes) ?></strong> instituição(ões)
            </div>

            <form class="filter-form" method="get" action="instituicoes.php">
                <select name="area" aria-label="Filtrar por área de atuação">
                    <option value="">Todas as áreas</option>
                    <?php foreach ($areas as $area): ?>
                        <option value="<?= indexEsc($area) ?>" <?= $filtroArea === $area ? 'selected' : '' ?>>
                            <?= indexEsc($area) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button class="btn-filter" type="submit">Filtrar</button>
                <?php if ($filtroArea !== ''): ?>
                    <a class="btn-clear" href="instituicoes.php">Limpar</a>
                <?php endif; ?>
            </form>
        </div>

        <section class="table-card" aria-label="Lista de instituições">
            <?php if (empty($instituicoes)): ?>
                <div class="empty-state">
                    <p>Nenhuma instituição encontrada para os critérios selecionados.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="instituicoes-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome da instituição</th>
                                <th>E-mail institucional</th>
                                <th>Endereço</th>
                                <th>Área de atuação</th>
                                <th>Disponibilidade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($instituicoes as $inst): ?>
                                <tr>
                                    <td><strong>#<?= indexEsc((string) $inst['id']) ?></strong></td>
                                    <td><strong><?= indexEsc((string) $inst['nome']) ?></strong></td>
                                    <td><?= indexEsc((string) $inst['email']) ?></td>
                                    <td><?= indexEsc((string) $inst['endereco']) ?></td>
                                    <td><span class="badge-area"><?= indexEsc((string) $inst['area']) ?></span></td>
                                    <td><span class="badge-disp"><?= indexEsc((string) $inst['disponibilidade']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer class="site-footer">
        <a class="brand brand-footer" href="index.php">
            <img class="brand-logo" src="imagens/logo-volunteerconnect.png" alt="">
            <span>Volunteer<span>Connect</span></span>
        </a>
        <p>Conectando pessoas a causas que importam.</p>
        <span>© 2026 VolunteerConnect</span>
    </footer>

    <script>
        var botaoMenu = document.querySelector('.menu-toggle');
        var menu = document.querySelector('.main-nav');

        if (botaoMenu && menu) {
            botaoMenu.addEventListener('click', function () {
                var aberto = menu.classList.toggle('is-open');
                botaoMenu.setAttribute('aria-expanded', aberto);
            });
        }
    </script>
</body>

</html>