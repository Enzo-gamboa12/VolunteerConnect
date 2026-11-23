<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/banco/database.php';
require_once __DIR__ . '/banco/crud.php';

$pdo = database();

/** Evita que valores vindos do banco sejam interpretados como HTML. */
function indexEsc(mixed $valor): string
{
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

/** Gera as iniciais para o avatar do usuário exibido no cabeçalho (suporta acentos). */

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