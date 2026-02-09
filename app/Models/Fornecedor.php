<?php
// app/Models/Fornecedor.php

require_once __DIR__ . '/../Core/Database.php';

class Fornecedor {
    private $db;
    private $table = 'fornecedores';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function buscarPorCnpj($cnpj) {
        $cnpjLimpo = preg_replace('/[^0-9]/', '', $cnpj);
        // Cria versão formatada para buscar também (caso no banco esteja com máscara)
        $cnpjFormatado = preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $cnpjLimpo);
        
        $sql = "SELECT * FROM {$this->table} WHERE (cnpj = :cnpj_limpo OR cnpj = :cnpj_formatado) AND ativo = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':cnpj_limpo', $cnpjLimpo, PDO::PARAM_STR);
        $stmt->bindParam(':cnpj_formatado', $cnpjFormatado, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    // Método para validar se precisa calcular IRRF
    public function precisaCalcularIrrf($regimeTributario) {
        $regimesSemRetencao = ['simples_nacional', 'mei'];
        return !in_array(strtolower($regimeTributario), $regimesSemRetencao);
    }

    // Método para buscar todos os fornecedores (para futuras telas)
    public function listarTodos($limit = 100) {
        $sql = "SELECT id, cnpj, razao_social, regime_tributario FROM {$this->table} 
                WHERE ativo = 1 ORDER BY razao_social LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}