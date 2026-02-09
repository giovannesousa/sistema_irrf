<?php
// app/Models/NaturezaServico.php

require_once __DIR__ . '/../Core/Database.php';

class NaturezaServico {
    private $db;
    private $table = 'natureza_servicos';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function listarTodas() {
        $sql = "SELECT id, codigo_rfb, descricao, aliquota_padrao 
                FROM {$this->table} 
                WHERE permite_retencao = 1 
                ORDER BY codigo_rfb";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function buscarPorId($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    public function buscarPorCodigoRfb($codigo) {
        $sql = "SELECT * FROM {$this->table} WHERE codigo_rfb = :codigo";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':codigo', $codigo, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetch();
    }
}