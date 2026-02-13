<?php
// public/api/api-r1000.php

ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

if (!defined('BASE_PATH')) {
    define('BASE_PATH', realpath(__DIR__ . '/../../'));
}

require_once BASE_PATH . '/app/Core/Session.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name('sistema_irrf_session');
    session_start();
}

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Não autenticado']);
    exit;
}

$controllerPath = BASE_PATH . '/app/Controllers/R1000Controller.php';

if (file_exists($controllerPath)) {
    require_once $controllerPath;
    
    $controller = new R1000Controller();
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'verificar_status':
            $controller->verificarStatus(); break;
        case 'salvar':
            $controller->salvarDados(); break;
        case 'validar':
            $controller->validarXml(); break;
        case 'enviar':
            $controller->enviarEvento(); break;
        case 'excluir':
            $controller->excluirEvento(); break;
        case 'consultar':
            $controller->consultarLoteR1000(); break;
        default:
            echo json_encode(['success' => false, 'error' => 'Ação inválida']);
    }
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Controller R1000 não encontrado.']);
}