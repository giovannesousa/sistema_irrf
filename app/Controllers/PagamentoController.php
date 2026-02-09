<?php
// app/Controllers/PagamentoController.php - VERSÃO CORRIGIDA

require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Core/Session.php';
require_once __DIR__ . '/../Models/NotaFiscal.php';
require_once __DIR__ . '/../Models/Pagamento.php';

class PagamentoController
{
    private $notaModel;
    private $pagamentoModel;
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->notaModel = new NotaFiscal();
        $this->pagamentoModel = new Pagamento();
        
        // Garantir que a tabela pagamentos existe
        $this->pagamentoModel->criarTabelaSeNaoExistir();
    }

    /**
     * Registrar pagamento de uma nota - VERSÃO SIMPLIFICADA E CORRIGIDA
     */
    public function registrarPagamento($idNota, $dataPagamento = null, $observacoes = null)
    {
        try {
            // Verificar sessão
            Session::start();
            if (!Session::isLoggedIn()) {
                return ['success' => false, 'error' => 'Não autenticado'];
            }

            $usuario = Session::getUser();
            $idUsuario = $usuario['id'];

            // Validar ID da nota
            if (!$idNota || !is_numeric($idNota) || $idNota <= 0) {
                return ['success' => false, 'error' => 'ID da nota inválido'];
            }

            // Verificar se a nota existe e está pendente
            $nota = $this->notaModel->buscarPorId($idNota);
            
            if (!$nota) {
                return ['success' => false, 'error' => 'Nota não encontrada'];
            }

            if ($nota['status_pagamento'] === 'pago') {
                return ['success' => false, 'error' => 'Nota já está paga'];
            }

            // Definir data do pagamento
            if (!$dataPagamento) {
                $dataPagamento = date('Y-m-d');
            }

            // Iniciar transação
            $this->db->beginTransaction();

            try {
                // 1. Atualizar status da nota (sem updated_at para evitar erro)
                $sqlAtualizaNota = "UPDATE notas_fiscais 
                                   SET status_pagamento = 'pago', 
                                       data_pagamento = :data_pagamento
                                   WHERE id = :id";
                
                $stmtNota = $this->db->prepare($sqlAtualizaNota);
                $stmtNota->bindParam(':data_pagamento', $dataPagamento);
                $stmtNota->bindParam(':id', $idNota, PDO::PARAM_INT);
                
                if (!$stmtNota->execute()) {
                    throw new Exception('Erro ao atualizar nota');
                }

                // 2. Registrar pagamento
                $dadosPagamento = [
                    'id_nota' => $idNota,
                    'data_baixa' => $dataPagamento,
                    'valor_pago' => $nota['valor_liquido'],
                    'responsavel_baixa' => $idUsuario,
                    'observacoes' => $observacoes
                ];
                
                $pagamentoId = $this->pagamentoModel->criar($dadosPagamento);
                
                if (!$pagamentoId) {
                    throw new Exception('Erro ao registrar pagamento na tabela pagamentos');
                }

                // 3. Commit da transação
                $this->db->commit();

                return [
                    'success' => true,
                    'message' => 'Nota paga com sucesso',
                    'id_pagamento' => $pagamentoId,
                    'data_pagamento' => $dataPagamento,
                    'valor_pago' => $nota['valor_liquido']
                ];

            } catch (Exception $e) {
                // Rollback em caso de erro
                $this->db->rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            error_log("Erro no PagamentoController::registrarPagamento: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erro interno: ' . $e->getMessage()];
        }
    }

    /**
     * Registrar pagamento múltiplo - VERSÃO SIMPLIFICADA
     */
    public function registrarPagamentoMultiplo($idsNotas, $dataPagamento = null, $observacoes = null)
    {
        try {
            // Verificar sessão
            Session::start();
            if (!Session::isLoggedIn()) {
                return ['success' => false, 'error' => 'Não autenticado'];
            }

            $usuario = Session::getUser();
            $idUsuario = $usuario['id'];

            // Validar lista de notas
            if (!is_array($idsNotas) || empty($idsNotas)) {
                return ['success' => false, 'error' => 'Nenhuma nota selecionada'];
            }

            // Filtrar IDs válidos
            $idsNotas = array_map('intval', $idsNotas);
            $idsNotas = array_filter($idsNotas, function($id) {
                return $id > 0;
            });

            if (empty($idsNotas)) {
                return ['success' => false, 'error' => 'IDs de notas inválidos'];
            }

            // Definir data do pagamento
            if (!$dataPagamento) {
                $dataPagamento = date('Y-m-d');
            }

            $sucesso = 0;
            $erros = [];

            // Processar cada nota individualmente (sem transação global para evitar rollback total)
            foreach ($idsNotas as $idNota) {
                try {
                    $result = $this->registrarPagamento($idNota, $dataPagamento, $observacoes);
                    
                    if ($result['success']) {
                        $sucesso++;
                    } else {
                        $erros[] = "Nota ID {$idNota}: " . $result['error'];
                    }
                    
                } catch (Exception $e) {
                    $erros[] = "Nota ID {$idNota}: " . $e->getMessage();
                }
            }

            return [
                'success' => $sucesso > 0,
                'total_pagas' => $sucesso,
                'total_erros' => count($erros),
                'message' => "{$sucesso} nota(s) pagas com sucesso",
                'erros' => $erros
            ];

        } catch (Exception $e) {
            error_log("Erro no PagamentoController::registrarPagamentoMultiplo: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erro interno: ' . $e->getMessage()];
        }
    }

    /**
     * Listar pagamentos por nota
     */
    public function listarPorNota($idNota)
    {
        try {
            Session::start();
            if (!Session::isLoggedIn()) {
                return ['success' => false, 'error' => 'Não autenticado'];
            }

            $pagamentos = $this->pagamentoModel->buscarPorNota($idNota);
            
            return [
                'success' => true,
                'pagamentos' => $pagamentos
            ];

        } catch (Exception $e) {
            error_log("Erro no PagamentoController::listarPorNota: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erro interno'];
        }
    }

    /**
     * Processar requisições HTTP
     */
    public function handleRequest()
    {
        $action = $_GET['action'] ?? '';
        $method = $_SERVER['REQUEST_METHOD'];

        switch ($action) {
            case 'registrar':
                if ($method === 'POST') {
                    $this->handleRegistrarPagamento();
                } else {
                    $this->sendJsonError('Método não permitido', 405);
                }
                break;

            case 'registrar_multiplo':
                if ($method === 'POST') {
                    $this->handleRegistrarPagamentoMultiplo();
                } else {
                    $this->sendJsonError('Método não permitido', 405);
                }
                break;

            case 'listar':
                if ($method === 'GET') {
                    $this->handleListarPagamentos();
                } else {
                    $this->sendJsonError('Método não permitido', 405);
                }
                break;

            default:
                $this->sendJsonError('Ação não encontrada', 404);
                break;
        }
    }

    /**
     * Manipular registro de pagamento único
     */
    private function handleRegistrarPagamento()
    {
        try {
            $idNota = $_POST['id_nota'] ?? 0;
            $dataPagamento = $_POST['data_pagamento'] ?? null;
            $observacoes = $_POST['observacoes'] ?? null;

            $result = $this->registrarPagamento($idNota, $dataPagamento, $observacoes);
            
            header('Content-Type: application/json');
            echo json_encode($result, JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            $this->sendJsonError('Erro interno: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Manipular registro de pagamento múltiplo
     */
    private function handleRegistrarPagamentoMultiplo()
    {
        try {
            $idsNotasJson = $_POST['ids_notas'] ?? '[]';
            $idsNotas = json_decode($idsNotasJson, true);
            
            $dataPagamento = $_POST['data_pagamento'] ?? null;
            $observacoes = $_POST['observacoes'] ?? null;

            $result = $this->registrarPagamentoMultiplo($idsNotas, $dataPagamento, $observacoes);
            
            header('Content-Type: application/json');
            echo json_encode($result, JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            $this->sendJsonError('Erro interno: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Manipular listagem de pagamentos
     */
    private function handleListarPagamentos()
    {
        try {
            $idNota = $_GET['id_nota'] ?? 0;
            
            if (!$idNota) {
                $this->sendJsonError('ID da nota não informado', 400);
                return;
            }

            $result = $this->listarPorNota($idNota);
            
            header('Content-Type: application/json');
            echo json_encode($result, JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            $this->sendJsonError('Erro interno', 500);
        }
    }

    /**
     * Enviar erro JSON
     */
    private function sendJsonError($message, $statusCode = 400)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $message
        ], JSON_UNESCAPED_UNICODE);
    }
}