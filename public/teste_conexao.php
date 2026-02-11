<?php
// public/teste_conexao.php
header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnóstico de Conexão EFD-Reinf</h1>";

// 1. Configurações (AJUSTE AQUI SE NECESSÁRIO)
// -------------------------------------------------------
$senhaCertificado = '123456'; // <--- COLOCAR A SENHA AQUI
$nomeArquivoPfx = 'certificado.pfx';
// -------------------------------------------------------

// 2. Verificação do Arquivo
$caminhoCert = realpath(__DIR__ . '/../certificados/' . $nomeArquivoPfx);

echo "<h3>1. Verificação do Arquivo</h3>";
echo "Procurando em: " . $caminhoCert . "<br>";

if (file_exists($caminhoCert)) {
    echo "<span style='color:green'>[OK] Arquivo encontrado.</span><br>";
} else {
    echo "<span style='color:red'>[ERRO] Arquivo não encontrado! Verifique o nome e se está na pasta 'certificados'.</span>";
    exit;
}

// 3. Teste de Leitura do PFX (Senha)
echo "<h3>2. Teste de Leitura do Certificado (Senha)</h3>";
$pfxContent = file_get_contents($caminhoCert);
$certs = [];
if (openssl_pkcs12_read($pfxContent, $certs, $senhaCertificado)) {
    echo "<span style='color:green'>[OK] Senha correta. Certificado lido com sucesso.</span><br>";
    $dadosCert = openssl_x509_parse($certs['cert']);
    echo "Titular: " . $dadosCert['subject']['CN'] . "<br>";
    echo "Válido até: " . date('d/m/Y', $dadosCert['validTo_time_t']) . "<br>";
} else {
    echo "<span style='color:red'>[ERRO] Não foi possível ler o certificado. Senha incorreta ou formato inválido.</span><br>";
    echo "Erro OpenSSL: "; 
    while ($msg = openssl_error_string()) echo $msg . "<br>";
    exit;
}

// 4. Teste de Conexão cURL
echo "<h3>3. Teste de Conexão com a Receita (Pre-Prod)</h3>";
$url = 'https://pre-reinf.receita.economia.gov.br/recepcao/lotes';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, '<dummy>Teste</dummy>'); // Corpo inválido proposital
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/xml"]);

// Configuração PFX para cURL
curl_setopt($ch, CURLOPT_SSLCERT, $caminhoCert);
curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $senhaCertificado);
curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'P12'); // Importante para .pfx

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ignora erro de SSL local
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo "<span style='color:red'>[ERRO DE CONEXÃO]</span><br>";
    echo "cURL Error: " . $curlError . "<br>";
    
    if (strpos($curlError, 'could not load PEM') !== false) {
        echo "<b>Dica:</b> O cURL no Windows as vezes falha com PFX direto. Pode ser necessário converter para PEM.";
    }
} else {
    echo "<span style='color:blue'>[RESPOSTA DA RECEITA]</span><br>";
    echo "HTTP Code: <b>" . $httpCode . "</b> (Esperado: 400 ou 422, pois enviamos XML falso)<br>";
    echo "Se o código for 0, houve bloqueio. Se for > 0, conectou!<br>";
    echo "Resposta bruta: <textarea style='width:100%; height:100px'>" . htmlspecialchars($response) . "</textarea>";
}
?>