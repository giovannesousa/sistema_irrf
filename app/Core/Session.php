<?php
// app/Core/Session.php

class Session {
    private static $started = false;
    
    public static function start() {
        if (!self::$started && session_status() === PHP_SESSION_NONE) {
            session_name('sistema_irrf_session');
            
            // Configurações de segurança da sessão
            session_set_cookie_params([
                'lifetime' => 86400, // 24 horas
                'path' => '/',
                'domain' => $_SERVER['HTTP_HOST'] ?? 'localhost',
                'secure' => isset($_SERVER['HTTPS']), // Apenas HTTPS se disponível
                'httponly' => true, // Apenas HTTP, não acessível via JavaScript
                'samesite' => 'Strict'
            ]);
            
            session_start();
            self::$started = true;
            
            // Renovar ID da sessão periodicamente para segurança
            if (!isset($_SESSION['created'])) {
                $_SESSION['created'] = time();
            } else if (time() - $_SESSION['created'] > 1800) {
                // Renovar a cada 30 minutos
                session_regenerate_id(true);
                $_SESSION['created'] = time();
            }
        }
    }
    
    public static function get($key, $default = null) {
        self::start();
        return $_SESSION[$key] ?? $default;
    }
    
    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }
    
    public static function delete($key) {
        self::start();
        unset($_SESSION[$key]);
    }
    
    public static function destroy() {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            // Limpar todas as variáveis de sessão
            $_SESSION = array();
            
            // Se desejar destruir o cookie de sessão também
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }
            
            session_destroy();
            self::$started = false;
        }
    }
    
    public static function isLoggedIn() {
        return self::get('logado', false) === true && 
               self::get('usuario') !== null;
    }
    
    public static function getUser() {
        return self::get('usuario');
    }
    
    public static function getIdOrgao() {
        $usuario = self::getUser();
        return $usuario['id_orgao'] ?? null;
    }
    
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            // Salvar URL atual para redirecionar depois do login
            self::set('redirect_url', $_SERVER['REQUEST_URI']);
            header('Location: /sistema_irrf/public/login.php');
            exit;
        }
    }
    
    public static function regenerate() {
        self::start();
        session_regenerate_id(true);
    }
}
?>