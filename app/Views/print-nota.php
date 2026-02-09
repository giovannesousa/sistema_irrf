<?php
// app/Views/print-nota.php

require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Models/NotaFiscal.php';
require_once __DIR__ . '/../Core/Session.php';

Session::start();

if (!Session::isLoggedIn()) {
    die("Acesso negado. Faça login para imprimir.");
}

$idNota = $_GET['id'] ?? 0;
$usuarioLogado = Session::getUser();
$idOrgao = Session::getIdOrgao() ?? $usuarioLogado['id_orgao'];

if (!$idNota) {
    die("ID da nota não informado.");
}

$notaModel = new NotaFiscal();
$nota = $notaModel->buscarPorId($idNota, $idOrgao);

if (!$nota) {
    die("Nota não encontrada ou você não tem permissão para visualizá-la.");
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impressão de Nota - <?php echo $nota['numero_nota']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #525659; /* Fundo cinza estilo visualizador de PDF */
            font-family: 'Times New Roman', Times, serif;
        }
        .page {
            background: white;
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 20mm;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
            position: relative;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }
        .header h2 { font-size: 18pt; font-weight: bold; margin: 0; text-transform: uppercase; }
        .header h3 { font-size: 14pt; margin: 5px 0; }
        .header p { margin: 0; font-size: 10pt; }
        
        .doc-title {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin: 20px 0;
            background-color: #f0f0f0;
            padding: 5px;
            border: 1px solid #ddd;
        }
        
        .section-box {
            border: 1px solid #000;
            padding: 15px;
            margin-bottom: 20px;
        }
        .section-title {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 10px;
            text-decoration: underline;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            width: 150px;
        }
        .info-value {
            flex: 1;
        }
        
        .table-calc {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table-calc th, .table-calc td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }
        .table-calc th {
            background-color: #f0f0f0;
        }
        
        .signatures {
            margin-top: 80px;
            display: flex;
            justify-content: space-between;
        }
        .signature-line {
            width: 40%;
            border-top: 1px solid #000;
            text-align: center;
            padding-top: 5px;
            font-size: 10pt;
        }
        
        .footer {
            position: absolute;
            bottom: 10mm;
            left: 20mm;
            right: 20mm;
            text-align: center;
            font-size: 9pt;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }

        @media print {
            body { background: none; }
            .page { margin: 0; box-shadow: none; width: auto; height: auto; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="container text-center mt-3 mb-3 no-print">
        <button onclick="window.print()" class="btn btn-primary btn-lg"><i class="bi bi-printer"></i> Imprimir</button>
        <button onclick="window.close()" class="btn btn-secondary btn-lg">Fechar</button>
    </div>

    <div class="page">
        <div class="header">
            <h2><?php echo htmlspecialchars($nota['orgao_nome']); ?></h2>
            <p>CNPJ: <?php echo htmlspecialchars($nota['orgao_cnpj'] ?? '00.000.000/0000-00'); ?></p>
            <p>Documento emitido pelo Sistema de Gestão de IRRF</p>
        </div>

        <div class="doc-title">
            DEMONSTRATIVO DE RETENÇÃO DE IRRF
        </div>

        <div class="section-box">
            <div class="section-title">1. DADOS DO CÁLCULO IRRF</div>
            <div class="info-row">
                <span class="info-label">Número da Nota:</span>
                <span class="info-value"><?php echo htmlspecialchars($nota['numero_nota']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Data de Emissão:</span>
                <span class="info-value"><?php echo date('d/m/Y', strtotime($nota['data_emissao'])); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Situação:</span>
                <span class="info-value"><?php echo strtoupper($nota['status_pagamento']); ?></span>
            </div>
        </div>

        <div class="section-box">
            <div class="section-title">2. DADOS DO PRESTADOR DE SERVIÇO (FORNECEDOR)</div>
            <div class="info-row">
                <span class="info-label">Razão Social:</span>
                <span class="info-value"><?php echo htmlspecialchars($nota['razao_social']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">CNPJ:</span>
                <span class="info-value"><?php echo htmlspecialchars($nota['cnpj']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Regime Tributário:</span>
                <span class="info-value"><?php echo htmlspecialchars($nota['regime_tributario']); ?></span>
            </div>
        </div>

        <div class="section-box">
            <div class="section-title">3. DADOS DO SERVIÇO E CÁLCULO</div>
            <div class="info-row">
                <span class="info-label">Natureza do Serviço:</span>
                <span class="info-value"><?php echo htmlspecialchars($nota['codigo_rfb'] . ' - ' . $nota['natureza_desc']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Descrição:</span>
                <span class="info-value"><?php echo htmlspecialchars($nota['descricao_servico']); ?></span>
            </div>

            <table class="table-calc">
                <thead>
                    <tr>
                        <th>Valor Bruto (R$)</th>
                        <th>Alíquota IRRF (%)</th>
                        <th>Valor Retido (R$)</th>
                        <th>Valor Líquido (R$)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo number_format($nota['valor_bruto'], 2, ',', '.'); ?></td>
                        <td><?php echo number_format($nota['aliquota_aplicada'], 2, ',', '.'); ?>%</td>
                        <td><?php echo number_format($nota['valor_irrf_retido'], 2, ',', '.'); ?></td>
                        <td><strong><?php echo number_format($nota['valor_liquido'], 2, ',', '.'); ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="signatures">
            <div class="signature-line">
                Responsável pelo Cálculo<br>
                <?php echo htmlspecialchars($usuarioLogado['nome']); ?>
            </div>
            <div class="signature-line">
                Responsável Financeiro<br>
                (Assinatura)
            </div>
        </div>

        <div class="footer">
            Impresso em <?php echo date('d/m/Y H:i:s'); ?> - Sistema IRRF
        </div>
    </div>

    <script>
        // Opcional: Imprimir automaticamente ao carregar
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>