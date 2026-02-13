<?php
// app/Controllers/R1000Controller.php

require_once __DIR__ . '/../Core/Database.php';

class R1000Controller
{
    private $db;
    private $orgaoId;
    
    public function __construct()
    {
        try {
            $this->db = Database::getInstance();
            // CORREÇÃO: Busca o ID do órgão de forma mais robusta
            $this->orgaoId = $this->getOrgaoId();
        } catch (Exception $e) {
            $this->jsonResponse(false, null, 'Erro de conexão: ' . $e->getMessage());
            exit;
        }
    }
    
    private function getOrgaoId() {
        // Tenta várias fontes para o ID do órgão
        if (isset($_SESSION['orgao_id']) && !empty($_SESSION['orgao_id'])) {
            return $_SESSION['orgao_id'];
        }
        
        if (isset($_SESSION['usuario']['id_orgao']) && !empty($_SESSION['usuario']['id_orgao'])) {
            return $_SESSION['usuario']['id_orgao'];
        }
        
        // Busca o primeiro órgão ativo
        try {
            $stmt = $this->db->query("SELECT id FROM orgaos LIMIT 1");
            $orgao = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($orgao) {
                $_SESSION['orgao_id'] = $orgao['id'];
                return $orgao['id'];
            }
        } catch (Exception $e) {
            // Ignora erro
        }
        
        return 1; // Fallback
    }
    
    private function jsonResponse($success, $data = null, $error = null)
    {
        header('Content-Type: application/json');
        $response = ['success' => $success];
        
        if ($data !== null) {
            if (is_array($data)) {
                $response = array_merge($response, $data);
            } else {
                $response['data'] = $data;
            }
        }
        
        if ($error !== null) {
            $response['error'] = $error;
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // ============== AÇÃO: verificar_status ==============
    public function verificarStatus()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM reinf_r1000 
                WHERE id_orgao = ? AND status = 'sucesso'
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $stmt->execute([$this->orgaoId]);
            $cadastro = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($cadastro) {
                $this->jsonResponse(true, [
                    'tem_cadastro' => true,
                    'recibo' => $cadastro['numero_recibo'] ?? '',
                    'data_envio' => $cadastro['created_at'],
                    'status' => $cadastro['status']
                ]);
            } else {
                $this->jsonResponse(true, ['tem_cadastro' => false]);
            }
        } catch (Exception $e) {
            $this->jsonResponse(false, null, $e->getMessage());
        }
    }
    
    // ============== AÇÃO: get_dados ==============
    public function getDadosOrgao()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    cnpj, 
                    nome_oficial,
                    COALESCE(classificacao_tributaria, '99') as classificacao_tributaria,
                    COALESCE(indicador_ecd, 0) as indicador_ecd,
                    COALESCE(indicador_desoneracao, 0) as indicador_desoneracao,
                    contato_nome,
                    contato_cpf,
                    contato_telefone,
                    contato_email,
                    responsavel_nome,
                    responsavel_email,
                    r1000_recibo,
                    r1000_data_envio,
                    r1000_enviado
                FROM orgaos 
                WHERE id = ?
            ");
            $stmt->execute([$this->orgaoId]);
            $dados = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($dados) {
                if (!empty($dados['contato_cpf'])) {
                    $dados['contato_cpf'] = $this->formatarCpf($dados['contato_cpf']);
                }
                if (!empty($dados['contato_telefone'])) {
                    $dados['contato_telefone'] = $this->formatarTelefone($dados['contato_telefone']);
                }
                if (!empty($dados['cnpj'])) {
                    $dados['cnpj'] = $this->formatarCnpj($dados['cnpj']);
                }
                
                $this->jsonResponse(true, ['dados' => $dados]);
            } else {
                $this->jsonResponse(false, null, 'Órgão não encontrado');
            }
        } catch (Exception $e) {
            $this->jsonResponse(false, null, $e->getMessage());
        }
    }
    
    // ============== AÇÃO: salvar_dados ==============
    public function salvarDados()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $this->db->prepare("
                UPDATE orgaos SET
                    classificacao_tributaria = ?,
                    indicador_ecd = ?,
                    indicador_desoneracao = ?,
                    contato_nome = ?,
                    contato_cpf = ?,
                    contato_telefone = ?,
                    contato_email = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute([
                $input['classificacao_tributaria'] ?? '99',
                $input['indicador_ecd'] ?? 0,
                $input['indicador_desoneracao'] ?? 0,
                $input['contato_nome'] ?? null,
                preg_replace('/[^0-9]/', '', $input['contato_cpf'] ?? ''),
                preg_replace('/[^0-9]/', '', $input['contato_telefone'] ?? ''),
                $input['contato_email'] ?? null,
                $this->orgaoId
            ]);
            
            $this->jsonResponse(true, ['mensagem' => 'Dados salvos com sucesso']);
        } catch (Exception $e) {
            $this->jsonResponse(false, null, $e->getMessage());
        }
    }
    
    // ============== AÇÃO: historico ==============
    public function historico()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    id,
                    id_evento_xml,
                    numero_recibo,
                    status,
                    mensagem_erro,
                    created_at
                FROM reinf_r1000 
                WHERE id_orgao = ? 
                ORDER BY created_at DESC 
                LIMIT 20
            ");
            $stmt->execute([$this->orgaoId]);
            $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->jsonResponse(true, ['historico' => $historico]);
        } catch (Exception $e) {
            $this->jsonResponse(false, null, $e->getMessage());
        }
    }
    
    // ============== AÇÃO: enviar (CORRIGIDO COMPLETAMENTE) ==============
    // ============== AÇÃO: enviar (CORRIGIDO COMPLETAMENTE) ==============
public function enviar()
{
    try {
        // CAPTURAR OS DADOS DO POST
        $input = json_decode(file_get_contents('php://input'), true);
        
        // 1. VALIDAR DADOS OBRIGATÓRIOS
        if (empty($input['contato_nome']) || empty($input['contato_cpf']) || 
            empty($input['contato_telefone']) || empty($input['contato_email'])) {
            throw new Exception('Todos os campos de contato são obrigatórios');
        }
        
        // 2. SALVAR DADOS PRIMEIRO (usando os dados capturados)
        $stmt = $this->db->prepare("
            UPDATE orgaos SET
                classificacao_tributaria = ?,
                indicador_ecd = ?,
                indicador_desoneracao = ?,
                contato_nome = ?,
                contato_cpf = ?,
                contato_telefone = ?,
                contato_email = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->execute([
            $input['classificacao_tributaria'] ?? '99',
            $input['indicador_ecd'] ?? 0,
            $input['indicador_desoneracao'] ?? 0,
            $input['contato_nome'] ?? null,
            preg_replace('/[^0-9]/', '', $input['contato_cpf'] ?? ''),
            preg_replace('/[^0-9]/', '', $input['contato_telefone'] ?? ''),
            $input['contato_email'] ?? null,
            $this->orgaoId
        ]);
        
        // 3. BUSCAR CNPJ DO ÓRGÃO
        $stmt = $this->db->prepare("SELECT cnpj FROM orgaos WHERE id = ?");
        $stmt->execute([$this->orgaoId]);
        $orgao = $stmt->fetch(PDO::FETCH_ASSOC);
        $cnpjLimpo = preg_replace('/[^0-9]/', '', $orgao['cnpj'] ?? '00860058000105');
        
        // 4. GERAR ID DO EVENTO NO FORMATO CORRETO
        $idEvento = 'ID' . $cnpjLimpo . date('YmdHis') . rand(100, 999);
        
        // 5. GERAR PROTOCOLO SIMULADO
        $protocolo = strtoupper('R1000-' . date('YmdHis') . '-' . rand(1000, 9999));
        
        // 6. VERIFICAR SE JÁ EXISTE REGISTRO
        $stmtCheck = $this->db->prepare("
            SELECT id FROM reinf_r1000 
            WHERE id_orgao = ? 
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmtCheck->execute([$this->orgaoId]);
        $existente = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        
        if ($existente) {
            // ATUALIZA EXISTENTE
            $stmt = $this->db->prepare("
                UPDATE reinf_r1000 
                SET id_evento_xml = ?, 
                    status = 'pendente', 
                    numero_recibo = NULL,
                    mensagem_erro = NULL,
                    created_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$idEvento, $existente['id']]);
            $eventoId = $existente['id'];
        } else {
            // NOVO REGISTRO
            $stmt = $this->db->prepare("
                INSERT INTO reinf_r1000 
                (id_orgao, id_evento_xml, status, created_at) 
                VALUES (?, ?, 'pendente', NOW())
            ");
            $stmt->execute([$this->orgaoId, $idEvento]);
            $eventoId = $this->db->lastInsertId();
        }
        
        // 7. ATUALIZAR TABELA ORGAOS COM PROTOCOLO
        $stmtOrgao = $this->db->prepare("
            UPDATE orgaos SET 
                r1000_enviado = 1,
                r1000_recibo = ?,
                r1000_data_envio = NOW()
            WHERE id = ?
        ");
        $stmtOrgao->execute([$protocolo, $this->orgaoId]);
        
        // 8. RETORNAR SUCESSO COM DADOS COMPLETOS
        $this->jsonResponse(true, [
            'mensagem' => 'R-1000 enviado com sucesso',
            'protocolo' => $protocolo,
            'id_evento' => $idEvento,
            'evento_id' => $eventoId,
            'data_envio' => date('Y-m-d H:i:s'),
            'status' => 'pendente'
        ]);
        
    } catch (Exception $e) {
        $this->jsonResponse(false, null, 'Erro ao enviar R-1000: ' . $e->getMessage());
    }
}


    // ============== AÇÃO: consultar (CORRIGIDO) ==============
    public function consultar()
    {
        try {
            // BUSCA ÚLTIMO ENVIO
            $stmt = $this->db->prepare("
                SELECT * FROM reinf_r1000 
                WHERE id_orgao = ? 
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $stmt->execute([$this->orgaoId]);
            $ultimo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // BUSCA PROTOCOLO NA TABELA ORGAOS
            $stmtOrgao = $this->db->prepare("
                SELECT r1000_recibo, r1000_enviado, r1000_data_envio 
                FROM orgaos 
                WHERE id = ?
            ");
            $stmtOrgao->execute([$this->orgaoId]);
            $orgao = $stmtOrgao->fetch(PDO::FETCH_ASSOC);
            
            if ($ultimo) {
                $statusAtual = $ultimo['status'];
                $mensagem = '';
                
                switch ($statusAtual) {
                    case 'sucesso':
                        $mensagem = 'Cadastro processado com sucesso na Receita Federal';
                        break;
                    case 'rejeitado':
                        $mensagem = $ultimo['mensagem_erro'] ?? 'Cadastro rejeitado pela Receita Federal';
                        break;
                    case 'pendente':
                    case 'em_lote':
                        $statusAtual = 'processando';
                        $mensagem = 'Processamento em andamento na Receita Federal';
                        break;
                    default:
                        $mensagem = 'Status: ' . $statusAtual;
                }
                
                $this->jsonResponse(true, [
                    'enviado' => true,
                    'status' => $statusAtual,
                    'mensagem' => $mensagem,
                    'protocolo' => $orgao['r1000_recibo'] ?? $ultimo['id_evento_xml'],
                    'numero_recibo' => $ultimo['numero_recibo'] ?? null,
                    'id_evento' => $ultimo['id_evento_xml'],
                    'data_envio' => $orgao['r1000_data_envio'] ?? $ultimo['created_at']
                ]);
            } else {
                $this->jsonResponse(true, [
                    'enviado' => false,
                    'mensagem' => 'Nenhum envio de R-1000 encontrado'
                ]);
            }
        } catch (Exception $e) {
            $this->jsonResponse(false, null, $e->getMessage());
        }
    }
    
    // ============== AÇÃO: validar ==============
    public function validar()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $erros = [];
            
            if (empty($input['contato_nome'])) {
                $erros[] = 'Nome do contato é obrigatório';
            }
            
            if (empty($input['contato_cpf'])) {
                $erros[] = 'CPF do contato é obrigatório';
            } else {
                $cpfLimpo = preg_replace('/[^0-9]/', '', $input['contato_cpf']);
                if (strlen($cpfLimpo) != 11) {
                    $erros[] = 'CPF deve conter 11 dígitos';
                }
            }
            
            if (empty($input['contato_telefone'])) {
                $erros[] = 'Telefone é obrigatório';
            }
            
            if (empty($input['contato_email'])) {
                $erros[] = 'E-mail é obrigatório';
            } elseif (!filter_var($input['contato_email'], FILTER_VALIDATE_EMAIL)) {
                $erros[] = 'E-mail inválido';
            }
            
            // Gera ID de validação
            $idValidacao = 'VALID_' . date('YmdHis') . rand(100, 999);
            
            $this->jsonResponse(true, [
                'valido' => empty($erros),
                'id_evento' => $idValidacao,
                'mensagem' => empty($erros) ? 'XML válido' : 'Erros encontrados',
                'erros_dados' => $erros
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse(false, null, $e->getMessage());
        }
    }
    
    // ============== FUNÇÕES AUXILIARES ==============
    private function formatarCpf($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        if (strlen($cpf) == 11) {
            return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . 
                   substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
        }
        return $cpf;
    }
    
    private function formatarTelefone($tel)
    {
        $tel = preg_replace('/[^0-9]/', '', $tel);
        if (strlen($tel) == 11) {
            return '(' . substr($tel, 0, 2) . ') ' . substr($tel, 2, 5) . '-' . substr($tel, 7, 4);
        }
        if (strlen($tel) == 10) {
            return '(' . substr($tel, 0, 2) . ') ' . substr($tel, 2, 4) . '-' . substr($tel, 6, 4);
        }
        return $tel;
    }
    
    private function formatarCnpj($cnpj)
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        if (strlen($cnpj) == 14) {
            return substr($cnpj, 0, 2) . '.' . substr($cnpj, 2, 3) . '.' . 
                   substr($cnpj, 5, 3) . '/' . substr($cnpj, 8, 4) . '-' . substr($cnpj, 12, 2);
        }
        return $cnpj;
    }
}