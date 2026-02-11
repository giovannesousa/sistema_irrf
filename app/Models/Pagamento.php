<?php
// app/Models/Pagamento.php

require_once __DIR__ . '/../Core/Database.php';

class Pagamento
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Criar novo registro de pagamento
     */
    public function criar($dados)
    {
        try {
            // Valores obrigatórios
            $id_nota = intval($dados['id_nota']);
            $data_pagamento = $dados['data_pagamento'] ?? date('Y-m-d');
            $valor_pago = floatval($dados['valor_pago']);
            $responsavel_baixa = intval($dados['responsavel_baixa']);
            
            // Valores Reinf
            $valor_bruto = floatval($dados['valor_bruto'] ?? 0);
            $valor_base_ir = floatval($dados['valor_base_ir'] ?? 0);
            $valor_ir = floatval($dados['valor_ir'] ?? 0);

            // Observações é opcional
            $observacoes = $dados['observacoes'] ?? null;

            // Verificar colunas opcionais
            $hasObservacoes = $this->verificarColunaExiste('pagamentos', 'observacoes');
            // Se o valor bruto foi passado nos dados, assumimos que deve ser gravado (Reinf)
            $hasReinf = isset($dados['valor_bruto']) || $this->verificarColunaExiste('pagamentos', 'valor_bruto');

            $campos = "id_nota, data_pagamento, valor_pago, responsavel_baixa";
            $params = ":id_nota, :data_pagamento, :valor_pago, :responsavel_baixa";

            if ($hasReinf) {
                $campos .= ", valor_bruto, valor_base_ir, valor_ir";
                $params .= ", :valor_bruto, :valor_base_ir, :valor_ir";
            }

            if ($hasObservacoes) {
                $campos .= ", observacoes";
                $params .= ", :observacoes";
            }

            $sql = "INSERT INTO pagamentos ($campos) VALUES ($params)";
            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':id_nota', $id_nota, PDO::PARAM_INT);
            $stmt->bindParam(':data_pagamento', $data_pagamento);
            $stmt->bindParam(':valor_pago', $valor_pago);
            $stmt->bindParam(':responsavel_baixa', $responsavel_baixa, PDO::PARAM_INT);

            if ($hasReinf) {
                $stmt->bindParam(':valor_bruto', $valor_bruto);
                $stmt->bindParam(':valor_base_ir', $valor_base_ir);
                $stmt->bindParam(':valor_ir', $valor_ir);
            }
            
            if ($hasObservacoes) {
                $stmt->bindParam(':observacoes', $observacoes);
            }

            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }

            // Log do erro SQL
            $errorInfo = $stmt->errorInfo();
            error_log("Erro SQL ao criar pagamento: " . print_r($errorInfo, true));
            return false;

        } catch (Exception $e) {
            error_log("Erro no Pagamento::criar: " . $e->getMessage());
            return false;
        }
    }

    private function verificarColunaExiste($tabela, $coluna)
    {
        try {
            $sql = "SHOW COLUMNS FROM {$tabela} LIKE :coluna";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':coluna' => $coluna]);

            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Erro ao verificar coluna {$coluna} em {$tabela}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar se tabela pagamentos existe
     */
    public function tabelaExiste()
    {
        try {
            $sql = "SHOW TABLES LIKE 'pagamentos'";
            $stmt = $this->db->query($sql);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Criar tabela pagamentos se não existir
     */
    public function criarTabelaSeNaoExistir()
    {
        try {
            if (!$this->tabelaExiste()) {
                $sql = "CREATE TABLE IF NOT EXISTS `pagamentos` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `id_nota` int(11) NOT NULL,
                    `data_pagamento` date NOT NULL,
                    `valor_pago` decimal(15,2) NOT NULL,
                    `responsavel_baixa` int(11) DEFAULT NULL,
                    `valor_bruto` decimal(15,2) DEFAULT 0.00,
                    `valor_base_ir` decimal(15,2) DEFAULT 0.00,
                    `valor_ir` decimal(15,2) DEFAULT 0.00,
                    `observacoes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                    PRIMARY KEY (`id`),
                    KEY `id_nota` (`id_nota`),
                    KEY `responsavel_baixa` (`responsavel_baixa`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                
                ALTER TABLE `pagamentos`
                ADD CONSTRAINT `pagamentos_ibfk_1` FOREIGN KEY (`id_nota`) REFERENCES `notas_fiscais` (`id`),
                ADD CONSTRAINT `pagamentos_ibfk_2` FOREIGN KEY (`responsavel_baixa`) REFERENCES `usuarios` (`id`);";

                $this->db->exec($sql);
                return true;
            }
            return true;
        } catch (Exception $e) {
            error_log("Erro ao criar tabela pagamentos: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar pagamentos por nota
     */
    public function buscarPorNota($idNota)
    {
        try {
            $sql = "SELECT p.*, u.nome as responsavel_nome 
                    FROM pagamentos p
                    LEFT JOIN usuarios u ON p.responsavel_baixa = u.id
                    WHERE p.id_nota = :id_nota
                    ORDER BY p.data_pagamento DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_nota', $idNota, PDO::PARAM_INT);
            $stmt->execute();

            $pagamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Formatar valores e datas
            foreach ($pagamentos as &$pagamento) {
                $pagamento['valor_pago_formatado'] = 'R$ ' . number_format($pagamento['valor_pago'], 2, ',', '.');
                $pagamento['data_pagamento_formatada'] = date('d/m/Y', strtotime($pagamento['data_pagamento']));
            }

            return $pagamentos;

        } catch (Exception $e) {
            error_log("Erro no Pagamento::buscarPorNota: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar pagamento por ID
     */
    public function buscarPorId($id)
    {
        try {
            $sql = "SELECT p.*, u.nome as responsavel_nome, nf.numero_nota
                    FROM pagamentos p
                    LEFT JOIN usuarios u ON p.responsavel_baixa = u.id
                    LEFT JOIN notas_fiscais nf ON p.id_nota = nf.id
                    WHERE p.id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Erro no Pagamento::buscarPorId: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar se nota já tem pagamento
     */
    public function notaPossuiPagamento($idNota)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM pagamentos WHERE id_nota = :id_nota";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_nota', $idNota, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] > 0;

        } catch (Exception $e) {
            error_log("Erro no Pagamento::notaPossuiPagamento: " . $e->getMessage());
            return false;
        }
    }
}