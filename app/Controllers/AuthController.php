<?php
// app/Controllers/AuthController.php

require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/BaseController.php';

class AuthController extends BaseController
{
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $login = $_POST['login'] ?? '';
            $senha = $_POST['senha'] ?? '';

            if (empty($login) || empty($senha)) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Login e senha são obrigatórios'
                ], 400);
            }

            $usuarioModel = new Usuario();
            $usuario = $usuarioModel->autenticar($login, $senha);

            if ($usuario) {
                // Iniciar sessão
                session_name('sistema_irrf_session');
                session_start();

                $_SESSION['usuario'] = [
                    'id' => $usuario['id'],
                    'nome' => $usuario['nome'],
                    'login' => $usuario['login'],
                    'nivel_acesso' => $usuario['nivel_acesso'],
                    'orgao_nome' => $usuario['orgao_nome'] ?? '',
                    'id_orgao' => $usuario['id_orgao'] // Esta linha deve funcionar agora
                ];
                $_SESSION['logado'] = true;
                $_SESSION['session_start'] = time();

                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Login realizado com sucesso',
                    'redirect' => '/sistema_irrf/public/'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Usuário ou senha incorretos'
                ], 401);
            }
        }
    }

    public function logout()
    {
        session_name('sistema_irrf_session');
        
        // Verificar se a sessão já foi iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Limpar todas as variáveis de sessão
        $_SESSION = array();
        
        // Destruir a sessão
        session_destroy();
        
        // Redirecionar para login
        header('Location: /sistema_irrf/public/login.php');
        exit;
    }

    public function verificarSessao()
    {
        session_start();

        if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Não autenticado'
            ], 401);
        }

        $this->jsonResponse([
            'success' => true,
            'usuario' => $_SESSION['usuario'] ?? null
        ]);
    }

    // REMOVA ESTE MÉTODO - já existe no modelo Usuario
    // public function autenticar($login, $senha)
    // {
    //     // Remova este método completamente
    // }
}

// Processar requisições
$action = $_GET['action'] ?? '';
$controller = new AuthController();

switch ($action) {
    case 'login':
        $controller->login();
        break;
    case 'logout':
        $controller->logout();
        break;
    case 'verificar':
        $controller->verificarSessao();
        break;
    default:
        // Se alguém acessar diretamente sem ação, redirecionar para login
        header('Location: /sistema_irrf/public/login.php');
        exit;
}
?>