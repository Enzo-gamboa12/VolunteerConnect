<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start(['use_strict_mode' => 1, 'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
}
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Cache-Control: no-store');

function indexEsc(mixed $value): string {
    return htmlspecialchars(is_scalar($value) ? (string) $value : '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function appError(int $status, string $message): never {
    http_response_code($status);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!doctype html><html lang="pt-BR"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>VolunteerConnect</title><body><main><h1>VolunteerConnect</h1><p>' . indexEsc($message) . '</p><p><a href="index.php">Voltar ao início</a></p></main></body></html>';
    exit;
}
set_exception_handler(static function (Throwable $error): void {
    error_log((string) $error);
    appError($error instanceof PDOException ? 503 : 500,
        'Não foi possível concluir a solicitação. Confira a configuração do servidor e do banco de dados ou tente novamente em instantes.');
});
// Campos escalares não aceitam arrays. A lista GET area[] é usada pelo filtro.
foreach (['GET' => $_GET, 'POST' => $_POST] as $source => $values) {
    foreach ($values as $key => $value) {
        if (is_array($value)) {
            if ($source !== 'GET' || $key !== 'area' || count($value) > 20) {
                appError(400, 'Formato de campo inválido. Reabra o formulário.');
            }
            foreach ($value as $item) {
                if (!is_string($item)) appError(400, 'Filtro inválido.');
            }
        }
    }
}
function appText(array $source, string $name, bool $trim = true): string {
    $value = $source[$name] ?? '';
    return is_string($value) ? ($trim ? trim($value) : $value) : '';
}
function appLength(string $text): int {
    return preg_match_all('/./us', $text, $matches) ?: 0;
}
function indexIniciais(string $name): string {
    $parts = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY);
    if (!$parts) return 'VC';
    preg_match('/^./us', $parts[0], $first);
    preg_match('/^./us', $parts[count($parts) - 1], $last);
    $letters = ($first[0] ?? '') . (count($parts) > 1 ? ($last[0] ?? '') : '');
    return strtr(strtoupper($letters), ['á'=>'Á','à'=>'À','ã'=>'Ã','â'=>'Â','é'=>'É','ê'=>'Ê','í'=>'Í','ó'=>'Ó','ô'=>'Ô','õ'=>'Õ','ú'=>'Ú','ç'=>'Ç']);
}
function normalizarTexto(mixed $text): string {
    return strtolower(strtr(trim(is_scalar($text) ? (string)$text : ''),
        ['á'=>'a','à'=>'a','ã'=>'a','â'=>'a','é'=>'e','ê'=>'e','í'=>'i','ó'=>'o','ô'=>'o','õ'=>'o','ú'=>'u','ü'=>'u','ç'=>'c',
         'Á'=>'a','À'=>'a','Ã'=>'a','Â'=>'a','É'=>'e','Ê'=>'e','Í'=>'i','Ó'=>'o','Ô'=>'o','Õ'=>'o','Ú'=>'u','Ü'=>'u','Ç'=>'c']));
}
function appDateValid(string $value): bool {
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
}
function appDate(mixed $value): string {
    return is_string($value) && appDateValid($value) ? (new DateTimeImmutable($value))->format('d/m/Y') : 'A definir';
}
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . indexEsc(csrfToken()) . '">';
}
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $token = appText($_POST, 'csrf_token');
    if ($token === '' || !hash_equals(csrfToken(), $token)) appError(403, 'Formulário expirado ou inválido. Recarregue a página e tente novamente.');
}
function requireAccount(PDO $pdo, string $type): array {
    $table = $type === 'instituicao' ? 'instituicoes' : 'voluntarios';
    $id = (int) ($_SESSION['id_' . $type] ?? 0);
    if (($_SESSION['tipo_conta'] ?? '') !== $type || $id < 1) {
        header('Location: login.php?erro=acesso_negado'); exit;
    }
    $account = read($pdo, $table, 'id = ?', [$id]);
    if (!$account) {
        unset($_SESSION['tipo_conta'], $_SESSION['id_voluntario'], $_SESSION['id_instituicao'], $_SESSION['nome_voluntario'], $_SESSION['nome_instituicao']);
        header('Location: login.php?erro=acesso_negado'); exit;
    }
    return $account;
}
$tipoConta = $_SESSION['tipo_conta'] ?? null;
$linkMeuPainel = $tipoConta === 'instituicao' ? 'painel-instituicao.php' : ($tipoConta === 'voluntario' ? 'painel-voluntario.php' : 'login.php');
