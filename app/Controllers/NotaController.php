<?php
// app/Controllers/NotaController.php

// Incluir arquivos necessários
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Core/Session.php';
require_once __DIR__ . '/../Models/Fornecedor.php';
require_once __DIR__ . '/../Models/NotaFiscal.php';

// Habilitar erros para debug (remova em produção)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Iniciar sessão se necessário
if (session_status() === PHP_SESSION_NONE) {
    session_name('sistema_irrf_session');
    session_start();
}

// Obter ação
$action = $_GET['action'] ?? '';

// Verificar se está logado para ações que precisam de autenticação
$requireAuth = in_array($action, ['salvar_nota', 'detalhes_nota', 'registrar_pagamento', 'registrar_pagamento_multiplo', 'inativar_nota']);

if ($requireAuth) {
    if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
        header('Content-Type: application/json', true, 401);
        echo json_encode(['success' => false, 'error' => 'Não autenticado']);
        exit;
    }
}

// Buscar fornecedor por CNPJ
if ($action == 'buscar_fornecedor') {
    $cnpj = $_GET['cnpj'] ?? '';
    
    if (empty($cnpj)) {
        header('Content-Type: application/json', true, 400);
        echo json_encode(['success' => false, 'error' => 'CNPJ não informado']);
        exit;
    }
    
    try {
        $model = new Fornecedor();
        $dados = $model->buscarPorCnpj($cnpj);
        
        if ($dados) {
            // Formatar dados para resposta
            $response = [
                'success' => true,
                'dados' => [
                    'id' => $dados['id'],
                    'cnpj' => $dados['cnpj'],
                    'razao_social' => $dados['razao_social'],
                    'nome_fantasia' => $dados['nome_fantasia'],
                    'regime_tributario' => $dados['regime_tributario'],
                    'email' => $dados['email'],
                    'telefone' => $dados['telefone'],
                    'endereco_completo' => $dados['endereco_completo'],
                    'ativo' => $dados['ativo']
                ]
            ];
        } else {
            $response = [
                'success' => false,
                'error' => 'Fornecedor não encontrado'
            ];
        }
        
        header('Content-Type: application/json');
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        error_log("Erro ao buscar fornecedor: " . $e->getMessage());
        header('Content-Type: application/json', true, 500);
        echo json_encode(['success' => false, 'error' => 'Erro interno ao buscar fornecedor']);
    }
    exit;
}

// Listar naturezas de serviço
if ($action == 'listar_naturezas') {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT id, codigo_rfb, descricao, aliquota_padrao 
                           FROM natureza_servicos 
                           WHERE permite_retencao = 1 
                           ORDER BY codigo_rfb");
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        error_log("Erro ao listar naturezas: " . $e->getMessage());
        header('Content-Type: application/json', true, 500);
        echo json_encode(['error' => 'Erro interno ao carregar naturezas']);
    }
    exit;
}

// Salvar nova nota fiscal
if ($action == 'salvar_nota') {
    // Verificar se está autenticado
    if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
        header('Content-Type: application/json', true, 401);
        echo json_encode(['success' => false, 'error' => 'Não autenticado']);
        exit;
    }
    
    // Verificar método POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Content-Type: application/json', true, 405);
        echo json_encode(['success' => false, 'error' => 'Método não permitido']);
        exit;
    }
    
    try {
        // Receber dados
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Se não conseguir ler JSON, tenta POST normal
        if (!$input) {
            $input = $_POST;
        }
        
        // Validar campos obrigatórios
        $camposObrigatorios = ['id_fornecedor', 'id_natureza', 'valor_bruto', 'aliquota', 'valor_irrf_retido'];
        $faltantes = [];
        
        foreach ($camposObrigatorios as $campo) {
            if (empty($input[$campo])) {
                $faltantes[] = $campo;
            }
        }
        
        if (!empty($faltantes)) {
            header('Content-Type: application/json', true, 400);
            echo json_encode([
                'success' => false, 
                'error' => 'Campos obrigatórios faltando: ' . implode(', ', $faltantes)
            ]);
            exit;
        }
        
        // Obter dados do usuário logado
        $usuario = $_SESSION['usuario'] ?? null;
        if (!$usuario) {
            header('Content-Type: application/json', true, 401);
            echo json_encode(['success' => false, 'error' => 'Sessão inválida']);
            exit;
        }
        
        // Calcular valor líquido
        $valorBruto = floatval(str_replace(['.', ','], ['', '.'], $input['valor_bruto']));
        $valorIRRF = floatval(str_replace(['.', ','], ['', '.'], $input['valor_irrf_retido']));
        $valorLiquido = $valorBruto - $valorIRRF;
        
        // Preparar dados da nota
        $dadosNota = [
            'id_orgao' => $usuario['id_orgao'] ?? 1,
            'id_fornecedor' => intval($input['id_fornecedor']),
            'id_natureza_servico' => intval($input['id_natureza']),
            'numero_nota' => $input['numero_nota'] ?? 'NF-' . date('Ymd-His'),
            'serie_nota' => $input['serie_nota'] ?? '1',
            'data_emissao' => $input['data_emissao'] ?? date('Y-m-d'),
            'valor_bruto' => $valorBruto,
            'aliquota_aplicada' => floatval(str_replace(',', '.', $input['aliquota'])),
            'valor_irrf_retido' => $valorIRRF,
            'valor_iss_retido' => 0.00, // Se for necessário
            'descricao_servico' => $input['descricao_servico'] ?? '',
            'observacoes' => $input['observacoes'] ?? '',
            'status_pagamento' => 'pendente'
        ];
        
        // Salvar no banco
        $db = Database::getInstance()->getConnection();
        
        // Preparar SQL
        $campos = implode(', ', array_keys($dadosNota));
        $valores = ':' . implode(', :', array_keys($dadosNota));
        
        $sql = "INSERT INTO notas_fiscais ({$campos}) VALUES ({$valores})";
        $stmt = $db->prepare($sql);
        
        // Bind dos valores
        foreach ($dadosNota as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        // Executar
        if ($stmt->execute()) {
            $idNota = $db->lastInsertId();
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'id_nota' => $idNota,
                'message' => 'Registro de cálculo de IRRF salvo com sucesso!',
                'numero_nota' => $dadosNota['numero_nota']
            ], JSON_UNESCAPED_UNICODE);
        } else {
            throw new Exception('Erro ao executar SQL: ' . implode(', ', $stmt->errorInfo()));
        }
        
    } catch (Exception $e) {
        error_log("Erro ao salvar nota: " . $e->getMessage());
        header('Content-Type: application/json', true, 500);
        echo json_encode([
            'success' => false, 
            'error' => 'Erro ao salvar nota: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Buscar nota por ID
if ($action == 'buscar_nota') {
    try {
        if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
            header('Content-Type: application/json', true, 401);
            echo json_encode(['success' => false, 'error' => 'Não autenticado']);
            exit;
        }
        
        $idNota = $_GET['id'] ?? 0;
        if (!$idNota) {
            header('Content-Type: application/json', true, 400);
            echo json_encode(['success' => false, 'error' => 'ID da nota não informado']);
            exit;
        }
        
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT nf.*, 
                       f.razao_social, f.cnpj,
                       ns.descricao as natureza_descricao, ns.codigo_rfb,
                       o.nome_oficial as orgao_nome
                FROM notas_fiscais nf
                LEFT JOIN fornecedores f ON nf.id_fornecedor = f.id
                LEFT JOIN natureza_servicos ns ON nf.id_natureza_servico = ns.id
                LEFT JOIN orgaos o ON nf.id_orgao = o.id
                WHERE nf.id = :id";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $idNota, PDO::PARAM_INT);
        $stmt->execute();
        
        $nota = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($nota) {
            // Formatar valores
            $nota['valor_bruto_formatado'] = number_format($nota['valor_bruto'], 2, ',', '.');
            $nota['valor_irrf_retido_formatado'] = number_format($nota['valor_irrf_retido'], 2, ',', '.');
            $nota['valor_liquido_formatado'] = number_format($nota['valor_liquido'], 2, ',', '.');
            $nota['aliquota_aplicada_formatada'] = number_format($nota['aliquota_aplicada'], 2, ',', '.') . '%';
            
            if ($nota['data_emissao']) {
                $nota['data_emissao_formatada'] = date('d/m/Y', strtotime($nota['data_emissao']));
            }
            
            if ($nota['data_pagamento']) {
                $nota['data_pagamento_formatada'] = date('d/m/Y', strtotime($nota['data_pagamento']));
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'nota' => $nota
            ], JSON_UNESCAPED_UNICODE);
        } else {
            header('Content-Type: application/json', true, 404);
            echo json_encode(['success' => false, 'error' => 'Nota não encontrada']);
        }
        
    } catch (Exception $e) {
        error_log("Erro ao buscar nota: " . $e->getMessage());
        header('Content-Type: application/json', true, 500);
        echo json_encode(['success' => false, 'error' => 'Erro interno']);
    }
    exit;
}

// Listar todas as notas
if ($action == 'listar_notas') {
    try {
        if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
            header('Content-Type: application/json', true, 401);
            echo json_encode(['success' => false, 'error' => 'Não autenticado']);
            exit;
        }
        
        $usuario = $_SESSION['usuario'] ?? null;
        $idOrgao = $usuario['id_orgao'] ?? 1;
        
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT nf.id, nf.numero_nota, nf.data_emissao, nf.valor_bruto, 
                       nf.valor_irrf_retido, nf.valor_liquido, nf.status_pagamento,
                       f.razao_social, f.cnpj,
                       ns.descricao as natureza_descricao
                FROM notas_fiscais nf
                LEFT JOIN fornecedores f ON nf.id_fornecedor = f.id
                LEFT JOIN natureza_servicos ns ON nf.id_natureza_servico = ns.id
                WHERE nf.id_orgao = :id_orgao
                ORDER BY nf.created_at DESC
                LIMIT 100";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id_orgao', $idOrgao, PDO::PARAM_INT);
        $stmt->execute();
        
        $notas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatar valores
        foreach ($notas as &$nota) {
            $nota['valor_bruto_formatado'] = 'R$ ' . number_format($nota['valor_bruto'], 2, ',', '.');
            $nota['valor_irrf_retido_formatado'] = 'R$ ' . number_format($nota['valor_irrf_retido'], 2, ',', '.');
            $nota['valor_liquido_formatado'] = 'R$ ' . number_format($nota['valor_liquido'], 2, ',', '.');
            
            if ($nota['data_emissao']) {
                $nota['data_emissao_formatada'] = date('d/m/Y', strtotime($nota['data_emissao']));
            }
            
            // Badge para status
            $nota['status_badge'] = $nota['status_pagamento'] === 'pago' 
                ? '<span class="badge bg-success">Paga</span>' 
                : '<span class="badge bg-warning">Pendente</span>';
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'total' => count($notas),
            'notas' => $notas
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        error_log("Erro ao listar notas: " . $e->getMessage());
        header('Content-Type: application/json', true, 500);
        echo json_encode(['success' => false, 'error' => 'Erro interno']);
    }
    exit;
}

if ($action == 'registrar_pagamento') {
    try {
        // Verificar autenticação
        if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
            echo json_encode(['success' => false, 'error' => 'Não autenticado']);
            exit;
        }
        
        // Obter dados
        $idNota = $_POST['id_nota'] ?? 0;
        
        if (!$idNota || $idNota <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID da nota inválido']);
            exit;
        }
        
        // Obter usuário da sessão
        $usuario = $_SESSION['usuario'] ?? null;
        if (!$usuario || !isset($usuario['id'])) {
            echo json_encode(['success' => false, 'error' => 'Sessão inválida']);
            exit;
        }
        
        $db = Database::getInstance()->getConnection();
        
        // 1. Verificar se a nota existe e está pendente
        $sql = "SELECT id, valor_liquido FROM notas_fiscais 
               WHERE id = :id AND status_pagamento = 'pendente'";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $idNota, PDO::PARAM_INT);
        $stmt->execute();
        $nota = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$nota) {
            echo json_encode(['success' => false, 'error' => 'Nota não encontrada ou já paga']);
            exit;
        }
        
        // 2. Iniciar transação
        $db->beginTransaction();
        
        // 3. Atualizar nota como paga
        $sql = "UPDATE notas_fiscais 
               SET status_pagamento = 'pago', 
                   data_pagamento = CURDATE(),
                   updated_at = NOW()
               WHERE id = :id";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $idNota, PDO::PARAM_INT);
        $stmt->execute();
        
        // 4. Registrar no histórico de pagamentos
        $sql = "INSERT INTO pagamentos 
                (id_nota, data_pagamento, valor_pago, responsavel_baixa, valor_bruto, valor_base_ir, valor_ir) 
                VALUES (:id_nota, CURDATE(), :valor_pago, :responsavel_baixa, :valor_bruto, :valor_base_ir, :valor_ir)";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id_nota', $idNota, PDO::PARAM_INT);
        $stmt->bindParam(':valor_pago', $nota['valor_liquido']);
        $stmt->bindParam(':responsavel_baixa', $usuario['id'], PDO::PARAM_INT);
        
        // Valores Reinf
        $stmt->bindValue(':valor_bruto', $nota['valor_bruto']);
        $stmt->bindValue(':valor_base_ir', $nota['valor_bruto']);
        $stmt->bindValue(':valor_ir', $nota['valor_irrf_retido']);
        
        $stmt->execute();
        
        // 5. Confirmar transação
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Nota paga com sucesso'
        ]);
        
    } catch (Exception $e) {
        if (isset($db)) {
            $db->rollBack();
        }
        error_log("Erro ao registrar pagamento: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Erro interno: ' . $e->getMessage()]);
    }
    exit;
}



// ==================== REGISTRAR PAGAMENTO MÚLTIPLO ====================
if ($action == 'registrar_pagamento_multiplo') {
    try {
        // Verificar autenticação
        if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
            echo json_encode(['success' => false, 'error' => 'Não autenticado']);
            exit;
        }
        
        // Obter dados
        $idsNotasJson = $_POST['ids_notas'] ?? '[]';
        $idsNotas = json_decode($idsNotasJson, true);
        
        if (!is_array($idsNotas) || empty($idsNotas)) {
            echo json_encode(['success' => false, 'error' => 'Nenhuma nota selecionada']);
            exit;
        }
        
        // Obter usuário da sessão
        $usuario = $_SESSION['usuario'] ?? null;
        if (!$usuario || !isset($usuario['id'])) {
            echo json_encode(['success' => false, 'error' => 'Sessão inválida']);
            exit;
        }
        
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        
        $sucesso = 0;
        $erros = [];
        
        foreach ($idsNotas as $idNota) {
            try {
                $idNota = intval($idNota);
                
                // Verificar se a nota existe e está pendente
                $sqlVerifica = "SELECT id, valor_liquido FROM notas_fiscais 
                               WHERE id = :id AND status_pagamento = 'pendente'";
                $stmtVerifica = $db->prepare($sqlVerifica);
                $stmtVerifica->bindParam(':id', $idNota, PDO::PARAM_INT);
                $stmtVerifica->execute();
                $nota = $stmtVerifica->fetch(PDO::FETCH_ASSOC);
                
                if (!$nota) {
                    $erros[] = "Nota ID $idNota não encontrada ou já paga";
                    continue;
                }
                
                // Atualizar nota como paga
                $sqlAtualiza = "UPDATE notas_fiscais 
                               SET status_pagamento = 'pago', 
                                   data_pagamento = CURDATE(),
                                   updated_at = NOW()
                               WHERE id = :id";
                
                $stmtAtualiza = $db->prepare($sqlAtualiza);
                $stmtAtualiza->bindParam(':id', $idNota, PDO::PARAM_INT);
                $stmtAtualiza->execute();
                
                // Registrar pagamento
                $sqlPagamento = "INSERT INTO pagamentos 
                                (id_nota, data_pagamento, valor_pago, responsavel_baixa, valor_bruto, valor_base_ir, valor_ir) 
                                VALUES (:id_nota, CURDATE(), :valor_pago, :responsavel_baixa, :valor_bruto, :valor_base_ir, :valor_ir)";
                
                $stmtPagamento = $db->prepare($sqlPagamento);
                $stmtPagamento->bindParam(':id_nota', $idNota, PDO::PARAM_INT);
                $stmtPagamento->bindParam(':valor_pago', $nota['valor_liquido']);
                $stmtPagamento->bindParam(':responsavel_baixa', $usuario['id'], PDO::PARAM_INT);
                
                // Valores Reinf
                $stmtPagamento->bindValue(':valor_bruto', $nota['valor_bruto']);
                $stmtPagamento->bindValue(':valor_base_ir', $nota['valor_bruto']);
                $stmtPagamento->bindValue(':valor_ir', $nota['valor_irrf_retido']);
                
                $stmtPagamento->execute();
                
                $sucesso++;
                
            } catch (Exception $e) {
                $erros[] = "Nota ID $idNota: " . $e->getMessage();
            }
        }
        
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'total_pagas' => $sucesso,
            'total_erros' => count($erros),
            'message' => "{$sucesso} nota(s) pagas com sucesso" . 
                        (count($erros) > 0 ? " (" . count($erros) . " erro(s))" : ""),
            'erros' => $erros
        ]);
        
    } catch (Exception $e) {
        if (isset($db)) {
            $db->rollBack();
        }
        error_log("Erro ao registrar pagamento múltiplo: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Erro interno: ' . $e->getMessage()]);
    }
    exit;
}

// ==================== INATIVAR NOTA ====================
if ($action == 'inativar_nota') {
    try {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Método inválido');
        }

        $idNota = $_POST['id_nota'] ?? 0;
        $usuario = $_SESSION['usuario'] ?? null;
        
        if (!$idNota) throw new Exception('ID inválido');
        if (!$usuario) throw new Exception('Sessão inválida');

        $db = Database::getInstance()->getConnection();
        
        // Verificar se pode inativar (apenas se não estiver paga)
        $sqlCheck = "SELECT status_pagamento FROM notas_fiscais WHERE id = :id AND id_orgao = :id_orgao";
        $stmtCheck = $db->prepare($sqlCheck);
        $stmtCheck->execute([':id' => $idNota, ':id_orgao' => $usuario['id_orgao']]);
        $nota = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$nota) throw new Exception('Nota não encontrada');
        if ($nota['status_pagamento'] === 'pago') throw new Exception('Não é possível inativar uma nota já paga');

        // Executar inativação
        $model = new NotaFiscal();
        if ($model->inativar($idNota, $usuario['id_orgao'])) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('Erro ao atualizar registro');
        }

    } catch (Exception $e) {
        header('Content-Type: application/json', true, 500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Ação não encontrada
header('Content-Type: application/json', true, 404);
echo json_encode([
    'success' => false,
    'error' => 'Ação não encontrada',
    'available_actions' => [
        'buscar_fornecedor',
        'listar_naturezas',
        'salvar_nota',
        'buscar_nota',
        'listar_notas',
        'registrar_pagamento'
    ]
]);