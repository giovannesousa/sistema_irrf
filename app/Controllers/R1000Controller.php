<?php
// app/Controllers/R1000Controller.php

require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Core/Session.php';
require_once __DIR__ . '/../Services/Reinf/ReinfConfig.php';
require_once __DIR__ . '/../Services/Reinf/R1000Builder.php';
require_once __DIR__ . '/../Services/Reinf/ReinfSigner.php';
require_once __DIR__ . '/../Services/Reinf/ReinfClient.php';

class R1000Controller {
    private $db;
    private $reinfConfig;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        
        // Verifica sessão e obtém ID do órgão
        $idOrgao = $_SESSION['usuario']['id_orgao'] ?? null;
        
        if (!$idOrgao) {
            $this->jsonError("Sessão inválida ou órgão não identificado.");
        }

        // Busca configurações do certificado do órgão no banco
        $stmt = $this->db->prepare("SELECT certificado_arquivo, certificado_senha FROM orgaos WHERE id = ?");
        $stmt->execute([$idOrgao]);
        $configOrgao = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$configOrgao || empty($configOrgao['certificado_arquivo'])) {
            // Se não tiver certificado configurado, não inicializa config (será tratado nos métodos)
            $this->reinfConfig = null;
        } else {
            $certPath = __DIR__ . '/../../certificados/' . $configOrgao['certificado_arquivo'];
            $certPass = $configOrgao['certificado_senha'];
            $this->reinfConfig = new ReinfConfig($certPath, $certPass, 2); // 2 = Pre-Prod
        }
    }

    public function verificarStatus() {
        try {
            $idOrgao = $_SESSION['usuario']['id_orgao'] ?? null;
            if (!$idOrgao) throw new Exception("Órgão não identificado na sessão.");
            
            $sql = "SELECT r1000_enviado, r1000_recibo, r1000_data_envio, 
                           classificacao_tributaria, contato_nome, contato_cpf, contato_email, contato_telefone,
                           indicador_ecd, indicador_desoneracao, cnpj, nome_oficial,
                           ide_efr, cnpj_efr
                    FROM orgaos WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $idOrgao]);
            $dados = $stmt->fetch(PDO::FETCH_ASSOC);

            // Busca histórico de envios
            $sqlHist = "SELECT * FROM reinf_r1000 WHERE id_orgao = :id ORDER BY created_at DESC LIMIT 20";
            $stmtHist = $this->db->prepare($sqlHist);
            $stmtHist->execute([':id' => $idOrgao]);
            $historico = $stmtHist->fetchAll(PDO::FETCH_ASSOC);

            $this->jsonResponse([
                'success' => true,
                'tem_cadastro' => !empty($dados['r1000_recibo']),
                'dados' => $dados,
                'historico' => $historico
            ]);
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }

    public function salvarDados() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $idOrgao = $_SESSION['usuario']['id_orgao'] ?? null;
            if (!$idOrgao) throw new Exception("Órgão não identificado na sessão.");

            $sql = "UPDATE orgaos SET 
                    classificacao_tributaria = :class_trib,
                    indicador_ecd = :ind_ecd,
                    indicador_desoneracao = :ind_deson,
                    contato_nome = :nome,
                    contato_cpf = :cpf,
                    contato_telefone = :tel,
                    contato_email = :email,
                    ide_efr = :ide_efr,
                    cnpj_efr = :cnpj_efr,
                    updated_at = NOW()
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':class_trib' => $input['classificacao_tributaria'],
                ':ind_ecd' => $input['indicador_ecd'],
                ':ind_deson' => $input['indicador_desoneracao'],
                ':nome' => $input['contato_nome'],
                ':cpf' => preg_replace('/[^0-9]/', '', $input['contato_cpf']),
                ':tel' => preg_replace('/[^0-9]/', '', $input['contato_telefone']),
                ':email' => $input['contato_email'],
                ':ide_efr' => $input['ide_efr'] ?? 'S',
                ':cnpj_efr' => !empty($input['cnpj_efr']) ? preg_replace('/[^0-9]/', '', $input['cnpj_efr']) : null,
                ':id' => $idOrgao
            ]);

            $this->jsonResponse(['success' => true, 'message' => 'Dados salvos com sucesso.']);
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }

    public function validarXml() {
        try {
            if (!$this->reinfConfig) throw new Exception("Certificado digital não configurado para este órgão.");
            
            $idOrgao = $_SESSION['usuario']['id_orgao'] ?? null;
            if (!$idOrgao) throw new Exception("Órgão não identificado.");

            $dadosOrgao = $this->getDadosOrgao($idOrgao);
            $dadosOrgao['ambiente'] = 2; // Pre-prod

            $builder = new R1000Builder();
            $signer = new ReinfSigner($this->reinfConfig);

            // Gera XML
            $resultadoBuild = $builder->build($dadosOrgao, 'inclusao');
            
            // Assina
            $xmlAssinado = $signer->sign($resultadoBuild['xml'], 'evtInfoContri');

            // Valida XSD
            $xsdPath = __DIR__ . '/../Schemas/R-1000-evtInfoContribuinte-v2_01_02f.xsd';
            if (!file_exists($xsdPath)) {
                throw new Exception("Arquivo XSD R-1000 não encontrado em: $xsdPath");
            }

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
                    if (strpos($msg, 'Signature') !== false) continue;
                    $listaErros[] = "Linha {$error->line}: {$msg}";
                }
                libxml_clear_errors();
                
                if (!empty($listaErros)) {
                    $this->jsonResponse(['success' => false, 'erros' => $listaErros]);
                }
            }

            $this->jsonResponse(['success' => true, 'message' => 'XML Válido e Assinado com sucesso!']);

        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }

    public function enviarEvento() {
        try {
            if (!$this->reinfConfig) throw new Exception("Certificado digital não configurado para este órgão.");

            $idOrgao = $_SESSION['usuario']['id_orgao'] ?? null;
            if (!$idOrgao) throw new Exception("Órgão não identificado.");

            $dadosOrgao = $this->getDadosOrgao($idOrgao);
            $dadosOrgao['ambiente'] = 2;

            // 1. Gera e Assina
            $builder = new R1000Builder();
            $signer = new ReinfSigner($this->reinfConfig);
            $resultadoBuild = $builder->build($dadosOrgao, 'inclusao');
            $xmlAssinado = $signer->sign($resultadoBuild['xml'], 'evtInfoContri');

            // 2. Monta Lote (Usando método local)
            $xmlLote = $this->montarEnvelopeLote($dadosOrgao['cnpj'], [$xmlAssinado]);

            // 3. Envia
            $client = new ReinfClient($this->reinfConfig);
            $retorno = $client->enviarLote($xmlLote);

            // 4. Processa Retorno Inicial (Protocolo)
            $domRetorno = new DOMDocument();
            $domRetorno->loadXML($retorno['response']);
            $protocolo = $domRetorno->getElementsByTagName('protocoloEnvio')->item(0)->nodeValue ?? null;

            if (!$protocolo) {
                throw new Exception("Erro no envio: " . strip_tags(substr($retorno['response'], 0, 500)));
            }

            // 5. Salva na tabela de lotes
            $sqlLote = "INSERT INTO reinf_lotes (id_orgao, protocolo, xml_envio, xml_retorno, status) VALUES (?, ?, ?, ?, 'enviado')";
            $stmtLote = $this->db->prepare($sqlLote);
            $stmtLote->execute([$idOrgao, $protocolo, $xmlLote, $retorno['response']]);
            $idLote = $this->db->lastInsertId();

            // 6. Salva no Histórico (reinf_r1000) com vínculo ao lote
            $sqlIns = "INSERT INTO reinf_r1000 (id_orgao, id_lote, id_evento_xml, status, xml_assinado, dados_contribuinte) VALUES (?, ?, ?, 'em_lote', ?, ?)";
            $stmtIns = $this->db->prepare($sqlIns);
            $stmtIns->execute([$idOrgao, $idLote, $resultadoBuild['id'], $xmlAssinado, json_encode($dadosOrgao)]);

            $this->jsonResponse(['success' => true, 'protocolo' => $protocolo, 'id_lote' => $idLote]);

        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }

    public function excluirEvento() {
        try {
            if (!$this->reinfConfig) throw new Exception("Certificado digital não configurado para este órgão.");

            $idOrgao = $_SESSION['usuario']['id_orgao'] ?? null;
            if (!$idOrgao) throw new Exception("Órgão não identificado.");

            $dadosOrgao = $this->getDadosOrgao($idOrgao);
            $dadosOrgao['ambiente'] = 2;

            // 1. Gera XML de Exclusão
            $builder = new R1000Builder();
            $signer = new ReinfSigner($this->reinfConfig);
            
            // Usa o período atual ou o que está salvo no banco (idealmente deveria vir do input)
            $periodo = date('Y-m'); 
            $resultadoBuild = $builder->build($dadosOrgao, 'exclusao', $periodo);
            
            // Assina
            $xmlAssinado = $signer->sign($resultadoBuild['xml'], 'evtInfoContri');

            // 2. Monta Lote
            $xmlLote = $this->montarEnvelopeLote($dadosOrgao['cnpj'], [$xmlAssinado]);

            // 3. Envia
            $client = new ReinfClient($this->reinfConfig);
            $retorno = $client->enviarLote($xmlLote);

            // 4. Processa Retorno
            $domRetorno = new DOMDocument();
            $domRetorno->loadXML($retorno['response']);
            $protocolo = $domRetorno->getElementsByTagName('protocoloEnvio')->item(0)->nodeValue ?? null;

            if (!$protocolo) throw new Exception("Erro no envio: " . strip_tags($retorno['response']));

            // 5. Salva Lote e Histórico
            $sqlLote = "INSERT INTO reinf_lotes (id_orgao, protocolo, xml_envio, xml_retorno, status) VALUES (?, ?, ?, ?, 'enviado')";
            $stmtLote = $this->db->prepare($sqlLote);
            $stmtLote->execute([$idOrgao, $protocolo, $xmlLote, $retorno['response']]);
            $idLote = $this->db->lastInsertId();

            $sqlIns = "INSERT INTO reinf_r1000 (id_orgao, id_lote, id_evento_xml, status, xml_assinado, dados_contribuinte) VALUES (?, ?, ?, 'em_lote', ?, ?)";
            $stmtIns = $this->db->prepare($sqlIns);
            $stmtIns->execute([$idOrgao, $idLote, $resultadoBuild['id'], $xmlAssinado, json_encode(['acao' => 'exclusao'])]);

            $this->jsonResponse(['success' => true, 'protocolo' => $protocolo, 'id_lote' => $idLote, 'message' => 'Solicitação de exclusão enviada! Consulte o status em instantes.']);

        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }

    public function consultarLoteR1000() {
        try {
            if (!$this->reinfConfig) throw new Exception("Certificado digital não configurado para este órgão.");

            $idLote = $_POST['id_lote'] ?? null;
            if (!$idLote) throw new Exception("ID do lote não informado.");

            // Busca protocolo
            $sql = "SELECT * FROM reinf_lotes WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $idLote]);
            $lote = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$lote || empty($lote['protocolo'])) throw new Exception("Lote sem protocolo.");

            // Consulta API
            $client = new ReinfClient($this->reinfConfig);
            $retornoApi = $client->consultarLote($lote['protocolo']);

            if ($retornoApi['code'] != 200) {
                throw new Exception("Erro API: " . strip_tags(substr($retornoApi['response'], 0, 300)));
            }

            // Processa XML Retorno
            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadXML($retornoApi['response']);
            libxml_clear_errors();
            $xpath = new DOMXPath($dom);

            // Verifica Status do Lote (cdResposta ou cdStatus)
            $statusNode = $xpath->query("//*[local-name()='cdResposta']")->item(0);
            if (!$statusNode) {
                $statusNode = $xpath->query("//*[local-name()='cdStatus']")->item(0);
            }
            $cdStatus = $statusNode ? $statusNode->nodeValue : '0';

            if ($cdStatus == '2' || $cdStatus == '4') { // Sucesso Total
                // Busca o evento dentro do retorno
                $eventoNode = $xpath->query("//*[local-name()='retornoEventos']/*[local-name()='evento']")->item(0);
                if ($eventoNode) {
                    // Pega ID do evento para update seguro
                    $idEventoXml = $eventoNode->getAttribute('Id');
                    if (!$idEventoXml) $idEventoXml = $eventoNode->getAttribute('id');

                    $nodeRecibo = $xpath->query(".//*[local-name()='nrRecibo']", $eventoNode)->item(0);
                    if (!$nodeRecibo) {
                        $nodeRecibo = $xpath->query(".//*[local-name()='nrRecArqBase']", $eventoNode)->item(0);
                    }
                    $nrRecibo = $nodeRecibo ? $nodeRecibo->nodeValue : null;

                    if ($nrRecibo) {
                        // Atualiza tabela orgaos (Cadastro Oficial)
                        // Verifica se foi uma exclusão para limpar os dados
                        $stmtCheckExclusao = $this->db->prepare("SELECT xml_assinado FROM reinf_r1000 WHERE id_evento_xml = ?");
                        $stmtCheckExclusao->execute([$idEventoXml]);
                        $xmlEvento = $stmtCheckExclusao->fetchColumn();

                        if ($xmlEvento && strpos($xmlEvento, '<exclusao>') !== false) {
                            $sqlUpdateOrgao = "UPDATE orgaos SET r1000_enviado = 0, r1000_recibo = NULL, r1000_data_envio = NULL WHERE id = ?";
                            $this->db->prepare($sqlUpdateOrgao)->execute([$lote['id_orgao']]);

                            // Limpa eventos periódicos locais (R-4020, etc) para refletir o reset na Receita
                            $sqlLimpa = "UPDATE reinf_eventos e 
                                         INNER JOIN reinf_lotes l ON e.id_lote = l.id 
                                         SET e.status = 'excluido' 
                                         WHERE l.id_orgao = ? AND e.status = 'sucesso'";
                            $this->db->prepare($sqlLimpa)->execute([$lote['id_orgao']]);
                        } else {
                            $sqlUpdateOrgao = "UPDATE orgaos SET r1000_enviado = 1, r1000_recibo = ?, r1000_data_envio = NOW() WHERE id = ?";
                            $this->db->prepare($sqlUpdateOrgao)->execute([$nrRecibo, $lote['id_orgao']]);
                        }

                        // Atualiza histórico R1000 (Tenta pelo ID do evento, fallback para o último do órgão)
                        if ($idEventoXml) {
                            $sqlUpdateHist = "UPDATE reinf_r1000 SET status = 'sucesso', numero_recibo = ? WHERE id_evento_xml = ?";
                            $this->db->prepare($sqlUpdateHist)->execute([$nrRecibo, $idEventoXml]);
                        } else {
                            $sqlUpdateHist = "UPDATE reinf_r1000 SET status = 'sucesso', numero_recibo = ? WHERE id_orgao = ? AND status = 'em_lote' ORDER BY id DESC LIMIT 1";
                            $this->db->prepare($sqlUpdateHist)->execute([$nrRecibo, $lote['id_orgao']]);
                        }

                        // Atualiza Lote
                        $this->db->prepare("UPDATE reinf_lotes SET status = 'processado', xml_retorno = ? WHERE id = ?")->execute([$retornoApi['response'], $idLote]);

                        $this->jsonResponse(['success' => true, 'status' => 'sucesso', 'recibo' => $nrRecibo, 'mensagem' => 'Cadastro realizado com sucesso!']);
                    }
                }
            } elseif ($cdStatus == '3' || $cdStatus == '5') { // Erro
                // Tenta pegar mensagem de erro
                $msgErro = "Erro no processamento.";
                $nodeDesc = $xpath->query("//*[local-name()='descResposta']")->item(0); // Erro do lote
                if ($nodeDesc) $msgErro = $nodeDesc->nodeValue;

                // Erro do evento específico
                $nodeDscResp = $xpath->query("//*[local-name()='dscResp']")->item(0);
                if ($nodeDscResp) $msgErro = $nodeDscResp->nodeValue;

                $this->db->prepare("UPDATE reinf_lotes SET status = 'erro', xml_retorno = ? WHERE id = ?")->execute([$retornoApi['response'], $idLote]);
                
                // Tenta vincular erro ao evento específico
                $eventoNode = $xpath->query("//*[local-name()='retornoEventos']/*[local-name()='evento']")->item(0);
                $idEventoXml = $eventoNode ? ($eventoNode->getAttribute('Id') ?: $eventoNode->getAttribute('id')) : null;

                if ($idEventoXml) {
                    $this->db->prepare("UPDATE reinf_r1000 SET status = 'rejeitado', mensagem_erro = ? WHERE id_evento_xml = ?")->execute([$msgErro, $idEventoXml]);
                } else {
                    $this->db->prepare("UPDATE reinf_r1000 SET status = 'rejeitado', mensagem_erro = ? WHERE id_orgao = ? AND status = 'em_lote' ORDER BY id DESC LIMIT 1")->execute([$msgErro, $lote['id_orgao']]);
                }

                $this->jsonResponse(['success' => true, 'status' => 'erro', 'mensagem' => $msgErro]);
            } else {
                // Status 1 ou 2 (Processando) - Atualiza o banco para refletir que foi consultado
                $this->db->prepare("UPDATE reinf_lotes SET status = 'processando', xml_retorno = ? WHERE id = ?")->execute([$retornoApi['response'], $idLote]);
                
                $this->jsonResponse(['success' => true, 'status' => 'processando', 'mensagem' => 'Aguardando processamento da Receita... (Cód: ' . $cdStatus . ')']);
            }

        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }

    private function getDadosOrgao($id) {
        $stmt = $this->db->prepare("SELECT * FROM orgaos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function montarEnvelopeLote($cnpjOrgao, $xmlsAssinados) {
        $nsLote = 'http://www.reinf.esocial.gov.br/schemas/envioLoteEventosAssincrono/v1_00_00';
        
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
            preg_match('/id="([^"]+)"/i', $xmlAssinado, $matches);
            $idEvt = $matches[1] ?? '';
            if (empty($idEvt)) continue;

            $eventoLimpo = preg_replace('/<\?xml.*?\?>/', '', $xmlAssinado);
            $eventoLimpo = trim($eventoLimpo);

            if (strpos($eventoLimpo, '<Signature>') !== false) {
                $eventoLimpo = str_replace('<Signature>', '<Signature xmlns="http://www.w3.org/2000/09/xmldsig#">', $eventoLimpo);
            }

            $xml .= '<evento Id="' . $idEvt . '">';
            $xml .= $eventoLimpo;
            $xml .= '</evento>';
        }

        $xml .= '</eventos>';
        $xml .= '</envioLoteEventos>';
        $xml .= '</Reinf>';

        return $xml;
    }

    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    private function jsonError($msg) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $msg]);
        exit;
    }
}