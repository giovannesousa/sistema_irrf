<?php
// config/config.php

// Configurações do Banco de Dados
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'sistema_irrf');
if (!defined('DB_USER')) define('DB_USER', 'root');
// Verifique se sua senha é '1234' ou vazia '' (padrão XAMPP)
if (!defined('DB_PASS')) define('DB_PASS', '1234'); 

// Configurações do Sistema
if (!defined('BASE_URL')) define('BASE_URL', 'http://localhost/sistema_irrf/public/');
if (!defined('SITE_NAME')) define('SITE_NAME', 'Sistema IRRF - Cálculo de Tributos');
if (!defined('DEBUG_MODE')) define('DEBUG_MODE', true); // Altere para false em produção

// Configurações de Sessão
if (!defined('SESSION_NAME')) define('SESSION_NAME', 'sistema_irrf_session');
if (!defined('SESSION_LIFETIME')) define('SESSION_LIFETIME', 7200); // 2 horas

// Habilitar exibição de erros apenas em desenvolvimento
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}