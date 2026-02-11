<?php
// app/Controllers/Reinf/ReinfController4020.php

require_once __DIR__ . '/ReinfBaseController.php';
require_once __DIR__ . '/../../Services/Reinf/R4020Builder.php';

class ReinfController4020 extends ReinfBaseController {

    public function listarPendencias() {
        try {
            $periodo = $_GET['periodo'] ?? date('Y-m');
            
            // Query original preservada
            $sql = "
                SELECT 
                    f.id as id_fornecedor,
                    f.razao_social,
                    f.cnpj,
                    COUNT(nf.id) as qtd_notas,
                    SUM(nf.valor_irrf_retido) as total_ir,
                    SUM(nf.valor_bruto) as total_bruto
                FROM notas_fiscais nf
                JOIN fornecedores f ON nf.id_fornecedor = f.id
                LEFT JOIN reinf_eventos re ON re.id_fornecedor = f.id 
                                           AND re.per_apuracao = :periodo 
                                           AND re.status IN ('sucesso', 'em_lote', 'pendente')
                WHERE DATE_FORMAT(nf.data_emissao, '%Y-%m') = :periodo
                AND nf.nota_ativa = 1
                AND re.id IS NULL 
                GROUP BY f.id, f.razao_social, f.cnpj
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':periodo' => $periodo]);
            $pendencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sqlHist = "SELECT * FROM reinf_lotes ORDER BY created_at DESC LIMIT 10";
            $stmtHist = $this->db->query($sqlHist);
            $historico = $stmtHist->fetchAll(PDO::FETCH_ASSOC);

            $this->jsonResponse([
                'success' => true, 
                'pendencias' => $pendencias,
                'historico' => $historico
            ]);

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

            $dadosOrgao = [
                'tpInsc' => 1, 'nrInsc' => '00860058000105', 'cnpj' => '00860058000105', 'ambiente' => 2 
            ];

            $builder = new R4020Builder();
            $signer = new ReinfSigner($this->reinfConfig);
            
            $xmlsAssinados = [];
            $eventosGerados = []; 

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
                $xmlAssinado = $signer->sign($resultadoBuild['xml'], 'evtRetPJ');
                
                $xmlsAssinados[] = $xmlAssinado;
                
                $eventosGerados[] = [
                    'id_fornecedor' => $idFornecedor,
                    'id_evento_xml' => $resultadoBuild['id'], 
                    'xml_assinado' => $xmlAssinado
                ];
            }

            if (empty($xmlsAssinados)) {
                throw new Exception("Nenhum evento gerado.");
            }

            // Chama o método do PAI (BaseController)
            $xmlLote = $this->montarEnvelopeLote($dadosOrgao['cnpj'], $xmlsAssinados);

            $client = new ReinfClient($this->reinfConfig);
            $retorno = $client->enviarLote($xmlLote);

            $domRetorno = new DOMDocument();
            $domRetorno->loadXML($retorno['response']);
            $protocolo = $domRetorno->getElementsByTagName('protocoloEnvio')->item(0)->nodeValue ?? null;

            if (!$protocolo) {
                $msgErro = strip_tags($retorno['response']);
                throw new Exception("Erro no envio (Sem Protocolo): " . substr($msgErro, 0, 200));
            }

            $this->db->beginTransaction();

            $sqlLote = "INSERT INTO reinf_lotes (id_orgao, protocolo, xml_envio, xml_retorno, status) VALUES (?, ?, ?, ?, 'enviado')";
            $stmtLote = $this->db->prepare($sqlLote);
            $stmtLote->execute([1, $protocolo, $xmlLote, $retorno['response']]);
            $idLoteDb = $this->db->lastInsertId();

            $sqlEvento = "INSERT INTO reinf_eventos (id_lote, id_fornecedor, per_apuracao, tipo_evento, id_evento_xml, status, xml_assinado) VALUES (?, ?, ?, 'R-4020', ?, 'em_lote', ?)";
            $stmtEvento = $this->db->prepare($sqlEvento);

            foreach ($eventosGerados as $evt) {
                $stmtEvento->execute([
                    $idLoteDb, $evt['id_fornecedor'], $periodo, $evt['id_evento_xml'], $evt['xml_assinado']
                ]);
            }

            $this->db->commit();
            $this->jsonResponse(['success' => true, 'protocolo' => $protocolo]);

        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->jsonError($e->getMessage());
        }
    }

    public function consultarLote() {
        try {
            $idLote = $_POST['id_lote'] ?? null;
            if (!$idLote) throw new Exception("ID do lote não informado.");

            $sql = "SELECT * FROM reinf_lotes WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $idLote]);
            $lote = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$lote || empty($lote['protocolo'])) throw new Exception("Lote sem protocolo.");

            $client = new ReinfClient($this->reinfConfig);
            $retornoApi = $client->consultarLote($lote['protocolo']);

            if ($retornoApi['code'] != 200) {
                 throw new Exception("Erro API: " . strip_tags(substr($retornoApi['response'], 0, 300)));
            }

            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadXML($retornoApi['response']);
            libxml_clear_errors();

            $xpath = new DOMXPath($dom);
            
            $statusNode = $xpath->query("//*[local-name()='cdResposta']")->item(0);
            if (!$statusNode) $statusNode = $xpath->query("//*[local-name()='cdStatus']")->item(0);
            
            $cdStatus = $statusNode ? $statusNode->nodeValue : '0';

            $statusLoteDb = 'processando'; 
            $mensagem = 'Aguardando processamento...';

            if ($cdStatus == '3' || $cdStatus == '4') {
                $statusLoteDb = 'processado'; 
                $mensagem = ($cdStatus == '4') ? 'Sucesso total!' : 'Processado com erros.';
                
                $eventosNodes = $xpath->query("//*[local-name()='retornoEventos']/*[local-name()='evento']");
                
                $sqlUpdEvento = "UPDATE reinf_eventos SET status = ?, numero_recibo = ?, mensagem_erro = ? WHERE id_lote = ? AND id_evento_xml = ?";
                $stmtUpdEvento = $this->db->prepare($sqlUpdEvento);

                foreach ($eventosNodes as $eventoNode) {
                    $idEventoXml = $eventoNode->getAttribute('Id');
                    if(empty($idEventoXml)) $idEventoXml = $eventoNode->getAttribute('id');

                    $nodeCod = $xpath->query(".//*[local-name()='cdRetorno']", $eventoNode)->item(0);
                    $codRetorno = $nodeCod ? $nodeCod->nodeValue : '';

                    $nrRecibo = null;
                    $msgErro = null;
                    $statusEvento = 'rejeitado';

                    if ($codRetorno == '2001') { 
                        $statusEvento = 'sucesso';
                        $nodeRecibo = $xpath->query(".//*[local-name()='nrRecibo']", $eventoNode)->item(0);
                        if ($nodeRecibo) $nrRecibo = $nodeRecibo->nodeValue;
                    } else {
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

            $sqlUpdLote = "UPDATE reinf_lotes SET status = ?, xml_retorno = ? WHERE id = ?";
            $stmtUpdLote = $this->db->prepare($sqlUpdLote);
            $stmtUpdLote->execute([$statusLoteDb, $retornoApi['response'], $idLote]);

            $this->jsonResponse([
                'success' => true, 
                'status_lote' => $statusLoteDb,
                'mensagem' => $mensagem . " (Status: $cdStatus)"
            ]);

        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }

    public function detalharLote() {
        try {
            $idLote = $_GET['id_lote'] ?? null;
            if (!$idLote) throw new Exception("ID do lote não informado.");

            $sql = "
                SELECT e.status, e.numero_recibo, e.mensagem_erro, e.id_evento_xml, f.razao_social, f.cnpj
                FROM reinf_eventos e
                JOIN fornecedores f ON e.id_fornecedor = f.id
                WHERE e.id_lote = :id_lote
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id_lote' => $idLote]);
            $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->jsonResponse(['success' => true, 'eventos' => $eventos]);

        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }

    public function validarLote() {
        // ... (Mantive essa função que fizemos para validação local) ...
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $periodo = $input['periodo'] ?? null;
            $idsFornecedores = $input['fornecedores'] ?? [];

            if (!$periodo || empty($idsFornecedores)) throw new Exception("Selecione o período e os fornecedores.");

            $xsdPath = __DIR__ . '/../../Schemas/R-4020-evt4020PagtoBeneficiarioPJ-v2_01_02e.xsd';
            
            if (!file_exists($xsdPath)) throw new Exception("XSD não encontrado.");

            $dadosOrgao = ['tpInsc' => 1, 'nrInsc' => '00860058000105', 'cnpj' => '00860058000105', 'ambiente' => 2];
            $builder = new R4020Builder();
            $signer = new ReinfSigner($this->reinfConfig);
            $errosValidacao = []; $sucessoValidacao = [];

            foreach ($idsFornecedores as $idFornecedor) {
                // ... Lógica de busca de dados (Resumida para caber aqui, mas copie a do anterior)
                $sqlForn = "SELECT * FROM fornecedores WHERE id = ?";
                $stmtForn = $this->db->prepare($sqlForn);
                $stmtForn->execute([$idFornecedor]);
                $dadosFornecedor = $stmtForn->fetch(PDO::FETCH_ASSOC);
                $sqlPgtos = "SELECT p.*, ns.codigo_rfb as nat_rendimento FROM pagamentos p JOIN notas_fiscais nf ON p.id_nota = nf.id JOIN natureza_servicos ns ON nf.id_natureza_servico = ns.id WHERE nf.id_fornecedor = ? AND DATE_FORMAT(p.data_pagamento, '%Y-%m') = ?";
                $stmtPgtos = $this->db->prepare($sqlPgtos);
                $stmtPgtos->execute([$idFornecedor, $periodo]);
                $listaPagamentos = $stmtPgtos->fetchAll(PDO::FETCH_ASSOC);
                if (empty($listaPagamentos)) continue;
                foreach($listaPagamentos as &$p) $p['per_apuracao'] = $periodo;

                $resultadoBuild = $builder->build($dadosOrgao, $dadosFornecedor, $listaPagamentos);
                $xmlAssinado = $signer->sign($resultadoBuild['xml'], 'evtRetPJ');
                
                $dom = new DOMDocument();
                $dom->preserveWhiteSpace = false; $dom->formatOutput = true; $dom->loadXML($xmlAssinado);
                libxml_use_internal_errors(true);
                if (!$dom->schemaValidate($xsdPath)) {
                    $errors = libxml_get_errors(); $listaErros = [];
                    foreach ($errors as $error) {
                        $msg = trim($error->message);
                        if (strpos($msg, 'Signature') !== false) continue; 
                        $listaErros[] = "Linha {$error->line}: {$msg}";
                    }
                    libxml_clear_errors();
                    if (!empty($listaErros)) $errosValidacao[] = ['fornecedor' => $dadosFornecedor['razao_social'], 'erros' => $listaErros];
                    else $sucessoValidacao[] = $dadosFornecedor['razao_social'];
                } else {
                    $sucessoValidacao[] = $dadosFornecedor['razao_social'];
                }
            }
            $this->jsonResponse(['success' => true, 'validos' => $sucessoValidacao, 'invalidos' => $errosValidacao]);
        } catch (Exception $e) { $this->jsonError($e->getMessage()); }
    }
}