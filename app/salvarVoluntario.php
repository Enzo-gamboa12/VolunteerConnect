<?php
require_once __DIR__ . "/crud.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Captura os dados do formulário com os nomes dos atributos 'name' do HTML
    $id_campanha = isset($_POST['id_campanha']) ? (int)$_POST['id_campanha'] : 0;
    $nome        = trim($_POST['name'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $cpf         = trim($_POST['cpf'] ?? '');
    $endereco    = trim($_POST['endereco'] ?? '');

    // Validação básica dos campos obrigatórios
    if ($id_campanha > 0 && !empty($nome) && !empty($email)) {
        try {
            // 2. Verifica se o voluntário já existe pelo e-mail
            $voluntarioExistente = read($pdo, "voluntarios", "email = " . $pdo->quote($email));

            if ($voluntarioExistente) {
                $id_voluntario = $voluntarioExistente['id'];
            } else {
                // Cadastra o novo voluntário no banco de dados
                $objVoluntario = [
                    'nome'     => $nome,
                    'email'    => $email,
                    'cpf'      => $cpf,
                    'endereco' => $endereco
                ];
                $id_voluntario = create($pdo, "voluntarios", $objVoluntario);
            }

            if ($id_voluntario) {
                // 3. Verifica se o voluntário já se inscreveu nesta mesma campanha
                $inscricaoExistente = read($pdo, "inscricoes", "id_campanha = $id_campanha AND id_voluntario = $id_voluntario");

                if ($inscricaoExistente) {
                    header("Location: detalhes-oportunidade.php?id={$id_campanha}&status=ja_inscrito");
                    exit;
                } else {
                    // Cadastra a relação na tabela 'inscricoes'
                    $objInscricao = [
                        'id_campanha'   => $id_campanha,
                        'id_voluntario' => $id_voluntario,
                        'status'        => 'aguardando',
                        'horas'         => 0
                    ];
                    create($pdo, "inscricoes", $objInscricao);
                    
                    header("Location: detalhes-oportunidade.php?id={$id_campanha}&status=sucesso");
                    exit;
                }
            }
        } catch (PDOException $e) {
            header("Location: detalhes-oportunidade.php?id={$id_campanha}&status=erro");
            exit;
        }
    } else {
        header("Location: detalhes-oportunidade.php?id={$id_campanha}&status=invalido");
        exit;
    }
} else {
    // Redireciona caso o arquivo seja acessado diretamente via GET
    header("Location: oportunidades.php");
    exit;
}
?>