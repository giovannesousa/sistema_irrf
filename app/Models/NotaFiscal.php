<?php
// app/Models/NotaFiscal.php

require_once __DIR__ . '/../Core/Database.php';

class NotaFiscal {
    private $db;
    private $table = 'notas_fiscais';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function salvarNota($dados) {
        try {
            $sql = "INSERT INTO {$this->table} 
                    (id_orgao, id_fornecedor, id_natureza_servico, numero_nota, 
                     data_emissao, valor_bruto, aliquota_aplicada, valor_irrf_retido, 
                     valor_iss_retido, valor_liquido, descricao_servico, caminho_anexo, created_at, updated_at) 
                    VALUES (:id_orgao, :id_fornecedor, :id_natureza_servico, :numero_nota, 
                            :data_emissao, :valor_bruto, :aliquota_aplicada, :valor_irrf_retido, 
                            :valor_iss_retido, :valor_liquido, :descricao_servico, :caminho_anexo, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            
            $stmt->execute([
                ':id_orgao' => $dados['id_orgao'] ?? 1,
                ':id_fornecedor' => $dados['id_fornecedor'],
                ':id_natureza_servico' => $dados['id_natureza'],
                ':numero_nota' => $dados['numero_nota'],
                ':data_emissao' => $dados['data_emissao'] ?? date('Y-m-d'),
                ':valor_bruto' => $dados['valor_bruto'],
                ':aliquota_aplicada' => $dados['aliquota'],
                ':valor_irrf_retido' => $dados['valor_irrf_retido'],
                ':valor_iss_retido' => $dados['valor_iss_retido'] ?? 0.00,
                ':valor_liquido' => $dados['valor_liquido'],
                ':descricao_servico' => $dados['descricao_servico'],
                ':caminho_anexo' => $dados['caminho_anexo'] ?? null
            ]);
            
            return $this->db->lastInsertId();
            
        } catch (PDOException $e) {
            error_log("Erro ao salvar nota: " . $e->getMessage());
            throw $e;
        }
    }

    public function listarPorOrgao($idOrgao, $limit = 100, $status = 1) {
        $sql = "SELECT nf.*, f.razao_social, f.cnpj, ns.descricao as natureza_desc 
                FROM {$this->table} nf
                JOIN fornecedores f ON nf.id_fornecedor = f.id
                JOIN natureza_servicos ns ON nf.id_natureza_servico = ns.id
                WHERE nf.id_orgao = :id_orgao";
        
        if ($status !== -1) {
            $sql .= " AND nf.nota_ativa = :status";
        }
        
        $sql .= " ORDER BY nf.created_at DESC LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_orgao', $idOrgao, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        if ($status !== -1) {
            $stmt->bindParam(':status', $status, PDO::PARAM_INT);
        }
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function listarPendentesPagamento($idOrgao) {
        $sql = "SELECT nf.*, f.razao_social, f.cnpj, ns.descricao as natureza_desc 
                FROM {$this->table} nf
                JOIN fornecedores f ON nf.id_fornecedor = f.id
                JOIN natureza_servicos ns ON nf.id_natureza_servico = ns.id
                WHERE nf.id_orgao = :id_orgao 
                AND nf.status_pagamento = 'pendente'
                AND nf.nota_ativa = 1
                ORDER BY nf.data_emissao ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_orgao', $idOrgao, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function buscarPorId($idNota, $idOrgao = null) {
        $sql = "SELECT nf.*, f.razao_social, f.cnpj, f.regime_tributario,
                       ns.descricao as natureza_desc, ns.codigo_rfb,
                       o.nome_oficial as orgao_nome, o.cnpj as orgao_cnpj
                FROM {$this->table} nf
                JOIN fornecedores f ON nf.id_fornecedor = f.id
                JOIN natureza_servicos ns ON nf.id_natureza_servico = ns.id
                JOIN orgaos o ON nf.id_orgao = o.id
                WHERE nf.id = :id_nota";
        
        if ($idOrgao) {
            $sql .= " AND nf.id_orgao = :id_orgao";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_nota', $idNota, PDO::PARAM_INT);
        
        if ($idOrgao) {
            $stmt->bindParam(':id_orgao', $idOrgao, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        
        return $stmt->fetch();
    }

    // REMOVIDA: Função registrarPagamento - será movida para PagamentoController
    // public function registrarPagamento($idNota, $idUsuario, $dataPagamento = null) { ... }

    public function estatisticasPagamentos($idOrgao) {
        $sql = "SELECT 
                COUNT(*) as total_notas,
                SUM(CASE WHEN status_pagamento = 'pendente' THEN 1 ELSE 0 END) as pendentes,
                SUM(CASE WHEN status_pagamento = 'pago' THEN 1 ELSE 0 END) as pagas,
                SUM(valor_bruto) as total_bruto,
                SUM(valor_irrf_retido) as total_irrf_retido,
                SUM(valor_liquido) as total_liquido
                FROM {$this->table}
                WHERE id_orgao = :id_orgao AND nota_ativa = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_orgao', $idOrgao, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    // NOVO MÉTODO: Atualizar status de pagamento da nota
     public function atualizarStatusPagamento($idNota, $status, $dataPagamento = null, $idOrgao = null) {
        try {
            // Verificar se a coluna updated_at existe
            $columnExists = $this->verificarColunaExiste('updated_at');
            
            if ($columnExists) {
                $sql = "UPDATE {$this->table} 
                       SET status_pagamento = :status, 
                           data_pagamento = :data_pagamento,
                           updated_at = NOW()
                       WHERE id = :id_nota";
            } else {
                $sql = "UPDATE {$this->table} 
                       SET status_pagamento = :status, 
                           data_pagamento = :data_pagamento
                       WHERE id = :id_nota";
            }
            
            if ($idOrgao) {
                $sql .= " AND id_orgao = :id_orgao";
            }
            
            $stmt = $this->db->prepare($sql);
            $params = [
                ':status' => $status,
                ':data_pagamento' => $dataPagamento ?: ($status === 'pago' ? date('Y-m-d') : null),
                ':id_nota' => $idNota
            ];
            
            if ($idOrgao) {
                $params[':id_orgao'] = $idOrgao;
            }
            
            $stmt->execute($params);
            
            return $stmt->rowCount() > 0;
            
        } catch (Exception $e) {
            error_log("Erro ao atualizar status de pagamento: " . $e->getMessage());
            return false;
        }
    }

     // NOVO MÉTODO: Verificar se coluna existe na tabela
    private function verificarColunaExiste($columnName) {
        try {
            $sql = "SHOW COLUMNS FROM {$this->table} LIKE :column_name";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':column_name' => $columnName]);
            
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    // NOVO MÉTODO: Verificar se nota pode ser paga
    public function podeSerPaga($idNota, $idOrgao = null) {
        $sql = "SELECT id, status_pagamento, valor_liquido
                FROM {$this->table}
                WHERE id = :id_nota 
                AND nota_ativa = 1
                AND status_pagamento = 'pendente'";
        
        if ($idOrgao) {
            $sql .= " AND id_orgao = :id_orgao";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_nota', $idNota, PDO::PARAM_INT);
        
        if ($idOrgao) {
            $stmt->bindParam(':id_orgao', $idOrgao, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        
        $nota = $stmt->fetch();
        
        if (!$nota) {
            return false;
        }
        
        return [
            'id' => $nota['id'],
            'valor_liquido' => $nota['valor_liquido'],
            'status' => $nota['status_pagamento']
        ];
    }

    // NOVO MÉTODO: Buscar múltiplas notas por IDs
    public function buscarMultiplasPorIds($idsNotas, $idOrgao = null) {
        if (empty($idsNotas)) {
            return [];
        }
        
        // Criar placeholders para IN clause
        $placeholders = implode(',', array_fill(0, count($idsNotas), '?'));
        
        $sql = "SELECT nf.*, f.razao_social, f.cnpj, f.regime_tributario,
                       ns.descricao as natureza_desc, ns.codigo_rfb
                FROM {$this->table} nf
                JOIN fornecedores f ON nf.id_fornecedor = f.id
                JOIN natureza_servicos ns ON nf.id_natureza_servico = ns.id
                WHERE nf.id IN ({$placeholders}) 
                AND nf.nota_ativa = 1
                AND nf.status_pagamento = 'pendente'";
        
        if ($idOrgao) {
            $sql .= " AND nf.id_orgao = ?";
            $idsNotas[] = $idOrgao;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($idsNotas);
        
        return $stmt->fetchAll();
    }

    // NOVO MÉTODO: Inativar nota (Soft Delete)
    public function inativar($idNota, $idOrgao = null) {
        $sql = "UPDATE {$this->table} SET nota_ativa = 0, updated_at = NOW() WHERE id = :id";
        
        if ($idOrgao) {
            $sql .= " AND id_orgao = :id_orgao";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $idNota, PDO::PARAM_INT);
        if ($idOrgao) $stmt->bindParam(':id_orgao', $idOrgao, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
}
?>