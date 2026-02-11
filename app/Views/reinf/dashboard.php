<?php
// app/Views/reinf/dashboard.php
require_once __DIR__ . '/../layout/header.php';
?>

<div class="container-fluid mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-primary fw-bold"><i class="fas fa-university me-2"></i>Gestão EFD-Reinf (R-4020)</h2>
            <p class="text-muted mb-0">Gerenciamento de retenções na fonte e transmissão assíncrona.</p>
        </div>
        <div class="card shadow-sm border-0">
            <div class="card-body p-2 d-flex align-items-center gap-2">
                <label for="filtroPeriodo" class="fw-bold text-secondary mb-0">Período:</label>
                <input type="month" id="filtroPeriodo" class="form-control form-control-sm border-primary"
                    value="<?= date('Y-m') ?>" style="width: 160px;">
                <button onclick="carregarDados()" class="btn btn-primary btn-sm">
                    <i class="fas fa-sync-alt me-1"></i> Atualizar
                </button>
            </div>
        </div>
    </div>

    <div id="alertContainer"></div>

    <div class="row">
        <div class="col-xl-6 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h5 class="card-title text-primary mb-0"><i class="fas fa-list-ul me-2"></i>Pendências de Envio</h5>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tabelaPendencias">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" width="40"><input type="checkbox" id="checkTodos"
                                        onclick="toggleTodos(this)"></th>
                                <th>Fornecedor</th>
                                <th class="text-center">Qtd. Notas</th>
                                <th class="text-end pe-4">Total Retido</th>
                            </tr>
                        </thead>
                        <tbody id="listaPendencias">
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Carregando...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-light border-top-0 p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Total Selecionado:</span>
                            <h4 class="mb-0 text-success fw-bold" id="totalSelecionado">R$ 0,00</h4>
                        </div>
                        <button type="button" class="btn btn-info" onclick="validarLoteLocal()">
                            <i class="fas fa-check-double"></i> Validar XML (Local)
                        </button>
                        <button id="btnTransmitir" onclick="transmitirLote()" class="btn btn-success px-4" disabled>
                            <i class="fas fa-paper-plane me-2"></i>Transmitir Lote
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-white border-bottom-0 py-3 d-flex justify-content-between">
                    <h5 class="card-title text-secondary mb-0"><i class="fas fa-history me-2"></i>Histórico de
                        Transmissões</h5>
                    <span class="badge bg-light text-dark border">Últimos 10 lotes</span>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-striped align-middle mb-0" id="tabelaHistorico">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Protocolo / Data</th>
                                <th class="text-center">Status</th>
                                <th class="text-end pe-4">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="listaHistorico">
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">Nenhum histórico encontrado.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetalhes" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do Processamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <pre id="conteudoDetalhes" class="bg-light p-3 border rounded"></pre>
            </div>
        </div>
    </div>
</div>

<div id="loadingOverlay"
    class="d-none position-fixed top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex justify-content-center align-items-center"
    style="z-index: 9999;">
    <div class="text-center">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-2 fw-bold text-primary">Processando comunicação com a Receita...</p>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        carregarDados();
    });

    // --- FUNÇÕES PRINCIPAIS ---

    function carregarDados() {
        const periodo = $('#filtroPeriodo').val();
        toggleLoading(true);

        $.ajax({
            url: 'api/reinf.php?action=listar_pendencias',
            method: 'GET',
            data: { periodo: periodo },
            dataType: 'json',
            success: function (res) {
                toggleLoading(false);
                if (res.success) {
                    renderPendencias(res.pendencias);
                    renderHistorico(res.historico);
                } else {
                    showAlert('danger', 'Erro ao carregar dados: ' + res.error);
                }
            },
            error: function (xhr) {
                toggleLoading(false);
                console.error(xhr.responseText);
                showAlert('danger', 'Erro de conexão com o servidor.');
            }
        });
    }

    function validarLoteLocal() {
        let periodo = $('#filtroPeriodo').val();
        let fornecedores = [];

        $('.check-fornecedor:checked').each(function () {
            fornecedores.push($(this).val());
        });

        if (fornecedores.length === 0) {
            alert('Selecione pelo menos um fornecedor.');
            return;
        }

        // Mostra loading...
        if (typeof toggleLoading === 'function') toggleLoading(true);
        else $('#loadingOverlay').removeClass('d-none');

        $.ajax({
            url: 'api/reinf.php?action=validar_lote',
            method: 'POST',
            data: JSON.stringify({ periodo: periodo, fornecedores: fornecedores }),
            contentType: 'application/json',
            success: function (response) {
                if (typeof toggleLoading === 'function') toggleLoading(false);
                else $('#loadingOverlay').addClass('d-none');

                // --- CORREÇÃO DO ERRO JAVASCRIPT ---

                // 1. Verifica se deu erro no servidor (Exception PHP)
                if (response.success === false) {
                    alert('Erro no Servidor: ' + response.error);
                    return; // Para aqui para não travar
                }

                // 2. Agora é seguro ler 'invalidos'
                if (response.invalidos && response.invalidos.length > 0) {
                    let html = '<div class="alert alert-danger"><strong>Erros encontrados no XSD:</strong><br>';
                    response.invalidos.forEach(item => {
                        html += `<strong>${item.fornecedor}:</strong><ul>`;
                        item.erros.forEach(erro => {
                            html += `<li>${erro}</li>`;
                        });
                        html += '</ul></div>';
                    });
                    $('#alertContainer').html(html);
                } else {
                    $('#alertContainer').html('<div class="alert alert-success"><i class="fas fa-check"></i> Todos os XMLs selecionados estão válidos conforme o XSD!</div>');
                }
            },
            error: function (xhr) {
                if (typeof toggleLoading === 'function') toggleLoading(false);
                else $('#loadingOverlay').addClass('d-none');

                // Tenta ler mensagem de erro do PHP se vier em JSON
                let msg = 'Erro ao validar.';
                try {
                    let jsonErr = JSON.parse(xhr.responseText);
                    if (jsonErr.error) msg += ' ' + jsonErr.error;
                } catch (e) {
                    msg += ' ' + xhr.responseText;
                }
                alert(msg);
            }
        });
    }

    function transmitirLote() {
        const periodo = $('#filtroPeriodo').val();
        let fornecedores = [];

        $('.check-fornecedor:checked').each(function () {
            fornecedores.push($(this).val());
        });

        if (fornecedores.length === 0) {
            showAlert('warning', 'Selecione pelo menos um fornecedor.');
            return;
        }

        if (!confirm(`Confirma a transmissão do R-4020 para ${fornecedores.length} fornecedores?`)) return;

        toggleLoading(true);

        $.ajax({
            url: 'api/reinf.php?action=enviar_lote',
            method: 'POST',
            data: JSON.stringify({
                periodo: periodo,
                fornecedores: fornecedores
            }),
            contentType: 'application/json',
            dataType: 'json',
            success: function (res) {
                toggleLoading(false);
                if (res.success) {
                    showAlert('success', `Lote enviado! Protocolo: ${res.protocolo}`);
                    carregarDados(); // Recarrega para mover da pendência para o histórico
                } else {
                    showAlert('danger', res.error);
                }
            },
            error: function (xhr) {
                toggleLoading(false);
                let msg = 'Erro desconhecido.';
                try {
                    let json = JSON.parse(xhr.responseText);
                    msg = json.error;
                } catch (e) { msg = xhr.responseText; }
                showAlert('danger', 'Falha na transmissão: ' + msg);
            }
        });
    }

    function consultarLote(idLote) {
        toggleLoading(true);
        $.ajax({
            url: 'api/reinf.php?action=consultar_lote',
            method: 'POST',
            data: { id_lote: idLote },
            dataType: 'json',
            success: function (res) {
                toggleLoading(false);
                if (res.success) {
                    let tipo = res.status_lote === 'processado' ? 'success' : 'warning';
                    showAlert(tipo, res.mensagem);
                    carregarDados(); // Atualiza o status na tabela visualmente
                } else {
                    showAlert('danger', res.error);
                }
            },
            error: function (xhr) {
                toggleLoading(false);
                showAlert('danger', 'Erro ao consultar: ' + xhr.responseText);
            }
        });
    }

    // --- RENDERIZAÇÃO ---

    function renderPendencias(lista) {
        let html = '';
        let totalGeral = 0;

        if (lista.length === 0) {
            html = '<tr><td colspan="4" class="text-center py-4 text-muted"><i class="fas fa-check-circle me-2"></i>Nenhuma pendência para este período.</td></tr>';
        } else {
            lista.forEach(f => {
                let valor = parseFloat(f.total_ir);
                totalGeral += valor;

                html += `
                <tr>
                    <td class="ps-4">
                        <input type="checkbox" class="form-check-input check-fornecedor" value="${f.id_fornecedor}" data-valor="${valor}" onclick="atualizarTotal()">
                    </td>
                    <td>
                        <div class="fw-bold text-dark">${f.razao_social}</div>
                        <div class="small text-muted">CNPJ: ${formatCnpj(f.cnpj)}</div>
                    </td>
                    <td class="text-center"><span class="badge bg-light text-dark border">${f.qtd_pagamentos} pagtos</span></td>
                    <td class="text-end pe-4 fw-bold text-danger">${formatMoney(valor)}</td>
                </tr>
            `;
            });
        }

        $('#listaPendencias').html(html);
        $('#checkTodos').prop('checked', false);
        atualizarTotal();
    }

    function renderHistorico(lista) {
        let html = '';

        if (lista.length === 0) {
            html = '<tr><td colspan="3" class="text-center py-4 text-muted">Nenhum envio registrado.</td></tr>';
        } else {
            lista.forEach(l => {
                let statusBadge = '';
                let btnAcao = '';

                // Lógica de Status
                switch (l.status) {
                    case 'enviado':
                    case 'processando':
                        statusBadge = '<span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Em Análise</span>';
                        btnAcao = `<button onclick="consultarLote(${l.id})" class="btn btn-sm btn-outline-primary"><i class="fas fa-sync me-1"></i>Consultar</button>`;
                        break;
                    case 'processado':
                        // Status 3 ou 4 caem aqui
                        statusBadge = '<span class="badge bg-success"><i class="fas fa-check-double me-1"></i>Finalizado</span>';
                        // BOTÃO ATIVADO AGORA:
                        btnAcao = `<button onclick="verDetalhes(${l.id})" class="btn btn-sm btn-info text-white"><i class="fas fa-search me-1"></i>Ver Detalhes</button>`;
                        break;
                    case 'erro':
                        statusBadge = '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Falha Lote</span>';
                        btnAcao = `<button onclick="verDetalhes(${l.id})" class="btn btn-sm btn-outline-danger">Ver Erro</button>`;
                        break;
                    default:
                        statusBadge = '<span class="badge bg-secondary">' + l.status + '</span>';
                }

                let dataEnvio = new Date(l.created_at).toLocaleString('pt-BR');

                html += `
                <tr>
                    <td class="ps-4">
                        <div class="fw-bold text-dark">Protocolo: ${l.protocolo || 'N/D'}</div>
                        <div class="small text-muted"><i class="far fa-calendar-alt me-1"></i>${dataEnvio}</div>
                    </td>
                    <td class="text-center">${statusBadge}</td>
                    <td class="text-end pe-4">${btnAcao}</td>
                </tr>
            `;
            });
        }

        $('#listaHistorico').html(html);
    }

    function verDetalhes(idLote) {
        toggleLoading(true);
        $.ajax({
            url: 'api/reinf.php?action=detalhar_lote',
            method: 'GET',
            data: { id_lote: idLote },
            dataType: 'json',
            success: function (res) {
                toggleLoading(false);
                if (res.success) {
                    let html = '<div class="table-responsive"><table class="table table-bordered table-sm">';
                    html += '<thead class="table-light"><tr><th>Fornecedor</th><th>Status</th><th>Detalhe (Recibo ou Erro)</th></tr></thead><tbody>';

                    res.eventos.forEach(evt => {
                        let statusColor = evt.status === 'sucesso' ? 'text-success' : 'text-danger';
                        let icone = evt.status === 'sucesso' ? '<i class="fas fa-check me-1"></i>' : '<i class="fas fa-times me-1"></i>';
                        let detalhe = evt.status === 'sucesso'
                            ? `<span class="fw-bold text-success">${evt.numero_recibo}</span>`
                            : `<span class="text-danger small">${evt.mensagem_erro || 'Erro sem descrição'}</span>`;

                        html += `
                        <tr>
                            <td>
                                <div class="fw-bold">${evt.razao_social}</div>
                                <div class="small text-muted">${formatCnpj(evt.cnpj)}</div>
                            </td>
                            <td class="${statusColor} fw-bold">${icone} ${evt.status.toUpperCase()}</td>
                            <td>${detalhe}</td>
                        </tr>
                    `;
                    });

                    html += '</tbody></table></div>';

                    // Preenche e abre o modal
                    $('#conteudoDetalhes').html(html);
                    $('#conteudoDetalhes').removeClass('bg-light p-3 border').addClass('p-0'); // Remove estilos de texto puro
                    new bootstrap.Modal(document.getElementById('modalDetalhes')).show();
                } else {
                    showAlert('danger', res.error);
                }
            },
            error: function () {
                toggleLoading(false);
                showAlert('danger', 'Erro ao carregar detalhes.');
            }
        });
    }

    // --- UTILITÁRIOS ---

    function atualizarTotal() {
        let total = 0;
        let count = 0;
        $('.check-fornecedor:checked').each(function () {
            total += parseFloat($(this).data('valor'));
            count++;
        });

        $('#totalSelecionado').text(formatMoney(total));
        $('#btnTransmitir').prop('disabled', count === 0);
    }

    function toggleTodos(source) {
        $('.check-fornecedor').prop('checked', source.checked);
        atualizarTotal();
    }

    function toggleLoading(show) {
        if (show) $('#loadingOverlay').removeClass('d-none');
        else $('#loadingOverlay').addClass('d-none');
    }

    function showAlert(type, message) {
        // Tipos: success, danger, warning, info
        let icon = type === 'success' ? 'check-circle' : (type === 'danger' ? 'exclamation-circle' : 'info-circle');
        let html = `
        <div class="alert alert-${type} alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-${icon} me-2"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
        $('#alertContainer').html(html);

        // Auto-hide após 10 segundos
        setTimeout(() => { $('.alert').alert('close'); }, 10000);
    }

    function formatMoney(value) {
        return value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }

    function formatCnpj(v) {
        v = v.replace(/\D/g, "");
        return v.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, "$1.$2.$3/$4-$5");
    }
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>