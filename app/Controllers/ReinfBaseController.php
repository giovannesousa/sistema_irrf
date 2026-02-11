<?php
// app/Controllers/Reinf/ReinfBaseController.php

require_once __DIR__ . '/../../Core/Database.php';
require_once __DIR__ . '/../../Services/Reinf/ReinfConfig.php';
require_once __DIR__ . '/../../Services/Reinf/ReinfSigner.php';
require_once __DIR__ . '/../../Services/Reinf/ReinfClient.php';

class ReinfBaseController {
    // Mudamos de private para protected para que os filhos (4020 e 1000) possam usar
    protected $db;
    protected $reinfConfig;

    public function __construct() {
        // Lógica de conexão original preservada
        $database = new Database();
        $this->db = $database->getConnection();
        
        // Configurações originais preservadas
        $certPath = __DIR__ . '/../../certificados/certificado.pfx'; 
        $certPass = '123456'; 
        $ambiente = 2; 
        
        $this->reinfConfig = new ReinfConfig($certPath, $certPass, $ambiente);
    }

    protected function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function jsonError($msg) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $msg]);
        exit;
    }

    /**
     * MANTIDA EXATAMENTE COMO A VERSÃO QUE FUNCIONOU (STRING MANUAL)
     */
    protected function montarEnvelopeLote($cnpjOrgao, $xmlsAssinados) {
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

            // Injeção de namespace da assinatura (Correção MS0030)
            if (strpos($eventoLimpo, '<Signature>') !== false) {
                $eventoLimpo = str_replace(
                    '<Signature>', 
                    '<Signature xmlns="http://www.w3.org/2000/09/xmldsig#">', 
                    $eventoLimpo
                );
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
}