<?php
// app/Controllers/Reinf/ReinfController1000.php

require_once __DIR__ . '/ReinfBaseController.php';
require_once __DIR__ . '/../../Services/Reinf/R1000Builder.php';

class ReinfController1000 extends ReinfBaseController {

    public function enviarEvento() {
        try {
            $dadosOrgao = [
                'tpInsc' => 1, 
                'nrInsc' => '00860058000105', 
                'cnpj' => '00860058000105',
                'ambiente' => 2 
            ];

            $builder = new R1000Builder();
            $signer = new ReinfSigner($this->reinfConfig);

            // 1. Gera XML R-1000
            $resultadoBuild = $builder->build($dadosOrgao);
            
            // 2. Assina
            $xmlAssinado = $signer->sign($resultadoBuild['xml'], 'evtInfoContri');

            // 3. Empacota
            $xmlLote = $this->montarEnvelopeLote($dadosOrgao['cnpj'], [$xmlAssinado]);

            // 4. Envia
            $client = new ReinfClient($this->reinfConfig);
            $retorno = $client->enviarLote($xmlLote);

            // 5. Verifica Retorno
            $domRetorno = new DOMDocument();
            $domRetorno->loadXML($retorno['response']);
            $protocolo = $domRetorno->getElementsByTagName('protocoloEnvio')->item(0)->nodeValue ?? null;

            if ($protocolo) {
                $this->jsonResponse([
                    'success' => true, 
                    'message' => 'R-1000 Enviado com Sucesso! Protocolo: ' . $protocolo
                ]);
            } else {
                throw new Exception('Erro API R-1000: ' . strip_tags($retorno['response']));
            }

        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
}