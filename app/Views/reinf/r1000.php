<?php
// app/Views/reinf/r1000.php
require_once __DIR__ . '/../layout/header.php';
?>

<div class="container-fluid mt-4">
    <!-- Cabeçalho igual ao dashboard -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-primary fw-bold">
                <i class="fas fa-building me-2"></i>R-1000 - Cadastro do Contribuinte
            </h2>
            <p class="text-muted mb-0">Informações cadastrais do órgão para o ambiente da Receita Federal.</p>
        </div>
        <div id="status-badge-header"></div>
    </div>

    <!-- Container de Alertas (igual ao dashboard) -->
    <div id="alertContainer"></div>
    <div id="alerta-r1000"></div>

    <!-- Loading Overlay (igual ao dashboard) -->
    <div id="loadingOverlay" class="d-none position-fixed top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex justify-content-center align-items-center" style="z-index: 9999;">
        <div class="text-center">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
            <p class="mt-2 fw-bold text-primary" id="loadingMessage">Processando...</p>
        </div>
    </div>

    <!-- Status do Cadastro - Card igual ao dashboard -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow border-0">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h5 class="card-title text-primary mb-0">
                        <i class="fas fa-chart-line me-2"></i>Status do Cadastro
                    </h5>
                </div>
                <div class="card-body" id="status-cadastro">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Verificando status do cadastro...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulário de Cadastro - Card igual ao dashboard -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow border-0">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h5 class="card-title text-primary mb-0">
                        <i class="fas fa-edit me-2"></i>Dados do Contribuinte (Evento R-1000)
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formR1000">
                        <!-- Dados Fixos do Órgão -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small mb-1">CNPJ do Órgão</label>
                                <input type="text" class="form-control bg-light" id="cnpj" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small mb-1">Nome Oficial</label>
                                <input type="text" class="form-control bg-light" id="nome_oficial" readonly>
                            </div>
                        </div>

                        <!-- Classificação Tributária -->
                        <h6 class="text-primary fw-bold mb-3 pb-2 border-bottom">
                            <i class="fas fa-tag me-2"></i>Classificação Tributária
                        </h6>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-secondary small mb-1">Código de Classificação</label>
                                <select class="form-select" id="classificacao_tributaria" required>
                                    <option value="99">99 - Pessoa Jurídica em Geral</option>
                                    <option value="01">01 - Empresa Privada</option>
                                    <option value="02">02 - Empresa Pública</option>
                                    <option value="03">03 - Órgão Público</option>
                                    <option value="04">04 - Sociedade de Economia Mista</option>
                                    <option value="05">05 - Autarquia</option>
                                    <option value="06">06 - Fundação Privada</option>
                                    <option value="07">07 - Fundação Pública</option>
                                    <option value="08">08 - Organização Social</option>
                                </select>
                                <small class="text-muted">Padrão: 99 (PJ em geral)</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-secondary small mb-1">Escrituração Contábil (ECD)</label>
                                <select class="form-select" id="indicador_ecd">
                                    <option value="0">0 - Não obrigado</option>
                                    <option value="1">1 - Obrigado</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-secondary small mb-1">Desoneração (CPRB)</label>
                                <select class="form-select" id="indicador_desoneracao">
                                    <option value="0">0 - Não desonerado</option>
                                    <option value="1">1 - Desonerado</option>
                                </select>
                            </div>
                        </div>

                        <!-- Informações de Contato -->
                        <h6 class="text-primary fw-bold mb-3 pb-2 border-bottom">
                            <i class="fas fa-address-card me-2"></i>Informações de Contato <span class="text-danger">*</span>
                        </h6>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-secondary small mb-1">Nome do Contato *</label>
                                <input type="text" class="form-control" id="contato_nome" required maxlength="70" 
                                       placeholder="Nome completo do responsável">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-secondary small mb-1">CPF do Contato *</label>
                                <input type="text" class="form-control" id="contato_cpf" required maxlength="14" 
                                       placeholder="000.000.000-00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-secondary small mb-1">Telefone *</label>
                                <input type="text" class="form-control" id="contato_telefone" required 
                                       placeholder="(00) 00000-0000">
                            </div>
                        </div>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small mb-1">E-mail *</label>
                                <input type="email" class="form-control" id="contato_email" required maxlength="60" 
                                       placeholder="contato@camara.gov.br">
                            </div>
                        </div>

                        <!-- Botões de Ação - Padrão Dashboard Reinf -->
                        <div class="bg-light p-3 rounded mt-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <span class="text-muted small">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Campos marcados com * são obrigatórios
                                    </span>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-warning" onclick="validarR1000()">
                                        <i class="fas fa-check-circle me-1"></i>Validar XML
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="salvarDados()">
                                        <i class="fas fa-save me-1"></i>Salvar Rascunho
                                    </button>
                                    <button type="button" class="btn btn-info" onclick="consultarR1000()">
                                        <i class="fas fa-sync-alt me-1"></i>Consultar Status
                                    </button>
                                    <button type="button" class="btn btn-success" id="btnEnviarR1000" onclick="enviarR1000()">
                                        <i class="fas fa-paper-plane me-1"></i>Enviar para Receita
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Histórico de Envios - Card igual ao dashboard -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow border-0">
                <div class="card-header bg-white border-bottom-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title text-secondary mb-0">
                        <i class="fas fa-history me-2"></i>Histórico de Envios
                    </h5>
                    <span class="badge bg-light text-dark border">Últimos 20 registros</span>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tabela-historico">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Data/Hora</th>
                                <th>Status</th>
                                <th>Protocolo</th>
                                <th>Recibo</th>
                                <th>Mensagem</th>
                                <th class="text-end pe-4">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="historico-body">
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                    Carregando histórico...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

<script>
    // =============== CONSTANTES ===============
    const BASE_URL = '/sistema_irrf';
    
    // =============== DOCUMENT READY ===============
    $(document).ready(function() {
        // Máscaras
        $('#contato_cpf').mask('000.000.000-00');
        $('#contato_telefone').mask('(00) 00000-0000');
        $('#cnpj').mask('00.000.000/0000-00');
        
        // Carrega dados iniciais
        carregarDadosOrgao();
        verificarStatusR1000();
        carregarHistorico();
        
        // Auto-consulta a cada 30 segundos
        setInterval(autoConsultarPendentes, 30000);
    });

    // =============== LOADING ===============
    function toggleLoading(show, mensagem = 'Processando...') {
        $('#loadingMessage').text(mensagem);
        if (show) {
            $('#loadingOverlay').removeClass('d-none');
        } else {
            $('#loadingOverlay').addClass('d-none');
        }
    }

    // =============== ALERTAS (Padrão Dashboard) ===============
    function showAlert(type, message, title = null) {
        let icon = type === 'success' ? 'check-circle' : 
                   type === 'danger' ? 'exclamation-circle' : 
                   type === 'warning' ? 'exclamation-triangle' : 'info-circle';
        
        let html = `
        <div class="alert alert-${type} alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-${icon} me-2"></i>
            ${title ? `<strong>${title}:</strong> ` : ''}
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        `;
        
        $('#alertContainer').prepend(html);
        
        setTimeout(() => { 
            $('.alert').first().fadeOut(500, function() { $(this).remove(); });
        }, 10000);
    }

    // =============== CARREGAR DADOS DO ÓRGÃO ===============
    function carregarDadosOrgao() {
        $.ajax({
            url: BASE_URL + '/public/api/r1000.php?action=get_dados',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const dados = response.dados;
                    $('#cnpj').val(dados.cnpj);
                    $('#nome_oficial').val(dados.nome_oficial);
                    $('#classificacao_tributaria').val(dados.classificacao_tributaria || '99');
                    $('#indicador_ecd').val(dados.indicador_ecd || '0');
                    $('#indicador_desoneracao').val(dados.indicador_desoneracao || '0');
                    $('#contato_nome').val(dados.contato_nome || dados.responsavel_nome || '');
                    $('#contato_cpf').val(dados.contato_cpf || '');
                    $('#contato_telefone').val(dados.contato_telefone || '');
                    $('#contato_email').val(dados.contato_email || dados.responsavel_email || '');
                }
            },
            error: function(xhr) {
                showAlert('danger', 'Erro ao carregar dados: ' + (xhr.responseJSON?.error || 'Erro desconhecido'));
            }
        });
    }

    // =============== VERIFICAR STATUS ===============
    function verificarStatusR1000() {
        $.ajax({
            url: BASE_URL + '/public/api/r1000.php?action=verificar_status',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    let html = '';
                    
                    if (response.tem_cadastro) {
                        html = `
                            <div class="d-flex align-items-center p-4">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                                        <i class="fas fa-check-circle text-success fa-3x"></i>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="fw-bold text-success mb-2">CADASTRO ATIVO</h4>
                                    <p class="mb-1"><strong>Recibo:</strong> ${response.recibo}</p>
                                    <p class="mb-1"><strong>Data:</strong> ${new Date(response.data_envio).toLocaleString('pt-BR')}</p>
                                    <p class="mb-0"><span class="badge bg-success">${response.status || 'sucesso'}</span></p>
                                </div>
                            </div>
                        `;
                        $('#status-badge-header').html('<span class="badge bg-success fs-6 px-3 py-2">Cadastrado</span>');
                    } else {
                        html = `
                            <div class="d-flex align-items-center p-4">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                                        <i class="fas fa-exclamation-triangle text-warning fa-3x"></i>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="fw-bold text-warning mb-2">CADASTRO NÃO LOCALIZADO</h4>
                                    <p class="mb-1">O órgão ainda não possui cadastro no ambiente da Receita Federal.</p>
                                    <p class="mb-0">Preencha os dados abaixo e clique em "Enviar para Receita".</p>
                                </div>
                            </div>
                        `;
                        $('#status-badge-header').html('<span class="badge bg-warning fs-6 px-3 py-2">Pendente</span>');
                    }
                    
                    $('#status-cadastro').html(html);
                }
            },
            error: function(xhr) {
                $('#status-cadastro').html(`
                    <div class="d-flex align-items-center p-4">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-danger bg-opacity-10 p-3 rounded-circle">
                                <i class="fas fa-times-circle text-danger fa-3x"></i>
                            </div>
                        </div>
                        <div>
                            <h4 class="fw-bold text-danger mb-2">ERRO AO VERIFICAR STATUS</h4>
                            <p class="mb-0">${xhr.responseJSON?.error || 'Erro desconhecido'}</p>
                        </div>
                    </div>
                `);
            }
        });
    }

    // =============== SALVAR DADOS ===============
    function salvarDados() {
        const dados = {
            classificacao_tributaria: $('#classificacao_tributaria').val(),
            indicador_ecd: $('#indicador_ecd').val(),
            indicador_desoneracao: $('#indicador_desoneracao').val(),
            contato_nome: $('#contato_nome').val(),
            contato_cpf: $('#contato_cpf').val(),
            contato_telefone: $('#contato_telefone').val(),
            contato_email: $('#contato_email').val()
        };
        
        toggleLoading(true, 'Salvando dados...');
        
        $.ajax({
            url: BASE_URL + '/public/api/r1000.php?action=salvar_dados',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(dados),
            dataType: 'json',
            success: function(response) {
                toggleLoading(false);
                if (response.success) {
                    showAlert('success', response.mensagem, 'Sucesso');
                }
            },
            error: function(xhr) {
                toggleLoading(false);
                showAlert('danger', xhr.responseJSON?.error || 'Erro ao salvar dados', 'Erro');
            }
        });
    }

    // =============== VALIDAR XML ===============
    function validarR1000() {
        const dados = {
            classificacao_tributaria: $('#classificacao_tributaria').val(),
            indicador_ecd: $('#indicador_ecd').val(),
            indicador_desoneracao: $('#indicador_desoneracao').val(),
            contato_nome: $('#contato_nome').val(),
            contato_cpf: $('#contato_cpf').val(),
            contato_telefone: $('#contato_telefone').val(),
            contato_email: $('#contato_email').val()
        };
        
        toggleLoading(true, 'Validando XML...');
        
        $.ajax({
            url: BASE_URL + '/public/api/r1000.php?action=validar',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(dados),
            dataType: 'json',
            success: function(response) {
                toggleLoading(false);
                
                if (response.success) {
                    if (response.valido) {
                        showAlert('success', 
                            `<strong>XML VÁLIDO!</strong><br>
                             ID do Evento: ${response.id_evento}<br>
                             Todos os dados estão corretos.`, 
                            'Validação'
                        );
                    } else {
                        let msg = '<strong>ERROS ENCONTRADOS:</strong><br>';
                        if (response.erros_dados) {
                            response.erros_dados.forEach(e => msg += '• ' + e + '<br>');
                        }
                        if (response.erros_xml) {
                            response.erros_xml.forEach(e => msg += '• ' + e + '<br>');
                        }
                        showAlert('warning', msg, 'Validação');
                    }
                }
            },
            error: function(xhr) {
                toggleLoading(false);
                showAlert('danger', xhr.responseJSON?.error || 'Erro ao validar XML', 'Erro');
            }
        });
    }

    // =============== ENVIAR R1000 ===============
    function enviarR1000() {
        if (!confirm('Confirma o envio do evento R-1000 para a Receita Federal? Esta operação não pode ser desfeita.')) {
            return;
        }
        
        if (!$('#contato_nome').val() || !$('#contato_cpf').val() || !$('#contato_telefone').val() || !$('#contato_email').val()) {
            showAlert('warning', 'Preencha todos os campos obrigatórios de contato.', 'Atenção');
            return;
        }
        
        const dados = {
            classificacao_tributaria: $('#classificacao_tributaria').val(),
            indicador_ecd: $('#indicador_ecd').val(),
            indicador_desoneracao: $('#indicador_desoneracao').val(),
            contato_nome: $('#contato_nome').val(),
            contato_cpf: $('#contato_cpf').val(),
            contato_telefone: $('#contato_telefone').val(),
            contato_email: $('#contato_email').val()
        };
        
        toggleLoading(true, 'Enviando R-1000 para Receita...');
        $('#btnEnviarR1000').prop('disabled', true);
        
        $.ajax({
            url: BASE_URL + '/public/api/r1000.php?action=enviar',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(dados),
            dataType: 'json',
            success: function(response) {
                toggleLoading(false);
                $('#btnEnviarR1000').prop('disabled', false);
                
                if (response.success) {
                    showAlert('success', 
                        `R-1000 enviado com sucesso!<br>
                         Protocolo: ${response.protocolo}<br>
                         ID do Evento: ${response.id_evento}`, 
                        'Sucesso'
                    );
                    
                    verificarStatusR1000();
                    carregarHistorico();
                    setTimeout(consultarR1000, 5000);
                }
            },
            error: function(xhr) {
                toggleLoading(false);
                $('#btnEnviarR1000').prop('disabled', false);
                showAlert('danger', xhr.responseJSON?.error || 'Erro ao enviar R-1000', 'Erro');
            }
        });
    }

    // =============== CONSULTAR STATUS ===============
    function consultarR1000() {
        toggleLoading(true, 'Consultando status na Receita...');
        
        $.ajax({
            url: BASE_URL + '/public/api/r1000.php?action=consultar',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                toggleLoading(false);
                
                if (response.success) {
                    if (response.enviado) {
                        let tipo = response.status === 'sucesso' ? 'success' : 
                                  (response.status === 'rejeitado' || response.status === 'erro') ? 'danger' : 'info';
                        let titulo = response.status === 'sucesso' ? 'Sucesso' : 
                                   (response.status === 'rejeitado' || response.status === 'erro') ? 'Erro' : 'Consulta';
                        
                        showAlert(tipo, response.mensagem, titulo);
                        verificarStatusR1000();
                        carregarHistorico();
                    } else {
                        showAlert('info', 'Nenhum envio de R-1000 encontrado.', 'Consulta');
                    }
                }
            },
            error: function(xhr) {
                toggleLoading(false);
                showAlert('danger', xhr.responseJSON?.error || 'Erro ao consultar status', 'Erro');
            }
        });
    }

    // =============== CARREGAR HISTÓRICO ===============
    function carregarHistorico() {
        $.ajax({
            url: BASE_URL + '/public/api/r1000.php?action=historico',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    let html = '';
                    
                    if (response.historico.length === 0) {
                        html = '<tr><td colspan="6" class="text-center py-4 text-muted">Nenhum envio encontrado.</td></tr>';
                    } else {
                        response.historico.forEach(function(item) {
                            let statusBadge = '';
                            let statusClass = '';
                            
                            switch(item.status) {
                                case 'sucesso':
                                    statusClass = 'bg-success';
                                    statusBadge = 'Sucesso';
                                    break;
                                case 'rejeitado':
                                case 'erro':
                                    statusClass = 'bg-danger';
                                    statusBadge = 'Rejeitado';
                                    break;
                                case 'em_lote':
                                case 'processando':
                                case 'pendente':
                                    statusClass = 'bg-warning text-dark';
                                    statusBadge = 'Processando';
                                    break;
                                default:
                                    statusClass = 'bg-secondary';
                                    statusBadge = item.status || 'Pendente';
                            }
                            
                            let data = new Date(item.created_at).toLocaleString('pt-BR');
                            let mensagem = item.mensagem_erro ? item.mensagem_erro.substring(0, 50) + '...' : '-';
                            
                            html += `
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold">${data}</div>
                                    </td>
                                    <td><span class="badge ${statusClass} px-3 py-2">${statusBadge}</span></td>
                                    <td>${item.protocolo || item.id_evento_xml || '-'}</td>
                                    <td>${item.numero_recibo || '-'}</td>
                                    <td><small class="text-muted">${mensagem}</small></td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-primary" onclick="consultarR1000()" title="Consultar">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    }
                    
                    $('#historico-body').html(html);
                }
            },
            error: function(xhr) {
                $('#historico-body').html(`
                    <tr>
                        <td colspan="6" class="text-center text-danger py-4">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Erro ao carregar histórico: ${xhr.responseJSON?.error || 'Erro desconhecido'}
                        </td>
                    </tr>
                `);
            }
        });
    }

    // =============== AUTO CONSULTA ===============
    function autoConsultarPendentes() {
        const temPendente = $('#historico-body').text().includes('Processando') || 
                          $('#historico-body').text().includes('em_lote') ||
                          $('#historico-body').text().includes('pendente');
        
        if (temPendente) {
            consultarR1000();
        }
    }

    // =============== FORMATADORES ===============
    function formatMoney(value) {
        return parseFloat(value).toLocaleString('pt-BR', { 
            style: 'currency', 
            currency: 'BRL' 
        });
    }

    function formatCnpj(v) {
        v = v.replace(/\D/g, "");
        return v.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, "$1.$2.$3/$4-$5");
    }
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>