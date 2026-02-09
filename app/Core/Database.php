<?php
// app/Core/Database.php

require_once __DIR__ . '/../../config/config.php';

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                die("Erro na conexão com o banco de dados: " . $e->getMessage());
            } else {
                error_log("Erro de conexão: " . $e->getMessage());
                die("Erro interno do sistema. Contate o administrador.");
            }
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    // Método para preparar statements com tratamento de erro
    public function prepare($sql) {
        try {
            return $this->conn->prepare($sql);
        } catch (PDOException $e) {
            error_log("Erro ao preparar SQL: " . $e->getMessage());
            throw $e;
        }
    }
}