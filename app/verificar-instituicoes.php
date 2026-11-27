<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se está logado e se é uma instituição
if (!isset($_SESSION['id']) || $_SESSION['tipo'] != 'instituicao') {
    die("Acesso permitido somente para instituições!");
}

?>