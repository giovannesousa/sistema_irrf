<?php
// app/Services/Reinf/ReinfSigner.php

class ReinfSigner {
    private $config;

    public function __construct(ReinfConfig $config) {
        $this->config = $config;
    }

    public function sign($xmlContent, $tagName = 'evtRetPJ') {
        $certOptions = $this->config->getCertOptions();
        
        if (!file_exists($certOptions['cert'])) {
            throw new Exception("Certificado não encontrado: " . $certOptions['cert']);
        }

        // 1. Ler o PFX e extrair chaves
        $pfxContent = file_get_contents($certOptions['cert']);
        $certs = [];
        if (!openssl_pkcs12_read($pfxContent, $certs, $certOptions['pass'])) {
            throw new Exception("Erro ao ler PFX. Verifique a senha.");
        }

        $privateKey = $certs['pkey'];
        $publicKey = $certs['cert'];

        // 2. Preparar XML
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->loadXML($xmlContent);

        $root = $dom->getElementsByTagName($tagName)->item(0);
        if (!$root) throw new Exception("Tag $tagName não encontrada.");
        
        $id = $root->getAttribute('id');

        // 3. Canonicalizar e Hash
        $canonicalized = $root->C14N(false, false, null, null);
        $hash = base64_encode(hash('sha256', $canonicalized, true));

        // 4. SignedInfo
        $signedInfo = 
            '<SignedInfo xmlns="http://www.w3.org/2000/09/xmldsig#">' .
                '<CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"></CanonicalizationMethod>' .
                '<SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"></SignatureMethod>' .
                '<Reference URI="#' . $id . '">' .
                    '<Transforms>' .
                        '<Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"></Transform>' .
                        '<Transform Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"></Transform>' .
                    '</Transforms>' .
                    '<DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"></DigestMethod>' .
                    '<DigestValue>' . $hash . '</DigestValue>' .
                '</Reference>' .
            '</SignedInfo>';

        // 5. Assinar
        $signature = '';
        if (!openssl_sign($signedInfo, $signature, $privateKey, "sha256WithRSAEncryption")) {
            throw new Exception("Erro ao gerar assinatura OpenSSL.");
        }
        $signatureValue = base64_encode($signature);

        // 6. Montar nó de Assinatura
        $certClean = str_replace(["-----BEGIN CERTIFICATE-----", "-----END CERTIFICATE-----", "\r", "\n"], "", $publicKey);

        $signatureXML = 
        '<Signature xmlns="http://www.w3.org/2000/09/xmldsig#">' .
            $signedInfo .
            '<SignatureValue>' . $signatureValue . '</SignatureValue>' .
            '<KeyInfo><X509Data><X509Certificate>' . $certClean . '</X509Certificate></X509Data></KeyInfo>' .
        '</Signature>';

        // Inserir no XML
        $signatureFragment = $dom->createDocumentFragment();
        $signatureFragment->appendXML($signatureXML);
        $dom->documentElement->appendChild($signatureFragment);

        return $dom->saveXML();
    }
}