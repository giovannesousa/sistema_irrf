<?php
// public/login.php

// Iniciar sessão
session_name('sistema_irrf_session');
session_start();

// Se já estiver logado, redireciona para home
if (isset($_SESSION['logado']) && $_SESSION['logado'] === true) {
    header('Location: /sistema_irrf/public/');
    exit;
}

// Inclui a página de login
require_once __DIR__ . '/../app/Views/auth/login.php';