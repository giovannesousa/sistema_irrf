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

    public function listarTodos() {
        $sql = "SELECT u.id, u.nome, u.login, u.nivel_acesso, u.created_at, u.id_orgao, o.nome_oficial as orgao_nome
                FROM {$this->table} u
                LEFT JOIN orgaos o ON u.id_orgao = o.id
                ORDER BY u.nome";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function buscarPorLogin($login) {
        $sql = "SELECT * FROM {$this->table} WHERE login = :login";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':login', $login, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function salvar($dados) {
        $sql = "INSERT INTO {$this->table} (id_orgao, nome, login, senha_hash, nivel_acesso, created_at, updated_at) 
                VALUES (:id_orgao, :nome, :login, :senha_hash, :nivel_acesso, NOW(), NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_orgao' => $dados['id_orgao'],
            ':nome' => $dados['nome'],
            ':login' => $dados['login'],
            ':senha_hash' => $dados['senha_hash'],
            ':nivel_acesso' => $dados['nivel_acesso']
        ]);
        
        return $this->db->lastInsertId();
    }

    public function atualizar($id, $dados) {
        $campos = "nome = :nome, login = :login, nivel_acesso = :nivel_acesso";
        $params = [
            ':nome' => $dados['nome'],
            ':login' => $dados['login'],
            ':nivel_acesso' => $dados['nivel_acesso'],
            ':id' => $id
        ];

        if (!empty($dados['senha_hash'])) {
            $campos .= ", senha_hash = :senha_hash";
            $params[':senha_hash'] = $dados['senha_hash'];
        }

        $sql = "UPDATE {$this->table} SET $campos, updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function excluir($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>