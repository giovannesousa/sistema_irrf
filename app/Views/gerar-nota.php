<?php
require_once __DIR__ . '/layout/header.php';
?>
<style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --light-bg: #f8f9fa;
            --border-color: #dee2e6;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .main-container {
            /*max-width: 1200px;*/
            margin: 0 auto;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background: linear-gradient(90deg, var(--primary-color), #0a58ca);
            color: white;
            border-bottom: none;
            padding: 1.2rem 1.5rem;
        }
        
        .card-header h3 {
            margin: 0;
            font-weight: 600;
        }
        
        .form-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 4px solid var(--primary-color);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        .form-section:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        
        .section-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title i {
            font-size: 1.2rem;
        }
        
        .readonly-bg {
            background-color: #f8f9fa !important;
            border-color: #e9ecef;
            cursor: not-allowed;
        }
        
        .result-box {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            border: 2px solid var(--primary-color);
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .result-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
            text-align: center;
            margin: 10px 0;
        }
        
        .alert-custom {
            border-radius: 10px;
            border-left: 4px solid;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .alert-info-custom {
            background-color: #e3f2fd;
            border-color: #2196f3;
            color: #0c5460;
        }
        
        .alert-success-custom {
            background-color: #e8f5e9;
            border-color: #4caf50;
            color: #155724;
        }
        
        .alert-warning-custom {
            background-color: #fff3cd;
            border-color: var(--warning-color);
            color: #856404;
        }
        
        .btn-custom {
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-custom-primary {
            background: linear-gradient(90deg, var(--primary-color), #0a58ca);
            color: white;
        }
        
        .btn-custom-primary:hover {
            background: linear-gradient(90deg, #0a58ca, #084298);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }
        
        .btn-custom-success {
            background: linear-gradient(90deg, var(--success-color), #157347);
            color: white;
        }
        
        .btn-custom-success:hover {
            background: linear-gradient(90deg, #157347, #0d5b3a);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(25, 135, 84, 0.3);
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }
        
        .required-field::after {
            content: " *";
            color: var(--danger-color);
        }
        
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
        
        .badge-regime {
            font-size: 0.85rem;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .badge-simples {
            background-color: #f8d7da;
            color: #721c24;
        }

        .badge-mei {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .badge-presumido {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .badge-real {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .badge-outros {
            background-color: #d4edda;
            color: #155724;
        }
        
        .info-text {
            font-size: 0.9rem;
            color: #6c757d;
            font-style: italic;
            margin-top: 5px;
        }
        
        .tooltip-icon {
            color: var(--primary-color);
            cursor: help;
            margin-left: 5px;
        }
        
        .floating-buttons {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .floating-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        
        .floating-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }
        
        @media (max-width: 768px) {
            .floating-buttons {
                bottom: 20px;
                right: 20px;
            }
            
            .floating-btn {
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
            }
            
            .result-value {
                font-size: 1.5rem;
            }
        }

    /* Estilos Drag and Drop */
    .drop-zone {
        border: 2px dashed var(--primary-color);
        border-radius: 10px;
        padding: 30px;
        text-align: center;
        background-color: #f8f9fa;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
    }

    .drop-zone:hover, .drop-zone.dragover {
        background-color: #e3f2fd;
        border-color: #0a58ca;
    }

    .drop-zone-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: var(--secondary-color);
    }

    .drop-zone i {
        font-size: 3rem;
        margin-bottom: 10px;
        color: var(--primary-color);
    }

    .file-preview {
        margin-top: 15px;
        display: none;
    }
    </style>
    <div class="main-container">
        <!-- Cabeçalho -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1" style="color: var(--primary-color); font-weight: 700;">
                    <i class="bi bi-calculator-fill"></i> Sistema IRRF
                </h1>
                <p class="text-muted mb-0">Cálculo de Retenção de Impostos para Órgãos Públicos</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-2"></i>Usuário Logado
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="userMenu">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-house me-2"></i>Dashboard</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-file-text me-2"></i>Minhas Notas</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-box-arrow-right me-2"></i>Sair</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Container para Alertas Dinâmicos -->
        <div id="alertContainer" class="mb-4"></div>

        <!-- Alertas de Informação -->
        <div class="alert alert-info-custom alert-custom mb-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-info-circle-fill me-3" style="font-size: 1.5rem;"></i>
                <div>
                    <h5 class="alert-heading mb-1">Instruções Importantes</h5>
                    <p class="mb-0">Preencha o CNPJ do fornecedor para buscar seus dados. O cálculo do IRRF será realizado apenas para fornecedores que NÃO sejam do Simples Nacional, conforme IN RFB Nº 2.145/2023.</p>
                </div>
            </div>
        </div>

        <!-- Card Principal -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="mb-0"><i class="bi bi-file-earmark-plus me-2"></i>Gerar Novo Cálculo IRRF</h3>
            </div>
            <div class="card-body">
                <!-- Loading Spinner -->
                <div class="loading-spinner" id="loadingSpinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <p class="mt-2 text-muted">Processando dados...</p>
                </div>

                <!-- Seção 1: Fornecedor -->
                <!-- Campo oculto para ID da nota em edição -->
                <input type="hidden" id="id_nota_edit">
                <div class="form-section">
                    <h5 class="section-title">
                        <i class="bi bi-building"></i> Dados do Fornecedor
                    </h5>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label required-field">CNPJ do Fornecedor</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-building"></i></span>
                                <input type="text" id="cnpj_busca" class="form-control" 
                                       placeholder="00.000.000/0000-00"
                                       maxlength="18"
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="top" 
                                       title="Digite o CNPJ completo do fornecedor">
                            </div>
                            <div class="info-text">
                                <i class="bi bi-info-circle"></i> Digite o CNPJ sem pontuação ou com formatação
                            </div>
                        </div>
                        
                        <div class="col-md-5">
                            <label class="form-label">Razão Social</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                <input type="text" id="razao_social" class="form-control readonly-bg" readonly>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Regime Tributário</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                                <input type="text" id="regime_txt" class="form-control readonly-bg" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div id="mensagem_regime" class="mt-3"></div>
                </div>

                <!-- Seção 2: Serviço e Valores (Inicialmente oculta) -->
                <div class="form-section d-none" id="secao_calculo">
                    <h5 class="section-title">
                        <i class="bi bi-cash-stack"></i> Dados do Serviço e Valores
                    </h5>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required-field">Natureza do Serviço</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-tags"></i></span>
                                <select id="id_natureza" class="form-select">
                                    <option value="">Selecione uma natureza...</option>
                                </select>
                            </div>
                            <div class="info-text">
                                <i class="bi bi-info-circle"></i> Seleciona a natureza conforme IN RFB Nº 2.145
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Descrição do Serviço</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-text-paragraph"></i></span>
                                <input type="text" id="descricao_servico" class="form-control" placeholder="Descreva o serviço prestado">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-3 mt-3">
                        <div class="col-md-4">
                            <label class="form-label required-field">Valor da Nota (R$)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                <input type="text" id="valor_nota" class="form-control" placeholder="0,00">
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Alíquota (%)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-percent"></i></span>
                                <input type="text" id="aliq_retencao" class="form-control readonly-bg text-center fw-bold" readonly>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Número do Cálculo</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-file-earmark-text"></i></span>
                                <input type="text" id="numero_nota" class="form-control" placeholder="Número da NF">
                            </div>
                        </div>

                        <!-- Área de Upload Drag and Drop -->
                        <div class="col-md-12 mt-4">
                            <label class="form-label">Anexo da Nota Fiscal (Opcional)</label>
                            <div class="drop-zone" id="dropZone">
                                <input type="file" id="arquivo_nota" name="arquivo_nota" class="d-none" accept=".pdf,.jpg,.jpeg,.png,.xml">
                                <div class="drop-zone-content">
                                    <i class="bi bi-cloud-upload"></i>
                                    <p class="mb-1 fw-bold">Arraste e solte o arquivo aqui</p>
                                    <p class="small mb-0">ou clique para selecionar (PDF, JPG, PNG, XML)</p>
                                </div>
                                <div class="file-preview" id="filePreview">
                                    <div class="alert alert-light border d-flex align-items-center justify-content-between mb-0">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-file-earmark-text fs-4 me-2 text-primary"></i>
                                            <span id="fileName" class="fw-bold text-dark">nome_arquivo.pdf</span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="btnRemoveFile"><i class="bi bi-x"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seção 3: Resultados do Cálculo -->
                <div class="form-section d-none" id="secao_resultado">
                    <h5 class="section-title">
                        <i class="bi bi-calculator"></i> Resultado do Cálculo
                    </h5>
                    
                    <div class="result-box">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <i class="bi bi-cash-coin" style="font-size: 3rem; color: var(--primary-color);"></i>
                                    <h6 class="mt-2">Valor Bruto</h6>
                                    <div class="result-value" id="display_valor_bruto">R$ 0,00</div>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="text-center">
                                    <i class="bi bi-percent" style="font-size: 3rem; color: var(--warning-color);"></i>
                                    <h6 class="mt-2">Alíquota Aplicada</h6>
                                    <div class="result-value" id="display_aliq">0%</div>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="text-center">
                                    <i class="bi bi-arrow-down-circle" style="font-size: 3rem; color: var(--danger-color);"></i>
                                    <h6 class="mt-2">IRRF Retido</h6>
                                    <div class="result-value" id="display_valor_retencao">R$ 0,00</div>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="text-center">
                                    <i class="bi bi-arrow-up-circle" style="font-size: 3rem; color: var(--success-color);"></i>
                                    <h6 class="mt-2">Valor Líquido</h6>
                                    <div class="result-value" id="display_valor_liquido">R$ 0,00</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-success-custom alert-custom mt-4">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill me-3" style="font-size: 1.5rem;"></i>
                                <div>
                                    <h5 class="alert-heading mb-1">Cálculo Realizado com Sucesso!</h5>
                                    <p class="mb-0" id="mensagem_calculo">O valor de retenção foi calculado conforme a legislação vigente.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary btn-custom" id="btn_limpar">
                                <i class="bi bi-x-circle me-2"></i>Limpar Formulário
                            </button>
                            
                            <div class="d-flex gap-3">
                                <button type="button" class="btn btn-custom-primary btn-custom" id="btn_calcular" disabled>
                                    <i class="bi bi-calculator me-2"></i>Calcular IRRF
                                </button>
                                
                                <button type="button" class="btn btn-custom-success btn-custom" id="btn_salvar" disabled>
                                    <i class="bi bi-save me-2"></i>Salvar Nota
                                </button>
                                
                                <button type="button" class="btn btn-outline-primary btn-custom" id="btn_imprimir" disabled>
                                    <i class="bi bi-printer me-2"></i>Imprimir
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informações Legais -->
        <div class="alert alert-warning-custom alert-custom">
            <h5 class="alert-heading"><i class="bi bi-shield-exclamation me-2"></i>Informação Legal</h5>
            <p class="mb-2">Este sistema realiza o cálculo do Imposto de Renda Retido na Fonte (IRRF) conforme estabelecido na <strong>INSTRUÇÃO NORMATIVA RFB Nº 2.145, DE 31 DE MAIO DE 2023</strong>.</p>
            <hr>
            <p class="mb-0"><small>Os valores calculados são para fins informativos. Consulte sempre um contador para validação dos cálculos.</small></p>
        </div>
    </div>

    <!-- Loading Overlay (padrão Reinf) -->
    <div id="loadingOverlay"
        class="d-none position-fixed top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex justify-content-center align-items-center"
        style="z-index: 9999;">
        <div class="text-center">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
            <p class="mt-2 fw-bold text-primary" id="loadingMessage">Processando...</p>
        </div>
    </div>

    <!-- Botões Flutuantes -->
    <div class="floating-buttons">
        <button class="floating-btn btn btn-custom-primary" id="btnAjuda" title="Ajuda">
            <i class="bi bi-question-circle"></i>
        </button>
        <button class="floating-btn btn btn-success" id="btnNovo" title="Nova Nota">
            <i class="bi bi-plus-circle"></i>
        </button>
        <button class="floating-btn btn btn-info" id="btnHistorico" title="Histórico">
            <i class="bi bi-clock-history"></i>
        </button>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Inicializar tooltips do Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Configurar máscaras
        $('#cnpj_busca').mask('00.000.000/0000-00');
        $('#valor_nota').mask('#.##0,00', {reverse: true});
        
        // Variáveis globais
        var fornecedorData = null;
        var naturezaData = null;

        // Função para validar CNPJ
        function validarCNPJ(cnpj) {
            cnpj = cnpj.replace(/[^\d]+/g,'');
            
            if(cnpj == '') return false;
            if (cnpj.length != 14) return false;

            // Elimina CNPJs inválidos conhecidos
            var cnpjsInvalidos = [
                "00000000000000", "11111111111111", "22222222222222",
                "33333333333333", "44444444444444", "55555555555555",
                "66666666666666", "77777777777777", "88888888888888",
                "99999999999999"
            ];
            
            if (cnpjsInvalidos.indexOf(cnpj) !== -1) return false;
                
            // Valida DVs
            var tamanho = cnpj.length - 2;
            var numeros = cnpj.substring(0, tamanho);
            var digitos = cnpj.substring(tamanho);
            var soma = 0;
            var pos = tamanho - 7;
            
            for (var i = tamanho; i >= 1; i--) {
                soma += numeros.charAt(tamanho - i) * pos--;
                if (pos < 2) pos = 9;
            }
            
            var resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
            if (resultado != digitos.charAt(0)) return false;
            
            tamanho = tamanho + 1;
            numeros = cnpj.substring(0, tamanho);
            soma = 0;
            pos = tamanho - 7;
            
            for (var i = tamanho; i >= 1; i--) {
                soma += numeros.charAt(tamanho - i) * pos--;
                if (pos < 2) pos = 9;
            }
            
            resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
            if (resultado != digitos.charAt(1)) return false;
            
            return true;
        }

        // Buscar fornecedor por CNPJ
        $('#cnpj_busca').on('blur', function() {
            var cnpjLimpo = $(this).val().replace(/\D/g, '');
            
            if (cnpjLimpo.length === 14) {
                if (!validarCNPJ(cnpjLimpo)) {
                    showAlert('CNPJ inválido! Verifique o número digitado.', 'danger');
                    $(this).addClass('is-invalid').focus();
                    return;
                }
                
                $(this).removeClass('is-invalid');
                $('#loadingSpinner').show();
                
                $.getJSON('/sistema_irrf/app/Controllers/NotaController.php', {
                    action: 'buscar_fornecedor',
                    cnpj: cnpjLimpo
                }, function(res) {
                    $('#loadingSpinner').hide();
                    
                    if (res.success && res.dados) {
                        fornecedorData = res.dados;
                        $('#razao_social').val(res.dados.razao_social);
                        
                        var regime = res.dados.regime_tributario.toUpperCase();
                        $('#regime_txt').val(regime);
                        
                        // Aplicar badge ao regime
                        var badgeClass = 'badge-outros';
                        if (regime.toLowerCase().includes('simples')) badgeClass = 'badge-simples';
                        else if (regime.toLowerCase().includes('mei')) badgeClass = 'badge-mei';
                        else if (regime.toLowerCase().includes('presumido')) badgeClass = 'badge-presumido';
                        else if (regime.toLowerCase().includes('real')) badgeClass = 'badge-real';
                        
                        $('#mensagem_regime').html(
                            '<span class="badge ' + badgeClass + ' badge-regime">' + 
                            '<i class="bi bi-shield me-1"></i>' + regime + 
                            '</span>'
                        );
                        
                        var regimeCod = res.dados.regime_tributario.toLowerCase();
                        if (regimeCod !== 'simples_nacional' && regimeCod !== 'mei') {
                            $('#secao_calculo').removeClass('d-none').addClass('animate__animated animate__fadeIn');
                            $('#btn_calcular').prop('disabled', false);
                            carregarNaturezas();
                            showAlert('Fornecedor encontrado! Preencha os dados do serviço.', 'success');
                        } else {
                            $('#secao_calculo').addClass('d-none');
                            $('#secao_resultado').addClass('d-none');
                            showAlert('Fornecedor MEI ou Simples Nacional: Não há retenção de IRRF conforme legislação.', 'info');
                        }
                    } else {
                        showAlert('Fornecedor não encontrado no cadastro. Verifique o CNPJ ou cadastre-o primeiro.', 'warning');
                        limparCamposFornecedor();
                    }
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    $('#loadingSpinner').hide();
                    console.error("Erro na requisição:", textStatus, errorThrown);
                    showAlert('Erro ao buscar dados do fornecedor. Tente novamente.', 'danger');
                });
            }
        });

        // Carregar naturezas de serviço
        function carregarNaturezas() {
            $('#loadingSpinner').show();
            
            $.getJSON('/sistema_irrf/app/Controllers/NotaController.php', { 
                action: 'listar_naturezas' 
            }, function(data) {
                $('#loadingSpinner').hide();
                
                if (data && data.length > 0) {
                    var html = '<option value="">Selecione uma natureza...</option>';
                    $.each(data, function(i, item) {
                        html += '<option value="' + item.id + '" ' +
                                'data-aliq="' + item.aliquota_padrao + '" ' +
                                'data-codigo="' + item.codigo_rfb + '" ' +
                                'data-descricao="' + item.descricao + '">' +
                                item.codigo_rfb + ' - ' + item.descricao + ' (' + item.aliquota_padrao + '%)' +
                                '</option>';
                    });
                    $('#id_natureza').html(html);
                } else {
                    showAlert('Não foi possível carregar as naturezas de serviço.', 'warning');
                }
            }).fail(function() {
                $('#loadingSpinner').hide();
                showAlert('Erro ao carregar as naturezas de serviço.', 'danger');
            });
        }

        // Calcular totais
        function calcularTotais() {
            // Converter valor para número
            var valorNotaStr = $('#valor_nota').val() || '0';
            var valorNota = parseFloat(valorNotaStr.replace(/\./g, '').replace(',', '.')) || 0;
            
            // Pegar alíquota
            var aliq = parseFloat($('#id_natureza').find(':selected').data('aliq')) || 0;
            
            // Calcular retenção
            var valorRetencao = (valorNota * aliq) / 100;
            var valorLiquido = valorNota - valorRetencao;
            
            // Atualizar campos
            $('#aliq_retencao').val(aliq.toFixed(2).replace('.', ','));
            
            // Atualizar displays
            $('#display_valor_bruto').text(formatCurrency(valorNota));
            $('#display_aliq').text(aliq.toFixed(2) + '%');
            $('#display_valor_retencao').text(formatCurrency(valorRetencao));
            $('#display_valor_liquido').text(formatCurrency(valorLiquido));
            
            // Atualizar mensagem
            var naturezaDesc = $('#id_natureza').find(':selected').data('descricao') || '';
            $('#mensagem_calculo').html(
                'Cálculo realizado com base na natureza <strong>' + 
                ($('#id_natureza').find(':selected').data('codigo') || '') + 
                '</strong> - ' + naturezaDesc
            );
            
            // Mostrar seção de resultados
            if (valorNota > 0 && aliq > 0) {
                $('#secao_resultado').removeClass('d-none').addClass('animate__animated animate__fadeIn');
                $('#btn_salvar').prop('disabled', false);
                $('#btn_imprimir').prop('disabled', false);
            }
        }

        // Formatar moeda
        function formatCurrency(value) {
            return 'R$ ' + value.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // Função para controlar o loading overlay
        function toggleLoading(show, message = 'Processando...') {
            $('#loadingMessage').text(message);
            if (show) {
                $('#loadingOverlay').removeClass('d-none');
            } else {
                $('#loadingOverlay').addClass('d-none');
            }
        }

        // Mostrar alerta personalizado no container dedicado
        function showAlert(message, type, clear = true) {
            // Limpa alertas antigos
            if (clear) {
                $('#alertContainer').empty();
            }

            var alertClass = 'alert-' + type;
            var icon = '';
            
            switch(type) {
                case 'success': icon = '<i class="bi bi-check-circle-fill me-2"></i>'; break;
                case 'danger': icon = '<i class="bi bi-exclamation-circle-fill me-2"></i>'; break;
                default: icon = '<i class="bi bi-info-circle-fill me-2"></i>';
            }
            
            var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                            icon + message +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                            '</div>';

            $('#alertContainer').append(alertHtml);

            // Rola a página para o topo para que o usuário veja a mensagem
            $('html, body').animate({ scrollTop: 0 }, 'slow');
        }

        // Limpar campos do fornecedor
        function limparCamposFornecedor() {
            $('#razao_social').val('');
            $('#regime_txt').val('');
            $('#mensagem_regime').html('');
            $('#secao_calculo').addClass('d-none');
            $('#secao_resultado').addClass('d-none');
            $('#btn_calcular').prop('disabled', true);
            $('#btn_salvar').prop('disabled', true);
            $('#btn_imprimir').prop('disabled', true);
            $('#id_natureza').html('<option value="">Selecione uma natureza...</option>');
        }

        // Limpar formulário completo
        $('#btn_limpar').on('click', function(e, data) {
            $('#cnpj_busca').val('').removeClass('is-invalid');
            limparCamposFornecedor();
            $('#valor_nota').val('');
            $('#aliq_retencao').val('');
            $('#descricao_servico').val('');
            $('#numero_nota').val('');
            resetDropZone();
            
            var clearAlerts = true;
            if (data && data.keepAlerts) {
                clearAlerts = false;
            }
            showAlert('Formulário limpo com sucesso!', 'info', clearAlerts);
        });

        // Botão Calcular
        $('#btn_calcular').click(function() {
            if (!$('#id_natureza').val()) {
                showAlert('Selecione uma natureza de serviço antes de calcular.', 'warning');
                $('#id_natureza').focus();
                return;
            }
            
            if (!$('#valor_nota').val() || parseFloat($('#valor_nota').val().replace(/\./g, '').replace(',', '.')) <= 0) {
                showAlert('Informe um valor válido para a nota antes de calcular.', 'warning');
                $('#valor_nota').focus();
                return;
            }
            
            calcularTotais();
            showAlert('Cálculo realizado com sucesso!', 'success');
        });      

        // --- Lógica Drag and Drop ---
        const dropZone = $('#dropZone');
        const fileInput = $('#arquivo_nota');
        const filePreview = $('#filePreview');
        const fileName = $('#fileName');
        const btnRemoveFile = $('#btnRemoveFile');
        const dropZoneContent = $('.drop-zone-content');

        // Clique na zona abre o seletor
        dropZone.on('click', function(e) {
            // Evita recursão infinita se o clique vier do próprio input file (propagação)
            if ($(e.target).is(fileInput)) return;

            if (e.target !== btnRemoveFile[0] && !$.contains(btnRemoveFile[0], e.target)) {
                fileInput.click();
            }
        });

        // Eventos de arrastar
        dropZone.on('dragover dragenter', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('dragover');
        });

        dropZone.on('dragleave drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');
        });

        // Soltar arquivo
        dropZone.on('drop', function(e) {
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                fileInput[0].files = files;
                updateFilePreview(files[0]);
            }
        });

        // Selecionar via input
        fileInput.on('change', function() {
            if (this.files.length > 0) {
                updateFilePreview(this.files[0]);
            }
        });

        // Remover arquivo
        btnRemoveFile.on('click', function(e) {
            e.stopPropagation(); // Evita abrir o seletor novamente
            resetDropZone();
        });

        function updateFilePreview(file) {
            fileName.text(file.name);
            dropZoneContent.addClass('d-none');
            filePreview.removeClass('d-none').addClass('animate__animated animate__fadeIn');
        }

        function resetDropZone() {
            fileInput.val('');
            dropZoneContent.removeClass('d-none');
            filePreview.addClass('d-none');
        }

// Botão Salvar - COM CAMINHO ABSOLUTO CORRETO
$('#btn_salvar').click(function() {
    // Validar campos obrigatórios
    if (!fornecedorData) {
        showAlert('Fornecedor não selecionado.', 'warning');
        return;
    }
    
    if (!$('#id_natureza').val()) {
        showAlert('Natureza do serviço não selecionada.', 'warning');
        return;
    }
    
    if (!$('#valor_nota').val()) {
        showAlert('Valor da nota não informado.', 'warning');
        return;
    }
    
    // Pegar valores calculados
    var valorBrutoStr = $('#valor_nota').val() || '0';
    var valorBruto = parseFloat(valorBrutoStr.replace(/\./g, '').replace(',', '.'));
    var aliq = parseFloat($('#id_natureza').find(':selected').data('aliq')) || 0;
    var valorRetencao = (valorBruto * aliq) / 100;
    var valorLiquido = valorBruto - valorRetencao;
    
    toggleLoading(true, 'Salvando nota...');
    
    // Preparar dados para salvar usando FormData para suportar arquivo
    var formData = new FormData();
    formData.append('action', 'salvar_nota');
    formData.append('id_fornecedor', fornecedorData.id);
    
    // Adiciona ID se for edição
    if ($('#id_nota_edit').val()) {
        formData.append('id_nota', $('#id_nota_edit').val());
    }

    formData.append('id_natureza', $('#id_natureza').val());
    formData.append('valor_bruto', valorBruto);
    formData.append('aliquota', aliq);
    formData.append('valor_irrf_retido', valorRetencao);
    formData.append('valor_liquido', valorLiquido);
    formData.append('numero_nota', $('#numero_nota').val() || 'NF-' + new Date().getTime());
    formData.append('descricao_servico', $('#descricao_servico').val());
    
    // Adicionar arquivo se existir
    var file = $('#arquivo_nota')[0].files[0];
    if (file) {
        formData.append('arquivo_nota', file);
    }
    
    console.log('Enviando dados para:', '/sistema_irrf/app/Controllers/NotaController.php');
    
    // Enviar para o servidor - CAMINHO ABSOLUTO
    $.ajax({
        url: '/sistema_irrf/public/api/nota.php?action=salvar_nota',
        type: 'POST',
        data: formData,
        processData: false, // Necessário para FormData
        contentType: false, // Necessário para FormData
        success: function(res) {
            toggleLoading(false);
            
            if (res.success) {
                showAlert('Nota salva com sucesso! ID: ' + res.id_nota + ' - Número: ' + res.numero_nota, 'success');
                
                // Habilitar botão de impressão
                $('#btn_imprimir').prop('disabled', false);
                
                // Opcional: resetar formulário após sucesso
                setTimeout(function() {
                    // Limpa o formulário automaticamente para a próxima nota
                    $('#btn_limpar').trigger('click', [{ keepAlerts: true }]);
                }, 2000);
                
            } else {
                showAlert('Erro ao salvar nota: ' + (res.error || 'Erro desconhecido'), 'danger');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            toggleLoading(false);
            console.error('Erro AJAX ao salvar:', {
                status: jqXHR.status,
                statusText: jqXHR.statusText,
                responseText: jqXHR.responseText,
                textStatus: textStatus,
                errorThrown: errorThrown
            });
            
            if (jqXHR.status === 401) {
                showAlert('Você precisa fazer login para salvar notas. Redirecionando...', 'warning');
                setTimeout(function() {
                    window.location.href = '/sistema_irrf/login.php';
                }, 2000);
            } else if (jqXHR.status === 500) {
                showAlert('Erro no servidor: ' + jqXHR.responseText, 'danger');
            } else {
                showAlert('Erro ao salvar: ' + textStatus, 'danger');
            }
        }
    });
});

        // Botão Imprimir
        $('#btn_imprimir').click(function() {
            window.print();
        });

        // Botões flutuantes
        $('#btnAjuda').click(function() {
            showAlert('<strong>Ajuda:</strong><br>1. Digite o CNPJ do fornecedor.<br>2. Selecione a natureza do serviço.<br>3. Informe o valor da nota.<br>4. Clique em "Calcular".<br>5. Clique em "Salvar Nota".', 'info');
        });
        
        $('#btnNovo').click(function() {
            $('#btn_limpar').click();
        });
        
        $('#btnHistorico').click(function() {
            showAlert('Funcionalidade de histórico em desenvolvimento...', 'info');
        });

        // Eventos para cálculo automático
        $('#id_natureza').on('change', function() {
            if ($(this).val() && $('#valor_nota').val()) {
                calcularTotais();
            }
        });
        
        $('#valor_nota').on('keyup', function() {
            if ($('#id_natureza').val() && $(this).val()) {
                calcularTotais();
            }
        });

        // Validação em tempo real do valor da nota
        $('#valor_nota').on('blur', function() {
            var valor = $(this).val().replace(/\./g, '').replace(',', '.');
            valor = parseFloat(valor) || 0;
            
            if (valor <= 0) {
                $(this).addClass('is-invalid');
                showAlert('O valor da nota deve ser maior que zero.', 'warning');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // ==========================================
        // LÓGICA DE EDIÇÃO (Carregar dados da URL)
        // ==========================================
        const urlParams = new URLSearchParams(window.location.search);
        const editId = urlParams.get('id');
        
        if (editId) {
            toggleLoading(true, 'Carregando dados da nota...');
            $('h3.mb-0').html('<i class="bi bi-pencil-square me-2"></i>Editar Nota Fiscal');
            
            $.getJSON('/sistema_irrf/public/api/nota.php', {
                action: 'buscar_nota',
                id: editId
            }, function(res) {
                if (res.success) {
                    const nota = res.nota;
                    
                    // Preenche ID oculto
                    $('#id_nota_edit').val(nota.id);
                    
                    // Preenche campos básicos
                    $('#cnpj_busca').val(nota.cnpj.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, "$1.$2.$3/$4-$5"));
                    $('#valor_nota').val(nota.valor_bruto_formatado.replace('R$ ', ''));
                    $('#numero_nota').val(nota.numero_nota);
                    $('#descricao_servico').val(nota.descricao_servico);
                    
                    // Busca dados do fornecedor para preencher a UI e carregar naturezas
                    $.getJSON('/sistema_irrf/app/Controllers/NotaController.php', {
                        action: 'buscar_fornecedor',
                        cnpj: nota.cnpj.replace(/\D/g, '')
                    }, function(resForn) {
                        if (resForn.success) {
                            fornecedorData = resForn.dados;
                            $('#razao_social').val(resForn.dados.razao_social);
                            $('#regime_txt').val(resForn.dados.regime_tributario.toUpperCase());
                            
                            // Badge do regime
                            var regime = resForn.dados.regime_tributario.toUpperCase();
                            var badgeClass = 'badge-outros';
                            if (regime.toLowerCase().includes('simples')) badgeClass = 'badge-simples';
                            else if (regime.toLowerCase().includes('presumido')) badgeClass = 'badge-presumido';
                            else if (regime.toLowerCase().includes('real')) badgeClass = 'badge-real';
                            
                            $('#mensagem_regime').html('<span class="badge ' + badgeClass + ' badge-regime"><i class="bi bi-shield me-1"></i>' + regime + '</span>');
                            
                            $('#secao_calculo').removeClass('d-none');
                            $('#btn_calcular').prop('disabled', false);
                            
                            // Carrega naturezas e seleciona a correta
                            $.getJSON('/sistema_irrf/app/Controllers/NotaController.php', { 
                                action: 'listar_naturezas' 
                            }, function(data) {
                                toggleLoading(false);
                                if (data && data.length > 0) {
                                    var html = '<option value="">Selecione uma natureza...</option>';
                                    $.each(data, function(i, item) {
                                        html += '<option value="' + item.id + '" data-aliq="' + item.aliquota_padrao + '" data-codigo="' + item.codigo_rfb + '" data-descricao="' + item.descricao + '">' + item.codigo_rfb + ' - ' + item.descricao + ' (' + item.aliquota_padrao + '%)' + '</option>';
                                    });
                                    $('#id_natureza').html(html);
                                    
                                    // Seleciona a natureza da nota
                                    $('#id_natureza').val(nota.id_natureza_servico);
                                    
                                    // Executa cálculo para mostrar resultados
                                    calcularTotais();
                                    
                                    // Atualiza texto do botão
                                    $('#btn_salvar').html('<i class="bi bi-save me-2"></i>Atualizar Nota');
                                }
                            });
                        }
                    });
                } else {
                    toggleLoading(false);
                    showAlert('Erro ao carregar nota: ' + res.error, 'danger');
                }
            }).fail(function() {
                toggleLoading(false);
                showAlert('Erro de conexão ao buscar nota.', 'danger');
            });
        }
    });
    </script>
<?php require_once __DIR__ . '/layout/footer.php'; ?>