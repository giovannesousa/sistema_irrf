<?php
// app/Models/Usuario.php

require_once __DIR__ . '/../Core/Database.php';

class Usuario {
    private $db;
    private $table = 'usuarios';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function autenticar($login, $senha) {
        $sql = "SELECT u.*, o.nome_oficial as orgao_nome, o.id as id_orgao 
                FROM {$this->table} u 
                LEFT JOIN orgaos o ON u.id_orgao = o.id 
                WHERE u.login = :login AND u.id_orgao IS NOT NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':login', $login, PDO::PARAM_STR);
        $stmt->execute();
        
        $usuario = $stmt->fetch();
        
        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            // Remove a senha do array antes de retornar
            unset($usuario['senha_hash']);
            return $usuario;
        }
        
        return false;
    }

    public function buscarPorId($id) {
        $sql = "SELECT u.*, o.nome_oficial as orgao_nome, o.id as id_orgao 
                FROM {$this->table} u 
                LEFT JOIN orgaos o ON u.id_orgao = o.id 
                WHERE u.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $usuario = $stmt->fetch();
        
        if ($usuario) {
            unset($usuario['senha_hash']);
        }
        
        return $usuario;
    }

    public function listarPorOrgao($idOrgao) {
        $sql = "SELECT id, nome, login, nivel_acesso, created_at 
                FROM {$this->table} 
                WHERE id_orgao = :id_orgao 
                ORDER BY nome";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_orgao', $idOrgao, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
?>