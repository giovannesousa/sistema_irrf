<?php
// app/Services/Reinf/R9000Builder.php

class R9000Builder {

    public function build(array $dadosOrgao, string $tipoEventoOriginal, string $nrReciboOriginal, string $periodoApuracao) {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;

        // Namespace do evento de exclusão (v2.01.02)
        $ns = 'http://www.reinf.esocial.gov.br/schemas/evtExclusao/v2_01_02';
        
        // 1. Cria a Raiz (Reinf)
        $root = $dom->createElementNS($ns, 'Reinf');
        $dom->appendChild($root);

        // 2. Cria o Evento (evtExclusao)
        $evt = $dom->createElementNS($ns, 'evtExclusao');
        $evt->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns', $ns);
        
        // Monta ID único
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
        $ideContri->appendChild($dom->createElementNS($ns, 'nrInsc', substr($cnpjLimpo, 0, 8))); 
        $evt->appendChild($ideContri);

        // infoExclusao
        $infoExclusao = $dom->createElementNS($ns, 'infoExclusao');
        $infoExclusao->appendChild($dom->createElementNS($ns, 'tpEvento', $tipoEventoOriginal)); // Ex: R-4020
        $infoExclusao->appendChild($dom->createElementNS($ns, 'nrRecEvt', $nrReciboOriginal));
        $infoExclusao->appendChild($dom->createElementNS($ns, 'perApur', $periodoApuracao));
        $evt->appendChild($infoExclusao);

        return [
            'xml' => $dom->saveXML(),
            'id' => $idEvento
        ];
    }
}
