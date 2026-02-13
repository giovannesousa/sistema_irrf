<?php
// app/Services/Reinf/R1000Builder.php

class R1000Builder {

    public function build(array $dadosOrgao, string $acao = 'inclusao', string $periodo = null) {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;

        $ns = 'http://www.reinf.esocial.gov.br/schemas/evtInfoContribuinte/v2_01_02';
        
        // 1. Cria a Raiz (Reinf)
        $root = $dom->createElementNS($ns, 'Reinf');
        $dom->appendChild($root);

        // 2. Cria o Evento (evtInfoContri)
        $evt = $dom->createElementNS($ns, 'evtInfoContri');
        $evt->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns', $ns);
        
        // ID: ID1 + CNPJ(14) + Timestamp(14) + Random(5)
        $cnpjLimpo = preg_replace('/[^0-9]/', '', $dadosOrgao['cnpj']);
        $cnpjRaiz = substr($cnpjLimpo, 0, 8);
        $cnpjParaId = str_pad($cnpjRaiz, 14, '0', STR_PAD_RIGHT);
        $idEvento = 'ID1' . $cnpjParaId . date('YmdHis') . mt_rand(10000, 99999);
        
        $evt->setAttribute('id', $idEvento);
        $root->appendChild($evt);

        // 3. ideEvento
        $ideEvento = $dom->createElementNS($ns, 'ideEvento');
        $ideEvento->appendChild($dom->createElementNS($ns, 'tpAmb', $dadosOrgao['ambiente'])); // 1-Prod, 2-PreProd
        $ideEvento->appendChild($dom->createElementNS($ns, 'procEmi', '1')); // 1-App do Contribuinte
        $ideEvento->appendChild($dom->createElementNS($ns, 'verProc', '1.0'));
        $evt->appendChild($ideEvento);

        // 4. ideContri
        $ideContri = $dom->createElementNS($ns, 'ideContri');
        $ideContri->appendChild($dom->createElementNS($ns, 'tpInsc', '1')); // 1-CNPJ
        $ideContri->appendChild($dom->createElementNS($ns, 'nrInsc', substr($cnpjLimpo, 0, 8))); // Apenas a Raiz (8 dígitos)
        $evt->appendChild($ideContri);

        // 5. infoContri
        $infoContri = $dom->createElementNS($ns, 'infoContri');
        
        // Bloco de Inclusão, Alteração ou Exclusão
        // Neste exemplo focaremos na Inclusão/Alteração que possuem a mesma estrutura de dados
        $acaoTag = $dom->createElementNS($ns, $acao); // 'inclusao' ou 'alteracao'
        
        // idePeriodo
        $idePeriodo = $dom->createElementNS($ns, 'idePeriodo');
        // Início da validade: Formato AAAA-MM
        $iniValid = $periodo ?? date('Y-m'); 
        $idePeriodo->appendChild($dom->createElementNS($ns, 'iniValid', $iniValid));
        $acaoTag->appendChild($idePeriodo);

        // infoCadastro (Apenas para Inclusão e Alteração)
        if ($acao !== 'exclusao') {
            $infoCadastro = $dom->createElementNS($ns, 'infoCadastro');
            
            // Classificação Tributária (Ex: 99, 03, etc)
            $classTrib = $dadosOrgao['classificacao_tributaria'] ?? '99';
            $infoCadastro->appendChild($dom->createElementNS($ns, 'classTrib', $classTrib));
            
            // Indicador de Escrituração Contábil (0 - Não obrigado, 1 - Obrigado)
            $indEcd = $dadosOrgao['indicador_ecd'] ?? '0';
            $infoCadastro->appendChild($dom->createElementNS($ns, 'indEscrituracao', $indEcd));
            
            // Indicador de Desoneração da Folha (0 - Não aplicável, 1 - Aplicável)
            $indDesoneracao = $dadosOrgao['indicador_desoneracao'] ?? '0';
            $infoCadastro->appendChild($dom->createElementNS($ns, 'indDesoneracao', $indDesoneracao));
            
            // Indicador de Acordo Isenção Multa (0 - Não, 1 - Sim) - Geralmente 0
            $infoCadastro->appendChild($dom->createElementNS($ns, 'indAcordoIsenMulta', '0'));

            // Situação da PJ (0 - Normal) - Obrigatório para CNPJ
            $infoCadastro->appendChild($dom->createElementNS($ns, 'indSitPJ', '0'));

            // Contato
            $contato = $dom->createElementNS($ns, 'contato');
            $contato->appendChild($dom->createElementNS($ns, 'nmCtt', mb_substr($dadosOrgao['contato_nome'], 0, 70)));
            $contato->appendChild($dom->createElementNS($ns, 'cpfCtt', preg_replace('/[^0-9]/', '', $dadosOrgao['contato_cpf'])));
            
            // Telefone (sem formatação)
            $fone = preg_replace('/[^0-9]/', '', $dadosOrgao['contato_telefone']);
            $contato->appendChild($dom->createElementNS($ns, 'foneFixo', $fone));
            
            if (!empty($dadosOrgao['contato_email'])) {
                $contato->appendChild($dom->createElementNS($ns, 'email', mb_substr($dadosOrgao['contato_email'], 0, 60)));
            }
            
            $infoCadastro->appendChild($contato);

            // infoEFR (Obrigatório para Órgãos Públicos - Classificação 03, 04, 80)
            // Se não houver dados específicos, assume 'S' (é o próprio ente) para evitar rejeição
            if (in_array($dadosOrgao['classificacao_tributaria'] ?? '', ['03', '04', '80']) || !empty($dadosOrgao['ide_efr'])) {
                $infoEFR = $dom->createElementNS($ns, 'infoEFR');
                $ideEFR = $dadosOrgao['ide_efr'] ?? 'S';
                $infoEFR->appendChild($dom->createElementNS($ns, 'ideEFR', $ideEFR));
                
                if ($ideEFR === 'N' && !empty($dadosOrgao['cnpj_efr'])) {
                    $cnpjEFR = preg_replace('/[^0-9]/', '', $dadosOrgao['cnpj_efr']);
                    $infoEFR->appendChild($dom->createElementNS($ns, 'cnpjEFR', $cnpjEFR));
                }
                $infoCadastro->appendChild($infoEFR);
            }

            $acaoTag->appendChild($infoCadastro);
        }
        $infoContri->appendChild($acaoTag);
        $evt->appendChild($infoContri);

        return [
            'xml' => $dom->saveXML(),
            'id' => $idEvento
        ];
    }
}