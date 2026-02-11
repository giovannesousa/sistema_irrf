<?php
// app/Services/Reinf/ReinfClient.php

class ReinfClient {
    private $config;

    public function __construct(ReinfConfig $config) {
        $this->config = $config;
    }

    public function enviarLote($xmlLoteAssinado) {
        // URL CONFIRMADA PELO SEU TESTE (NÃO MUDE)
        $url = $this->config->getBaseUrl() . '/recepcao/lotes';
        return $this->executarCurl($url, $xmlLoteAssinado);
    }

    public function consultarLote($protocolo) {
        // URL DE CONSULTA
        $url = $this->config->getBaseUrl() . "/consulta/lotes/{$protocolo}";
        return $this->executarCurl($url, null, 'GET');
    }

    private function executarCurl($url, $postData = null, $method = 'POST') {
        $ch = curl_init();
        $options = $this->config->getCertOptions();
        
        // Configurações Básicas
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // SSL e Certificado
        curl_setopt($ch, CURLOPT_SSLCERT, $options['cert']);
        curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $options['pass']);
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'P12');
        
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/xml; charset=UTF-8"
            ]);
        } else {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        
        curl_close($ch);

        if ($curlError) {
            return ['code' => 0, 'response' => "Erro cURL: $curlError"];
        }

        return ['code' => $httpCode, 'response' => $response];
    }
}