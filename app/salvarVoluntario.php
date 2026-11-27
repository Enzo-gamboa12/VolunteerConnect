<?php
require_once __DIR__ . '/init.php';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') { header('Location: oportunidades.php'); exit; }
$voluntario = requireAccount($pdo, 'voluntario');
$id = filter_var($_POST['id_campanha'] ?? '', FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
if (!$id) appError(400, 'Campanha inválida.');
function enrollmentRedirect(int $id, string $status): never {
    header('Location: detalhes-oportunidade.php?id='.$id.'&status='.$status, true, 303); exit;
}
if (appText($_POST, 'confirmacao') !== '1') enrollmentRedirect($id, 'invalido');
try {
    $pdo->beginTransaction();
    // Serializa alterações nas vagas da mesma campanha, inclusive reinscrições.
    $stmt = $pdo->prepare('SELECT * FROM campanhas WHERE id = ? FOR UPDATE');
    $stmt->execute([$id]); $campanha = $stmt->fetch();
    if (!$campanha) { $pdo->rollBack(); appError(404, 'Campanha não encontrada.'); }
    $existing = read($pdo, 'inscricoes', 'id_campanha = ? AND id_voluntario = ?', [$id, (int)$voluntario['id']]);
    if ($existing && normalizarTexto($existing['status']) !== 'cancelado') {
        $pdo->rollBack(); enrollmentRedirect($id, 'ja_inscrito');
    }
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM inscricoes WHERE id_campanha = ? AND status <> 'Cancelado'");
    $stmt->execute([$id]); $occupied = (int)$stmt->fetchColumn();
    if (normalizarTexto($campanha['status']) !== 'ativa' || (int)$campanha['vagas'] < 1 || $occupied >= (int)$campanha['vagasTotais']) {
        $pdo->rollBack(); enrollmentRedirect($id, 'indisponivel');
    }
    if ($existing) {
        update($pdo, 'inscricoes', ['status'=>'Aguardando','horas'=>0], 'id = ?', [(int)$existing['id']]);
    } else {
        create($pdo, 'inscricoes', ['id_campanha'=>$id,'id_voluntario'=>(int)$voluntario['id'],'status'=>'Aguardando','horas'=>0]);
    }
    $stmt = $pdo->prepare('UPDATE campanhas SET vagas = vagas - 1 WHERE id = ? AND vagas > 0');
    $stmt->execute([$id]);
    if ($stmt->rowCount() !== 1) throw new RuntimeException('Falha ao reservar a vaga.');
    $pdo->commit(); enrollmentRedirect($id, 'sucesso');
} catch (Throwable $error) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log((string)$error); enrollmentRedirect($id, 'erro');
}
