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
            // Verificar se a tabela pagamentos existe e tem as colunas corretas
            $sql = "INSERT INTO pagamentos 
                    (id_nota, data_baixa, valor_pago, responsavel_baixa) 
                    VALUES (:id_nota, :data_baixa, :valor_pago, :responsavel_baixa)";

            $stmt = $this->db->prepare($sql);

            // Valores obrigatórios
            $id_nota = intval($dados['id_nota']);
            $data_baixa = $dados['data_baixa'] ?? date('Y-m-d');
            $valor_pago = floatval($dados['valor_pago']);
            $responsavel_baixa = intval($dados['responsavel_baixa']);

            // Observações é opcional
            $observacoes = $dados['observacoes'] ?? null;

            // Se a tabela tiver coluna observacoes, usar SQL diferente
            $hasObservacoes = $this->verificarColunaExiste('pagamentos', 'observacoes');

            if ($hasObservacoes) {
                $sql = "INSERT INTO pagamentos 
                        (id_nota, data_baixa, valor_pago, responsavel_baixa, observacoes) 
                        VALUES (:id_nota, :data_baixa, :valor_pago, :responsavel_baixa, :observacoes)";

                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':id_nota', $id_nota, PDO::PARAM_INT);
                $stmt->bindParam(':data_baixa', $data_baixa);
                $stmt->bindParam(':valor_pago', $valor_pago);
                $stmt->bindParam(':responsavel_baixa', $responsavel_baixa, PDO::PARAM_INT);
                $stmt->bindParam(':observacoes', $observacoes);
            } else {
                $stmt->bindParam(':id_nota', $id_nota, PDO::PARAM_INT);
                $stmt->bindParam(':data_baixa', $data_baixa);
                $stmt->bindParam(':valor_pago', $valor_pago);
                $stmt->bindParam(':responsavel_baixa', $responsavel_baixa, PDO::PARAM_INT);
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
                    `data_baixa` date NOT NULL,
                    `valor_pago` decimal(15,2) NOT NULL,
                    `responsavel_baixa` int(11) DEFAULT NULL,
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
                    ORDER BY p.data_baixa DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_nota', $idNota, PDO::PARAM_INT);
            $stmt->execute();

            $pagamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Formatar valores e datas
            foreach ($pagamentos as &$pagamento) {
                $pagamento['valor_pago_formatado'] = 'R$ ' . number_format($pagamento['valor_pago'], 2, ',', '.');
                $pagamento['data_baixa_formatada'] = date('d/m/Y', strtotime($pagamento['data_baixa']));
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