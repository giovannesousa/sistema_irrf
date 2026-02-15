<?php
// app/Controllers/ReinfController.php

require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Core/Session.php';
require_once __DIR__ . '/../Services/Reinf/ReinfConfig.php';
require_once __DIR__ . '/../Services/Reinf/R4020Builder.php';
require_once __DIR__ . '/../Services/Reinf/R9000Builder.php';
require_once __DIR__ . '/../Services/Reinf/R4099Builder.php';
require_once __DIR__ . '/../Services/Reinf/ReinfSigner.php';
require_once __DIR__ . '/../Services/Reinf/ReinfClient.php';

ini_set('display_errors', 0);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_name('sistema_irrf_session');
    session_start();
}

$action = $_GET['action'] ?? '';

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header('Content-Type: application/json', true, 401);
    echo json_encode(['success' => false, 'error' => 'Não autenticado']);
    exit;
}

$controller = new ReinfController();

switch ($action) {
    case 'listar_pendencias':
        $controller->listarPendencias();
        break;
    case 'enviar_lote':
        $controller->enviarLote();
        break;
    case 'consultar_lote': // <--- ADICIONE ISTO
        $controller->consultarLote();
        break;
    case 'detalhar_lote': // <--- ADICIONE ESTA LINHA
        $controller->detalharLote();
        break;
        case 'validar_lote': // NOVO
        $controller->validarLote();
        break;
    case 'excluir_evento': // NOVO - Exclusão R-9000
        $controller->excluirEvento();
        break;
    case 'enviar_fechamento': // NOVO - R-4099
        $controller->enviarFechamento();
        break;
    case 'buscar_extrato_fechamento': // NOVO - Visualização R-9015
        $controller->buscarExtratoFechamento();
        break;
    case 'recuperar_recibos': // NOVO - Utilitário para listar recibos
        $controller->recuperarRecibos4020();
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Ação inválida']);
}

class ReinfController
{
    private $db;
    private $idOrgao;
    private $reinfConfig;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->idOrgao = $_SESSION['usuario']['id_orgao'] ?? null;

        if (!$this->idOrgao) {
            $this->jsonError("Sessão inválida ou órgão não identificado.");
        }

        // Busca configurações do certificado do órgão no banco
        $stmt = $this->db->prepare("SELECT certificado_arquivo, certificado_senha FROM orgaos WHERE id = ?");
        $stmt->execute([$this->idOrgao]);
        $configOrgao = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$configOrgao || empty($configOrgao['certificado_arquivo'])) {
            // Permite instanciar o controller para listar pendências, mas falhará ao tentar assinar/enviar
            $this->reinfConfig = null;
        } else {
            $certPath = __DIR__ . '/../../certificados/' . $configOrgao['certificado_arquivo'];
            $certPass = $configOrgao['certificado_senha'];
            $this->reinfConfig = new ReinfConfig($certPath, $certPass, 2);
        }
    }    

    public function detalharLote()
    {
        try {
            $idLote = $_GET['id_lote'] ?? null;
            if (!$idLote)
                throw new Exception("ID do lote não informado.");

            $sql = "
                SELECT 
                    e.id,
                    e.id_lote,
                    e.status,
                    e.numero_recibo,
                    e.mensagem_erro,
                    e.id_evento_xml,
                    COALESCE(f.razao_social, CASE 
                        WHEN e.tipo_evento = 'R-4099' AND e.xml_assinado LIKE '%<fechRet>1</fechRet>%' THEN 'Reabertura de Período'
                        WHEN e.tipo_evento = 'R-4099' AND e.xml_assinado LIKE '%<fechRet>0</fechRet>%' THEN 'Fechamento de Período'
                        WHEN e.tipo_evento LIKE '%Reabertura%' OR e.tipo_evento LIKE '%-Reab%' THEN 'Reabertura de Período'
                        WHEN e.tipo_evento LIKE 'R-4099%' THEN 'Fechamento de Período'
                        ELSE 'Sistema' 
                    END) as razao_social,
                    f.cnpj
                FROM reinf_eventos e
                LEFT JOIN fornecedores f ON e.id_fornecedor = f.id
                WHERE e.id_lote = :id_lote
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id_lote' => $idLote]);
            $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'eventos' => $eventos]);

        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }

    public function consultarLote()
    {
        try {
            $idLote = $_POST['id_lote'] ?? null;
            if (!$idLote)
                throw new Exception("ID do lote não informado.");

            // 1. Busca protocolo
            $sql = "SELECT * FROM reinf_lotes WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $idLote]);
            $lote = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$lote || empty($lote['protocolo'])) {
                throw new Exception("Lote sem protocolo.");
            }

            if (!$this->reinfConfig) throw new Exception("Certificado digital não configurado para este órgão.");

            // 2. Consulta API
            $client = new ReinfClient($this->reinfConfig);
            $retornoApi = $client->consultarLote($lote['protocolo']);

            // Debug
            file_put_contents(__DIR__ . '/../../public/debug_consulta.xml', $retornoApi['response']);

            if ($retornoApi['code'] != 200) {
                throw new Exception("Erro API: " . strip_tags(substr($retornoApi['response'], 0, 300)));
            }

            // 3. Leitura com XPath (Ignorando Namespaces para facilitar)
            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadXML($retornoApi['response']);
            libxml_clear_errors();

            $xpath = new DOMXPath($dom);
            // Registra namespace para evitar erros, mas usaremos local-name()
            $xpath->registerNamespace('r', 'http://www.reinf.esocial.gov.br/schemas/retornoLoteEventosAssincrono/v1_00_00');
            $xpath->registerNamespace('evt', 'http://www.reinf.esocial.gov.br/schemas/evtExclusao/v2_01_02');

            // Busca Status (cdResposta ou cdStatus)
            $statusNode = $xpath->query("//*[local-name()='cdResposta']")->item(0);
            if (!$statusNode)
                $statusNode = $xpath->query("//*[local-name()='cdStatus']")->item(0);

            $cdStatus = $statusNode ? $statusNode->nodeValue : '0';

            $statusLoteDb = 'processando';
            $mensagem = 'Aguardando processamento...';

            // Códigos de Finalização: 3 (Com Erros) ou 4 (Sucesso)
            if ($cdStatus == '2' || $cdStatus == '3' || $cdStatus == '4') {
                $statusLoteDb = 'processado'; // Finaliza o ciclo do lote
                $mensagem = ($cdStatus == '2' || $cdStatus == '4') ? 'Sucesso total!' : 'Processado com erros.';

                // Busca eventos
                $eventosNodes = $xpath->query("//*[local-name()='retornoEventos']/*[local-name()='evento']");

                $sqlUpdEvento = "UPDATE reinf_eventos SET status = ?, numero_recibo = ?, mensagem_erro = ? WHERE id_lote = ? AND id_evento_xml = ?";
                $stmtUpdEvento = $this->db->prepare($sqlUpdEvento);

                // Carrega XML de envio para verificar retificações e erros específicos
                $domEnvio = new DOMDocument();
                $domEnvio->loadXML($lote['xml_envio']);
                $xpathEnvio = new DOMXPath($domEnvio);

                foreach ($eventosNodes as $eventoNode) {
                    $idEventoXml = $eventoNode->getAttribute('Id');
                    if (empty($idEventoXml))
                        $idEventoXml = $eventoNode->getAttribute('id');

                    // Verifica se tem Sucesso (2001) ou Erro
                    // O código de retorno fica dentro de ideStatus -> cdRetorno
                    $nodeCod = $xpath->query(".//*[local-name()='cdRetorno']", $eventoNode)->item(0);
                    $codRetorno = $nodeCod ? $nodeCod->nodeValue : '';

                    $nrRecibo = null;
                    $msgErro = null;
                    $statusEvento = 'rejeitado';

                    if ($codRetorno == '2001' || $codRetorno == '0') { // 2001 ou 0 = Sucesso
                        $statusEvento = 'sucesso';
                        $nodeRecibo = $xpath->query(".//*[local-name()='nrRecibo']", $eventoNode)->item(0);
                        if (!$nodeRecibo) {
                            $nodeRecibo = $xpath->query(".//*[local-name()='nrRecArqBase']", $eventoNode)->item(0);
                        }
                        if ($nodeRecibo)
                            $nrRecibo = $nodeRecibo->nodeValue;
                        
                        // [SISTEMA] Verifica se foi uma retificação (indRetif=2)
                        // Se sim, marca o evento anterior (recibo informado) como 'retificado' para sair dos totais
                        $eventoEnvioNode = $xpathEnvio->query("//*[local-name()='evento'][@Id='$idEventoXml']")->item(0);
                        if ($eventoEnvioNode) {
                            $indRetifNode = $xpathEnvio->query(".//*[local-name()='indRetif']", $eventoEnvioNode)->item(0);
                            if ($indRetifNode && $indRetifNode->nodeValue == '2') {
                                $nrReciboAntNode = $xpathEnvio->query(".//*[local-name()='nrRecibo']", $eventoEnvioNode)->item(0);
                                if ($nrReciboAntNode) {
                                    $reciboAnterior = $nrReciboAntNode->nodeValue;
                                    $this->db->prepare("UPDATE reinf_eventos SET status = 'retificado' WHERE numero_recibo = ?")->execute([$reciboAnterior]);
                                }
                            }
                        }
                    } else {
                        // Captura ERRO detalhado
                        // Procura <codResp> e <dscResp>
                        $nodeCodResp = $xpath->query(".//*[local-name()='codResp']", $eventoNode)->item(0);
                        $nodeDscResp = $xpath->query(".//*[local-name()='dscResp']", $eventoNode)->item(0);

                        $codigoErro = $nodeCodResp ? $nodeCodResp->nodeValue : $codRetorno;
                        $descErro = $nodeDscResp ? $nodeDscResp->nodeValue : "Erro desconhecido";

                        $msgErro = "$descErro (Cód: $codigoErro)";
                    }

                    if (!empty($idEventoXml)) {
                        $stmtUpdEvento->execute([$statusEvento, $nrRecibo, $msgErro, $idLote, $idEventoXml]);
                    }
                }

                // --- PROCESSAMENTO DE SUCESSO DE EXCLUSÃO (R-9000) ---
                // Se este lote contém eventos de exclusão, precisamos marcar os eventos originais como excluídos
                if (strpos($lote['xml_envio'], 'evtExclusao') !== false) {
                    $exclusoes = $xpathEnvio->query("//*[local-name()='evtExclusao']");
                    foreach ($exclusoes as $exclusao) {
                        $idExclusao = $exclusao->getAttribute('id');
                        
                        // 1. Verifica SUCESSO (2001)
                        $sucessoNode = $xpath->query("//*[local-name()='evento'][@Id='$idExclusao']//*[local-name()='cdRetorno'][text()='2001']")->item(0);
                        
                        if ($sucessoNode) {
                            // Pega o recibo que foi excluído (ou tentado)
                            $nrRecEvt = $xpathEnvio->query(".//*[local-name()='nrRecEvt']", $exclusao)->item(0);
                            if ($nrRecEvt) {
                                $reciboExcluido = $nrRecEvt->nodeValue;
                                $this->db->prepare("UPDATE reinf_eventos SET status = 'excluido' WHERE numero_recibo = ?")->execute([$reciboExcluido]);
                            }
                        }
                    }
                }
            } elseif ($cdStatus == '5') {
                $statusLoteDb = 'erro';
                $mensagem = 'Lote rejeitado.';
            }

            // Atualiza Lote
            $sqlUpdLote = "UPDATE reinf_lotes SET status = ?, xml_retorno = ? WHERE id = ?";
            $stmtUpdLote = $this->db->prepare($sqlUpdLote);
            $stmtUpdLote->execute([$statusLoteDb, $retornoApi['response'], $idLote]);

            echo json_encode([
                'success' => true,
                'status_lote' => $statusLoteDb,
                'mensagem' => $mensagem . " (Status: $cdStatus)"
            ]);

        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }

    public function listarPendencias()
    {
        try {
            $periodo = $_GET['periodo'] ?? date('Y-m');

            $sql = "
                SELECT 
                    f.id as id_fornecedor,
                    f.razao_social,
                    f.cnpj,
                    COUNT(p.id) as qtd_pagamentos,
                    SUM(p.valor_bruto) as total_bruto,
                    SUM(p.valor_ir) as total_ir,
                    GROUP_CONCAT(p.id) as ids_pagamentos
                FROM pagamentos p
                JOIN notas_fiscais nf ON p.id_nota = nf.id
                JOIN fornecedores f ON nf.id_fornecedor = f.id
                WHERE DATE_FORMAT(p.data_pagamento, '%Y-%m') = :periodo
                AND nf.id_orgao = :id_orgao
                AND p.id NOT IN (
                    SELECT rep.id_pagamento 
                    FROM reinf_evento_pagamentos rep
                    JOIN reinf_eventos re ON rep.id_evento = re.id
                    WHERE re.status IN ('sucesso', 'em_lote', 'processando', 'enviado')
                )
                GROUP BY f.id, f.razao_social, f.cnpj
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':periodo' => $periodo, ':id_orgao' => $this->idOrgao]);
            $pendencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sqlLotes = "
                SELECT l.*, 
                       (SELECT 
                            CASE 
                                WHEN e.tipo_evento = 'R-4099' AND e.xml_assinado LIKE '%<fechRet>1</fechRet>%' THEN 'R-4099-Reabertura'
                                ELSE e.tipo_evento 
                            END
                        FROM reinf_eventos e WHERE e.id_lote = l.id LIMIT 1) as tipo_evento
                FROM reinf_lotes l 
                WHERE l.id_orgao = :id_orgao 
                AND EXISTS (
                    SELECT 1 FROM reinf_eventos e 
                    WHERE e.id_lote = l.id 
                    AND (e.tipo_evento = 'R-4020' OR e.tipo_evento = 'R-9000' OR e.tipo_evento LIKE 'R-4099%')
                )
                ORDER BY l.created_at DESC 
                LIMIT 100
            ";
            $stmtLotes = $this->db->prepare($sqlLotes);
            $stmtLotes->execute([':id_orgao' => $this->idOrgao]);
            $historico = $stmtLotes->fetchAll(PDO::FETCH_ASSOC);

            // Busca histórico de EVENTOS individuais (R-4020, R-9000, etc)
            $sqlEventos = "
                SELECT e.id, e.id_lote, l.created_at, e.tipo_evento, e.status, e.numero_recibo, e.mensagem_erro,
                       COALESCE(f.razao_social, CASE 
                           WHEN e.tipo_evento = 'R-4099' AND e.xml_assinado LIKE '%<fechRet>1</fechRet>%' THEN 'Reabertura de Período'
                           WHEN e.tipo_evento = 'R-4099' AND e.xml_assinado LIKE '%<fechRet>0</fechRet>%' THEN 'Fechamento de Período'
                           WHEN e.tipo_evento LIKE '%Reabertura%' OR e.tipo_evento LIKE '%-Reab%' THEN 'Reabertura de Período'
                           WHEN e.tipo_evento LIKE 'R-4099%' THEN 'Fechamento de Período'
                           ELSE 'Sistema' 
                       END) as razao_social, 
                       f.cnpj, e.id_fornecedor, e.per_apuracao
                FROM reinf_eventos e
                LEFT JOIN fornecedores f ON e.id_fornecedor = f.id
                JOIN reinf_lotes l ON e.id_lote = l.id
                WHERE l.id_orgao = :id_orgao
                ORDER BY l.created_at DESC
                LIMIT 100
            ";
            $stmtEventos = $this->db->prepare($sqlEventos);
            $stmtEventos->execute([':id_orgao' => $this->idOrgao]);
            $eventos = $stmtEventos->fetchAll(PDO::FETCH_ASSOC);

            // --- RESUMO DE ENVIOS COM SUCESSO ---
            $sqlResumo = "
                SELECT 
                    f.razao_social,
                    f.cnpj,
                    e.numero_recibo,
                    COUNT(rep.id_pagamento) as qtd_pagamentos,
                    SUM(p.valor_bruto) as total_bruto,
                    SUM(p.valor_ir) as total_retido
                FROM reinf_eventos e
                JOIN fornecedores f ON e.id_fornecedor = f.id
                JOIN reinf_evento_pagamentos rep ON e.id = rep.id_evento
                JOIN pagamentos p ON rep.id_pagamento = p.id
                JOIN reinf_lotes l ON e.id_lote = l.id
                WHERE l.id_orgao = :id_orgao
                AND e.per_apuracao = :periodo
                AND e.status = 'sucesso'
                AND e.tipo_evento = 'R-4020'
                GROUP BY f.id, f.razao_social, f.cnpj, e.numero_recibo
            ";
            
            $stmtResumo = $this->db->prepare($sqlResumo);
            $stmtResumo->execute([':id_orgao' => $this->idOrgao, ':periodo' => $periodo]);
            $resumo = $stmtResumo->fetchAll(PDO::FETCH_ASSOC);

            // --- STATUS DO PERÍODO (ABERTO/FECHADO) ---
            $sqlStatus = "
                SELECT e.xml_assinado 
                FROM reinf_eventos e
                JOIN reinf_lotes l ON e.id_lote = l.id
                WHERE l.id_orgao = :id_orgao
                AND e.per_apuracao = :periodo
                AND e.tipo_evento = 'R-4099'
                AND e.status = 'sucesso'
                ORDER BY e.id DESC LIMIT 1
            ";
            $stmtStatus = $this->db->prepare($sqlStatus);
            $stmtStatus->execute([':id_orgao' => $this->idOrgao, ':periodo' => $periodo]);
            $ultimoFechamento = $stmtStatus->fetch(PDO::FETCH_ASSOC);

            // Se encontrar um evento de fechamento (0), o status é Fechado. Caso contrário (1 ou null), é Aberto.
            $statusPeriodo = ($ultimoFechamento && strpos($ultimoFechamento['xml_assinado'], '<fechRet>0</fechRet>') !== false) ? 'Fechado' : 'Aberto';

            echo json_encode(['success' => true, 'pendencias' => $pendencias, 'historico' => $historico, 'eventos' => $eventos, 'resumo' => $resumo, 'status_periodo' => $statusPeriodo]);

        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }

    public function enviarLote() {
        try {            
            $input = json_decode(file_get_contents('php://input'), true);
            $periodo = $input['periodo'] ?? null;
            $idsFornecedores = $input['fornecedores'] ?? [];

            if (!$periodo || empty($idsFornecedores)) {
                throw new Exception("Dados inválidos para envio.");
            }

            if (!$this->reinfConfig) throw new Exception("Certificado digital não configurado. Verifique o cadastro do órgão.");

            // 1. Dados do Órgão (Busca do Banco de Dados)
            $sqlOrgao = "SELECT * FROM orgaos WHERE id = ?";
            $stmtOrgao = $this->db->prepare($sqlOrgao);
            $stmtOrgao->execute([$this->idOrgao]);
            $orgaoDb = $stmtOrgao->fetch(PDO::FETCH_ASSOC);

            if (!$orgaoDb) throw new Exception("Órgão não encontrado.");

            $dadosOrgao = [
                'tpInsc' => 1, 
                'nrInsc' => preg_replace('/[^0-9]/', '', $orgaoDb['cnpj']), 
                'cnpj' => preg_replace('/[^0-9]/', '', $orgaoDb['cnpj']),
                'ambiente' => 2 // 2 = Pre-Prod
            ];

            $builder = new R4020Builder();
            $signer = new ReinfSigner($this->reinfConfig);
            
            $xmlsAssinados = [];
            $eventosGerados = []; 

            // 2. Gera XML para cada fornecedor
            foreach ($idsFornecedores as $idFornecedor) {
                $sqlForn = "SELECT * FROM fornecedores WHERE id = ?";
                $stmtForn = $this->db->prepare($sqlForn);
                $stmtForn->execute([$idFornecedor]);
                $dadosFornecedor = $stmtForn->fetch(PDO::FETCH_ASSOC);

                // VERIFICAÇÃO DE PENDÊNCIAS: Impede envio se houver lote anterior sem consulta
                $sqlPending = "SELECT id FROM reinf_eventos WHERE id_fornecedor = ? AND per_apuracao = ? AND status IN ('em_lote', 'processando', 'pendente')";
                $stmtPending = $this->db->prepare($sqlPending);
                $stmtPending->execute([$idFornecedor, $periodo]);
                if ($stmtPending->fetch()) {
                    throw new Exception("Existem envios pendentes para o fornecedor {$dadosFornecedor['razao_social']}. Consulte o status dos lotes anteriores antes de enviar novamente.");
                }

                $sqlPgtos = "
                    SELECT p.*, 
                           CASE 
                               WHEN ns.codigo_reinf IS NOT NULL AND ns.codigo_reinf != '' THEN ns.codigo_reinf 
                               ELSE ns.codigo_rfb 
                           END as nat_rendimento 
                    FROM pagamentos p
                    JOIN notas_fiscais nf ON p.id_nota = nf.id
                    JOIN natureza_servicos ns ON nf.id_natureza_servico = ns.id
                    WHERE nf.id_fornecedor = ? 
                    AND nf.id_orgao = ?
                    AND DATE_FORMAT(p.data_pagamento, '%Y-%m') = ?
                ";
                $stmtPgtos = $this->db->prepare($sqlPgtos);
                $stmtPgtos->execute([$idFornecedor, $this->idOrgao, $periodo]);
                $listaPagamentos = $stmtPgtos->fetchAll(PDO::FETCH_ASSOC);

                if (empty($listaPagamentos)) continue;

                foreach($listaPagamentos as &$p) {
                    $p['per_apuracao'] = $periodo;
                }

                // Verifica se já existe evento com sucesso para retificação (Busca por CNPJ para maior segurança)
                $sqlCheck = "
                    SELECT e.numero_recibo 
                    FROM reinf_eventos e
                    JOIN reinf_lotes l ON e.id_lote = l.id
                    JOIN fornecedores f ON e.id_fornecedor = f.id
                    WHERE f.cnpj = ? 
                    AND l.id_orgao = ?
                    AND e.per_apuracao = ? 
                    AND e.status = 'sucesso' 
                    AND e.tipo_evento = 'R-4020'
                    AND e.numero_recibo IS NOT NULL 
                    ORDER BY e.id DESC LIMIT 1
                ";
                $stmtCheck = $this->db->prepare($sqlCheck);
                $stmtCheck->execute([$dadosFornecedor['cnpj'], $this->idOrgao, $periodo]);
                $eventoAnterior = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                $indRetif = 1;
                $nrRecibo = null;

                if ($eventoAnterior) {
                    $indRetif = 2;
                    $nrRecibo = $eventoAnterior['numero_recibo'];
                }

                $resultadoBuild = $builder->build($dadosOrgao, $dadosFornecedor, $listaPagamentos, $indRetif, $nrRecibo);
                $xmlsAssinados[] = $signer->sign($resultadoBuild['xml'], 'evtRetPJ');
                
                $eventosGerados[] = [
                    'id_fornecedor' => $idFornecedor,
                    'id_evento_xml' => $resultadoBuild['id'],
                    'xml_assinado' => end($xmlsAssinados),
                    'ids_pagamentos' => array_column($listaPagamentos, 'id')
                ];
            }

            if (empty($xmlsAssinados)) throw new Exception("Nenhum evento gerado.");

            // --- CORREÇÃO DO ERRO FATAL AQUI ---
            // Antes estava invertido. O certo é: (CNPJ, Array de XMLs)
            $xmlLote = $this->montarEnvelopeLote($dadosOrgao['cnpj'], $xmlsAssinados);
            // ------------------------------------

            // 4. Envia para a API
            $client = new ReinfClient($this->reinfConfig);
            $retorno = $client->enviarLote($xmlLote);

            $domRetorno = new DOMDocument();
            $domRetorno->loadXML($retorno['response']);
            
            $protocolo = $domRetorno->getElementsByTagName('protocoloEnvio')->item(0)->nodeValue ?? null;

            if (!$protocolo) {
                throw new Exception("Erro no envio: " . strip_tags($retorno['response']));
            }

            // 5. Salva no Banco
            $this->db->beginTransaction();
            
            $stmtLote = $this->db->prepare("INSERT INTO reinf_lotes (id_orgao, protocolo, xml_envio, xml_retorno, status) VALUES (?, ?, ?, ?, 'enviado')");
            $stmtLote->execute([$this->idOrgao, $protocolo, $xmlLote, $retorno['response']]);
            $idLoteDb = $this->db->lastInsertId();

            $stmtEvento = $this->db->prepare("INSERT INTO reinf_eventos (id_lote, id_fornecedor, per_apuracao, tipo_evento, id_evento_xml, status, xml_assinado) VALUES (?, ?, ?, 'R-4020', ?, 'em_lote', ?)");
            $stmtVinculo = $this->db->prepare("INSERT INTO reinf_evento_pagamentos (id_evento, id_pagamento) VALUES (?, ?)");
            
            foreach ($eventosGerados as $evt) {
                $stmtEvento->execute([$idLoteDb, $evt['id_fornecedor'], $periodo, $evt['id_evento_xml'], $evt['xml_assinado']]);
                $idEventoDb = $this->db->lastInsertId();
                
                foreach ($evt['ids_pagamentos'] as $idPagamento) {
                    $stmtVinculo->execute([$idEventoDb, $idPagamento]);
                }
            }

            $this->db->commit();
            echo json_encode(['success' => true, 'protocolo' => $protocolo]);

        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->jsonError($e->getMessage());
        }
    }

    public function excluirEvento() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $idEventoDb = $input['id_evento'] ?? null;

            if (!$idEventoDb) throw new Exception("ID do evento não informado.");

            if (!$this->reinfConfig) throw new Exception("Certificado digital não configurado.");

            // 1. Busca o evento original no banco para pegar o recibo
            $sql = "SELECT * FROM reinf_eventos WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idEventoDb]);
            $eventoOriginal = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$eventoOriginal) throw new Exception("Evento não encontrado.");
            if (empty($eventoOriginal['numero_recibo'])) throw new Exception("Este evento não possui recibo, não pode ser excluído.");

            // 2. Dados do Órgão
            $sqlOrgao = "SELECT * FROM orgaos WHERE id = ?";
            $stmtOrgao = $this->db->prepare($sqlOrgao);
            $stmtOrgao->execute([$this->idOrgao]);
            $orgaoDb = $stmtOrgao->fetch(PDO::FETCH_ASSOC);

            $dadosOrgao = [
                'tpInsc' => 1, 
                'nrInsc' => preg_replace('/[^0-9]/', '', $orgaoDb['cnpj']), 
                'cnpj' => preg_replace('/[^0-9]/', '', $orgaoDb['cnpj']),
                'ambiente' => 2
            ];

            // 3. Gera XML de Exclusão (R-9000)
            $builder = new R9000Builder();
            $signer = new ReinfSigner($this->reinfConfig);

            // O tipo de evento original (ex: R-4020) vem do banco
            $tipoEvento = $eventoOriginal['tipo_evento'] ?? 'R-4020';
            
            $resultadoBuild = $builder->build($dadosOrgao, $tipoEvento, $eventoOriginal['numero_recibo'], $eventoOriginal['per_apuracao']);
            
            // VERIFICAÇÃO DE SEGURANÇA: Garante que não estamos enviando um R-4020 por engano
            if (strpos($resultadoBuild['xml'], 'evtRetPJ') !== false) {
                throw new Exception("Erro Crítico: O sistema tentou gerar um evento de inclusão (R-4020) em vez de exclusão (R-9000). Verifique o arquivo R9000Builder.php.");
            }

            $xmlAssinado = $signer->sign($resultadoBuild['xml'], 'evtExclusao');

            // 4. Monta Lote e Envia
            $xmlLote = $this->montarEnvelopeLote($dadosOrgao['cnpj'], [$xmlAssinado]);
            
            $client = new ReinfClient($this->reinfConfig);
            $retorno = $client->enviarLote($xmlLote);

            // 5. Processa Retorno (Protocolo)
            $domRetorno = new DOMDocument();
            $domRetorno->loadXML($retorno['response']);
            $protocolo = $domRetorno->getElementsByTagName('protocoloEnvio')->item(0)->nodeValue ?? null;

            if (!$protocolo) {
                throw new Exception("Erro no envio da exclusão: " . strip_tags($retorno['response']));
            }

            // 6. Salva o lote de exclusão no banco
            $stmtLote = $this->db->prepare("INSERT INTO reinf_lotes (id_orgao, protocolo, xml_envio, xml_retorno, status) VALUES (?, ?, ?, ?, 'enviado')");
            $stmtLote->execute([$this->idOrgao, $protocolo, $xmlLote, $retorno['response']]);
            $idLoteDb = $this->db->lastInsertId();

            // Salva também na tabela de eventos para rastreabilidade
            $stmtEvento = $this->db->prepare("INSERT INTO reinf_eventos (id_lote, id_fornecedor, per_apuracao, tipo_evento, id_evento_xml, status, xml_assinado) VALUES (?, ?, ?, 'R-9000', ?, 'em_lote', ?)");
            $stmtEvento->execute([$idLoteDb, $eventoOriginal['id_fornecedor'], $eventoOriginal['per_apuracao'], $resultadoBuild['id'], $xmlAssinado]);
            
            echo json_encode(['success' => true, 'protocolo' => $protocolo, 'message' => 'Solicitação de exclusão enviada com sucesso. Consulte o lote em instantes.']);

        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }

    public function enviarFechamento() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $periodo = $input['periodo'] ?? null;
            $acao = $input['acao'] ?? 1; // 1 = Fechamento, 0 = Reabertura

            if (!$periodo) throw new Exception("Período não informado.");
            if (!$this->reinfConfig) throw new Exception("Certificado digital não configurado.");

            // Dados do Órgão
            $sqlOrgao = "SELECT * FROM orgaos WHERE id = ?";
            $stmtOrgao = $this->db->prepare($sqlOrgao);
            $stmtOrgao->execute([$this->idOrgao]);
            $orgaoDb = $stmtOrgao->fetch(PDO::FETCH_ASSOC);

            $dadosOrgao = [
                'tpInsc' => 1, 
                'nrInsc' => preg_replace('/[^0-9]/', '', $orgaoDb['cnpj']), 
                'tpInsc' => 1, // 1 = CNPJ
                'ambiente' => 2 // Pre-prod
            ];

            $responsavel = [
                'nome' => $orgaoDb['contato_nome'],
                'cpf' => $orgaoDb['contato_cpf'],
                'telefone' => $orgaoDb['contato_telefone'],
                'email' => $orgaoDb['contato_email']
            ];

            $builder = new R4099Builder();
            $signer = new ReinfSigner($this->reinfConfig);

            // Mapeamento conforme Manual Reinf (R-4099):
            // Frontend envia: 1 = Fechar, 0 = Reabrir
            // XML exige: 0 = Fechamento, 1 = Reabertura
            $fechRet = ($acao == 1) ? 0 : 1;
            
            $resultadoBuild = $builder->build($dadosOrgao, $periodo, $responsavel, $fechRet);
            $xmlAssinado = $signer->sign($resultadoBuild['xml'], 'evtFech');

            // Monta Lote
            $xmlLote = $this->montarEnvelopeLote($dadosOrgao['nrInsc'], [$xmlAssinado]);

            // Envia
            $client = new ReinfClient($this->reinfConfig);
            $retorno = $client->enviarLote($xmlLote);

            // Processa Retorno
            $domRetorno = new DOMDocument();
            $domRetorno->loadXML($retorno['response']);
            $protocolo = $domRetorno->getElementsByTagName('protocoloEnvio')->item(0)->nodeValue ?? null;

            if (!$protocolo) throw new Exception("Erro no envio: " . strip_tags($retorno['response']));

            // Salva Lote
            $stmtLote = $this->db->prepare("INSERT INTO reinf_lotes (id_orgao, protocolo, xml_envio, xml_retorno, status) VALUES (?, ?, ?, ?, 'enviado')");
            $stmtLote->execute([$this->idOrgao, $protocolo, $xmlLote, $retorno['response']]);
            $idLoteDb = $this->db->lastInsertId();

            // Salva Evento para histórico
            $tipoEvento = ($acao == 1) ? 'R-4099' : 'R-4099-Reabertura';
            $stmtEvento = $this->db->prepare("INSERT INTO reinf_eventos (id_lote, id_fornecedor, per_apuracao, tipo_evento, id_evento_xml, status, xml_assinado) VALUES (?, NULL, ?, ?, ?, 'em_lote', ?)");
            $stmtEvento->execute([$idLoteDb, $periodo, $tipoEvento, $resultadoBuild['id'], $xmlAssinado]);

            echo json_encode(['success' => true, 'protocolo' => $protocolo, 'message' => 'Evento de fechamento/reabertura enviado com sucesso.']);

        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }

    public function buscarExtratoFechamento() {
        try {
            $periodo = $_GET['periodo'] ?? date('Y-m');
            
            // Busca o último evento de fechamento (R-4099) com status 'sucesso'
            // O XML de retorno do lote (que contém o R-9015) está na tabela reinf_lotes
            $sql = "
                SELECT l.xml_retorno, e.numero_recibo
                FROM reinf_eventos e
                JOIN reinf_lotes l ON e.id_lote = l.id
                WHERE e.per_apuracao = :periodo
                AND l.id_orgao = :id_orgao
                AND e.tipo_evento = 'R-4099'
                AND e.status = 'sucesso'
                ORDER BY e.id DESC LIMIT 1
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':periodo' => $periodo, ':id_orgao' => $this->idOrgao]);
            $evento = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$evento || empty($evento['xml_retorno'])) {
                echo json_encode(['success' => false, 'message' => 'Fechamento não encontrado ou sem retorno processado.']);
                return;
            }

            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadXML($evento['xml_retorno']);
            libxml_clear_errors();
            
            $xpath = new DOMXPath($dom);
            
            // Busca o evento R-9015 (evtRetCons) dentro do retorno
            // Usamos local-name() para evitar problemas com namespaces variáveis
            $evtRetCons = $xpath->query("//*[local-name()='evtRetCons']")->item(0);
            
            if (!$evtRetCons) {
                echo json_encode(['success' => false, 'message' => 'O XML de retorno não contém o extrato consolidado (R-9015).']);
                return;
            }

            $dados = [];
            
            // Mapeamento dos tipos de apuração conforme XSD R-9015
            $tiposApuracao = [
                'totApurMen' => ['nome' => 'Mensal', 'tagCR' => 'CRMen', 'tagVal' => 'vlrCRMenDCTF', 'tagSusp' => 'vlrCRMenSuspDCTF'],
                'totApurQui' => ['nome' => 'Quinzenal', 'tagCR' => 'CRQui', 'tagVal' => 'vlrCRQuiDCTF', 'tagSusp' => 'vlrCRQuiSuspDCTF'],
                'totApurDec' => ['nome' => 'Decendial', 'tagCR' => 'CRDec', 'tagVal' => 'vlrCRDecDCTF', 'tagSusp' => 'vlrCRDecSuspDCTF'],
                'totApurSem' => ['nome' => 'Semanal', 'tagCR' => 'CRSem', 'tagVal' => 'vlrCRSemDCTF', 'tagSusp' => 'vlrCRSemSuspDCTF'],
                'totApurDia' => ['nome' => 'Diário', 'tagCR' => 'CRDia', 'tagVal' => 'vlrCRDiaDCTF', 'tagSusp' => 'vlrCRDiaSuspDCTF']
            ];

            // [CORREÇÃO] Busca especificamente dentro de infoTotalCR para evitar duplicidade
            // O XML possui infoCR_CNR (detalhado) e infoTotalCR (consolidado).
            // Usamos o consolidado pois é o que reflete a guia de pagamento (DCTFWeb).
            $infoTotalCR = $xpath->query(".//*[local-name()='infoTotalCR']", $evtRetCons)->item(0);

            // Se não houver infoTotalCR (ex: sem movimento), usa o nó raiz para não quebrar, 
            // mas a busca abaixo não retornará nada se não houver as tags.
            $contextNode = $infoTotalCR ? $infoTotalCR : $evtRetCons;

            foreach ($tiposApuracao as $tagPai => $config) {
                // Usa ./ para buscar apenas filhos diretos do contexto selecionado
                $nodes = $xpath->query(".//*[local-name()='$tagPai']", $contextNode);
                foreach ($nodes as $node) {
                    $cr = $xpath->query(".//*[local-name()='{$config['tagCR']}']", $node)->item(0)->nodeValue ?? '-';
                    $val = $xpath->query(".//*[local-name()='{$config['tagVal']}']", $node)->item(0)->nodeValue ?? '0,00';
                    $susp = $xpath->query(".//*[local-name()='{$config['tagSusp']}']", $node)->item(0)->nodeValue ?? '0,00';
                    
                    // Evita adicionar se o CR for inválido ou vazio
                    if ($cr !== '-') {
                        $dados[] = [
                            'tipo' => $config['nome'],
                            'cr' => $cr,
                            'valor' => $val,
                            'suspenso' => $susp
                        ];
                    }
                }
            }

            echo json_encode([
                'success' => true, 
                'recibo' => $evento['numero_recibo'], 
                'extrato' => $dados,
                'xml_raw' => $evento['xml_retorno'] // Retorna o XML original para conferência
            ]);

        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }

    public function recuperarRecibos4020() {
        try {
            // Busca lotes processados que contenham retorno de R-4020 (evtRet)
            $sql = "SELECT id, xml_retorno, created_at FROM reinf_lotes WHERE status = 'processado' AND xml_retorno LIKE '%evtRet%' ORDER BY id DESC";
            $stmt = $this->db->query($sql);
            
            $recibosEncontrados = [];

            while ($lote = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (empty($lote['xml_retorno'])) continue;

                $dom = new DOMDocument();
                libxml_use_internal_errors(true);
                $dom->loadXML($lote['xml_retorno']);
                libxml_clear_errors();
                
                $xpath = new DOMXPath($dom);
                
                // Busca eventos de retorno R-4020 (evtRet)
                // O namespace pode variar, então usamos local-name()
                $eventos = $xpath->query("//*[local-name()='evtRet']");

                foreach ($eventos as $evento) {
                    // Verifica status de sucesso (cdRetorno = 0)
                    $cdRetorno = $xpath->query(".//*[local-name()='cdRetorno']", $evento)->item(0);
                    
                    if ($cdRetorno && $cdRetorno->nodeValue == '0') {
                        $nrRecibo = $xpath->query(".//*[local-name()='nrRecArqBase']", $evento)->item(0);
                        
                        if ($nrRecibo) {
                            $recibosEncontrados[] = [
                                'lote_id' => $lote['id'],
                                'data_lote' => $lote['created_at'],
                                'numero_recibo' => $nrRecibo->nodeValue
                            ];
                        }
                    }
                }
            }

            echo json_encode(['success' => true, 'total' => count($recibosEncontrados), 'dados' => $recibosEncontrados]);

        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }

    /**
     * Monta o envelope manualmente para garantir que a Assinatura (Signature)
     * mantenha seu namespace original (http://www.w3.org/2000/09/xmldsig#).
     */
    private function montarEnvelopeLote($cnpjOrgao, $xmlsAssinados) {
        $nsLote = 'http://www.reinf.esocial.gov.br/schemas/envioLoteEventosAssincrono/v1_00_00';
        
        // Garante CNPJ limpo e formata tags iniciais
        $cnpjString = is_array($cnpjOrgao) ? ($cnpjOrgao['cnpj'] ?? '') : $cnpjOrgao;
        $cnpjLimpo = preg_replace('/[^0-9]/', '', $cnpjString);
        $nrInsc = substr($cnpjLimpo, 0, 8);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<Reinf xmlns="' . $nsLote . '">';
        $xml .= '<envioLoteEventos>';
        $xml .= '<ideContribuinte>';
        $xml .= '<tpInsc>1</tpInsc>';
        $xml .= '<nrInsc>' . $nrInsc . '</nrInsc>';
        $xml .= '</ideContribuinte>';
        $xml .= '<eventos>';

        foreach ($xmlsAssinados as $xmlAssinado) {
            // Extrai o ID para o atributo do envelope
            preg_match('/id="([^"]+)"/i', $xmlAssinado, $matches);
            $idEvt = $matches[1] ?? '';
            
            if (empty($idEvt)) continue;

            // 1. Limpa o cabeçalho do evento individual
            $eventoLimpo = preg_replace('/<\?xml.*?\?>/', '', $xmlAssinado);
            $eventoLimpo = trim($eventoLimpo);

            // 2. CORREÇÃO "CIRÚRGICA" DA ASSINATURA
            // Verifica se a tag Signature está "pelada" (sem xmlns) e injeta o namespace correto.
            // Isso resolve o erro: List of possible elements expected: 'Signature' in namespace 'http://www.w3.org/2000/09/xmldsig#'.
            if (strpos($eventoLimpo, '<Signature>') !== false) {
                $eventoLimpo = str_replace(
                    '<Signature>', 
                    '<Signature xmlns="http://www.w3.org/2000/09/xmldsig#">', 
                    $eventoLimpo
                );
            }

            // Adiciona ao lote
            $xml .= '<evento Id="' . $idEvt . '">';
            $xml .= $eventoLimpo;
            $xml .= '</evento>';
        }

        $xml .= '</eventos>';
        $xml .= '</envioLoteEventos>';
        $xml .= '</Reinf>';

        return $xml;
    }

    private function jsonError($msg)
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $msg]);
        exit;
    }

    // Cole isso DENTRO da classe ReinfController, antes do último fecha-chaves "}"
    
    public function validarLote() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $periodo = $input['periodo'] ?? null;
            $idsFornecedores = $input['fornecedores'] ?? [];

            if (!$periodo || empty($idsFornecedores)) {
                throw new Exception("Selecione o período e os fornecedores.");
            }

            if (!$this->reinfConfig) throw new Exception("Certificado digital não configurado.");

            // --- CORREÇÃO DO CAMINHO DO XSD ---
            // __DIR__ é a pasta do Controller (app/Controllers)
            // Saimos dela (../) e entramos em Schemas
            $xsdPath = __DIR__ . '/../Schemas/R-4020-evt4020PagtoBeneficiarioPJ-v2_01_02e.xsd';
            
            // Corrige barras para o padrão do SO (Windows/Linux)
            $xsdPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $xsdPath);

            if (!file_exists($xsdPath)) {
                throw new Exception("Arquivo XSD não encontrado no caminho: $xsdPath");
            }

            // Busca dados do órgão
            $sqlOrgao = "SELECT * FROM orgaos WHERE id = ?";
            $stmtOrgao = $this->db->prepare($sqlOrgao);
            $stmtOrgao->execute([$this->idOrgao]);
            $orgaoDb = $stmtOrgao->fetch(PDO::FETCH_ASSOC);

            if (!$orgaoDb) throw new Exception("Órgão não encontrado.");

            $dadosOrgao = [
                'tpInsc' => 1, 
                'nrInsc' => preg_replace('/[^0-9]/', '', $orgaoDb['cnpj']), 
                'cnpj' => preg_replace('/[^0-9]/', '', $orgaoDb['cnpj']), 
                'ambiente' => 2
            ];

            $builder = new R4020Builder();
            $signer = new ReinfSigner($this->reinfConfig);
            
            $errosValidacao = [];
            $sucessoValidacao = [];

            foreach ($idsFornecedores as $idFornecedor) {
                // Busca dados
                $sqlForn = "SELECT * FROM fornecedores WHERE id = ?";
                $stmtForn = $this->db->prepare($sqlForn);
                $stmtForn->execute([$idFornecedor]);
                $dadosFornecedor = $stmtForn->fetch(PDO::FETCH_ASSOC);

                $sqlPgtos = "
                    SELECT p.*, ns.codigo_rfb as nat_rendimento 
                    FROM pagamentos p
                    JOIN notas_fiscais nf ON p.id_nota = nf.id
                    JOIN natureza_servicos ns ON nf.id_natureza_servico = ns.id
                    WHERE nf.id_fornecedor = ? AND DATE_FORMAT(p.data_pagamento, '%Y-%m') = ?
                ";
                $stmtPgtos = $this->db->prepare($sqlPgtos);
                $stmtPgtos->execute([$idFornecedor, $periodo]);
                $listaPagamentos = $stmtPgtos->fetchAll(PDO::FETCH_ASSOC);

                if (empty($listaPagamentos)) continue;

                foreach($listaPagamentos as &$p) $p['per_apuracao'] = $periodo;

                // Gera XML
                $resultadoBuild = $builder->build($dadosOrgao, $dadosFornecedor, $listaPagamentos);
                
                // Assina (O XML precisa estar assinado para validar se o XSD exigir Signature)
                $xmlAssinado = $signer->sign($resultadoBuild['xml'], 'evtRetPJ');

                // --- VALIDAÇÃO ---
                $dom = new DOMDocument();
                $dom->preserveWhiteSpace = false;
                $dom->formatOutput = true;
                $dom->loadXML($xmlAssinado);

                libxml_use_internal_errors(true);

                if (!$dom->schemaValidate($xsdPath)) {
                    $errors = libxml_get_errors();
                    $listaErros = [];
                    foreach ($errors as $error) {
                        $msg = trim($error->message);
                        // Filtra erros de assinatura se você não tiver o xmldsig-core-schema.xsd na mesma pasta
                        if (strpos($msg, 'Signature') !== false && strpos($msg, 'definition') !== false) {
                            continue; 
                        }
                        $listaErros[] = "Linha {$error->line}: {$msg}";
                    }
                    libxml_clear_errors();
                    
                    if (!empty($listaErros)) {
                        $errosValidacao[] = [
                            'fornecedor' => $dadosFornecedor['razao_social'],
                            'erros' => $listaErros
                        ];
                    } else {
                         $sucessoValidacao[] = $dadosFornecedor['razao_social'];
                    }
                } else {
                    $sucessoValidacao[] = $dadosFornecedor['razao_social'];
                }
            }

            echo json_encode([
                'success' => true,
                'validos' => $sucessoValidacao,
                'invalidos' => $errosValidacao
            ]);

        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
}