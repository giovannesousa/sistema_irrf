<?php
// public/teste_r1000.php

session_start();
$_SESSION['logado'] = true;
$_SESSION['orgao_id'] = 1;

require_once __DIR__ . '/../app/Controllers/R1000Controller.php';

echo "<h2>Teste R1000 - Envio Direto</h2>";

$controller = new R1000Controller();

// Simular dados de envio
$_SERVER['REQUEST_METHOD'] = 'POST';
$_GET['action'] = 'enviar';

// Dados simulados
$dados = [
    'classificacao_tributaria' => '03',
    'indicador_ecd' => '0',
    'indicador_desoneracao' => '0',
    'contato_nome' => 'FRANCISCO GIOVANE DE SOUSA',
    'contato_cpf' => '00436413345',
    'contato_telefone' => '89999712721',
    'contato_email' => 'giovannesousa@hotmail.com'
];

// Salvar dados primeiro
$controller->salvarDados();

// Agora enviar
$controller->enviar();
?>