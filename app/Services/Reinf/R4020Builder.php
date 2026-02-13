<?php
// app/Services/Reinf/R4020Builder.php

class R4020Builder {

    public function build(array $dadosOrgao, array $dadosFornecedor, array $listaPagamentos, int $indRetif = 1, ?string $nrRecibo = null) {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;

        $ns = 'http://www.reinf.esocial.gov.br/schemas/evt4020PagtoBeneficiarioPJ/v2_01_02';
        
        // 1. Cria a Raiz (Reinf)
        $root = $dom->createElementNS($ns, 'Reinf');
        $dom->appendChild($root);

        // 2. Cria o Evento (evtRetPJ)
        $evt = $dom->createElementNS($ns, 'evtRetPJ');
        $evt->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns', $ns);
        
        // --- CORREÇÃO DO ID (ERRO MS1010) ---
        // A regra diz: CNPJ com 8 dígitos e completado com zeros até 14.
        // Ex: Se CNPJ é 00860058000105, o ID deve usar 00860058000000
        $cnpjLimpo = preg_replace('/[^0-9]/', '', $dadosOrgao['nrInsc']);
        $cnpjRaiz = substr($cnpjLimpo, 0, 8); // Pega os 8 primeiros
        $cnpjParaId = str_pad($cnpjRaiz, 14, '0', STR_PAD_RIGHT); // Completa com zeros à direita

        // Monta ID: ID(2) + tpInsc(1) + CNPJ_Raiz_Zerado(14) + Data(14) + Random(5) = 36 caracteres
        $idEvento = 'ID' . $dadosOrgao['tpInsc'] . $cnpjParaId . date('YmdHis') . mt_rand(10000, 99999);
        
        $evt->setAttribute('id', $idEvento);
        $root->appendChild($evt);

        // ideEvento
        $ideEvento = $dom->createElementNS($ns, 'ideEvento');
        $ideEvento->appendChild($dom->createElementNS($ns, 'indRetif', (string)$indRetif));
        
        if ($indRetif == 2 && !empty($nrRecibo)) {
            $ideEvento->appendChild($dom->createElementNS($ns, 'nrRecibo', $nrRecibo));
        }

        $ideEvento->appendChild($dom->createElementNS($ns, 'perApur', $listaPagamentos[0]['per_apuracao']));
        $ideEvento->appendChild($dom->createElementNS($ns, 'tpAmb', $dadosOrgao['ambiente']));
        $ideEvento->appendChild($dom->createElementNS($ns, 'procEmi', '1')); 
        $ideEvento->appendChild($dom->createElementNS($ns, 'verProc', '1.0'));
        $evt->appendChild($ideEvento);

        // ideContri
        $ideContri = $dom->createElementNS($ns, 'ideContri');
        $ideContri->appendChild($dom->createElementNS($ns, 'tpInsc', $dadosOrgao['tpInsc']));
        // Aqui no ideContri usa-se apenas a Raiz (8 dígitos), o que está correto
        $ideContri->appendChild($dom->createElementNS($ns, 'nrInsc', substr($cnpjLimpo, 0, 8))); 
        $evt->appendChild($ideContri);

        // ideEstab
        $ideEstab = $dom->createElementNS($ns, 'ideEstab');
        $ideEstab->appendChild($dom->createElementNS($ns, 'tpInscEstab', '1'));
        // Aqui no estabelecimento usa-se o CNPJ completo (Matriz ou Filial)
        $ideEstab->appendChild($dom->createElementNS($ns, 'nrInscEstab', $cnpjLimpo));
        $evt->appendChild($ideEstab);

        // ideBenef
        $ideBenef = $dom->createElementNS($ns, 'ideBenef');
        $cnpjBenef = preg_replace('/[^0-9]/', '', $dadosFornecedor['cnpj']);
        $ideBenef->appendChild($dom->createElementNS($ns, 'cnpjBenef', $cnpjBenef));
        $ideEstab->appendChild($ideBenef);

        // --- INÍCIO DA NOVA LÓGICA DE AGRUPAMENTO ---
        
        // 1. Agrupa os pagamentos pela Natureza de Rendimento
        $pagamentosAgrupados = [];
        foreach ($listaPagamentos as $pgto) {
            // Se vier nulo, assume 17099 (Demais Serviços) como fallback seguro
            $codNat = preg_replace('/[^0-9]/', '', $pgto['nat_rendimento'] ?? '17099');
            $natRend = str_pad($codNat, 5, '0', STR_PAD_LEFT); 
            
            $pagamentosAgrupados[$natRend][] = $pgto;
        }

        // 2. Cria um bloco <idePgto> para CADA natureza diferente encontrada no mês
        foreach ($pagamentosAgrupados as $natRend => $pagamentosDaNatureza) {
            
            $idePgto = $dom->createElementNS($ns, 'idePgto');
            $idePgto->appendChild($dom->createElementNS($ns, 'natRend', $natRend));

            // 3. Adiciona as notas (infoPgto) dentro do seu respectivo grupo
            foreach ($pagamentosDaNatureza as $pgto) {
                $infoPgto = $dom->createElementNS($ns, 'infoPgto');
                
                $infoPgto->appendChild($dom->createElementNS($ns, 'dtFG', $pgto['data_pagamento'])); 
                
                // Valores Monetários
                $vlrBruto = number_format((float)$pgto['valor_bruto'], 2, ',', '');
                $infoPgto->appendChild($dom->createElementNS($ns, 'vlrBruto', $vlrBruto));
                
                // Retenções
                $retencoes = $dom->createElementNS($ns, 'retencoes');
                $vlrBaseIR = number_format((float)$pgto['valor_base_ir'], 2, ',', '');
                $vlrIR = number_format((float)$pgto['valor_ir'], 2, ',', '');

                $retencoes->appendChild($dom->createElementNS($ns, 'vlrBaseIR', $vlrBaseIR));
                $retencoes->appendChild($dom->createElementNS($ns, 'vlrIR', $vlrIR));
                
                $infoPgto->appendChild($retencoes);
                $idePgto->appendChild($infoPgto);
            }

            $ideBenef->appendChild($idePgto);
        }

        return [
            'xml' => $dom->saveXML(),
            'id' => $idEvento
        ];
    }
}