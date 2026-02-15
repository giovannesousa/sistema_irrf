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

                        <!-- Informações EFR -->
                        <h6 class="text-primary fw-bold mb-3 pb-2 border-bottom">
                            <i class="fas fa-university me-2"></i>Ente Federativo Responsável (EFR)
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-secondary small mb-1">É Ente Federativo?</label>
                                <select class="form-select" id="ide_efr">
                                    <option value="">Não informar (Dispensado)</option>
                                    <option value="S">Sim</option>
                                    <option value="N">Não</option>
                                </select>
                            </div>
                            <div class="col-md-4 d-none" id="div_cnpj_efr">
                                <label class="form-label fw-bold text-secondary small mb-1">CNPJ do EFR</label>
                                <input type="text" class="form-control" id="cnpj_efr" placeholder="00.000.000/0000-00">
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
                                    <button type="button" class="btn btn-outline-danger ms-2" onclick="excluirR1000()">
                                        <i class="fas fa-trash-alt me-1"></i>Resetar Base (Excluir)
                                    </button>
                                    <button type="button" class="btn btn-danger ms-2" onclick="resetarLocal()" title="Limpa apenas o banco de dados local, ignorando a Receita">
                                        <i class="fas fa-bomb me-1"></i>Forçar Reset Local
                                    </button>
                                    <button type="button" class="btn btn-dark ms-2" onclick="limparEventosReceita()" title="Envia exclusão para todos os eventos R-4020 ativos na Receita">
                                        <i class="fas fa-eraser me-1"></i>Limpar Eventos (Receita)
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
    $(document).ready(function() {
        // Máscaras
        $('#contato_cpf').mask('000.000.000-00');
        $('#contato_telefone').mask('(00) 00000-0000');
        $('#cnpj_efr').mask('00.000.000/0000-00');

        $('#ide_efr').change(function() {
            if($(this).val() === 'N') {
                $('#div_cnpj_efr').removeClass('d-none');
            } else {
                $('#div_cnpj_efr').addClass('d-none');
                $('#cnpj_efr').val('');
            }
        });

        carregarDados();
    });

    function carregarDados() {
        $.ajax({
            url: '/sistema_irrf/public/api/api-r1000.php?action=verificar_status',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if(res.success) {
                    const d = res.dados;
                    $('#cnpj').val(formatCnpj(d.cnpj));
                    $('#nome_oficial').val(d.nome_oficial);
                    
                    // Preenche campos editáveis
                    if(d.classificacao_tributaria) $('#classificacao_tributaria').val(d.classificacao_tributaria);
                    if(d.indicador_ecd !== null) $('#indicador_ecd').val(d.indicador_ecd);
                    if(d.indicador_desoneracao !== null) $('#indicador_desoneracao').val(d.indicador_desoneracao);
                    
                    if(d.ide_efr) {
                        $('#ide_efr').val(d.ide_efr).trigger('change');
                    }
                    if(d.cnpj_efr) $('#cnpj_efr').val(formatCnpj(d.cnpj_efr));
                    
                    $('#contato_nome').val(d.contato_nome);
                    $('#contato_cpf').val(d.contato_cpf).trigger('input');
                    $('#contato_telefone').val(d.contato_telefone).trigger('input');
                    $('#contato_email').val(d.contato_email);

                    // Atualiza Status Visual
                    atualizarStatusVisual(res.tem_cadastro, d.r1000_recibo);
                    
                    // Histórico
                    renderHistorico(res.historico);
                }
            }
        });
    }

    function salvarDados() {
        const dados = getFormData();
        $.ajax({
            url: '/sistema_irrf/public/api/api-r1000.php?action=salvar',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(dados),
            success: function(res) {
                if(res.success) alert('Dados salvos com sucesso!');
                else alert('Erro: ' + res.error);
            }
        });
    }

    function validarR1000() {
        salvarDados(); // Salva antes de validar
        $('#loadingOverlay').removeClass('d-none');
        $('#loadingMessage').text('Validando XML...');

        $.ajax({
            url: '/sistema_irrf/public/api/api-r1000.php?action=validar',
            type: 'POST',
            success: function(res) {
                $('#loadingOverlay').addClass('d-none');
                if(res.success) {
                    alert(res.message);
                } else {
                    if (res.erros && Array.isArray(res.erros)) {
                        let msg = "Erros de Validação:\n" + res.erros.join("\n");
                        alert(msg);
                    } else {
                        alert('Erro: ' + (res.error || 'Erro desconhecido'));
                    }
                }
            }
        });
    }

    function enviarR1000() {
        if(!confirm('Confirma o envio do cadastro para a Receita Federal?')) return;
        
        salvarDados();
        $('#loadingOverlay').removeClass('d-none');
        $('#loadingMessage').text('Enviando para a Receita...');

        $.ajax({
            url: '/sistema_irrf/public/api/api-r1000.php?action=enviar',
            type: 'POST',
            success: function(res) {
                if(res.success) {
                    $('#loadingMessage').text('Consultando processamento...');
                    // Aguarda 2s e consulta
                    setTimeout(() => {
                        consultarR1000(res.id_lote);
                    }, 2000);
                } else {
                    $('#loadingOverlay').addClass('d-none');
                    alert('Erro no envio: ' + res.error);
                }
            }
        });
    }

    function excluirR1000() {
        if(!confirm('ATENÇÃO: Isso enviará um evento de EXCLUSÃO para a Receita Federal, removendo o cadastro do órgão do ambiente de testes.\n\nTem certeza que deseja resetar a base?')) return;
        
        $('#loadingOverlay').removeClass('d-none');
        $('#loadingMessage').text('Enviando exclusão...');

        $.ajax({
            url: '/sistema_irrf/public/api/api-r1000.php?action=excluir',
            type: 'POST',
            success: function(res) {
                if(res.success) {
                    $('#loadingMessage').text('Consultando exclusão...');
                    setTimeout(() => {
                        consultarR1000(res.id_lote);
                    }, 2000);
                } else {
                    $('#loadingOverlay').addClass('d-none');
                    alert('Erro ao excluir: ' + res.error);
                }
            }
        });
    }

    function resetarLocal() {
        if(!confirm('ATENÇÃO: Esta ação apagará TODOS os registros locais de envio (Lotes, Eventos, Recibos) deste órgão.\n\nIsso NÃO envia nada para a Receita Federal, apenas limpa seu banco de dados local para recomeçar.\n\nDeseja continuar?')) return;
        
        if(!confirm('Tem certeza absoluta? As notas voltarão para a lista de pendências e o histórico será perdido.')) return;

        $('#loadingOverlay').removeClass('d-none');
        $('#loadingMessage').text('Limpando base de dados...');

        $.ajax({
            url: '/sistema_irrf/public/api/api-r1000.php?action=resetar_local',
            type: 'POST',
            success: function(res) {
                $('#loadingOverlay').addClass('d-none');
                if(res.success) {
                    alert(res.message);
                    carregarDados();
                } else {
                    alert('Erro ao resetar: ' + res.error);
                }
            }
        });
    }

    function limparEventosReceita() {
        if(!confirm('ATENÇÃO: Esta ação enviará eventos de EXCLUSÃO (R-9000) para TODOS os eventos R-4020 ativos deste órgão na Receita Federal.\n\nIsso é necessário para permitir a exclusão do cadastro R-1000.\n\nDeseja continuar?')) return;

        $('#loadingOverlay').removeClass('d-none');
        $('#loadingMessage').text('Enviando exclusões em massa...');

        $.ajax({
            url: '/sistema_irrf/public/api/api-r1000.php?action=limpar_eventos',
            type: 'POST',
            success: function(res) {
                $('#loadingOverlay').addClass('d-none');
                if(res.success) {
                    alert(res.message);
                } else {
                    alert('Erro/Aviso: ' + res.error);
                }
            }
        });
    }

    function consultarR1000(idLote = null) {
        if(!idLote) {
            // Se não passou ID, tenta pegar o último do histórico (implementação futura)
            alert('ID do lote necessário para consulta manual neste momento.');
            return;
        }

        $.ajax({
            url: '/sistema_irrf/public/api/api-r1000.php?action=consultar',
            type: 'POST',
            data: { id_lote: idLote },
            success: function(res) {
                $('#loadingOverlay').addClass('d-none');
                if(res.success) {
                    if(res.status === 'sucesso') {
                        alert('Sucesso! Recibo: ' + res.recibo);
                        carregarDados();
                    } else if(res.status === 'erro') {
                        alert('Erro no processamento: ' + res.mensagem);
                    } else {
                        alert('Ainda em processamento. Tente novamente em instantes.');
                    }
                } else {
                    alert('Erro na consulta: ' + res.error);
                }
            }
        });
    }

    function getFormData() {
        return {
            classificacao_tributaria: $('#classificacao_tributaria').val(),
            indicador_ecd: $('#indicador_ecd').val(),
            indicador_desoneracao: $('#indicador_desoneracao').val(),
            contato_nome: $('#contato_nome').val(),
            contato_cpf: $('#contato_cpf').val(),
            contato_telefone: $('#contato_telefone').val(),
            contato_email: $('#contato_email').val(),
            ide_efr: $('#ide_efr').val(),
            cnpj_efr: $('#cnpj_efr').val()
        };
    }

    function atualizarStatusVisual(ativo, recibo) {
        const html = ativo 
            ? `<div class="text-success fw-bold"><i class="fas fa-check-circle fa-3x mb-2"></i><br>Cadastro Ativo<br><small>Recibo: ${recibo}</small></div>`
            : `<div class="text-warning fw-bold"><i class="fas fa-exclamation-circle fa-3x mb-2"></i><br>Pendente de Envio</div>`;
        $('#status-cadastro').html(html);
    }

    function formatCnpj(v) {
        if(!v) return '';
        v = v.replace(/\D/g, "");
        return v.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, "$1.$2.$3/$4-$5");
    }

    function renderHistorico(lista) {
        let html = '';
        if(!lista || lista.length === 0) {
            html = '<tr><td colspan="6" class="text-center text-muted">Nenhum envio registrado.</td></tr>';
        } else {
            lista.forEach(item => {
                let statusClass = item.status === 'sucesso' ? 'text-success' : (item.status === 'rejeitado' ? 'text-danger' : 'text-warning');
                let acao = '';
                
                if (item.status === 'em_lote' && item.id_lote) {
                    acao = `<button class="btn btn-sm btn-outline-primary" onclick="consultarR1000(${item.id_lote})"><i class="fas fa-sync-alt"></i></button>`;
                }

                html += `<tr>
                    <td>${new Date(item.created_at).toLocaleString('pt-BR')}</td>
                    <td class="${statusClass} fw-bold">${item.status.toUpperCase()}</td>
                    <td>-</td>
                    <td>${item.numero_recibo || '-'}</td>
                    <td>${item.mensagem_erro || '-'}</td>
                    <td>${acao}</td>
                </tr>`;
            });
        }
        $('#historico-body').html(html);
    }
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>