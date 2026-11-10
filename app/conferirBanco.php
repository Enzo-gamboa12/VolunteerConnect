<?php
// Inclui a conexão e funções CRUD
require_once __DIR__ . "/crud.php";

// Busca todos os voluntários cadastrados
$voluntarios = readAll($pdo, "voluntarios");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Voluntários — VolunteerConnect</title>
    <style>
        body {
            font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #F4F8FA;
            color: #183447;
            padding: 30px;
            margin: 0;
        }
        .container {
            max-width: 1100px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 12px 35px rgba(20, 58, 78, .08);
            border: 1px solid #dfebee;
        }
        h1 {
            color: #1B4965;
            margin-top: 0;
            font-size: 1.8rem;
        }
        p {
            color: #6D818D;
            margin-bottom: 20px;
        }
        .table-responsive {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 0.9rem;
        }
        th, td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #EDF2F3;
        }
        th {
            background-color: #1B4965;
            color: #ffffff;
            font-weight: 700;
        }
        tr:hover {
            background-color: #F4F8FA;
        }
        .total-count {
            display: inline-block;
            background: #E8F5F6;
            color: #266779;
            padding: 6px 14px;
            border-radius: 99px;
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 15px;
        }
        .btn {
            display: inline-block;
            margin-top: 25px;
            padding: 10px 20px;
            background-color: #1B4965;
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.9rem;
            transition: background 0.2s;
        }
        .btn:hover {
            background-color: #12364c;
        }
        .empty-msg {
            padding: 20px;
            background-color: #FFF5DF;
            color: #99701B;
            border-radius: 8px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Conferência de Voluntários</h1>
        <p>Lista de registros salvos na tabela <code>voluntarios</code> do banco de dados.</p>

        <div class="total-count">
            Total de voluntários cadastrados: <?php echo count($voluntarios); ?>
        </div>

        <?php if (!empty($voluntarios)): ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>CPF</th>
                            <th>Endereço</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($voluntarios as $v): ?>
                            <tr>
                                <td><strong>#<?php echo htmlspecialchars($v['id']); ?></strong></td>
                                <td><?php echo htmlspecialchars($v['nome']); ?></td>
                                <td><?php echo htmlspecialchars($v['email']); ?></td>
                                <td><?php echo htmlspecialchars($v['cpf'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($v['endereco'] ?? '-'); ?></td>
                                
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-msg">
                Nenhum voluntário encontrado na tabela. Cadastre um novo formulário para testar.
            </div>
        <?php endif; ?>

        <a href="oportunidades.php" class="btn">← Voltar para Oportunidades</a>
    </div>
</body>
</html>