<?php
// public/api/api-reinf.php

// 1. Configurações de API
// Desativa a exibição de erros no output (para não quebrar o JSON)
ini_set('display_errors', 0); 
error_reporting(E_ALL);

// Define o cabeçalho como JSON e UTF-8
header('Content-Type: application/json; charset=utf-8');

// 2. Definição de Constantes de Caminho
if (!defined('BASE_PATH')) {
    define('BASE_PATH', realpath(__DIR__ . '/../../'));
}

// 3. Garantia de Sessão
// Inicia a sessão com o nome correto antes de carregar o controller
if (session_status() === PHP_SESSION_NONE) {
    session_name('sistema_irrf_session');
    session_start();
}

try {
    // 4. Carregar o Controller
    // O ReinfController.php já possui a lógica de roteamento (switch case) e autenticação no início do arquivo.
    // Ao fazer o require, ele executa automaticamente a ação solicitada.
    $controllerPath = BASE_PATH . '/app/Controllers/ReinfController.php';

    if (file_exists($controllerPath)) {
        require_once $controllerPath;
    } else {
        throw new Exception("Controlador Reinf não encontrado no caminho: $controllerPath");
    }

} catch (Throwable $e) {
    // 5. Tratamento Global de Erros
    // Captura qualquer erro fatal ou exceção que não tenha sido tratada no Controller
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Erro interno na API Reinf: ' . $e->getMessage()
    ]);
}
