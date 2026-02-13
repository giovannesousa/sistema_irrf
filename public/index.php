<?php
// public/index.php - Front Controller

// ============================================
// CONFIGURAÇÕES INICIAIS
// ============================================
// Habilitar erros para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

// Definir timezone
date_default_timezone_set('America/Sao_Paulo');

// Definir constantes
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', __DIR__);
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/sistema_irrf/public/');

// ============================================
// AUTOLOAD SIMPLES
// ============================================

spl_autoload_register(function ($className) {
    $file = APP_PATH . '/' . str_replace('\\', '/', $className) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// ============================================
// GERENCIAMENTO DE SESSÃO
// ============================================

session_name('sistema_irrf_session');
session_start();

// ============================================
// VERIFICAÇÃO DE AUTENTICAÇÃO
// ============================================

// Páginas que não precisam de autenticação
$public_pages = ['login', 'logout'];

// Extrair a rota da URL
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$script_name = dirname($_SERVER['SCRIPT_NAME']);

// Normalizar a rota
$route = str_replace($script_name, '', $request_uri);
$route = trim($route, '/');
$route_parts = explode('/', $route);
$page = $route_parts[0];

if (empty($page) || $page === 'index.php') {
    $page = $_GET['page'] ?? 'dashboard';
}

// Verificar se precisa de autenticação
if (!in_array($page, $public_pages)) {
    if (!isset($_SESSION['usuario']) || !isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
        // Salvar a página que estava tentando acessar
        $_SESSION['redirect_url'] = $request_uri;
        
        // Redirecionar para login
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }
}

// ============================================
// ROTEAMENTO
// ============================================

// Mapeamento de rotas para views
$routes = [
    '' => 'home.php',
    'home' => 'home.php',
    'dashboard' => 'home.php',
    'gerar-nota' => 'gerar-nota.php',
    'pagar-nota' => 'pagar-nota.php',
    'relatorios' => 'relatorios.php',
    'fornecedores' => 'fornecedores.php',
    'reinf' => 'reinf/dashboard.php',
    'configuracoes' => 'configuracoes.php',
    'usuarios' => 'usuarios.php',
    'orgao' => 'orgao.php',
    'orgaos' => 'orgaos.php', // Nova rota para lista de órgãos
    'print-nota' => 'print-nota.php',
    'login' => 'auth/login.php'
];

// Determinar qual página carregar
if (isset($routes[$page])) {
    $view_file = $routes[$page];
    
    // Se for login, não precisa verificar sessão novamente
    if ($page === 'login' && isset($_SESSION['logado']) && $_SESSION['logado'] === true) {
        // Se já está logado e tentou acessar login, redireciona para home
        header('Location: ' . BASE_URL);
        exit;
    }
    
    // Preparar variáveis para a view
    $titulo = ucwords(str_replace(['-', '.php'], [' ', ''], $page));
    $pagina_atual = $page;
    $usuario = $_SESSION['usuario'] ?? null;
    
    // Caminho completo para a view
    $view_path = APP_PATH . '/Views/' . $view_file;
    
    // Verificar se o arquivo existe
    if (file_exists($view_path)) {
        require_once $view_path;
    } else {
        // View não encontrada
        http_response_code(404);
        require_once APP_PATH . '/Views/errors/404.php';
    }
} else {
    // Rota não encontrada
    http_response_code(404);
    require_once APP_PATH . '/Views/errors/404.php';
}