<?php
// app/Controllers/ReinfController.php

require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Core/Session.php';
require_once __DIR__ . '/../Services/Reinf/ReinfConfig.php';
require_once __DIR__ . '/../Services/Reinf/R4020Builder.php';
require_once __DIR__ . '/../Services/Reinf/ReinfSigner.php';
require_once __DIR__ . '/../Services/Reinf/ReinfClient.php';

ini_set('display_errors', 1);
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
        $this->idOrgao = $_SESSION['usuario']['id_orgao'] ?? 1;

        // Configuração do Certificado (Caminho Absoluto)
        $caminhoRelativo = __DIR__ . '/../../certificados/certificado.pfx';
        $certPath = realpath($caminhoRelativo);

        if ($certPath === false || !file_exists($certPath)) {
            header('Content-Type: application/json', true, 500);
            echo json_encode(['success' => false, 'error' => "Certificado não encontrado em: $caminhoRelativo"]);
            exit;
        }

        $certPass = '123456'; // SENHA DO CERTIFICADO
        $this->reinfConfig = new ReinfConfig($certPath, $certPass, 2);
    }    

    public function detalharLote()
    {
        try {
            $idLote = $_GET['id_lote'] ?? null;
            if (!$idLote)
                throw new Exception("ID do lote não informado.");

            $sql = "
                SELECT 
                    e.status,
                    e.numero_recibo,
                    e.mensagem_erro,
                    e.id_evento_xml,
                    f.razao_social,
                    f.cnpj
                FROM reinf_eventos e
                JOIN fornecedores f ON e.id_fornecedor = f.id
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

            // Busca Status (cdResposta ou cdStatus)
            $statusNode = $xpath->query("//*[local-name()='cdResposta']")->item(0);
            if (!$statusNode)
                $statusNode = $xpath->query("//*[local-name()='cdStatus']")->item(0);

            $cdStatus = $statusNode ? $statusNode->nodeValue : '0';

            $statusLoteDb = 'processando';
            $mensagem = 'Aguardando processamento...';

            // Códigos de Finalização: 3 (Com Erros) ou 4 (Sucesso)
            if ($cdStatus == '3' || $cdStatus == '4') {
                $statusLoteDb = 'processado'; // Finaliza o ciclo do lote
                $mensagem = ($cdStatus == '4') ? 'Sucesso total!' : 'Processado com erros.';

                // Busca eventos
                $eventosNodes = $xpath->query("//*[local-name()='retornoEventos']/*[local-name()='evento']");

                $sqlUpdEvento = "UPDATE reinf_eventos SET status = ?, numero_recibo = ?, mensagem_erro = ? WHERE id_lote = ? AND id_evento_xml = ?";
                $stmtUpdEvento = $this->db->prepare($sqlUpdEvento);

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

                    if ($codRetorno == '2001') { // 2001 = Sucesso
                        $statusEvento = 'sucesso';
                        $nodeRecibo = $xpath->query(".//*[local-name()='nrRecibo']", $eventoNode)->item(0);
                        if ($nodeRecibo)
                            $nrRecibo = $nodeRecibo->nodeValue;
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
                    SELECT rep.id_pagamento FROM reinf_evento_pagamentos rep
                )
                GROUP BY f.id, f.razao_social, f.cnpj
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':periodo' => $periodo, ':id_orgao' => $this->idOrgao]);
            $pendencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sqlLotes = "SELECT * FROM reinf_lotes WHERE id_orgao = :id_orgao ORDER BY created_at DESC LIMIT 10";
            $stmtLotes = $this->db->prepare($sqlLotes);
            $stmtLotes->execute([':id_orgao' => $this->idOrgao]);
            $historico = $stmtLotes->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'pendencias' => $pendencias, 'historico' => $historico]);

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

            // 1. Dados do Órgão
            $dadosOrgao = [
                'tpInsc' => 1, 
                'nrInsc' => '00860058000105', 
                'cnpj' => '00860058000105',
                'ambiente' => 2 
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

                $sqlPgtos = "
                    SELECT p.*, ns.codigo_rfb as nat_rendimento 
                    FROM pagamentos p
                    JOIN notas_fiscais nf ON p.id_nota = nf.id
                    JOIN natureza_servicos ns ON nf.id_natureza_servico = ns.id
                    WHERE nf.id_fornecedor = ? 
                    AND DATE_FORMAT(p.data_pagamento, '%Y-%m') = ?
                ";
                $stmtPgtos = $this->db->prepare($sqlPgtos);
                $stmtPgtos->execute([$idFornecedor, $periodo]);
                $listaPagamentos = $stmtPgtos->fetchAll(PDO::FETCH_ASSOC);

                if (empty($listaPagamentos)) continue;

                foreach($listaPagamentos as &$p) {
                    $p['per_apuracao'] = $periodo;
                }

                $resultadoBuild = $builder->build($dadosOrgao, $dadosFornecedor, $listaPagamentos);
                $xmlsAssinados[] = $signer->sign($resultadoBuild['xml'], 'evtRetPJ');
                
                $eventosGerados[] = [
                    'id_fornecedor' => $idFornecedor,
                    'id_evento_xml' => $resultadoBuild['id'],
                    'xml_assinado' => end($xmlsAssinados)
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
            $stmtLote->execute([1, $protocolo, $xmlLote, $retorno['response']]);
            $idLoteDb = $this->db->lastInsertId();

            $stmtEvento = $this->db->prepare("INSERT INTO reinf_eventos (id_lote, id_fornecedor, per_apuracao, tipo_evento, id_evento_xml, status, xml_assinado) VALUES (?, ?, ?, 'R-4020', ?, 'em_lote', ?)");
            foreach ($eventosGerados as $evt) {
                $stmtEvento->execute([$idLoteDb, $evt['id_fornecedor'], $periodo, $evt['id_evento_xml'], $evt['xml_assinado']]);
            }

            $this->db->commit();
            echo json_encode(['success' => true, 'protocolo' => $protocolo]);

        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
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

            // --- CORREÇÃO DO CAMINHO DO XSD ---
            // __DIR__ é a pasta do Controller (app/Controllers)
            // Saimos dela (../) e entramos em Schemas
            $xsdPath = __DIR__ . '/../Schemas/R-4020-evt4020PagtoBeneficiarioPJ-v2_01_02e.xsd';
            
            // Corrige barras para o padrão do SO (Windows/Linux)
            $xsdPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $xsdPath);

            if (!file_exists($xsdPath)) {
                throw new Exception("Arquivo XSD não encontrado no caminho: $xsdPath");
            }

            // Prepara dados
            $dadosOrgao = [
                'tpInsc' => 1, 'nrInsc' => '00860058000105', 'cnpj' => '00860058000105', 'ambiente' => 2
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