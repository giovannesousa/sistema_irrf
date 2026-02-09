<?php
// public/api/pagamento.php

// Incluir o controller
require_once __DIR__ . '/../../app/Controllers/PagamentoController.php';

// Habilitar CORS se necessário
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Verificar se é requisição OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_name('sistema_irrf_session');
    session_start();
}

// Criar e executar controller
$controller = new PagamentoController();
$controller->handleRequest();