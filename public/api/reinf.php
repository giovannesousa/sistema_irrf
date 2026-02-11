<?php
// public/api/reinf.php

// 1. Configurações de API
// Desativa a exibição de erros no output (para não quebrar o JSON)
ini_set('display_errors', 0); 
error_reporting(E_ALL);

// Define o cabeçalho como JSON e UTF-8
header('Content-Type: application/json; charset=utf-8');

// 2. Definição de Constantes de Caminho (se ainda não estiverem definidas)
if (!defined('BASE_PATH')) {
    define('BASE_PATH', realpath(__DIR__ . '/../../'));
}

// 3. Carregar o Controller
// Como seu ReinfController.php já possui a lógica de execução (switch case) no início do arquivo,
// apenas o require já irá disparar o processamento.
$controllerPath = BASE_PATH . '/app/Controllers/ReinfController.php';

if (file_exists($controllerPath)) {
    require_once $controllerPath;
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Controller Reinf não encontrado.']);
}