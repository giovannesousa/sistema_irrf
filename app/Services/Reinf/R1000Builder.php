<?php
// app/Services/Reinf/R1000Builder.php

class R1000Builder {

    public function build(array $dadosOrgao) {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;

        $ns = 'http://www.reinf.esocial.gov.br/schemas/evtInfoContribuinte/v2_01_02';
        
        $root = $dom->createElementNS($ns, 'Reinf');
        $dom->appendChild($root);

        $evt = $dom->createElementNS($ns, 'evtInfoContri');
        // Força o namespace para evitar erros de assinatura
        $evt->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns', $ns);
        
        // ID: ID + tpInsc(1) + CNPJ_Raiz(8)+Zeros(6) + Data(14) + Random(5)
        $cnpjLimpo = preg_replace('/[^0-9]/', '', $dadosOrgao['nrInsc']);
        $cnpjRaiz = substr($cnpjLimpo, 0, 8); 
        $cnpjParaId = str_pad($cnpjRaiz, 14, '0', STR_PAD_RIGHT);

        $idEvento = 'ID' . $dadosOrgao['tpInsc'] . $cnpjParaId . date('YmdHis') . mt_rand(10000, 99999);
        $evt->setAttribute('id', $idEvento);
        $root->appendChild($evt);

        // ideEvento
        $ideEvento = $dom->createElementNS($ns, 'ideEvento');
        $ideEvento->appendChild($dom->createElementNS($ns, 'tpAmb', $dadosOrgao['ambiente']));
        $ideEvento->appendChild($dom->createElementNS($ns, 'procEmi', '1'));
        $ideEvento->appendChild($dom->createElementNS($ns, 'verProc', '1.0'));
        $evt->appendChild($ideEvento);

        // ideContri
        $ideContri = $dom->createElementNS($ns, 'ideContri');
        $ideContri->appendChild($dom->createElementNS($ns, 'tpInsc', $dadosOrgao['tpInsc']));
        $ideContri->appendChild($dom->createElementNS($ns, 'nrInsc', substr($cnpjLimpo, 0, 8))); // Apenas Raiz
        $evt->appendChild($ideContri);

        // infoContri -> inclusao
        $infoContri = $dom->createElementNS($ns, 'infoContri');
        $inclusao = $dom->createElementNS($ns, 'inclusao');
        
        // idePeriodo
        $idePeriodo = $dom->createElementNS($ns, 'idePeriodo');
        // Define inicio da validade para Janeiro deste ano
        $iniValid = date('Y') . '-01'; 
        $idePeriodo->appendChild($dom->createElementNS($ns, 'iniValid', $iniValid));
        $inclusao->appendChild($idePeriodo);

        // infoCadastro
        $infoCadastro = $dom->createElementNS($ns, 'infoCadastro');
        
        // 99 = Pessoa Jurídica em Geral (Lucro Real/Presumido)
        $infoCadastro->appendChild($dom->createElementNS($ns, 'classTrib', '99')); 
        $infoCadastro->appendChild($dom->createElementNS($ns, 'indEscrituracao', '0')); // 0 = Não obrigado a ECD imediata
        $infoCadastro->appendChild($dom->createElementNS($ns, 'indDesoneracao', '0')); // 0 = Não aplicável
        $infoCadastro->appendChild($dom->createElementNS($ns, 'indAcordoIsenMulta', '0')); // 0 = Não

        // Contato (Obrigatório)
        $contato = $dom->createElementNS($ns, 'contato');
        $contato->appendChild($dom->createElementNS($ns, 'nmCtt', 'Responsavel Fiscal'));
        $contato->appendChild($dom->createElementNS($ns, 'cpfCtt', '12345678901')); // CPF Responsável
        $contato->appendChild($dom->createElementNS($ns, 'foneFixo', '11999999999'));
        $contato->appendChild($dom->createElementNS($ns, 'email', 'fiscal@empresa.com.br'));
        
        $infoCadastro->appendChild($contato);
        $inclusao->appendChild($infoCadastro);
        
        $infoContri->appendChild($inclusao);
        $evt->appendChild($infoContri);

        return [
            'xml' => $dom->saveXML(),
            'id' => $idEvento
        ];
    }
}