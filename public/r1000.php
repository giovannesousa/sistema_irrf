<?php
// public/r1000.php - CRIE ESTE ARQUIVO

require_once __DIR__ . '/../app/Core/Session.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name('sistema_irrf_session');
    session_start();
}

// Verifica autenticação
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header('Location: login.php');
    exit;
}

// Inclui a view
require_once __DIR__ . '/../app/Views/reinf/r1000.php';