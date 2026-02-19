<?php
class ReinfConfig {
    // URL base definida no manual [cite: 266]
    const URL_PRE_PROD = 'https://pre-reinf.receita.economia.gov.br';
    const URL_PROD = 'https://reinf.receita.economia.gov.br';

    private $certPath;
    private $certPass;
    private $ambiente; // 2 = Produção Restrita

    public function __construct($certPath, $certPass, $ambiente = 1) {
        $this->certPath = $certPath; // Caminho para o .pem ou .pfx
        $this->certPass = $certPass;
        $this->ambiente = $ambiente;
    }

    public function getBaseUrl() {
        return ($this->ambiente == 2) ? self::URL_PRE_PROD : self::URL_PROD;
    }

    public function getCertOptions() {
        // Opções para o cURL e Assinatura
        return [
            'cert' => $this->certPath,
            'pass' => $this->certPass
        ];
    }
}