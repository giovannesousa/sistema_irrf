<?php
// public/api/orgao.php

ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

if (!defined('BASE_PATH')) {
    define('BASE_PATH', realpath(__DIR__ . '/../../'));
}

// INICIO DA CORREÇÃO: Iniciar sessão com o nome correto do sistema
if (session_status() === PHP_SESSION_NONE) {
    session_name('sistema_irrf_session');
    session_start();
}

require_once BASE_PATH . '/app/Controllers/OrgaoController.php';

try {
    $controller = new OrgaoController();
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'listar':
            $controller->listar();
            break;
        case 'salvar':
            $controller->salvar();
            break;
        case 'buscar':
            $controller->buscar();
            break;
        case 'excluir':
            $controller->excluir();
            break;
        case 'selecionar': // NOVO: Troca de contexto para admin
            $controller->selecionar();
            break;
        default:
            echo json_encode(['success' => false, 'error' => 'Ação inválida']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro interno: ' . $e->getMessage()]);
}