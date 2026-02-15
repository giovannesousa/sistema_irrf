<?php
// app/Services/Reinf/R4099Builder.php

class R4099Builder {

    public function build(array $dadosOrgao, string $periodo, array $responsavel, int $acao = 1) {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;

        $ns = 'http://www.reinf.esocial.gov.br/schemas/evt4099FechamentoDirf/v2_01_02';
        
        // 1. Cria a Raiz (Reinf)
        $root = $dom->createElementNS($ns, 'Reinf');
        $dom->appendChild($root);

        // 2. Cria o Evento (evtFech)
        $evt = $dom->createElementNS($ns, 'evtFech');
        $evt->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns', $ns);
        
        // Monta ID único: ID + tpInsc(1) + CNPJ(14) + Timestamp(14) + Random(5)
        $cnpjLimpo = preg_replace('/[^0-9]/', '', $dadosOrgao['nrInsc']);
        $cnpjRaiz = substr($cnpjLimpo, 0, 8);
        $cnpjParaId = str_pad($cnpjRaiz, 14, '0', STR_PAD_RIGHT);
        $idEvento = 'ID' . $dadosOrgao['tpInsc'] . $cnpjParaId . date('YmdHis') . mt_rand(10000, 99999);
        
        $evt->setAttribute('id', $idEvento);
        $root->appendChild($evt);

        // ideEvento
        $ideEvento = $dom->createElementNS($ns, 'ideEvento');
        $ideEvento->appendChild($dom->createElementNS($ns, 'perApur', $periodo));
        $ideEvento->appendChild($dom->createElementNS($ns, 'tpAmb', $dadosOrgao['ambiente']));
        $ideEvento->appendChild($dom->createElementNS($ns, 'procEmi', '1')); 
        $ideEvento->appendChild($dom->createElementNS($ns, 'verProc', '1.0'));
        $evt->appendChild($ideEvento);

        // ideContri
        $ideContri = $dom->createElementNS($ns, 'ideContri');
        $ideContri->appendChild($dom->createElementNS($ns, 'tpInsc', $dadosOrgao['tpInsc']));
        $ideContri->appendChild($dom->createElementNS($ns, 'nrInsc', substr($cnpjLimpo, 0, 8))); 
        $evt->appendChild($ideContri);

        // ideRespInf (Responsável pelas informações)
        $ideRespInf = $dom->createElementNS($ns, 'ideRespInf');
        $ideRespInf->appendChild($dom->createElementNS($ns, 'nmResp', mb_substr($responsavel['nome'], 0, 70)));
        $ideRespInf->appendChild($dom->createElementNS($ns, 'cpfResp', preg_replace('/[^0-9]/', '', $responsavel['cpf'])));
        
        if (!empty($responsavel['telefone'])) {
            $ideRespInf->appendChild($dom->createElementNS($ns, 'telefone', preg_replace('/[^0-9]/', '', $responsavel['telefone'])));
        }
        if (!empty($responsavel['email'])) {
            $ideRespInf->appendChild($dom->createElementNS($ns, 'email', mb_substr($responsavel['email'], 0, 60)));
        }
        $evt->appendChild($ideRespInf);

        // infoFech
        $infoFech = $dom->createElementNS($ns, 'infoFech');
        // fechRet: 0 = Fechamento, 1 = Reabertura (conforme Manual R-4099)
        $infoFech->appendChild($dom->createElementNS($ns, 'fechRet', (string)$acao));
        $evt->appendChild($infoFech);

        return [
            'xml' => $dom->saveXML(),
            'id' => $idEvento
        ];
    }
}