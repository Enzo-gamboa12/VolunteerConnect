<?php

declare(strict_types=1);

require_once __DIR__ . '/init.php';

$voluntarios = []; $erroConsulta = '';
$filtroArea = appText($_GET, 'area');
$areas = ['Meio Ambiente','Educação','Assistência Social','Saúde','Proteção Animal','Cultura e Esportes'];
$tipo = $_SESSION['tipo_conta'] ?? '';
if (!in_array($tipo, ['instituicao','voluntario'], true)) { header('Location: login.php?erro=acesso_negado'); exit; }
$account = requireAccount($pdo, $tipo);
if ($tipo === 'instituicao') {
    $sql = "SELECT DISTINCT v.* FROM voluntarios v JOIN inscricoes ins ON ins.id_voluntario=v.id JOIN campanhas c ON c.id=ins.id_campanha WHERE c.id_instituicao=? AND ins.status <> 'Cancelado'";
} else { $sql = 'SELECT v.* FROM voluntarios v WHERE v.id=?'; }
$params = [(int)$account['id']];
if (in_array($filtroArea, $areas, true)) { $sql .= ' AND v.area_atuacao=?'; $params[]=$filtroArea; } else { $filtroArea=''; }
$sql .= ' ORDER BY v.id DESC';
$stmt=$pdo->prepare($sql); $stmt->execute($params); $voluntarios=$stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voluntários | VolunteerConnect</title>
    <link rel="stylesheet" href="css/cadastro.css">
    <style>
        .voluntarios-shell {
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
            background-color: #437057;
            color: #FFFFFF;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-filter:hover {
            background-color: #355945;
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
            border-top: 4px solid #437057;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .voluntarios-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 14px;
        }

        .voluntarios-table thead {
            background-color: #F4F7F5;
            border-bottom: 1px solid #E2E6E3;
        }

        .voluntarios-table th {
            padding: 14px 18px;
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #4A5550;
            white-space: nowrap;
        }

        .voluntarios-table td {
            padding: 15px 18px;
            border-bottom: 1px solid #EEF1EF;
            color: #262C29;
            vertical-align: middle;
        }

        .voluntarios-table tbody tr:hover {
            background-color: #FAFBF9;
        }

        .voluntarios-table tbody tr:last-child td {
            border-bottom: none;
        }

        .badge-area {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            background-color: #E2EFF2;
            color: #3B7380;
            white-space: nowrap;
        }

        .badge-disp {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            background-color: #F9EAE4;
            color: #CD6B47;
            white-space: nowrap;
        }

        .text-muted {
            color: #8C9490;
            font-size: 13px;
        }

        .empty-state {
            padding: 48px 20px;
            text-align: center;
            color: #68706C;
        }
    </style>
    <link rel="stylesheet" href="css/navigation.css">
    <script src="js/navigation.js" defer></script>
</head>

<body>
    <?php require __DIR__ . '/partials/navigation.php'; ?>

    <main class="voluntarios-shell">
        <div class="signup-heading">
            <span class="eyebrow"><span class="eyebrow-dot"></span> REDE DE APOIO</span>
            <h1>Voluntários cadastrados na<br><em>plataforma VolunteerConnect.</em></h1>
            <p>Consulte os perfis disponíveis, áreas de interesse e horários de atuação.</p>
        </div>

        <?php if ($erroConsulta !== ''): ?>
            <div class="form-error" role="alert">
                <p><?= indexEsc($erroConsulta) ?></p>
            </div>
        <?php endif; ?>

        <div class="toolbar">
            <div class="toolbar-info">
                Total exibido: <strong><?= count($voluntarios) ?></strong> voluntário(s)
            </div>

            <form class="filter-form" method="get" action="verificar-voluntario.php">
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
                    <a class="btn-clear" href="verificar-voluntario.php">Limpar</a>
                <?php endif; ?>
            </form>
        </div>

        <section class="table-card" aria-label="Lista de voluntários">
            <?php if (empty($voluntarios)): ?>
                <div class="empty-state">
                    <p>Nenhum voluntário encontrado para os critérios selecionados.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="voluntarios-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome completo</th>
                                <th>E-mail</th>
                                <th>CPF</th>
                                <th>Endereço</th>
                                <th>Área de atuação</th>
                                <th>Disponibilidade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($voluntarios as $vol): ?>
                                <tr>
                                    <td><strong>#<?= indexEsc((string) $vol['id']) ?></strong></td>
                                    <td><strong><?= indexEsc((string) $vol['nome']) ?></strong></td>
                                    <td><?= indexEsc((string) $vol['email']) ?></td>
                                    <td>
                                        <?php if (!empty($vol['cpf'])): ?>
                                            <?= 'Restrito' ?>
                                        <?php else: ?>
                                            <span class="text-muted">Não informado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= 'Restrito' ?></td>
                                    <td><span class="badge-area"><?= indexEsc((string) $vol['area_atuacao']) ?></span></td>
                                    <td><span class="badge-disp"><?= indexEsc((string) $vol['disponibilidade']) ?></span></td>
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