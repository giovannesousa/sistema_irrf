<?php
// public/api/r1000.php

session_start();
require_once __DIR__ . '/../../app/Controllers/R1000Controller.php';

// Verificar autenticação
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header('Content-Type: application/json', true, 401);
    echo json_encode(['success' => false, 'error' => 'Não autenticado']);
    exit;
}

$controller = new R1000Controller();
$action = $_GET['action'] ?? '';

// DEBUG - Log da ação recebida
error_log("R1000 API - Action recebida: " . $action);

switch ($action) {
    case 'get_dados':
        $controller->getDadosOrgao();
        break;
    case 'verificar_status':
        $controller->verificarStatus();
        break;
    case 'salvar_dados':
        $controller->salvarDados();
        break;
    case 'enviar':
        $controller->enviar(); // Esta linha NÃO está sendo chamada
        break;
    case 'consultar':
        $controller->consultar();
        break;
    case 'historico':
        $controller->historico();
        break;
    case 'validar':
        $controller->validar();
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Ação inválida: ' . $action]);
}